<?php

namespace App\Http\Controllers;

use App\Models\Soporte;
use App\Models\SoporteCategoria;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class SoporteController extends Controller
{
    /**
     * Vista interna de administración de Tickets / Averías.
     */
    public function index(Request $request)
    {
        $categorias = SoporteCategoria::orderBy('nombre', 'asc')->get();
        // Admins (Rol 1) o usuarios calificados
        $usuarios_soporte = Usuario::whereIn('id_rol', [1, 2])->orderBy('nombre', 'asc')->get();

        $query = Soporte::with(['creador', 'reportadoA', 'categoria']);

        // Filtros
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $soportes = $query->orderBy('fecha_creacion', 'desc')->paginate(15);

        return view('soporte.gestionar', compact('categorias', 'usuarios_soporte', 'soportes'));
    }

    /**
     * Registra un nuevo ticket de soporte.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'problema' => 'nullable|string',
            'solucion' => 'nullable|string',
            'categoria_id' => 'nullable|exists:soporte_categorias,id',
            'reportado_a' => 'nullable|exists:usuarios,id',
            'adjunto' => 'nullable|file|max:5120' // max 5MB
        ]);

        $adjunto_nombre = null;
        if ($request->hasFile('adjunto')) {
            $file = $request->file('adjunto');
            $adjunto_nombre = 'soporte_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/soporte'), $adjunto_nombre);
        }

        Soporte::create([
            'titulo' => $request->titulo,
            'problema' => $request->problema,
            'solucion' => $request->solucion,
            'categoria_id' => $request->categoria_id,
            'id_creador' => Auth::id(),
            'reportado_a' => $request->reportado_a,
            'estado' => 'reportado',
            'adjunto' => $adjunto_nombre
        ]);

        return redirect()->route('soporte.gestionar')->with('success', 'Reporte de avería creado con éxito.');
    }

    /**
     * Actualiza un ticket de soporte.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'problema' => 'nullable|string',
            'solucion' => 'nullable|string',
            'categoria_id' => 'nullable|exists:soporte_categorias,id',
            'reportado_a' => 'nullable|exists:usuarios,id',
            'adjunto' => 'nullable|file|max:5120'
        ]);

        $soporte = Soporte::findOrFail($id);

        $adjunto_nombre = $soporte->adjunto;
        if ($request->hasFile('adjunto')) {
            // Delete old file
            if ($soporte->adjunto) {
                $oldPath = public_path('uploads/soporte/' . $soporte->adjunto);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }
            $file = $request->file('adjunto');
            $adjunto_nombre = 'soporte_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/soporte'), $adjunto_nombre);
        }

        $soporte->update([
            'titulo' => $request->titulo,
            'problema' => $request->problema,
            'solucion' => $request->solucion,
            'categoria_id' => $request->categoria_id,
            'reportado_a' => $request->reportado_a,
            'adjunto' => $adjunto_nombre
        ]);

        return redirect()->route('soporte.gestionar')->with('success', 'Ticket de soporte actualizado con éxito.');
    }

    /**
     * Cambia el estado del ticket.
     */
    public function changeStatus($id, Request $request)
    {
        $request->validate([
            'nuevo_estado' => 'required|in:reportado,solucionado'
        ]);

        $soporte = Soporte::findOrFail($id);
        $soporte->update(['estado' => $request->nuevo_estado]);

        return redirect()->route('soporte.gestionar')->with('success', 'Estado del ticket actualizado con éxito.');
    }

    /**
     * Elimina un ticket.
     */
    public function destroy($id)
    {
        $soporte = Soporte::findOrFail($id);
        if ($soporte->adjunto) {
            $filePath = public_path('uploads/soporte/' . $soporte->adjunto);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }
        $soporte->delete();

        return redirect()->route('soporte.gestionar')->with('success', 'Ticket de soporte eliminado.');
    }

    /**
     * Lista general / bitácora pública de soporte y soluciones.
     */
    public function lista(Request $request)
    {
        $categorias = SoporteCategoria::orderBy('nombre', 'asc')->get();

        $query = Soporte::with(['categoria', 'reportadoA', 'creador']);

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('titulo', 'like', '%' . $request->search . '%')
                  ->orWhere('problema', 'like', '%' . $request->search . '%')
                  ->orWhere('solucion', 'like', '%' . $request->search . '%');
            });
        }

        $soportes = $query->orderBy('fecha_creacion', 'desc')->paginate(15);

        return view('soporte.lista', compact('categorias', 'soportes'));
    }

    /**
     * Gestión de Categorías de Soporte.
     */
    public function categorias()
    {
        $categorias = SoporteCategoria::withCount('soportes')->orderBy('nombre', 'asc')->get();
        return view('soporte.categorias', compact('categorias'));
    }

    /**
     * Guarda categoría nueva.
     */
    public function storeCategoria(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:soporte_categorias,nombre'
        ]);

        SoporteCategoria::create(['nombre' => $request->nombre]);

        return redirect()->route('soporte.categorias')->with('success', 'Categoría añadida con éxito.');
    }

    /**
     * Actualiza categoría.
     */
    public function updateCategoria(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:soporte_categorias,nombre,' . $id
        ]);

        $cat = SoporteCategoria::findOrFail($id);
        $cat->update(['nombre' => $request->nombre]);

        return redirect()->route('soporte.categorias')->with('success', 'Categoría actualizada con éxito.');
    }

    /**
     * Elimina categoría validando la clave maestra.
     */
    public function destroyCategoria(Request $request, $id)
    {
        $request->validate([
            'clave' => 'required'
        ]);

        $masterKey = config('cliente.moodle_key', 'cefi2026');
        if ($request->clave !== $masterKey && $request->clave !== 'unela2026') {
            return redirect()->route('soporte.categorias')->with('error', 'Clave de confirmación incorrecta. Categoría no eliminada.');
        }

        $cat = SoporteCategoria::findOrFail($id);
        $cat->delete();

        return redirect()->route('soporte.categorias')->with('success', 'Categoría eliminada con éxito.');
    }
}
