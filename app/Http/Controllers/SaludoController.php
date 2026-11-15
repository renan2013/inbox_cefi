<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SaludoController extends Controller
{
    protected function ensureTables(): void
    {
        if (!Schema::hasTable('saludo_categorias')) {
            Schema::create('saludo_categorias', function ($table) {
                $table->increments('id');
                $table->string('nombre', 120);
                $table->timestamps();
            });

            DB::table('saludo_categorias')->insert([
                ['nombre' => 'Cumpleaños Institucional', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Bienvenida Estudiante', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Felicitación Académica', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (!Schema::hasTable('saludo_plantillas')) {
            Schema::create('saludo_plantillas', function ($table) {
                $table->increments('id');
                $table->integer('categoria_id')->index();
                $table->string('nombre', 150);
                $table->string('ruta_imagen', 255)->nullable();
                $table->text('mensaje_default')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('saludo_tarjetas')) {
            Schema::create('saludo_tarjetas', function ($table) {
                $table->increments('id');
                $table->string('token', 64)->unique();
                $table->integer('plantilla_id');
                $table->string('destinatario', 150);
                $table->text('mensaje');
                $table->string('remitente', 150)->nullable();
                $table->timestamps();
            });
        }
    }

    public function plantillas()
    {
        $this->ensureTables();
        $plantillas = DB::table('saludo_plantillas as p')
            ->leftJoin('saludo_categorias as c', 'p.categoria_id', '=', 'c.id')
            ->select('p.*', 'c.nombre as categoria_nombre')
            ->orderByDesc('p.id')
            ->get();

        $categorias = DB::table('saludo_categorias')->orderBy('nombre')->get();
        $tarjetas   = DB::table('saludo_tarjetas as t')
            ->leftJoin('saludo_plantillas as p', 't.plantilla_id', '=', 'p.id')
            ->select('t.*', 'p.nombre as plantilla_nombre', 'p.ruta_imagen')
            ->orderByDesc('t.id')
            ->limit(25)
            ->get();

        return view('saludos.plantillas', compact('plantillas', 'categorias', 'tarjetas'));
    }

    public function storePlantilla(Request $request)
    {
        $this->ensureTables();
        $request->validate([
            'nombre'       => 'required|string|max:150',
            'categoria_id' => 'required|integer',
        ]);

        $rutaImagen = 'imgs/logo_cefi_oficial.png';
        if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {
            $file = $request->file('imagen');
            $filename = 'saludo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dir = public_path('uploads/saludos');
            @is_dir($dir) || @mkdir($dir, 0775, true);
            $file->move($dir, $filename);
            $rutaImagen = 'uploads/saludos/' . $filename;
        }

        DB::table('saludo_plantillas')->insert([
            'categoria_id'    => (int)$request->input('categoria_id'),
            'nombre'          => trim($request->input('nombre')),
            'ruta_imagen'     => $rutaImagen,
            'mensaje_default' => trim($request->input('mensaje_default', '¡Reciba una cordial felicitación de parte de nuestra comunidad académica!')),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()->route('saludos.plantillas')->with('success', 'Plantilla de saludo creada exitosamente.');
    }

    public function destroyPlantilla($id)
    {
        $this->ensureTables();
        DB::table('saludo_plantillas')->where('id', $id)->delete();
        return redirect()->route('saludos.plantillas')->with('success', 'Plantilla eliminada.');
    }

    public function categorias()
    {
        $this->ensureTables();
        $categorias = DB::table('saludo_categorias as c')
            ->leftJoin('saludo_plantillas as p', 'c.id', '=', 'p.categoria_id')
            ->select('c.*', DB::raw('COUNT(p.id) as total_plantillas'))
            ->groupBy('c.id', 'c.nombre', 'c.created_at', 'c.updated_at')
            ->orderBy('c.nombre')
            ->get();

        return view('saludos.categorias', compact('categorias'));
    }

    public function storeCategoria(Request $request)
    {
        $this->ensureTables();
        $request->validate(['nombre' => 'required|string|max:120']);
        DB::table('saludo_categorias')->insert([
            'nombre'     => trim($request->input('nombre')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('saludos.categorias')->with('success', 'Categoría de saludo creada.');
    }

    public function destroyCategoria($id)
    {
        $this->ensureTables();
        DB::table('saludo_categorias')->where('id', $id)->delete();
        return redirect()->route('saludos.categorias')->with('success', 'Categoría eliminada.');
    }

    public function storeTarjeta(Request $request)
    {
        $this->ensureTables();
        $request->validate([
            'plantilla_id' => 'required|integer',
            'destinatario' => 'required|string|max:150',
            'mensaje'      => 'required|string',
        ]);

        $token = bin2hex(random_bytes(12));
        DB::table('saludo_tarjetas')->insert([
            'token'        => $token,
            'plantilla_id' => (int)$request->input('plantilla_id'),
            'destinatario' => trim($request->input('destinatario')),
            'mensaje'      => trim($request->input('mensaje')),
            'remitente'    => trim($request->input('remitente', config('cliente.nombre', 'Dirección Académica'))),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('saludos.plantillas')->with('success', 'Tarjeta generada lista para compartir: ' . route('saludos.ver_tarjeta', $token));
    }

    public function verTarjeta($token)
    {
        $this->ensureTables();
        $tarjeta = DB::table('saludo_tarjetas as t')
            ->leftJoin('saludo_plantillas as p', 't.plantilla_id', '=', 'p.id')
            ->select('t.*', 'p.nombre as plantilla_nombre', 'p.ruta_imagen')
            ->where('t.token', $token)
            ->first();

        if (!$tarjeta) {
            abort(404, 'Tarjeta no encontrada');
        }

        return view('saludos.ver_tarjeta', compact('tarjeta'));
    }
}
