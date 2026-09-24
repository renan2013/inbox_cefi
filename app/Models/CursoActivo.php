<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CursoActivo extends Model
{
    protected $table = 'cursos_activos';
    protected $primaryKey = 'id_curso_activo';

    public $timestamps = false;

    protected $fillable = [
        'id_plan',
        'id_profesor',
        'periodo',
        'fecha_inicio',
        'fecha_final',
        'hora_inicio',
        'hora_final',
        'titulo_prefijo',
        'titulo_sufijo',
        'moodle_course_id',
        'enlace_zoom',
        'id_moodle',
        'check_proceso',
        'check_listo',
        'check_moodle',
        'check_estudiantes',
        'check_terminado',
        'modalidad',
        'tipo_curso',
        'horario',
        'en_supervision',
        'check_actividades',
        'check_calificaciones',
        'check_promedios',
        'check_acta',
        'check_pago',
        'fecha_notificacion',
        'fecha_whatsapp',
        'check_recursos',
        'check_portada'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_final' => 'date',
        'fecha_notificacion' => 'datetime',
        'fecha_whatsapp' => 'datetime',
        'check_proceso' => 'boolean',
        'check_listo' => 'boolean',
        'check_moodle' => 'boolean',
        'check_estudiantes' => 'boolean',
        'check_terminado' => 'boolean',
        'en_supervision' => 'boolean',
        'check_actividades' => 'boolean',
        'check_calificaciones' => 'boolean',
        'check_promedios' => 'boolean',
        'check_acta' => 'boolean',
        'check_pago' => 'boolean',
        'check_recursos' => 'boolean',
        'check_portada' => 'boolean',
    ];

    public function planEstudio()
    {
        return $this->belongsTo(PlanEstudio::class, 'id_plan', 'id_plan');
    }

    public function profesor()
    {
        return $this->belongsTo(Usuario::class, 'id_profesor', 'id');
    }
}
