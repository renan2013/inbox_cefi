<?php

namespace App\Http\Controllers;

use App\Models\ExpedienteDigital;
use App\Models\ExpedienteArchivo;
use App\Models\ExpedienteObservacion;
use App\Models\Usuario;
use App\Models\Programa;
use App\Models\Boleta;
use App\Services\ExpedienteStorageService;
use App\Services\RecordAcademicoPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpedienteController extends Controller
{
    /**
     * Listado principal de Expedientes Digitales 360° con filtros y métricas KPI.
     */
    public function index(Request $request)
    {
        $estadoFiltro = $request->get('estado', '');
        $busqueda = $request->get('q', '');

        $query = ExpedienteDigital::with('usuario');

        if (!empty($estadoFiltro)) {
            $query->where('estado', $estadoFiltro);
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('grado_a_matricular', 'like', "%{$busqueda}%")
                  ->orWhere('especialidad_deseada', 'like', "%{$busqueda}%")
                  ->orWhere('cedula_residencia', 'like', "%{$busqueda}%")
                  ->orWhere('pasaporte', 'like', "%{$busqueda}%")
                  ->orWhereHas('usuario', function ($u) use ($busqueda) {
                      $u->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('apellidos', 'like', "%{$busqueda}%")
                        ->orWhere('email', 'like', "%{$busqueda}%")
                        ->orWhere('cedula', 'like', "%{$busqueda}%");
                  });
            });
        }

        $expedientes = $query->orderBy('id_expediente', 'desc')->paginate(20);

        // Contadores KPI
        $totalExpedientes = ExpedienteDigital::count();
        $totalAprobados = ExpedienteDigital::where('estado', 'Aprobado')->count();
        $totalPendientes = ExpedienteDigital::where('estado', 'Pendiente')->count();
        $totalRechazados = ExpedienteDigital::where('estado', 'Rechazado')->count();

        return view('expedientes.index', compact(
            'expedientes',
            'totalExpedientes',
            'totalAprobados',
            'totalPendientes',
            'totalRechazados',
            'estadoFiltro',
            'busqueda'
        ));
    }

    /**
     * Vista 360° integral de un estudiante con las 5 pestañas interactivas.
     */
    public function ver($id)
    {
        $expediente = ExpedienteDigital::with(['usuario', 'archivos', 'observaciones.autor'])->findOrFail($id);
        $usuario = $expediente->usuario;
        $idUsuario = $usuario ? $usuario->id : $expediente->id_usuario;

        // 1. Fotografía y Firma digital (resueltas desde la bóveda)
        $fotoArchivo = $expediente->archivos->where('tipo_documento', 'fotografia')->first();
        $fotoUrl = $fotoArchivo ? ExpedienteStorageService::resolveFilePath($fotoArchivo->nombre_servidor)['web_url'] : null;

        $firmaArchivo = $expediente->archivos->where('tipo_documento', 'firma')->first();
        $firmaUrl = $firmaArchivo ? ExpedienteStorageService::resolveFilePath($firmaArchivo->nombre_servidor)['web_url'] : null;

        // 2. Kardex en Vivo / Historial Académico
        $cursos = DB::table('matriculas as m')
            ->join('cursos_activos as ca', 'm.id_curso_activo', '=', 'ca.id_curso_activo')
            ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->leftJoin('programas as p', 'pe.id_programa', '=', 'p.id_programa')
            ->leftJoin('usuarios as u_prof', 'ca.id_profesor', '=', 'u_prof.id')
            ->where('m.id_estudiante', $idUsuario)
            ->select(
                'm.id_matricula', 'm.calificacion', 'm.comentarios',
                'ca.id_curso_activo', 'ca.periodo', 'ca.fecha_inicio', 'ca.fecha_final', 'ca.check_acta',
                'pe.materia', 'pe.codigo', DB::raw('IFNULL(pe.creditos, 3) as creditos'),
                'p.nombre_programa',
                'u_prof.nombre as prof_nombre', 'u_prof.apellidos as prof_apellidos'
            )
            ->orderBy('ca.fecha_inicio', 'desc')
            ->orderBy('pe.codigo', 'asc')
            ->get();

        $cursosHistorial = [];
        $totalCreditosAprobados = 0;
        $totalCreditosCursados = 0;
        $sumaPonderada = 0;
        $cntAprobados = 0;
        $cntReprobados = 0;
        $cntEnCurso = 0;

        foreach ($cursos as $c) {
            $nota = $c->calificacion;
            $cred = (float)$c->creditos;
            if ($cred <= 0) $cred = 3;

            if ($nota !== null && $nota !== '') {
                $notaNum = (float)$nota;
                $totalCreditosCursados += $cred;
                $sumaPonderada += ($notaNum * $cred);

                if ($notaNum >= 70.0) {
                    $estadoCurso = 'Aprobado';
                    $totalCreditosAprobados += $cred;
                    $cntAprobados++;
                } else {
                    $estadoCurso = 'Reprobado';
                    $cntReprobados++;
                }
            } else {
                $estadoCurso = 'En Curso';
                $cntEnCurso++;
            }

            $cursosHistorial[] = [
                'id_matricula'   => $c->id_matricula,
                'codigo'         => $c->codigo,
                'materia'        => $c->materia,
                'periodo'        => $c->periodo,
                'creditos_calc'  => $cred,
                'calificacion'   => $c->calificacion,
                'estado_calc'    => $estadoCurso,
                'prof_nombre'    => $c->prof_nombre ? "{$c->prof_nombre} {$c->prof_apellidos}" : 'Docente Asignado',
                'check_acta'     => $c->check_acta,
                'id_curso_activo'=> $c->id_curso_activo,
                'comentarios'    => $c->comentarios,
            ];
        }

        $promedioGpa = ($totalCreditosCursados > 0) ? round($sumaPonderada / $totalCreditosCursados, 2) : 0.00;

        // 3. Finanzas / Boletas y Cuotas
        $boletas = Boleta::where('id_estudiante', $idUsuario)
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        $totalFacturado = 0;
        $totalPagado = 0;
        $cuotasPendientesCount = 0;
        $cuotasMoraCount = 0;
        $fechaHoy = date('Y-m-d');
        $boletasHistorial = [];

        foreach ($boletas as $b) {
            $totalFacturado += (float)($b->total ?? 0);
            $totalPagado += (float)($b->monto_pagado ?? 0);

            $cuotas = DB::table('seguimiento_pagos as sp')
                ->leftJoin('pagos as p', 'p.boleta_id', '=', 'sp.id_boleta')
                ->where('sp.id_boleta', $b->id)
                ->select('sp.*', 'p.fecha_pago', 'p.ruta_comprobante')
                ->orderBy('sp.numero_cuota', 'asc')
                ->get();

            foreach ($cuotas as $sp) {
                $spEstado = $sp->estado ?? 'pendiente';
                if ($spEstado !== 'pagado') {
                    $cuotasPendientesCount++;
                    if (!empty($sp->fecha_vencimiento) && $sp->fecha_vencimiento < $fechaHoy) {
                        $cuotasMoraCount++;
                    }
                }
            }

            $boletasHistorial[] = [
                'boleta' => $b,
                'cuotas' => $cuotas,
            ];
        }

        $saldoPendiente = max(0, $totalFacturado - $totalPagado);

        // 4. WhatsApp Directo
        $phoneWa = preg_replace('/[^0-9]/', '', $expediente->contacto_tel_celular ?: ($usuario->telefono ?? ''));
        if (strlen($phoneWa) === 8) {
            $phoneWa = "506" . $phoneWa;
        }
        $linkWa = !empty($phoneWa) ? "https://wa.me/{$phoneWa}?text=" . urlencode("Hola " . ($usuario->nombre ?? 'Estudiante') . ", le saludamos de Registro Académico de " . config('cliente.nombre', 'CEFI') . ".") : "#";

        // Categorías para bóveda
        $categoriasBoveda = ExpedienteStorageService::CATEGORIAS;

        return view('expedientes.ver', compact(
            'expediente',
            'usuario',
            'fotoUrl',
            'firmaUrl',
            'cursosHistorial',
            'promedioGpa',
            'totalCreditosAprobados',
            'cntAprobados',
            'cntReprobados',
            'cntEnCurso',
            'boletasHistorial',
            'totalFacturado',
            'totalPagado',
            'saldoPendiente',
            'cuotasPendientesCount',
            'cuotasMoraCount',
            'linkWa',
            'categoriasBoveda'
        ));
    }

    /**
     * Muestra el formulario para crear un nuevo expediente digital.
     */
    public function create(Request $request)
    {
        $programas = Programa::orderBy('nombre_programa', 'asc')->get();
        
        $pendientes = ExpedienteDigital::with('usuario')
            ->where('estado', 'Pendiente')
            ->orderBy('fecha_registro', 'desc')
            ->get();

        $usuarioPreseleccionado = null;
        if ($request->filled('id_usuario')) {
            $usuarioPreseleccionado = Usuario::with('expediente')->find($request->id_usuario);
        }

        return view('expedientes.create', compact('programas', 'pendientes', 'usuarioPreseleccionado'));
    }

    /**
     * Guarda o actualiza el expediente en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id',
            'grado_a_matricular' => 'nullable|string|max:50',
            'especialidad_deseada' => 'nullable|string|max:255',
            'genero' => 'nullable|string|max:50',
            'fecha_nacimiento' => 'nullable|date',
            'domicilio_direccion' => 'nullable|string',
        ]);

        $id_usuario = $request->id_usuario;
        $expediente = ExpedienteDigital::where('id_usuario', $id_usuario)->first();

        $data = $request->except(['_token', 'id_usuario']);
        
        $docFields = [
            'registro_doc_titulo_sec', 
            'registro_doc_titulo_univ', 
            'registro_doc_certificaciones', 
            'registro_doc_cedula', 
            'registro_doc_fotografia'
        ];
        foreach ($docFields as $docField) {
            $data[$docField] = $request->has($docField) ? 1 : 0;
        }

        if (empty($request->fecha_registro)) {
            $data['fecha_registro'] = Carbon::today()->format('Y-m-d');
        }
        $data['estado'] = $request->input('estado', 'Aprobado');

        if ($expediente) {
            $expediente->update($data);
            $message = 'Expediente digital actualizado con éxito.';
            $targetId = $expediente->id_expediente;
        } else {
            $data['id_usuario'] = $id_usuario;
            $nuevo = ExpedienteDigital::create($data);
            $message = 'Expediente digital creado con éxito.';
            $targetId = $nuevo->id_expediente;
        }

        return redirect()->route('expedientes.ver', $targetId)->with('success', $message);
    }

    /**
     * Cambia el estado del expediente (Aprobado, Rechazado, Pendiente).
     */
    public function cambiarEstado(Request $request, $id)
    {
        $expediente = ExpedienteDigital::findOrFail($id);
        $action = $request->input('action_expediente', '');
        
        $nuevoEstado = match($action) {
            'aprobar' => 'Aprobado',
            'rechazar' => 'Rechazado',
            default => 'Pendiente',
        };

        $expediente->update(['estado' => $nuevoEstado]);

        return redirect()->route('expedientes.ver', $id)
            ->with('success', "El expediente ha sido actualizado a estado: {$nuevoEstado}.");
    }

    /**
     * Endpoint AJAX para subir documentos a la Bóveda Jerárquica.
     */
    public function subirDocumento(Request $request)
    {
        $idExpediente = (int)$request->input('id_expediente');
        $expediente = ExpedienteDigital::with('usuario')->find($idExpediente);

        if (!$expediente) {
            return response()->json(['success' => false, 'message' => 'Expediente no encontrado.'], 404);
        }

        if (!$request->hasFile('archivo') || !$request->file('archivo')->isValid()) {
            return response()->json(['success' => false, 'message' => 'Seleccione un archivo válido para cargar.'], 400);
        }

        $file = $request->file('archivo');
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        $allowedExts = ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];

        if (!in_array($ext, $allowedExts)) {
            return response()->json(['success' => false, 'message' => 'Formato no permitido. Solo se aceptan PDF, imágenes, Office y comprimidos.'], 422);
        }

        // Límite 25MB
        if ($file->getSize() > 25 * 1024 * 1024) {
            return response()->json(['success' => false, 'message' => 'El archivo supera el tamaño máximo permitido de 25MB.'], 422);
        }

        $tipoDocumento = trim($request->input('tipo_documento', 'otro'));
        $categoria = trim($request->input('categoria', 'General'));
        $descripcion = trim($request->input('descripcion', ''));
        $nombreEstudiante = $expediente->usuario ? "{$expediente->usuario->nombre} {$expediente->usuario->apellidos}" : "Estudiante_{$idExpediente}";

        // Obtener directorio jerárquico
        $dirInfo = ExpedienteStorageService::getTargetDir($idExpediente, $nombreEstudiante, $tipoDocumento, $categoria);

        $tipoSlug = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $tipoDocumento);
        $fileBaseName = "exp_{$idExpediente}_{$tipoSlug}_" . time() . "_" . mt_rand(100, 999) . ".{$ext}";
        $destPath = $dirInfo['full_dir'] . DIRECTORY_SEPARATOR . $fileBaseName;
        $serverRelativeName = $dirInfo['relative_dir'] . $fileBaseName;

        try {
            $file->move($dirInfo['full_dir'], $fileBaseName);

            $subidoPor = Auth::check() ? Auth::user()->nombre . ' ' . (Auth::user()->apellidos ?? '') : 'Admin';

            $archivo = ExpedienteArchivo::create([
                'id_expediente'   => $idExpediente,
                'tipo_documento'  => $tipoDocumento,
                'categoria'       => $categoria,
                'nombre_original' => $originalName,
                'descripcion'     => $descripcion,
                'nombre_servidor' => $serverRelativeName,
                'ruta_archivo'    => $destPath,
                'subido_por'      => $subidoPor,
                'fecha_subida'    => now(),
            ]);

            return response()->json([
                'success'         => true,
                'message'         => 'Documento guardado exitosamente en la bóveda digital.',
                'id_archivo'      => $archivo->id_archivo,
                'nombre_original' => $originalName,
                'tipo_documento'  => $tipoDocumento,
                'categoria'       => $categoria,
                'descripcion'     => $descripcion,
                'url'             => ExpedienteStorageService::resolveFilePath($serverRelativeName)['web_url'],
                'fecha'           => now()->format('d/m/Y g:i a')
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error al guardar archivo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX para eliminar un documento de la bóveda.
     */
    public function eliminarDocumento(Request $request, $id_archivo)
    {
        $archivo = ExpedienteArchivo::find($id_archivo);
        if (!$archivo) {
            return response()->json(['success' => false, 'message' => 'Documento no encontrado.'], 404);
        }

        $res = ExpedienteStorageService::resolveFilePath($archivo->nombre_servidor);
        if ($res['exists']) {
            @unlink($res['full_path']);
        }

        $archivo->delete();

        return response()->json(['success' => true, 'message' => 'Documento eliminado de la bóveda digital.']);
    }

    /**
     * Endpoint AJAX para listar observaciones de bitácora.
     */
    public function listarObservaciones($id_expediente)
    {
        $observaciones = ExpedienteObservacion::where('id_expediente', $id_expediente)
            ->orderBy('fecha_registro', 'desc')
            ->get();

        $data = $observaciones->map(function ($obs) {
            return [
                'id'               => $obs->id,
                'autor_nombre'     => $obs->autor_nombre ?: 'Administración',
                'categoria'        => $obs->categoria ?: 'General',
                'observacion'      => $obs->observacion,
                'fecha_formateada' => $obs->fecha_registro ? $obs->fecha_registro->format('d/m/Y g:i a') : 'N/D',
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Endpoint AJAX para registrar una observación en la bitácora.
     */
    public function guardarObservacion(Request $request)
    {
        $idExpediente = (int)$request->input('id_expediente');
        $observacion = trim($request->input('observacion', ''));
        $categoria = trim($request->input('categoria', 'General'));

        if ($idExpediente <= 0 || empty($observacion)) {
            return response()->json(['success' => false, 'message' => 'Debe ingresar el texto de la observación.'], 422);
        }

        $idAutor = Auth::id() ?? 0;
        $autorNombre = Auth::check() ? Auth::user()->nombre . ' ' . (Auth::user()->apellidos ?? '') : 'Admin';

        $nueva = ExpedienteObservacion::create([
            'id_expediente'  => $idExpediente,
            'id_autor'       => $idAutor,
            'autor_nombre'   => $autorNombre,
            'categoria'      => $categoria,
            'observacion'    => $observacion,
            'fecha_registro' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Observación registrada exitosamente en la bitácora.',
            'data'    => [
                'id'               => $nueva->id,
                'autor_nombre'     => $autorNombre,
                'categoria'        => $categoria,
                'observacion'      => $observacion,
                'fecha_formateada' => now()->format('d/m/Y g:i a'),
            ]
        ]);
    }

    /**
     * Endpoint AJAX para eliminar una observación de la bitácora.
     */
    public function eliminarObservacion(Request $request, $id_observacion)
    {
        $obs = ExpedienteObservacion::find($id_observacion);
        if (!$obs) {
            return response()->json(['success' => false, 'message' => 'Observación no encontrada.'], 404);
        }

        $obs->delete();

        return response()->json(['success' => true, 'message' => 'Nota de bitácora eliminada exitosamente.']);
    }

    /**
     * Descarga masiva del expediente digital comprimido en ZIP.
     */
    public function descargarZip($id)
    {
        $expediente = ExpedienteDigital::with(['usuario', 'archivos'])->findOrFail($id);
        $zipPath = ExpedienteStorageService::generarZipExpediente($expediente);

        if (!$zipPath || !file_exists($zipPath)) {
            return redirect()->route('expedientes.ver', $id)
                ->with('error', 'No se encontraron documentos en la bóveda para descargar en ZIP.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    /**
     * Genera la Certificación Oficial de Récord Académico en PDF.
     */
    public function generarRecordPdf($id)
    {
        $expediente = ExpedienteDigital::with('usuario')->findOrFail($id);
        $idUsuario = $expediente->usuario ? $expediente->usuario->id : $expediente->id_usuario;

        $cursos = DB::table('matriculas as m')
            ->join('cursos_activos as ca', 'm.id_curso_activo', '=', 'ca.id_curso_activo')
            ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->leftJoin('programas as p', 'pe.id_programa', '=', 'p.id_programa')
            ->leftJoin('usuarios as u_prof', 'ca.id_profesor', '=', 'u_prof.id')
            ->where('m.id_estudiante', $idUsuario)
            ->select(
                'm.id_matricula', 'm.calificacion', 'm.comentarios',
                'ca.periodo', 'ca.fecha_inicio', 'ca.fecha_final', 'ca.check_acta',
                'pe.materia', 'pe.codigo', DB::raw('IFNULL(pe.creditos, 3) as creditos'),
                'p.nombre_programa',
                'u_prof.nombre as prof_nombre', 'u_prof.apellidos as prof_apellidos'
            )
            ->orderBy('ca.fecha_inicio', 'asc')
            ->orderBy('pe.codigo', 'asc')
            ->get();

        $cursosData = [];
        $totalCreditosAprobados = 0;
        $totalCreditosCursados = 0;
        $sumaPonderada = 0;
        $cursosAprobados = 0;
        $cursosReprobados = 0;
        $cursosEnCurso = 0;

        foreach ($cursos as $c) {
            $nota = $c->calificacion;
            $cred = (float)$c->creditos;
            if ($cred <= 0) $cred = 3;

            if ($nota !== null && $nota !== '') {
                $notaNum = (float)$nota;
                $totalCreditosCursados += $cred;
                $sumaPonderada += ($notaNum * $cred);

                if ($notaNum >= 70.0) {
                    $estado = 'APROBADO';
                    $totalCreditosAprobados += $cred;
                    $cursosAprobados++;
                } else {
                    $estado = 'REPROBADO';
                    $cursosReprobados++;
                }
            } else {
                $estado = 'EN CURSO';
                $cursosEnCurso++;
            }

            $cursosData[] = [
                'codigo'         => $c->codigo,
                'materia'        => $c->materia,
                'periodo'        => $c->periodo,
                'creditos_calc'  => $cred,
                'calificacion'   => $c->calificacion,
                'estado_calc'    => $estado,
            ];
        }

        $promedioPonderado = ($totalCreditosCursados > 0) ? round($sumaPonderada / $totalCreditosCursados, 2) : 0.00;

        $pdfContent = RecordAcademicoPdfService::generarPdf($expediente, [
            'cursos'                   => $cursosData,
            'total_creditos_aprobados' => $totalCreditosAprobados,
            'cursos_aprobados'         => $cursosAprobados,
            'cursos_en_curso'          => $cursosEnCurso,
            'promedio_ponderado'       => $promedioPonderado,
        ]);

        $fileName = "Record_Academico_EXP_{$expediente->id_expediente}.pdf";

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Busca usuarios y comprueba si ya tienen un expediente (para modal de creación).
     */
    public function buscarUsuarioAjax(Request $request)
    {
        $query = $request->get('query');
        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $parts = array_filter(explode(' ', $query));
        $usuariosQuery = Usuario::query();

        foreach ($parts as $part) {
            $usuariosQuery->where(function($q) use ($part) {
                $q->where('nombre', 'like', "%{$part}%")
                  ->orWhere('apellidos', 'like', "%{$part}%")
                  ->orWhere('email', 'like', "%{$part}%")
                  ->orWhere('cedula', 'like', "%{$part}%");
            });
        }

        $usuarios = $usuariosQuery->take(10)->get();

        $results = [];
        foreach ($usuarios as $u) {
            $exp = ExpedienteDigital::where('id_usuario', $u->id)->first();
            $results[] = [
                'id'                => $u->id,
                'nombre'            => $u->nombre,
                'apellidos'         => $u->apellidos,
                'email'             => $u->email,
                'has_expediente'    => !is_null($exp),
                'id_expediente'     => $exp ? $exp->id_expediente : null,
                'estado_expediente' => $exp ? $exp->estado : null
            ];
        }

        return response()->json($results);
    }
}
