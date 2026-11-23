<?php

namespace App\Services;

class ModuleService
{
    protected static array $runtimeCache = [];
    protected static ?array $hubTreeCache = null;

    /**
     * Ruta al archivo local de estado sincronizado desde Inbox Cloud Hub (renangalvan.net).
     */
    public static function getStateFilePath(): string
    {
        return storage_path('app/menu_tree_state.json');
    }

    /**
     * Carga el mapa completo del Árbol de Menús y Licencias sincronizado desde el Hub.
     */
    public static function getHubTreeState(bool $forceReload = false): array
    {
        if (self::$hubTreeCache !== null && !$forceReload) {
            return self::$hubTreeCache;
        }

        $state = null;
        $filePath = self::getStateFilePath();

        // 1. Intentar leer desde archivo JSON rápido en storage/app
        if (file_exists($filePath)) {
            $raw = @file_get_contents($filePath);
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $state = $decoded;
                }
            }
        }

        // 2. Fallback desde Base de Datos (configuracion_sistema -> hub_menu_tree_json)
        if ($state === null) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('configuracion_sistema')) {
                    $dbJson = \App\Models\ConfiguracionSistema::where('clave', 'hub_menu_tree_json')->value('valor');
                    if ($dbJson) {
                        $decoded = json_decode($dbJson, true);
                        if (is_array($decoded)) {
                            $state = $decoded;
                            @file_put_contents($filePath, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Continuar si la BD no está disponible
            }
        }

        return self::$hubTreeCache = (is_array($state) ? $state : []);
    }

    /**
     * Guarda el estado recibido desde Inbox Cloud Hub (renangalvan.net) en JSON y Base de Datos.
     */
    public static function saveHubTreeState(array $flags, string $status = 'activo', string $notice = ''): bool
    {
        $cleanFlags = [];
        foreach ($flags as $k => $v) {
            if (str_starts_with((string)$k, '_')) {
                continue;
            }
            $cleanFlags[$k] = (bool)$v;
        }

        // Sincronizar banderas macro de compatibilidad según sub-ítems
        if (array_key_exists('item.generar_boleta', $cleanFlags) || array_key_exists('item.gestion_boletas', $cleanFlags)) {
            $cleanFlags['boletas_matricula'] = !empty($cleanFlags['item.generar_boleta']) || !empty($cleanFlags['item.gestion_boletas']);
        }
        if (array_key_exists('item.estado_cuenta', $cleanFlags) || array_key_exists('item.morosidad', $cleanFlags)) {
            $cleanFlags['finanzas'] = !empty($cleanFlags['item.estado_cuenta']) || !empty($cleanFlags['item.morosidad']);
        }
        if (array_key_exists('item.campanas_whatsapp', $cleanFlags) || array_key_exists('item.suite_whatsapp', $cleanFlags)) {
            $cleanFlags['whatsapp_n8n'] = !empty($cleanFlags['item.campanas_whatsapp']) || !empty($cleanFlags['item.suite_whatsapp']);
        }

        $cleanFlags['_license_status'] = in_array($status, ['activo', 'aviso_cobro', 'suspendido'], true) ? $status : 'activo';
        $cleanFlags['_license_notice'] = $notice;
        $cleanFlags['_last_hub_sync']  = date('Y-m-d H:i:s');

        $json = json_encode($cleanFlags, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filePath = self::getStateFilePath();
        @is_dir(dirname($filePath)) || @mkdir(dirname($filePath), 0775, true);
        $savedFile = @file_put_contents($filePath, $json) !== false;

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('configuracion_sistema')) {
                \App\Models\ConfiguracionSistema::updateOrCreate(
                    ['clave' => 'hub_menu_tree_json'],
                    [
                        'valor'       => $json,
                        'grupo'       => 'licencia_hub',
                        'tipo'        => 'json',
                        'descripcion' => 'Árbol jerárquico de menús sincronizado desde Inbox Cloud Hub (renangalvan.net)',
                    ]
                );

                // También actualizar claves module_* individuales para compatibilidad total
                foreach ($cleanFlags as $mKey => $mVal) {
                    if (!str_starts_with((string)$mKey, '_') && !str_starts_with((string)$mKey, 'item.')) {
                        \App\Models\ConfiguracionSistema::updateOrCreate(
                            ['clave' => 'module_' . $mKey],
                            [
                                'valor' => $mVal ? '1' : '0',
                                'grupo' => 'modulos',
                                'tipo'  => 'boolean',
                            ]
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignorar error de BD si archivo ya guardó
        }

        self::$runtimeCache = [];
        self::getHubTreeState(true);

        return $savedFile;
    }

    public static function getLicenseStatus(): string
    {
        $hub = self::getHubTreeState();
        return (string)($hub['_license_status'] ?? 'activo');
    }

    public static function getLicenseNotice(): string
    {
        $hub = self::getHubTreeState();
        return trim((string)($hub['_license_notice'] ?? ''));
    }

    /**
     * Comprueba si un módulo o sub-ítem (`item.xxx`) está habilitado.
     *
     * @param string $moduleKey Clave del módulo o sub-ítem (ej: 'inventario', 'item.mis_cursos')
     * @return bool
     */
    public static function isEnabled(string $moduleKey): bool
    {
        if (isset(self::$runtimeCache[$moduleKey])) {
            return self::$runtimeCache[$moduleKey];
        }

        // 1. Prioridad máxima: Estado sincronizado desde Inbox Cloud Hub (renangalvan.net)
        $hubState = self::getHubTreeState();
        if (array_key_exists($moduleKey, $hubState)) {
            return self::$runtimeCache[$moduleKey] = (bool)$hubState[$moduleKey];
        }

        $modules = config('modules.modules', []);

        // 2. Revisar si hay override específico en base de datos
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('configuracion_sistema')) {
                $dbVal = \App\Models\ConfiguracionSistema::where('clave', 'module_' . $moduleKey)->value('valor');
                if ($dbVal !== null && $dbVal !== '') {
                    return self::$runtimeCache[$moduleKey] = in_array((string)$dbVal, ['1', 'true', 'yes'], true);
                }
            }
        } catch (\Throwable $e) {
            // Continuar con config si BD no está accesible aún
        }

        // 3. Si no hay override en BD, consultar configuración del archivo
        if (array_key_exists($moduleKey, $modules)) {
            $val = $modules[$moduleKey];
            if (is_string($val)) {
                $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
            }
            return self::$runtimeCache[$moduleKey] = (bool)$val;
        }

        return self::$runtimeCache[$moduleKey] = true; // Por defecto disponible para sub-ítems si no han sido apagados
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
                'enabled' => self::isEnabled($key),
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

    /**
     * Escanea dinámicamente resources/views/layouts/app.blade.php para detectar
     * automáticamente cualquier nuevo menú o sub-ítem que se agregue al navbar.
     */
    public static function discoverNavbarTree(): array
    {
        $layoutFile = resource_path('views/layouts/app.blade.php');
        if (!file_exists($layoutFile)) {
            return [];
        }

        $cacheKey = 'navbar_tree_v1_' . (@filemtime($layoutFile) ?: 0);
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($layoutFile) {
            $content = @file_get_contents($layoutFile);
            if (!$content) {
                return [];
            }

            $discovered = [];
            // Extraer cada bloque <li class="nav-item dropdown"> ... </li>
            if (preg_match_all('/<li\s+class=["\'][^"\']*nav-item\s+dropdown[^"\']*["\'][^>]*>\s*<a[^>]*dropdown-toggle[^>]*>(.*?)<\/a>\s*<ul[^>]*dropdown-menu[^>]*>(.*?)<\/ul>\s*<\/li>/is', $content, $menuMatches, PREG_SET_ORDER)) {
                $menuMapByTitle = [
                    'logística'       => 'inventario',
                    'logistica'       => 'inventario',
                    'gestionar curso' => 'gestion_cursos',
                    'estudiante'      => 'estudiante_tcu',
                    'soporte'         => 'soporte',
                    'planilla'        => 'planilla',
                    'registro'        => 'registro_academico',
                    'base de datos'   => 'base_datos',
                    'configuración'   => 'menu_configuracion',
                    'configuracion'   => 'menu_configuracion',
                ];

                foreach ($menuMatches as $mm) {
                    $rawTitle = trim(preg_replace('/\s+/', ' ', strip_tags($mm[1])));
                    $ulHtml   = $mm[2];
                    $lowerTitle = mb_strtolower($rawTitle, 'UTF-8');

                    $parentKey = $menuMapByTitle[$lowerTitle] ?? ('menu_' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $rawTitle)));

                    $discovered[$parentKey] = [
                        'key'         => $parentKey,
                        'nombre'      => $rawTitle,
                        'icono'       => 'bi-grid-fill',
                        'color'       => 'primary',
                        'categoria'   => 'Menú de Navegación',
                        'descripcion' => 'Menú sincronizado en vivo desde app.blade.php (' . $rawTitle . ')',
                        'default'     => true,
                        'children'    => [],
                    ];

                    $pattern = '/(?:@module\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*)?<li>\s*<a[^>]*class=["\'][^"\']*dropdown-item[^"\']*["\'][^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>\s*<\/li>/is';
                    if (preg_match_all($pattern, $ulHtml, $itemMatches, PREG_SET_ORDER)) {
                        foreach ($itemMatches as $im) {
                            $explicitKey = trim($im[1] ?? '');
                            $rawHref     = trim($im[2] ?? '');
                            $innerHtml   = $im[3] ?? '';
                            $label       = trim(preg_replace('/\s+/', ' ', strip_tags($innerHtml)));

                            if ($label === '' || stripos($label, 'Bloquear Acceso') !== false) {
                                continue;
                            }

                            $icon = 'bi-circle text-info';
                            if (preg_match('/bi-[a-z0-9-]+(?:\s+text-[a-z]+)?/i', $innerHtml, $icMatch)) {
                                $icon = $icMatch[0];
                            }

                            $itemKey = ($explicitKey !== '' && (str_starts_with($explicitKey, 'item.') || $explicitKey === 'inbox_ai' || $explicitKey === 'expedientes_360'))
                                ? $explicitKey
                                : ('item.auto_' . substr(md5($parentKey . ':' . $label), 0, 8));

                            $discovered[$parentKey]['children'][$itemKey] = [
                                'nombre'  => $label,
                                'icono'   => $icon,
                                'ruta'    => $rawHref,
                                'default' => true,
                            ];
                        }
                    }
                }
            }

            return $discovered;
        });
    }
}
