<?php

namespace App\Services;

use App\Models\ConfiguracionSistema;
use App\Models\Usuario;
use App\Models\Boleta;
use App\Models\SeguimientoPago;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
     * Obtiene la configuración activa de WhatsApp (Evolution API / Green-API / n8n).
     */
    public static function getConfig(): array
    {
        $configMap = ConfiguracionSistema::whereIn('clave', [
            'evolution_api_url',
            'evolution_api_key',
            'evolution_instance',
            'n8n_webhook_campana_url',
            'green_api_url',
            'green_api_instance',
            'green_api_token',
            'whatsapp_adjuntar_logo',
            'whatsapp_logo_url',
            'whatsapp_phone',
            'n8n_webhook_recordatorio_url'
        ])->pluck('valor', 'clave')->all();

        $clienteCfg = ClienteService::all();

        return [
            'evolution_url' => !empty($configMap['evolution_api_url']) ? $configMap['evolution_api_url'] : ($clienteCfg['whatsapp']['api_url'] ?? 'http://93.127.215.91:8080'),
            'evolution_key' => !empty($configMap['evolution_api_key']) ? $configMap['evolution_api_key'] : ($clienteCfg['whatsapp']['api_key'] ?? 'RenanEvolution2026_KeySecret!'),
            'evolution_instance' => !empty($configMap['evolution_instance']) ? $configMap['evolution_instance'] : ($clienteCfg['whatsapp']['instance_name'] ?? 'cefi_whatsapp'),
            'n8n_campana_webhook' => !empty($configMap['n8n_webhook_campana_url']) ? $configMap['n8n_webhook_campana_url'] : ($clienteCfg['n8n']['webhook_campana'] ?? ''),
            'url' => !empty($configMap['green_api_url']) ? $configMap['green_api_url'] : 'https://7107.api.greenapi.com',
            'instance' => !empty($configMap['green_api_instance']) ? $configMap['green_api_instance'] : '710722714932',
            'token' => !empty($configMap['green_api_token']) ? $configMap['green_api_token'] : '5239b260fd484deb853ddd1789d534509409e8737a7b43fbb2',
            'adjuntar_logo' => isset($configMap['whatsapp_adjuntar_logo']) ? ($configMap['whatsapp_adjuntar_logo'] === '1') : true,
            'logo_url' => !empty($configMap['whatsapp_logo_url']) ? $configMap['whatsapp_logo_url'] : ClienteService::bannerWhatsappUrl(),
            'admin_phone' => !empty($configMap['whatsapp_phone']) ? $configMap['whatsapp_phone'] : ClienteService::telefono(),
            'n8n_webhook' => !empty($configMap['n8n_webhook_recordatorio_url']) ? $configMap['n8n_webhook_recordatorio_url'] : ($clienteCfg['n8n']['webhook_recordatorio'] ?? '')
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
            // Envío con Imagen/Flyer
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
            // Envío de solo texto
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
     * Envía un mensaje de WhatsApp a un destinatario específico (prioriza Evolution API VPS).
     */
    public static function sendTo($recipientPhone, string $message, ?string $mediaUrl = null): array
    {
        $cleanPhone = self::normalizarTelefono($recipientPhone);
        if (empty($cleanPhone) || empty(trim($message))) {
            return [
                'success' => false,
                'message' => 'Número de teléfono o mensaje vacío.',
                'provider' => null,
                'id' => null
            ];
        }

        $cfg = self::getConfig();

        // 1. Intentar primero con Evolution API en el VPS (Sin límites de prueba)
        if (!empty($cfg['evolution_url']) && !empty($cfg['evolution_instance']) && !empty($cfg['evolution_key'])) {
            $evoLogo = $mediaUrl ?: ($cfg['adjuntar_logo'] ? $cfg['logo_url'] : null);
            $resEvo = self::sendViaEvolutionApi($cleanPhone, $message, $evoLogo);
            if ($resEvo['success']) {
                return $resEvo;
            }
        }

        $apiUrl = rtrim($cfg['url'], '/');
        $idInstance = $cfg['instance'];
        $apiToken = $cfg['token'];
        $adjuntarLogo = $cfg['adjuntar_logo'];
        $logoUrl = $mediaUrl ?: $cfg['logo_url'];

        $lastErrorDesc = '';
        $sentOk = false;
        $msgId = null;

        if (!empty($idInstance) && !empty($apiToken)) {
            // 1. Envío con Imagen/Logo y Caption (Evita fondo negro en WhatsApp)
            if ($adjuntarLogo && !empty($logoUrl)) {
                $endpointImage = "{$apiUrl}/waInstance{$idInstance}/sendFileByUrl/{$apiToken}";
                $payloadImage = json_encode([
                    'chatId' => $cleanPhone . '@c.us',
                    'urlFile' => $logoUrl,
                    'fileName' => 'logo_' . ClienteService::id() . '.png',
                    'caption' => $message
                ]);

                $ch = curl_init($endpointImage);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $payloadImage,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 14,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode >= 200 && $httpCode < 300) {
                    $respData = json_decode((string)$response, true);
                    $msgId = $respData['idMessage'] ?? null;
                    $sentOk = true;
                } else {
                    $errData = json_decode((string)$response, true);
                    $lastErrorDesc = $errData['correspondentsStatus']['description'] ?? ($errData['message'] ?? 'Error HTTP ' . $httpCode);
                }
            }

            // 2. Si no se adjuntó logo o falló el envío multimedia, fallback a mensaje de texto directo
            if (!$sentOk) {
                $endpointText = "{$apiUrl}/waInstance{$idInstance}/sendMessage/{$apiToken}";
                $payloadText = json_encode([
                    'chatId' => $cleanPhone . '@c.us',
                    'message' => $message
                ]);

                $ch = curl_init($endpointText);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $payloadText,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 12,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode >= 200 && $httpCode < 300) {
                    $respData = json_decode((string)$response, true);
                    $msgId = $respData['idMessage'] ?? null;
                    $sentOk = true;
                } else {
                    $errData = json_decode((string)$response, true);
                    $lastErrorDesc = $errData['correspondentsStatus']['description'] ?? ($errData['message'] ?? 'Error HTTP ' . $httpCode);
                }
            }

            if ($sentOk) {
                return [
                    'success' => true,
                    'message' => 'Enviado con éxito por WhatsApp.',
                    'provider' => 'Green-API',
                    'id' => $msgId
                ];
            }
        }

        // 3. Fallback a n8n Webhook si está configurado
        if (!empty($cfg['n8n_webhook'])) {
            $payloadN8n = json_encode([
                'telefono' => $cleanPhone,
                'mensaje' => $message,
                'fecha' => now()->toDateTimeString()
            ]);

            $ch = curl_init($cfg['n8n_webhook']);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payloadN8n,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                return [
                    'success' => true,
                    'message' => 'Despachado al flujo n8n.',
                    'provider' => 'n8n',
                    'id' => null
                ];
            }
        }

        return [
            'success' => false,
            'message' => !empty($lastErrorDesc) ? $lastErrorDesc : 'No se pudo enviar el WhatsApp. Verifique credenciales o conexión.',
            'provider' => null,
            'id' => null
        ];
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

        if (empty($customMessage)) {
            $hoy = Carbon::today();
            $cuotas = SeguimientoPago::with('boleta')
                ->where('id_estudiante', $estudianteId)
                ->where('estado', 'pendiente')
                ->where('fecha_vencimiento', '<', $hoy)
                ->orderBy('fecha_vencimiento', 'asc')
                ->get();

            $totalCapital = 0.0;
            $totalMora = 0.0;
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
            }

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

        $res = self::sendTo($phoneClean, $customMessage);
        $res['wa_link'] = $directWaUrl;
        $res['mensaje'] = $customMessage;

        return $res;
    }

    /**
     * Envía notificación de Boleta de Matrícula (para firma o con enlace al comprobante oficial PDF).
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

        $res = self::sendTo($phoneClean, $mensaje);
        $res['wa_link'] = $directWaUrl;
        $res['mensaje'] = $mensaje;

        return $res;
    }

    /**
     * Envía alerta administrativa al teléfono principal.
     */
    public static function sendAdminAlert(string $message): array
    {
        $cfg = self::getConfig();
        return self::sendTo($cfg['admin_phone'], $message);
    }
}
