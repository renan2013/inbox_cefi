<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncuestaPregunta extends Model
{
    use HasFactory;

    protected $table = 'encuesta_preguntas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'texto',
        'categoria',
        'orden',
        'activo'
    ];

    protected $casts = [
        'orden' => 'integer',
        'activo' => 'integer',
    ];

    public function opciones()
    {
        return $this->hasMany(EncuestaOpcion::class, 'id_pregunta', 'id')->orderBy('orden', 'asc');
    }

    public function respuestas()
    {
        return $this->hasMany(EncuestaRespuesta::class, 'id_pregunta', 'id');
    }
}
