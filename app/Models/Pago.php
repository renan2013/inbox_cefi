<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'boleta_id',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'referencia',
        'interes_condonado',
        'enviado_email',
        'registrado_por',
        'ruta_comprobante'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'interes_condonado' => 'decimal:2',
        'fecha_pago' => 'datetime',
        'enviado_email' => 'boolean'
    ];

    public function boleta()
    {
        return $this->belongsTo(Boleta::class, 'boleta_id', 'id');
    }

    public function registrador()
    {
        return $this->belongsTo(Usuario::class, 'registrado_por', 'id');
    }
}
