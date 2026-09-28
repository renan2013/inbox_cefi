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
        if (Schema::hasTable('plan_estudios')) {
            Schema::table('plan_estudios', function (Blueprint $table) {
            if (!Schema::hasColumn('plan_estudios', 'duracion')) {
                $table->string('duracion', 100)->nullable()->default('15 semanas')->after('creditos');
            }
            if (!Schema::hasColumn('plan_estudios', 'distribucion_horas')) {
                $table->text('distribucion_horas')->nullable()->after('duracion');
            }
            if (!Schema::hasColumn('plan_estudios', 'horas_teoricas')) {
                $table->integer('horas_teoricas')->nullable()->default(3)->after('distribucion_horas');
            }
            if (!Schema::hasColumn('plan_estudios', 'horas_practicas')) {
                $table->integer('horas_practicas')->nullable()->default(1)->after('horas_teoricas');
            }
            if (!Schema::hasColumn('plan_estudios', 'horas_independientes')) {
                $table->integer('horas_independientes')->nullable()->default(8)->after('horas_practicas');
            }
            if (!Schema::hasColumn('plan_estudios', 'horas_totales')) {
                $table->integer('horas_totales')->nullable()->default(12)->after('horas_independientes');
            }
            if (!Schema::hasColumn('plan_estudios', 'modalidad')) {
                $table->string('modalidad', 100)->nullable()->default('Virtual (aprendizaje electrónico)')->after('horas_totales');
            }
            if (!Schema::hasColumn('plan_estudios', 'naturaleza')) {
                $table->string('naturaleza', 100)->nullable()->default('Teórico-práctica')->after('modalidad');
            }
            if (!Schema::hasColumn('plan_estudios', 'correquisitos')) {
                $table->text('correquisitos')->nullable()->after('requisitos');
            }
            if (!Schema::hasColumn('plan_estudios', 'nivel')) {
                $table->string('nivel', 100)->nullable()->after('correquisitos');
            }
            if (!Schema::hasColumn('plan_estudios', 'profesor')) {
                $table->string('profesor', 255)->nullable()->after('nivel');
            }
            if (!Schema::hasColumn('plan_estudios', 'descripcion_curso')) {
                $table->longText('descripcion_curso')->nullable()->after('adjunto_pdf');
            }
            if (!Schema::hasColumn('plan_estudios', 'contenidos_tematicos')) {
                $table->longText('contenidos_tematicos')->nullable()->after('objetivos_especificos');
            }
            if (!Schema::hasColumn('plan_estudios', 'metodologia_ensenanza')) {
                $table->longText('metodologia_ensenanza')->nullable()->after('contenidos_tematicos');
            }
            if (!Schema::hasColumn('plan_estudios', 'estrategias_aprendizaje')) {
                $table->longText('estrategias_aprendizaje')->nullable()->after('metodologia_ensenanza');
            }
            if (!Schema::hasColumn('plan_estudios', 'evaluacion_aprendizajes')) {
                $table->longText('evaluacion_aprendizajes')->nullable()->after('estrategias_aprendizaje');
            }
            if (!Schema::hasColumn('plan_estudios', 'recursos_didacticos')) {
                $table->longText('recursos_didacticos')->nullable()->after('evaluacion_aprendizajes');
            }
            if (!Schema::hasColumn('plan_estudios', 'cronograma')) {
                $table->longText('cronograma')->nullable()->after('recursos_didacticos');
            }
            if (!Schema::hasColumn('plan_estudios', 'guias_evaluacion')) {
                $table->longText('guias_evaluacion')->nullable()->after('cronograma');
            }
            if (!Schema::hasColumn('plan_estudios', 'bibliografia')) {
                $table->longText('bibliografia')->nullable()->after('guias_evaluacion');
            }
            if (!Schema::hasColumn('plan_estudios', 'fecha_descriptor_pdf')) {
                $table->dateTime('fecha_descriptor_pdf')->nullable()->after('bibliografia');
            }
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe to leave columns intact to avoid data loss
    }
};
