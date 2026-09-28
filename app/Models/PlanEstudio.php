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
}
