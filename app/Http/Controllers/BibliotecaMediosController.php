<?php

namespace App\Http\Controllers;

use App\Models\RecursoCorporativo;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BibliotecaMediosController extends Controller
{
    /**
     * Muestra la galería multimedia corporativa.
     */
    public function index(Request $request)
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para acceder a la biblioteca corporativa.');
        }

        // Cargar programas para filtros y subida
        $programas = Programa::orderBy('categoria', 'asc')
            ->orderBy('nombre_programa', 'asc')
            ->get();

        // Aplicar filtros
        $id_programa = $request->get('id_programa');
        $categoria_academica = $request->get('categoria_academica');

        $query = RecursoCorporativo::with('programa');

        if (!empty($id_programa)) {
            $query->where('id_programa', $id_programa);
        }

        if (!empty($categoria_academica)) {
            $query->where('categoria_academica', $categoria_academica);
        }

        $recursos = $query->orderBy('id', 'desc')->paginate(18);

        return view('biblioteca.index', compact('recursos', 'programas', 'id_programa', 'categoria_academica'));
    }

    /**
     * Sube un activo multimedia.
     */
    public function store(Request $request)
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('biblioteca.index')->with('error', 'No autorizado.');
        }

        $request->validate([
            'nombre_imagen' => 'required|string|max:255',
            'id_programa' => 'nullable|integer',
            'categoria_academica' => 'required|string|max:100',
            'imagen_biblioteca' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120'
        ]);

        if ($request->hasFile('imagen_biblioteca')) {
            $file = $request->file('imagen_biblioteca');
            $filename = time() . '_' . preg_replace("/[^A-Z0-9._-]/i", "_", $file->getClientOriginalName());
            
            $file->move(public_path('uploads/biblioteca'), $filename);
            $filePath = 'uploads/biblioteca/' . $filename;

            RecursoCorporativo::create([
                'nombre' => $request->nombre_imagen,
                'nombre_archivo' => $file->getClientOriginalName(),
                'tipo_archivo' => $file->getClientOriginalExtension(),
                'ruta_archivo' => $filePath,
                'id_programa' => $request->id_programa,
                'categoria_academica' => $request->categoria_academica
            ]);

            return redirect()->route('biblioteca.index')->with('success', 'Imagen subida correctamente a la biblioteca corporativa.');
        }

        return redirect()->route('biblioteca.index')->with('error', 'Error al procesar el archivo subido.');
    }

    /**
     * Elimina un activo multimedia.
     */
    public function destroy($id)
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('biblioteca.index')->with('error', 'No autorizado.');
        }

        $recurso = RecursoCorporativo::findOrFail($id);

        $filePath = public_path($recurso->ruta_archivo);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $recurso->delete();

        return redirect()->route('biblioteca.index')->with('success', 'Activo multimedia eliminado de la biblioteca con éxito.');
    }
}
