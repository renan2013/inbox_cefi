<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Soporte de estados de matrícula y retiros en matriculas
     */
    public function up(): void
    {
        if (!Schema::hasTable('matriculas')) {
            Schema::create('matriculas', function (Blueprint $table) {
                $table->id('id_matricula');
                $table->unsignedBigInteger('id_estudiante')->index();
                $table->unsignedBigInteger('id_curso_activo')->index();
                $table->unsignedBigInteger('id_boleta')->nullable()->index();
                $table->decimal('calificacion', 5, 2)->nullable();
                $table->text('comentarios')->nullable();
                $table->boolean('email_enviado')->default(false);
                $table->dateTime('fecha_envio_email')->nullable();
                
                // Campos de estado académico y retiro
                $table->string('estado', 50)->default('Matriculado')->comment('Matriculado, Aprobado, Reprobado, Retirado, Convalidado');
                $table->dateTime('fecha_retiro')->nullable();
                $table->string('motivo_retiro', 255)->nullable();
                $table->unsignedBigInteger('id_retiro')->nullable()->index();
            });
        } else {
            Schema::table('matriculas', function (Blueprint $table) {
                if (!Schema::hasColumn('matriculas', 'estado')) {
                    $table->string('estado', 50)->default('Matriculado')->comment('Matriculado, Aprobado, Reprobado, Retirado, Convalidado');
                }
                if (!Schema::hasColumn('matriculas', 'fecha_retiro')) {
                    $table->dateTime('fecha_retiro')->nullable();
                }
                if (!Schema::hasColumn('matriculas', 'motivo_retiro')) {
                    $table->string('motivo_retiro', 255)->nullable();
                }
                if (!Schema::hasColumn('matriculas', 'id_retiro')) {
                    $table->unsignedBigInteger('id_retiro')->nullable()->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('matriculas')) {
            Schema::table('matriculas', function (Blueprint $table) {
                if (Schema::hasColumn('matriculas', 'id_retiro')) {
                    $table->dropColumn('id_retiro');
                }
                if (Schema::hasColumn('matriculas', 'motivo_retiro')) {
                    $table->dropColumn('motivo_retiro');
                }
                if (Schema::hasColumn('matriculas', 'fecha_retiro')) {
                    $table->dropColumn('fecha_retiro');
                }
                if (Schema::hasColumn('matriculas', 'estado')) {
                    $table->dropColumn('estado');
                }
            });
        }
    }
};
