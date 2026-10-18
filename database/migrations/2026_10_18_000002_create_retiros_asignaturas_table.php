<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Módulo: Control Académico - Retiros y Cambios de Asignaturas (CEFI)
     */
    public function up(): void
    {
        if (!Schema::hasTable('retiros_asignaturas')) {
            Schema::create('retiros_asignaturas', function (Blueprint $table) {
                $table->id('id_retiro');
                $table->unsignedBigInteger('id_matricula')->index();
                $table->unsignedBigInteger('id_estudiante')->index();
                $table->unsignedBigInteger('id_curso_activo')->index()->comment('Curso activo actual a retirar');
                $table->unsignedBigInteger('id_curso_activo_nuevo')->nullable()->index()->comment('En caso de cambio de curso o grupo');
                
                // Tipo de solicitud
                $table->string('tipo_tramite', 50)->default('Retiro')->comment('Retiro / Cambio');
                $table->string('tipo_retiro', 50)->default('Justificado')->comment('Justificado, Ordinario, Fuerza Mayor, Medico, Laboral');
                $table->text('motivo');
                $table->string('documento_probatorio', 255)->nullable()->comment('Ruta al documento probatorio o constancia');
                
                // Fechas y resolución
                $table->dateTime('fecha_solicitud')->useCurrent();
                $table->dateTime('fecha_resolucion')->nullable();
                $table->string('estado', 50)->default('Pendiente')->comment('Pendiente, Aprobado, Rechazado');
                
                // Impacto financiero / Contable
                $table->string('impacto_financiero', 50)->default('Mantiene Cobro')->comment('Sin Costo, Mantiene Cobro, Nota de Credito, Cobro Diferencia');
                $table->decimal('monto_ajuste', 10, 2)->default(0.00);
                
                // Observaciones y auditoría
                $table->text('observaciones_resolucion')->nullable();
                $table->unsignedBigInteger('procesado_por')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retiros_asignaturas');
    }
};
