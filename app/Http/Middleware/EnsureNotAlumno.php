<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotAlumno
{
    /**
     * Handle an incoming request.
     * Impide que los usuarios con rol de Alumno / Estudiante ingresen a rutas de registro o gestión académica.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ((int)$user->id_rol === 3 || (method_exists($user, 'esAlumno') && $user->esAlumno())) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Los alumnos no tienen acceso a esta sección.'
                    ], 403);
                }

                return redirect()->route('estudiante.portal')->with('warning', 'Como alumno, tu área de trabajo es Mi Portal Estudiantil.');
            }
        }

        return $next($request);
    }
}
