<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ingresos_sistema')) {
            Schema::create('ingresos_sistema', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_usuario')->nullable()->index();
                $table->string('nombre', 150);
                $table->string('email', 150)->index();
                $table->unsignedTinyInteger('id_rol')->default(2);
                $table->string('ip', 45)->nullable();
                $table->string('origen', 80)->default('inbox_cefi');
                $table->dateTime('fecha_ingreso')->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ingresos_sistema');
    }
};
