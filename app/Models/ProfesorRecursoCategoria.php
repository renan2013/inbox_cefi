<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfesorRecursoCategoria extends Model
{
    protected $table = 'profesor_recursos_categorias';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'id_profesor'
    ];

    public function profesor()
    {
        return $this->belongsTo(Usuario::class, 'id_profesor', 'id');
    }

    public function recursos()
    {
        return $this->hasMany(ProfesorRecurso::class, 'id_categoria', 'id');
    }
}
