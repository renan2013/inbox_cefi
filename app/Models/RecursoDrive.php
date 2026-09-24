<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecursoDrive extends Model
{
    protected $table = 'recursos_drive';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_curso_activo',
        'nombre_archivo',
        'id_drive',
        'link_publico',
        'fecha_subida',
        'es_compartido'
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
        'es_compartido' => 'boolean'
    ];

    public function cursoActivo()
    {
        return $this->belongsTo(CursoActivo::class, 'id_curso_activo', 'id_curso_activo');
    }
}
