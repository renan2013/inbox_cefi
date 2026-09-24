<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TcuBitacora extends Model
{
    protected $table = 'tcu_bitacoras';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'nombre_proyecto',
        'lugar_realizacion',
        'institucion_beneficiada',
        'nombre_supervisor',
        'cedula_supervisor',
        'email_institucion',
        'telefono_institucion',
        'carrera',
        'estado',
        'fecha_creacion',
        'fecha_finalizacion'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_finalizacion' => 'date'
    ];

    public function estudiante()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id');
    }

    public function actividades()
    {
        return $this->hasMany(TcuActividad::class, 'bitacora_id', 'id');
    }
}
