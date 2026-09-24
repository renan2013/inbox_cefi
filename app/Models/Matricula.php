<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    protected $table = 'matriculas';
    protected $primaryKey = 'id_matricula';

    public $timestamps = false;

    protected $fillable = [
        'id_estudiante',
        'id_curso_activo',
        'id_boleta',
        'calificacion',
        'email_enviado',
        'fecha_envio_email'
    ];

    protected $casts = [
        'calificacion' => 'decimal:2',
        'email_enviado' => 'boolean',
        'fecha_envio_email' => 'datetime'
    ];

    public function estudiante()
    {
        return $this->belongsTo(Usuario::class, 'id_estudiante', 'id');
    }

    public function cursoActivo()
    {
        return $this->belongsTo(CursoActivo::class, 'id_curso_activo', 'id_curso_activo');
    }

    public function boleta()
    {
        return $this->belongsTo(Boleta::class, 'id_boleta', 'id');
    }
}
