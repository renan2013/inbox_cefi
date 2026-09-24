<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionSistema;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    /**
     * Muestra la vista de configuración de alertas del sistema.
     */
    public function alertas()
    {
        $config = ConfiguracionSistema::pluck('valor', 'clave')->all();

        // Asegurar que existan claves básicas en el arreglo
        $config['whatsapp_phone'] = $config['whatsapp_phone'] ?? '';
        $config['whatsapp_api_key'] = $config['whatsapp_api_key'] ?? '';
        $config['inventario_alert_email'] = $config['inventario_alert_email'] ?? '';

        return view('configuracion.alertas', compact('config'));
    }

    /**
     * Guarda las configuraciones de alertas de WhatsApp e email.
     */
    public function guardarAlertas(Request $request)
    {
        $request->validate([
            'whatsapp_phone' => 'nullable|string',
            'whatsapp_api_key' => 'nullable|string',
            'inventario_alert_email' => 'nullable|email'
        ]);

        $updates = [
            'whatsapp_phone' => preg_replace('/[^0-9]/', '', $request->whatsapp_phone),
            'whatsapp_api_key' => trim($request->whatsapp_api_key),
            'inventario_alert_email' => trim($request->inventario_alert_email)
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
        if (!\Illuminate\Support\Facades\Schema::hasTable('base_conocimiento_inbox_ai')) {
            \Illuminate\Support\Facades\Schema::create('base_conocimiento_inbox_ai', function ($table) {
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

        $conocimientos = \DB::table('base_conocimiento_inbox_ai')->orderBy('id', 'desc')->get();
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
            'pregunta' => $pregunta,
            'palabras_clave' => $palabras_clave,
            'respuesta' => $respuesta,
            'categoria' => $categoria,
            'estado' => $estado,
            'updated_at' => now()
        ];

        if (!empty($imagen_name)) {
            $data['imagen'] = $imagen_name;
        }

        if ($id > 0) {
            \DB::table('base_conocimiento_inbox_ai')->where('id', $id)->update($data);
            $msg = 'Conocimiento actualizado exitosamente.';
        } else {
            $data['created_at'] = now();
            \DB::table('base_conocimiento_inbox_ai')->insert($data);
            $msg = 'Nuevo conocimiento registrado para Inbox AI 2.0.';
        }

        return redirect()->route('configuracion.conocimiento_ai')->with('success', $msg);
    }

    public function eliminarConocimientoAi($id)
    {
        \DB::table('base_conocimiento_inbox_ai')->where('id', $id)->delete();
        return redirect()->route('configuracion.conocimiento_ai')->with('success', 'Registro de conocimiento eliminado.');
    }
}
