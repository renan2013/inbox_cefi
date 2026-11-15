<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RepositorioActasController extends Controller
{
    /**
     * Muestra el Repositorio Oficial de Actas de Calificaciones para Registro Académico.
     */
    public function index(Request $request)
    {
        $filtroAnio     = $request->input('anio', '');
        $filtroPrograma = $request->input('programa', '');
        $filtroEstado   = $request->input('estado', 'todos');
        $filtroBusqueda = trim($request->input('q', ''));

        $programas = DB::table('programas')->orderBy('nombre_programa')->get();

        $query = DB::table('cursos_activos as ca')
            ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->join('programas as p', 'pe.id_programa', '=', 'p.id_programa')
            ->leftJoin('usuarios as u', 'ca.id_profesor', '=', 'u.id')
            ->leftJoin('matriculas as m', function ($join) {
                $join->on('m.id_curso_activo', '=', 'ca.id_curso_activo')
                     ->whereRaw('m.id_estudiante != COALESCE(ca.id_profesor, 0)');
            })
            ->select(
                'ca.id_curso_activo',
                'ca.periodo',
                'ca.fecha_inicio',
                'ca.fecha_final',
                'ca.check_acta',
                'ca.check_terminado',
                'ca.check_calificaciones',
                'pe.id_plan',
                'pe.materia',
                'pe.codigo',
                'p.id_programa',
                'p.nombre_programa',
                'u.nombre as prof_nombre',
                'u.apellidos as prof_apellidos',
                DB::raw('COUNT(m.id_matricula) as total_estudiantes'),
                DB::raw('SUM(CASE WHEN m.calificacion >= 70 THEN 1 ELSE 0 END) as total_aprobados'),
                DB::raw('SUM(CASE WHEN m.calificacion IS NOT NULL AND m.calificacion < 70 THEN 1 ELSE 0 END) as total_reprobados'),
                DB::raw('AVG(m.calificacion) as promedio_curso')
            )
            ->groupBy(
                'ca.id_curso_activo',
                'ca.periodo',
                'ca.fecha_inicio',
                'ca.fecha_final',
                'ca.check_acta',
                'ca.check_terminado',
                'ca.check_calificaciones',
                'pe.id_plan',
                'pe.materia',
                'pe.codigo',
                'p.id_programa',
                'p.nombre_programa',
                'u.nombre',
                'u.apellidos'
            );

        if ($filtroAnio !== '') {
            $query->where(function ($q) use ($filtroAnio) {
                $q->where('ca.periodo', 'like', "%{$filtroAnio}%")
                  ->orWhereYear('ca.fecha_inicio', $filtroAnio);
            });
        }
        if ($filtroPrograma !== '') {
            $query->where('p.id_programa', (int)$filtroPrograma);
        }
        if ($filtroEstado === 'selladas') {
            $query->where('ca.check_acta', 1);
        } elseif ($filtroEstado === 'pendientes') {
            $query->where('ca.check_acta', 0);
        }
        if ($filtroBusqueda !== '') {
            $query->where(function ($q) use ($filtroBusqueda) {
                $q->where('pe.materia', 'like', "%{$filtroBusqueda}%")
                  ->orWhere('pe.codigo', 'like', "%{$filtroBusqueda}%")
                  ->orWhere('u.nombre', 'like', "%{$filtroBusqueda}%")
                  ->orWhere('u.apellidos', 'like', "%{$filtroBusqueda}%");
            });
        }

        $actas = $query->orderByDesc('ca.id_curso_activo')->get();

        return view('registro.repositorio_actas', compact(
            'actas',
            'programas',
            'filtroAnio',
            'filtroPrograma',
            'filtroEstado',
            'filtroBusqueda'
        ));
    }

    /**
     * Permite a Registro Académico sellar o desbloquear un acta para corrección de notas.
     */
    public function toggleSelloActa(Request $request, $id)
    {
        $curso = DB::table('cursos_activos')->where('id_curso_activo', $id)->first();
        if (!$curso) {
            return back()->with('error', 'Curso no encontrado.');
        }

        $nuevoEstado = $curso->check_acta ? 0 : 1;
        DB::table('cursos_activos')->where('id_curso_activo', $id)->update([
            'check_acta' => $nuevoEstado,
        ]);

        $msg = $nuevoEstado
            ? 'Acta oficializada y sellada correctamente en el Repositorio.'
            : 'Acta desbloqueada para permitir ajustes de calificaciones.';

        return back()->with('success', $msg);
    }
}
