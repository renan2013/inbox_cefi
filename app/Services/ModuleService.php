<?php

namespace App\Services;

class ModuleService
{
    /**
     * Comprueba si un módulo está habilitado en la configuración.
     *
     * @param string $moduleKey Clave del módulo (ej: 'inventario', 'finanzas')
     * @return bool
     */
    public static function isEnabled(string $moduleKey): bool
    {
        $modules = config('modules.modules', []);

        if (!array_key_exists($moduleKey, $modules)) {
            return true; // Por defecto disponible si no se ha restringido
        }

        return (bool)$modules[$moduleKey];
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
