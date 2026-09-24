<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncuestaOpcion extends Model
{
    use HasFactory;

    protected $table = 'encuesta_opciones';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_pregunta',
        'texto',
        'emoji',
        'puntaje',
        'orden'
    ];

    protected $casts = [
        'id_pregunta' => 'integer',
        'puntaje' => 'integer',
        'orden' => 'integer',
    ];

    public function pregunta()
    {
        return $this->belongsTo(EncuestaPregunta::class, 'id_pregunta', 'id');
    }
}
