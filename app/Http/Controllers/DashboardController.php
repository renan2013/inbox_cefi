<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Muestra la vista del panel de control principal (Dashboard).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $es_admin = ($user->id_rol == 1);
        $user_id = $user->id;

        // 1. Estadísticas de Tareas
        $queryStats = DB::table('tareas');
        if (!$es_admin) {
            $queryStats->join('tarea_asignaciones as ta', 'tareas.id', '=', 'ta.id_tarea')
                       ->where('ta.id_usuario', $user_id);
        }
        $rawStats = $queryStats->select('tareas.estado', DB::raw('COUNT(DISTINCT tareas.id) as count'))
                               ->groupBy('tareas.estado')
                               ->get()
                               ->pluck('count', 'estado')
                               ->toArray();

        $stats = [
            'pendiente' => $rawStats['pendiente'] ?? 0,
            'en_proceso' => $rawStats['en_proceso'] ?? 0,
            'completada' => $rawStats['completada'] ?? 0,
            'cancelada' => $rawStats['cancelada'] ?? 0,
        ];
        $stats['total'] = array_sum($stats);

        // 2. Tareas pendientes del usuario actual
        $tareas_pendientes_count = DB::table('tareas as t')
            ->join('tarea_asignaciones as ta', 't.id', '=', 'ta.id_tarea')
            ->where('ta.id_usuario', $user_id)
            ->where('t.estado', '!=', 'completada')
            ->count('t.id');

        // 3. Alerta de Inventario (Bajo Stock)
        $productos_bajo_stock = 0;
        if ($es_admin) {
            $productos_bajo_stock = DB::table('inventario_productos')
                ->whereRaw('stock_actual <= stock_minimo')
                ->where('estado', 'activo')
                ->count();
        }

        // 4. Filtros
        $anio_seleccionado = $request->input('anio', date('Y'));
        $etiqueta_seleccionada = $request->input('etiqueta');
        $etiquetas_disponibles = DB::table('etiquetas')->orderBy('nombre', 'asc')->get();

        // 5. Consulta de Tareas principal
        $tasksQuery = DB::table('tareas as t')
            ->leftJoin('cursos_activos as ca', 't.id_curso_activo', '=', 'ca.id_curso_activo')
            ->leftJoin('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->select('t.*', 'pe.materia as curso_nombre', 'ca.periodo as curso_periodo');

        if (!$es_admin) {
            $tasksQuery->join('tarea_asignaciones as ta', 't.id', '=', 'ta.id_tarea')
                       ->where('ta.id_usuario', $user_id);
        }

        if ($etiqueta_seleccionada) {
            $tasksQuery->join('tarea_etiquetas as te', 't.id', '=', 'te.id_tarea')
                       ->where('te.id_etiqueta', $etiqueta_seleccionada);
        }

        if ($anio_seleccionado) {
            $tasksQuery->whereRaw('YEAR(t.fecha_creacion) = ?', [$anio_seleccionado]);
        }

        $tareas = $tasksQuery->orderBy('t.fecha_creacion', 'desc')
                             ->distinct()
                             ->paginate(30)
                             ->appends($request->all());

        // Obtener asignaciones para las tareas en pantalla
        $taskIds = collect($tareas->items())->pluck('id')->toArray();
        $asignaciones = [];
        if (!empty($taskIds)) {
            $asigRows = DB::table('tarea_asignaciones as ta')
                ->join('usuarios as u', 'ta.id_usuario', '=', 'u.id')
                ->whereIn('ta.id_tarea', $taskIds)
                ->select('ta.id_tarea', 'u.id as user_id', 'u.nombre', 'u.apellidos')
                ->get();
            foreach ($asigRows as $a) {
                $asignaciones[$a->id_tarea][] = $a;
            }
        }

        // 6. Cumpleaños del Mes
        $cumpleaneros = DB::table('usuarios')
            ->whereRaw('MONTH(fecha_nacimiento) = MONTH(CURDATE())')
            ->whereRaw('DAY(fecha_nacimiento) >= DAY(CURDATE())')
            ->orderByRaw('DAY(fecha_nacimiento) ASC')
            ->get();

        // 7. Mis Cursos Asignados (Profesor o Docente)
        $mis_cursos_asignados = DB::table('cursos_activos as ca')
            ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->join('programas as p', 'pe.id_programa', '=', 'p.id_programa')
            ->where('ca.id_profesor', $user_id)
            ->select('ca.id_curso_activo', 'ca.periodo', 'pe.materia', 'pe.codigo', 'p.nombre_programa')
            ->orderBy('ca.fecha_inicio', 'desc')
            ->limit(6)
            ->get();

        // 8. Listas de apoyo para modal de edición completa
        $usuarios_disponibles = DB::table('usuarios')->orderBy('nombre', 'asc')->get();
        $cursos_activos_disponibles = Schema::hasTable('cursos_activos') ? DB::table('cursos_activos as ca')
            ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->select('ca.id_curso_activo', 'pe.materia', 'pe.codigo', 'ca.periodo')
            ->orderBy('pe.materia', 'asc')
            ->get() : [];

        return view('dashboard', compact(
            'stats',
            'tareas_pendientes_count',
            'productos_bajo_stock',
            'anio_seleccionado',
            'etiqueta_seleccionada',
            'etiquetas_disponibles',
            'usuarios_disponibles',
            'cursos_activos_disponibles',
            'tareas',
            'asignaciones',
            'cumpleaneros',
            'mis_cursos_asignados',
            'es_admin'
        ));
    }

    /**
     * Cambiar estado de una tarea vía AJAX (para vista Kanban o botones directos).
     */
    public function cambiarEstado(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'estado' => 'required|string|in:pendiente,en_proceso,completada,cancelada'
        ]);

        $updated = DB::table('tareas')
            ->where('id', $request->id)
            ->update(['estado' => $request->estado]);

        return response()->json([
            'success' => (bool)$updated,
            'id' => $request->id,
            'estado' => $request->estado
        ]);
    }

    /**
     * Cambiar prioridad de una tarea vía AJAX estilo Monday.
     */
    public function cambiarPrioridad(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'prioridad' => 'required|string|in:baja,media,alta'
        ]);

        $updated = DB::table('tareas')
            ->where('id', $request->id)
            ->update(['prioridad' => $request->prioridad]);

        return response()->json([
            'success' => (bool)$updated,
            'id' => $request->id,
            'prioridad' => $request->prioridad
        ]);
    }

    /**
     * Creación rápida de tarea en línea estilo Monday.com.
     */
    public function crearTareaRapida(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255'
        ]);

        $id = DB::table('tareas')->insertGetId([
            'titulo' => trim($request->titulo),
            'descripcion' => '',
            'prioridad' => 'media',
            'estado' => 'pendiente',
            'fecha_creacion' => now(),
            'id_creador' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'tarea' => [
                'id' => $id,
                'titulo' => trim($request->titulo),
                'prioridad' => 'media',
                'estado' => 'pendiente',
                'fecha_creacion' => now()->format('Y-m-d H:i:s'),
                'fecha_vencimiento' => null,
            ]
        ]);
    }

    /**
     * Obtener el detalle completo de una tarea para el modal de edición.
     */
    public function obtenerTarea($id)
    {
        $tarea = DB::table('tareas as t')
            ->leftJoin('cursos_activos as ca', 't.id_curso_activo', '=', 'ca.id_curso_activo')
            ->leftJoin('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->where('t.id', $id)
            ->select('t.*', 'pe.materia as curso_nombre', 'ca.periodo as curso_periodo')
            ->first();

        if (!$tarea) {
            return response()->json(['error' => 'Tarea no encontrada'], 404);
        }

        $asignados = DB::table('tarea_asignaciones')->where('id_tarea', $id)->pluck('id_usuario')->toArray();
        $etiquetas = DB::table('tarea_etiquetas')->where('id_tarea', $id)->pluck('id_etiqueta')->toArray();

        return response()->json([
            'tarea' => $tarea,
            'asignados' => $asignados,
            'etiquetas' => $etiquetas
        ]);
    }

    /**
     * Actualizar una tarea con todos sus campos completos.
     */
    public function actualizarTarea(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'prioridad' => 'required|string|in:baja,media,alta',
            'estado' => 'required|string|in:pendiente,en_proceso,completada,cancelada',
        ]);

        $updateData = [
            'titulo' => trim($request->titulo),
            'descripcion' => $request->descripcion ?? '',
            'prioridad' => $request->prioridad,
            'estado' => $request->estado,
            'fecha_vencimiento' => $request->fecha_vencimiento ?: null,
            'id_curso_activo' => $request->id_curso_activo ?: null,
        ];

        DB::table('tareas')->where('id', $id)->update($updateData);

        // Sincronizar asignados
        DB::table('tarea_asignaciones')->where('id_tarea', $id)->delete();
        if ($request->has('id_asignado')) {
            foreach ((array)$request->input('id_asignado') as $userId) {
                if (!empty($userId)) {
                    DB::table('tarea_asignaciones')->insert([
                        'id_tarea' => $id,
                        'id_usuario' => $userId
                    ]);
                }
            }
        }

        // Sincronizar etiquetas
        DB::table('tarea_etiquetas')->where('id_tarea', $id)->delete();
        if ($request->has('etiquetas')) {
            foreach ((array)$request->input('etiquetas') as $tagId) {
                if (!empty($tagId)) {
                    DB::table('tarea_etiquetas')->insert([
                        'id_tarea' => $id,
                        'id_etiqueta' => $tagId
                    ]);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Eliminar tarea (solo administradores).
     */
    public function eliminarTarea($id)
    {
        $user = Auth::user();
        if ($user->id_rol != 1) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        DB::table('tarea_asignaciones')->where('id_tarea', $id)->delete();
        DB::table('tarea_etiquetas')->where('id_tarea', $id)->delete();
        DB::table('tareas')->where('id', $id)->delete();

        return response()->json(['success' => true]);
    }
}
