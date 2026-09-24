<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevisionCurso extends Model
{
    protected $table = 'revisiones_curso';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_curso_activo',
        'sugerencia',
        'captura_pantalla',
        'estado',
        'fecha_creacion',
        'creado_por',
        'fecha_resolucion',
        'resuelto_por'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_resolucion' => 'datetime'
    ];

    public function cursoActivo()
    {
        return $this->belongsTo(CursoActivo::class, 'id_curso_activo', 'id_curso_activo');
    }

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'creado_por', 'id');
    }

    public function resolutor()
    {
        return $this->belongsTo(Usuario::class, 'resuelto_por', 'id');
    }
}
