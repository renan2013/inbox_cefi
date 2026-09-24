<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoTarifa extends Model
{
    protected $table = 'empleados_tarifas';
    protected $primaryKey = 'id_usuario';
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_rol_planilla',
        'cargo_credencial',
        'foto_credencial',
        'vigencia_credencial',
        'tipo_pago',
        'monto_pago',
        'moneda'
    ];

    protected $casts = [
        'vigencia_credencial' => 'date',
        'monto_pago' => 'decimal:2'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id');
    }

    public function rolPlanilla()
    {
        return $this->belongsTo(Rol::class, 'id_rol_planilla', 'id');
    }
}
