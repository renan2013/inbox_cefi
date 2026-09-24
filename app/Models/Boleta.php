<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boleta extends Model
{
    protected $table = 'boletas';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'numero_boleta',
        'periodo',
        'id_estudiante',
        'id_creador',
        'total',
        'interes_acumulado',
        'saldo_pendiente',
        'monto_pagado',
        'estado',
        'cuotas',
        'aplicar_cargos_fijos',
        'ruta_firma',
        'ruta_pdf',
        'token_firma',
        'token_expiracion',
        'fecha_creacion',
        'enviado_email'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'interes_acumulado' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'cuotas' => 'integer',
        'aplicar_cargos_fijos' => 'boolean',
        'token_expiracion' => 'datetime',
        'fecha_creacion' => 'datetime',
        'enviado_email' => 'boolean',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Usuario::class, 'id_estudiante', 'id');
    }

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'id_creador', 'id');
    }
}
