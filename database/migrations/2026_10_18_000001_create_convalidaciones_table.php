<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Módulo: Control Académico - Convalidaciones y Homologaciones (CEFI)
     */
    public function up(): void
    {
        if (!Schema::hasTable('convalidaciones')) {
            Schema::create('convalidaciones', function (Blueprint $table) {
                $table->id('id_convalidacion');
                $table->unsignedBigInteger('id_estudiante')->index();
                $table->unsignedBigInteger('id_expediente')->nullable()->index();
                $table->unsignedBigInteger('id_programa')->nullable()->index();
                $table->unsignedBigInteger('id_plan')->nullable()->index()->comment('Materia equivalente en plan_estudios de CEFI');
                
                // Datos de la institución y materia de procedencia
                $table->string('institucion_origen', 255);
                $table->string('carrera_origen', 255)->nullable();
                $table->string('materia_origen', 255);
                $table->string('codigo_origen', 50)->nullable();
                $table->decimal('creditos_origen', 5, 2)->nullable();
                $table->string('calificacion_origen', 50)->nullable();
                
                // Homologación oficial en CEFI
                $table->decimal('creditos_convalidados', 5, 2)->default(3.00);
                $table->decimal('calificacion_homologada', 5, 2)->nullable();
                $table->string('tipo_convalidacion', 50)->default('Externa')->comment('Externa / Interna');
                
                // Respaldos oficiales
                $table->string('numero_resolucion', 100)->nullable();
                $table->date('fecha_resolucion')->nullable();
                $table->string('archivo_resolucion', 255)->nullable()->comment('Ruta al PDF probatorio en storage');
                
                // Estado del trámite
                $table->string('estado', 50)->default('Aprobada')->comment('Pendiente, Aprobada, Rechazada');
                $table->text('observaciones')->nullable();
                
                // Auditoría interna
                $table->unsignedBigInteger('registrado_por')->nullable();
                $table->unsignedBigInteger('aprobado_por')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convalidaciones');
    }
};
