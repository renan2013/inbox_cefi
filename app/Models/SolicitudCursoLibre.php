<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudCursoLibre extends Model
{
    protected $table = 'solicitudes_cursos_libres';
    protected $primaryKey = 'id';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null; // No hay columna de actualización en esta tabla

    protected $fillable = [
        'nombre',
        'primer_apellido',
        'segundo_apellido',
        'nacionalidad',
        'identificacion',
        'programa_deseado',
        'sexo',
        'estado_civil',
        'fecha_nacimiento',
        'lugar_nacimiento',
        'profesion',
        'telefono',
        'email',
        'direccion'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_creacion' => 'datetime'
    ];
}
