<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('programas')) {
            Schema::table('programas', function (Blueprint $table) {
                if (!Schema::hasColumn('programas', 'duracion')) {
                    $table->string('duracion', 100)->nullable()->default('12 meses')->after('categoria');
                }
                if (!Schema::hasColumn('programas', 'modalidad')) {
                    $table->string('modalidad', 100)->nullable()->default('Presencial')->after('duracion');
                }
                if (!Schema::hasColumn('programas', 'objetivo_curso')) {
                    $table->longText('objetivo_curso')->nullable()->after('informacion');
                }
                if (!Schema::hasColumn('programas', 'opciones_laborales')) {
                    $table->longText('opciones_laborales')->nullable()->after('objetivo_curso');
                }
                if (!Schema::hasColumn('programas', 'lo_que_obtendras')) {
                    $table->longText('lo_que_obtendras')->nullable()->after('perfil');
                }
                if (!Schema::hasColumn('programas', 'duracion_plan_estudios')) {
                    $table->longText('duracion_plan_estudios')->nullable()->after('detalles_programa');
                }
                if (!Schema::hasColumn('programas', 'perfil_docentes')) {
                    $table->longText('perfil_docentes')->nullable()->after('duracion_plan_estudios');
                }
                if (!Schema::hasColumn('programas', 'requisitos_ingreso')) {
                    $table->longText('requisitos_ingreso')->nullable()->after('perfil_docentes');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe to keep columns to avoid data loss
    }
};

