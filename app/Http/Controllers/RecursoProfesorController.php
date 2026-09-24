<?php

namespace App\Http\Controllers;

use App\Models\ProfesorRecurso;
use App\Models\ProfesorRecursoCategoria;
use App\Models\RecursoDrive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RecursoProfesorController extends Controller
{
    /**
     * Muestra el panel de recursos didácticos privados del profesor.
     */
    public function index()
    {
        $id_usuario = Auth::id();

        // Obtener categorías del profesor
        $categorias = ProfesorRecursoCategoria::with(['recursos' => function ($query) use ($id_usuario) {
            $query->where('id_profesor', $id_usuario)->orderBy('titulo', 'asc');
        }])
        ->where('id_profesor', $id_usuario)
        ->orderBy('nombre', 'asc')
        ->get();

        return view('recursos.index', compact('categorias'));
    }

    /**
     * Guarda una nueva categoría.
     */
    public function storeCategoria(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100'
        ]);

        ProfesorRecursoCategoria::create([
            'nombre' => $request->nombre,
            'id_profesor' => Auth::id()
        ]);

        return redirect()->route('recursos.index')->with('success', 'Categoría de recursos creada con éxito.');
    }

    /**
     * Actualiza el nombre de una categoría.
     */
    public function updateCategoria(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:100'
        ]);

        $categoria = ProfesorRecursoCategoria::where('id', $id)
            ->where('id_profesor', Auth::id())
            ->firstOrFail();

        $categoria->update(['nombre' => $request->nombre]);

        return redirect()->route('recursos.index')->with('success', 'Categoría actualizada con éxito.');
    }

    /**
     * Elimina una categoría.
     */
    public function destroyCategoria($id)
    {
        $categoria = ProfesorRecursoCategoria::where('id', $id)
            ->where('id_profesor', Auth::id())
            ->firstOrFail();

        $categoria->delete();

        return redirect()->route('recursos.index')->with('success', 'Categoría eliminada con éxito.');
    }

    /**
     * Agrega un nuevo recurso didáctico.
     */
    public function storeRecurso(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'url' => 'required|url|max:512',
            'id_categoria' => 'required|integer',
            'tipo' => 'required|string|max:20',
            'descripcion' => 'nullable|string',
            'contenido' => 'nullable|string'
        ]);

        ProfesorRecurso::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'url' => $request->url,
            'id_categoria' => $request->id_categoria,
            'id_profesor' => Auth::id(),
            'tipo' => $request->tipo,
            'contenido' => $request->contenido,
            'fecha_creacion' => Carbon::now(),
            'es_compartido' => $request->has('es_compartido')
        ]);

        return redirect()->route('recursos.index')->with('success', 'Recurso didáctico agregado con éxito.');
    }

    /**
     * Actualiza un recurso didáctico.
     */
    public function updateRecurso(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'url' => 'required|url|max:512',
            'id_categoria' => 'required|integer',
            'tipo' => 'required|string|max:20',
            'descripcion' => 'nullable|string',
            'contenido' => 'nullable|string'
        ]);

        $recurso = ProfesorRecurso::where('id', $id)
            ->where('id_profesor', Auth::id())
            ->firstOrFail();

        $recurso->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'url' => $request->url,
            'id_categoria' => $request->id_categoria,
            'tipo' => $request->tipo,
            'contenido' => $request->contenido,
            'es_compartido' => $request->has('es_compartido')
        ]);

        return redirect()->route('recursos.index')->with('success', 'Recurso actualizado con éxito.');
    }

    /**
     * Elimina un recurso didáctico.
     */
    public function destroyRecurso($id)
    {
        $recurso = ProfesorRecurso::where('id', $id)
            ->where('id_profesor', Auth::id())
            ->firstOrFail();

        $recurso->delete();

        return redirect()->route('recursos.index')->with('success', 'Recurso eliminado con éxito.');
    }

    /**
     * Panel general de recursos compartidos públicamente.
     */
    public function compartidos()
    {
        // 1. Recursos de Drive compartidos
        $drive_compartidos = RecursoDrive::with('cursoActivo.planEstudio')
            ->where('es_compartido', 1)
            ->orderBy('fecha_subida', 'desc')
            ->get();

        // 2. Recursos didácticos compartidos
        $didacticos_compartidos = ProfesorRecurso::with(['profesor', 'categoria'])
            ->where('es_compartido', 1)
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        return view('recursos.compartidos', compact('drive_compartidos', 'didacticos_compartidos'));
    }
}
