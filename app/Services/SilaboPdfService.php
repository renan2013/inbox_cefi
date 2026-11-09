<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SilaboPdfService
{
    /**
     * Genera y transmite el Programa / Sílabo Oficial del Curso en PDF,
     * delegando directamente a DescriptorPdfService para que sea 100% idéntico.
     *
     * @param int $id_curso ID del curso activo
     * @param int|null $id_plan ID del plan de estudios (opcional)
     * @param string $dest 'I' inline, 'D' download, 'F' save
     * @return mixed
     */
    public static function generar(int $id_curso, ?int $id_plan = null, string $dest = 'I')
    {
        $curso = DB::table('cursos_activos as ca')
            ->leftJoin('usuarios as u', 'ca.id_profesor', '=', 'u.id')
            ->select('ca.*', 'u.nombre as prof_nombre', 'u.apellidos as prof_apellidos')
            ->where('ca.id_curso_activo', $id_curso)
            ->first();

        if (!$curso) {
            if ($id_plan) {
                return DescriptorPdfService::generar((int)$id_plan, $dest);
            }
            abort(404, 'Curso activo no encontrado.');
        }

        $planId = (int)($curso->id_plan ?? $id_plan);
        $profesorNombre = trim(($curso->prof_nombre ?? '') . ' ' . ($curso->prof_apellidos ?? ''));
        $profesorId = (int)($curso->id_profesor ?? 0);

        return DescriptorPdfService::generar(
            $planId,
            $dest,
            $profesorNombre !== '' ? $profesorNombre : null,
            $profesorId
        );
    }
}
