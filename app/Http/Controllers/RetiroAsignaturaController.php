<?php

namespace App\Http\Controllers;

use App\Models\RetiroAsignatura;
use App\Models\Matricula;
use App\Models\Usuario;
use App\Models\CursoActivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RetiroAsignaturaController extends Controller
{
    /**
     * Listado general de solicitudes de retiros y cambios de asignaturas con KPIs.
     */
    public function index(Request $request)
    {
        $busqueda = trim($request->get('q', ''));
        $estadoFiltro = $request->get('estado', '');
        $tipoFiltro = $request->get('tipo_tramite', '');

        $query = RetiroAsignatura::with([
            'estudiante', 
            'matricula.cursoActivo.planEstudio', 
            'cursoActivo.planEstudio',
            'cursoActivoNuevo.planEstudio',
            'procesador'
        ]);

        if (!empty($estadoFiltro)) {
            $query->where('estado', $estadoFiltro);
        }

        if (!empty($tipoFiltro)) {
            $query->where('tipo_tramite', $tipoFiltro);
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('motivo', 'like', "%{$busqueda}%")
                  ->orWhereHas('estudiante', function ($u) use ($busqueda) {
                      $u->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('apellidos', 'like', "%{$busqueda}%")
                        ->orWhere('cedula', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('cursoActivo.planEstudio', function ($p) use ($busqueda) {
                      $p->where('materia', 'like', "%{$busqueda}%")
                        ->orWhere('codigo', 'like', "%{$busqueda}%");
                  });
            });
        }

        $retiros = $query->orderBy('id_retiro', 'desc')->paginate(20)->withQueryString();

        // KPIs
        $kpiTotal = RetiroAsignatura::count();
        $kpiPendientes = RetiroAsignatura::where('estado', 'Pendiente')->count();
        $kpiAprobados = RetiroAsignatura::where('estado', 'Aprobado')->count();
        $kpiCambios = RetiroAsignatura::where('tipo_tramite', 'Cambio')->count();

        return view('retiros.index', compact(
            'retiros', 'kpiTotal', 'kpiPendientes', 'kpiAprobados', 'kpiCambios',
            'busqueda', 'estadoFiltro', 'tipoFiltro'
        ));
    }

    /**
     * Formulario para solicitar o registrar un retiro/cambio de asignatura.
     */
    public function create(Request $request)
    {
        $estudiantes = Usuario::where('id_rol', 3)
            ->orWhereHas('matriculas')
            ->orderBy('apellidos', 'asc')
            ->get();

        $cursosActivos = CursoActivo::with(['planEstudio', 'profesor'])
            ->orderBy('id_curso_activo', 'desc')
            ->take(60)
            ->get();

        return view('retiros.create', compact('estudiantes', 'cursosActivos'));
    }

    /**
     * Guarda la solicitud de retiro o cambio en base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_estudiante'      => 'required|exists:usuarios,id',
            'id_matricula'       => 'required|exists:matriculas,id_matricula',
            'tipo_tramite'       => 'required|in:Retiro,Cambio',
            'tipo_retiro'        => 'required|string',
            'motivo'             => 'required|string',
            'documento_probatorio'=> 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $matricula = Matricula::with('cursoActivo')->findOrFail($request->id_matricula);

        $rutaArchivo = null;
        if ($request->hasFile('documento_probatorio')) {
            $file = $request->file('documento_probatorio');
            $nombreArchivo = 'retiro_' . time() . '_' . $file->getClientOriginalName();
            $rutaArchivo = $file->storeAs('retiros_academicos', $nombreArchivo, 'public');
        }

        $estadoInicial = $request->get('estado_inicial', 'Pendiente');

        $retiro = RetiroAsignatura::create([
            'id_matricula'          => $matricula->id_matricula,
            'id_estudiante'         => $request->id_estudiante,
            'id_curso_activo'       => $matricula->id_curso_activo,
            'id_curso_activo_nuevo' => ($request->tipo_tramite === 'Cambio') ? $request->id_curso_activo_nuevo : null,
            'tipo_tramite'          => $request->tipo_tramite,
            'tipo_retiro'           => $request->tipo_retiro,
            'motivo'                => $request->motivo,
            'documento_probatorio'  => $rutaArchivo,
            'fecha_solicitud'       => now(),
            'estado'                => $estadoInicial,
            'impacto_financiero'    => $request->impacto_financiero ?: 'Mantiene Cobro',
            'monto_ajuste'          => $request->monto_ajuste ?: 0.00,
            'procesado_por'         => ($estadoInicial === 'Aprobado') ? Auth::id() : null,
            'fecha_resolucion'      => ($estadoInicial === 'Aprobado') ? now() : null,
        ]);

        // Si se aprueba de inmediato, aplicar cambio a matrícula
        if ($estadoInicial === 'Aprobado') {
            $this->aplicarResolucionEnMatricula($retiro);
        }

        return redirect()->route('retiros.index')
            ->with('success', 'La solicitud académica ha sido registrada correctamente.');
    }

    /**
     * Resuelve formalmente un trámite (Aprobado / Rechazado).
     */
    public function resolver(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:Aprobado,Rechazado',
            'observaciones_resolucion' => 'nullable|string'
        ]);

        $retiro = RetiroAsignatura::findOrFail($id);
        $retiro->estado = $request->estado;
        $retiro->observaciones_resolucion = $request->observaciones_resolucion;
        $retiro->procesado_por = Auth::id();
        $retiro->fecha_resolucion = now();
        $retiro->save();

        if ($request->estado === 'Aprobado') {
            $this->aplicarResolucionEnMatricula($retiro);
        }

        return back()->with('success', "El trámite ha sido marcado como {$request->estado} y se actualizaron los registros académicos.");
    }

    /**
     * Aplica el impacto en la tabla matriculas.
     */
    protected function aplicarResolucionEnMatricula(RetiroAsignatura $retiro)
    {
        $matricula = Matricula::find($retiro->id_matricula);
        if (!$matricula) return;

        if ($retiro->tipo_tramite === 'Retiro') {
            $matricula->estado = 'Retirado';
            $matricula->fecha_retiro = now();
            $matricula->motivo_retiro = "Retiro {$retiro->tipo_retiro}: " . substr($retiro->motivo, 0, 150);
            $matricula->id_retiro = $retiro->id_retiro;
            $matricula->save();
        } elseif ($retiro->tipo_tramite === 'Cambio' && !empty($retiro->id_curso_activo_nuevo)) {
            // Se marca la anterior como trasladada / retirada
            $matricula->estado = 'Retirado';
            $matricula->fecha_retiro = now();
            $matricula->motivo_retiro = "Cambio de curso a grupo #{$retiro->id_curso_activo_nuevo}";
            $matricula->id_retiro = $retiro->id_retiro;
            $matricula->save();

            // Se matricula en el nuevo curso activo
            Matricula::create([
                'id_estudiante'    => $retiro->id_estudiante,
                'id_curso_activo'  => $retiro->id_curso_activo_nuevo,
                'id_boleta'        => $matricula->id_boleta,
                'calificacion'     => null,
                'estado'           => 'Matriculado',
                'comentarios'      => "Inscrito por cambio de curso (Trámite #{$retiro->id_retiro})",
            ]);
        }
    }

    /**
     * Elimina el registro del trámite.
     */
    public function destroy($id)
    {
        $retiro = RetiroAsignatura::findOrFail($id);
        $retiro->delete();

        return back()->with('success', 'Registro de trámite eliminado correctamente.');
    }

    /**
     * AJAX: Devuelve las materias matriculadas de un estudiante en formato JSON.
     */
    public function ajaxMatriculasEstudiante($id_estudiante)
    {
        $matriculas = Matricula::with(['cursoActivo.planEstudio'])
            ->where('id_estudiante', $id_estudiante)
            ->where(function ($q) {
                $q->whereNull('estado')
                  ->orWhere('estado', '!=', 'Retirado');
            })
            ->get()
            ->map(function ($m) {
                return [
                    'id_matricula'    => $m->id_matricula,
                    'id_curso_activo' => $m->id_curso_activo,
                    'codigo'          => $m->cursoActivo && $m->cursoActivo->planEstudio ? $m->cursoActivo->planEstudio->codigo : 'N/D',
                    'materia'         => $m->cursoActivo && $m->cursoActivo->planEstudio ? $m->cursoActivo->planEstudio->materia : 'Curso #' . $m->id_curso_activo,
                    'periodo'         => $m->cursoActivo ? $m->cursoActivo->periodo : '',
                    'estado'          => $m->estado ?: 'Matriculado'
                ];
            });

        return response()->json($matriculas);
    }
}
