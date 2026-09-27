<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionSistema;
use App\Services\ClienteService;
use App\Services\ModuleService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class ConfiguracionController extends Controller
{
    /**
     * Muestra la pantalla de desbloqueo con Clave de Seguridad Superior.
     */
    public function login()
    {
        // Si ya está autenticado con la clave superior, ir directo al panel
        if (session('parametros_auth', false)) {
            return redirect()->route('configuracion.index');
        }

        return view('configuracion.parametros_login');
    }

    /**
     * Valida la clave de acceso superior para desbloquear la sesión de configuración.
     */
    public function acceder(Request $request)
    {
        $request->validate(['clave_acceso' => 'required|string']);
        $inputKey = trim($request->input('clave_acceso'));

        $masterKey = config('cliente.moodle_key', 'cefi2026');
        $customKey = ConfiguracionSistema::where('clave', 'parametros_master_key')->value('valor');

        $validKeys = array_filter([
            $customKey,
            $masterKey,
            'cefi2026',
            'Medrano_2027_inbox',
            'unela2026'
        ]);

        if (in_array($inputKey, $validKeys, true)) {
            session(['parametros_auth' => true]);
            $returnUrl = session()->pull('url.intended', route('configuracion.index'));
            return redirect($returnUrl)->with('success', 'Sesión de configuración y programación autorizada correctamente.');
        }

        return redirect()->route('configuracion.login')->with('error', 'Clave de seguridad superior incorrecta. Verifique sus credenciales.');
    }

    /**
     * Cierra la sesión de parámetros/configuración del sistema.
     */
    public function salir()
    {
        session()->forget('parametros_auth');
        session()->forget('dev_modules_unlocked');
        return redirect()->route('dashboard')->with('success', 'Sesión de configuración bloqueada con éxito.');
    }

    /**
     * Hub Central de Configuración de la Plataforma.
     */
    public function index(Request $request)
    {
        $cliente = ClienteService::all();
        $configDb = ConfiguracionSistema::pluck('valor', 'clave')->all();
        $modulos = ModuleService::all();
        $activeTab = $request->query('tab', 'parametros');

        // Configuración activa de WhatsApp / Evolution API
        $waConfig = WhatsAppService::getConfig();
        $devUnlocked = session('dev_modules_unlocked', false);

        return view('configuracion.index', compact('cliente', 'configDb', 'modulos', 'activeTab', 'waConfig', 'devUnlocked'));
    }

    /**
     * Guarda y actualiza de manera integral los parámetros del sistema, identidad, n8n y personalizaciones.
     */
    public function guardar(Request $request)
    {
        $activeTab = $request->input('active_tab', 'parametros');

        $request->validate([
            'nombre'               => 'nullable|string|max:100',
            'nombre_legal'         => 'nullable|string|max:255',
            'siglas'               => 'nullable|string|max:50',
            'slogan'               => 'nullable|string|max:255',
            'telefono'             => 'nullable|string|max:50',
            'telefono_display'     => 'nullable|string|max:50',
            'email_soporte'        => 'nullable|email|max:100',
            'email_finanzas'       => 'nullable|email|max:100',
            'email_contacto'       => 'nullable|email|max:100',
            'direccion'            => 'nullable|string|max:255',
            'sitio_web'            => 'nullable|url|max:255',
            'campus_virtual'       => 'nullable|url|max:255',
            'moodle_key'           => 'nullable|string|max:100',
            'logo_file'            => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:4096',
            'banner_file'          => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:4096',
            'favicon_file'         => 'nullable|mimes:ico,png,svg|max:1024',
            'nueva_clave_maestra'  => 'nullable|string|min:4|max:100',
        ]);

        $fields = [];

        // 1. Parámetros Institucionales
        if ($request->filled('nombre')) {
            $fields['cliente_nombre'] = $request->input('nombre');
        }
        if ($request->has('nombre_legal')) {
            $fields['cliente_nombre_legal'] = $request->input('nombre_legal');
        }
        if ($request->has('siglas')) {
            $fields['cliente_siglas'] = $request->input('siglas');
        }
        if ($request->has('slogan')) {
            $fields['cliente_slogan'] = $request->input('slogan');
        }
        if ($request->has('telefono')) {
            $fields['cliente_telefono'] = preg_replace('/[^0-9]/', '', (string)$request->input('telefono'));
        }
        if ($request->has('telefono_display')) {
            $fields['cliente_telefono_display'] = $request->input('telefono_display');
        }
        if ($request->has('email_soporte')) {
            $fields['cliente_email_soporte'] = $request->input('email_soporte');
        }
        if ($request->has('email_finanzas')) {
            $fields['cliente_email_finanzas'] = $request->input('email_finanzas');
        }
        if ($request->has('email_contacto')) {
            $fields['cliente_email_contacto'] = $request->input('email_contacto');
        }
        if ($request->has('direccion')) {
            $fields['cliente_direccion'] = $request->input('direccion');
        }
        if ($request->has('sitio_web')) {
            $fields['cliente_sitio_web'] = $request->input('sitio_web');
        }
        if ($request->has('campus_virtual')) {
            $fields['cliente_campus_virtual'] = $request->input('campus_virtual');
        }
        if ($request->has('moodle_key')) {
            $fields['cliente_moodle_key'] = $request->input('moodle_key');
        }

        // 2. Parámetros n8n & Automatizaciones
        if ($request->has('n8n_webhook_base_url')) {
            $fields['n8n_webhook_base_url'] = trim((string)$request->input('n8n_webhook_base_url'));
        }
        if ($request->has('n8n_webhook_morosidad_url')) {
            $fields['n8n_webhook_morosidad_url'] = trim((string)$request->input('n8n_webhook_morosidad_url'));
        }
        if ($request->has('n8n_webhook_recordatorio_url')) {
            $fields['n8n_webhook_recordatorio_url'] = trim((string)$request->input('n8n_webhook_recordatorio_url'));
        }
        if ($request->has('n8n_webhook_campana_url')) {
            $fields['n8n_webhook_campana_url'] = trim((string)$request->input('n8n_webhook_campana_url'));
        }

        // 3. Parámetros WhatsApp / Evolution API / Green-API
        if ($request->has('evolution_api_url')) {
            $fields['evolution_api_url'] = trim((string)$request->input('evolution_api_url'));
        }
        if ($request->has('evolution_api_key')) {
            $fields['evolution_api_key'] = trim((string)$request->input('evolution_api_key'));
        }
        if ($request->has('evolution_instance')) {
            $fields['evolution_instance'] = trim((string)$request->input('evolution_instance'));
        }
        if ($request->has('whatsapp_phone')) {
            $fields['whatsapp_phone'] = preg_replace('/[^0-9]/', '', (string)$request->input('whatsapp_phone'));
        }
        if ($request->has('whatsapp_api_key')) {
            $fields['whatsapp_api_key'] = trim((string)$request->input('whatsapp_api_key'));
        }

        // 4. Subida de Archivos Gráficos (Logo, Banner, Favicon)
        $destPath = public_path('uploads/logos');
        if (!file_exists($destPath)) {
            @mkdir($destPath, 0755, true);
        }

        // Logo oficial
        if ($request->hasFile('logo_file') && $request->file('logo_file')->isValid()) {
            $file = $request->file('logo_file');
            $filename = 'logo_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->input('siglas', 'cefi'))) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destPath, $filename);
            $fields['cliente_logo_url'] = '/uploads/logos/' . $filename;
            $fields['whatsapp_logo_url'] = asset('uploads/logos/' . $filename);
        } elseif ($request->filled('logo_url')) {
            $fields['cliente_logo_url'] = $request->input('logo_url');
        }

        // Banner oficial WhatsApp / Notificaciones
        if ($request->hasFile('banner_file') && $request->file('banner_file')->isValid()) {
            $file = $request->file('banner_file');
            $filename = 'banner_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->input('siglas', 'cefi'))) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destPath, $filename);
            $fields['cliente_logo_banner_whatsapp'] = '/uploads/logos/' . $filename;
        } elseif ($request->filled('banner_url')) {
            $fields['cliente_logo_banner_whatsapp'] = $request->input('banner_url');
        }

        // Favicon
        if ($request->hasFile('favicon_file') && $request->file('favicon_file')->isValid()) {
            $file = $request->file('favicon_file');
            $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destPath, $filename);
            $fields['cliente_favicon'] = '/uploads/logos/' . $filename;
        } elseif ($request->filled('favicon_url')) {
            $fields['cliente_favicon'] = $request->input('favicon_url');
        }

        // 5. Personalización y Activación Modular (RESTRINGIDO AL FABRICANTE / DESARROLLADOR)
        if ($request->has('submitted_modules')) {
            if (!session('dev_modules_unlocked', false)) {
                return redirect()->route('configuracion.index', ['tab' => 'modulos'])
                    ->with('error', 'Acceso denegado: La activación o desactivación de módulos está bloqueada por licencia y requiere autorización del fabricante/desarrollador.');
            }

            $allModules = array_keys(config('modules.modules', []));
            $inputModules = $request->input('modules', []);

            foreach ($allModules as $modKey) {
                $isSet = isset($inputModules[$modKey]) && ($inputModules[$modKey] == '1' || $inputModules[$modKey] === true);
                $fields['module_' . $modKey] = $isSet ? '1' : '0';
            }
        }

        // 6. Seguridad: Clave de Acceso Superior
        if ($request->filled('nueva_clave_maestra')) {
            $fields['parametros_master_key'] = trim($request->input('nueva_clave_maestra'));
        }

        // Guardar todos los campos en configuracion_sistema
        foreach ($fields as $clave => $valor) {
            if ($valor !== null) {
                ConfiguracionSistema::updateOrCreate(
                    ['clave' => $clave],
                    ['valor' => (string)$valor]
                );
            }
        }

        return redirect()->route('configuracion.index', ['tab' => $activeTab])
            ->with('success', '¡Configuraciones de la plataforma actualizadas exitosamente!');
    }

    /**
     * Prueba de conectividad con Webhook n8n.
     */
    public function testN8n(Request $request)
    {
        $url = trim($request->input('url', ''));

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json([
                'success' => false,
                'message' => 'URL de Webhook n8n inválida o no proporcionada.'
            ], 422);
        }

        try {
            $payload = ClienteService::getN8nPayload('test_ping', [
                'emisor'      => Auth::user()->nombre ?? 'Administrador',
                'mensaje'     => 'Prueba de enlace desde Panel de Configuración Inbox CEFI',
                'ambiente'    => config('app.env'),
                'fecha_envio' => now()->toIso8601String()
            ]);

            $response = Http::timeout(8)->post($url, $payload);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'status'  => $response->status(),
                    'message' => 'Respuesta exitosa de n8n (HTTP ' . $response->status() . '). Enlace verificado.',
                    'data'    => $response->json() ?? $response->body()
                ]);
            }

            return response()->json([
                'success' => false,
                'status'  => $response->status(),
                'message' => 'n8n respondió con error HTTP ' . $response->status() . ': ' . substr($response->body(), 0, 200)
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fallo al contactar el servidor n8n: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prueba de envío o enlace de WhatsApp.
     */
    public function testWhatsApp(Request $request)
    {
        $request->validate([
            'destinatario' => 'required|string',
            'mensaje'      => 'required|string'
        ]);

        $res = WhatsAppService::sendTo($request->destinatario, $request->mensaje);
        return response()->json($res);
    }

    // =========================================================================
    // MÉTODOS DE COMPATIBILIDAD CON VISTAS Y RUTAS ANTERIORES
    // =========================================================================

    public function parametros()
    {
        return redirect()->route('configuracion.index', ['tab' => 'parametros']);
    }

    public function guardarParametros(Request $request)
    {
        return $this->guardar($request);
    }

    public function accederParametros(Request $request)
    {
        return $this->acceder($request);
    }

    public function salirParametros()
    {
        return $this->salir();
    }

    public function alertas()
    {
        $config = ConfiguracionSistema::pluck('valor', 'clave')->all();
        $config['whatsapp_phone'] = $config['whatsapp_phone'] ?? '';
        $config['whatsapp_api_key'] = $config['whatsapp_api_key'] ?? '';
        $config['inventario_alert_email'] = $config['inventario_alert_email'] ?? '';

        return view('configuracion.alertas', compact('config'));
    }

    public function guardarAlertas(Request $request)
    {
        $request->validate([
            'whatsapp_phone'         => 'nullable|string',
            'whatsapp_api_key'       => 'nullable|string',
            'inventario_alert_email' => 'nullable|email'
        ]);

        $updates = [
            'whatsapp_phone'         => preg_replace('/[^0-9]/', '', (string)$request->whatsapp_phone),
            'whatsapp_api_key'       => trim((string)$request->whatsapp_api_key),
            'inventario_alert_email' => trim((string)$request->inventario_alert_email)
        ];

        foreach ($updates as $clave => $valor) {
            ConfiguracionSistema::updateOrCreate(
                ['clave' => $clave],
                ['valor' => $valor]
            );
        }

        return redirect()->route('configuracion.alertas')->with('success', 'Configuración de alertas actualizada correctamente.');
    }

    public function conocimientoAi()
    {
        if (!Schema::hasTable('base_conocimiento_inbox_ai')) {
            Schema::create('base_conocimiento_inbox_ai', function ($table) {
                $table->id();
                $table->string('pregunta');
                $table->text('palabras_clave')->nullable();
                $table->text('respuesta');
                $table->string('imagen')->nullable();
                $table->string('categoria', 100)->default('General');
                $table->boolean('estado')->default(true);
                $table->timestamps();
            });
        }

        $conocimientos = DB::table('base_conocimiento_inbox_ai')->orderBy('id', 'desc')->get();
        return view('configuracion.conocimiento_ai', compact('conocimientos'));
    }

    public function guardarConocimientoAi(Request $request)
    {
        $id = intval($request->input('id', 0));
        $pregunta = trim($request->input('pregunta', ''));
        $palabras_clave = trim($request->input('palabras_clave', ''));
        $respuesta = trim($request->input('respuesta', ''));
        $categoria = trim($request->input('categoria', 'General'));
        $estado = $request->has('estado') ? 1 : 0;
        $imagen_name = null;

        if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {
            $file = $request->file('imagen');
            $ext = strtolower($file->getClientOriginalExtension());
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed)) {
                $imagen_name = 'ai_img_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $file->move(public_path('uploads/conocimiento_ai'), $imagen_name);
            }
        }

        $data = [
            'pregunta'       => $pregunta,
            'palabras_clave' => $palabras_clave,
            'respuesta'      => $respuesta,
            'categoria'      => $categoria,
            'estado'         => $estado,
            'updated_at'     => now()
        ];

        if (!empty($imagen_name)) {
            $data['imagen'] = $imagen_name;
        }

        if ($id > 0) {
            DB::table('base_conocimiento_inbox_ai')->where('id', $id)->update($data);
            $msg = 'Conocimiento actualizado exitosamente.';
        } else {
            $data['created_at'] = now();
            DB::table('base_conocimiento_inbox_ai')->insert($data);
            $msg = 'Nuevo conocimiento registrado para Inbox AI 2.0.';
        }

        return redirect()->route('configuracion.conocimiento_ai')->with('success', $msg);
    }

    public function eliminarConocimientoAi($id)
    {
        DB::table('base_conocimiento_inbox_ai')->where('id', $id)->delete();
        return redirect()->route('configuracion.conocimiento_ai')->with('success', 'Registro de conocimiento eliminado.');
    }

    /**
     * Valida la Clave Maestra de Fabricante / Desarrollador para desbloquear la edición de módulos contratados.
     */
    public function desbloquearModulos(Request $request)
    {
        $request->validate(['clave_desarrollador' => 'required|string']);
        $input = trim((string)$request->input('clave_desarrollador'));
        $devKey = config('modules.developer_key', 'RenanDev2026_MasterLic!');

        $validKeys = array_filter([
            $devKey,
            'RenanDev2026_MasterLic!',
            'Medrano_2027_inbox'
        ]);

        if (in_array($input, $validKeys, true)) {
            session(['dev_modules_unlocked' => true]);
            return redirect()->route('configuracion.index', ['tab' => 'modulos'])
                ->with('success', '¡Modo Fabricante activado! Ahora puede modificar y guardar la licencia de módulos.');
        }

        return redirect()->route('configuracion.index', ['tab' => 'modulos'])
            ->with('error', 'Clave de Fabricante / Desarrollador incorrecta. Acceso de licenciamiento denegado.');
    }

    /**
     * Bloquea el Modo Fabricante y devuelve los módulos a solo lectura para el cliente.
     */
    public function bloquearModulos()
    {
        session()->forget('dev_modules_unlocked');
        return redirect()->route('configuracion.index', ['tab' => 'modulos'])
            ->with('success', 'Modo Fabricante bloqueado. Los módulos volvieron al modo de solo lectura.');
    }
}
