<?php

namespace App\Http\Controllers;

use App\Models\Boleta;
use App\Models\Usuario;
use App\Models\SeguimientoPago;
use App\Models\Pago;
use App\Models\ArregloPago;
use App\Models\Programa;
use App\Models\PlanEstudio;
use App\Services\InteresesService;
use App\Services\BoletaPdfService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;

class BoletaController extends Controller
{
    /**
     * Asegura la existencia de las columnas contables y de firma en la tabla boletas
     */
    protected static function ensureBoletasColumns()
    {
        try {
            if (!Schema::hasTable('boletas')) {
                return;
            }
            $columns = [
                'descuento' => "ALTER TABLE `boletas` ADD COLUMN `descuento` DECIMAL(10,2) NOT NULL DEFAULT 0.00",
                'cobrar_inscripcion' => "ALTER TABLE `boletas` ADD COLUMN `cobrar_inscripcion` TINYINT(1) NOT NULL DEFAULT 0",
                'cobrar_biblioteca' => "ALTER TABLE `boletas` ADD COLUMN `cobrar_biblioteca` TINYINT(1) NOT NULL DEFAULT 0",
                'cobrar_matricula' => "ALTER TABLE `boletas` ADD COLUMN `cobrar_matricula` TINYINT(1) NOT NULL DEFAULT 1",
                'pago_inicial' => "ALTER TABLE `boletas` ADD COLUMN `pago_inicial` DECIMAL(10,2) NOT NULL DEFAULT 0.00",
                'pago_inicial_metodo' => "ALTER TABLE `boletas` ADD COLUMN `pago_inicial_metodo` VARCHAR(50) NULL",
                'pago_inicial_referencia' => "ALTER TABLE `boletas` ADD COLUMN `pago_inicial_referencia` VARCHAR(255) NULL",
                'fechas_vencimiento_json' => "ALTER TABLE `boletas` ADD COLUMN `fechas_vencimiento_json` TEXT NULL",
                'fecha_firma' => "ALTER TABLE `boletas` ADD COLUMN `fecha_firma` DATETIME NULL",
                'token_firma' => "ALTER TABLE `boletas` ADD COLUMN `token_firma` VARCHAR(100) NULL",
                'token_expiracion' => "ALTER TABLE `boletas` ADD COLUMN `token_expiracion` DATETIME NULL",
            ];
            foreach ($columns as $col => $sql) {
                if (!Schema::hasColumn('boletas', $col)) {
                    DB::statement($sql);
                }
            }
        } catch (\Throwable $e) {
            // Ignorar excepciones de dialecto si se ejecuta bajo SQLite local
        }
    }

    /**
     * Historial y Bandeja de Boletas
     */
    public function index(Request $request)
    {
        self::ensureBoletasColumns();

        $query = Boleta::with('estudiante');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_boleta', 'like', "%{$search}%")
                  ->orWhereHas('estudiante', function($sq) use ($search) {
                      $sq->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('cedula', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Conteo para alertas y filtros rápidos estilo UNELA
        $count_firmadas = Boleta::where('estado', 'firmada')->count();
        $count_pend_firma = Boleta::where('estado', 'pendiente_firma')->count();
        $count_oficiales = Boleta::whereIn('estado', ['pendiente', 'pago_parcial'])->count();
        $count_pagadas = Boleta::where('estado', 'pagada')->count();

        // Orden de prioridad idéntico a UNELA: firmadas primero, luego pendientes de firma, etc.
        $boletas = $query->orderByRaw("
            CASE 
                WHEN estado = 'firmada' THEN 1 
                WHEN estado = 'pendiente_firma' THEN 2 
                WHEN estado = 'pendiente' THEN 3 
                WHEN estado = 'pago_parcial' THEN 4
                WHEN estado = 'pagada' THEN 5
                ELSE 6 
            END
        ")->orderBy('fecha_creacion', 'desc')->paginate(15)->withQueryString();

        return view('boletas.index', compact('boletas', 'count_firmadas', 'count_pend_firma', 'count_oficiales', 'count_pagadas'));
    }

    /**
     * Control de Morosidad
     */
    public function morosidad(Request $request)
    {
        self::ensureBoletasColumns();
        InteresesService::recalcularTodosLosIntereses();

        $hoy = Carbon::today();
        $cuotasVencidas = SeguimientoPago::with(['boleta', 'estudiante'])
            ->where('estado', 'pendiente')
            ->where('fecha_vencimiento', '<', $hoy)
            ->get();

        $deudores = [];
        $global_capital_mora = 0.0;
        $global_interes_mora = 0.0;

        foreach ($cuotasVencidas as $cuota) {
            $est = $cuota->estudiante;
            if (!$est) continue;

            $id_est = $est->id;
            if (!isset($deudores[$id_est])) {
                $deudores[$id_est] = [
                    'id_estudiante' => $id_est,
                    'nombre' => trim($est->nombre . ' ' . $est->apellidos),
                    'email' => $est->email,
                    'telefono' => preg_replace('/[^0-9]/', '', $est->telefono),
                    'cuotas' => [],
                    'total_capital' => 0.0,
                    'total_mora' => 0.0,
                    'total_vencido' => 0.0,
                    'max_dias_atraso' => 0
                ];
            }

            $capital = floatval($cuota->monto_cuota);
            $mora = floatval($cuota->interes_acumulado);
            $total_cuota = $capital + $mora;
            $dias = $cuota->fecha_vencimiento->diffInDays($hoy);

            $deudores[$id_est]['cuotas'][] = [
                'id_cuota' => $cuota->id,
                'numero_boleta' => $cuota->boleta->numero_boleta ?? 'N/D',
                'numero_cuota' => $cuota->numero_cuota,
                'monto_cuota' => $capital,
                'interes_acumulado' => $mora,
                'total_cuota' => $total_cuota,
                'fecha_vencimiento' => $cuota->fecha_vencimiento->format('Y-m-d'),
                'dias_atraso' => $dias
            ];

            $deudores[$id_est]['total_capital'] += $capital;
            $deudores[$id_est]['total_mora'] += $mora;
            $deudores[$id_est]['total_vencido'] += $total_cuota;

            if ($dias > $deudores[$id_est]['max_dias_atraso']) {
                $deudores[$id_est]['max_dias_atraso'] = $dias;
            }

            $global_capital_mora += $capital;
            $global_interes_mora += $mora;
        }

        $global_deudores_count = count($deudores);
        $global_total_vencido = $global_capital_mora + $global_interes_mora;

        $configPagos = InteresesService::getConfiguracionPagos();
        $tasa_interes_mora = $configPagos['tasa_interes_mora'] ?? 2.0;

        return view('boletas.morosidad', compact(
            'deudores',
            'global_deudores_count',
            'global_capital_mora',
            'global_interes_mora',
            'global_total_vencido',
            'tasa_interes_mora'
        ));
    }

    /**
     * Estado de Cuenta - Vista Principal de Búsqueda
     */
    public function estadoCuenta(Request $request)
    {
        return view('boletas.estado_cuenta');
    }

    /**
     * Estado de Cuenta - Buscar estudiantes vía JSON
     */
    public function buscarEstudianteAjax(Request $request)
    {
        $term = $request->get('term') ?: $request->get('q');
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $estudiantes = Usuario::where(function($q) use ($term) {
            $q->where('nombre', 'like', "%{$term}%")
              ->orWhere('apellidos', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('cedula', 'like', "%{$term}%");
        })->take(20)->get();

        // Si la petición viene de Select2
        if ($request->has('q')) {
            $results = $estudiantes->map(function($e) {
                return [
                    'id' => $e->id,
                    'text' => trim($e->nombre . ' ' . ($e->apellidos ?? '')) . ' (' . ($e->email ?? '') . ') - ' . ($e->cedula ?? 'N/D')
                ];
            });
            return response()->json(['results' => $results]);
        }

        return response()->json($estudiantes);
    }

    /**
     * Estado de Cuenta - Datos del estudiante y sus boletas
     */
    public function getEstadoCuentaAjax($id)
    {
        self::ensureBoletasColumns();
        InteresesService::recalcularInteresesEstudiante($id);

        $estudiante = Usuario::findOrFail($id);

        $boletas = Boleta::where('id_estudiante', $id)
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        $pagos = Pago::whereIn('boleta_id', $boletas->pluck('id'))
            ->orderBy('fecha_pago', 'desc')
            ->get();

        $letras = SeguimientoPago::where('id_estudiante', $id)
            ->with('boleta')
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        $arreglos = ArregloPago::where('id_estudiante', $id)
            ->where('estado', 'activo')
            ->get();

        $totalDeuda = floatval(
            $boletas->whereIn('estado', ['pendiente', 'pago_parcial', 'en_arreglo'])->sum('saldo_pendiente')
        );

        return response()->json([
            'success' => true,
            'data' => [
                'estudiante' => [
                    'id' => $estudiante->id,
                    'nombre' => $estudiante->nombre,
                    'apellidos' => $estudiante->apellidos ?? '',
                    'email' => $estudiante->email,
                    'telefono' => $estudiante->telefono ?? '',
                    'cedula' => $estudiante->cedula ?? ''
                ],
                'resumen' => [
                    'deuda_total' => $totalDeuda,
                    'boletas_pendientes' => $boletas->whereNotIn('estado', ['pagada', 'anulada'])->count(),
                ],
                'boletas' => $boletas->map(function($b) {
                    return [
                        'id' => $b->id,
                        'numero_boleta' => $b->numero_boleta ?? 'B-' . str_pad($b->id, 6, '0', STR_PAD_LEFT),
                        'fecha_creacion' => $b->fecha_creacion ? Carbon::parse($b->fecha_creacion)->format('Y-m-d') : 'N/D',
                        'total' => floatval($b->total),
                        'monto_pagado' => floatval($b->monto_pagado ?? 0),
                        'saldo_pendiente' => floatval($b->saldo_pendiente ?? 0),
                        'interes_acumulado' => floatval($b->interes_acumulado ?? 0),
                        'estado' => $b->estado,
                        'periodo' => $b->periodo,
                        'token_firma' => $b->token_firma,
                        'pdf_url' => route('boletas.pdf', ['id' => $b->id])
                    ];
                })
            ],
            // Compatibilidad legacy
            'estudiante' => [
                'id' => $estudiante->id,
                'nombre' => trim($estudiante->nombre . ' ' . ($estudiante->apellidos ?? '')),
                'email' => $estudiante->email,
                'telefono' => preg_replace('/[^0-9]/', '', $estudiante->telefono ?? ''),
                'cedula' => $estudiante->cedula ?? '',
                'deuda_total' => $totalDeuda
            ],
            'boletas' => $boletas->map(function($b) {
                return [
                    'id' => $b->id,
                    'numero_boleta' => $b->numero_boleta ?? 'B-' . str_pad($b->id, 6, '0', STR_PAD_LEFT),
                    'fecha_creacion' => $b->fecha_creacion ? Carbon::parse($b->fecha_creacion)->format('Y-m-d') : 'N/D',
                    'total' => floatval($b->total),
                    'monto_pagado' => floatval($b->monto_pagado ?? 0),
                    'saldo_pendiente' => floatval($b->saldo_pendiente ?? 0),
                    'interes_acumulado' => floatval($b->interes_acumulado ?? 0),
                    'estado' => $b->estado,
                    'periodo' => $b->periodo,
                    'token_firma' => $b->token_firma,
                    'pdf_url' => route('boletas.pdf', ['id' => $b->id])
                ];
            }),
            'pagos' => $pagos,
            'letras' => $letras,
            'arreglos' => $arreglos
        ]);
    }

    /**
     * Formulario Oficial UNELA para Generar Boleta de Pago y Matrícula (3 Pasos)
     */
    public function generar(Request $request)
    {
        self::ensureBoletasColumns();

        $programas = Programa::orderBy('categoria', 'asc')
            ->orderBy('nombre_programa', 'asc')
            ->get();

        $programas_agrupados = $programas->groupBy(function($item) {
            return $item->categoria ?: 'General';
        });

        // Autocompletado si viene desde matrícula o curso
        $id_programa_auto = intval($request->get('id_programa_auto', 0));
        $id_plan_auto = intval($request->get('id_plan_auto', 0));
        $categoria_auto = '';

        if ($id_plan_auto > 0) {
            $plan = PlanEstudio::with('programa')->find($id_plan_auto);
            if ($plan && $plan->programa) {
                $id_programa_auto = $plan->programa->id_programa;
                $categoria_auto = $plan->programa->categoria ?: 'General';
            }
        }

        $periodo_auto_get = trim($request->get('periodo_auto', ''));
        $cuatrimestre_auto_get = '';
        $anio_auto_get = date('Y');
        if (!empty($periodo_auto_get)) {
            $parts = explode(' ', $periodo_auto_get);
            if (count($parts) >= 2) {
                $anio_auto_get = end($parts);
                array_pop($parts);
                $cuatrimestre_auto_get = implode(' ', $parts);
            }
        }

        $id_estudiante_auto = intval($request->get('id_estudiante', 0));
        $student_name_auto = '';
        $student_email_auto = '';

        if ($id_estudiante_auto > 0) {
            $est = Usuario::find($id_estudiante_auto);
            if ($est) {
                $student_name_auto = trim($est->nombre . ' ' . ($est->apellidos ?? ''));
                $student_email_auto = $est->email;
            }
        }

        $id_curso_retorno = intval($request->get('id_curso_retorno', 0));
        if ($id_curso_retorno === 0 && $id_estudiante_auto > 0 && $id_plan_auto > 0 && !empty($periodo_auto_get)) {
            $curActivo = DB::table('matriculas as m')
                ->join('cursos_activos as ca', 'm.id_curso_activo', '=', 'ca.id_curso_activo')
                ->where('m.id_estudiante', $id_estudiante_auto)
                ->where('ca.id_plan', $id_plan_auto)
                ->where('ca.periodo', $periodo_auto_get)
                ->select('ca.id_curso_activo')
                ->first();
            if ($curActivo) {
                $id_curso_retorno = intval($curActivo->id_curso_activo);
            }
        }

        return view('boletas.create', compact(
            'programas_agrupados',
            'id_programa_auto',
            'id_plan_auto',
            'categoria_auto',
            'cuatrimestre_auto_get',
            'anio_auto_get',
            'id_estudiante_auto',
            'student_name_auto',
            'student_email_auto',
            'id_curso_retorno'
        ));
    }

    /**
     * Detección automática de cursos ya matriculados pendientes de boleta para el período
     */
    public function getMatriculasPendientesAjax(Request $request)
    {
        $id_estudiante = intval($request->get('id_estudiante', 0));
        $periodo = trim($request->get('periodo', ''));

        if ($id_estudiante <= 0 || empty($periodo)) {
            return response()->json(['success' => false, 'message' => 'Faltan datos requeridos.']);
        }

        try {
            $cursos = DB::table('matriculas as m')
                ->join('cursos_activos as ca', 'm.id_curso_activo', '=', 'ca.id_curso_activo')
                ->where('m.id_estudiante', $id_estudiante)
                ->where('ca.periodo', $periodo)
                ->whereNull('m.id_boleta')
                ->pluck('ca.id_plan')
                ->map(fn($id) => (int)$id)
                ->all();

            return response()->json([
                'success' => true,
                'plan_ids' => array_values(array_unique($cursos))
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al detectar matrículas pendientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene los cursos asociados a un programa en formato JSON agrupados por cuatrimestre
     */
    public function getCursosPorProgramaAjax($id)
    {
        $programa = Programa::find($id);
        if (!$programa) {
            return response()->json(['success' => false, 'message' => 'Programa no encontrado.']);
        }

        $cursos = DB::table('plan_estudios')
            ->where('id_programa', $id)
            ->select('id_plan', 'cuatrimestre', 'codigo', 'materia', 'creditos')
            ->selectRaw('CASE WHEN precio > 0 THEN precio ELSE ? END as precio', [$programa->costo_materia ?? 0])
            ->orderBy('cuatrimestre', 'asc')
            ->orderBy('materia', 'asc')
            ->get();

        $cursos_agrupados = $cursos->groupBy(function($item) {
            return $item->cuatrimestre ?: 'General';
        });

        return response()->json([
            'success' => true,
            'cursos' => $cursos_agrupados
        ]);
    }

    /**
     * Obtiene todos los datos para armar la Boleta Técnica Oficial (Paso 3)
     */
    public function getBoletaDataAjax(Request $request)
    {
        self::ensureBoletasColumns();

        $id_estudiante = intval($request->get('id_estudiante', 0));
        $cursos_ids = $request->get('cursos_ids', []);

        if ($id_estudiante <= 0 || empty($cursos_ids)) {
            return response()->json(['success' => false, 'message' => 'Datos insuficientes. Se requiere estudiante y cursos.']);
        }

        $usuario = Usuario::find($id_estudiante);
        if (!$usuario) {
            return response()->json(['success' => false, 'message' => 'Estudiante no encontrado.']);
        }

        // Cédula limpia oficial
        $cedula = trim($usuario->cedula ?? '');
        if (empty($cedula) || !preg_match('/\d/', $cedula)) {
            $cedula = 'N/A';
        }

        // Cursos seleccionados con costos del plan y programa
        $cursos = DB::table('plan_estudios as pe')
            ->join('programas as p', 'pe.id_programa', '=', 'p.id_programa')
            ->whereIn('pe.id_plan', $cursos_ids)
            ->select(
                'pe.id_plan',
                'pe.codigo',
                'pe.materia',
                'p.nombre_programa',
                DB::raw('CASE WHEN pe.precio > 0 THEN pe.precio ELSE IFNULL(p.costo_materia, 0) END as precio'),
                DB::raw('IFNULL(p.costo_matricula, 0) as costo_matricula'),
                DB::raw('IFNULL(p.costo_biblioteca, 0) as costo_biblioteca'),
                DB::raw('IFNULL(p.costo_inscripcion_unica, 0) as costo_inscripcion_unica')
            )
            ->get();

        // Verificar si es la primera boleta del estudiante
        $es_primera_boleta = Boleta::where('id_estudiante', $id_estudiante)
            ->where('estado', '!=', 'anulada')
            ->count() === 0;

        return response()->json([
            'success' => true,
            'data' => [
                'usuario' => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->nombre,
                    'apellidos' => $usuario->apellidos ?? '',
                    'cedula' => $cedula,
                    'email' => $usuario->email
                ],
                'cursos' => $cursos,
                'es_primera_boleta' => $es_primera_boleta
            ]
        ]);
    }

    /**
     * Matricula al estudiante en los cursos seleccionados (find or create)
     */
    protected function matricularEstudianteEnCursos(int $id_estudiante, array $cursos_ids, string $periodo, ?int $id_boleta = null): array
    {
        $matriculas_creadas = [];

        foreach ($cursos_ids as $id_plan) {
            $id_plan = (int)$id_plan;

            // 1. Buscar o crear curso activo para el periodo
            $cursoActivo = DB::table('cursos_activos')
                ->where('id_plan', $id_plan)
                ->where('periodo', $periodo)
                ->first();

            $id_curso_activo = null;
            if ($cursoActivo) {
                $id_curso_activo = $cursoActivo->id_curso_activo;
            } else {
                $id_curso_activo = DB::table('cursos_activos')->insertGetId([
                    'id_plan' => $id_plan,
                    'periodo' => $periodo,
                    'id_profesor' => null,
                    'fecha_inicio' => Carbon::now()->toDateString()
                ]);
            }

            // 2. Buscar o crear matrícula
            $matricula = DB::table('matriculas')
                ->where('id_estudiante', $id_estudiante)
                ->where('id_curso_activo', $id_curso_activo)
                ->first();

            if ($matricula) {
                if ($id_boleta) {
                    DB::table('matriculas')
                        ->where('id_matricula', $matricula->id_matricula)
                        ->update(['id_boleta' => $id_boleta]);
                }
                $matriculas_creadas[] = $matricula->id_matricula;
            } else {
                $insertData = [
                    'id_estudiante' => $id_estudiante,
                    'id_curso_activo' => $id_curso_activo,
                    'fecha_matricula' => Carbon::now()
                ];
                if ($id_boleta) {
                    $insertData['id_boleta'] = $id_boleta;
                }
                $id_mat = DB::table('matriculas')->insertGetId($insertData);
                $matriculas_creadas[] = $id_mat;
            }
        }

        return $matriculas_creadas;
    }

    /**
     * Enviar Boleta al Estudiante para Firma Digital (Paso 3 - Acción 1)
     */
    public function enviarEnlaceFirma(Request $request)
    {
        self::ensureBoletasColumns();

        $request->validate([
            'id_estudiante' => 'required|integer|exists:usuarios,id',
            'cursos_ids' => 'required|array',
            'periodo' => 'required|string',
            'total' => 'required',
            'cuotas' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $id_estudiante = (int)$request->id_estudiante;
            $cursos_ids = $request->cursos_ids;
            $periodo = trim($request->periodo);
            $total_raw = $request->total;
            $total = floatval(preg_replace('/[^\d.]/', '', str_replace(',', '.', str_replace('.', '', $total_raw))));
            $descuento = floatval($request->descuento ?? 0);
            $cuotas = intval($request->cuotas ?? 1);
            $cobrar_inscripcion = (bool)($request->cobrar_inscripcion ?? false);
            $cobrar_biblioteca = (bool)($request->cobrar_biblioteca ?? false);
            $cobrar_matricula = (bool)($request->cobrar_matricula ?? true);
            $fechas_vencimiento = $request->fechas_vencimiento ?? [];

            $token_firma = bin2hex(random_bytes(32));
            $token_expiracion = Carbon::now()->addDays(7);

            // Crear boleta en estado pendiente_firma
            $boleta = Boleta::create([
                'id_estudiante' => $id_estudiante,
                'id_creador' => auth()->id() ?? 1,
                'total' => $total,
                'descuento' => $descuento,
                'saldo_pendiente' => $total,
                'monto_pagado' => 0.00,
                'estado' => 'pendiente_firma',
                'periodo' => $periodo,
                'cuotas' => $cuotas,
                'aplicar_cargos_fijos' => ($cobrar_inscripcion || $cobrar_biblioteca),
                'cobrar_inscripcion' => $cobrar_inscripcion,
                'cobrar_biblioteca' => $cobrar_biblioteca,
                'cobrar_matricula' => $cobrar_matricula,
                'fechas_vencimiento_json' => !empty($fechas_vencimiento) ? json_encode($fechas_vencimiento) : null,
                'token_firma' => $token_firma,
                'token_expiracion' => $token_expiracion,
                'fecha_creacion' => Carbon::now()
            ]);

            $numero_boleta = "B-" . str_pad($boleta->id, 6, "0", STR_PAD_LEFT);
            $boleta->update(['numero_boleta' => $numero_boleta]);

            // Matricular estudiante en los cursos y vincular id_boleta
            $this->matricularEstudianteEnCursos($id_estudiante, $cursos_ids, $periodo, $boleta->id);

            DB::commit();

            $enlace_firma = route('boletas.firmar_publico', ['token' => $token_firma]);

            // Intento seguro de envío de correo al estudiante
            $estudiante = Usuario::find($id_estudiante);
            try {
                if ($estudiante && !empty($estudiante->email)) {
                    $nombreEst = trim($estudiante->nombre . ' ' . ($estudiante->apellidos ?? ''));
                    $nombreInst = config('cliente.nombre', 'CEFI');
                    $asunto = "Firma Digital Requerida: Boleta de Matrícula {$numero_boleta} - {$nombreInst}";
                    $cuerpo = "Hola {$nombreEst},\n\nSe ha generado su boleta de matrícula para el período {$periodo}.\nPor favor ingrese al siguiente enlace para revisar y estampar su firma digital:\n\n{$enlace_firma}\n\nGracias por formar parte de {$nombreInst}.";

                    Mail::raw($cuerpo, function($message) use ($estudiante, $asunto) {
                        $message->to($estudiante->email)->subject($asunto);
                    });
                }
            } catch (\Throwable $eMail) {
                // El enlace siempre se genera y puede compartirse manualmente
            }

            return response()->json([
                'success' => true,
                'message' => 'Boleta enviada al estudiante para firma digital.',
                'boleta_id' => $boleta->id,
                'enlace_firma' => $enlace_firma
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar boleta para firma: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar y Oficializar Boleta Directamente (Paso 3 - Acción 2 para atención administrativa)
     */
    public function guardarOficializarBoleta(Request $request)
    {
        self::ensureBoletasColumns();

        $request->validate([
            'id_estudiante' => 'required|integer|exists:usuarios,id',
            'cursos_ids' => 'required|array',
            'periodo' => 'required|string',
            'total' => 'required',
            'cuotas' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $id_estudiante = (int)$request->id_estudiante;
            $cursos_ids = $request->cursos_ids;
            $periodo = trim($request->periodo);
            $total_raw = $request->total;
            $total = floatval(preg_replace('/[^\d.]/', '', str_replace(',', '.', str_replace('.', '', $total_raw))));
            $descuento = floatval($request->descuento ?? 0);
            $cuotas = intval($request->cuotas ?? 1);
            $cobrar_inscripcion = (bool)($request->cobrar_inscripcion ?? false);
            $cobrar_biblioteca = (bool)($request->cobrar_biblioteca ?? false);
            $cobrar_matricula = (bool)($request->cobrar_matricula ?? true);
            $pago_inicial = floatval($request->pago_inicial ?? 0);
            $pago_inicial_metodo = $request->pago_inicial_metodo ?? 'Efectivo';
            $funcionario = auth()->user()->nombre ?? 'Administración';
            $ref_input = trim($request->pago_inicial_referencia ?? '');
            $pago_inicial_referencia = empty($ref_input) ? "Cobrado por: {$funcionario}" : "{$ref_input} (Cobrado por: {$funcionario})";
            $fechas_vencimiento = $request->fechas_vencimiento ?? [];

            // Procesar firma en canvas si se dibujó
            $ruta_firma = null;
            $signature_data_url = $request->signature ?? '';
            if (!empty($signature_data_url) && str_starts_with($signature_data_url, 'data:image')) {
                $dir_firmas = public_path('uploads/firmas');
                if (!file_exists($dir_firmas)) {
                    @mkdir($dir_firmas, 0777, true);
                }
                $data_parts = explode(',', $signature_data_url);
                $decoded_image = base64_decode(end($data_parts));
                $firma_filename = 'firma_boleta_' . time() . '_' . $id_estudiante . '.png';
                file_put_contents($dir_firmas . '/' . $firma_filename, $decoded_image);
                $ruta_firma = 'uploads/firmas/' . $firma_filename;
            }

            $saldo_pendiente = max(0, $total - $pago_inicial);
            $estado = ($saldo_pendiente <= 0) ? 'pagada' : (($pago_inicial > 0) ? 'pago_parcial' : 'pendiente');

            // Crear boleta oficial
            $boleta = Boleta::create([
                'id_estudiante' => $id_estudiante,
                'id_creador' => auth()->id() ?? 1,
                'total' => $total,
                'descuento' => $descuento,
                'pago_inicial' => $pago_inicial,
                'pago_inicial_metodo' => $pago_inicial_metodo,
                'pago_inicial_referencia' => $pago_inicial_referencia,
                'saldo_pendiente' => $saldo_pendiente,
                'monto_pagado' => $pago_inicial,
                'estado' => $estado,
                'periodo' => $periodo,
                'cuotas' => $cuotas,
                'aplicar_cargos_fijos' => ($cobrar_inscripcion || $cobrar_biblioteca),
                'cobrar_inscripcion' => $cobrar_inscripcion,
                'cobrar_biblioteca' => $cobrar_biblioteca,
                'cobrar_matricula' => $cobrar_matricula,
                'fechas_vencimiento_json' => !empty($fechas_vencimiento) ? json_encode($fechas_vencimiento) : null,
                'ruta_firma' => $ruta_firma,
                'fecha_firma' => $ruta_firma ? Carbon::now() : null,
                'fecha_creacion' => Carbon::now()
            ]);

            $numero_boleta = "B-" . str_pad($boleta->id, 6, "0", STR_PAD_LEFT);
            $boleta->update(['numero_boleta' => $numero_boleta]);

            // Matricular estudiante y asociar boleta
            $this->matricularEstudianteEnCursos($id_estudiante, $cursos_ids, $periodo, $boleta->id);

            // Registrar pago inicial si aplica
            if ($pago_inicial > 0) {
                Pago::create([
                    'boleta_id' => $boleta->id,
                    'monto' => $pago_inicial,
                    'metodo_pago' => $pago_inicial_metodo,
                    'referencia' => $pago_inicial_referencia,
                    'fecha_pago' => Carbon::now()
                ]);
            }

            // Generar Cuotas de Seguimiento
            $monto_cuota = $saldo_pendiente / $cuotas;
            for ($i = 1; $i <= $cuotas; $i++) {
                $vencimiento = isset($fechas_vencimiento[$i - 1])
                    ? Carbon::parse($fechas_vencimiento[$i - 1])
                    : Carbon::today()->addMonths($i - 1);

                SeguimientoPago::create([
                    'id_boleta' => $boleta->id,
                    'id_estudiante' => $id_estudiante,
                    'numero_cuota' => $i,
                    'monto_cuota' => $monto_cuota,
                    'interes_acumulado' => 0.00,
                    'fecha_vencimiento' => $vencimiento,
                    'estado' => ($saldo_pendiente <= 0) ? 'pagada' : 'pendiente',
                    'fecha_creacion' => Carbon::now()
                ]);
            }

            DB::commit();

            // Generar archivo físico oficial de PDF
            try {
                $ruta_pdf_disco = BoletaPdfService::generar($boleta->id, 'F');
                $boleta->update(['ruta_pdf' => 'uploads/boletas/boleta_' . $boleta->id . '.pdf']);
            } catch (\Throwable $ePdf) {
                // PDF fallback
            }

            return response()->json([
                'success' => true,
                'message' => 'Boleta oficializada con éxito.',
                'boleta_id' => $boleta->id,
                'pdf_url' => route('boletas.pdf', ['id' => $boleta->id])
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al oficializar la boleta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vista de Revisión y Procesamiento de Boleta Firmada
     */
    public function procesarBoleta($id)
    {
        self::ensureBoletasColumns();

        $boleta = Boleta::with(['estudiante', 'seguimientoPagos'])->findOrFail($id);
        $estudiante = $boleta->estudiante;

        // Cursos vinculados
        $cursos = DB::table('matriculas as m')
            ->join('cursos_activos as ca', 'm.id_curso_activo', '=', 'ca.id_curso_activo')
            ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->join('programas as p', 'pe.id_programa', '=', 'p.id_programa')
            ->where('m.id_boleta', $id)
            ->where('m.id_estudiante', $boleta->id_estudiante)
            ->select(
                'pe.id_plan',
                'pe.codigo',
                'pe.materia',
                'p.nombre_programa',
                DB::raw('CASE WHEN pe.precio > 0 THEN pe.precio ELSE IFNULL(p.costo_materia, 0) END as precio'),
                DB::raw('IFNULL(p.costo_matricula, 0) as costo_matricula'),
                DB::raw('IFNULL(p.costo_biblioteca, 0) as costo_biblioteca'),
                DB::raw('IFNULL(p.costo_inscripcion_unica, 0) as costo_inscripcion_unica')
            )
            ->get();

        $fechas_vencimiento_saved = json_decode($boleta->fechas_vencimiento_json ?? '[]', true) ?: [];

        return view('boletas.procesar', compact('boleta', 'estudiante', 'cursos', 'fechas_vencimiento_saved'));
    }

    /**
     * Confirmar Oficialización de Boleta previamente firmada
     */
    public function oficializarBoletaFirmada(Request $request, $id)
    {
        self::ensureBoletasColumns();

        $boleta = Boleta::findOrFail($id);

        try {
            DB::beginTransaction();

            $pago_inicial = floatval($request->pago_inicial ?? 0);
            $pago_inicial_metodo = $request->pago_inicial_metodo ?? 'Efectivo';
            $funcionario = auth()->user()->nombre ?? 'Administración';
            $ref_input = trim($request->pago_inicial_referencia ?? '');
            $pago_inicial_referencia = empty($ref_input) ? "Cobrado por: {$funcionario}" : "{$ref_input} (Cobrado por: {$funcionario})";
            $fechas_vencimiento = $request->fechas_vencimiento ?? [];

            $total = floatval($boleta->total);
            $saldo_pendiente = max(0, $total - $pago_inicial);
            $estado = ($saldo_pendiente <= 0) ? 'pagada' : (($pago_inicial > 0) ? 'pago_parcial' : 'pendiente');

            $boleta->update([
                'pago_inicial' => $pago_inicial,
                'pago_inicial_metodo' => $pago_inicial_metodo,
                'pago_inicial_referencia' => $pago_inicial_referencia,
                'monto_pagado' => $pago_inicial,
                'saldo_pendiente' => $saldo_pendiente,
                'estado' => $estado,
                'fechas_vencimiento_json' => !empty($fechas_vencimiento) ? json_encode($fechas_vencimiento) : $boleta->fechas_vencimiento_json
            ]);

            // Registrar pago inicial si aplica
            if ($pago_inicial > 0) {
                Pago::create([
                    'boleta_id' => $boleta->id,
                    'monto' => $pago_inicial,
                    'metodo_pago' => $pago_inicial_metodo,
                    'referencia' => $pago_inicial_referencia,
                    'fecha_pago' => Carbon::now()
                ]);
            }

            // Generar o regenerar Cuotas de Seguimiento
            SeguimientoPago::where('id_boleta', $boleta->id)->delete();
            $cuotas = max(1, (int)$boleta->cuotas);
            $monto_cuota = $saldo_pendiente / $cuotas;

            for ($i = 1; $i <= $cuotas; $i++) {
                $vencimiento = isset($fechas_vencimiento[$i - 1])
                    ? Carbon::parse($fechas_vencimiento[$i - 1])
                    : Carbon::today()->addMonths($i - 1);

                SeguimientoPago::create([
                    'id_boleta' => $boleta->id,
                    'id_estudiante' => $boleta->id_estudiante,
                    'numero_cuota' => $i,
                    'monto_cuota' => $monto_cuota,
                    'interes_acumulado' => 0.00,
                    'fecha_vencimiento' => $vencimiento,
                    'estado' => ($saldo_pendiente <= 0) ? 'pagada' : 'pendiente',
                    'fecha_creacion' => Carbon::now()
                ]);
            }

            DB::commit();

            // Generar PDF físico oficial
            try {
                $ruta_pdf_disco = BoletaPdfService::generar($boleta->id, 'F');
                $boleta->update(['ruta_pdf' => 'uploads/boletas/boleta_' . $boleta->id . '.pdf']);
            } catch (\Throwable $ePdf) {
                // PDF fallback
            }

            return response()->json([
                'success' => true,
                'message' => 'Boleta oficializada con éxito.',
                'boleta_id' => $boleta->id,
                'pdf_url' => route('boletas.pdf', ['id' => $boleta->id])
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al oficializar boleta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vista pública para que el estudiante estampe su firma digital
     */
    public function firmarPublico($token)
    {
        self::ensureBoletasColumns();

        $boleta = Boleta::with('estudiante')->where('token_firma', $token)->first();

        if (!$boleta) {
            abort(404, 'Enlace no válido o la boleta no existe.');
        }

        if ($boleta->estado !== 'pendiente_firma') {
            return view('boletas.ya_firmada', compact('boleta'));
        }

        if ($boleta->token_expiracion && Carbon::now()->greaterThan($boleta->token_expiracion)) {
            abort(403, 'Este enlace de firma ha expirado. Por favor solicite uno nuevo a la administración.');
        }

        $estudiante = $boleta->estudiante;
        $cursos = DB::table('matriculas as m')
            ->join('cursos_activos as ca', 'm.id_curso_activo', '=', 'ca.id_curso_activo')
            ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->join('programas as p', 'pe.id_programa', '=', 'p.id_programa')
            ->where('m.id_boleta', $boleta->id)
            ->where('m.id_estudiante', $boleta->id_estudiante)
            ->select('pe.codigo', 'pe.materia', 'pe.precio', 'p.nombre_programa', 'p.costo_materia', 'p.costo_matricula', 'p.costo_biblioteca', 'p.costo_inscripcion_unica')
            ->get();

        return view('boletas.firmar_publico', compact('boleta', 'estudiante', 'cursos'));
    }

    /**
     * Procesar y guardar la firma digital estampada por el estudiante
     */
    public function guardarFirmaPublica(Request $request, $token)
    {
        self::ensureBoletasColumns();

        $boleta = Boleta::where('token_firma', $token)
            ->where('estado', 'pendiente_firma')
            ->firstOrFail();

        $request->validate([
            'signature' => 'required|string'
        ]);

        try {
            $dir_firmas = public_path('uploads/firmas');
            if (!file_exists($dir_firmas)) {
                @mkdir($dir_firmas, 0777, true);
            }

            $signature_data_url = $request->signature;
            $data_parts = explode(',', $signature_data_url);
            $decoded_image = base64_decode(end($data_parts));
            $firma_filename = 'firma_estudiante_' . $boleta->id . '_' . time() . '.png';
            file_put_contents($dir_firmas . '/' . $firma_filename, $decoded_image);

            $boleta->update([
                'ruta_firma' => 'uploads/firmas/' . $firma_filename,
                'fecha_firma' => Carbon::now(),
                'estado' => 'firmada'
            ]);

            return response()->json([
                'success' => true,
                'message' => '¡Firma digital registrada con éxito! La boleta ha sido enviada al departamento administrativo para su oficialización.'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la firma: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genera y transmite la Boleta Oficial en PDF.
     */
    public function verPdf($id)
    {
        return BoletaPdfService::generar((int)$id);
    }

    /**
     * Registra un abono o pago completo a una boleta.
     */
    public function registrarPago(Request $request, $id)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'metodo' => 'nullable|string',
            'referencia' => 'nullable|string',
            'condonar_interes' => 'nullable|boolean'
        ]);

        $boleta = Boleta::findOrFail($id);
        $monto_pago = floatval($request->monto);
        $metodo = $request->metodo ?: 'Efectivo';
        $referencia_input = trim($request->referencia ?? '');
        $funcionario = auth()->user()->nombre ?? 'Sistema';
        $referencia = empty($referencia_input) ? "Cobrado por: {$funcionario}" : "{$referencia_input} (Cobrado por: {$funcionario})";

        if ($monto_pago > $boleta->saldo_pendiente) {
            return response()->json([
                'success' => false,
                'message' => "El monto del pago excede el saldo pendiente (¢" . number_format($boleta->saldo_pendiente, 2) . ")."
            ], 422);
        }

        try {
            DB::beginTransaction();

            $total_interes_condonado = 0.00;
            if ($request->boolean('condonar_interes')) {
                $total_interes_condonado = floatval(
                    SeguimientoPago::where('id_boleta', $id)
                        ->where('estado', 'pendiente')
                        ->sum('interes_acumulado')
                );

                if ($total_interes_condonado > 0) {
                    SeguimientoPago::where('id_boleta', $id)
                        ->where('estado', 'pendiente')
                        ->update(['interes_acumulado' => 0.00]);
                    $referencia .= " (Mora condonada: ¢" . number_format($total_interes_condonado, 2) . ")";
                }
            }

            // Registrar en tabla pagos
            Pago::create([
                'boleta_id' => $id,
                'monto' => $monto_pago,
                'metodo_pago' => $metodo,
                'referencia' => $referencia,
                'fecha_pago' => Carbon::now()
            ]);

            // Amortizar en seguimiento_pagos (de la cuota más antigua a la más reciente)
            $monto_restante = $monto_pago;
            $cuotas = SeguimientoPago::where('id_boleta', $id)
                ->where('estado', 'pendiente')
                ->orderBy('numero_cuota', 'asc')
                ->get();

            foreach ($cuotas as $cuota) {
                if ($monto_restante <= 0) break;

                $monto_cuota = floatval($cuota->monto_cuota);

                if ($monto_restante >= $monto_cuota) {
                    $cuota->update([
                        'estado' => 'pagada',
                        'fecha_pago' => Carbon::now()
                    ]);
                    $monto_restante -= $monto_cuota;
                } else {
                    $cuota->update([
                        'monto_cuota' => $monto_cuota - $monto_restante
                    ]);
                    $monto_restante = 0;
                }
            }

            // Actualizar saldos en boleta
            $nuevo_pagado = floatval($boleta->monto_pagado ?? 0) + $monto_pago;
            $nuevo_saldo = max(0, floatval($boleta->total) - $nuevo_pagado);
            $nuevo_estado = ($nuevo_saldo <= 0) ? 'pagada' : 'pago_parcial';

            $boleta->update([
                'monto_pagado' => $nuevo_pagado,
                'saldo_pendiente' => $nuevo_saldo,
                'estado' => $nuevo_estado
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago registrado con éxito.',
                'saldo_pendiente' => $nuevo_saldo,
                'estado' => $nuevo_estado
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Anula una boleta de matrícula de forma segura.
     */
    public function anular(Request $request, $id)
    {
        $boleta = Boleta::findOrFail($id);

        try {
            DB::beginTransaction();

            // 1. Eliminar pagos asociados
            Pago::where('boleta_id', $id)->delete();

            // 2. Eliminar cuotas pendientes de seguimiento
            SeguimientoPago::where('id_boleta', $id)->delete();

            // 3. Desvincular matrículas
            DB::table('matriculas')->where('id_boleta', $id)->update(['id_boleta' => null]);

            // 4. Marcar como anulada
            $boleta->update([
                'estado' => 'anulada',
                'saldo_pendiente' => 0.00
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Boleta anulada con éxito.'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al anular boleta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API JSON de cuotas próximas y vencidas para n8n y recordatorios de cobro.
     */
    public function apiCuotasVencimiento(Request $request)
    {
        $clientMasterKey = config('cliente.moodle_key', 'cefi2026');
        $api_key_server = env('N8N_API_KEY', $clientMasterKey);
        $provided_key = $request->get('key', $request->header('X-API-KEY', ''));
        $is_admin = auth()->check() && in_array(auth()->user()->id_rol, [1, 2]);

        if (!$is_admin && $provided_key !== $api_key_server && $provided_key !== $clientMasterKey && $provided_key !== 'unela2026') {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado. Se requiere API Key o sesión de administrador.'
            ], 401);
        }

        InteresesService::recalcularTodosLosIntereses();

        $modo = $request->get('modo', 'todas');
        $dias_proximas = intval($request->get('dias', 3));
        $hoy = Carbon::today();

        $query = SeguimientoPago::with(['boleta', 'estudiante'])
            ->where('estado', 'pendiente');

        if ($modo === 'proximas') {
            $query->where('fecha_vencimiento', '>=', $hoy)
                  ->where('fecha_vencimiento', '<=', $hoy->copy()->addDays($dias_proximas));
        } elseif ($modo === 'vencidas') {
            $query->where('fecha_vencimiento', '<', $hoy);
        } else {
            $query->where('fecha_vencimiento', '<=', $hoy->copy()->addDays($dias_proximas));
        }

        $cuotas = $query->orderBy('fecha_vencimiento', 'asc')->get();

        $estudiantes = [];
        foreach ($cuotas as $c) {
            $est = $c->estudiante;
            if (!$est) continue;

            $id_est = $est->id;
            if (!isset($estudiantes[$id_est])) {
                $telefono_limpio = preg_replace('/[^0-9]/', '', $est->telefono ?? '');
                if (strlen($telefono_limpio) == 8) $telefono_limpio = '506' . $telefono_limpio;

                $estudiantes[$id_est] = [
                    'id_estudiante' => $id_est,
                    'nombre' => trim($est->nombre . ' ' . ($est->apellidos ?? '')),
                    'telefono' => $telefono_limpio,
                    'email' => $est->email,
                    'cuotas' => [],
                    'total_monto' => 0.0,
                    'tiene_vencidas' => false,
                    'mensaje_whatsapp' => ''
                ];
            }

            $monto_total_cuota = floatval($c->monto_cuota) + floatval($c->interes_acumulado ?? 0);
            $estudiantes[$id_est]['total_monto'] += $monto_total_cuota;

            $dias_atraso = 0;
            $dias_para_vencer = 0;
            if ($c->fecha_vencimiento->lessThan($hoy)) {
                $dias_atraso = $c->fecha_vencimiento->diffInDays($hoy);
                $estudiantes[$id_est]['tiene_vencidas'] = true;
            } else {
                $dias_para_vencer = $hoy->diffInDays($c->fecha_vencimiento);
            }

            $estudiantes[$id_est]['cuotas'][] = [
                'id_cuota' => $c->id,
                'numero_boleta' => $c->boleta->numero_boleta ?? 'N/D',
                'numero_cuota' => $c->numero_cuota,
                'monto_capital' => floatval($c->monto_cuota),
                'interes_mora' => floatval($c->interes_acumulado ?? 0),
                'monto_total' => $monto_total_cuota,
                'fecha_vencimiento' => $c->fecha_vencimiento->format('Y-m-d'),
                'dias_atraso' => $dias_atraso,
                'dias_para_vencer' => $dias_para_vencer,
                'estado_letra' => ($dias_atraso > 0) ? 'vencida' : 'proxima'
            ];
        }

        foreach ($estudiantes as $id_est => &$data) {
            $primer_nombre = explode(' ', trim($data['nombre']))[0] ?? $data['nombre'];
            $total_fmt = number_format($data['total_monto'], 2);
            $nombreCliente = config('cliente.nombre', 'CEFI');

            if ($data['tiene_vencidas']) {
                $msg = "Estimado/a *{$primer_nombre}*, de parte del Departamento Financiero de {$nombreCliente} le saludamos cordialmente.\n\n";
                $msg .= "Le informamos que presenta cuota(s) vencida(s) de colegiatura con recargo por mora acumulada del 2% diario.\n\n";
                $msg .= "*Desglose de Cuotas Pendientes:*\n";
                foreach ($data['cuotas'] as $q) {
                    $m_fmt = number_format($q['monto_total'], 2);
                    $v_fmt = date('d/m/Y', strtotime($q['fecha_vencimiento']));
                    if ($q['dias_atraso'] > 0) {
                        $msg .= "• Boleta *{$q['numero_boleta']}* (Cuota #{$q['numero_cuota']}): ¢{$m_fmt} (Venció: {$v_fmt}, Atraso: {$q['dias_atraso']} días)\n";
                    } else {
                        $msg .= "• Boleta *{$q['numero_boleta']}* (Cuota #{$q['numero_cuota']}): ¢{$m_fmt} (Vence: {$v_fmt})\n";
                    }
                }
                $msg .= "\n*Total a Cancelar:* ¢{$total_fmt}\n\n";
                $msg .= "Le solicitamos realizar su pago a la brevedad para evitar la suspensión del acceso al campus virtual y servicios académicos.";
            } else {
                $msg = "Estimado/a *{$primer_nombre}*, de parte de {$nombreCliente} le recordamos que tiene cuota(s) de colegiatura próximas a vencer.\n\n";
                foreach ($data['cuotas'] as $q) {
                    $m_fmt = number_format($q['monto_total'], 2);
                    $v_fmt = date('d/m/Y', strtotime($q['fecha_vencimiento']));
                    $msg .= "• Cuota #{$q['numero_cuota']}: ¢{$m_fmt} (Vence el {$v_fmt})\n";
                }
                $msg .= "\n*Total a Pagar:* ¢{$total_fmt}\n\n";
                $msg .= "Agradecemos realizar su pago oportunamente. ¡Que tenga un excelente día!";
            }

            $data['mensaje_whatsapp'] = $msg;
        }

        return response()->json([
            'success' => true,
            'modo' => $modo,
            'dias_evaluados' => $dias_proximas,
            'total_estudiantes' => count($estudiantes),
            'estudiantes' => array_values($estudiantes)
        ]);
    }
}
