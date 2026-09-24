<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionSistema;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    /**
     * Muestra la interfaz de configuración y probador en vivo de WhatsApp / Green-API.
     */
    public function configuracion()
    {
        $config = WhatsAppService::getConfig();
        return view('configuracion.whatsapp', compact('config'));
    }

    /**
     * Guarda los parámetros de conexión de Green-API, logos y webhooks.
     */
    public function guardarConfiguracion(Request $request)
    {
        $request->validate([
            'green_api_instance' => 'nullable|string',
            'green_api_token' => 'nullable|string',
            'green_api_url' => 'nullable|url',
            'whatsapp_phone' => 'nullable|string',
            'whatsapp_logo_url' => 'nullable|url',
            'n8n_webhook_recordatorio_url' => 'nullable|url',
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072'
        ]);

        $updates = [
            'green_api_instance' => trim($request->input('green_api_instance', '')),
            'green_api_token' => trim($request->input('green_api_token', '')),
            'green_api_url' => trim($request->input('green_api_url', 'https://7107.api.greenapi.com')),
            'whatsapp_phone' => preg_replace('/[^0-9]/', '', (string)$request->input('whatsapp_phone', '')),
            'whatsapp_adjuntar_logo' => $request->has('whatsapp_adjuntar_logo') ? '1' : '0',
            'n8n_webhook_recordatorio_url' => trim($request->input('n8n_webhook_recordatorio_url', ''))
        ];

        if ($request->filled('whatsapp_logo_url')) {
            $updates['whatsapp_logo_url'] = trim($request->input('whatsapp_logo_url'));
        }

        // Si se subió un nuevo archivo de logo institucional
        if ($request->hasFile('logo_file') && $request->file('logo_file')->isValid()) {
            $file = $request->file('logo_file');
            $filename = 'logo_whatsapp_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/logos'), $filename);
            $updates['whatsapp_logo_url'] = asset('uploads/logos/' . $filename);
        }

        foreach ($updates as $clave => $valor) {
            ConfiguracionSistema::updateOrCreate(
                ['clave' => $clave],
                ['valor' => $valor]
            );
        }

        return redirect()->route('configuracion.whatsapp')->with('success', 'Configuración de WhatsApp guardada exitosamente.');
    }

    /**
     * Prueba de envío en vivo desde el panel de control.
     */
    public function testEnvio(Request $request)
    {
        $request->validate([
            'destinatario' => 'required|string',
            'mensaje' => 'required|string'
        ]);

        $res = WhatsAppService::sendTo($request->destinatario, $request->mensaje);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($res);
        }

        if ($res['success']) {
            return back()->with('success', '¡Mensaje despachado con éxito vía ' . ($res['provider'] ?? 'WhatsApp') . '!');
        }

        return back()->with('error', 'Error al enviar: ' . $res['message']);
    }

    /**
     * Envía aviso de morosidad con cálculo diario de interés compuesto al estudiante.
     */
    public function enviarMora(Request $request, $id)
    {
        $customMessage = $request->filled('mensaje') ? trim($request->input('mensaje')) : null;
        $res = WhatsAppService::sendMoraAviso((int)$id, $customMessage);

        return response()->json($res);
    }

    /**
     * Envía comprobante o solicitud de firma de boleta de matrícula por WhatsApp.
     */
    public function enviarBoleta(Request $request, $id)
    {
        $res = WhatsAppService::sendBoletaAviso((int)$id);

        return response()->json($res);
    }

    /**
     * Webhook receptor para notificaciones de Green-API o n8n.
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        Log::info('WhatsApp Webhook recibido:', $payload);

        return response()->json([
            'success' => true,
            'status' => 'received',
            'timestamp' => now()->toIso8601String()
        ]);
    }
}
