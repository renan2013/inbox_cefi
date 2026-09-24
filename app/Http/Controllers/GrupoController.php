<?php

namespace App\Http\Controllers;

use App\Models\CursoActivo;
use App\Models\PlanEstudio;
use App\Models\Programa;
use App\Models\Usuario;
use App\Models\Matricula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrupoController extends Controller
{
    /**
     * Muestra el panel para gestionar grupos académicos.
     */
    public function index()
    {
        $programas = Programa::orderBy('nombre_programa', 'asc')->get();
        $profesores = Usuario::whereIn('id_rol', [1, 4]) // Admin (1) o Profesor (4)
                             ->orderBy('apellidos', 'asc')
                             ->get();

        $grupos = CursoActivo::with(['planEstudio.programa', 'profesor'])
            ->get()
            ->groupBy(function ($item) {
                return $item->planEstudio->programa->nombre_programa ?? 'Sin Programa';
            });

        // Resumen de períodos
        $resumen_periodos = CursoActivo::select('periodo', DB::raw('count(id_curso_activo) as total_cursos'))
            ->groupBy('periodo')
            ->orderBy('periodo', 'asc')
            ->get();

        // Para cada período, contar estudiantes matriculados
        foreach ($resumen_periodos as $res) {
            $cursosIds = CursoActivo::where('periodo', $res->periodo)->pluck('id_curso_activo');
            $res->total_estudiantes = Matricula::whereIn('id_curso_activo', $cursosIds)->count();
        }

        return view('grupos.index', compact('programas', 'profesores', 'grupos', 'resumen_periodos'));
    }

    /**
     * Crea un nuevo grupo académico y matricula al profesor.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_plan' => 'required|exists:plan_estudios,id_plan',
            'id_profesor' => 'required|exists:usuarios,id',
            'periodo' => 'required|string|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_final' => 'required|date',
            'titulo_prefijo' => 'nullable|string|max:50',
            'titulo_sufijo' => 'nullable|string|max:50',
            'modalidad' => 'required|string|max:50',
            'tipo_curso' => 'required|string|max:100',
            'id_moodle' => 'nullable|integer',
            'enlace_zoom' => 'nullable|url',
            'horario_dia' => 'required|string',
            'horario_inicio' => 'required',
            'horario_fin' => 'required',
        ]);

        $horario = trim("{$request->horario_dia} {$request->horario_inicio} - {$request->horario_fin}");

        DB::beginTransaction();
        try {
            // 1. Crear el curso activo / grupo
            $grupo = CursoActivo::create([
                'id_plan' => $request->id_plan,
                'id_profesor' => $request->id_profesor,
                'periodo' => $request->periodo,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_final' => $request->fecha_final,
                'titulo_prefijo' => $request->titulo_prefijo,
                'titulo_sufijo' => $request->titulo_sufijo,
                'modalidad' => $request->modalidad,
                'tipo_curso' => $request->tipo_curso,
                'horario' => $horario,
                'enlace_zoom' => $request->enlace_zoom,
                'id_moodle' => $request->id_moodle,
                'check_proceso' => 1,
                'en_supervision' => 1
            ]);

            // 2. Matricular al profesor
            Matricula::create([
                'id_estudiante' => $request->id_profesor,
                'id_curso_activo' => $grupo->id_curso_activo
            ]);

            // 3. Actualizar el rol del profesor a 4 (si no es administrador)
            $profesor = Usuario::find($request->id_profesor);
            if ($profesor && $profesor->id_rol != 1 && $profesor->id_rol != 4) {
                $profesor->update(['id_rol' => 4]);
            }

            DB::commit();
            return redirect()->route('grupos.index')->with('success', 'Grupo de estudio creado con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('grupos.index')->with('error', 'Error al crear el grupo: ' . $e->getMessage());
        }
    }

    /**
     * Elimina el grupo y las matrículas asociadas bajo confirmación de clave maestra.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:cursos_activos,id_curso_activo',
            'clave' => 'required|string'
        ]);

        if ($request->clave !== 'unela2026') {
            return redirect()->route('grupos.index')->with('error', 'Clave de confirmación incorrecta. El grupo no ha sido eliminado.');
        }

        DB::beginTransaction();
        try {
            // 1. Eliminar matrículas asociadas
            Matricula::where('id_curso_activo', $request->id)->delete();

            // 2. Eliminar el curso activo
            CursoActivo::destroy($request->id);

            DB::commit();
            return redirect()->route('grupos.index')->with('success', 'Grupo de estudio y matrículas eliminados con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('grupos.index')->with('error', 'Error al eliminar el grupo: ' . $e->getMessage());
        }
    }
}
