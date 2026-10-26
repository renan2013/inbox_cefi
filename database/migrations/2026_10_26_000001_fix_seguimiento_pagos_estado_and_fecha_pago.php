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
        if (Schema::hasTable('seguimiento_pagos')) {
            try {
                DB::statement("ALTER TABLE `seguimiento_pagos` MODIFY COLUMN `estado` VARCHAR(50) NOT NULL DEFAULT 'pendiente'");
            } catch (\Throwable $e) {}

            Schema::table('seguimiento_pagos', function (Blueprint $table) {
                if (!Schema::hasColumn('seguimiento_pagos', 'fecha_pago')) {
                    $table->dateTime('fecha_pago')->nullable()->after('estado');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('seguimiento_pagos')) {
            Schema::table('seguimiento_pagos', function (Blueprint $table) {
                if (Schema::hasColumn('seguimiento_pagos', 'fecha_pago')) {
                    $table->dropColumn('fecha_pago');
                }
            });
        }
    }
};
