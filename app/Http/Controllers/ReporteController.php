<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReporteController extends Controller
{
    /**
     * Muestra el panel generador de reportes.
     */
    public function index()
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'No tiene permisos para acceder a esta sección.');
        }
        return view('reportes.index');
    }

    /**
     * Genera e imprime un reporte específico en formato imprimible.
     */
    public function generar(Request $request)
    {
        if (Auth::user()->id_rol != 1) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }

        $tipo = $request->query('tipo');
        $titulo = 'Reporte Institucional';
        $columnas = [];
        $filas = [];

        switch ($tipo) {
            case 'tareas_todas':
                $titulo = 'Listado Completo de Tareas';
                $columnas = ['ID', 'Título', 'Prioridad', 'Estado', 'Fecha Creación'];
                $rows = DB::table('tareas')->orderBy('id', 'desc')->get();
                foreach ($rows as $r) {
                    $filas[] = [
                        $r->id,
                        e($r->titulo),
                        e(ucfirst($r->prioridad)),
                        '<span class="badge bg-' . ($r->estado === 'completada' ? 'success' : ($r->estado === 'en_proceso' ? 'info' : 'warning')) . '">' . e(ucfirst($r->estado)) . '</span>',
                        Carbon::parse($r->fecha_creacion)->format('d/m/Y')
                    ];
                }
                break;

            case 'tareas_pendientes':
                $titulo = 'Listado de Tareas Pendientes';
                $columnas = ['ID', 'Título', 'Prioridad', 'Vencimiento'];
                $rows = DB::table('tareas')->where('estado', 'pendiente')->orderBy('id', 'desc')->get();
                foreach ($rows as $r) {
                    $venc = ($r->fecha_vencimiento && $r->fecha_vencimiento !== '0000-00-00') ? Carbon::parse($r->fecha_vencimiento)->format('d/m/Y') : 'N/A';
                    $filas[] = [
                        $r->id,
                        e($r->titulo),
                        e(ucfirst($r->prioridad)),
                        $venc
                    ];
                }
                break;

            case 'usuarios_lista':
                $titulo = 'Padrón de Usuarios del Sistema';
                $columnas = ['ID', 'Nombre', 'Email', 'Rol', 'Fecha Registro'];
                $rows = DB::table('usuarios as u')
                    ->leftJoin('roles as r', 'u.id_rol', '=', 'r.id')
                    ->select('u.*', 'r.nombre as rol_nombre')
                    ->orderBy('u.id', 'asc')
                    ->get();
                foreach ($rows as $r) {
                    $filas[] = [
                        $r->id,
                        e($r->nombre . ' ' . ($r->apellidos ?? '')),
                        e($r->email),
                        e($r->rol_nombre ?? 'Sin Rol'),
                        Carbon::parse($r->creado_en)->format('d/m/Y')
                    ];
                }
                break;

            case 'credenciales_lista':
                $titulo = 'Reporte de Accesos y Credenciales';
                $columnas = ['Fecha', 'Usuario', 'Clave', 'Tipo', 'Enlace / Datos', 'Atendido Por'];
                $rows = DB::table('credenciales as c')
                    ->leftJoin('usuarios as u', 'c.creado_por', '=', 'u.id')
                    ->select('c.*', 'u.nombre as creador_nombre')
                    ->orderBy('c.fecha', 'desc')
                    ->get();
                foreach ($rows as $r) {
                    $filas[] = [
                        Carbon::parse($r->fecha)->format('d/m/Y H:i'),
                        e($r->usuario),
                        e($r->clave),
                        e($r->tipo),
                        e($r->datos_link),
                        e($r->creador_nombre ?? 'Sistema')
                    ];
                }
                break;

            case 'programas_lista':
                $titulo = 'Lista de Programas Académicos';
                $columnas = ['ID', 'Programa Académico', 'Categoría'];
                $rows = DB::table('programas')->orderBy('categoria', 'asc')->orderBy('nombre_programa', 'asc')->get();
                foreach ($rows as $r) {
                    $filas[] = [
                        $r->id_programa,
                        e($r->nombre_programa),
                        e($r->categoria)
                    ];
                }
                break;

            case 'grupos_activos':
                $titulo = 'Grupos de Estudio Activos';
                $columnas = ['Periodo', 'Materia', 'Profesor', 'Modalidad'];
                $rows = DB::table('cursos_activos as ca')
                    ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
                    ->leftJoin('usuarios as u', 'ca.id_profesor', '=', 'u.id')
                    ->select('ca.periodo', 'ca.modalidad', 'pe.materia', DB::raw("CONCAT(u.nombre, ' ', COALESCE(u.apellidos, '')) as profesor"))
                    ->orderBy('ca.periodo', 'desc')
                    ->orderBy('pe.materia', 'asc')
                    ->get();
                foreach ($rows as $r) {
                    $filas[] = [
                        e($r->periodo),
                        e($r->materia),
                        e($r->profesor ?: 'Sin Asignar'),
                        e($r->modalidad)
                    ];
                }
                break;

            default:
                abort(404, 'Reporte no definido.');
        }

        return view('reportes.imprimir', compact('titulo', 'columnas', 'filas'));
    }

    /**
     * Muestra la vista de Ingresos en Tiempo Real (Drive / n8n).
     */
    public function ingresosDrive()
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'No tiene permisos para acceder a esta sección.');
        }
        return view('reportes.ingresos_drive');
    }
}
