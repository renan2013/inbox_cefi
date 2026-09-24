<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfesorRecurso extends Model
{
    protected $table = 'profesor_recursos';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'descripcion',
        'url',
        'id_categoria',
        'id_profesor',
        'tipo',
        'contenido',
        'fecha_creacion',
        'es_compartido'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'es_compartido' => 'boolean'
    ];

    public function categoria()
    {
        return $this->belongsTo(ProfesorRecursoCategoria::class, 'id_categoria', 'id');
    }

    public function profesor()
    {
        return $this->belongsTo(Usuario::class, 'id_profesor', 'id');
    }
}
