<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificacionSupervision extends Model
{
    protected $table = 'notificaciones_supervision';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_curso_activo',
        'tipo',
        'mensaje',
        'fecha_envio',
        'enviado_por'
    ];

    protected $casts = [
        'fecha_envio' => 'datetime'
    ];

    public function cursoActivo()
    {
        return $this->belongsTo(CursoActivo::class, 'id_curso_activo', 'id_curso_activo');
    }

    public function remitente()
    {
        return $this->belongsTo(Usuario::class, 'enviado_por', 'id');
    }
}
