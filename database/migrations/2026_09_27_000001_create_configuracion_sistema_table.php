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
        if (!Schema::hasTable('configuracion_sistema')) {
            Schema::create('configuracion_sistema', function (Blueprint $table) {
                $table->id();
                $table->string('clave', 120)->unique();
                $table->longText('valor')->nullable();
                $table->text('descripcion')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_sistema');
    }
};
