<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionSistema;
use App\Models\MarketingProspecto;
use App\Models\Programa;
use App\Models\Usuario;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CampanaWhatsAppController extends Controller
{
    /**
     * Muestra la interfaz principal de Campañas de Difusión de Oferta Académica.
     */
    public function index()
    {
        $programas = Programa::orderBy('nombre_programa', 'asc')->get();
        
        $totalEstudiantes = Usuario::where('id_rol', 3)->count();
        $totalEstudiantesConTelefono = Usuario::where('id_rol', 3)
            ->whereNotNull('telefono')
            ->where('telefono', '!=', '')
            ->count();

        $totalUsuariosConTelefono = Usuario::whereNotNull('telefono')
            ->where('telefono', '!=', '')
            ->count();

        // Prospectos de Marketing Externos
        try {
            $totalProspectosConTelefono = MarketingProspecto::activosConWhatsApp()->count();
            $origenesProspectos = MarketingProspecto::select('origen')->distinct()->pluck('origen')->filter()->values();
        } catch (\Throwable $e) {
            $totalProspectosConTelefono = 0;
            $origenesProspectos = collect();
        }

        $config = WhatsAppService::getConfig();

        // Plantilla predeterminada de lanzamiento
        $plantillaDefault = "🏛️ *UNIVERSIDAD UNELA - Nueva Oferta Académica*\n\n" .
                            "¡Hola {nombre}! Esperamos que te encuentres muy bien.\n\n" .
                            "Nos alegra presentarte nuestra *Nueva Oferta Académica y Programas Especializados* diseñados para impulsar tu crecimiento profesional y ministerial.\n\n" .
                            "✨ *Beneficios Exclusivos para Estudiantes y Graduados:*\n" .
                            "• Modalidad 100% virtual y flexible.\n" .
                            "• Convalidaciones directas y planes de pago en cuotas.\n" .
                            "• Certificación universitaria internacional.\n\n" .
                            "📲 *¿Deseas conocer los detalles del plan de estudios y matrícula?*\n" .
                            "Responde a este mensaje con la palabra *INFO* o contáctanos directamente a admisiones.\n\n" .
                            "_Universidad Evangélica de las Américas - Formando Líderes para el Mundo_";

        return view('configuracion.campanas', compact(
            'programas',
            'totalEstudiantes',
            'totalEstudiantesConTelefono',
            'totalUsuariosConTelefono',
            'totalProspectosConTelefono',
            'origenesProspectos',
            'config',
            'plantillaDefault'
        ));
    }

    /**
     * Devuelve el recuento y listado previo de destinatarios vía AJAX según los filtros elegidos.
     */
    public function destinatariosAjax(Request $request)
    {
        $filtro = $request->input('filtro_audiencia', 'todos_estudiantes');
        $idPrograma = $request->input('id_programa');
        $origenProspecto = $request->input('origen_prospecto');

        $destinatariosMuestra = [];
        $total = 0;

        if ($filtro === 'prospectos_todos') {
            // Todos los prospectos de marketing externos
            $query = MarketingProspecto::activosConWhatsApp();
            $total = $query->count();
            $destinatariosMuestra = $query->limit(8)->get()->map(function ($p) {
                return [
                    'nombre' => $p->nombre_completo,
                    'telefono' => $p->telefono
                ];
            });
        } elseif ($filtro === 'prospectos_origen' && !empty($origenProspecto)) {
            // Prospectos filtrados por origen
            $query = MarketingProspecto::activosConWhatsApp()->where('origen', $origenProspecto);
            $total = $query->count();
            $destinatariosMuestra = $query->limit(8)->get()->map(function ($p) {
                return [
                    'nombre' => $p->nombre_completo,
                    'telefono' => $p->telefono
                ];
            });
        } elseif ($filtro === 'mixto') {
            // Estudiantes + Prospectos externos
            $estCount = Usuario::where('id_rol', 3)->whereNotNull('telefono')->where('telefono', '!=', '')->count();
            $prosCount = MarketingProspecto::activosConWhatsApp()->count();
            $total = $estCount + $prosCount;

            $destinatariosMuestra = Usuario::where('id_rol', 3)->whereNotNull('telefono')->where('telefono', '!=', '')->limit(4)->get()->map(function ($u) {
                return ['nombre' => trim($u->nombre . ' ' . ($u->apellidos ?? '')), 'telefono' => WhatsAppService::normalizarTelefono($u->telefono)];
            })->concat(
                MarketingProspecto::activosConWhatsApp()->limit(4)->get()->map(function ($p) {
                    return ['nombre' => $p->nombre_completo, 'telefono' => $p->telefono];
                })
            );
        } else {
            // Filtros basados en usuarios / estudiantes
            $query = Usuario::query()
                ->whereNotNull('telefono')
                ->where('telefono', '!=', '');

            if ($filtro === 'todos_estudiantes') {
                $query->where('id_rol', 3);
            } elseif ($filtro === 'todos_usuarios') {
                // Todos los usuarios registrados
            } elseif ($filtro === 'programa' && !empty($idPrograma)) {
                $query->where('id_rol', 3)
                      ->whereExists(function ($sub) use ($idPrograma) {
                          $sub->selectRaw(1)
                              ->from('boletas')
                              ->whereColumn('boletas.id_estudiante', 'usuarios.id')
                              ->where('boletas.id_programa', $idPrograma);
                      });
            }

            $total = $query->count();
            $destinatariosMuestra = $query->select('id', 'nombre', 'apellidos', 'telefono')
                ->limit(8)
                ->get()
                ->map(function ($u) {
                    return [
                        'nombre' => trim($u->nombre . ' ' . ($u->apellidos ?? '')),
                        'telefono' => WhatsAppService::normalizarTelefono($u->telefono)
                    ];
                });
        }

        return response()->json([
            'total' => $total,
            'muestra' => $destinatariosMuestra
        ]);
    }

    /**
     * Envía una prueba unitaria del flyer y texto al número del administrador.
     */
    public function testEnvio(Request $request)
    {
        $request->validate([
            'telefono_test' => 'required|string',
            'mensaje' => 'required|string',
            'flyer_url' => 'nullable|string',
            'flyer_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        $flyerUrl = $this->resolverFlyerUrl($request);

        // Reemplazar variables simuladas
        $testNombre = 'Carlos';
        $testApellidos = 'Mendoza';
        $mensajeFormat = str_replace(
            ['{nombre}', '{apellidos}', '{nombre_completo}', '{telefono}'],
            [$testNombre, $testApellidos, "{$testNombre} {$testApellidos}", $request->telefono_test],
            $request->mensaje
        );

        $res = WhatsAppService::sendViaEvolutionApi(
            $request->telefono_test,
            $mensajeFormat,
            $flyerUrl,
            'oferta_academica.jpg'
        );

        return response()->json(array_merge($res, [
            'flyer_url' => $flyerUrl,
            'preview_mensaje' => $mensajeFormat
        ]));
    }

    /**
     * Lanza la campaña de difusión masiva hacia n8n o la encola con retardo seguro.
     */
    public function lanzar(Request $request)
    {
        $request->validate([
            'titulo_campana' => 'required|string|max:150',
            'mensaje_template' => 'required|string',
            'filtro_audiencia' => 'required|string',
            'intervalo_segundos' => 'nullable|integer|min:10|max:60',
            'flyer_url' => 'nullable|string',
            'flyer_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        $flyerUrl = $this->resolverFlyerUrl($request);
        $intervalo = (int)$request->input('intervalo_segundos', 20);

        // Filtrar destinatarios con teléfono válido según la audiencia elegida
        $destinatarios = [];

        if ($request->filtro_audiencia === 'prospectos_todos') {
            $prospectos = MarketingProspecto::activosConWhatsApp()->get();
            foreach ($prospectos as $p) {
                $tel = $p->telefono;
                if (!empty($tel) && strlen($tel) >= 8) {
                    $destinatarios[] = [
                        'id' => 'LEAD-' . $p->id,
                        'nombre' => trim($p->nombre ?: 'Estimado(a)'),
                        'apellidos' => trim($p->apellidos ?? ''),
                        'nombre_completo' => $p->nombre_completo,
                        'telefono' => $tel,
                        'email' => $p->email
                    ];
                }
            }
        } elseif ($request->filtro_audiencia === 'prospectos_origen' && $request->filled('origen_prospecto')) {
            $prospectos = MarketingProspecto::activosConWhatsApp()->where('origen', $request->origen_prospecto)->get();
            foreach ($prospectos as $p) {
                $tel = $p->telefono;
                if (!empty($tel) && strlen($tel) >= 8) {
                    $destinatarios[] = [
                        'id' => 'LEAD-' . $p->id,
                        'nombre' => trim($p->nombre ?: 'Estimado(a)'),
                        'apellidos' => trim($p->apellidos ?? ''),
                        'nombre_completo' => $p->nombre_completo,
                        'telefono' => $tel,
                        'email' => $p->email
                    ];
                }
            }
        } elseif ($request->filtro_audiencia === 'mixto') {
            // Estudiantes
            $estudiantes = Usuario::where('id_rol', 3)->whereNotNull('telefono')->where('telefono', '!=', '')->get();
            foreach ($estudiantes as $u) {
                $tel = WhatsAppService::normalizarTelefono($u->telefono);
                if (!empty($tel) && strlen($tel) >= 8) {
                    $destinatarios[] = [
                        'id' => 'EST-' . $u->id,
                        'nombre' => trim($u->nombre),
                        'apellidos' => trim($u->apellidos ?? ''),
                        'nombre_completo' => trim($u->nombre . ' ' . ($u->apellidos ?? '')),
                        'telefono' => $tel,
                        'email' => $u->email
                    ];
                }
            }
            // + Prospectos
            $prospectos = MarketingProspecto::activosConWhatsApp()->get();
            foreach ($prospectos as $p) {
                $tel = $p->telefono;
                if (!empty($tel) && strlen($tel) >= 8) {
                    $destinatarios[] = [
                        'id' => 'LEAD-' . $p->id,
                        'nombre' => trim($p->nombre ?: 'Estimado(a)'),
                        'apellidos' => trim($p->apellidos ?? ''),
                        'nombre_completo' => $p->nombre_completo,
                        'telefono' => $tel,
                        'email' => $p->email
                    ];
                }
            }
        } else {
            // Audiencias basadas en usuarios
            $query = Usuario::query()
                ->whereNotNull('telefono')
                ->where('telefono', '!=', '');

            if ($request->filtro_audiencia === 'todos_estudiantes') {
                $query->where('id_rol', 3);
            } elseif ($request->filtro_audiencia === 'programa' && $request->filled('id_programa')) {
                $idProg = $request->id_programa;
                $query->where('id_rol', 3)
                      ->whereExists(function ($sub) use ($idProg) {
                          $sub->selectRaw(1)
                              ->from('boletas')
                              ->whereColumn('boletas.id_estudiante', 'usuarios.id')
                              ->where('boletas.id_programa', $idProg);
                      });
            }

            $usuarios = $query->select('id', 'nombre', 'apellidos', 'telefono', 'email')->get();
            foreach ($usuarios as $u) {
                $tel = WhatsAppService::normalizarTelefono($u->telefono);
                if (!empty($tel) && strlen($tel) >= 8) {
                    $destinatarios[] = [
                        'id' => 'USR-' . $u->id,
                        'nombre' => trim($u->nombre),
                        'apellidos' => trim($u->apellidos ?? ''),
                        'nombre_completo' => trim($u->nombre . ' ' . ($u->apellidos ?? '')),
                        'telefono' => $tel,
                        'email' => $u->email
                    ];
                }
            }
        }

        if (empty($destinatarios)) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron destinatarios con números de teléfono válidos para este filtro.'
            ], 422);
        }

        $cfg = WhatsAppService::getConfig();
        $webhookN8n = $cfg['n8n_campana_webhook'] ?: ($request->input('webhook_n8n_override') ?: 'https://n8n.renangalvan.net/webhook/campana-whatsapp');

        $payload = [
            'campana_id' => 'CAMP-' . date('Ymd-His'),
            'titulo' => $request->titulo_campana,
            'fecha_lanzamiento' => now()->toIso8601String(),
            'media_url' => $flyerUrl,
            'caption_template' => $request->mensaje_template,
            'intervalo_segundos' => $intervalo,
            'total_destinatarios' => count($destinatarios),
            'destinatarios' => $destinatarios,
            'evolution_api' => [
                'server_url' => $cfg['evolution_url'],
                'instance' => $cfg['evolution_instance'],
                'apikey' => $cfg['evolution_key']
            ]
        ];

        // Despachar a n8n para ejecución en segundo plano (Background Worker)
        try {
            $response = Http::timeout(15)->post($webhookN8n, $payload);
            
            Log::info("Campaña de WhatsApp despachada a n8n ({$payload['campana_id']}): " . count($destinatarios) . " destinatarios.");

            return response()->json([
                'success' => true,
                'campana_id' => $payload['campana_id'],
                'total' => count($destinatarios),
                'tiempo_estimado_minutos' => round((count($destinatarios) * $intervalo) / 60, 1),
                'message' => "¡Campaña '{$request->titulo_campana}' iniciada con éxito! Se despacharán " . count($destinatarios) . " mensajes con retardo seguro de {$intervalo}s."
            ]);
        } catch (\Exception $e) {
            Log::warning("No se pudo conectar con el webhook de n8n ({$webhookN8n}): " . $e->getMessage());

            return response()->json([
                'success' => true,
                'campana_id' => $payload['campana_id'],
                'total' => count($destinatarios),
                'tiempo_estimado_minutos' => round((count($destinatarios) * $intervalo) / 60, 1),
                'message' => "Campaña preparada (" . count($destinatarios) . " destinatarios listos). Si el webhook n8n aún no está publicado, puedes activar el flujo en n8n."
            ]);
        }
    }

    /**
     * Guarda la URL del Webhook de Campañas en la configuración del sistema.
     */
    public function guardarConfigWebhook(Request $request)
    {
        $request->validate([
            'n8n_webhook_campana_url' => 'nullable|url'
        ]);

        ConfiguracionSistema::updateOrCreate(
            ['clave' => 'n8n_webhook_campana_url'],
            ['valor' => trim($request->input('n8n_webhook_campana_url', ''))]
        );

        return back()->with('success', 'Webhook de campañas n8n guardado exitosamente.');
    }

    /**
     * Resuelve la URL pública del flyer (subido o ingresado por enlace).
     */
    private function resolverFlyerUrl(Request $request): ?string
    {
        if ($request->hasFile('flyer_file') && $request->file('flyer_file')->isValid()) {
            $file = $request->file('flyer_file');
            $dir = public_path('uploads/campanas');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = 'flyer_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            return asset('uploads/campanas/' . $filename);
        }

        if ($request->filled('flyer_url')) {
            return trim($request->input('flyer_url'));
        }

        $cfg = WhatsAppService::getConfig();
        return $cfg['logo_url'] ?? 'https://unela.org/bpm_unela/imgs/logo_unela_banner.jpg';
    }
}
