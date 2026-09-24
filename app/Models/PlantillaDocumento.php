<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantillaDocumento extends Model
{
    protected $table = 'plantillas_documentos';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'imagen_fondo',
        'configuracion_campos',
        'ancho_mm',
        'alto_mm'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'ancho_mm' => 'float',
        'alto_mm' => 'float'
    ];
}
