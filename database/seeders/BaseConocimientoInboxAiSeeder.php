<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BaseConocimientoInboxAiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): array
    {
        // 1. Asegurar que la tabla y columnas existan
        if (!Schema::hasTable('base_conocimiento_inbox_ai')) {
            Schema::create('base_conocimiento_inbox_ai', function ($table) {
                $table->id();
                $table->string('pregunta');
                $table->text('palabras_clave')->nullable();
                $table->text('respuesta');
                $table->string('imagen')->nullable();
                $table->string('categoria', 100)->default('General');
                $table->string('ruta')->nullable();
                $table->boolean('estado')->default(true);
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('base_conocimiento_inbox_ai', 'ruta')) {
                Schema::table('base_conocimiento_inbox_ai', function ($table) {
                    $table->string('ruta')->nullable()->after('categoria');
                });
            }
        }

        // 2. Cargar el dataset de 500 preguntas
        $dataFilePath = __DIR__ . '/data/preguntas_inbox_cefi.php';
        if (!file_exists($dataFilePath)) {
            throw new \Exception("No se encontró el archivo de datos: {$dataFilePath}");
        }

        $items = require $dataFilePath;
        $totalItems = count($items);
        $insertados = 0;
        $actualizados = 0;
        $now = now();

        // 3. Procesar mediante updateOrInsert en lotes
        foreach ($items as $item) {
            $pregunta = trim($item['pregunta'] ?? '');
            if (empty($pregunta)) {
                continue;
            }

            $exists = DB::table('base_conocimiento_inbox_ai')
                ->where('pregunta', $pregunta)
                ->first();

            $payload = [
                'pregunta' => $pregunta,
                'palabras_clave' => trim($item['palabras_clave'] ?? ''),
                'respuesta' => trim($item['respuesta'] ?? ''),
                'categoria' => trim($item['categoria'] ?? 'General'),
                'ruta' => trim($item['ruta'] ?? ''),
                'estado' => intval($item['estado'] ?? 1),
                'updated_at' => $now,
            ];

            if ($exists) {
                DB::table('base_conocimiento_inbox_ai')
                    ->where('id', $exists->id)
                    ->update($payload);
                $actualizados++;
            } else {
                $payload['created_at'] = $now;
                DB::table('base_conocimiento_inbox_ai')->insert($payload);
                $insertados++;
            }
        }

        $totalFinal = DB::table('base_conocimiento_inbox_ai')->count();

        $this->command?->info("✓ Base de Conocimiento Tutor AI sembrada con éxito: {$insertados} insertados, {$actualizados} actualizados. Total en BD: {$totalFinal}");

        return [
            'total_cargados' => $totalItems,
            'insertados' => $insertados,
            'actualizados' => $actualizados,
            'total_en_bd' => $totalFinal,
        ];
    }
}
