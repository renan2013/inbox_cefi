<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    /**
     * Restringe el acceso exclusivamente al Administrador General (id_rol == 1).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if ((int)Auth::user()->id_rol !== 1) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Acceso denegado',
                    'message' => 'Esta acción está reservada exclusivamente para el Administrador General.'
                ], 403);
            }

            abort(403, 'Acceso denegado: Esta sección está reservada exclusivamente para el Administrador General.');
        }

        return $next($request);
    }
}
