<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncuestaRespuesta extends Model
{
    use HasFactory;

    protected $table = 'encuesta_respuestas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_sesion',
        'id_pregunta',
        'id_opcion',
        'puntaje_obtenido'
    ];

    protected $casts = [
        'id_sesion' => 'integer',
        'id_pregunta' => 'integer',
        'id_opcion' => 'integer',
        'puntaje_obtenido' => 'integer',
    ];

    public function sesion()
    {
        return $this->belongsTo(EncuestaSesion::class, 'id_sesion', 'id');
    }

    public function pregunta()
    {
        return $this->belongsTo(EncuestaPregunta::class, 'id_pregunta', 'id');
    }

    public function opcion()
    {
        return $this->belongsTo(EncuestaOpcion::class, 'id_opcion', 'id');
    }
}
