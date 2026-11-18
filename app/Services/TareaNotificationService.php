<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TareaNotificationService
{
    /**
     * Envía notificaciones por correo electrónico al crear o asignar una tarea.
     *
     * @param  int         $idTarea
     * @param  array       $asignadosIds
     * @param  object|null $creador
     * @return array{sent: int, recipients: array<int, string>, details: array<int, array{nombre: string, email: string, tipo: string}>, errors: array<int, string>}
     */
    public static function notificarCreacion(int $idTarea, array $asignadosIds = [], ?object $creador = null): array
    {
        $sent = 0;
        $recipients = [];
        $details = [];
        $errors = [];

        $tarea = DB::table('tareas')->where('id', $idTarea)->first();
        if (!$tarea) {
            return compact('sent', 'recipients', 'details', 'errors');
        }

        $asignadosIds = array_values(array_unique(array_filter(array_map('intval', $asignadosIds))));
        $asignados = !empty($asignadosIds)
            ? DB::table('usuarios')->whereIn('id', $asignadosIds)->get()
            : collect();

        $nombresAsignados = $asignados->map(function ($u) {
            return trim(($u->nombre ?? '') . ' ' . ($u->apellidos ?? ''));
        })->filter()->values()->all();

        $nombreCreador = $creador
            ? trim(($creador->nombre ?? '') . ' ' . ($creador->apellidos ?? ''))
            : 'Sistema';

        $cursoNombre = null;
        if (!empty($tarea->id_curso_activo)) {
            $curso = DB::table('cursos_activos as ca')
                ->leftJoin('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
                ->where('ca.id_curso_activo', $tarea->id_curso_activo)
                ->select('pe.materia', 'ca.periodo')
                ->first();
            if ($curso && !empty($curso->materia)) {
                $cursoNombre = $curso->materia . (!empty($curso->periodo) ? " ({$curso->periodo})" : '');
            }
        }

        $institucion = ClienteService::nombre();
        $dashboardUrl = route('dashboard');

        // Enviar a cada responsable asignado
        $emailsEnviados = [];
        foreach ($asignados as $usuario) {
            $email = trim((string)($usuario->email ?? ''));
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            if (in_array(strtolower($email), $emailsEnviados, true)) {
                continue;
            }

            $nombreDestino = trim(($usuario->nombre ?? '') . ' ' . ($usuario->apellidos ?? ''));
            $asunto = "Nueva Tarea Asignada: {$tarea->titulo} | {$institucion}";
            $html = self::buildEmailHtml(
                $tarea,
                $institucion,
                $nombreDestino,
                $nombreCreador,
                $nombresAsignados,
                $cursoNombre,
                $dashboardUrl,
                false
            );

            try {
                Mail::html($html, function ($message) use ($email, $nombreDestino, $asunto) {
                    $message->to($email, $nombreDestino ?: null)->subject($asunto);
                });
                $emailsEnviados[] = strtolower($email);
                $recipients[] = $email;
                $details[] = [
                    'nombre' => $nombreDestino ?: $email,
                    'email'  => $email,
                    'tipo'   => 'asignado',
                ];
                $sent++;
            } catch (\Throwable $e) {
                $errors[] = "{$email}: " . $e->getMessage();
                Log::error("Error enviando notificación de tarea #{$idTarea} a {$email}: " . $e->getMessage());
            }
        }

        // Enviar confirmación al creador si tiene correo válido y no recibió ya como asignado
        $emailCreador = $creador ? trim((string)($creador->email ?? '')) : '';
        if (!empty($emailCreador) && filter_var($emailCreador, FILTER_VALIDATE_EMAIL) && !in_array(strtolower($emailCreador), $emailsEnviados, true)) {
            $asuntoCreador = "Tarea Creada: {$tarea->titulo} | {$institucion}";
            $htmlCreador = self::buildEmailHtml(
                $tarea,
                $institucion,
                $nombreCreador,
                $nombreCreador,
                $nombresAsignados,
                $cursoNombre,
                $dashboardUrl,
                true
            );

            try {
                Mail::html($htmlCreador, function ($message) use ($emailCreador, $nombreCreador, $asuntoCreador) {
                    $message->to($emailCreador, $nombreCreador ?: null)->subject($asuntoCreador);
                });
                $recipients[] = $emailCreador;
                $details[] = [
                    'nombre' => $nombreCreador ?: $emailCreador,
                    'email'  => $emailCreador,
                    'tipo'   => 'creador',
                ];
                $sent++;
            } catch (\Throwable $e) {
                $errors[] = "{$emailCreador}: " . $e->getMessage();
                Log::error("Error enviando confirmación de tarea #{$idTarea} al creador {$emailCreador}: " . $e->getMessage());
            }
        }

        return compact('sent', 'recipients', 'details', 'errors');
    }

    /**
     * Construye el cuerpo HTML institucional para la notificación de tarea.
     */
    protected static function buildEmailHtml(
        object $tarea,
        string $institucion,
        string $nombreDestino,
        string $nombreCreador,
        array $nombresAsignados,
        ?string $cursoNombre,
        string $dashboardUrl,
        bool $esConfirmacionCreador
    ): string {
        $prioridad = strtolower((string)($tarea->prioridad ?? 'media'));
        $prioridadMap = [
            'alta'  => ['label' => 'ALTA (Urgente)', 'bg' => '#fee2e2', 'color' => '#991b1b'],
            'media' => ['label' => 'MEDIA (Normal)', 'bg' => '#fef3c7', 'color' => '#92400e'],
            'baja'  => ['label' => 'BAJA',           'bg' => '#dcfce7', 'color' => '#166534'],
        ];
        $prio = $prioridadMap[$prioridad] ?? $prioridadMap['media'];

        $encabezadoTitulo = $esConfirmacionCreador
            ? 'Confirmación de Tarea Creada'
            : 'Se te ha asignado una nueva tarea';

        $saludo = $nombreDestino !== '' ? "Hola <strong>" . e($nombreDestino) . "</strong>," : "Hola,";
        $intro = $esConfirmacionCreador
            ? "Tu tarea ha sido registrada correctamente en la plataforma <strong>" . e($institucion) . "</strong>."
            : "<strong>" . e($nombreCreador) . "</strong> te ha asignado una nueva tarea en <strong>" . e($institucion) . "</strong>.";

        $descripcion = trim((string)($tarea->descripcion ?? ''));
        $descripcionHtml = $descripcion !== ''
            ? nl2br(e($descripcion))
            : '<span style="color:#64748b;font-style:italic;">Sin descripción adicional.</span>';

        $fechaInicio = !empty($tarea->fecha_creacion) ? date('d/m/Y', strtotime($tarea->fecha_creacion)) : date('d/m/Y');
        $fechaVenc = !empty($tarea->fecha_vencimiento) ? date('d/m/Y', strtotime($tarea->fecha_vencimiento)) : 'Sin fecha límite';
        $asignadosTexto = !empty($nombresAsignados) ? e(implode(', ', $nombresAsignados)) : 'Sin responsables asignados';
        $cursoFila = $cursoNombre
            ? "<tr><td style='padding:6px 0;color:#64748b;width:150px;'>Curso asociado:</td><td style='padding:6px 0;color:#0f172a;font-weight:600;'>" . e($cursoNombre) . "</td></tr>"
            : '';

        return "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f1f5f9;padding:24px;'>
            <div style='max-width:620px;margin:0 auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;box-shadow:0 4px 16px rgba(15,23,42,0.06);'>
                <div style='background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);padding:22px 28px;color:#ffffff;'>
                    <div style='font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#86efac;font-weight:bold;'>" . e($institucion) . " · Gestión de Tareas</div>
                    <h2 style='margin:6px 0 0;font-size:20px;color:#ffffff;'>{$encabezadoTitulo}</h2>
                </div>
                <div style='padding:26px 28px;color:#1e293b;font-size:14px;line-height:1.6;'>
                    <p style='margin-top:0;'>{$saludo}</p>
                    <p>{$intro}</p>

                    <div style='background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #5fb230;border-radius:10px;padding:16px 18px;margin:18px 0;'>
                        <div style='margin-bottom:8px;'>
                            <span style='display:inline-block;background:{$prio['bg']};color:{$prio['color']};font-size:11px;font-weight:bold;padding:3px 10px;border-radius:999px;'>PRIORIDAD {$prio['label']}</span>
                            <span style='display:inline-block;color:#64748b;font-size:12px;margin-left:8px;'>Tarea #{$tarea->id}</span>
                        </div>
                        <h3 style='margin:4px 0 10px;font-size:17px;color:#0f172a;'>" . e($tarea->titulo) . "</h3>
                        <div style='color:#334155;font-size:13.5px;'>{$descripcionHtml}</div>
                    </div>

                    <table style='width:100%;border-collapse:collapse;font-size:13.5px;margin-bottom:20px;'>
                        <tr>
                            <td style='padding:6px 0;color:#64748b;width:150px;'>Fecha de inicio:</td>
                            <td style='padding:6px 0;color:#0f172a;font-weight:600;'>{$fechaInicio}</td>
                        </tr>
                        <tr>
                            <td style='padding:6px 0;color:#64748b;'>Vencimiento:</td>
                            <td style='padding:6px 0;color:#dc2626;font-weight:600;'>{$fechaVenc}</td>
                        </tr>
                        <tr>
                            <td style='padding:6px 0;color:#64748b;'>Creada por:</td>
                            <td style='padding:6px 0;color:#0f172a;font-weight:600;'>" . e($nombreCreador) . "</td>
                        </tr>
                        <tr>
                            <td style='padding:6px 0;color:#64748b;'>Responsables:</td>
                            <td style='padding:6px 0;color:#0f172a;font-weight:600;'>{$asignadosTexto}</td>
                        </tr>
                        {$cursoFila}
                    </table>

                    <div style='text-align:center;margin-top:24px;'>
                        <a href='" . e($dashboardUrl) . "' style='display:inline-block;background:#5fb230;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 26px;border-radius:999px;font-size:14px;'>Ver Tarea en el Dashboard</a>
                    </div>
                </div>
                <div style='background:#f8fafc;padding:14px 28px;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;text-align:center;'>
                    Notificación automática generada por " . e($institucion) . ".
                </div>
            </div>
        </div>";
    }
}
