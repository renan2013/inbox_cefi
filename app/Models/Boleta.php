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
        'descuento',
        'interes_acumulado',
        'saldo_pendiente',
        'monto_pagado',
        'pago_inicial',
        'pago_inicial_metodo',
        'pago_inicial_referencia',
        'fechas_vencimiento_json',
        'estado',
        'cuotas',
        'aplicar_cargos_fijos',
        'cobrar_inscripcion',
        'cobrar_biblioteca',
        'cobrar_matricula',
        'ruta_firma',
        'ruta_pdf',
        'token_firma',
        'token_expiracion',
        'fecha_creacion',
        'fecha_firma',
        'enviado_email'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'descuento' => 'decimal:2',
        'interes_acumulado' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'pago_inicial' => 'decimal:2',
        'cuotas' => 'integer',
        'aplicar_cargos_fijos' => 'boolean',
        'cobrar_inscripcion' => 'boolean',
        'cobrar_biblioteca' => 'boolean',
        'cobrar_matricula' => 'boolean',
        'token_expiracion' => 'datetime',
        'fecha_creacion' => 'datetime',
        'fecha_firma' => 'datetime',
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

    public function seguimientoPagos()
    {
        return $this->hasMany(SeguimientoPago::class, 'id_boleta', 'id')->orderBy('numero_cuota');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'boleta_id', 'id')->orderBy('fecha_pago', 'desc');
    }
}
