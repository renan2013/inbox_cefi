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
        if (Schema::hasTable('boletas')) {
            Schema::table('boletas', function (Blueprint $table) {
                if (!Schema::hasColumn('boletas', 'descuento')) {
                    $table->decimal('descuento', 10, 2)->default(0)->after('total');
                }
                if (!Schema::hasColumn('boletas', 'cobrar_inscripcion')) {
                    $table->boolean('cobrar_inscripcion')->default(false)->after('aplicar_cargos_fijos');
                }
                if (!Schema::hasColumn('boletas', 'cobrar_biblioteca')) {
                    $table->boolean('cobrar_biblioteca')->default(false)->after('cobrar_inscripcion');
                }
                if (!Schema::hasColumn('boletas', 'cobrar_matricula')) {
                    $table->boolean('cobrar_matricula')->default(true)->after('cobrar_biblioteca');
                }
                if (!Schema::hasColumn('boletas', 'pago_inicial')) {
                    $table->decimal('pago_inicial', 10, 2)->default(0)->after('monto_pagado');
                }
                if (!Schema::hasColumn('boletas', 'pago_inicial_metodo')) {
                    $table->string('pago_inicial_metodo', 50)->nullable()->after('pago_inicial');
                }
                if (!Schema::hasColumn('boletas', 'pago_inicial_referencia')) {
                    $table->string('pago_inicial_referencia', 255)->nullable()->after('pago_inicial_metodo');
                }
                if (!Schema::hasColumn('boletas', 'fechas_vencimiento_json')) {
                    $table->text('fechas_vencimiento_json')->nullable()->after('pago_inicial_referencia');
                }
                if (!Schema::hasColumn('boletas', 'fecha_firma')) {
                    $table->dateTime('fecha_firma')->nullable()->after('ruta_firma');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('boletas')) {
            Schema::table('boletas', function (Blueprint $table) {
                $columns = [
                    'descuento',
                    'cobrar_inscripcion',
                    'cobrar_biblioteca',
                    'cobrar_matricula',
                    'pago_inicial',
                    'pago_inicial_metodo',
                    'pago_inicial_referencia',
                    'fechas_vencimiento_json',
                    'fecha_firma',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('boletas', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
