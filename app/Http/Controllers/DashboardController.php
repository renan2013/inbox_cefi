<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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

        return view('dashboard', compact(
            'stats',
            'tareas_pendientes_count',
            'productos_bajo_stock',
            'anio_seleccionado',
            'etiqueta_seleccionada',
            'etiquetas_disponibles',
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

        return response()->json(['success' => (bool)$updated]);
    }
}
