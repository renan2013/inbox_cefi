<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArregloPago extends Model
{
    protected $table = 'arreglos_pago';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'estudiante_id',
        'monto_total_acordado',
        'fecha_creacion',
        'estado',
        'observaciones'
    ];

    protected $casts = [
        'monto_total_acordado' => 'decimal:2',
        'fecha_creacion' => 'datetime'
    ];

    public function estudiante()
    {
        return $this->belongsTo(Usuario::class, 'estudiante_id', 'id');
    }
}
