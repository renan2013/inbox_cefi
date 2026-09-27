<?php

namespace App\Services;

use App\Models\ConfiguracionSistema;

class ClienteService
{
    /**
     * Cache en memoria de valores de cliente para alto rendimiento.
     */
    protected static ?array $config = null;

    /**
     * Carga y resuelve la configuración del cliente (combinando config/cliente.php con base de datos si existe).
     */
    public static function all(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        $base = config('cliente', []);

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('configuracion_sistema')) {
                $dbItems = ConfiguracionSistema::pluck('valor', 'clave')->all();

                foreach ($dbItems as $k => $val) {
                    if (str_starts_with($k, 'cliente_')) {
                        $prop = str_replace('cliente_', '', $k);
                        if (!empty($val)) {
                            $base[$prop] = $val;
                        }
                    }
                }

                // Overrides para n8n
                if (!empty($dbItems['n8n_webhook_base_url'])) {
                    $base['n8n']['webhook_base_url'] = $dbItems['n8n_webhook_base_url'];
                }
                if (!empty($dbItems['n8n_webhook_morosidad_url'])) {
                    $base['n8n']['webhook_morosidad'] = $dbItems['n8n_webhook_morosidad_url'];
                }
                if (!empty($dbItems['n8n_webhook_recordatorio_url'])) {
                    $base['n8n']['webhook_recordatorio'] = $dbItems['n8n_webhook_recordatorio_url'];
                }
                if (!empty($dbItems['n8n_webhook_campana_url'])) {
                    $base['n8n']['webhook_campana'] = $dbItems['n8n_webhook_campana_url'];
                }

                // Overrides para WhatsApp / Evolution API
                if (!empty($dbItems['evolution_api_url'])) {
                    $base['whatsapp']['api_url'] = $dbItems['evolution_api_url'];
                }
                if (!empty($dbItems['evolution_api_key'])) {
                    $base['whatsapp']['api_key'] = $dbItems['evolution_api_key'];
                }
                if (!empty($dbItems['evolution_instance'])) {
                    $base['whatsapp']['instance_name'] = $dbItems['evolution_instance'];
                }
            }
        } catch (\Throwable $e) {
            // Continuar con config/cliente.php si BD no está lista
        }

        return self::$config = $base;
    }

    public static function id(): string
    {
        return self::all()['id'] ?? 'cefi';
    }

    public static function nombre(): string
    {
        return self::all()['nombre'] ?? 'CEFI';
    }

    public static function nombreLegal(): string
    {
        return self::all()['nombre_legal'] ?? 'Centro de Estudios e Investigación';
    }

    public static function siglas(): string
    {
        return self::all()['siglas'] ?? 'CEFI';
    }

    public static function slogan(): string
    {
        return self::all()['slogan'] ?? '';
    }

    public static function telefono(): string
    {
        return self::all()['telefono'] ?? '';
    }

    public static function telefonoDisplay(): string
    {
        return self::all()['telefono_display'] ?? self::telefono();
    }

    public static function email(string $tipo = 'soporte'): string
    {
        $cfg = self::all();
        $key = 'email_' . $tipo;
        return $cfg[$key] ?? ($cfg['email_contacto'] ?? 'info@cefi.cr');
    }

    public static function sitioWeb(): string
    {
        return self::all()['sitio_web'] ?? 'https://cefi.cr';
    }

    public static function campusVirtual(): string
    {
        return self::all()['campus_virtual'] ?? 'https://virtual.cefi.cr';
    }

    public static function moodleKey(): string
    {
        return self::all()['moodle_key'] ?? 'cefi2026';
    }

    public static function logoUrl(): string
    {
        return self::all()['logo_url'] ?? asset('imgs/logo.png');
    }

    public static function bannerWhatsappUrl(): string
    {
        return self::all()['logo_banner_whatsapp'] ?? asset('imgs/fondo_defecto_notificacion.png');
    }

    /**
     * Construye un payload estandarizado para n8n con los metadatos del cliente.
     */
    public static function getN8nPayload(string $event, array $data = []): array
    {
        $cfg = self::all();

        return [
            'client_id'      => $cfg['id'] ?? 'cefi',
            'client_name'    => $cfg['nombre'] ?? 'CEFI',
            'client_siglas'  => $cfg['siglas'] ?? 'CEFI',
            'instance_name'  => $cfg['whatsapp']['instance_name'] ?? 'cefi_whatsapp',
            'support_phone'  => $cfg['telefono'] ?? '',
            'support_email'  => $cfg['email_soporte'] ?? '',
            'event'          => $event,
            'timestamp'      => now()->toIso8601String(),
            'data'           => $data
        ];
    }
}
