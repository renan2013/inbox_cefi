<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaProfesor extends Model
{
    protected $table = 'facturas_profesores';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_curso_activo',
        'id_profesor',
        'numero_factura',
        'monto',
        'moneda',
        'archivo_ruta',
        'fecha_subida',
        'estado_revision',
        'comentarios'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_subida' => 'datetime'
    ];

    public function cursoActivo()
    {
        return $this->belongsTo(CursoActivo::class, 'id_curso_activo', 'id_curso_activo');
    }

    public function profesor()
    {
        return $this->belongsTo(Usuario::class, 'id_profesor', 'id');
    }
}
