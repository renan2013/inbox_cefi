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
}
