<?php

namespace App\Http\Controllers;

use App\Models\PlanEstudio;
use App\Models\Programa;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    /**
     * Muestra el listado de materias/planes de estudio.
     */
    public function index(Request $request)
    {
        $query = PlanEstudio::with('programa');

        // Búsqueda por término (materia o código)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('materia', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        // Filtro por Programa
        if ($request->filled('programa_id')) {
            $query->where('id_programa', $request->programa_id);
        }

        $cursos = $query->orderBy('cuatrimestre', 'asc')->paginate(15)->withQueryString();
        $programas = Programa::all();

        return view('cursos.index', compact('cursos', 'programas'));
    }

    /**
     * Muestra el formulario para añadir un nuevo plan de estudio / materia.
     */
    public function create()
    {
        $programas = Programa::orderBy('nombre_programa', 'asc')->get();
        return view('cursos.create', compact('programas'));
    }

    /**
     * Almacena una nueva materia en el plan de estudios.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_programa' => 'required|exists:programas,id_programa',
            'cuatrimestre' => 'required|string|max:100',
            'codigo' => 'required|string|max:50',
            'materia' => 'required|string|max:255',
            'creditos' => 'nullable|integer|min:0',
            'requisitos' => 'nullable|string',
            'objetivo_general' => 'nullable|string',
            'objetivos_especificos' => 'nullable|string',
            'adjunto_pdf' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $data = $request->except(['adjunto_pdf']);

        // Obtener el precio desde el programa
        $programa = Programa::find($request->id_programa);
        $data['precio'] = $programa ? $programa->costo_materia : 0.00;

        // Subida de PDF
        if ($request->hasFile('adjunto_pdf')) {
            $file = $request->file('adjunto_pdf');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/planes_estudio'), $filename);
            $data['adjunto_pdf'] = $filename;
        } else {
            $data['adjunto_pdf'] = '';
        }

        PlanEstudio::create($data);

        return redirect()->route('cursos.index')->with('success', 'Curso/materia añadido exitosamente al plan de estudios.');
    }

    /**
     * Genera y descarga el Descriptor Oficial de Curso en formato Microsoft Word (.doc).
     */
    public function generarDescriptorDoc($id)
    {
        return \App\Services\DescriptorDocService::generar((int)$id);
    }

    /**
     * Genera y transmite el Descriptor Oficial de Curso en formato PDF.
     */
    public function generarDescriptorPdf($id)
    {
        return \App\Services\DescriptorPdfService::generar((int)$id);
    }
}
