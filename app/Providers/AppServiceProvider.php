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
    }
}
