<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Estandariza los 4 roles oficiales del sistema:
     * 1 => Administrador General
     * 2 => Profesor
     * 3 => Estudiante
     * + => Administrador (rol operativo)
     */
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        // 1. Asegurar roles base 1, 2, 3
        DB::table('roles')->updateOrInsert(['id' => 1], ['nombre' => 'Administrador General']);
        DB::table('roles')->updateOrInsert(['id' => 2], ['nombre' => 'Profesor']);
        DB::table('roles')->updateOrInsert(['id' => 3], ['nombre' => 'Estudiante']);

        // 2. Asegurar el rol operativo "Administrador"
        $existeAdminOp = DB::table('roles')->where('nombre', 'Administrador')->exists();
        if (!$existeAdminOp) {
            DB::table('roles')->insert(['nombre' => 'Administrador']);
        }

        // 3. Reasignar usuarios de roles heredados equivalentes sin tocar tablas de tarifas
        if (Schema::hasTable('usuarios')) {
            $rolesProfesor = DB::table('roles')
                ->whereIn(DB::raw('LOWER(nombre)'), ['profesor activo', 'docente', 'tutor'])
                ->pluck('id')
                ->all();
            if (!empty($rolesProfesor)) {
                DB::table('usuarios')->whereIn('id_rol', $rolesProfesor)->update(['id_rol' => 2]);
            }

            $rolesEstudiante = DB::table('roles')
                ->whereIn(DB::raw('LOWER(nombre)'), ['miembro', 'alumno', 'alumnos', 'estudiantes'])
                ->pluck('id')
                ->all();
            if (!empty($rolesEstudiante)) {
                DB::table('usuarios')->whereIn('id_rol', $rolesEstudiante)->update(['id_rol' => 3]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversible de forma segura sin eliminar datos
    }
};
