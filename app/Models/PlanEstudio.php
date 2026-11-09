<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanEstudio extends Model
{
    protected $table = 'plan_estudios';
    protected $primaryKey = 'id_plan';

    public $timestamps = false;

    protected $fillable = [
        'id_programa',
        'cuatrimestre',
        'codigo',
        'materia',
        'creditos',
        'duracion',
        'distribucion_horas',
        'horas_teoricas',
        'horas_practicas',
        'horas_independientes',
        'horas_totales',
        'modalidad',
        'naturaleza',
        'nivel',
        'requisitos',
        'correquisitos',
        'profesor',
        'precio',
        'adjunto_pdf',
        'descripcion_curso',
        'objetivo_general',
        'objetivos_especificos',
        'contenidos_tematicos',
        'metodologia_ensenanza',
        'estrategias_aprendizaje',
        'evaluacion_aprendizajes',
        'recursos_didacticos',
        'cronograma',
        'guias_evaluacion',
        'bibliografia',
        'fecha_descriptor_pdf'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'creditos' => 'integer',
        'horas_teoricas' => 'integer',
        'horas_practicas' => 'integer',
        'horas_independientes' => 'integer',
        'horas_totales' => 'integer',
        'fecha_descriptor_pdf' => 'datetime'
    ];

    public function programa()
    {
        return $this->belongsTo(Programa::class, 'id_programa', 'id_programa');
    }

    /**
     * Calcula el porcentaje de completitud (0 a 100%) del sílabo / descriptor curricular.
     */
    public function getPorcentajeSilaboAttribute(): int
    {
        $puntos = 0;

        // 1. Ficha técnica básica (Código y Materia) -> 15%
        if (!empty($this->codigo) && !empty($this->materia)) {
            $puntos += 15;
        }

        $tieneContenido = function ($texto, $minLen = 10) {
            if (empty($texto)) return false;
            $limpio = trim(html_entity_decode(strip_tags((string)$texto), ENT_QUOTES, 'UTF-8'));
            return mb_strlen($limpio, 'UTF-8') >= $minLen;
        };

        // 2. I. Descripción del curso -> 15%
        if ($tieneContenido($this->descripcion_curso, 15)) {
            $puntos += 15;
        }

        // 3. II-III. Objetivos -> 15%
        if ($tieneContenido($this->objetivo_general, 10) || $tieneContenido($this->objetivos_especificos, 15)) {
            $puntos += 15;
        }

        // 4. IV-VI. Contenidos temáticos y Metodología -> 15%
        if ($tieneContenido($this->contenidos_tematicos, 15) && $tieneContenido($this->metodologia_ensenanza, 10)) {
            $puntos += 15;
        } elseif ($tieneContenido($this->contenidos_tematicos, 15) || $tieneContenido($this->metodologia_ensenanza, 10)) {
            $puntos += 8;
        }

        // 5. VII. Evaluación de los aprendizajes -> 15%
        if ($tieneContenido($this->evaluacion_aprendizajes, 10)) {
            $puntos += 15;
        }

        // 6. VIII. Cronograma -> 15%
        if ($tieneContenido($this->cronograma, 25)) {
            $puntos += 15;
        }

        // 7. IX-XI. Guías de evaluación y Bibliografía -> 10%
        if ($tieneContenido($this->guias_evaluacion, 15) && $tieneContenido($this->bibliografia, 10)) {
            $puntos += 10;
        } elseif ($tieneContenido($this->guias_evaluacion, 15) || $tieneContenido($this->bibliografia, 10)) {
            $puntos += 5;
        }

        return min(100, $puntos);
    }
}
