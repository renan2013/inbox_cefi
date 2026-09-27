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
                $dbItems = ConfiguracionSistema::where('clave', 'like', 'cliente_%')
                    ->pluck('valor', 'clave')
                    ->all();

                foreach ($dbItems as $k => $val) {
                    $prop = str_replace('cliente_', '', $k);
                    if (!empty($val)) {
                        $base[$prop] = $val;
                    }
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
