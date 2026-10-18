<?php

namespace App\Http\Controllers;

use App\Models\Convalidacion;
use App\Models\Usuario;
use App\Models\Programa;
use App\Models\PlanEstudio;
use App\Models\ExpedienteDigital;
use App\Services\ExpedienteStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConvalidacionController extends Controller
{
    /**
     * Listado general de convalidaciones y homologaciones académicas con KPIs y filtros.
     */
    public function index(Request $request)
    {
        $busqueda = trim($request->get('q', ''));
        $estadoFiltro = $request->get('estado', '');
        $programaFiltro = $request->get('id_programa', '');

        $query = Convalidacion::with(['estudiante', 'programa', 'planEstudio', 'expediente']);

        if (!empty($estadoFiltro)) {
            $query->where('estado', $estadoFiltro);
        }

        if (!empty($programaFiltro)) {
            $query->where('id_programa', $programaFiltro);
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('institucion_origen', 'like', "%{$busqueda}%")
                  ->orWhere('materia_origen', 'like', "%{$busqueda}%")
                  ->orWhere('numero_resolucion', 'like', "%{$busqueda}%")
                  ->orWhereHas('estudiante', function ($u) use ($busqueda) {
                      $u->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('apellidos', 'like', "%{$busqueda}%")
                        ->orWhere('cedula', 'like', "%{$busqueda}%")
                        ->orWhere('email', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('planEstudio', function ($p) use ($busqueda) {
                      $p->where('materia', 'like', "%{$busqueda}%")
                        ->orWhere('codigo', 'like', "%{$busqueda}%");
                  });
            });
        }

        $convalidaciones = $query->orderBy('id_convalidacion', 'desc')->paginate(20)->withQueryString();

        // KPIs
        $kpiTotal = Convalidacion::count();
        $kpiAprobadas = Convalidacion::where('estado', 'Aprobada')->count();
        $kpiPendientes = Convalidacion::where('estado', 'Pendiente')->count();
        $kpiCreditos = Convalidacion::where('estado', 'Aprobada')->sum('creditos_convalidados');

        $programas = Programa::orderBy('nombre_programa', 'asc')->get();

        return view('convalidaciones.index', compact(
            'convalidaciones', 'kpiTotal', 'kpiAprobadas', 'kpiPendientes', 'kpiCreditos',
            'programas', 'busqueda', 'estadoFiltro', 'programaFiltro'
        ));
    }

    /**
     * Formulario para registrar una nueva convalidación.
     */
    public function create(Request $request)
    {
        $idEstudiante = $request->get('id_estudiante');
        $estudianteSeleccionado = null;
        if ($idEstudiante) {
            $estudianteSeleccionado = Usuario::with('expediente')->find($idEstudiante);
        }

        $programas = Programa::orderBy('nombre_programa', 'asc')->get();
        $estudiantes = Usuario::where('id_rol', 3) // Rol estudiante o general
            ->orWhereHas('expediente')
            ->orderBy('apellidos', 'asc')
            ->get();

        return view('convalidaciones.create', compact('estudiantes', 'programas', 'estudianteSeleccionado'));
    }

    /**
     * Guarda una nueva convalidación en base de datos y almacena documento probatorio.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_estudiante'          => 'required|exists:usuarios,id',
            'institucion_origen'     => 'required|string|max:255',
            'materia_origen'         => 'required|string|max:255',
            'creditos_convalidados'  => 'required|numeric|min:0',
            'estado'                 => 'required|in:Pendiente,Aprobada,Rechazada',
            'archivo_resolucion'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $estudiante = Usuario::with('expediente')->findOrFail($request->id_estudiante);
        $idExpediente = $estudiante->expediente ? $estudiante->expediente->id_expediente : null;

        $rutaArchivo = null;
        if ($request->hasFile('archivo_resolucion')) {
            $file = $request->file('archivo_resolucion');
            if ($idExpediente) {
                $nombreArchivo = 'CONV_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
                $rutaArchivo = ExpedienteStorageService::guardarDocumento($idExpediente, '04_Convalidaciones', $file, $nombreArchivo);
            } else {
                $nombreArchivo = 'conv_' . time() . '_' . $file->getClientOriginalName();
                $rutaArchivo = $file->storeAs('convalidaciones', $nombreArchivo, 'public');
            }
        }

        Convalidacion::create([
            'id_estudiante'          => $request->id_estudiante,
            'id_expediente'          => $idExpediente,
            'id_programa'            => $request->id_programa ?: null,
            'id_plan'                => $request->id_plan ?: null,
            'institucion_origen'     => $request->institucion_origen,
            'carrera_origen'         => $request->carrera_origen,
            'materia_origen'         => $request->materia_origen,
            'codigo_origen'          => $request->codigo_origen,
            'creditos_origen'        => $request->creditos_origen ?: null,
            'calificacion_origen'    => $request->calificacion_origen,
            'creditos_convalidados'  => $request->creditos_convalidados ?: 3.00,
            'calificacion_homologada'=> $request->calificacion_homologada ?: null,
            'tipo_convalidacion'     => $request->tipo_convalidacion ?: 'Externa',
            'numero_resolucion'      => $request->numero_resolucion,
            'fecha_resolucion'       => $request->fecha_resolucion ?: now()->toDateString(),
            'archivo_resolucion'     => $rutaArchivo,
            'estado'                 => $request->estado,
            'observaciones'          => $request->observaciones,
            'registrado_por'         => Auth::id(),
            'aprobado_por'           => ($request->estado === 'Aprobada') ? Auth::id() : null,
        ]);

        return redirect()->route('convalidaciones.index')
            ->with('success', 'La convalidación académica ha sido registrada exitosamente.');
    }

    /**
     * Cambia el estado de una convalidación (Aprobada / Rechazada / Pendiente).
     */
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:Pendiente,Aprobada,Rechazada'
        ]);

        $convalidacion = Convalidacion::findOrFail($id);
        $convalidacion->estado = $request->estado;
        $convalidacion->aprobado_por = ($request->estado === 'Aprobada') ? Auth::id() : null;
        $convalidacion->save();

        return back()->with('success', "Estado de la convalidación actualizado a {$request->estado}.");
    }

    /**
     * Elimina una convalidación registrada.
     */
    public function destroy($id)
    {
        $convalidacion = Convalidacion::findOrFail($id);
        $convalidacion->delete();

        return back()->with('success', 'Registro de convalidación eliminado correctamente.');
    }

    /**
     * AJAX: Devuelve las materias del plan de estudios de un programa en formato JSON.
     */
    public function ajaxCursosPrograma($id_programa)
    {
        $cursos = PlanEstudio::where('id_programa', $id_programa)
            ->orderBy('cuatrimestre', 'asc')
            ->orderBy('codigo', 'asc')
            ->get(['id_plan', 'codigo', 'materia', 'creditos', 'cuatrimestre']);

        return response()->json($cursos);
    }
}
