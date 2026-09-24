<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoporteCategoria extends Model
{
    protected $table = 'soporte_categorias';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nombre'
    ];

    public function soportes()
    {
        return $this->hasMany(Soporte::class, 'categoria_id', 'id');
    }
}
