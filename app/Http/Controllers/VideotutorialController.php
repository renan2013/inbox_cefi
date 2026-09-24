<?php

namespace App\Http\Controllers;

use App\Models\Videotutorial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VideotutorialController extends Controller
{
    /**
     * Muestra la lista de videotutoriales.
     */
    public function index()
    {
        $es_admin = (Auth::user()->id_rol == 1);
        $videos = Videotutorial::orderBy('id', 'asc')->get();

        return view('videotutoriales.index', compact('videos', 'es_admin'));
    }

    /**
     * Guarda un nuevo videotutorial (Admin).
     */
    public function store(Request $request)
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('videotutoriales.index')->with('error', 'No tienes permiso para administrar videos.');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'categoria' => 'required|string|max:50',
            'video_url' => 'required|url',
            'descripcion' => 'nullable|string',
            'duracion' => 'nullable|string|max:20'
        ]);

        Videotutorial::create([
            'titulo' => $request->titulo,
            'categoria' => $request->categoria,
            'video_url' => $request->video_url,
            'descripcion' => $request->descripcion,
            'duracion' => $request->duracion,
            'fecha_creacion' => Carbon::now()
        ]);

        return redirect()->route('videotutoriales.index')->with('success', 'Videotutorial registrado con éxito.');
    }

    /**
     * Actualiza un videotutorial (Admin).
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('videotutoriales.index')->with('error', 'No tienes permiso para administrar videos.');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'categoria' => 'required|string|max:50',
            'video_url' => 'required|url',
            'descripcion' => 'nullable|string',
            'duracion' => 'nullable|string|max:20'
        ]);

        $video = Videotutorial::findOrFail($id);
        $video->update($request->all());

        return redirect()->route('videotutoriales.index')->with('success', 'Videotutorial actualizado con éxito.');
    }

    /**
     * Elimina un videotutorial (Admin).
     */
    public function destroy($id)
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('videotutoriales.index')->with('error', 'No tienes permiso para administrar videos.');
        }

        $video = Videotutorial::findOrFail($id);
        $video->delete();

        return redirect()->route('videotutoriales.index')->with('success', 'Videotutorial eliminado con éxito.');
    }
}
