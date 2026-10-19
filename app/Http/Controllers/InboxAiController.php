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

        // 1. PRIORIDAD 1: Consultar la Base de Conocimiento RAG en MySQL
        try {
            $kb_items = DB::table('base_conocimiento_inbox_ai')->where('estado', 1)->orderBy('id', 'desc')->get();
            $matchedItem = $this->findBestKbMatch($query, $kb_items);

            if ($matchedItem) {
                $reply = nl2br($matchedItem->respuesta);

                // A) Verificar si la imagen existe antes de renderizarla
                if (!empty($matchedItem->imagen)) {
                    $img_path = public_path('uploads/conocimiento_ai/' . $matchedItem->imagen);
                    if (file_exists($img_path)) {
                        $img_url = asset('uploads/conocimiento_ai/' . $matchedItem->imagen);
                        $reply .= "<br><br><a href='{$img_url}' target='_blank'><img src='{$img_url}' class='img-fluid rounded-3 mt-2 shadow-sm' style='max-height: 240px; width: auto; border: 1px solid #cbd5e1; display: block;'></a>";
                    }
                }

                // B) Ruta del módulo
                $ruta_final = trim($matchedItem->ruta ?? '');
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
        } catch (\Exception $e) {}

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

    /**
     * Normaliza un texto eliminando tildes, caracteres especiales y convirtiendo a minúsculas.
     */
    private function normalizeText(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $replacements = [
            'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 'ü'=>'u', 'ñ'=>'n',
            'à'=>'a', 'è'=>'e', 'ì'=>'i', 'ò'=>'o', 'ù'=>'u',
            'Á'=>'a', 'É'=>'e', 'Í'=>'i', 'Ó'=>'o', 'Ú'=>'u', 'Ü'=>'u', 'Ñ'=>'n'
        ];
        $text = strtr($text, $replacements);
        $text = preg_replace('/[^\w\s]/u', ' ', $text);
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    /**
     * Extrae palabras clave significativas de una cadena excluyendo stopwords gramaticales.
     */
    private function extractTokens(string $text): array
    {
        $normalized = $this->normalizeText($text);
        $words = explode(' ', $normalized);
        $stopwords = [
            'el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas', 'de', 'del',
            'al', 'a', 'en', 'para', 'por', 'con', 'sin', 'y', 'o', 'u', 'e',
            'que', 'como', 'donde', 'cuando', 'quien', 'cual', 'cuanto', 'cuanta',
            'mi', 'mis', 'tu', 'tus', 'su', 'sus', 'yo', 'me', 'se', 'te',
            'es', 'son', 'fue', 'era', 'hay', 'hacer', 'puedo', 'puedes', 'saber'
        ];

        $tokens = [];
        foreach ($words as $w) {
            $w = trim($w);
            if (mb_strlen($w) >= 3 && !in_array($w, $stopwords, true)) {
                $tokens[] = $w;
            }
        }
        return array_unique($tokens);
    }

    /**
     * Algoritmo de puntuación semántica y de palabras clave contra la Base de Conocimiento RAG.
     */
    private function findBestKbMatch(string $query, $kb_items)
    {
        $queryNormalized = $this->normalizeText($query);
        $queryTokens = $this->extractTokens($query);

        if (empty($queryTokens) && empty($queryNormalized)) {
            return null;
        }

        $bestItem = null;
        $highestScore = 0;

        foreach ($kb_items as $item) {
            $score = 0;
            $preguntaNorm = $this->normalizeText($item->pregunta ?? '');
            $palabrasNorm = $this->normalizeText($item->palabras_clave ?? '');
            $categoriaNorm = $this->normalizeText($item->categoria ?? '');

            // 1. Coincidencia exacta de frase completa
            if (!empty($queryNormalized)) {
                if (str_contains($preguntaNorm, $queryNormalized)) {
                    $score += 50;
                }
                if (str_contains($palabrasNorm, $queryNormalized)) {
                    $score += 40;
                }
            }

            // 2. Coincidencia por palabras clave declaradas
            if (!empty($item->palabras_clave)) {
                $rawKeywords = array_map('trim', explode(',', $item->palabras_clave));
                foreach ($rawKeywords as $rawKw) {
                    $normKw = $this->normalizeText($rawKw);
                    if (!empty($normKw)) {
                        if (str_contains($queryNormalized, $normKw)) {
                            $score += 30;
                        }
                    }
                }
            }

            // 3. Coincidencia por tokens
            $preguntaTokens = $this->extractTokens($item->pregunta ?? '');
            $keywordsTokens = $this->extractTokens($item->palabras_clave ?? '');
            $categoriaTokens = $this->extractTokens($item->categoria ?? '');

            $matchedTokensCount = 0;
            foreach ($queryTokens as $qToken) {
                $tokenMatched = false;
                if (in_array($qToken, $keywordsTokens, true) || str_contains($palabrasNorm, $qToken)) {
                    $score += 15;
                    $tokenMatched = true;
                }
                if (in_array($qToken, $preguntaTokens, true) || str_contains($preguntaNorm, $qToken)) {
                    $score += 12;
                    $tokenMatched = true;
                }
                if (in_array($qToken, $categoriaTokens, true) || str_contains($categoriaNorm, $qToken)) {
                    $score += 5;
                    $tokenMatched = true;
                }

                if ($tokenMatched) {
                    $matchedTokensCount++;
                }
            }

            // Bonus si todos los tokens del usuario coincidieron
            if (!empty($queryTokens) && $matchedTokensCount === count($queryTokens)) {
                $score += 25;
            }

            if ($score > $highestScore) {
                $highestScore = $score;
                $bestItem = $item;
            }
        }

        // Umbral de aceptación
        if ($highestScore >= 12) {
            return $bestItem;
        }

        return null;
    }
}
