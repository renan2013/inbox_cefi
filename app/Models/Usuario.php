<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_moodle',
        'nombre',
        'apellidos',
        'cedula',
        'email',
        'password',
        'id_rol',
        'pin_bodega',
        'creado_en',
        'fecha_nacimiento',
        'origen',
        'telefono'
    ];

    protected $hidden = [
        'password',
        'pin_bodega',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'creado_en' => 'datetime',
            'fecha_nacimiento' => 'date',
        ];
    }

    /**
     * Relación con el modelo Rol.
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id');
    }

    /**
     * Relación con el Expediente Digital (si existe).
     */
    public function expediente()
    {
        return $this->hasOne(ExpedienteDigital::class, 'id_usuario', 'id');
    }

    public function convalidaciones()
    {
        return $this->hasMany(Convalidacion::class, 'id_estudiante', 'id');
    }

    public function retiros()
    {
        return $this->hasMany(RetiroAsignatura::class, 'id_estudiante', 'id');
    }

    public function graduaciones()
    {
        return $this->hasMany(Graduacion::class, 'id_estudiante', 'id');
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'id_estudiante', 'id');
    }

    /**
     * Determina si el usuario tiene rol de Alumno / Estudiante.
     */
    public function esAlumno(): bool
    {
        if ((int)$this->id_rol === 3) {
            return true;
        }
        $nombreRol = strtolower(optional($this->rol)->nombre ?? '');
        return in_array($nombreRol, ['alumno', 'estudiante', 'miembro', 'alumnos', 'estudiantes']);
    }

    /**
     * Alias para esAlumno.
     */
    public function esEstudiante(): bool
    {
        return $this->esAlumno();
    }

    /**
     * Determina si el usuario es Docente / Profesor.
     */
    public function esDocente(): bool
    {
        if ((int)$this->id_rol === 2 || (int)$this->id_rol === 4) {
            return true;
        }
        $nombreRol = strtolower(optional($this->rol)->nombre ?? '');
        return in_array($nombreRol, ['profesor', 'docente', 'tutor', 'profesores', 'docentes']);
    }

    /**
     * Determina si el usuario es Administrador.
     */
    public function esAdmin(): bool
    {
        if ((int)$this->id_rol === 1) {
            return true;
        }
        $nombreRol = strtolower(optional($this->rol)->nombre ?? '');
        return in_array($nombreRol, ['administrador', 'admin', 'superadmin', 'coordinador']);
    }
}
