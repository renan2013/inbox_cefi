<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;
use App\Services\ModuleService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->clearCacheOnNewGitDeploy();

        Paginator::useBootstrapFive();

        Blade::if('module', function (string $moduleKey) {
            return ModuleService::isEnabled($moduleKey);
        });

        Blade::if('unlessmodule', function (string $moduleKey) {
            return !ModuleService::isEnabled($moduleKey);
        });

        // Compartir el servicio de cliente para fácil uso en Blade: {{ $cliente::nombre() }}
        \Illuminate\Support\Facades\View::share('cliente', \App\Services\ClienteService::class);

        // Cargar identidad dinámica del cliente y correo saliente desde la base de datos (1 sola consulta cacheada)
        try {
            $clienteConfig = \App\Services\ClienteService::all();
            config(['cliente' => array_merge(config('cliente', []), $clienteConfig)]);

            $mailConfigs = \App\Services\ClienteService::dbItems();
            if (!empty($mailConfigs['mail_host'])) {
                $port = (int)($mailConfigs['mail_port'] ?? 465);
                $scheme = $mailConfigs['mail_scheme'] ?? (($port == 465 || ($mailConfigs['mail_encryption'] ?? '') === 'ssl') ? 'smtps' : null);
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $mailConfigs['mail_host'],
                    'mail.mailers.smtp.port' => $port,
                    'mail.mailers.smtp.scheme' => $scheme,
                    'mail.mailers.smtp.username' => $mailConfigs['mail_username'] ?? '',
                    'mail.mailers.smtp.password' => $mailConfigs['mail_password'] ?? '',
                    'mail.from.address' => $mailConfigs['mail_from_address'] ?? $mailConfigs['mail_username'] ?? config('mail.from.address'),
                    'mail.from.name' => $mailConfigs['mail_from_name'] ?? config('mail.from.name'),
                ]);
            }
        } catch (\Throwable $e) {
            // Ignorar en migraciones iniciales o consola sin conexión
        }
    }

    /**
     * Limpia automáticamente vistas compiladas y cachés cuando cambia el commit de Git tras un auto-deploy.
     */
    protected function clearCacheOnNewGitDeploy(): void
    {
        try {
            if (app()->runningInConsole()) {
                return;
            }
            $headFile = base_path('.git/HEAD');
            if (!is_file($headFile)) {
                return;
            }
            $head = trim((string) @file_get_contents($headFile));
            if (str_starts_with($head, 'ref:')) {
                $refPath = base_path('.git/' . trim(substr($head, 4)));
                $rev = is_file($refPath) ? trim((string) @file_get_contents($refPath)) : $head;
            } else {
                $rev = $head;
            }
            if ($rev === '') {
                return;
            }

            $marker = storage_path('framework/deploy_rev');
            $lastRev = is_file($marker) ? trim((string) @file_get_contents($marker)) : '';
            if ($lastRev !== $rev) {
                foreach (glob(storage_path('framework/views/*.php')) ?: [] as $file) {
                    @unlink($file);
                }
                foreach (glob(base_path('bootstrap/cache/*.php')) ?: [] as $file) {
                    @unlink($file);
                }
                if (function_exists('opcache_reset')) {
                    @opcache_reset();
                }
                @file_put_contents($marker, $rev);
            }
        } catch (\Throwable $e) {
            // No interrumpir la petición ante errores de lectura/escritura
        }
    }
}
