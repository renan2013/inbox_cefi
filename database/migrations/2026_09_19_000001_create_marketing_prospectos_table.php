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
        if (!Schema::hasTable('marketing_prospectos')) {
            Schema::create('marketing_prospectos', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 100)->nullable();
                $table->string('apellidos', 100)->nullable();
                $table->string('telefono', 30)->index();
                $table->string('email', 150)->nullable()->index();
                $table->string('origen', 100)->default('Base Externa')->index();
                $table->string('interes', 100)->nullable();
                $table->enum('estado', ['activo', 'contactado', 'baja'])->default('activo')->index();
                $table->timestamp('ultimo_contacto_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_prospectos');
    }
};
