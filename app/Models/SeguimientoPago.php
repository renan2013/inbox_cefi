<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientoPago extends Model
{
    protected $table = 'seguimiento_pagos';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_boleta',
        'id_estudiante',
        'numero_cuota',
        'monto_cuota',
        'interes_acumulado',
        'fecha_vencimiento',
        'ultimo_recalculo_interes',
        'estado',
        'fecha_notificado',
        'enviado_email',
        'fecha_creacion'
    ];

    protected $casts = [
        'monto_cuota' => 'decimal:2',
        'interes_acumulado' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'ultimo_recalculo_interes' => 'date',
        'fecha_notificado' => 'datetime',
        'enviado_email' => 'boolean',
        'fecha_creacion' => 'datetime'
    ];

    public function boleta()
    {
        return $this->belongsTo(Boleta::class, 'id_boleta', 'id');
    }

    public function estudiante()
    {
        return $this->belongsTo(Usuario::class, 'id_estudiante', 'id');
    }
}
