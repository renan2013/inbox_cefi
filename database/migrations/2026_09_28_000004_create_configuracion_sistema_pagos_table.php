<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('configuracion_sistema_pagos')) {
            Schema::create('configuracion_sistema_pagos', function (Blueprint $table) {
                $table->id();
                $table->string('clave', 100)->unique();
                $table->text('valor')->nullable();
                $table->string('categoria', 50)->default('general');
                $table->string('descripcion', 255)->nullable();
                $table->timestamps();
            });

            // Semillas iniciales por defecto (UNELA / CEFI)
            $defaults = [
                ['clave' => 'firma_oficial_nombre', 'valor' => 'Merlin Silva', 'categoria' => 'firmas', 'descripcion' => 'Nombre de la autoridad o administrador firmante'],
                ['clave' => 'firma_oficial_cargo', 'valor' => 'Administración General', 'categoria' => 'firmas', 'descripcion' => 'Cargo oficial para la firma en documentos'],
                ['clave' => 'firma_oficial_imagen', 'valor' => '', 'categoria' => 'firmas', 'descripcion' => 'Ruta relativa de la imagen de firma o sello oficial'],
                ['clave' => 'tasa_interes_mora', 'valor' => '2.0', 'categoria' => 'morosidad', 'descripcion' => 'Porcentaje de interés o recargo por mora (%)'],
                ['clave' => 'tipo_interes_mora', 'valor' => 'diario_compuesto', 'categoria' => 'morosidad', 'descripcion' => 'Método de cálculo: diario_compuesto, diario_simple, mensual_simple'],
                ['clave' => 'dias_gracia_mora', 'valor' => '0', 'categoria' => 'morosidad', 'descripcion' => 'Días de gracia de tolerancia antes de iniciar cálculo de mora'],
                ['clave' => 'monto_inscripcion_unica', 'valor' => '8000.00', 'categoria' => 'cargos', 'descripcion' => 'Monto predeterminado de Inscripción Única (INS-01)'],
                ['clave' => 'monto_biblioteca', 'valor' => '5000.00', 'categoria' => 'cargos', 'descripcion' => 'Monto predeterminado de Uso de Biblioteca (BIB-01)'],
                ['clave' => 'monto_matricula_base', 'valor' => '30000.00', 'categoria' => 'cargos', 'descripcion' => 'Monto predeterminado de Matrícula de Período (ADM-01)']
            ];

            foreach ($defaults as $d) {
                DB::table('configuracion_sistema_pagos')->insertOrIgnore($d);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_sistema_pagos');
    }
};
