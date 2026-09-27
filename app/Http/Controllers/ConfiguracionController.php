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

    /**
     * Muestra la interfaz de Parámetros del Sistema, Identidad y Logotipo.
     */
    public function parametros()
    {
        $cliente = \App\Services\ClienteService::all();
        $configDb = ConfiguracionSistema::pluck('valor', 'clave')->all();

        return view('configuracion.parametros', compact('cliente', 'configDb'));
    }

    /**
     * Procesa y guarda los parámetros del sistema, logotipos y enlaces institucionales.
     */
    public function guardarParametros(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'nombre_legal' => 'nullable|string|max:255',
            'siglas' => 'nullable|string|max:50',
            'slogan' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'telefono_display' => 'nullable|string|max:50',
            'email_soporte' => 'nullable|email|max:100',
            'email_finanzas' => 'nullable|email|max:100',
            'email_contacto' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:255',
            'sitio_web' => 'nullable|url|max:255',
            'campus_virtual' => 'nullable|url|max:255',
            'moodle_key' => 'nullable|string|max:100',
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:4096',
            'banner_file' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:4096',
            'whatsapp_phone' => 'nullable|string|max:50',
            'whatsapp_api_key' => 'nullable|string|max:255',
        ]);

        $fields = [
            'cliente_nombre' => $request->input('nombre'),
            'cliente_nombre_legal' => $request->input('nombre_legal'),
            'cliente_siglas' => $request->input('siglas'),
            'cliente_slogan' => $request->input('slogan'),
            'cliente_telefono' => preg_replace('/[^0-9]/', '', (string)$request->input('telefono')),
            'cliente_telefono_display' => $request->input('telefono_display'),
            'cliente_email_soporte' => $request->input('email_soporte'),
            'cliente_email_finanzas' => $request->input('email_finanzas'),
            'cliente_email_contacto' => $request->input('email_contacto'),
            'cliente_direccion' => $request->input('direccion'),
            'cliente_sitio_web' => $request->input('sitio_web'),
            'cliente_campus_virtual' => $request->input('campus_virtual'),
            'cliente_moodle_key' => $request->input('moodle_key'),
            'whatsapp_phone' => preg_replace('/[^0-9]/', '', (string)$request->input('whatsapp_phone')),
            'whatsapp_api_key' => $request->input('whatsapp_api_key'),
        ];

        // Subida de Logo oficial
        if ($request->hasFile('logo_file') && $request->file('logo_file')->isValid()) {
            $file = $request->file('logo_file');
            $filename = 'logo_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->input('siglas', 'cefi'))) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destPath = public_path('uploads/logos');
            if (!file_exists($destPath)) {
                @mkdir($destPath, 0755, true);
            }
            $file->move($destPath, $filename);
            $fields['cliente_logo_url'] = '/uploads/logos/' . $filename;
            $fields['whatsapp_logo_url'] = asset('uploads/logos/' . $filename);
        } elseif ($request->filled('logo_url')) {
            $fields['cliente_logo_url'] = $request->input('logo_url');
        }

        // Subida de Banner oficial
        if ($request->hasFile('banner_file') && $request->file('banner_file')->isValid()) {
            $file = $request->file('banner_file');
            $filename = 'banner_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->input('siglas', 'cefi'))) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destPath = public_path('uploads/logos');
            if (!file_exists($destPath)) {
                @mkdir($destPath, 0755, true);
            }
            $file->move($destPath, $filename);
            $fields['cliente_logo_banner_whatsapp'] = '/uploads/logos/' . $filename;
        } elseif ($request->filled('banner_url')) {
            $fields['cliente_logo_banner_whatsapp'] = $request->input('banner_url');
        }

        foreach ($fields as $k => $v) {
            if ($v !== null) {
                ConfiguracionSistema::updateOrCreate(
                    ['clave' => $k],
                    ['valor' => (string)$v]
                );
            }
        }

        return redirect()->route('configuracion.parametros')->with('success', 'Parámetros del sistema y logotipo actualizados correctamente.');
    }
}
