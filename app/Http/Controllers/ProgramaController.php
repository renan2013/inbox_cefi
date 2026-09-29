<?php

namespace App\Http\Controllers;

use App\Models\Programa;
use Illuminate\Http\Request;

class ProgramaController extends Controller
{
    /**
     * Muestra el listado de programas académicos.
     */
    public function index(Request $request)
    {
        $query = Programa::withCount('planesEstudio');

        // Búsqueda por término (nombre o categoría)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre_programa', 'like', "%{$search}%")
                  ->orWhere('categoria', 'like', "%{$search}%");
            });
        }

        $programas = $query->orderBy('categoria', 'asc')
                           ->orderBy('nombre_programa', 'asc')
                           ->paginate(15)
                           ->withQueryString();

        return view('programas.index', compact('programas'));
    }

    /**
     * Muestra el formulario para registrar un nuevo programa.
     */
    public function create()
    {
        return view('programas.create');
    }

    /**
     * Almacena un nuevo programa en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_programa' => 'required|string|max:255',
            'categoria' => 'nullable|string|max:100',
            'costo_materia' => 'required|numeric|min:0',
            'costo_matricula' => 'required|numeric|min:0',
            'costo_biblioteca' => 'required|numeric|min:0',
            'costo_inscripcion_unica' => 'required|numeric|min:0',
            'informacion' => 'nullable|string',
            'perfil' => 'nullable|string',
            'detalles_programa' => 'nullable|string',
            'normas_netiqueta' => 'nullable|string',
            'imagen_principal' => 'nullable|image|max:2048',
            'imagen_secundaria' => 'nullable|image|max:2048',
            'imagen_encabezado' => 'nullable|image|max:2048',
            'imagen_footer' => 'nullable|image|max:2048',
        ]);

        $data = $request->except([
            'imagen_principal', 
            'imagen_secundaria', 
            'imagen_encabezado', 
            'imagen_footer'
        ]);

        // Manejo de la subida de imágenes
        $imageFields = ['imagen_principal', 'imagen_secundaria', 'imagen_encabezado', 'imagen_footer'];
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = uniqid('prog_', true) . '.' . $file->getClientOriginalExtension();
                // Almacenar directamente en el path público para compatibilidad legacy
                $file->move(public_path('uploads/programas'), $filename);
                $data[$field] = 'uploads/programas/' . $filename;
            } else {
                $data[$field] = null;
            }
        }

        Programa::create($data);

        return redirect()->route('programas.index')->with('success', 'Programa académico registrado exitosamente.');
    }

    /**
     * Muestra la vista detallada de programas completos y sus mallas de estudio.
     */
    public function programasCompletos()
    {
        $programas = Programa::with(['planesEstudio' => function($q) {
            $q->orderBy('cuatrimestre', 'asc')->orderBy('materia', 'asc');
        }])->orderBy('categoria', 'asc')
           ->orderBy('nombre_programa', 'asc')
           ->get();

        return view('programas.completos', compact('programas'));
    }

    /**
     * Muestra el formulario para editar un programa académico.
     */
    public function edit($id)
    {
        $programa = Programa::findOrFail($id);
        return view('programas.edit', compact('programa'));
    }

    /**
     * Actualiza la información de un programa en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $programa = Programa::findOrFail($id);

        $request->validate([
            'nombre_programa' => 'required|string|max:255',
            'categoria' => 'nullable|string|max:100',
            'costo_materia' => 'required|numeric|min:0',
            'costo_matricula' => 'required|numeric|min:0',
            'costo_biblioteca' => 'required|numeric|min:0',
            'costo_inscripcion_unica' => 'required|numeric|min:0',
            'informacion' => 'nullable|string',
            'perfil' => 'nullable|string',
            'detalles_programa' => 'nullable|string',
            'normas_netiqueta' => 'nullable|string',
            'imagen_principal' => 'nullable|image|max:2048',
            'imagen_secundaria' => 'nullable|image|max:2048',
            'imagen_encabezado' => 'nullable|image|max:2048',
            'imagen_footer' => 'nullable|image|max:2048',
        ]);

        $data = $request->except([
            'imagen_principal', 
            'imagen_secundaria', 
            'imagen_encabezado', 
            'imagen_footer'
        ]);

        // Manejo de la subida de imágenes
        $imageFields = ['imagen_principal', 'imagen_secundaria', 'imagen_encabezado', 'imagen_footer'];
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = uniqid('prog_', true) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/programas'), $filename);
                $data[$field] = 'uploads/programas/' . $filename;
            }
        }

        $programa->update($data);

        return redirect()->route('programas.index')->with('success', 'Programa académico actualizado exitosamente.');
    }

    /**
     * Elimina un programa académico si no tiene dependencias activas.
     */
    public function destroy($id)
    {
        $programa = Programa::findOrFail($id);

        if ($programa->planesEstudio()->count() > 0) {
            return redirect()->route('programas.index')->with('error', 'No se puede eliminar el programa porque tiene materias/cursos asociados en su plan de estudios.');
        }

        $programa->delete();
        return redirect()->route('programas.index')->with('success', 'Programa eliminado correctamente.');
    }
}
