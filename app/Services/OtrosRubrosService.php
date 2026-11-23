<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OtrosRubrosService
{
    /**
     * Categorías académicas estándar.
     */
    public static function categoriasAcademicas(): array
    {
        return [
            'Carrera Técnica',
            'Diplomado',
            'Curso Libre',
            'Certificación',
            'Bachillerato',
            'Licenciatura',
            'Maestría',
            'Doctorado',
        ];
    }

    /**
     * Catálogo inicial de otros rubros de pago (AV-CEFI-10).
     */
    public static function rubrosIniciales(): array
    {
        return [
            'CONSTANCIA',
            'REPOSICIÓN DE EXAMEN',
            'CONVALIDACIÓN',
            'EXAMEN POR SUFICIENCIA',
            'UNIFORME COMPLETO GABACHA',
            'UNIFORME CAMISA',
            'UNIFORME PHONETIC',
            'TITULO',
            'REPOSICIÓN DE TÍTULO',
            'CERTIFICADOS CURSO LIBRE - DE 8 SEMANAS',
            'CERTIFICADOS CURSO LIBRE + DE 8 SEMANAS',
            'REPOSICIÓN DE CERTIFICADO',
            'ACOMPAÑANTE GRADUACIÓN',
            'ENVÍO DE CORREOS',
            'EXAMEN DE AMPLIACIÓN',
            'HOSPEDAJE DIA ACTIVIDAD',
            'HOSPEDAJE DIA NORMAL',
            'TALLERES DENTAL',
            'TALLERES PREESCOLAR',
            'TALLERES VET. / EXP.',
            'TALLERES RED DE CUIDO',
            'PRÁCTICA PROFESIONAL',
            'PRÁCTICA PROFESIONAL FARMACIA',
            'PAGO DE GORRA',
            'MATERIAL DIDACTICO INGLES / PHONETIC',
            'PRUEBA DE NAT GEO',
            'PRUEBA TOEIC',
        ];
    }

    /**
     * Asegura la tabla rubros_pago_otros y la siembra con los rubros iniciales si está vacía.
     */
    public static function ensureTable(): void
    {
        try {
            if (!Schema::hasTable('rubros_pago_otros')) {
                DB::statement("
                    CREATE TABLE IF NOT EXISTS `rubros_pago_otros` (
                        `id` INT(11) NOT NULL AUTO_INCREMENT,
                        `nombre` VARCHAR(150) NOT NULL,
                        `descripcion` VARCHAR(255) NULL DEFAULT NULL,
                        `precio` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                        `activo` TINYINT(1) NOT NULL DEFAULT 1,
                        `creado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `uq_rubro_otro_nombre` (`nombre`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            } elseif (!Schema::hasColumn('rubros_pago_otros', 'descripcion')) {
                DB::statement("ALTER TABLE `rubros_pago_otros` ADD COLUMN `descripcion` VARCHAR(255) NULL DEFAULT NULL AFTER `nombre`");
            }

            if (DB::table('rubros_pago_otros')->count() === 0) {
                foreach (self::rubrosIniciales() as $nombre) {
                    DB::table('rubros_pago_otros')->insertOrIgnore([
                        'nombre' => $nombre,
                        'precio' => 0.00,
                        'activo' => 1,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Ignorar si ya existe o sin permisos DDL
        }
    }

    /**
     * Retorna todos los rubros activos ordenados alfabéticamente.
     */
    public static function all()
    {
        self::ensureTable();
        try {
            return DB::table('rubros_pago_otros')
                ->where('activo', 1)
                ->orderBy('nombre', 'asc')
                ->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }
}
