<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Graduacion extends Model
{
    protected $table = 'graduaciones';
    protected $primaryKey = 'id_graduacion';

    protected $fillable = [
        'id_estudiante',
        'id_programa',
        'id_expediente',
        'convocatoria',
        'fecha_graduacion',
        'promedio_ponderado_final',
        'total_creditos_aprobados',
        'cumplio_plan_estudios',
        'cumplio_tcu',
        'cumplio_financiero',
        'cumplio_expediente',
        'mencion_honor',
        'numero_titulo',
        'numero_folio',
        'numero_tomo',
        'numero_acta',
        'estado',
        'observaciones',
        'autorizado_por',
        'fecha_autorizacion'
    ];

    protected $casts = [
        'fecha_graduacion' => 'date',
        'fecha_autorizacion' => 'datetime',
        'promedio_ponderado_final' => 'decimal:2',
        'total_creditos_aprobados' => 'decimal:2',
        'cumplio_plan_estudios' => 'boolean',
        'cumplio_tcu' => 'boolean',
        'cumplio_financiero' => 'boolean',
        'cumplio_expediente' => 'boolean',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Usuario::class, 'id_estudiante', 'id');
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class, 'id_programa', 'id_programa');
    }

    public function expediente()
    {
        return $this->belongsTo(ExpedienteDigital::class, 'id_expediente', 'id_expediente');
    }

    public function autorizador()
    {
        return $this->belongsTo(Usuario::class, 'autorizado_por', 'id');
    }
}
