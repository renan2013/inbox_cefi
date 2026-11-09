<?php

namespace App\Http\Controllers;

use App\Models\CursoActivo;
use App\Models\PlanEstudio;
use App\Models\Programa;
use App\Models\Usuario;
use App\Models\Matricula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GrupoController extends Controller
{
    /**
     * Asegura que la tabla cursos_activos permita id_profesor NULL y cuente con nombre_grupo opcional.
     */
    protected static function ensureCursosActivosColumns(): void
    {
        try {
            if (!Schema::hasTable('cursos_activos')) {
                return;
            }
            if (!Schema::hasColumn('cursos_activos', 'nombre_grupo')) {
                DB::statement("ALTER TABLE `cursos_activos` ADD COLUMN `nombre_grupo` VARCHAR(100) NULL DEFAULT NULL AFTER `id_profesor`");
            }
            DB::statement("ALTER TABLE `cursos_activos` MODIFY COLUMN `id_profesor` INT(11) NULL DEFAULT NULL");
        } catch (\Throwable $e) {
            // Ignorar si ya existe o si el motor no requiere alteración
        }
    }

    /**
     * Construye el texto de horario manteniendo el día al inicio para el cuadro semanal.
     */
    protected function buildHorario(?string $dia, ?string $inicio, ?string $fin): string
    {
        $dia = trim((string)$dia);
        $inicio = trim((string)$inicio);
        $fin = trim((string)$fin);

        if ($dia === '' || $dia === 'Asincrónico' || $dia === 'Por definir') {
            return $dia !== '' ? $dia : 'Por definir';
        }
        if ($inicio !== '' && $fin !== '') {
            return "{$dia} {$inicio} - {$fin}";
        }
        if ($inicio !== '') {
            return "{$dia} {$inicio}";
        }
        return $dia;
    }

    /**
     * Muestra el panel para gestionar grupos académicos.
     */
    public function index()
    {
        self::ensureCursosActivosColumns();

        $programas = Programa::orderBy('nombre_programa', 'asc')->get();
        $profesores = Usuario::whereIn('id_rol', [1, 2, 4]) // Admin (1) o Profesor (2 / 4)
                             ->orderBy('apellidos', 'asc')
                             ->get();

        $listaGrupos = CursoActivo::with(['planEstudio.programa', 'profesor'])->get();

        // Contar alumnos matriculados por cada grupo (excluyendo al docente asignado si figura en matrículas)
        foreach ($listaGrupos as $g) {
            $qMat = Matricula::where('id_curso_activo', $g->id_curso_activo);
            if (!empty($g->id_profesor)) {
                $qMat->where('id_estudiante', '!=', $g->id_profesor);
            }
            $g->total_estudiantes = $qMat->count();
        }

        $grupos = $listaGrupos->groupBy(function ($item) {
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
            ->select('id_plan', 'codigo', 'materia', 'cuatrimestre', 'modalidad', 'duracion')
            ->orderBy('id_plan', 'asc')
            ->get();

        return response()->json($materias);
    }

    /**
     * Crea un nuevo grupo académico y matricula al profesor (si fue asignado).
     */
    public function store(Request $request)
    {
        self::ensureCursosActivosColumns();

        $request->validate([
            'id_plan' => 'required|exists:plan_estudios,id_plan',
            'id_profesor' => 'nullable|exists:usuarios,id',
            'nombre_grupo' => 'nullable|string|max:100',
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
            'horario_inicio' => 'nullable',
            'horario_fin' => 'nullable',
        ]);

        $horario = $this->buildHorario($request->horario_dia, $request->horario_inicio, $request->horario_fin);
        $idProfesor = $request->filled('id_profesor') ? (int)$request->id_profesor : null;

        DB::beginTransaction();
        try {
            $dataCreate = [
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
            ];
            if (Schema::hasColumn('cursos_activos', 'nombre_grupo')) {
                $dataCreate['nombre_grupo'] = $request->filled('nombre_grupo') ? trim($request->nombre_grupo) : null;
            }

            // 1. Crear el curso activo / grupo
            $grupo = CursoActivo::create($dataCreate);

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
     * Actualiza todos los datos de un grupo existente (profesor, horario, fechas, periodo, modalidad, Moodle, Zoom, etc.).
     */
    public function update(Request $request, $id)
    {
        self::ensureCursosActivosColumns();

        $request->validate([
            'id_plan' => 'nullable|exists:plan_estudios,id_plan',
            'id_profesor' => 'nullable|exists:usuarios,id',
            'nombre_grupo' => 'nullable|string|max:100',
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
            'horario_inicio' => 'nullable',
            'horario_fin' => 'nullable',
        ]);

        $horario = $this->buildHorario($request->horario_dia, $request->horario_inicio, $request->horario_fin);
        $idProfesor = $request->filled('id_profesor') ? (int)$request->id_profesor : null;

        DB::beginTransaction();
        try {
            $grupo = CursoActivo::findOrFail($id);

            $dataUpdate = [
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
            ];
            if ($request->filled('id_plan')) {
                $dataUpdate['id_plan'] = (int)$request->id_plan;
            }
            if (Schema::hasColumn('cursos_activos', 'nombre_grupo')) {
                $dataUpdate['nombre_grupo'] = $request->filled('nombre_grupo') ? trim($request->nombre_grupo) : null;
            }

            $grupo->update($dataUpdate);

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
            return redirect()->route('grupos.index')->with('success', 'Grupo de estudio actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('grupos.index')->with('error', 'Error al actualizar el grupo: ' . $e->getMessage());
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
