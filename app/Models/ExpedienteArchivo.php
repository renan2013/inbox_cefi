<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpedienteArchivo extends Model
{
    protected $table = 'expediente_archivos';
    protected $primaryKey = 'id_archivo';
    public $timestamps = false;

    protected $fillable = [
        'id_expediente',
        'tipo_documento',
        'categoria',
        'nombre_original',
        'descripcion',
        'nombre_servidor',
        'ruta_archivo',
        'subido_por',
        'fecha_subida',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
    ];

    public function expediente()
    {
        return $this->belongsTo(ExpedienteDigital::class, 'id_expediente', 'id_expediente');
    }
}
