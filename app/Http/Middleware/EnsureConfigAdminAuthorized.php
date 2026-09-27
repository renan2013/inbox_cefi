<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureConfigAdminAuthorized
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificar sesión activa de usuario
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Verificar que sea Administrador General (id_rol == 1)
        if (Auth::user()->id_rol != 1) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Acceso denegado',
                    'message' => 'Esta sección está reservada exclusivamente para el Administrador General.'
                ], 403);
            }

            abort(403, 'Acceso restringido: Esta sección de programación y personalización de la plataforma está reservada exclusivamente para el Administrador General.');
        }

        // 3. Verificar autorización con Clave de Seguridad Superior
        if (!session('parametros_auth', false)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Requiere autorización superior',
                    'message' => 'Debe ingresar la clave de seguridad superior para acceder.'
                ], 401);
            }

            // Guardar URL de destino y redirigir al formulario de clave
            return redirect()->route('configuracion.login')->with('warning', 'Debe identificarse con la clave de seguridad superior para gestionar la plataforma.');
        }

        return $next($request);
    }
}
