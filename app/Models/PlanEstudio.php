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
        'requisitos',
        'adjunto_pdf',
        'objetivo_general',
        'objetivos_especificos',
        'precio'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'creditos' => 'integer'
    ];

    public function programa()
    {
        return $this->belongsTo(Programa::class, 'id_programa', 'id_programa');
    }
}
