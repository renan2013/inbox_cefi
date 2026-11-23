<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /**
     * Muestra la vista de login.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectDestinoSegunRol(Auth::user());
        }

        // Generar Captcha Matemático si no existe en la sesión
        if (!session()->has('captcha_num1') || !session()->has('captcha_num2')) {
            $this->generateCaptcha();
        }

        return view('auth.login');
    }

    /**
     * Procesa el inicio de sesión.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'captcha_val' => 'required|integer',
        ]);

        // Validar captcha
        $expected = session('captcha_num1') + session('captcha_num2');
        if ((int)$request->captcha_val !== $expected) {
            $this->generateCaptcha();
            return back()->withInput()->withErrors([
                'captcha_val' => 'El captcha matemático ingresado es incorrecto.',
            ]);
        }

        // Intentar autenticación
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Limpiar captcha
            session()->forget(['captcha_num1', 'captcha_num2']);

            // Registrar ingreso directamente en BD local de CEFI (sin depender de Drive)
            try {
                $user = Auth::user();
                $date_cr = new \DateTime("now", new \DateTimeZone("America/Costa_Rica"));
                \App\Http\Controllers\ReporteController::ensureIngresosTable();
                \Illuminate\Support\Facades\DB::table('ingresos_sistema')->insert([
                    'id_usuario'    => $user->id ?? null,
                    'nombre'        => trim(($user->nombre ?? '') . ' ' . ($user->apellidos ?? '')),
                    'email'         => $user->email ?? '',
                    'id_rol'        => (int)($user->id_rol ?? 2),
                    'ip'            => $request->ip(),
                    'origen'        => 'inbox_' . config('cliente.id', 'cefi'),
                    'fecha_ingreso' => $date_cr->format('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $e) {
                // No bloquear el login si falla el log de acceso
            }

            return $this->redirectDestinoSegunRol($user);
        }

        // Regenerar captcha en caso de fallo de autenticación
        $this->generateCaptcha();

        return back()->withInput()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ]);
    }

    /**
     * Retorna la redirección apropiada según el rol del usuario institucional.
     */
    protected function redirectDestinoSegunRol($user)
    {
        if ($user && ((int)$user->id_rol === 3 || (method_exists($user, 'esAlumno') && $user->esAlumno()))) {
            return redirect()->intended('/estudiante/portal');
        } elseif ($user && ((int)$user->id_rol === 2 || (int)$user->id_rol === 4 || (method_exists($user, 'esDocente') && $user->esDocente()))) {
            return redirect()->intended('/docente/cursos');
        }
        return redirect()->intended('/dashboard');
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Genera números aleatorios para el captcha matemático.
     */
    private function generateCaptcha()
    {
        session([
            'captcha_num1' => rand(1, 9),
            'captcha_num2' => rand(1, 9)
        ]);
    }
}
