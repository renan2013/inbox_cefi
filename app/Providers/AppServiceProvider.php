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
}
