<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Módulo: Control Académico - Graduaciones y Auditoría de Requisitos (CEFI)
     */
    public function up(): void
    {
        if (!Schema::hasTable('graduaciones')) {
            Schema::create('graduaciones', function (Blueprint $table) {
                $table->id('id_graduacion');
                $table->unsignedBigInteger('id_estudiante')->index();
                $table->unsignedBigInteger('id_programa')->index();
                $table->unsignedBigInteger('id_expediente')->nullable()->index();
                
                // Convocatoria y fecha
                $table->string('convocatoria', 100)->comment('Ej: I Convocatoria Ordinaria 2026');
                $table->date('fecha_graduacion')->nullable();
                
                // Rendimiento académico para el título
                $table->decimal('promedio_ponderado_final', 5, 2)->nullable();
                $table->decimal('total_creditos_aprobados', 5, 2)->nullable();
                $table->string('mencion_honor', 50)->default('Ninguna')->comment('Ninguna, Cum Laude, Magna Cum Laude, Summa Cum Laude');
                
                // Auditoría de 4 requisitos oficiales (Semáforo CEFI)
                $table->boolean('cumplio_plan_estudios')->default(false)->comment('100% materias y créditos aprobados/convalidados');
                $table->boolean('cumplio_tcu')->default(false)->comment('Trabajo Comunal Universitario finalizado y aprobado');
                $table->boolean('cumplio_financiero')->default(false)->comment('Sin boletas pendientes ni morosidad activa');
                $table->boolean('cumplio_expediente')->default(false)->comment('Documentación obligatoria del expediente verificada');
                
                // Registro y archivo de títulos
                $table->string('numero_titulo', 50)->nullable();
                $table->string('numero_folio', 50)->nullable();
                $table->string('numero_tomo', 50)->nullable();
                $table->string('numero_acta', 50)->nullable();
                
                // Estado del trámite
                $table->string('estado', 50)->default('En Auditoría')->comment('En Auditoría, Aprobado para Graduación, Graduado, Rechazado');
                $table->text('observaciones')->nullable();
                
                // Autorización de autoridades
                $table->unsignedBigInteger('autorizado_por')->nullable();
                $table->dateTime('fecha_autorizacion')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduaciones');
    }
};
