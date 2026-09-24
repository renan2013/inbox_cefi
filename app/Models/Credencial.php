<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credencial extends Model
{
    protected $table = 'credenciales';
    protected $primaryKey = 'id_credencial';

    public $timestamps = false;

    protected $fillable = [
        'usuario',
        'clave',
        'link_acceso',
        'tipo',
        'datos_link',
        'creado_por'
    ];

    protected $casts = [
        'fecha' => 'datetime'
    ];

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'creado_por', 'id');
    }

    public function plataforma()
    {
        // Link matching lowcase
        return $this->belongsTo(Plataforma::class, 'link_acceso', 'link_acceso');
    }
}
