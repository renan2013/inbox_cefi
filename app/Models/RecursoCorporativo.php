<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecursoCorporativo extends Model
{
    protected $table = 'recursos_corporativos';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'nombre_archivo',
        'tipo_archivo',
        'ruta_archivo',
        'id_programa',
        'categoria_academica'
    ];

    public function programa()
    {
        return $this->belongsTo(Programa::class, 'id_programa', 'id_programa');
    }
}
