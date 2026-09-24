<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clave extends Model
{
    protected $table = 'claves';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'usuario',
        'clave',
        'link'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime'
    ];
}
