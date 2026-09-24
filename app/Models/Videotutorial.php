<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Videotutorial extends Model
{
    protected $table = 'videotutoriales';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'categoria',
        'video_url',
        'descripcion',
        'duracion',
        'fecha_creacion'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime'
    ];
}
