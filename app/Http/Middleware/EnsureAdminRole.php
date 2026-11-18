<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    /**
     * Restringe el acceso según nivel administrativo:
     * - 'general': exclusivamente Administrador General (id_rol == 1).
     * - 'any' (default): Administrador General o Administrador operativo.
     */
    public function handle(Request $request, Closure $next, string $level = 'any'): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $autorizado = $level === 'general'
            ? (method_exists($user, 'esAdminGeneral') ? $user->esAdminGeneral() : (int)$user->id_rol === 1)
            : (method_exists($user, 'esAdmin') ? $user->esAdmin() : (int)$user->id_rol === 1);

        if (!$autorizado) {
            $mensaje = $level === 'general'
                ? 'Esta acción está reservada exclusivamente para el Administrador General.'
                : 'Esta acción está reservada para personal administrativo autorizado.';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Acceso denegado',
                    'message' => $mensaje
                ], 403);
            }

            abort(403, 'Acceso denegado: ' . $mensaje);
        }

        return $next($request);
    }
}
