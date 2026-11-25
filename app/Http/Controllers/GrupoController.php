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
        static $checked = false;
        if ($checked || \Illuminate\Support\Facades\Cache::has('schema_cursos_activos_ok_v1')) {
            $checked = true;
            return;
        }
        $checked = true;

        try {
            if (!Schema::hasTable('cursos_activos')) {
                return;
            }
            if (!Schema::hasColumn('cursos_activos', 'nombre_grupo')) {
                DB::statement("ALTER TABLE `cursos_activos` ADD COLUMN `nombre_grupo` VARCHAR(100) NULL DEFAULT NULL AFTER `id_profesor`");
            }
            DB::statement("ALTER TABLE `cursos_activos` MODIFY COLUMN `id_profesor` INT(11) NULL DEFAULT NULL");
            \Illuminate\Support\Facades\Cache::put('schema_cursos_activos_ok_v1', true, 86400);
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
            'modalidad' => 'nullable|string|max:50',
            'tipo_curso' => 'nullable|string|max:100',
            'id_moodle' => 'nullable|integer',
            'enlace_zoom' => 'nullable|url',
            'horario_dia' => 'nullable|string',
            'horario_inicio' => 'nullable',
            'horario_fin' => 'nullable',
        ], [
            'id_plan.required' => 'Debe seleccionar una materia / curso específico.',
            'periodo.required' => 'Debe indicar el período lectivo.',
            'fecha_inicio.required' => 'Debe indicar la fecha de inicio.',
            'fecha_final.required' => 'Debe indicar la fecha final.',
            'enlace_zoom.url' => 'El enlace de Zoom debe ser una URL válida (ej: https://zoom.us/...).',
        ]);

        $modalidad = trim((string)$request->input('modalidad', '')) ?: 'Virtual';
        $tipoCurso = trim((string)$request->input('tipo_curso', '')) ?: 'Regular';
        $horario = $this->buildHorario($request->input('horario_dia', 'Por definir'), $request->horario_inicio, $request->horario_fin);
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
                'modalidad' => $modalidad,
                'tipo_curso' => $tipoCurso,
                'horario' => $horario,
                'enlace_zoom' => $request->enlace_zoom,
                'id_moodle' => $request->id_moodle,
                'check_proceso' => 1,
                'en_supervision' => 1
            ];
            if (Schema::hasColumn('cursos_activos', 'hora_inicio')) {
                $dataCreate['hora_inicio'] = $request->horario_inicio ?: null;
            }
            if (Schema::hasColumn('cursos_activos', 'hora_final')) {
                $dataCreate['hora_final'] = $request->horario_fin ?: null;
            }
            if (Schema::hasColumn('cursos_activos', 'nombre_grupo')) {
                $dataCreate['nombre_grupo'] = $request->filled('nombre_grupo') ? trim($request->nombre_grupo) : null;
            }

            // 1. Si existe un curso auto-creado por matrícula/boleta sin configurar (sin profesor, horario ni nombre_grupo), completarlo
            $grupo = CursoActivo::where('id_plan', $request->id_plan)
                ->where('periodo', $request->periodo)
                ->whereNull('id_profesor')
                ->where(function ($q) {
                    $q->whereNull('horario')->orWhere('horario', '');
                })
                ->when(Schema::hasColumn('cursos_activos', 'nombre_grupo'), function ($q) {
                    $q->whereNull('nombre_grupo');
                })
                ->first();

            if ($grupo) {
                $grupo->update($dataCreate);
            } else {
                $grupo = CursoActivo::create($dataCreate);
            }

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
            'modalidad' => 'nullable|string|max:50',
            'tipo_curso' => 'nullable|string|max:100',
            'id_moodle' => 'nullable|integer',
            'enlace_zoom' => 'nullable|url',
            'horario_dia' => 'nullable|string',
            'horario_inicio' => 'nullable',
            'horario_fin' => 'nullable',
        ]);

        $modalidad = trim((string)$request->input('modalidad', '')) ?: 'Virtual';
        $tipoCurso = trim((string)$request->input('tipo_curso', '')) ?: 'Regular';
        $horario = $this->buildHorario($request->input('horario_dia', 'Por definir'), $request->horario_inicio, $request->horario_fin);
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
                'modalidad' => $modalidad,
                'tipo_curso' => $tipoCurso,
                'horario' => $horario,
                'enlace_zoom' => $request->enlace_zoom,
                'id_moodle' => $request->id_moodle,
            ];
            if (Schema::hasColumn('cursos_activos', 'hora_inicio')) {
                $dataUpdate['hora_inicio'] = $request->horario_inicio ?: null;
            }
            if (Schema::hasColumn('cursos_activos', 'hora_final')) {
                $dataUpdate['hora_final'] = $request->horario_fin ?: null;
            }
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

    /**
     * Muestra la vista para gestionar y matricular estudiantes en un grupo.
     */
    public function gestionarEstudiantes($id)
    {
        $grupo = CursoActivo::with(['planEstudio.programa', 'profesor'])->findOrFail($id);

        $qMatriculados = DB::table('matriculas as m')
            ->join('usuarios as u', 'm.id_estudiante', '=', 'u.id')
            ->leftJoin('boletas as b', function ($join) {
                $join->on('m.id_boleta', '=', 'b.id')
                     ->where('b.estado', '!=', 'anulada');
            })
            ->where('m.id_curso_activo', $grupo->id_curso_activo);

        if (!empty($grupo->id_profesor)) {
            $qMatriculados->where('u.id', '!=', $grupo->id_profesor);
        }

        $matriculados = $qMatriculados
            ->select(
                'm.id_matricula',
                'u.id as id_estudiante',
                'u.nombre',
                'u.apellidos',
                'u.email',
                'u.cedula',
                'b.id as id_boleta',
                'b.numero_boleta',
                'b.estado as estado_boleta'
            )
            ->orderBy('u.apellidos', 'asc')
            ->orderBy('u.nombre', 'asc')
            ->get();

        $idsExcluidos = $matriculados->pluck('id_estudiante')->toArray();
        if (!empty($grupo->id_profesor)) {
            $idsExcluidos[] = (int)$grupo->id_profesor;
        }

        $estudiantesDisponibles = Usuario::whereNotIn('id', $idsExcluidos)
            ->orderBy('apellidos', 'asc')
            ->orderBy('nombre', 'asc')
            ->get(['id', 'nombre', 'apellidos', 'email', 'cedula', 'id_rol']);

        // Datos para Matrícula en Bloque: programas y grupos anteriores con sus estudiantes
        $programas = DB::table('programas')
            ->orderBy('nombre_programa', 'asc')
            ->get(['id_programa', 'nombre_programa']);

        $matriculasProgramas = DB::table('matriculas as m')
            ->join('cursos_activos as ca', 'm.id_curso_activo', '=', 'ca.id_curso_activo')
            ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->select('m.id_estudiante', 'pe.id_programa', 'ca.id_curso_activo')
            ->distinct()
            ->get();

        $programasEstudiantesMap = [];
        $gruposEstudiantesMap = [];
        foreach ($matriculasProgramas as $mp) {
            $estId = (int) $mp->id_estudiante;
            $progId = (int) $mp->id_programa;
            $caId = (int) $mp->id_curso_activo;

            if (!isset($programasEstudiantesMap[$progId])) {
                $programasEstudiantesMap[$progId] = [];
            }
            if (!in_array($estId, $programasEstudiantesMap[$progId], true)) {
                $programasEstudiantesMap[$progId][] = $estId;
            }

            if ($caId !== (int) $grupo->id_curso_activo) {
                if (!isset($gruposEstudiantesMap[$caId])) {
                    $gruposEstudiantesMap[$caId] = [];
                }
                if (!in_array($estId, $gruposEstudiantesMap[$caId], true)) {
                    $gruposEstudiantesMap[$caId][] = $estId;
                }
            }
        }

        $gruposOrigenIds = array_keys($gruposEstudiantesMap);
        $gruposOrigen = empty($gruposOrigenIds) ? collect() : DB::table('cursos_activos as ca')
            ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->join('programas as p', 'pe.id_programa', '=', 'p.id_programa')
            ->whereIn('ca.id_curso_activo', $gruposOrigenIds)
            ->select(
                'ca.id_curso_activo',
                'ca.periodo',
                'ca.nombre_grupo',
                'pe.id_programa',
                'pe.codigo',
                'pe.materia',
                'p.nombre_programa'
            )
            ->orderByRaw('CASE WHEN pe.id_programa = ? THEN 0 ELSE 1 END', [(int) ($grupo->planEstudio->id_programa ?? 0)])
            ->orderByDesc('ca.id_curso_activo')
            ->get();

        return view('grupos.estudiantes', compact(
            'grupo',
            'matriculados',
            'estudiantesDisponibles',
            'programas',
            'programasEstudiantesMap',
            'gruposOrigen',
            'gruposEstudiantesMap'
        ));
    }

    /**
     * Matricula un estudiante manualmente en el grupo seleccionado.
     */
    public function matricularEstudiante(Request $request, $id)
    {
        $grupo = CursoActivo::findOrFail($id);

        $request->validate([
            'id_estudiante' => 'required|exists:usuarios,id',
        ], [
            'id_estudiante.required' => 'Debe seleccionar un estudiante para matricular.',
            'id_estudiante.exists' => 'El estudiante seleccionado no existe.',
        ]);

        $idEstudiante = (int)$request->id_estudiante;

        if (!empty($grupo->id_profesor) && $idEstudiante === (int)$grupo->id_profesor) {
            return redirect()->route('grupos.estudiantes', $grupo->id_curso_activo)
                ->with('error', 'No puedes matricular al profesor asignado del grupo como estudiante.');
        }

        $existe = Matricula::where('id_estudiante', $idEstudiante)
            ->where('id_curso_activo', $grupo->id_curso_activo)
            ->exists();

        if ($existe) {
            return redirect()->route('grupos.estudiantes', $grupo->id_curso_activo)
                ->with('error', 'El estudiante ya se encuentra matriculado en este grupo.');
        }

        Matricula::create([
            'id_estudiante' => $idEstudiante,
            'id_curso_activo' => $grupo->id_curso_activo,
        ]);

        return redirect()->route('grupos.estudiantes', $grupo->id_curso_activo)
            ->with('success', 'Estudiante matriculado correctamente en el grupo.');
    }

    /**
     * Matricula múltiples estudiantes en bloque en el grupo seleccionado.
     */
    public function matricularEstudiantesBloque(Request $request, $id)
    {
        $grupo = CursoActivo::findOrFail($id);

        $request->validate([
            'estudiantes_ids' => 'required|array|min:1',
            'estudiantes_ids.*' => 'integer|exists:usuarios,id',
        ], [
            'estudiantes_ids.required' => 'Debe seleccionar al menos un estudiante para matricular en bloque.',
            'estudiantes_ids.min' => 'Debe seleccionar al menos un estudiante para matricular en bloque.',
        ]);

        $ids = array_values(array_unique(array_map('intval', $request->estudiantes_ids)));
        $agregados = 0;
        $omitidos = 0;

        DB::beginTransaction();
        try {
            $existentes = Matricula::where('id_curso_activo', $grupo->id_curso_activo)
                ->whereIn('id_estudiante', $ids)
                ->pluck('id_estudiante')
                ->map(fn($v) => (int) $v)
                ->toArray();

            foreach ($ids as $idEstudiante) {
                if ((!empty($grupo->id_profesor) && $idEstudiante === (int) $grupo->id_profesor) || in_array($idEstudiante, $existentes, true)) {
                    $omitidos++;
                    continue;
                }

                Matricula::create([
                    'id_estudiante' => $idEstudiante,
                    'id_curso_activo' => $grupo->id_curso_activo,
                ]);
                $agregados++;
            }

            DB::commit();

            if ($agregados === 0) {
                return redirect()->route('grupos.estudiantes', $grupo->id_curso_activo)
                    ->with('error', 'Ningún estudiante nuevo fue matriculado (ya estaban inscritos o corresponden al docente del grupo).');
            }

            $msg = "Se matricularon {$agregados} estudiante(s) en bloque correctamente.";
            if ($omitidos > 0) {
                $msg .= " ({$omitidos} omitido(s) por estar ya inscritos).";
            }

            return redirect()->route('grupos.estudiantes', $grupo->id_curso_activo)
                ->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('grupos.estudiantes', $grupo->id_curso_activo)
                ->with('error', 'Error al matricular en bloque: ' . $e->getMessage());
        }
    }

    /**
     * Elimina la matrícula de un estudiante en el grupo.
     */
    public function eliminarMatricula(Request $request, $id)
    {
        $grupo = CursoActivo::findOrFail($id);

        $request->validate([
            'id_matricula' => 'required|exists:matriculas,id_matricula',
        ]);

        DB::beginTransaction();
        try {
            $matricula = Matricula::where('id_matricula', $request->id_matricula)
                ->where('id_curso_activo', $grupo->id_curso_activo)
                ->firstOrFail();

            if (Schema::hasTable('notas_rubros')) {
                DB::table('notas_rubros')->where('id_matricula', $matricula->id_matricula)->delete();
            }

            $matricula->delete();
            DB::commit();

            return redirect()->route('grupos.estudiantes', $grupo->id_curso_activo)
                ->with('success', 'Matrícula eliminada del grupo correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('grupos.estudiantes', $grupo->id_curso_activo)
                ->with('error', 'No se pudo eliminar la matrícula: ' . $e->getMessage());
        }
    }
}
