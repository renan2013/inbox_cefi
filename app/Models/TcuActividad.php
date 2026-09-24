<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TcuActividad extends Model
{
    protected $table = 'tcu_actividades';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'bitacora_id',
        'fecha',
        'hora_entrada',
        'hora_salida',
        'cantidad_horas',
        'actividades',
        'fecha_registro'
    ];

    protected $casts = [
        'fecha' => 'date',
        'cantidad_horas' => 'decimal:2',
        'fecha_registro' => 'datetime'
    ];

    public function bitacora()
    {
        return $this->belongsTo(TcuBitacora::class, 'bitacora_id', 'id');
    }
}
