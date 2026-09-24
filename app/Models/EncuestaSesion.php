<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncuestaSesion extends Model
{
    use HasFactory;

    protected $table = 'encuesta_sesiones';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_curso_activo',
        'ip_origen',
        'comentarios',
        'fecha_envio'
    ];

    protected $casts = [
        'id_curso_activo' => 'integer',
        'fecha_envio' => 'datetime',
    ];

    public function curso()
    {
        return $this->belongsTo(CursoActivo::class, 'id_curso_activo', 'id_curso_activo');
    }

    public function respuestas()
    {
        return $this->hasMany(EncuestaRespuesta::class, 'id_sesion', 'id');
    }
}
