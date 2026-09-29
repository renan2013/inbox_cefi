<?php

namespace App\Services;

use App\Models\ConfiguracionSistema;
use App\Models\Usuario;
use App\Models\Boleta;
use App\Models\SeguimientoPago;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Normaliza un número telefónico con el código de país (506 para Costa Rica por defecto).
     */
    public static function normalizarTelefono($phone, $defaultPrefix = '506'): string
    {
        $clean = preg_replace('/\D+/', '', (string)$phone);
        if (empty($clean)) {
            return '';
        }

        if (strlen($clean) === 8 && $defaultPrefix === '506') {
            $clean = '506' . $clean;
        }

        return $clean;
    }

    /**
     * Obtiene la configuración activa de WhatsApp y Webhooks de n8n.
     */
    public static function getConfig(): array
    {
        $configMap = ConfiguracionSistema::whereIn('clave', [
            'n8n_webhook_boleta_url',
            'n8n_webhook_recordatorio_url',
            'n8n_webhook_morosidad_url',
            'n8n_webhook_campana_url',
            'n8n_webhook_base_url',
            'evolution_api_url',
            'evolution_api_key',
            'evolution_instance',
            'whatsapp_adjuntar_logo',
            'whatsapp_logo_url',
            'whatsapp_phone'
        ])->pluck('valor', 'clave')->all();

        $clienteCfg = ClienteService::all();

        $baseUrl = !empty($configMap['n8n_webhook_base_url']) 
            ? $configMap['n8n_webhook_base_url'] 
            : ($clienteCfg['n8n']['webhook_base_url'] ?? 'https://n8n.renangalvan.net');

        $boletaWebhook = !empty($configMap['n8n_webhook_boleta_url']) 
            ? $configMap['n8n_webhook_boleta_url'] 
            : ($clienteCfg['n8n']['webhook_boleta'] ?? rtrim($baseUrl, '/') . '/webhook/cefi-boleta');

        $recordatorioWebhook = !empty($configMap['n8n_webhook_recordatorio_url']) 
            ? $configMap['n8n_webhook_recordatorio_url'] 
            : ($clienteCfg['n8n']['webhook_recordatorio'] ?? rtrim($baseUrl, '/') . '/webhook/cefi-recordatorio');

        $morosidadWebhook = !empty($configMap['n8n_webhook_morosidad_url']) 
            ? $configMap['n8n_webhook_morosidad_url'] 
            : ($clienteCfg['n8n']['webhook_morosidad'] ?? rtrim($baseUrl, '/') . '/webhook/cefi-morosidad');

        $campanaWebhook = !empty($configMap['n8n_webhook_campana_url']) 
            ? $configMap['n8n_webhook_campana_url'] 
            : ($clienteCfg['n8n']['webhook_campana'] ?? rtrim($baseUrl, '/') . '/webhook/cefi-campana');

        return [
            'proveedor' => 'n8n',
            'n8n_webhook_base_url' => $baseUrl,
            'n8n_webhook_boleta_url' => $boletaWebhook,
            'n8n_webhook_recordatorio_url' => $recordatorioWebhook,
            'n8n_webhook_morosidad_url' => $morosidadWebhook,
            'n8n_webhook_campana_url' => $campanaWebhook,
            'n8n_webhook' => $boletaWebhook ?: $recordatorioWebhook,
            'n8n_campana_webhook' => $campanaWebhook,
            'evolution_url' => !empty($configMap['evolution_api_url']) ? $configMap['evolution_api_url'] : ($clienteCfg['whatsapp']['api_url'] ?? ''),
            'evolution_key' => !empty($configMap['evolution_api_key']) ? $configMap['evolution_api_key'] : ($clienteCfg['whatsapp']['api_key'] ?? ''),
            'evolution_instance' => !empty($configMap['evolution_instance']) ? $configMap['evolution_instance'] : ($clienteCfg['whatsapp']['instance_name'] ?? ''),
            'adjuntar_logo' => isset($configMap['whatsapp_adjuntar_logo']) ? ($configMap['whatsapp_adjuntar_logo'] === '1') : true,
            'logo_url' => !empty($configMap['whatsapp_logo_url']) ? $configMap['whatsapp_logo_url'] : ClienteService::bannerWhatsappUrl(),
            'admin_phone' => !empty($configMap['whatsapp_phone']) ? $configMap['whatsapp_phone'] : ClienteService::telefono(),
        ];
    }

    /**
     * Envía notificación vía Webhook a nuestro sistema de n8n.
     */
    public static function sendViaN8N($recipientPhone, string $message, array $extraData = [], ?string $webhookUrl = null): array
    {
        $cleanPhone = self::normalizarTelefono($recipientPhone);
        if (empty($cleanPhone) || empty(trim($message))) {
            return [
                'success' => false,
                'message' => 'Número de teléfono o mensaje vacío.',
                'provider' => 'n8n',
                'id' => null
            ];
        }

        $cfg = self::getConfig();

        // Determinar la URL del webhook de n8n a utilizar
        $targetUrl = $webhookUrl;
        if (empty($targetUrl)) {
            $tipo = $extraData['tipo'] ?? 'general';
            if ($tipo === 'boleta') {
                $targetUrl = $cfg['n8n_webhook_boleta_url'] ?: ($cfg['n8n_webhook_recordatorio_url'] ?: ($cfg['n8n_webhook_base_url'] . '/webhook/cefi-boleta'));
            } elseif ($tipo === 'morosidad') {
                $targetUrl = $cfg['n8n_webhook_morosidad_url'] ?: ($cfg['n8n_webhook_recordatorio_url'] ?: ($cfg['n8n_webhook_base_url'] . '/webhook/cefi-morosidad'));
            } elseif ($tipo === 'campana') {
                $targetUrl = $cfg['n8n_webhook_campana_url'] ?: ($cfg['n8n_webhook_base_url'] . '/webhook/cefi-campana');
            } else {
                $targetUrl = $cfg['n8n_webhook_boleta_url'] ?: ($cfg['n8n_webhook_recordatorio_url'] ?: ($cfg['n8n_webhook_base_url'] . '/webhook/cefi-recordatorio'));
            }
        }

        if (empty($targetUrl)) {
            return [
                'success' => false,
                'message' => 'No hay una URL de Webhook de n8n configurada.',
                'provider' => 'n8n',
                'id' => null
            ];
        }

        $payload = array_merge([
            'telefono' => $cleanPhone,
            'phone' => $cleanPhone,
            'number' => $cleanPhone,
            'mensaje' => $message,
            'message' => $message,
            'text' => $message,
            'caption' => $message,
            'client_id' => ClienteService::id(),
            'client_name' => ClienteService::nombre(),
            'institucion' => ClienteService::nombre(),
            'institucion_legal' => ClienteService::nombreLegal(),
            'media_url' => $cfg['adjuntar_logo'] ? $cfg['logo_url'] : null,
            'logo_url' => $cfg['logo_url'],
            'fecha' => now()->toDateTimeString(),
            'timestamp' => now()->toIso8601String(),
        ], $extraData);

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $ch = curl_init($targetUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: Inbox-CEFI/2.0-n8n'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        Log::info("WhatsApp n8n dispatch: target={$targetUrl}, code={$httpCode}, phone={$cleanPhone}");

        if ($httpCode >= 200 && $httpCode < 300) {
            $respData = json_decode((string)$response, true);
            return [
                'success' => true,
                'message' => 'Notificación despachada con éxito al sistema n8n.',
                'provider' => 'n8n',
                'webhook' => $targetUrl,
                'id' => $respData['id'] ?? null
            ];
        }

        $errorDetail = '';
        if ($httpCode === 404) {
            $errorDetail = "El webhook de n8n no está registrado o está inactivo ({$targetUrl}).";
        } elseif ($httpCode === 0) {
            $errorDetail = "No se pudo conectar con el servidor n8n: {$curlError}";
        } else {
            $respData = json_decode((string)$response, true);
            $msg = $respData['message'] ?? $response;
            $errorDetail = "Respuesta n8n HTTP {$httpCode}: {$msg}";
        }

        return [
            'success' => false,
            'message' => $errorDetail,
            'provider' => 'n8n',
            'webhook' => $targetUrl,
            'id' => null
        ];
    }

    /**
     * Envía mensaje directamente usando Evolution API (VPS propio).
     */
    public static function sendViaEvolutionApi($recipientPhone, string $message, ?string $mediaUrl = null, ?string $fileName = null): array
    {
        $fileName = $fileName ?: ('comunicado_' . ClienteService::id() . '.jpg');
        $cleanPhone = self::normalizarTelefono($recipientPhone);
        if (empty($cleanPhone) || empty(trim($message))) {
            return [
                'success' => false,
                'message' => 'Número de teléfono o mensaje vacío.',
                'provider' => 'Evolution API',
                'id' => null
            ];
        }

        $cfg = self::getConfig();
        $evoUrl = rtrim($cfg['evolution_url'], '/');
        $instance = $cfg['evolution_instance'];
        $apiKey = $cfg['evolution_key'];

        if (empty($evoUrl) || empty($instance) || empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'Evolution API no está configurada.',
                'provider' => 'Evolution API',
                'id' => null
            ];
        }

        if (!empty($mediaUrl)) {
            $endpoint = "{$evoUrl}/message/sendMedia/{$instance}";
            $payload = json_encode([
                'number' => $cleanPhone,
                'mediatype' => 'image',
                'mimetype' => 'image/jpeg',
                'caption' => $message,
                'media' => $mediaUrl,
                'fileName' => $fileName
            ]);
        } else {
            $endpoint = "{$evoUrl}/message/sendText/{$instance}";
            $payload = json_encode([
                'number' => $cleanPhone,
                'text' => $message
            ]);
        }

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'apikey: ' . $apiKey
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $respData = json_decode((string)$response, true);
            return [
                'success' => true,
                'message' => 'Mensaje enviado exitosamente vía Evolution API (VPS).',
                'provider' => 'Evolution API (VPS)',
                'id' => $respData['key']['id'] ?? ($respData['id'] ?? null)
            ];
        }

        return [
            'success' => false,
            'message' => 'Error Evolution API (HTTP ' . $httpCode . '): ' . ($curlError ?: $response),
            'provider' => 'Evolution API (VPS)',
            'id' => null
        ];
    }

    /**
     * Envía un mensaje de WhatsApp a un destinatario específico (Prioridad: n8n).
     */
    public static function sendTo($recipientPhone, string $message, ?string $mediaUrl = null, array $extraData = []): array
    {
        $cleanPhone = self::normalizarTelefono($recipientPhone);
        if (empty($cleanPhone) || empty(trim($message))) {
            return [
                'success' => false,
                'message' => 'Número de teléfono o mensaje vacío.',
                'provider' => 'n8n',
                'id' => null
            ];
        }

        // 1. Prioridad: Despacho a nuestro sistema de n8n
        $resN8n = self::sendViaN8N($cleanPhone, $message, $extraData);
        if ($resN8n['success']) {
            return $resN8n;
        }

        // 2. Si n8n reporta error pero existe Evolution API configurada, intentar como fallback de respaldo
        $cfg = self::getConfig();
        if (!empty($cfg['evolution_url']) && !empty($cfg['evolution_instance']) && !empty($cfg['evolution_key'])) {
            $evoLogo = $mediaUrl ?: ($cfg['adjuntar_logo'] ? $cfg['logo_url'] : null);
            $resEvo = self::sendViaEvolutionApi($cleanPhone, $message, $evoLogo);
            if ($resEvo['success']) {
                return $resEvo;
            }
        }

        return $resN8n;
    }

    /**
     * Envía aviso de mora oficial a un estudiante con desglose y morosidad compuesta.
     */
    public static function sendMoraAviso(int $estudianteId, ?string $customMessage = null): array
    {
        $estudiante = Usuario::find($estudianteId);
        if (!$estudiante) {
            return ['success' => false, 'message' => 'Estudiante no encontrado.', 'wa_link' => ''];
        }

        $phoneClean = self::normalizarTelefono($estudiante->telefono);
        $nombre = trim($estudiante->nombre . ' ' . ($estudiante->apellidos ?? ''));

        // Recalcular intereses antes de armar el desglose
        InteresesService::recalcularInteresesEstudiante($estudianteId);

        $hoy = Carbon::today();
        $cuotas = SeguimientoPago::with('boleta')
            ->where('id_estudiante', $estudianteId)
            ->where('estado', 'pendiente')
            ->where('fecha_vencimiento', '<', $hoy)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        $totalCapital = 0.0;
        $totalMora = 0.0;
        $cuotasDetalle = [];
        $detalleTxt = '';

        foreach ($cuotas as $c) {
            $cap = floatval($c->monto_cuota);
            $mora = floatval($c->interes_acumulado);
            $totalCapital += $cap;
            $totalMora += $mora;
            $vFmt = $c->fecha_vencimiento ? $c->fecha_vencimiento->format('d/m/Y') : 'N/D';
            $numBoleta = $c->boleta->numero_boleta ?? 'N/D';
            $totCuota = number_format($cap + $mora, 2);
            $detalleTxt .= "• Boleta *{$numBoleta}* | Cuota #{$c->numero_cuota} (Venció: {$vFmt}): ¢{$totCuota}\n";
            $cuotasDetalle[] = [
                'cuota_id' => $c->id,
                'boleta' => $numBoleta,
                'numero_cuota' => $c->numero_cuota,
                'vencimiento' => $vFmt,
                'capital' => $cap,
                'mora' => $mora,
                'total' => $cap + $mora
            ];
        }

        if (empty($customMessage)) {
            $totalGeneral = number_format($totalCapital + $totalMora, 2);
            $nombreInstitucion = ClienteService::nombre();
            $firmaLegal = ClienteService::nombreLegal();

            $customMessage = "🏛️ *{$nombreInstitucion} - Departamento de Finanzas*\n\n" .
                             "Estimado(a) estudiante *" . $nombre . "*,\n\n" .
                             "Le saludamos cordialmente. Nos comunicamos para informarle que mantiene cuota(s) vencidas con recargo de mora acumulada:\n\n" .
                             $detalleTxt . "\n" .
                             "📊 *Total Vencido a la fecha:* ¢" . $totalGeneral . "\n\n" .
                             "Le invitamos a realizar su pago vía Sinpe Móvil o transferencia bancaria y remitir su comprobante para mantener sus servicios académicos activos.\n\n" .
                             "_{$firmaLegal} - Control de Morosidad_";
        }

        $directWaUrl = "https://api.whatsapp.com/send?phone=" . $phoneClean . "&text=" . urlencode($customMessage);

        if (empty($phoneClean)) {
            return [
                'success' => false,
                'message' => 'El estudiante no tiene un número telefónico válido registrado.',
                'wa_link' => $directWaUrl,
                'mensaje' => $customMessage
            ];
        }

        $extraData = [
            'tipo' => 'morosidad',
            'id_estudiante' => $estudianteId,
            'estudiante_nombre' => $nombre,
            'estudiante_email' => $estudiante->email,
            'estudiante_cedula' => $estudiante->cedula,
            'total_capital' => $totalCapital,
            'total_mora' => $totalMora,
            'total_general' => $totalCapital + $totalMora,
            'cuotas_vencidas' => $cuotasDetalle,
        ];

        $cfg = self::getConfig();
        $res = self::sendViaN8N($phoneClean, $customMessage, $extraData, $cfg['n8n_webhook_morosidad_url']);
        $res['wa_link'] = $directWaUrl;
        $res['mensaje'] = $customMessage;

        return $res;
    }

    /**
     * Envía notificación de Boleta de Matrícula (para firma o con enlace al comprobante oficial PDF) vía n8n.
     */
    public static function sendBoletaAviso(int $boletaId): array
    {
        $boleta = Boleta::with('estudiante')->find($boletaId);
        if (!$boleta || !$boleta->estudiante) {
            return ['success' => false, 'message' => 'Boleta o estudiante no encontrado.', 'wa_link' => ''];
        }

        $est = $boleta->estudiante;
        $phoneClean = self::normalizarTelefono($est->telefono);
        $nombre = trim($est->nombre . ' ' . ($est->apellidos ?? ''));
        $numBoleta = $boleta->numero_boleta;
        $periodo = $boleta->periodo;
        $estado = $boleta->estado;
        $token = $boleta->token_firma ?? '';

        $nombreInstitucion = ClienteService::nombre();
        $firmaLegal = ClienteService::nombreLegal();

        if ($estado === 'pendiente_firma' && !empty($token)) {
            $linkFirma = url("/boleta/firmar/{$token}");
            $mensaje = "🏛️ *{$nombreInstitucion} - Boleta de Matrícula*\n\n" .
                       "Estimado(a) estudiante *" . $nombre . "*,\n\n" .
                       "Se ha emitido su Boleta de Matrícula *" . $numBoleta . "* para el período *" . $periodo . "*.\n\n" .
                       "✍️ *Firma Digital Requerida:*\n" .
                       "Por favor ingrese al siguiente enlace oficial para estampar su firma digital de conformidad:\n" .
                       $linkFirma . "\n\n" .
                       "_Departamento de Registro y Finanzas - {$nombreInstitucion}_";
        } else {
            $total = number_format($boleta->total, 2);
            $saldo = number_format($boleta->saldo_pendiente, 2);
            $pdfLink = route('boletas.pdf', $boleta->id);

            $mensaje = "🏛️ *{$nombreInstitucion} - Comprobante de Matrícula*\n\n" .
                       "Estimado(a) estudiante *" . $nombre . "*,\n\n" .
                       "Le compartimos el estado oficial de su Boleta *" . $numBoleta . "* (Período: " . $periodo . "):\n\n" .
                       "• *Total Facturado:* ¢" . $total . "\n" .
                       "• *Saldo Pendiente:* ¢" . $saldo . "\n" .
                       "• *Estado:* " . strtoupper(str_replace('_', ' ', $estado)) . "\n\n" .
                       "📄 *Ver Boleta Oficial en PDF:*\n" .
                       $pdfLink . "\n\n" .
                       "_{$firmaLegal}_";
        }

        $directWaUrl = "https://api.whatsapp.com/send?phone=" . $phoneClean . "&text=" . urlencode($mensaje);

        if (empty($phoneClean)) {
            return [
                'success' => false,
                'message' => 'El estudiante no tiene un número telefónico registrado.',
                'wa_link' => $directWaUrl,
                'mensaje' => $mensaje
            ];
        }

        $extraData = [
            'tipo' => 'boleta',
            'id_boleta' => $boleta->id,
            'numero_boleta' => $numBoleta,
            'periodo' => $periodo,
            'estado' => $estado,
            'id_estudiante' => $est->id,
            'estudiante_nombre' => $nombre,
            'estudiante_email' => $est->email,
            'estudiante_cedula' => $est->cedula,
            'total' => (float)$boleta->total,
            'monto_pagado' => (float)$boleta->monto_pagado,
            'saldo_pendiente' => (float)$boleta->saldo_pendiente,
            'link_firma' => ($estado === 'pendiente_firma' && !empty($token)) ? url("/boleta/firmar/{$token}") : null,
            'pdf_url' => route('boletas.pdf', $boleta->id),
            'nombre_institucion' => $nombreInstitucion,
        ];

        $cfg = self::getConfig();
        $res = self::sendViaN8N($phoneClean, $mensaje, $extraData, $cfg['n8n_webhook_boleta_url']);
        $res['wa_link'] = $directWaUrl;
        $res['mensaje'] = $mensaje;

        return $res;
    }

    /**
     * Envía alerta administrativa al teléfono principal vía n8n.
     */
    public static function sendAdminAlert(string $message): array
    {
        $cfg = self::getConfig();
        return self::sendViaN8N($cfg['admin_phone'], $message, ['tipo' => 'admin_alert']);
    }
}
