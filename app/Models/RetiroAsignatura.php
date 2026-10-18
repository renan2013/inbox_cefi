<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetiroAsignatura extends Model
{
    protected $table = 'retiros_asignaturas';
    protected $primaryKey = 'id_retiro';

    protected $fillable = [
        'id_matricula',
        'id_estudiante',
        'id_curso_activo',
        'id_curso_activo_nuevo',
        'tipo_tramite',
        'tipo_retiro',
        'motivo',
        'documento_probatorio',
        'fecha_solicitud',
        'fecha_resolucion',
        'estado',
        'impacto_financiero',
        'monto_ajuste',
        'observaciones_resolucion',
        'procesado_por'
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_resolucion' => 'datetime',
        'monto_ajuste' => 'decimal:2'
    ];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'id_matricula', 'id_matricula');
    }

    public function estudiante()
    {
        return $this->belongsTo(Usuario::class, 'id_estudiante', 'id');
    }

    public function cursoActivo()
    {
        return $this->belongsTo(CursoActivo::class, 'id_curso_activo', 'id_curso_activo');
    }

    public function cursoActivoNuevo()
    {
        return $this->belongsTo(CursoActivo::class, 'id_curso_activo_nuevo', 'id_curso_activo');
    }

    public function procesador()
    {
        return $this->belongsTo(Usuario::class, 'procesado_por', 'id');
    }
}
