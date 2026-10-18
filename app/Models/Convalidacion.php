<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Convalidacion extends Model
{
    protected $table = 'convalidaciones';
    protected $primaryKey = 'id_convalidacion';

    protected $fillable = [
        'id_estudiante',
        'id_expediente',
        'id_programa',
        'id_plan',
        'institucion_origen',
        'carrera_origen',
        'materia_origen',
        'codigo_origen',
        'creditos_origen',
        'calificacion_origen',
        'creditos_convalidados',
        'calificacion_homologada',
        'tipo_convalidacion',
        'numero_resolucion',
        'fecha_resolucion',
        'archivo_resolucion',
        'estado',
        'observaciones',
        'registrado_por',
        'aprobado_por'
    ];

    protected $casts = [
        'creditos_origen' => 'decimal:2',
        'creditos_convalidados' => 'decimal:2',
        'calificacion_homologada' => 'decimal:2',
        'fecha_resolucion' => 'date',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Usuario::class, 'id_estudiante', 'id');
    }

    public function expediente()
    {
        return $this->belongsTo(ExpedienteDigital::class, 'id_expediente', 'id_expediente');
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class, 'id_programa', 'id_programa');
    }

    public function planEstudio()
    {
        return $this->belongsTo(PlanEstudio::class, 'id_plan', 'id_plan');
    }

    public function registrador()
    {
        return $this->belongsTo(Usuario::class, 'registrado_por', 'id');
    }

    public function aprobador()
    {
        return $this->belongsTo(Usuario::class, 'aprobado_por', 'id');
    }
}
