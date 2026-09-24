<?php

namespace App\Http\Controllers;

use App\Models\SolicitudCursoLibre;
use App\Models\Programa;
use Illuminate\Http\Request;

class SolicitudCursoLibreController extends Controller
{
    /**
     * Lista todas las solicitudes de inscripción de cursos libres.
     */
    public function index()
    {
        $solicitudes = SolicitudCursoLibre::orderBy('fecha_creacion', 'desc')->paginate(15);
        return view('solicitudes.index', compact('solicitudes'));
    }

    /**
     * Muestra el formulario para crear una nueva solicitud desde el panel de administración.
     */
    public function create()
    {
        $programas = Programa::orderBy('nombre_programa', 'asc')->get();
        return view('solicitudes.create', compact('programas'));
    }

    /**
     * Guarda una solicitud ingresada por el administrador.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'segundo_apellido' => 'nullable|string|max:100',
            'nacionalidad' => 'nullable|string|max:100',
            'identificacion' => 'required|string|max:50',
            'programa_deseado' => 'required|string|max:255',
            'sexo' => 'nullable|in:M,F',
            'estado_civil' => 'nullable|in:Soltero,Casado,Viudo,Divorciado',
            'fecha_nacimiento' => 'nullable|date',
            'lugar_nacimiento' => 'nullable|string|max:255',
            'profesion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
        ]);

        SolicitudCursoLibre::create($request->all());

        return redirect()->route('solicitudes.index')->with('success', 'Solicitud registrada con éxito.');
    }

    /**
     * Muestra el formulario para editar una solicitud.
     */
    public function edit($id)
    {
        $solicitud = SolicitudCursoLibre::findOrFail($id);
        $programas = Programa::orderBy('nombre_programa', 'asc')->get();
        return view('solicitudes.edit', compact('solicitud', 'programas'));
    }

    /**
     * Actualiza la solicitud en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $solicitud = SolicitudCursoLibre::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'segundo_apellido' => 'nullable|string|max:100',
            'nacionalidad' => 'nullable|string|max:100',
            'identificacion' => 'required|string|max:50',
            'programa_deseado' => 'required|string|max:255',
            'sexo' => 'nullable|in:M,F',
            'estado_civil' => 'nullable|in:Soltero,Casado,Viudo,Divorciado',
            'fecha_nacimiento' => 'nullable|date',
            'lugar_nacimiento' => 'nullable|string|max:255',
            'profesion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
        ]);

        $solicitud->update($request->all());

        return redirect()->route('solicitudes.index')->with('success', 'Solicitud actualizada con éxito.');
    }

    /**
     * Elimina una solicitud.
     */
    public function destroy($id)
    {
        $solicitud = SolicitudCursoLibre::findOrFail($id);
        $solicitud->delete();

        return redirect()->route('solicitudes.index')->with('success', 'Solicitud eliminada con éxito.');
    }

    /**
     * Muestra el formulario público para inscripciones de cursos libres.
     */
    public function showPublicForm()
    {
        $programas = Programa::orderBy('nombre_programa', 'asc')->get();
        return view('solicitudes.public', compact('programas'));
    }

    /**
     * Guarda la inscripción pública y muestra confirmación.
     */
    public function storePublic(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'segundo_apellido' => 'nullable|string|max:100',
            'nacionalidad' => 'nullable|string|max:100',
            'identificacion' => 'required|string|max:50',
            'programa_deseado' => 'required|string|max:255',
            'sexo' => 'nullable|in:M,F',
            'estado_civil' => 'nullable|in:Soltero,Casado,Viudo,Divorciado',
            'fecha_nacimiento' => 'nullable|date',
            'lugar_nacimiento' => 'nullable|string|max:255',
            'profesion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
        ]);

        SolicitudCursoLibre::create($request->all());

        return redirect()->route('solicitudes.public.success');
    }
}
