<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SeguimientoPago;
use App\Models\CursoActivo;

class InboxAiController extends Controller
{
    public function chat(Request $request)
    {
        $query = trim($request->input('query', ''));
        if (empty($query)) {
            return response()->json(['reply' => 'Por favor escribe una consulta válida.']);
        }

        $user = Auth::user();
        $user_name = $user ? trim(($user->nombre ?? '') . ' ' . ($user->apellidos ?? '')) : 'Usuario BPM';
        $user_email = $user->email ?? ('usuario@' . config('cliente.id', 'cefi') . '.cr');
        $q_lower = mb_strtolower($query);

        // URL base absoluta garantizada de la aplicación
        $base_app_url = url('/');

        // Métricas en tiempo real con Eloquent
        $total_deudores = 0;
        $total_vencido = 0.00;
        $total_cursos_activos = 0;

        try {
            $deudores_query = SeguimientoPago::where('estado', 'pendiente');
            $total_deudores = $deudores_query->distinct('id_estudiante')->count('id_estudiante');
            $total_vencido = $deudores_query->sum(DB::raw('monto_cuota + interes_acumulado'));
            $total_cursos_activos = CursoActivo::count();
        } catch (\Exception $e) {}

        // 1. Detección de intenciones creativas / abiertas (Redacción, Objetivos, Ejemplos, Matemáticas, Teología)
        $is_generative_intent = false;
        $generative_keywords = ['redact', 'crear', 'objetivo', 'ejemplo', 'idea', 'cuanto es', 'calcula', 'explica', 'homiletic', 'teologi', 'sugerenci', 'escribe', 'dame', 'analiz', 'resumen', 'propuest'];
        foreach ($generative_keywords as $g_kw) {
            if (str_contains($q_lower, $g_kw)) {
                $is_generative_intent = true;
                break;
            }
        }

        // 2. PRIORIDAD 1: Consultar la Tabla `base_conocimiento_inbox_ai` en MySQL (si NO es una petición generativa abierta)
        if (!$is_generative_intent) {
            try {
                $kb_items = DB::table('base_conocimiento_inbox_ai')->where('estado', 1)->orderBy('id', 'desc')->get();
                $ignore_words = ['curso', 'cursos', 'mi', 'mis', 'el', 'la', 'los', 'las', 'de', 'del', 'un', 'una', 'para', 'por', 'con', 'en', 'al', 'que', 'como', 'donde'];

                foreach ($kb_items as $item) {
                    $keywords_str = $item->palabras_clave . ',' . $item->pregunta;
                    $keywords = array_map('trim', explode(',', mb_strtolower($keywords_str)));
                    
                    $match_found = false;
                    foreach ($keywords as $kw) {
                        if (empty($kw) || in_array($kw, $ignore_words)) {
                            continue;
                        }
                        
                        if (mb_strlen($kw) >= 4 && str_contains($q_lower, $kw)) {
                            $match_found = true;
                            break;
                        }
                    }

                    if ($match_found) {
                        $reply = nl2br($item->respuesta);

                        // A) Verificar si la imagen existe antes de renderizarla
                        if (!empty($item->imagen)) {
                            $img_path = public_path('uploads/conocimiento_ai/' . $item->imagen);
                            if (file_exists($img_path)) {
                                $img_url = asset('uploads/conocimiento_ai/' . $item->imagen);
                                $reply .= "<br><br><a href='{$img_url}' target='_blank'><img src='{$img_url}' class='img-fluid rounded-3 mt-2 shadow-sm' style='max-height: 240px; width: auto; border: 1px solid #cbd5e1; display: block;'></a>";
                            }
                        }

                        // B) Ruta del módulo
                        $ruta_final = trim($item->ruta ?? '');
                        if (!empty($ruta_final)) {
                            $ruta_url = str_contains($ruta_final, 'http') ? $ruta_final : (str_contains($ruta_final, '/') ? asset($ruta_final) : (\Illuminate\Support\Facades\Route::has($ruta_final) ? route($ruta_final) : url($ruta_final)));
                            $reply .= "<br><div class='mt-3'><a href='{$ruta_url}' class='btn btn-sm btn-success rounded-pill px-4 py-2 fw-bold text-white shadow-sm' style='background-color: #5fb230 !important; border: none; display: inline-flex; align-items: center;'><i class='bi bi-box-arrow-up-right me-2'></i> Ir al Módulo: " . htmlspecialchars($ruta_final) . "</a></div>";
                        }

                        // C) Métricas en vivo
                        if (str_contains($q_lower, 'mora') || str_contains($q_lower, 'deudores') || str_contains($q_lower, 'deuda')) {
                            $reply = "📊 **Estado de Morosidad en Tiempo Real (Inbox 2.0):**<br>- **Estudiantes Deudores:** {$total_deudores}<br>- **Total Vencido Acumulado:** ₡" . number_format($total_vencido, 2) . "<br><br>" . $reply;
                        } elseif (str_contains($q_lower, 'resumen cursos') || str_contains($q_lower, 'cuantos cursos') || str_contains($q_lower, 'total cursos')) {
                            $reply = "🎓 **Cursos Activos en Tiempo Real (Inbox 2.0):**<br>- Actualmente hay <strong>{$total_cursos_activos} cursos activos</strong> en la institución.<br><br>" . $reply;
                        }

                        return response()->json(['reply' => $reply]);
                    }
                }
            } catch (\Exception $e) {}
        }

        // 2. PRIORIDAD 2: Conectar directamente con Google Gemini API (Gemini Pro / Flash)
        $gemini_key = env('GEMINI_API_KEY', '');
        if (!empty($gemini_key)) {
            try {
                $appName = config('app.name', 'Inbox BPM');
                $appUrl = config('app.url', 'https://cefi.cr.com');
                $sys_info = "REGLA OBLIGATORIA: Responde DIRECTAMENTE a la consulta sin presentaciones repetitivas, sin frases introductorias como 'Hola, soy Inbox AI...' ni saludos largos. Ve directo al grano con la respuesta o contenido solicitado. Eres el copiloto inteligente de {$appName}. El sitio web oficial es {$appUrl}. Responde en español con formato limpio, claro y estructurado.";
                
                $response_g = Http::timeout(8)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$gemini_key}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $sys_info . "\n\nConsulta del usuario ({$user_name}): " . $query]
                            ]
                        ]
                    ]
                ]);

                if ($response_g->successful()) {
                    $data_g = $response_g->json();
                    if (isset($data_g['candidates'][0]['content']['parts'][0]['text'])) {
                        $raw_text = trim($data_g['candidates'][0]['content']['parts'][0]['text']);
                        if (!empty($raw_text)) {
                            $clean_text = nl2br(e($raw_text));
                            $clean_text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $clean_text);
                            return response()->json(['reply' => $clean_text]);
                        }
                    }
                }
            } catch (\Exception $e) {}
        }

        // 3. PRIORIDAD 3: Intentar llamar al VPS n8n si Gemini no responde
        $n8n_url = "https://n8n.renangalvan.net/webhook/inbox-ai-chat";
        try {
            $response = Http::timeout(4)->post($n8n_url, [
                'query' => $query,
                'user_name' => $user_name,
                'user_email' => $user_email,
                'client_id' => config('cliente.id', 'cefi'),
                'client_name' => config('cliente.nombre', 'CEFI'),
                'context' => [
                    'total_deudores' => $total_deudores,
                    'total_vencido' => $total_vencido,
                    'total_cursos_activos' => $total_cursos_activos
                ],
                'timestamp' => now()->toIso8601String(),
                'origen' => 'bpm_' . config('cliente.id', 'cefi') . '_laravel_2.0'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    $n8n_reply = trim($data['reply'] ?? $data['output'] ?? $data['message'] ?? '');
                    if (!empty($n8n_reply) && $n8n_reply !== "Workflow was started") {
                        return response()->json(['reply' => $n8n_reply]);
                    }
                }
            }
        } catch (\Exception $e) {}

        // 4. PRIORIDAD 4: Respuesta por defecto limpia
        $nombreCliente = config('cliente.nombre', 'CEFI');
        $default_reply = "Entendí tu consulta sobre *\"{$query}\"*. ¿Podrías ser un poco más específico con tu pregunta? Puedo ayudarte a redactar contenidos o guiarte en cualquier módulo del sistema BPM de {$nombreCliente}.";
        return response()->json(['reply' => $default_reply]);
    }
}
