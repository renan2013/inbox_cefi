<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\WhatsAppService;

class MarketingProspecto extends Model
{
    protected $table = 'marketing_prospectos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'apellidos',
        'telefono',
        'email',
        'origen',
        'interes',
        'estado',
        'ultimo_contacto_at'
    ];

    protected $casts = [
        'ultimo_contacto_at' => 'datetime'
    ];

    /**
     * Accesor para obtener el nombre completo formateado.
     */
    public function getNombreCompletoAttribute(): string
    {
        $full = trim(($this->nombre ?? '') . ' ' . ($this->apellidos ?? ''));
        return !empty($full) ? $full : 'Prospecto';
    }

    /**
     * Mutador para normalizar el teléfono automáticamente antes de guardar.
     */
    public function setTelefonoAttribute($value)
    {
        $this->attributes['telefono'] = WhatsAppService::normalizarTelefono($value);
    }

    /**
     * Scope para filtrar únicamente prospectos activos con teléfono válido.
     */
    public function scopeActivosConWhatsApp($query)
    {
        return $query->where('estado', 'activo')
                     ->whereNotNull('telefono')
                     ->where('telefono', '!=', '');
    }
}
