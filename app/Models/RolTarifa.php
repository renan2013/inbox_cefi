<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolTarifa extends Model
{
    protected $table = 'roles_tarifas';
    protected $primaryKey = 'id_rol';
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_rol',
        'tipo_pago',
        'monto_pago',
        'moneda'
    ];

    protected $casts = [
        'monto_pago' => 'decimal:2'
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id');
    }
}
