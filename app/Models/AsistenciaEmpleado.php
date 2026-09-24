<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaEmpleado extends Model
{
    protected $table = 'asistencia_empleados';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'fecha',
        'hora_entrada',
        'hora_salida',
        'horas_trabajadas',
        'estado'
    ];

    protected $casts = [
        'fecha' => 'date',
        'horas_trabajadas' => 'decimal:2'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id');
    }
}
