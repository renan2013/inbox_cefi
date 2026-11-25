<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RazonesDescuentoService
{
    /**
     * Catálogo inicial de razones de descuento o ajuste de precio.
     */
    public static function razonesIniciales(): array
    {
        return [
            'Beca',
            'Promoción',
            'Convenio Institucional',
            'Descuento Pronto Pago',
            'Descuento Familiar / Referido',
            'Acuerdo Especial / Cortesía',
        ];
    }

    /**
     * Asegura la tabla razones_descuento y la siembra si está vacía.
     */
    public static function ensureTable(): void
    {
        try {
            if (!Schema::hasTable('razones_descuento')) {
                DB::statement("
                    CREATE TABLE IF NOT EXISTS `razones_descuento` (
                        `id` INT(11) NOT NULL AUTO_INCREMENT,
                        `nombre` VARCHAR(150) NOT NULL,
                        `activo` TINYINT(1) NOT NULL DEFAULT 1,
                        `creado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `uq_razon_descuento_nombre` (`nombre`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }

            if (DB::table('razones_descuento')->count() === 0) {
                foreach (self::razonesIniciales() as $nombre) {
                    DB::table('razones_descuento')->insertOrIgnore([
                        'nombre' => $nombre,
                        'activo' => 1,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Ignorar si ya existe o sin permisos DDL
        }
    }

    /**
     * Retorna todas las razones activas ordenadas alfabéticamente.
     */
    public static function all()
    {
        self::ensureTable();
        try {
            return DB::table('razones_descuento')
                ->where('activo', 1)
                ->orderBy('nombre', 'asc')
                ->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }
}
