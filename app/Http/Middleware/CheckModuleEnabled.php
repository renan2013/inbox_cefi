<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ModuleService;

class CheckModuleEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $moduleKey  Clave del módulo a verificar
     */
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        if (!ModuleService::isEnabled($moduleKey)) {
            $info = ModuleService::getInfo($moduleKey);
            $nombreModulo = $info['nombre'] ?? ucfirst(str_replace('_', ' ', $moduleKey));

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Módulo no contratado',
                    'message' => "El módulo '{$nombreModulo}' no está habilitado para esta instalación o licencia.",
                    'module'  => $moduleKey,
                ], 403);
            }

            return response()->view('errors.module_disabled', [
                'nombreModulo' => $nombreModulo,
                'moduleKey'    => $moduleKey,
            ], 403);
        }

        return $next($request);
    }
}
