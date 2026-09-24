<?php

namespace App\Http\Controllers;

use App\Models\CursoActivo;
use App\Models\EncuestaPregunta;
use App\Models\EncuestaOpcion;
use App\Models\EncuestaSesion;
use App\Models\EncuestaRespuesta;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cookie;
use Carbon\Carbon;

class EncuestaController extends Controller
{
    /**
     * Muestra el formulario público para responder la encuesta de un curso.
     * Acceso público sin requerir inicio de sesión.
     */
    public function responder(Request $request, $id_curso)
    {
        $id_curso = intval($id_curso);
        $cookie_key = 'enc_resp_' . $id_curso;
        $session_key = 'enc_resp_' . $id_curso;

        $ya_respondio = ($id_curso > 0) && (
            $request->session()->has($session_key) ||
            $request->hasCookie($cookie_key)
        );

        $curso = CursoActivo::with(['planEstudio.programa', 'profesor'])->find($id_curso);

        $moodle_url = config('services.moodle.url', 'https://unela.ac.cr/virtual');
        $moodle_course_id = intval($curso->id_moodle ?? 0);
        $moodle_return_url = $moodle_course_id > 0
            ? rtrim($moodle_url, '/') . '/course/view.php?id=' . $moodle_course_id
            : rtrim($moodle_url, '/');

        // Cargar preguntas activas
        $preguntas_inst = EncuestaPregunta::with('opciones')
            ->where('activo', 1)
            ->where('categoria', 'institucion')
            ->orderBy('orden', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $preguntas_doc = EncuestaPregunta::with('opciones')
            ->where('activo', 1)
            ->where('categoria', 'docente')
            ->orderBy('orden', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $tiene_preguntas = ($preguntas_inst->isNotEmpty() || $preguntas_doc->isNotEmpty());

        return view('encuesta.responder', compact(
            'curso', 'id_curso', 'ya_respondio', 'moodle_return_url',
            'preguntas_inst', 'preguntas_doc', 'tiene_preguntas'
        ));
    }

    /**
     * Procesa y almacena las respuestas de la encuesta enviadas por un estudiante.
     */
    public function procesarRespuesta(Request $request)
    {
        $id_curso = intval($request->input('id_curso', 0));
        $comentarios = trim($request->input('comentarios', ''));
        $resp_raw = $request->input('resp', []);
        $ip_origen = $request->ip();

        if ($id_curso <= 0) {
            return response()->json(['success' => false, 'message' => 'ID de curso inválido.'], 400);
        }

        $curso = CursoActivo::find($id_curso);
        if (!$curso) {
            return response()->json(['success' => false, 'message' => 'Curso no encontrado.'], 404);
        }

        if (empty($resp_raw) || !is_array($resp_raw)) {
            return response()->json(['success' => false, 'message' => 'No se recibieron respuestas de la encuesta.'], 422);
        }

        // Cargar preguntas activas y sus opciones válidas
        $preguntas_activas = EncuestaPregunta::with('opciones')
            ->where('activo', 1)
            ->get()
            ->keyBy('id');

        if ($preguntas_activas->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No hay preguntas activas configuradas en el sistema.'], 400);
        }

        $respuestas_validadas = [];
        foreach ($preguntas_activas as $pid => $pregunta) {
            $id_opcion = intval($resp_raw[$pid] ?? 0);
            $opcion_valida = $pregunta->opciones->firstWhere('id', $id_opcion);

            if (!$opcion_valida) {
                return response()->json([
                    'success' => false,
                    'message' => "Por favor responda todas las preguntas antes de enviar."
                ], 422);
            }

            $respuestas_validadas[] = [
                'id_pregunta' => $pid,
                'id_opcion' => $id_opcion,
                'puntaje_obtenido' => intval($opcion_valida->puntaje)
            ];
        }

        DB::beginTransaction();
        try {
            // 1. Crear sesión de encuesta
            $sesion = EncuestaSesion::create([
                'id_curso_activo' => $id_curso,
                'ip_origen' => $ip_origen,
                'comentarios' => $comentarios,
                'fecha_envio' => Carbon::now()
            ]);

            // 2. Insertar respuestas
            foreach ($respuestas_validadas as $resp) {
                EncuestaRespuesta::create([
                    'id_sesion' => $sesion->id,
                    'id_pregunta' => $resp['id_pregunta'],
                    'id_opcion' => $resp['id_opcion'],
                    'puntaje_obtenido' => $resp['puntaje_obtenido']
                ]);
            }

            DB::commit();

            // Marcar sesión y cookie
            $session_key = 'enc_resp_' . $id_curso;
            $request->session()->put($session_key, true);

            $moodle_url = config('services.moodle.url', 'https://unela.ac.cr/virtual');
            $moodle_course_id = intval($curso->id_moodle ?? 0);
            $return_url = $moodle_course_id > 0
                ? rtrim($moodle_url, '/') . '/course/view.php?id=' . $moodle_course_id
                : rtrim($moodle_url, '/');

            $cookie = cookie('enc_resp_' . $id_curso, '1', 60 * 24 * 30); // 30 días

            return response()->json([
                'success' => true,
                'message' => '¡Muchas gracias! Tu evaluación ha sido registrada de forma 100% anónima.',
                'return_url' => $return_url
            ])->withCookie($cookie);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la encuesta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra el panel de administración para gestionar preguntas de encuesta.
     */
    public function gestionarPreguntas(Request $request)
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'Acceso denegado.');
        }

        $preguntas = EncuestaPregunta::with('opciones')
            ->orderBy('orden', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $total = $preguntas->count();
        $total_inst = $preguntas->where('categoria', 'institucion')->count();
        $total_doc = $preguntas->where('categoria', 'docente')->count();
        $total_activas = $preguntas->where('activo', 1)->count();

        return view('encuesta.gestionar_preguntas', compact(
            'preguntas', 'total', 'total_inst', 'total_doc', 'total_activas'
        ));
    }

    /**
     * Maneja operaciones AJAX para preguntas (crear, actualizar, eliminar, toggle, reordenar).
     */
    public function ajaxPreguntas(Request $request)
    {
        if (Auth::user()->id_rol != 1) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $action = $request->input('action', '');

        switch ($action) {
            case 'listar':
                $preguntas = EncuestaPregunta::with('opciones')
                    ->orderBy('orden', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();
                return response()->json(['success' => true, 'preguntas' => $preguntas]);

            case 'crear':
                $texto = trim($request->input('texto', ''));
                $categoria = in_array($request->input('categoria'), ['institucion', 'docente']) ? $request->input('categoria') : 'docente';

                if (empty($texto)) {
                    return response()->json(['success' => false, 'message' => 'El texto de la pregunta es obligatorio.']);
                }

                $next_ord = intval(EncuestaPregunta::max('orden')) + 1;

                $pregunta = EncuestaPregunta::create([
                    'texto' => $texto,
                    'categoria' => $categoria,
                    'orden' => $next_ord,
                    'activo' => 1
                ]);

                // Opciones por defecto
                $opciones_default = [
                    ['texto' => 'Malo', 'emoji' => '😞', 'puntaje' => 25, 'orden' => 1],
                    ['texto' => 'Regular', 'emoji' => '😐', 'puntaje' => 50, 'orden' => 2],
                    ['texto' => 'Bueno', 'emoji' => '🙂', 'puntaje' => 75, 'orden' => 3],
                    ['texto' => 'Excelente', 'emoji' => '😃', 'puntaje' => 100, 'orden' => 4],
                ];

                foreach ($opciones_default as $op) {
                    EncuestaOpcion::create([
                        'id_pregunta' => $pregunta->id,
                        'texto' => $op['texto'],
                        'emoji' => $op['emoji'],
                        'puntaje' => $op['puntaje'],
                        'orden' => $op['orden']
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'id' => $pregunta->id,
                    'message' => 'Pregunta creada con éxito.'
                ]);

            case 'actualizar':
                $id = intval($request->input('id', 0));
                $texto = trim($request->input('texto', ''));
                $categoria = in_array($request->input('categoria'), ['institucion', 'docente']) ? $request->input('categoria') : 'docente';
                $opciones = $request->input('opciones', []);

                $pregunta = EncuestaPregunta::find($id);
                if (!$pregunta || empty($texto)) {
                    return response()->json(['success' => false, 'message' => 'Pregunta no encontrada o datos incompletos.']);
                }

                $pregunta->update([
                    'texto' => $texto,
                    'categoria' => $categoria
                ]);

                if (is_array($opciones) && count($opciones) > 0) {
                    foreach ($opciones as $op) {
                        $op_id = intval($op['id'] ?? 0);
                        if ($op_id > 0) {
                            EncuestaOpcion::where('id', $op_id)
                                ->where('id_pregunta', $id)
                                ->update([
                                    'texto' => trim($op['texto'] ?? ''),
                                    'emoji' => trim($op['emoji'] ?? ''),
                                    'puntaje' => intval($op['puntaje'] ?? 0)
                                ]);
                        }
                    }
                }

                return response()->json(['success' => true, 'message' => 'Pregunta actualizada correctamente.']);

            case 'eliminar':
                $id = intval($request->input('id', 0));
                $pregunta = EncuestaPregunta::find($id);
                if ($pregunta) {
                    EncuestaOpcion::where('id_pregunta', $id)->delete();
                    $pregunta->delete();
                    return response()->json(['success' => true, 'message' => 'Pregunta eliminada con éxito.']);
                }
                return response()->json(['success' => false, 'message' => 'Pregunta no encontrada.']);

            case 'toggle_activo':
                $id = intval($request->input('id', 0));
                $pregunta = EncuestaPregunta::find($id);
                if ($pregunta) {
                    $nuevo_estado = $pregunta->activo ? 0 : 1;
                    $pregunta->update(['activo' => $nuevo_estado]);
                    return response()->json(['success' => true, 'activo' => $nuevo_estado]);
                }
                return response()->json(['success' => false, 'message' => 'Pregunta no encontrada.']);

            case 'reordenar':
                $orden = $request->input('orden', []);
                if (is_array($orden)) {
                    foreach ($orden as $pos => $pid) {
                        EncuestaPregunta::where('id', intval($pid))->update(['orden' => $pos + 1]);
                    }
                    return response()->json(['success' => true, 'message' => 'Orden actualizado.']);
                }
                return response()->json(['success' => false, 'message' => 'Datos de orden no válidos.']);

            default:
                return response()->json(['success' => false, 'message' => 'Acción no reconocida.']);
        }
    }

    /**
     * Muestra el panel de métricas, promedios y resultados de las encuestas evaluadas.
     */
    public function resultados(Request $request)
    {
        $es_admin = (Auth::user()->id_rol == 1);
        $usuario_id = Auth::id();

        $id_curso = intval($request->input('id_curso', 0));

        // Cargar lista de cursos con encuestas respondidas
        $queryCursos = DB::table('encuesta_sesiones as es')
            ->join('cursos_activos as ca', 'es.id_curso_activo', '=', 'ca.id_curso_activo')
            ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->join('programas as p', 'pe.id_programa', '=', 'p.id_programa')
            ->leftJoin('usuarios as u', 'ca.id_profesor', '=', 'u.id')
            ->select(
                'es.id_curso_activo',
                'pe.materia',
                'p.nombre_programa',
                'ca.periodo',
                'u.nombre as nombre_prof',
                'u.apellidos as apellidos_prof',
                DB::raw('COUNT(DISTINCT es.id) as total_respuestas'),
                DB::raw('MAX(es.fecha_envio) as ultima_respuesta')
            )
            ->groupBy('es.id_curso_activo', 'pe.materia', 'p.nombre_programa', 'ca.periodo', 'u.nombre', 'u.apellidos');

        if (!$es_admin) {
            $queryCursos->where('ca.id_profesor', $usuario_id);
        }

        $cursos_con_encuesta = $queryCursos->orderBy('ultima_respuesta', 'desc')->get();

        if ($id_curso <= 0 && $cursos_con_encuesta->isNotEmpty()) {
            $id_curso = $cursos_con_encuesta->first()->id_curso_activo;
        }

        $curso_detalle = null;
        $total_sesiones = 0;
        $resultados_inst = [];
        $resultados_doc = [];
        $comentarios = [];
        $promedio_inst = 0;
        $promedio_doc = 0;
        $promedio_global = 0;

        if ($id_curso > 0) {
            $cursoQuery = CursoActivo::with(['planEstudio.programa', 'profesor']);
            if (!$es_admin) {
                $cursoQuery->where('id_profesor', $usuario_id);
            }
            $curso_detalle = $cursoQuery->find($id_curso);

            if ($curso_detalle) {
                $total_sesiones = EncuestaSesion::where('id_curso_activo', $id_curso)->count();

                if ($total_sesiones > 0) {
                    $res_prom = DB::table('encuesta_preguntas as ep')
                        ->join('encuesta_respuestas as er', 'er.id_pregunta', '=', 'ep.id')
                        ->join('encuesta_sesiones as es', 'er.id_sesion', '=', 'es.id')
                        ->where('es.id_curso_activo', $id_curso)
                        ->where('ep.activo', 1)
                        ->select(
                            'ep.id', 'ep.texto', 'ep.categoria', 'ep.orden',
                            DB::raw('ROUND(AVG(er.puntaje_obtenido), 1) as promedio'),
                            DB::raw('COUNT(er.id) as votos')
                        )
                        ->groupBy('ep.id', 'ep.texto', 'ep.categoria', 'ep.orden')
                        ->orderBy('ep.orden', 'asc')
                        ->orderBy('ep.id', 'asc')
                        ->get();

                    foreach ($res_prom as $row) {
                        $entry = [
                            'id' => $row->id,
                            'texto' => $row->texto,
                            'categoria' => $row->categoria,
                            'promedio' => floatval($row->promedio),
                            'votos' => intval($row->votos),
                            'pct' => floatval($row->promedio),
                        ];

                        if ($row->categoria === 'institucion') {
                            $resultados_inst[] = $entry;
                        } else {
                            $resultados_doc[] = $entry;
                        }
                    }

                    if (!empty($resultados_inst)) {
                        $promedio_inst = round(collect($resultados_inst)->avg('promedio'), 1);
                    }
                    if (!empty($resultados_doc)) {
                        $promedio_doc = round(collect($resultados_doc)->avg('promedio'), 1);
                    }
                    if ($res_prom->isNotEmpty()) {
                        $promedio_global = round($res_prom->avg('promedio'), 1);
                    }

                    $comentarios = EncuestaSesion::where('id_curso_activo', $id_curso)
                        ->whereNotNull('comentarios')
                        ->where('comentarios', '!=', '')
                        ->orderBy('fecha_envio', 'desc')
                        ->get();
                }
            }
        }

        return view('encuesta.resultados', compact(
            'cursos_con_encuesta', 'id_curso', 'curso_detalle',
            'total_sesiones', 'resultados_inst', 'resultados_doc',
            'promedio_inst', 'promedio_doc', 'promedio_global', 'comentarios'
        ));
    }

    /**
     * Publica el enlace de la encuesta como actividad en Moodle Bridge.
     */
    public function publicarMoodle(Request $request, $id_curso)
    {
        if (Auth::user()->id_rol != 1) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $curso = CursoActivo::with('planEstudio')->findOrFail($id_curso);

        $id_moodle = intval($curso->id_moodle ?? 0);
        if ($id_moodle <= 0) {
            return response()->json(['success' => false, 'message' => 'Este curso no está sincronizado con Moodle aún.'], 400);
        }

        $url_encuesta = url('/encuesta/' . $id_curso);
        $nombre_encuesta = "📋 Encuesta de Evaluación Docente — " . ($curso->planEstudio->materia ?? 'Curso');
        $intro_encuesta = '<div style="font-family:sans-serif;padding:14px 18px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border-left:5px solid #16a34a;border-radius:10px;margin-bottom:6px;"><p style="margin:0 0 6px;font-size:14px;color:#166534;">📋 <strong>Encuesta de Evaluación Docente</strong></p><p style="margin:0;font-size:13px;color:#15803d;">Estimado estudiante, haz clic en el <strong>enlace de arriba</strong> para completar la <strong>encuesta de evaluación docente</strong> de forma anónima. Tu opinión es muy importante para mejorar la calidad académica de UNELA. ¡Gracias por participar!</p></div>';

        try {
            $response = Http::withoutVerifying()->timeout(30)->post('https://unela.ac.cr/virtual/webservice/moodle_bridge.php', [
                'token' => 'ef5bde9afb1fdd026330058ec405a30e',
                'action' => 'create_act',
                'courseid' => $id_moodle,
                'semana' => 0,
                'modname' => 'url',
                'name' => $nombre_encuesta,
                'intro' => $intro_encuesta,
                'externalurl' => $url_encuesta,
            ]);

            $res = $response->json();

            $curso->update(['check_encuesta' => 1]);

            return response()->json([
                'success' => true,
                'message' => '¡Encuesta de evaluación docente publicada y vinculada con éxito en Moodle!',
                'url_encuesta' => $url_encuesta,
                'moodle_response' => $res
            ]);
        } catch (\Throwable $e) {
            $curso->update(['check_encuesta' => 1]);
            return response()->json([
                'success' => true,
                'warning' => true,
                'message' => 'Encuesta activada en el sistema local, pero hubo un detalle de red con Moodle: ' . $e->getMessage(),
                'url_encuesta' => $url_encuesta
            ]);
        }
    }

    /**
     * Reinicia las respuestas de la encuesta para un curso o restablece permisos de prueba.
     */
    public function resetEncuesta(Request $request, $id_curso)
    {
        if (Auth::user()->id_rol != 1) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $session_key = 'enc_resp_' . $id_curso;
        $request->session()->forget($session_key);

        return response()->json([
            'success' => true,
            'message' => 'Estado de respuesta reiniciado para este navegador.'
        ])->withoutCookie('enc_resp_' . $id_curso);
    }
}
