<?php

namespace App\Http\Controllers;

use App\Models\Boleta;
use App\Models\Usuario;
use App\Models\SeguimientoPago;
use App\Models\Pago;
use App\Models\ArregloPago;
use App\Models\Programa;
use App\Services\InteresesService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BoletaController extends Controller
{
    /**
     * Historial de Boletas
     */
    public function index(Request $request)
    {
        $query = Boleta::with('estudiante');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_boleta', 'like', "%{$search}%")
                  ->orWhereHas('estudiante', function($sq) use ($search) {
                      $sq->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $boletas = $query->orderBy('fecha_creacion', 'desc')->paginate(15)->withQueryString();

        return view('boletas.index', compact('boletas'));
    }

    /**
     * Control de Morosidad
     */
    public function morosidad(Request $request)
    {
        // Recalcular intereses compuestos antes de renderizar
        InteresesService::recalcularTodosLosIntereses();

        // Obtener cuotas vencidas y pendientes
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
        $term = $request->get('term');
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $estudiantes = Usuario::where(function($q) use ($term) {
            $q->where('nombre', 'like', "%{$term}%")
              ->orWhere('apellidos', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('cedula', 'like', "%{$term}%");
        })->take(5)->get(['id', 'nombre', 'apellidos', 'email']);

        return response()->json($estudiantes);
    }

    /**
     * Estado de Cuenta - Obtener datos del estudiante vía AJAX
     */
    public function getEstadoCuentaAjax($id)
    {
        // Recalcular intereses antes de mostrar
        InteresesService::recalcularInteresesEstudiante($id);

        $estudiante = Usuario::find($id);
        if (!$estudiante) {
            return response()->json(['error' => 'Estudiante no encontrado.'], 404);
        }

        // Obtener Boletas
        $boletas = Boleta::where('id_estudiante', $id)
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        // Obtener Pagos
        $pagos = Pago::whereIn('boleta_id', $boletas->pluck('id'))
            ->orderBy('fecha_pago', 'desc')
            ->get();

        // Obtener Cuotas / Letras a Pagar
        $letras = SeguimientoPago::where('id_estudiante', $id)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        // Obtener Arreglos de Pago
        $arreglos = ArregloPago::where('estudiante_id', $id)
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        // Calcular Deuda Total (Capital pendiente + Interés acumulado de cuotas pendientes)
        $deudaTotal = SeguimientoPago::where('id_estudiante', $id)
            ->where('estado', 'pendiente')
            ->selectRaw('SUM(monto_cuota) as capital, SUM(interes_acumulado) as interes')
            ->first();

        $totalDeuda = floatval($deudaTotal->capital ?? 0) + floatval($deudaTotal->interes ?? 0);

        return response()->json([
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
                    'pdf_url' => route('boletas.pdf', ['id' => $b->id])
                ];
            }),
            'pagos' => $pagos->map(function($p) {
                return [
                    'id' => $p->id,
                    'boleta_id' => $p->boleta_id,
                    'fecha_pago' => $p->fecha_pago ? Carbon::parse($p->fecha_pago)->format('Y-m-d H:i') : 'N/D',
                    'monto' => floatval($p->monto),
                    'metodo_pago' => $p->metodo_pago ?? 'N/D',
                    'referencia' => $p->referencia ?? 'N/D',
                    'ruta_comprobante' => $p->ruta_comprobante
                ];
            }),
            'letras' => $letras->map(function($letra) {
                return [
                    'numero_cuota' => $letra->numero_cuota,
                    'monto_cuota' => floatval($letra->monto_cuota),
                    'interes_acumulado' => floatval($letra->interes_acumulado ?? 0),
                    'fecha_vencimiento' => $letra->fecha_vencimiento ? Carbon::parse($letra->fecha_vencimiento)->format('Y-m-d') : 'N/D',
                    'estado' => $letra->estado,
                    'boleta_numero' => $letra->boleta->numero_boleta ?? 'N/D'
                ];
            }),
            'arreglos' => $arreglos
        ]);
    }

    /**
     * Generar Boleta de Pago - Formulario
     */
    public function generar(Request $request)
    {
        $programas = Programa::orderBy('categoria', 'asc')
            ->orderBy('nombre_programa', 'asc')
            ->get();

        $programas_agrupados = $programas->groupBy(function($item) {
            return $item->categoria ?: 'General';
        });

        // Valores autocompletados desde parámetros
        $id_programa_auto = $request->get('id_programa_auto', 0);
        $id_estudiante_auto = $request->get('id_estudiante', 0);
        $student_name_auto = '';
        $student_email_auto = '';

        if ($id_estudiante_auto) {
            $est = Usuario::find($id_estudiante_auto);
            if ($est) {
                $student_name_auto = $est->nombre . ' ' . $est->apellidos;
                $student_email_auto = $est->email;
            }
        }

        return view('boletas.create', compact(
            'programas_agrupados',
            'id_programa_auto',
            'id_estudiante_auto',
            'student_name_auto',
            'student_email_auto'
        ));
    }

    /**
     * Obtiene los cursos asociados a un programa en formato JSON para el generador.
     */
    public function getCursosPorProgramaAjax($id)
    {
        $programa = Programa::find($id);
        if (!$programa) {
            return response()->json(['success' => false, 'message' => 'Programa no encontrado.']);
        }

        // Obtener cursos / materias del plan de estudios con su precio correspondiente
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
     * Almacena una nueva boleta generada y procesa matrícula y cuotas (simplificado para local).
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_estudiante' => 'required|exists:usuarios,id',
            'cursos_ids' => 'required|array',
            'periodo' => 'required|string',
            'total' => 'required|numeric',
            'cuotas' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $id_estudiante = $request->id_estudiante;
            $cursos_ids = $request->cursos_ids;
            $periodo = $request->periodo;
            $total = $request->total;
            $cuotas = $request->cuotas;
            $descuento = floatval($request->descuento ?? 0);
            $aplicar_cargos_fijos = intval($request->aplicar_cargos_fijos ?? 0);

            // Crear Boleta
            $boleta = Boleta::create([
                'id_estudiante' => $id_estudiante,
                'id_creador' => auth()->id() ?? 1,
                'total' => $total,
                'saldo_pendiente' => $total,
                'estado' => 'pendiente',
                'periodo' => $periodo,
                'cuotas' => $cuotas,
                'aplicar_cargos_fijos' => $aplicar_cargos_fijos,
                'fecha_creacion' => Carbon::now()
            ]);

            // Formatear número de boleta
            $numero_boleta = "B-" . str_pad($boleta->id, 6, "0", STR_PAD_LEFT);
            $boleta->update(['numero_boleta' => $numero_boleta]);

            // Generar Cuotas de Seguimiento de Pagos
            $fechas_vencimiento = $request->fechas_vencimiento ?? [];
            $monto_cuota = $total / $cuotas;

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
                    'estado' => 'pendiente',
                    'fecha_creacion' => Carbon::now()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Boleta generada con éxito.',
                'boleta_id' => $boleta->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al generar la boleta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genera y transmite la Boleta Oficial en PDF.
     */
    public function verPdf($id)
    {
        return \App\Services\BoletaPdfService::generar((int)$id);
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
        $referencia = empty($referencia_input) ? "Cobrado por: $funcionario" : "$referencia_input (Cobrado por: $funcionario)";

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

            // Distribuir el pago entre las cuotas pendientes
            $remanente = $monto_pago;
            $cuotasPendientes = SeguimientoPago::where('id_boleta', $id)
                ->where('estado', 'pendiente')
                ->orderBy('numero_cuota', 'asc')
                ->get();

            foreach ($cuotasPendientes as $cuota) {
                if ($remanente <= 0) break;

                $totalCuota = floatval($cuota->monto_cuota) + floatval($cuota->interes_acumulado);

                if ($remanente >= $totalCuota) {
                    $cuota->update([
                        'monto_cuota' => 0,
                        'interes_acumulado' => 0,
                        'estado' => 'pagada',
                        'fecha_pago' => Carbon::now()
                    ]);
                    $remanente -= $totalCuota;
                } else {
                    $nuevoMonto = max(0, $totalCuota - $remanente);
                    $cuota->update([
                        'monto_cuota' => $nuevoMonto,
                        'interes_acumulado' => 0
                    ]);
                    $remanente = 0;
                }
            }

            // Actualizar totales de la boleta
            InteresesService::actualizarTotalesBoleta($id);
            $boleta->refresh();
            $boleta->update([
                'monto_pagado' => floatval($boleta->monto_pagado) + $monto_pago
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago de ¢' . number_format($monto_pago, 2) . ' registrado exitosamente.',
                'saldo_restante' => $boleta->saldo_pendiente,
                'estado' => $boleta->estado
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

        // Recalcular intereses antes de listar
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
