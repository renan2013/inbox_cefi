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
        $profesores = Usuario::whereIn('id_rol', [1, 2, 4]) // Admin (1) o Profesor (2 / 4)
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
     * Devuelve en JSON la lista de materias (plan_estudios) de un programa académico para el selector de Grupos.
     */
    public function cursosPorProgramaAjax($id)
    {
        $materias = PlanEstudio::where('id_programa', $id)
            ->select('id_plan', 'codigo', 'materia', 'cuatrimestre', 'modalidad')
            ->orderBy('id_plan', 'asc')
            ->get();

        return response()->json($materias);
    }

    /**
     * Crea un nuevo grupo académico y matricula al profesor (si fue asignado).
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_plan' => 'required|exists:plan_estudios,id_plan',
            'id_profesor' => 'nullable|exists:usuarios,id',
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
        $idProfesor = $request->filled('id_profesor') ? (int)$request->id_profesor : null;

        // Asegurar que la columna id_profesor permita NULL cuando aún no se ha asignado docente
        if ($idProfesor === null) {
            try {
                DB::statement("ALTER TABLE `cursos_activos` MODIFY COLUMN `id_profesor` INT(11) NULL DEFAULT NULL");
            } catch (\Throwable $eCol) {
                // Ignorar si ya es nullable o el motor no requiere alteración
            }
        }

        DB::beginTransaction();
        try {
            // 1. Crear el curso activo / grupo
            $grupo = CursoActivo::create([
                'id_plan' => $request->id_plan,
                'id_profesor' => $idProfesor,
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

            // 2. Si se seleccionó un profesor, matricularlo y asegurar rol docente
            if ($idProfesor) {
                Matricula::firstOrCreate([
                    'id_estudiante' => $idProfesor,
                    'id_curso_activo' => $grupo->id_curso_activo
                ]);

                $profesor = Usuario::find($idProfesor);
                if ($profesor && $profesor->id_rol != 1 && $profesor->id_rol != 4) {
                    $profesor->update(['id_rol' => 4]);
                }
            }

            DB::commit();
            return redirect()->route('grupos.index')->with('success', 'Grupo de estudio creado con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('grupos.index')->with('error', 'Error al crear el grupo: ' . $e->getMessage());
        }
    }

    /**
     * Permite asignar o actualizar el profesor de un grupo existente en cualquier momento.
     */
    public function asignarProfesor(Request $request)
    {
        $request->validate([
            'id_curso_activo' => 'required|exists:cursos_activos,id_curso_activo',
            'id_profesor' => 'nullable|exists:usuarios,id',
        ]);

        $idProfesor = $request->filled('id_profesor') ? (int)$request->id_profesor : null;

        if ($idProfesor === null) {
            try {
                DB::statement("ALTER TABLE `cursos_activos` MODIFY COLUMN `id_profesor` INT(11) NULL DEFAULT NULL");
            } catch (\Throwable $eCol) {
            }
        }

        DB::beginTransaction();
        try {
            $grupo = CursoActivo::findOrFail($request->id_curso_activo);
            $grupo->update(['id_profesor' => $idProfesor]);

            if ($idProfesor) {
                Matricula::firstOrCreate([
                    'id_estudiante' => $idProfesor,
                    'id_curso_activo' => $grupo->id_curso_activo
                ]);

                $profesor = Usuario::find($idProfesor);
                if ($profesor && $profesor->id_rol != 1 && $profesor->id_rol != 4) {
                    $profesor->update(['id_rol' => 4]);
                }
            }

            DB::commit();
            return redirect()->route('grupos.index')->with('success', 'Profesor del grupo actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('grupos.index')->with('error', 'Error al actualizar el profesor: ' . $e->getMessage());
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

        $masterKey = config('cliente.moodle_key', 'cefi2026');
        if ($request->clave !== $masterKey && $request->clave !== 'unela2026') {
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
