<?php

namespace App\Http\Controllers;

use App\Models\CursoActivo;
use App\Models\Matricula;
use App\Models\FacturaProfesor;
use App\Models\RevisionCurso;
use App\Models\Usuario;
use App\Mail\GradeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class MisCursosController extends Controller
{
    /**
     * Listado de cursos asignados al profesor o todos si es admin.
     */
    public function index(Request $request)
    {
        $es_admin = (Auth::user()->id_rol == 1);
        $usuario_id = Auth::id();

        // Obtener todos los períodos disponibles
        $periodos = CursoActivo::select('periodo')
            ->whereNotNull('periodo')
            ->where('periodo', '!=', '')
            ->distinct()
            ->orderBy('periodo', 'desc')
            ->pluck('periodo')
            ->toArray();

        // Determinar periodo actual sugerido
        $mes = intval(date('n'));
        $anio = date('Y');
        if ($mes >= 1 && $mes <= 4) {
            $periodo_sugerido = "I Cuatrimestre " . $anio;
        } elseif ($mes >= 5 && $mes <= 8) {
            $periodo_sugerido = "II Cuatrimestre " . $anio;
        } else {
            $periodo_sugerido = "III Cuatrimestre " . $anio;
        }

        $periodo_actual = $request->get('periodo', '');
        if (empty($periodo_actual)) {
            if (in_array($periodo_sugerido, $periodos)) {
                $periodo_actual = $periodo_sugerido;
            } elseif (!empty($periodos)) {
                $periodo_actual = $periodos[0];
            } else {
                $periodo_actual = $periodo_sugerido;
            }
        }

        // Obtener cursos
        $query = CursoActivo::with(['planEstudio.programa', 'profesor'])
            ->where('periodo', $periodo_actual);

        if (!$es_admin) {
            $query->where('id_profesor', $usuario_id);
        }

        $cursos = $query->orderBy('horario', 'asc')->get();

        return view('mis_cursos.index', compact('cursos', 'periodos', 'periodo_actual', 'es_admin'));
    }

    /**
     * Muestra el detalle completo de un curso con la estructura exacta del prototipo.
     */
    public function ver($id)
    {
        $es_admin = (Auth::user()->id_rol == 1);
        $usuario_id = Auth::id();

        // Obtener el curso activo
        $query = CursoActivo::with(['planEstudio.programa', 'profesor']);
        if (!$es_admin) {
            $query->where('id_profesor', $usuario_id);
        }
        $curso = $query->findOrFail($id);

        // Obtener rúbricas del sílabo
        $rubros = DB::table('silabo_evaluacion')
            ->join('silabos', 'silabo_evaluacion.id_silabo', '=', 'silabos.id_silabo')
            ->where('silabos.id_plan', $curso->id_plan)
            ->select('silabo_evaluacion.id_evaluacion as id_rubro', 'silabo_evaluacion.rubro', 'silabo_evaluacion.porcentaje')
            ->orderBy('silabo_evaluacion.id_evaluacion', 'asc')
            ->get();

        // Obtener alumnos matriculados
        $alumnos = DB::table('matriculas')
            ->join('usuarios', 'matriculas.id_estudiante', '=', 'usuarios.id')
            ->where('matriculas.id_curso_activo', $id)
            ->where('usuarios.id', '!=', $curso->id_profesor)
            ->select('usuarios.id as id_estudiante', 'usuarios.nombre', 'usuarios.apellidos', 'usuarios.email', 'usuarios.cedula', 'matriculas.id_matricula', 'matriculas.calificacion as nota_final', 'matriculas.email_enviado', 'matriculas.fecha_envio_email')
            ->orderBy('usuarios.nombre', 'asc')
            ->get();

        // Obtener calificaciones por rubros de los alumnos matriculados
        $notas = [];
        $matriculas_ids = $alumnos->pluck('id_matricula')->toArray();
        if (count($matriculas_ids) > 0) {
            $notas_db = DB::table('notas_rubros')
                ->whereIn('id_matricula', $matriculas_ids)
                ->get();
            foreach ($notas_db as $n) {
                $notas[$n->id_matricula][$n->id_rubro] = $n->calificacion_obtenida;
            }
        }

        // Obtener facturas subidas del profesor por este curso
        $facturas = FacturaProfesor::where('id_curso_activo', $id)->get();

        // Obtener recursos Drive del curso
        $recursos_drive = DB::table('recursos_drive')
            ->where('id_curso_activo', $id)
            ->orderBy('fecha_subida', 'desc')
            ->get();

        // Observaciones / Revisiones académicas
        $revisiones_pendientes = RevisionCurso::where('id_curso_activo', $id)
            ->where('estado', 'pendiente')
            ->count();

        return view('mis_cursos.ver', compact(
            'curso', 
            'rubros', 
            'alumnos', 
            'notas', 
            'facturas', 
            'recursos_drive', 
            'revisiones_pendientes',
            'es_admin'
        ));
    }

    /**
     * Guarda las calificaciones de los alumnos.
     */
    public function guardarNotas(Request $request, $id)
    {
        $notas_parciales = $request->input('notas_parciales', []);

        foreach ($notas_parciales as $id_matricula => $rubros) {
            $nota_final = 0;
            foreach ($rubros as $id_rubro => $valor) {
                if ($valor !== null && $valor !== '') {
                    $check = DB::table('notas_rubros')
                        ->where('id_matricula', $id_matricula)
                        ->where('id_rubro', $id_rubro)
                        ->first();

                    if ($check) {
                        DB::table('notas_rubros')
                            ->where('id_nota_rubro', $check->id_nota_rubro)
                            ->update(['calificacion_obtenida' => $valor]);
                    } else {
                        DB::table('notas_rubros')->insert([
                            'id_matricula' => $id_matricula,
                            'id_rubro' => $id_rubro,
                            'calificacion_obtenida' => $valor
                        ]);
                    }
                    $nota_final += floatval($valor);
                }
            }

            // Actualizar nota final en matricula
            DB::table('matriculas')
                ->where('id_matricula', $id_matricula)
                ->update(['calificacion' => $nota_final]);
        }

        return redirect()->route('mis_cursos.ver', $id)->with('success', 'Calificaciones guardadas con éxito.');
    }

    /**
     * Envía de forma individual la calificación al estudiante por correo.
     */
    public function enviarNotaIndividual(Request $request, $id)
    {
        $id_matricula = $request->input('id_matricula');

        // Obtener datos
        $matricula = DB::table('matriculas')
            ->join('usuarios', 'matriculas.id_estudiante', '=', 'usuarios.id')
            ->join('cursos_activos', 'matriculas.id_curso_activo', '=', 'cursos_activos.id_curso_activo')
            ->join('plan_estudios', 'cursos_activos.id_plan', '=', 'plan_estudios.id_plan')
            ->where('matriculas.id_matricula', $id_matricula)
            ->select('usuarios.email', 'usuarios.nombre', 'usuarios.apellidos', 'matriculas.calificacion', 'matriculas.id_curso_activo', 'cursos_activos.id_plan', 'plan_estudios.materia', 'plan_estudios.codigo')
            ->first();

        if (!$matricula) {
            return response()->json(['success' => false, 'message' => 'Matrícula no encontrada.']);
        }

        if ($matricula->calificacion === null) {
            return response()->json(['success' => false, 'message' => 'El estudiante aún no tiene nota calificada.']);
        }

        // Obtener rubros del sílabo
        $rubros = DB::table('silabo_evaluacion')
            ->join('silabos', 'silabo_evaluacion.id_silabo', '=', 'silabos.id_silabo')
            ->where('silabos.id_plan', $matricula->id_plan)
            ->select('silabo_evaluacion.id_evaluacion as id_rubro', 'silabo_evaluacion.rubro', 'silabo_evaluacion.porcentaje')
            ->get();

        // Obtener notas parciales del alumno
        $notas_db = DB::table('notas_rubros')
            ->where('id_matricula', $id_matricula)
            ->get()
            ->pluck('calificacion_obtenida', 'id_rubro');

        // Construir desglose
        $gradeDetails = [];
        foreach ($rubros as $r) {
            $gradeDetails[] = [
                'rubro' => $r->rubro,
                'porcentaje' => $r->porcentaje,
                'calificacion' => $notas_db[$r->id_rubro] ?? 'N/A'
            ];
        }

        $cursoNombre = $matricula->materia . " (" . $matricula->codigo . ")";
        $destinatarioNombre = $matricula->nombre . " " . $matricula->apellidos;

        try {
            Mail::to($matricula->email)->send(new GradeMail(
                $destinatarioNombre,
                $cursoNombre,
                $matricula->calificacion,
                $gradeDetails
            ));

            // Registrar envío
            DB::table('matriculas')
                ->where('id_matricula', $id_matricula)
                ->update([
                    'email_enviado' => 1,
                    'fecha_envio_email' => Carbon::now()
                ]);

            // Automatización: Marcar check_calificaciones en curso
            DB::table('cursos_activos')
                ->where('id_curso_activo', $matricula->id_curso_activo)
                ->update(['check_calificaciones' => 1]);

            return response()->json(['success' => true, 'message' => 'Correo enviado correctamente a ' . $matricula->email]);

        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Error al enviar: ' . $th->getMessage()]);
        }
    }

    /**
     * Sube factura docente de cobro.
     */
    public function subirFactura(Request $request, $id)
    {
        $request->validate([
            'numero_factura' => 'required|string|max:100',
            'monto' => 'required|numeric|min:1',
            'moneda' => 'required|in:CRC,USD',
            'factura_archivo' => 'required|file|mimes:pdf,xml|max:5120'
        ]);

        $curso = CursoActivo::findOrFail($id);

        if ($request->hasFile('factura_archivo')) {
            $file = $request->file('factura_archivo');
            $filename = time() . '_' . preg_replace("/[^A-Z0-9._-]/i", "_", $file->getClientOriginalName());
            
            $file->move(public_path('uploads/facturas'), $filename);
            $filePath = 'uploads/facturas/' . $filename;

            FacturaProfesor::create([
                'id_curso_activo' => $id,
                'id_profesor' => $curso->id_profesor,
                'numero_factura' => $request->numero_factura,
                'monto' => $request->monto,
                'moneda' => $request->moneda,
                'archivo_ruta' => $filePath,
                'fecha_subida' => Carbon::now(),
                'estado_revision' => 'pendiente'
            ]);

            return response()->json(['success' => true, 'message' => 'Factura registrada con éxito.']);
        }

        return response()->json(['success' => false, 'message' => 'Error al cargar el archivo de factura.']);
    }

    /**
     * Elimina una factura de cobro.
     */
    public function eliminarFactura(Request $request, $id)
    {
        $factura_id = $request->input('id_factura_eliminar');
        $factura = FacturaProfesor::where('id_curso_activo', $id)->findOrFail($factura_id);
        
        $filePath = public_path($factura->archivo_ruta);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $factura->delete();

        return redirect()->route('mis_cursos.ver', $id)->with('success', 'Factura de cobro eliminada.');
    }

    /**
     * Muestra el panel de registro de asistencia de estudiantes.
     */
    public function verAsistencia(Request $request, $id)
    {
        $es_admin = (Auth::user()->id_rol == 1);
        $usuario_id = Auth::id();

        // Obtener el curso activo
        $query = CursoActivo::with(['planEstudio', 'profesor']);
        if (!$es_admin) {
            $query->where('id_profesor', $usuario_id);
        }
        $curso = $query->findOrFail($id);
        $materia = $curso->planEstudio->materia;

        // 1. Obtener Cronograma del Sílabo
        $silabo = DB::table('silabos')
            ->where('id_plan', $curso->id_plan)
            ->orderBy('fecha_creacion', 'desc')
            ->first();

        $cronograma = [];
        if ($silabo) {
            $cronograma = DB::table('silabo_cronograma')
                ->where('id_silabo', $silabo->id_silabo)
                ->orderByRaw('CAST(semana AS UNSIGNED) ASC')
                ->get()
                ->toArray();
        }

        // Auto-generar calendario de 15 semanas si cronograma está vacío
        if (empty($cronograma)) {
            $fecha_inicio = $curso->fecha_inicio ? $curso->fecha_inicio->format('Y-m-d') : date('Y-m-d');
            $horario_dia = explode(' ', trim($curso->horario))[0] ?? 'Lunes';
            
            $dias_map = [
                'Lunes' => 'Monday', 'Martes' => 'Tuesday', 'Miércoles' => 'Wednesday', 'Miercoles' => 'Wednesday',
                'Jueves' => 'Thursday', 'Viernes' => 'Friday', 'Sábado' => 'Saturday', 'Sabado' => 'Saturday', 'Domingo' => 'Sunday'
            ];
            $dia_ingles = $dias_map[$horario_dia] ?? 'Monday';

            $start_date = new Carbon($fecha_inicio);
            if ($start_date->format('l') !== $dia_ingles) {
                $start_date->modify("next $dia_ingles");
            }

            for ($w = 1; $w <= 15; $w++) {
                $cronograma[] = [
                    'id_cronograma' => -$w,
                    'semana' => (string)$w,
                    'fecha' => $start_date->format('Y-m-d'),
                    'actividad' => 'Sesión de clase ordinaria número ' . $w
                ];
                $start_date->addWeek();
            }
        }

        // Convertir cronograma items a arrays standarizado
        $cronograma = array_map(function($item) {
            return (array)$item;
        }, $cronograma);

        // Determinar semana activa
        $semana_activa = $request->get('semana');
        $fecha_clase_activa = null;
        $actividad_activa = '';

        if (!$semana_activa) {
            $fecha_hoy = date('Y-m-d');
            foreach ($cronograma as $crono) {
                if ($crono['fecha'] === $fecha_hoy) {
                    $semana_activa = $crono['semana'];
                    $fecha_clase_activa = $crono['fecha'];
                    $actividad_activa = $crono['actividad'];
                    break;
                }
            }
            if (!$semana_activa && !empty($cronograma)) {
                $semana_activa = $cronograma[0]['semana'];
                $fecha_clase_activa = $cronograma[0]['fecha'];
                $actividad_activa = $cronograma[0]['actividad'];
            }
        } else {
            foreach ($cronograma as $crono) {
                if ($crono['semana'] === $semana_activa) {
                    $fecha_clase_activa = $crono['fecha'];
                    $actividad_activa = $crono['actividad'];
                    break;
                }
            }
        }

        // Buscar tarea de base de datos vinculada
        $patron_titulo = "Clase " . $semana_activa . " - " . $materia . "%";
        $tarea = DB::table('tareas')
            ->where('id_curso_activo', $id)
            ->where('titulo', 'like', $patron_titulo)
            ->first();

        // Obtener estudiantes
        $estudiantes = DB::table('matriculas')
            ->join('usuarios', 'matriculas.id_estudiante', '=', 'usuarios.id')
            ->where('matriculas.id_curso_activo', $id)
            ->where('usuarios.id', '!=', $curso->id_profesor)
            ->select('usuarios.id as id_estudiante', 'usuarios.nombre', 'usuarios.apellidos', 'usuarios.cedula')
            ->orderBy('usuarios.apellidos', 'asc')
            ->get();

        // Cargar asistencias
        $asistencias_cargadas = [];
        if ($tarea) {
            $asistencias_db = DB::table('asistencias')
                ->where('id_tarea', $tarea->id)
                ->get();
            foreach ($asistencias_db as $a) {
                $asistencias_cargadas[$a->id_estudiante] = $a->estado;
            }
        }

        return view('mis_cursos.asistencia', compact(
            'curso',
            'cronograma',
            'semana_activa',
            'fecha_clase_activa',
            'actividad_activa',
            'estudiantes',
            'asistencias_cargadas',
            'es_admin'
        ));
    }

    /**
     * Guarda la asistencia del estudiante de una sesión.
     */
    public function registrarAsistencia(Request $request, $id)
    {
        $semana = $request->input('semana_activa');
        $fecha = $request->input('fecha_clase_activa');
        $asistencias = $request->input('asistencias', []);

        $curso = CursoActivo::with('planEstudio')->findOrFail($id);
        $materia = $curso->planEstudio->materia;

        // Buscar o crear tarea de base de datos
        $patron_titulo = "Clase " . $semana . " - " . $materia . "%";
        $tarea = DB::table('tareas')
            ->where('id_curso_activo', $id)
            ->where('titulo', 'like', $patron_titulo)
            ->first();

        DB::beginTransaction();
        try {
            if (!$tarea) {
                $titulo = "Clase " . $semana . " - " . $materia . " (" . Carbon::parse($fecha)->format('d/m/Y') . ")";
                $descripcion = "Registro de asistencia correspondiente a la Clase N° " . $semana;
                
                $tarea_id = DB::table('tareas')->insertGetId([
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'prioridad' => 'baja',
                    'id_creador' => $curso->id_profesor,
                    'fecha_creacion' => $fecha,
                    'fecha_vencimiento' => $fecha,
                    'id_curso_activo' => $id,
                    'estado' => 'pendiente'
                ]);
            } else {
                $tarea_id = $tarea->id;
            }

            foreach ($asistencias as $id_estudiante => $estado) {
                $check = DB::table('asistencias')
                    ->where('id_tarea', $tarea_id)
                    ->where('id_estudiante', $id_estudiante)
                    ->first();

                if ($check) {
                    DB::table('asistencias')
                        ->where('id_asistencia', $check->id_asistencia)
                        ->update(['estado' => $estado]);
                } else {
                    DB::table('asistencias')->insert([
                        'id_tarea' => $tarea_id,
                        'id_estudiante' => $id_estudiante,
                        'estado' => $estado
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('mis_cursos.asistencia', [$id, 'semana' => $semana])->with('success', 'Asistencia registrada con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('mis_cursos.asistencia', [$id, 'semana' => $semana])->with('error', 'Error al guardar asistencia: ' . $e->getMessage());
        }
    }

    /**
     * Endpoint para sincronizar matrícula de estudiantes desde Moodle (cURL bridge).
     */
    public function sincronizarEstudiantes(Request $request, $id)
    {
        $key = $request->input('key');
        $id_moodle = $request->input('id_moodle');

        $masterKey = config('cliente.moodle_key', 'cefi2026');
        if ($key !== $masterKey && $key !== 'unela2026') {
            return response()->json(['success' => false, 'message' => 'Palabra clave de validación incorrecta o ausente.']);
        }

        $curso = CursoActivo::findOrFail($id);

        // Guardar ID Moodle en el curso
        $curso->update(['id_moodle' => $id_moodle]);

        $auth_token = env('MOODLE_TOKEN', 'ef5bde9afb1fdd026330058ec405a30e');
        $bridge_url = rtrim(config('cliente.campus_virtual', 'https://virtual.cefi.cr'), '/') . '/webservice/moodle_bridge.php';

        try {
            $response = Http::timeout(30)->get($bridge_url, [
                'token' => $auth_token,
                'courseid' => $id_moodle,
                'action' => 'get_users'
            ]);

            if ($response->failed()) {
                return response()->json(['success' => false, 'message' => 'Error al conectar con Moodle Bridge.']);
            }

            $bridge_res = $response->json();
            if (!$bridge_res || !isset($bridge_res['success']) || !$bridge_res['success']) {
                return response()->json(['success' => false, 'message' => $bridge_res['message'] ?? 'Error desconocido']);
            }

            $moodle_users = $bridge_res['users'] ?? [];
            $actualizados = 0;
            $creados = 0;
            $matriculados = 0;
            $omitidos_profesores = 0;
            $ids_procesados = [];

            // Obtener ID de rol miembro / estudiante
            $default_id_rol = DB::table('roles')
                ->where('nombre', 'Miembro')
                ->orWhere('nombre', 'Estudiante')
                ->value('id') ?? 2;

            foreach ($moodle_users as $m_user) {
                $email = trim($m_user['email'] ?? '');
                if (empty($email)) continue;

                $firstname = trim($m_user['firstname'] ?? '');
                $lastname = trim($m_user['lastname'] ?? '');
                $idnumber = trim($m_user['idnumber'] ?? '');
                $moodle_role = $m_user['role'] ?? 'student';

                if (empty($idnumber)) {
                    $idnumber = 'M-' . $m_user['id'];
                }

                // Omitir profesores en Moodle
                if (in_array($moodle_role, ['editingteacher', 'teacher', 'manager', 'coursecreator'])) {
                    $omitidos_profesores++;
                    // Borrar matricula de este usuario si existía como alumno
                    $u_id = DB::table('usuarios')->where('email', $email)->value('id');
                    if ($u_id) {
                        DB::table('matriculas')->where('id_estudiante', $u_id)->where('id_curso_activo', $id)->delete();
                    }
                    continue;
                }

                // Buscar usuario en BPM
                $user_db = Usuario::where('id_moodle', $m_user['id'])->first();
                if (!$user_db) {
                    $user_db = Usuario::where('email', $email)->first();
                }

                if ($user_db) {
                    if ($user_db->id === $curso->id_profesor) {
                        $omitidos_profesores++;
                        DB::table('matriculas')->where('id_estudiante', $user_db->id)->where('id_curso_activo', $id)->delete();
                        continue;
                    }

                    $user_db->update([
                        'nombre' => $firstname,
                        'apellidos' => $lastname,
                        'id_moodle' => $m_user['id'],
                        'origen' => 'moodle'
                    ]);
                    $actualizados++;
                    $id_usuario_bpm = $user_db->id;
                } else {
                    $dummy_pass = password_hash(bin2hex(random_bytes(10)), PASSWORD_BCRYPT);
                    $new_u = Usuario::create([
                        'id_moodle' => $m_user['id'],
                        'nombre' => $firstname,
                        'apellidos' => $lastname,
                        'email' => $email,
                        'cedula' => $idnumber,
                        'password' => $dummy_pass,
                        'id_rol' => $default_id_rol,
                        'origen' => 'moodle'
                    ]);
                    $id_usuario_bpm = $new_u->id;
                    $creados++;
                }

                if ($id_usuario_bpm > 0) {
                    $ids_procesados[] = $id_usuario_bpm;
                    
                    // Matricular
                    $check_mat = DB::table('matriculas')
                        ->where('id_estudiante', $id_usuario_bpm)
                        ->where('id_curso_activo', $id)
                        ->first();

                    if (!$check_mat) {
                        DB::table('matriculas')->insert([
                            'id_estudiante' => $id_usuario_bpm,
                            'id_curso_activo' => $id
                        ]);
                        $matriculados++;
                    }
                }
            }

            // Limpieza final de matriculados
            $eliminados = 0;
            if (!empty($ids_procesados)) {
                $eliminados = DB::table('matriculas')
                    ->where('id_curso_activo', $id)
                    ->whereNotIn('id_estudiante', $ids_procesados)
                    ->delete();
            }

            // Marcar hitos en curso
            $curso->update([
                'check_moodle' => 1,
                'check_estudiantes' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sincronización de curso completada.',
                'details' => [
                    'moodle_total' => count($moodle_users),
                    'usuarios_creados' => $creados,
                    'usuarios_actualizados' => $actualizados,
                    'nuevas_matriculas' => $matriculados,
                    'profesores_omitidos' => $omitidos_profesores,
                    'estudiantes_eliminados' => $eliminados
                ]
            ]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Fallo al sincronizar: ' . $e->getMessage()]);
        }
    }

    /**
     * Endpoint para sincronizar calificaciones de estudiantes desde Moodle (Gradebook).
     */
    public function sincronizarNotasMoodle(Request $request, $id)
    {
        $curso = CursoActivo::findOrFail($id);

        $es_admin = (Auth::user()->id_rol == 1);
        $es_prof_titular = (Auth::id() == $curso->id_profesor);
        $es_docente = (Auth::user()->id_rol == 4 || Auth::user()->id_rol == 2);

        if (!$es_admin && !$es_prof_titular && !$es_docente) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para sincronizar las calificaciones de este curso.'
            ], 403);
        }

        try {
            $resultado = \App\Services\MoodleGradebookService::sincronizar((int)$id);

            return response()->json([
                'success' => true,
                'message' => '¡Sincronización completada con éxito! Se procesaron las calificaciones de ' . $resultado['total_procesados'] . ' estudiante(s) desde Moodle Virtual.',
                'total_procesados' => $resultado['total_procesados'],
                'notas_insertadas' => $resultado['notas_insertadas'],
                'detalles' => $resultado['detalles']
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Endpoint para listar revisiones académicas.
     */
    public function listarRevisiones($id)
    {
        $revisiones = RevisionCurso::with(['creador', 'resolutor'])
            ->where('id_curso_activo', $id)
            ->orderBy('id', 'desc')
            ->get();

        $data = [];
        foreach ($revisiones as $rev) {
            $data[] = [
                'id' => $rev->id,
                'sugerencia' => $rev->sugerencia,
                'captura_pantalla' => $rev->captura_pantalla ? asset($rev->captura_pantalla) : null,
                'estado' => $rev->estado,
                'fecha_creacion' => $rev->fecha_creacion ? $rev->fecha_creacion->format('d/m/Y g:i a') : '-',
                'creador' => $rev->creador->nombre ?? 'Académico',
                'fecha_resolucion' => $rev->fecha_resolucion ? $rev->fecha_resolucion->format('d/m/Y g:i a') : '-',
                'resolutor' => $rev->resolutor->nombre ?? 'N/A'
            ];
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Endpoint para crear sugerencia o revisión.
     */
    public function guardarRevision(Request $request, $id)
    {
        if (Auth::user()->id_rol != 1) {
            return response()->json(['success' => false, 'message' => 'No autorizado']);
        }

        $request->validate([
            'sugerencia' => 'required|string'
        ]);

        $filePath = null;
        if ($request->hasFile('captura')) {
            $file = $request->file('captura');
            $filename = time() . '_' . preg_replace("/[^A-Z0-9._-]/i", "_", $file->getClientOriginalName());
            $file->move(public_path('uploads/revisiones'), $filename);
            $filePath = 'uploads/revisiones/' . $filename;
        }

        RevisionCurso::create([
            'id_curso_activo' => $id,
            'sugerencia' => $request->sugerencia,
            'captura_pantalla' => $filePath,
            'estado' => 'pendiente',
            'fecha_creacion' => Carbon::now(),
            'creado_por' => Auth::id()
        ]);

        return response()->json(['success' => true, 'message' => 'Observación guardada.']);
    }

    /**
     * Endpoint para resolver revisión.
     */
    public function resolverRevision(Request $request)
    {
        $id = $request->input('id');
        $rev = RevisionCurso::findOrFail($id);

        $rev->update([
            'estado' => 'resuelto',
            'fecha_resolucion' => Carbon::now(),
            'resuelto_por' => Auth::id()
        ]);

        // Verificar si quedan pendientes
        $pendientes = RevisionCurso::where('id_curso_activo', $rev->id_curso_activo)
            ->where('estado', 'pendiente')
            ->count();

        return response()->json([
            'success' => true, 
            'message' => 'Recomendación marcada como solucionada.',
            'todo_resuelto' => ($pendientes == 0)
        ]);
    }

    /**
     * Endpoint para eliminar sugerencia.
     */
    public function eliminarRevision(Request $request)
    {
        if (Auth::user()->id_rol != 1) {
            return response()->json(['success' => false, 'message' => 'No autorizado']);
        }

        $id = $request->input('id');
        $rev = RevisionCurso::findOrFail($id);

        if ($rev->captura_pantalla) {
            $filePath = public_path($rev->captura_pantalla);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $rev->delete();

        return response()->json(['success' => true, 'message' => 'Observación eliminada.']);
    }

    /**
     * Muestra la pantalla de bienvenida/cortina.
     */
    public function verCortina($id, Request $request)
    {
        $es_admin = (Auth::user()->id_rol == 1);
        $usuario_id = Auth::id();
        $query = CursoActivo::with('planEstudio.programa', 'profesor');
        if (!$es_admin) {
            $query->where('id_profesor', $usuario_id);
        }
        $curso = $query->findOrFail($id);
        $countdown_minutes = (int)$request->get('minutes', 5);

        return view('mis_cursos.cortina', compact('curso', 'countdown_minutes'));
    }

    /**
     * Muestra el creador de portadas.
     */
    public function verCrearPortada($id)
    {
        $es_admin = (Auth::user()->id_rol == 1);
        $usuario_id = Auth::id();
        $query = CursoActivo::with('planEstudio.programa', 'profesor');
        if (!$es_admin) {
            $query->where('id_profesor', $usuario_id);
        }
        $curso = $query->findOrFail($id);

        $id_programa_curso = $curso->planEstudio->id_programa ?? 0;
        $categoria_curso = $curso->planEstudio->programa->categoria ?? '';

        // Cargar recursos de biblioteca ordenados por relevancia para este curso
        $biblioteca_imgs = DB::table('recursos_corporativos as rc')
            ->leftJoin('programas as p', 'rc.id_programa', '=', 'p.id_programa')
            ->select('rc.*', 'p.nombre_programa', 'p.categoria as programa_categoria')
            ->orderByRaw('(rc.id_programa = ? AND rc.id_programa > 0) DESC', [$id_programa_curso])
            ->orderByRaw("(p.categoria = ? AND p.categoria != '') DESC", [$categoria_curso])
            ->orderBy('rc.id', 'desc')
            ->get();

        // Calcular formato de cuatrimestre y año sugerido (ej: IIIC 2026)
        $periodo_raw = $curso->periodo ?? '';
        $cuat_num = 1;
        if (stripos($periodo_raw, 'II ') !== false || stripos($periodo_raw, 'II C') !== false || stripos($periodo_raw, '2') !== false) $cuat_num = 2;
        if (stripos($periodo_raw, 'III') !== false || stripos($periodo_raw, '3') !== false) $cuat_num = 3;
        $romanos = [1 => 'I', 2 => 'II', 3 => 'III'];
        $rom = $romanos[$cuat_num] ?? 'I';
        $anio_c = $curso->fecha_inicio ? Carbon::parse($curso->fecha_inicio)->format('Y') : date('Y');
        $periodo_texto_default = $rom . 'C ' . $anio_c;

        $imagen_inicial_ruta = "";
        $imagen_inicial_ruta_moodle = "";

        // 1. Buscar mejor coincidencia para Portada Principal
        foreach ($biblioteca_imgs as $img_lib) {
            $tipo = $img_lib->categoria_academica ?? 'portada';
            $nombre_lower = strtolower(($img_lib->nombre ?? '') . ' ' . ($img_lib->nombre_archivo ?? ''));
            $es_portada = ($tipo === 'portada' || empty($tipo) || strpos($nombre_lower, 'portada') !== false);
            if ($es_portada && $tipo !== 'miniatura') {
                if (!empty($img_lib->id_programa) && $img_lib->id_programa == $id_programa_curso) {
                    $imagen_inicial_ruta = $img_lib->ruta_archivo;
                    break;
                }
                if (!empty($img_lib->programa_categoria) && $img_lib->programa_categoria === $categoria_curso) {
                    $imagen_inicial_ruta = $img_lib->ruta_archivo;
                    break;
                }
            }
        }
        if (empty($imagen_inicial_ruta)) {
            foreach ($biblioteca_imgs as $img_lib) {
                $tipo = $img_lib->categoria_academica ?? 'portada';
                if ($tipo === 'portada' || empty($tipo)) {
                    $imagen_inicial_ruta = $img_lib->ruta_archivo;
                    break;
                }
            }
        }

        // 2. Buscar mejor coincidencia para Miniatura Moodle
        foreach ($biblioteca_imgs as $img_lib) {
            $tipo = $img_lib->categoria_academica ?? '';
            $nombre_lower = strtolower(($img_lib->nombre ?? '') . ' ' . ($img_lib->nombre_archivo ?? ''));
            $es_miniatura = ($tipo === 'miniatura' || strpos($nombre_lower, 'miniatura') !== false || strpos($nombre_lower, 'moodle') !== false);
            
            if ($es_miniatura) {
                if (!empty($img_lib->id_programa) && $img_lib->id_programa == $id_programa_curso) {
                    $imagen_inicial_ruta_moodle = $img_lib->ruta_archivo;
                    break;
                }
                if (!empty($img_lib->programa_categoria) && $img_lib->programa_categoria === $categoria_curso) {
                    $imagen_inicial_ruta_moodle = $img_lib->ruta_archivo;
                    break;
                }
                if (empty($imagen_inicial_ruta_moodle) && empty($img_lib->id_programa)) {
                    $imagen_inicial_ruta_moodle = $img_lib->ruta_archivo;
                }
            }
        }
        if (empty($imagen_inicial_ruta_moodle)) {
            foreach ($biblioteca_imgs as $img_lib) {
                $tipo = $img_lib->categoria_academica ?? '';
                $nombre_lower = strtolower(($img_lib->nombre ?? '') . ' ' . ($img_lib->nombre_archivo ?? ''));
                if ($tipo === 'miniatura' || strpos($nombre_lower, 'miniatura') !== false) {
                    $imagen_inicial_ruta_moodle = $img_lib->ruta_archivo;
                    break;
                }
            }
        }

        $path_saved_portada = 'uploads/portadas/portada_curso_' . $id . '.png';
        $path_saved_moodle = 'uploads/portadas/miniatura_curso_' . $id . '.png';
        $existe_portada = file_exists(public_path($path_saved_portada));
        $existe_moodle = file_exists(public_path($path_saved_moodle));

        return view('mis_cursos.crear_portada', compact(
            'curso', 'biblioteca_imgs', 'periodo_texto_default',
            'imagen_inicial_ruta', 'imagen_inicial_ruta_moodle',
            'path_saved_portada', 'path_saved_moodle', 'existe_portada', 'existe_moodle'
        ));
    }

    /**
     * Guarda la portada y miniatura generadas por GD o Canvas y sincroniza con Moodle si aplica.
     */
    public function guardarPortada(Request $request, $id)
    {
        $curso = CursoActivo::findOrFail($id);

        if (!file_exists(public_path('uploads/portadas'))) {
            mkdir(public_path('uploads/portadas'), 0777, true);
        }

        // Si se envió mediante canvas Base64
        if ($request->has('portada_data') && $request->has('miniatura_data')) {
            $portadaData = preg_replace('#^data:image/\w+;base64,#i', '', $request->input('portada_data'));
            $portadaBytes = base64_decode($portadaData);
            file_put_contents(public_path('uploads/portadas/portada_curso_' . $id . '.png'), $portadaBytes);

            $miniaturaData = preg_replace('#^data:image/\w+;base64,#i', '', $request->input('miniatura_data'));
            $miniaturaBytes = base64_decode($miniaturaData);
            file_put_contents(public_path('uploads/portadas/miniatura_curso_' . $id . '.png'), $miniaturaBytes);
        }

        // Sincronizar automáticamente con Moodle si el curso ya existe en Moodle
        if ($curso->id_moodle > 0 && file_exists(public_path('uploads/portadas/miniatura_curso_' . $id . '.png'))) {
            $miniatura_base64 = base64_encode(file_get_contents(public_path('uploads/portadas/miniatura_curso_' . $id . '.png')));
            
            $bridge_img_url = rtrim(config('cliente.campus_virtual', 'https://virtual.cefi.cr'), '/') . '/moodle_bridge.php';
            try {
                Http::withoutVerifying()->timeout(15)->post($bridge_img_url, [
                    'token' => env('MOODLE_TOKEN', 'ef5bde9afb1fdd026330058ec405a30e'),
                    'action' => 'set_course_image',
                    'courseid' => $curso->id_moodle,
                    'image_base64' => $miniatura_base64,
                    'filename' => 'course_overview_' . $id . '.png'
                ]);
            } catch (\Throwable $e) {
                // Registro silencioso si la red externa falla
            }
        }

        // Actualizar hito
        $curso->update([
            'check_portada' => 1
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Portada y miniatura Moodle guardadas y sincronizadas con éxito.',
                'portada_url' => asset('uploads/portadas/portada_curso_' . $id . '.png'),
                'miniatura_url' => asset('uploads/portadas/miniatura_curso_' . $id . '.png')
            ]);
        }

        return redirect()->back()->with('success', '¡Portada y miniatura Moodle generadas, guardadas y sincronizadas con éxito!');
    }

    /**
     * Muestra la vista de notificaciones y avisos de clase.
     */
    public function verNotificacionesClase($id)
    {
        $es_admin = (Auth::user()->id_rol == 1);
        $usuario_id = Auth::id();
        $query = CursoActivo::with('planEstudio.programa', 'profesor');
        if (!$es_admin) {
            $query->where('id_profesor', $usuario_id);
        }
        $curso = $query->findOrFail($id);

        $silabo = DB::table('silabos')
            ->where('id_plan', $curso->id_plan)
            ->orderBy('fecha_creacion', 'desc')
            ->first();

        $cronograma = [];
        if ($silabo) {
            $cronograma = DB::table('silabo_cronograma')
                ->where('id_silabo', $silabo->id_silabo)
                ->orderByRaw('CAST(semana AS UNSIGNED) ASC')
                ->get()
                ->toArray();
        }

        // Auto-generar calendario de 15 semanas si cronograma está vacío
        if (empty($cronograma)) {
            $fecha_inicio = $curso->fecha_inicio ? $curso->fecha_inicio->format('Y-m-d') : date('Y-m-d');
            $horario_dia = explode(' ', trim($curso->horario))[0] ?? 'Lunes';
            
            $dias_map = [
                'Lunes' => 'Monday', 'Martes' => 'Tuesday', 'Miércoles' => 'Wednesday', 'Miercoles' => 'Wednesday',
                'Jueves' => 'Thursday', 'Viernes' => 'Friday', 'Sábado' => 'Saturday', 'Sabado' => 'Saturday', 'Domingo' => 'Sunday'
            ];
            $dia_ingles = $dias_map[$horario_dia] ?? 'Monday';

            $start_date = new Carbon($fecha_inicio);
            if ($start_date->format('l') !== $dia_ingles) {
                $start_date->modify("next $dia_ingles");
            }

            for ($w = 1; $w <= 15; $w++) {
                $cronograma[] = (object)[
                    'semana' => (string)$w,
                    'fecha' => $start_date->format('Y-m-d'),
                    'actividad' => 'Sesión de clase ordinaria número ' . $w,
                    'tareas' => 'Lectura estándar semana ' . $w
                ];
                $start_date->addWeek();
            }
        }

        return view('mis_cursos.notificaciones_clase', compact('curso', 'cronograma'));
    }

    /**
     * Envía notificaciones / correos a todos los alumnos matriculados.
     */
    public function enviarNotificacionClase(Request $request, $id)
    {
        $request->validate([
            'asunto' => 'required|string|max:200',
            'mensaje' => 'required|string'
        ]);

        $curso = CursoActivo::findOrFail($id);

        $alumnos = DB::table('matriculas')
            ->join('usuarios', 'matriculas.id_estudiante', '=', 'usuarios.id')
            ->where('matriculas.id_curso_activo', $id)
            ->where('usuarios.id', '!=', $curso->id_profesor)
            ->select('usuarios.email', 'usuarios.nombre', 'usuarios.apellidos')
            ->get();

        if ($alumnos->isEmpty()) {
            return redirect()->back()->with('error', 'No hay estudiantes matriculados en este curso para enviar avisos.');
        }

        $enviados = 0;
        $fallados = 0;
        $asunto = $request->input('asunto');
        $cuerpo = $request->input('mensaje');

        foreach ($alumnos as $al) {
            try {
                Mail::raw($cuerpo, function ($message) use ($al, $asunto) {
                    $message->to($al->email)
                        ->subject($asunto);
                });
                $enviados++;
            } catch (\Throwable $th) {
                $fallados++;
            }
        }

        return redirect()->back()->with('success', "Avisos procesados. Enviados con éxito: {$enviados}. Fallados: {$fallados}.");
    }

    /**
     * Muestra la interfaz de gestión de sílabo.
     */
    public function verGestionarSilabo($id)
    {
        $es_admin = (Auth::user()->id_rol == 1);
        $usuario_id = Auth::id();
        $query = CursoActivo::with('planEstudio.programa', 'profesor');
        if (!$es_admin) {
            $query->where('id_profesor', $usuario_id);
        }
        $curso = $query->findOrFail($id);

        // Buscar sílabo activo (priorizar el del profesor del curso activo, luego el del usuario logueado)
        $silabo = DB::table('silabos')
            ->where('id_plan', $curso->id_plan)
            ->orderByRaw('(id_profesor = ?) DESC', [$curso->id_profesor])
            ->orderByRaw('(id_profesor = ?) DESC', [Auth::id()])
            ->orderBy('id_silabo', 'desc')
            ->first();

        $cronograma = [];
        $evaluaciones = [];
        $rubricas = [];

        if ($silabo) {
            $cronograma = DB::table('silabo_cronograma')
                ->where('id_silabo', $silabo->id_silabo)
                ->orderByRaw('CAST(semana AS UNSIGNED) ASC')
                ->get()
                ->toArray();

            $evaluaciones = DB::table('silabo_evaluacion')
                ->where('id_silabo', $silabo->id_silabo)
                ->orderBy('id_evaluacion', 'asc')
                ->get()
                ->toArray();

            $rubricas = DB::table('silabo_rubricas')
                ->where('id_silabo', $silabo->id_silabo)
                ->orderBy('codigo', 'asc')
                ->get()
                ->toArray();
        }

        // Auto-llenar 15 semanas si está vacío
        if (empty($cronograma)) {
            $fecha_inicio = $curso->fecha_inicio ? $curso->fecha_inicio->format('Y-m-d') : date('Y-m-d');
            $horario_dia = explode(' ', trim($curso->horario))[0] ?? 'Lunes';
            
            $dias_map = [
                'Lunes' => 'Monday', 'Martes' => 'Tuesday', 'Miércoles' => 'Wednesday', 'Miercoles' => 'Wednesday',
                'Jueves' => 'Thursday', 'Viernes' => 'Friday', 'Sábado' => 'Saturday', 'Sabado' => 'Saturday', 'Domingo' => 'Sunday'
            ];
            $dia_ingles = $dias_map[$horario_dia] ?? 'Monday';

            $start_date = new Carbon($fecha_inicio);
            if ($start_date->format('l') !== $dia_ingles) {
                $start_date->modify("next $dia_ingles");
            }

            for ($w = 1; $w <= 15; $w++) {
                $cronograma[] = (object)[
                    'id_cronograma' => -$w,
                    'semana' => (string)$w,
                    'fecha' => $start_date->format('Y-m-d'),
                    'actividad' => '',
                    'tareas' => '',
                    'modalidad_trabajo' => 'Asincrónico',
                    'actividades_moodle' => ''
                ];
                $start_date->addWeek();
            }
        }

        // Auto-llenar evaluaciones si está vacío
        if (empty($evaluaciones)) {
            $evaluaciones = [
                (object)['id_evaluacion' => -1, 'rubro' => 'Asistencia y Participación', 'porcentaje' => 10, 'cantidad' => 1, 'anexo' => '', 'tipo_moodle' => ''],
                (object)['id_evaluacion' => -2, 'rubro' => 'Tareas y Foros', 'porcentaje' => 20, 'cantidad' => 2, 'anexo' => '', 'tipo_moodle' => 'assign'],
                (object)['id_evaluacion' => -3, 'rubro' => 'Examen Parcial I', 'porcentaje' => 20, 'cantidad' => 1, 'anexo' => '', 'tipo_moodle' => 'quiz'],
                (object)['id_evaluacion' => -4, 'rubro' => 'Examen Parcial II', 'porcentaje' => 20, 'cantidad' => 1, 'anexo' => '', 'tipo_moodle' => 'quiz'],
                (object)['id_evaluacion' => -5, 'rubro' => 'Proyecto Final', 'porcentaje' => 30, 'cantidad' => 1, 'anexo' => '', 'tipo_moodle' => '']
            ];
        }

        $revisiones_pendientes = DB::table('revisiones_curso')
            ->where('id_curso_activo', $id)
            ->where('estado', 'pendiente')
            ->count();

        return view('mis_cursos.gestionar_silabo', compact('curso', 'silabo', 'cronograma', 'evaluaciones', 'rubricas', 'revisiones_pendientes', 'es_admin'));
    }

    /**
     * Guarda el sílabo, cronograma y rúbricas.
     */
    public function guardarSilabo(Request $request, $id)
    {
        $curso = CursoActivo::findOrFail($id);

        DB::beginTransaction();
        try {
            // A. Buscar o crear el Sílabo (bajo el ID del profesor del curso activo para evitar duplicados del administrador)
            $target_profesor = $curso->id_profesor ?? Auth::id();
            $silabo = DB::table('silabos')
                ->where('id_plan', $curso->id_plan)
                ->where('id_profesor', $target_profesor)
                ->first();

            $desc = $request->input('descripcion');
            $metodo = $request->input('metodologia');
            $estrategias = $request->input('estrategias_aprendizaje', '');
            $recursos_apr = $request->input('recursos_aprendizaje', '');
            $contenidos = $request->input('contenidos');
            $cronograma_txt = $request->input('cronograma_txt', '');
            $biblio = $request->input('bibliografia');
            $mod = $request->input('modalidad', 'Virtual');
            $tipo_curso = $request->input('tipo_curso', '');
            $obj_gen_post = $request->input('objetivo_general', '');
            $obj_esp_post = $request->input('objetivos_especificos', '');
            $anexos = $request->input('anexos', '');
            
            $h_dia = $request->input('horario_dia', '');
            $h_inicio = $request->input('horario_hora_inicio', '');
            $h_fin = $request->input('horario_hora_fin', '');
            $horario = trim("$h_dia $h_inicio - $h_fin");

            $silaboData = [
                'descripcion' => $desc,
                'metodologia' => $metodo,
                'estrategias_aprendizaje' => $estrategias,
                'recursos_aprendizaje' => $recursos_apr,
                'contenidos' => $contenidos,
                'cronograma' => $cronograma_txt,
                'bibliografia' => $biblio,
                'modalidad' => $mod,
                'horario' => $horario,
                'tipo_curso' => $tipo_curso,
                'objetivo_general' => $obj_gen_post,
                'objetivos_especificos' => $obj_esp_post,
                'anexos' => $anexos,
                'id_plan' => $curso->id_plan,
                'id_profesor' => $target_profesor
            ];

            if ($silabo) {
                DB::table('silabos')->where('id_silabo', $silabo->id_silabo)->update($silaboData);
                $silabo_id = $silabo->id_silabo;
            } else {
                $silaboData['fecha_creacion'] = Carbon::now();
                $silabo_id = DB::table('silabos')->insertGetId($silaboData);
            }

            // B. Guardar Evaluación
            DB::table('silabo_evaluacion')->where('id_silabo', $silabo_id)->delete();
            $rubros = $request->input('rubros', []);
            $porcentajes = $request->input('porcentajes', []);
            $cantidades_eval = $request->input('cantidades_eval', []);
            $tipos_moodle = $request->input('tipos_moodle_eval', []);
            $anexos_eval = $request->input('anexos_eval', []);

            foreach ($rubros as $k => $nom) {
                if (!empty($nom)) {
                    DB::table('silabo_evaluacion')->insert([
                        'id_silabo' => $silabo_id,
                        'rubro' => $nom,
                        'porcentaje' => floatval($porcentajes[$k] ?? 0),
                        'cantidad' => intval($cantidades_eval[$k] ?? 1),
                        'anexo' => $anexos_eval[$k] ?? '',
                        'tipo_moodle' => $tipos_moodle[$k] ?? ''
                    ]);
                }
            }

            // C. Guardar Rúbricas
            DB::table('silabo_rubricas')->where('id_silabo', $silabo_id)->delete();
            $r_nombres_sel = $request->input('rubrica_nombre_sel', []);
            $r_nombres_custom = $request->input('rubrica_nombre_custom', []);
            $r_porcentajes = $request->input('rubrica_porcentaje', []);
            $r_cantidades = $request->input('rubrica_cantidad', []);
            $r_valores = $request->input('rubrica_valor', []);
            $r_json = $request->input('rubrica_json', []);

            if (is_array($r_nombres_sel)) {
                foreach ($r_nombres_sel as $k => $sel_val) {
                    $nom = ($sel_val === "CUSTOM") ? ($r_nombres_custom[$k] ?? '') : $sel_val;
                    $js = $r_json[$k] ?? '';
                    if (!empty($nom) && !empty($js)) {
                        DB::table('silabo_rubricas')->insert([
                            'id_silabo' => $silabo_id,
                            'codigo' => 'R' . ($k + 1),
                            'nombre' => $nom,
                            'porcentaje_total' => floatval($r_porcentajes[$k] ?? 0),
                            'cantidad' => intval($r_cantidades[$k] ?? 1),
                            'valor' => floatval($r_valores[$k] ?? 0),
                            'contenido_json' => $js
                        ]);
                    }
                }
            }

            // D. Guardar Cronograma
            DB::table('silabo_cronograma')->where('id_silabo', $silabo_id)->delete();
            $semanas = $request->input('c_semana', []);
            $fechas = $request->input('c_fecha', []);
            $actividades = $request->input('c_actividad', []);
            $tareas = $request->input('c_tareas', []);
            $modalidades = $request->input('c_modalidad', []);
            $moodle = $request->input('c_moodle', []);

            if (is_array($semanas)) {
                foreach ($semanas as $k => $sem) {
                    DB::table('silabo_cronograma')->insert([
                        'id_silabo' => $silabo_id,
                        'semana' => $sem,
                        'fecha' => $fechas[$k] ?? '',
                        'actividad' => $actividades[$k] ?? '',
                        'tareas' => $tareas[$k] ?? '',
                        'modalidad_trabajo' => $modalidades[$k] ?? 'Asincrónico',
                        'actividades_moodle' => $moodle[$k] ?? ''
                    ]);
                }
            }

            // E. Marcar hitos en el curso
            $curso->update([
                'check_listo' => 1
            ]);

            DB::commit();
            
            $activeTab = $request->input('active_tab', 'general');
            return redirect()->route('mis_cursos.gestionar_silabo', [$id, 'tab' => $activeTab])
                ->with('success', 'Sílabo y plan de evaluación actualizados correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('mis_cursos.gestionar_silabo', $id)->with('error', 'Error al guardar el sílabo: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la pantalla para gestionar recursos de Drive.
     */
    public function verRecursosDrive($id)
    {
        $es_admin = (Auth::user()->id_rol == 1);
        $usuario_id = Auth::id();
        $query = CursoActivo::with('planEstudio.programa', 'profesor');
        if (!$es_admin) {
            $query->where('id_profesor', $usuario_id);
        }
        $curso = $query->findOrFail($id);

        $recursos = DB::table('recursos_drive')
            ->where('id_curso_activo', $id)
            ->orderBy('fecha_subida', 'desc')
            ->get();

        return view('mis_cursos.recursos_drive', compact('curso', 'recursos', 'es_admin'));
    }

    /**
     * Sube y vincula un recurso.
     */
    public function guardarRecursoDrive(Request $request, $id)
    {
        $request->validate([
            'archivo_drive' => 'required|file|max:20480', // max 20MB
        ]);

        $curso = CursoActivo::findOrFail($id);

        if ($request->hasFile('archivo_drive')) {
            $file = $request->file('archivo_drive');
            $originalName = $file->getClientOriginalName();
            
            // Store the file locally in public storage under recursos_drive
            $filename = time() . '_' . str_replace(' ', '_', $originalName);
            $path = $file->storeAs('recursos_drive', $filename, 'public');

            $linkPublico = asset('storage/' . $path);
            $es_compartido = $request->has('es_compartido') ? 1 : 0;

            DB::table('recursos_drive')->insert([
                'id_curso_activo' => $curso->id_curso_activo,
                'nombre_archivo' => $originalName,
                'id_drive' => md5($filename), // Dummy drive id
                'link_publico' => $linkPublico,
                'es_compartido' => $es_compartido,
                'fecha_subida' => Carbon::now()
            ]);

            return redirect()->route('mis_cursos.recursos_drive', $id)->with('success', 'Archivo subido y vinculado correctamente.');
        }

        return redirect()->route('mis_cursos.recursos_drive', $id)->with('error', 'No se ha seleccionado ningún archivo.');
    }

    /**
     * Elimina el recurso del curso.
     */
    public function eliminarRecursoDrive($id, $recurso_id)
    {
        $recurso = DB::table('recursos_drive')
            ->where('id', $recurso_id)
            ->where('id_curso_activo', $id)
            ->first();

        if ($recurso) {
            // Delete record from DB
            DB::table('recursos_drive')->where('id', $recurso_id)->delete();
            
            // Try to delete local file if it exists
            $relativePath = str_replace(asset('storage/'), '', $recurso->link_publico);
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($relativePath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($relativePath);
            }

            return redirect()->route('mis_cursos.recursos_drive', $id)->with('success', 'Recurso eliminado correctamente.');
        }

        return redirect()->route('mis_cursos.recursos_drive', $id)->with('error', 'Recurso no encontrado.');
    }

    /**
     * Genera y transmite el Sílabo Académico Oficial en PDF.
     */
    public function verSilaboPdf($id)
    {
        return \App\Services\SilaboPdfService::generar((int)$id);
    }

    /**
     * Genera y transmite el Acta Oficial de Calificaciones en PDF apaisado.
     */
    public function generarActaPdf($id)
    {
        return \App\Services\ActaOficialPdfService::generar((int)$id);
    }
}
