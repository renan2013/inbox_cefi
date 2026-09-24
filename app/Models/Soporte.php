<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Soporte extends Model
{
    protected $table = 'soporte';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'problema',
        'solucion',
        'categoria_id',
        'id_creador',
        'reportado_a',
        'estado',
        'adjunto'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime'
    ];

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'id_creador', 'id');
    }

    public function reportadoA()
    {
        return $this->belongsTo(Usuario::class, 'reportado_a', 'id');
    }

    public function categoria()
    {
        return $this->belongsTo(SoporteCategoria::class, 'categoria_id', 'id');
    }
}
