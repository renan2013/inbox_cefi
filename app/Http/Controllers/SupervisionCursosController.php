<?php

namespace App\Http\Controllers;

use App\Models\CursoActivo;
use App\Models\NotificacionSupervision;
use App\Models\Usuario;
use App\Mail\SupervisionMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SupervisionCursosController extends Controller
{
    /**
     * Muestra la bandeja de supervisión de cursos.
     */
    public function index(Request $request)
    {
        // Verificar que sea admin (Rol 1)
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para supervisar cursos.');
        }

        // Obtener periodos disponibles
        $periodos = CursoActivo::select('periodo')
            ->whereNotNull('periodo')
            ->where('periodo', '!=', '')
            ->distinct()
            ->orderBy('periodo', 'desc')
            ->pluck('periodo')
            ->toArray();

        // Periodo seleccionado
        $periodo_actual = $request->get('periodo', $periodos[0] ?? '');

        // Consulta de cursos supervisados (en_supervision = 1)
        $query_supervisados = CursoActivo::with(['planEstudio.programa', 'profesor'])
            ->where('en_supervision', 1);

        if ($periodo_actual !== 'todos' && !empty($periodo_actual)) {
            $query_supervisados->where('periodo', $periodo_actual);
        }

        $cursos_supervisados = $query_supervisados->get();

        // Lógica de agrupación por día
        $cursos_por_dia = [
            'Lunes' => [], 'Martes' => [], 'Miércoles' => [], 'Jueves' => [], 
            'Viernes' => [], 'Sábado' => [], 'Domingo' => [], 'Otros' => []
        ];

        foreach ($cursos_supervisados as $c) {
            $dia_encontrado = false;
            foreach (array_keys($cursos_por_dia) as $dia) {
                if ($dia === 'Otros') continue;
                if ($c->horario && stripos($c->horario, $dia) !== false) {
                    $cursos_por_dia[$dia][] = $c;
                    $dia_encontrado = true;
                    break;
                }
            }
            if (!$dia_encontrado) {
                $cursos_por_dia['Otros'][] = $c;
            }
        }

        // Selector de cursos disponibles (en_supervision = 0)
        $query_disponibles = CursoActivo::with(['planEstudio.programa', 'profesor'])
            ->where('en_supervision', 0);

        if ($periodo_actual !== 'todos' && !empty($periodo_actual)) {
            $query_disponibles->where('periodo', $periodo_actual);
        }

        $cursos_disponibles = $query_disponibles->get();

        return view('supervision.index', compact(
            'periodos',
            'periodo_actual',
            'cursos_por_dia',
            'cursos_supervisados',
            'cursos_disponibles'
        ));
    }

    /**
     * Activa o desactiva la supervisión de un curso.
     */
    public function toggleSupervision(Request $request)
    {
        if (Auth::user()->id_rol != 1) {
            return response()->json(['success' => false, 'message' => 'No autorizado']);
        }

        $id = $request->input('id');
        $curso = CursoActivo::findOrFail($id);

        $nuevo_estado = ($curso->en_supervision == 1) ? 0 : 1;
        $curso->update(['en_supervision' => $nuevo_estado]);

        return response()->json([
            'success' => true,
            'nuevo_estado' => $nuevo_estado,
            'message' => 'Estado de supervisión actualizado.'
        ]);
    }

    /**
     * Toggles a checklist column (similar to actualizar_estado_curso.php).
     */
    public function toggleCheck(Request $request)
    {
        $id = $request->input('id_curso');
        $tipo = $request->input('tipo');

        $curso = CursoActivo::findOrFail($id);

        // Map column type
        $columnMap = [
            'proceso' => 'check_proceso',
            'listo' => 'check_listo',
            'moodle' => 'check_moodle',
            'estudiantes' => 'check_estudiantes',
            'actividades' => 'check_actividades',
            'calificaciones' => 'check_calificaciones',
            'promedios' => 'check_promedios',
            'acta' => 'check_acta',
            'pago' => 'check_pago',
            'terminado' => 'check_terminado',
            'recursos' => 'check_recursos',
            'portada' => 'check_portada',
        ];

        if (!isset($columnMap[$tipo])) {
            return response()->json(['success' => false, 'message' => 'Tipo de estado inválido']);
        }

        $columna = $columnMap[$tipo];
        $nuevo_estado = ($curso->$columna == 1) ? 0 : 1;

        $curso->update([$columna => $nuevo_estado]);

        return response()->json([
            'success' => true,
            'nuevo_estado' => $nuevo_estado
        ]);
    }

    /**
     * Procesa las notificaciones a profesores.
     */
    public function notificar(Request $request)
    {
        if (Auth::user()->id_rol != 1) {
            return response()->json(['success' => false, 'message' => 'No autorizado']);
        }

        $id_curso = $request->input('id_curso');
        $message = $request->input('message');
        $only_log = $request->input('only_log') === '1';

        $curso = CursoActivo::with(['planEstudio', 'profesor'])->findOrFail($id_curso);

        if ($only_log) {
            // Registrar WhatsApp log
            $curso->update([
                'fecha_notificacion' => Carbon::now(),
                'fecha_whatsapp' => Carbon::now()
            ]);

            NotificacionSupervision::create([
                'id_curso_activo' => $id_curso,
                'tipo' => 'whatsapp',
                'mensaje' => $message,
                'enviado_por' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notificación de WhatsApp registrada correctamente.'
            ]);
        }

        // Email flow
        $profesor = $curso->profesor;
        if (!$profesor || empty($profesor->email)) {
            return response()->json([
                'success' => false,
                'message' => 'El profesor asignado no tiene un correo electrónico válido.'
            ]);
        }

        // Parse WhatsApp formatting to HTML
        $clean_message = $message;
        // Convert *bold* to <strong>bold</strong>
        $clean_message = preg_replace('/\*([^*]+)\*/', '<strong>$1</strong>', $clean_message);

        try {
            Mail::to($profesor->email)->send(new SupervisionMail(
                "Notificación de Supervisión - " . $curso->planEstudio->materia,
                $clean_message,
                $curso->planEstudio->materia
            ));

            $curso->update([
                'fecha_notificacion' => Carbon::now()
            ]);

            NotificacionSupervision::create([
                'id_curso_activo' => $id_curso,
                'tipo' => 'email',
                'mensaje' => $message,
                'enviado_por' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notificación de correo enviada con éxito.'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $th->getMessage()
            ]);
        }
    }

    /**
     * Devuelve el historial de notificaciones.
     */
    public function getHistorialNotificaciones($id)
    {
        $historial = NotificacionSupervision::with('remitente')
            ->where('id_curso_activo', $id)
            ->orderBy('fecha_envio', 'desc')
            ->get();

        $html = '';
        if ($historial->count() > 0) {
            foreach ($historial as $h) {
                $icon = $h->tipo === 'email' ? 'bi-envelope text-primary' : 'bi-whatsapp text-success';
                $badge = $h->tipo === 'email' ? 'bg-primary' : 'bg-success';
                $remitente = $h->remitente->nombre ?? 'Sistema';
                $fecha = $h->fecha_envio->format('d/m/Y g:i a');

                $html .= "
                <div class='mb-3 pb-3 border-bottom border-secondary'>
                    <div class='d-flex justify-content-between align-items-center mb-1'>
                        <span class='badge {$badge}'><i class='bi {$icon} me-1'></i> " . strtoupper($h->tipo) . "</span>
                        <small class='text-white-50'>{$fecha}</small>
                    </div>
                    <p class='text-white mb-1 small' style='white-space: pre-line;'>" . htmlspecialchars($h->mensaje) . "</p>
                    <small class='text-white-50 x-small'>Enviado por: {$remitente}</small>
                </div>";
            }
        } else {
            $html = "<div class='text-center text-white-50 py-3'>No hay notificaciones registradas para este curso.</div>";
        }

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
}
