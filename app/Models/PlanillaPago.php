<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanillaPago extends Model
{
    protected $table = 'planilla_pagos';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'fecha_inicio',
        'fecha_fin',
        'tipo_pago',
        'horas_totales',
        'tarifa_aplicada',
        'monto_total',
        'fecha_pago',
        'estado',
        'notas'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_pago' => 'date',
        'horas_totales' => 'decimal:2',
        'tarifa_aplicada' => 'decimal:2',
        'monto_total' => 'decimal:2'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id');
    }
}
