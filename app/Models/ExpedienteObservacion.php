<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpedienteObservacion extends Model
{
    protected $table = 'expediente_observaciones';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_expediente',
        'id_autor',
        'autor_nombre',
        'categoria',
        'observacion',
        'fecha_registro',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
    ];

    public function expediente()
    {
        return $this->belongsTo(ExpedienteDigital::class, 'id_expediente', 'id_expediente');
    }

    public function autor()
    {
        return $this->belongsTo(Usuario::class, 'id_autor', 'id');
    }
}
