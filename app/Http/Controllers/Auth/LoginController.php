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
            return redirect()->intended('/dashboard');
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

            // Notificar ingreso a n8n en segundo plano para hoja de cálculo de accesos en tiempo real
            try {
                $user = Auth::user();
                $date_cr = new \DateTime("now", new \DateTimeZone("America/Costa_Rica"));
                $n8n_webhook_url = config('app.n8n_webhook_url', 'https://n8n.renangalvan.net/webhook/ingreso-sistema');
                $n8n_data = [
                    "nombre" => trim(($user->nombre ?? '') . ' ' . ($user->apellidos ?? '')),
                    "email" => $user->email,
                    "id_rol" => $user->id_rol ?? 2,
                    "fecha" => $date_cr->format("Y-m-d H:i:s"),
                    "origen" => "sistema_bpm_unela"
                ];

                $ch = curl_init($n8n_webhook_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($n8n_data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Bypass-Tunnel-Reminder: true',
                    'serveo-skip-browser-warning: true'
                ]);
                curl_setopt($ch, CURLOPT_USERAGENT, 'cURL Webhook');
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_exec($ch);
                curl_close($ch);
            } catch (\Exception $e) {
                // Silenciosamente capturado si el webhook no responde
            }

            return redirect()->intended('/dashboard');
        }

        // Regenerar captcha en caso de fallo de autenticación
        $this->generateCaptcha();

        return back()->withInput()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ]);
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
