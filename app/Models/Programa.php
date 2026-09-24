<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    protected $table = 'programas';
    protected $primaryKey = 'id_programa';

    public $timestamps = false;

    protected $fillable = [
        'nombre_programa',
        'categoria',
        'informacion',
        'oferta',
        'perfil',
        'costo_materia',
        'costo_matricula',
        'costo_biblioteca',
        'costo_inscripcion_unica',
        'imagen_principal',
        'imagen_secundaria',
        'imagen_encabezado',
        'imagen_footer',
        'requisitos_ingreso',
        'detalles_programa',
        'normas_netiqueta'
    ];

    protected $casts = [
        'costo_materia' => 'decimal:2',
        'costo_matricula' => 'decimal:2',
        'costo_biblioteca' => 'decimal:2',
        'costo_inscripcion_unica' => 'decimal:2',
    ];

    public function planesEstudio()
    {
        return $this->hasMany(PlanEstudio::class, 'id_programa', 'id_programa');
    }
}
