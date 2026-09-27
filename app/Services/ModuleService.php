<?php

namespace App\Services;

class ModuleService
{
    protected static array $runtimeCache = [];

    /**
     * Comprueba si un módulo está habilitado en la configuración o base de datos.
     *
     * @param string $moduleKey Clave del módulo (ej: 'inventario', 'finanzas')
     * @return bool
     */
    public static function isEnabled(string $moduleKey): bool
    {
        if (isset(self::$runtimeCache[$moduleKey])) {
            return self::$runtimeCache[$moduleKey];
        }

        $modules = config('modules.modules', []);

        // 1. Si el módulo está apagado en la configuración, tiene prioridad absoluta (soporta boolean o string)
        if (array_key_exists($moduleKey, $modules)) {
            $val = $modules[$moduleKey];
            if (is_string($val)) {
                $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
            }
            if (!$val) {
                return self::$runtimeCache[$moduleKey] = false;
            }
        }

        // 2. Revisar si hay override específico en base de datos
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('configuracion_sistema')) {
                $dbVal = \App\Models\ConfiguracionSistema::where('clave', 'module_' . $moduleKey)->value('valor');
                if ($dbVal !== null) {
                    return self::$runtimeCache[$moduleKey] = in_array((string)$dbVal, ['1', 'true', 'yes'], true);
                }
            }
        } catch (\Throwable $e) {
            // Continuar con config si BD no está accesible aún
        }

        if (!array_key_exists($moduleKey, $modules)) {
            return self::$runtimeCache[$moduleKey] = true; // Por defecto disponible si no se ha restringido
        }

        return self::$runtimeCache[$moduleKey] = (bool)$modules[$moduleKey];
    }

    /**
     * Obtiene el listado completo de módulos con su estado actual.
     *
     * @return array
     */
    public static function all(): array
    {
        $modules = config('modules.modules', []);
        $info = config('modules.info', []);
        $result = [];

        foreach ($modules as $key => $enabled) {
            $meta = $info[$key] ?? [
                'nombre'      => ucfirst(str_replace('_', ' ', $key)),
                'categoria'   => 'General',
                'descripcion' => ''
            ];

            $result[$key] = array_merge($meta, [
                'key'     => $key,
                'enabled' => (bool)$enabled,
            ]);
        }

        return $result;
    }

    /**
     * Obtiene los metadatos de un módulo específico.
     *
     * @param string $moduleKey
     * @return array|null
     */
    public static function getInfo(string $moduleKey): ?array
    {
        return config("modules.info.{$moduleKey}");
    }
}
