<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpedienteDigital extends Model
{
    protected $table = 'expedientes_digitales';
    protected $primaryKey = 'id_expediente';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'id_usuario',
        'grado_a_matricular',
        'especialidad_deseada',
        'genero',
        'fecha_nacimiento',
        'lugar_nacimiento',
        'nacionalidad',
        'cedula_residencia',
        'pasaporte',
        'estado_civil',
        'domicilio_direccion',
        'domicilio_provincia',
        'domicilio_canton',
        'domicilio_distrito',
        'contacto_tel_habitacion',
        'contacto_tel_celular',
        'contacto_otro_emergencias',
        'procedencia_secundaria_institucion',
        'procedencia_secundaria_ano_graduacion',
        'procedencia_secundaria_grado_obtenido',
        'procedencia_universidad',
        'procedencia_universidad_ano_graduacion',
        'procedencia_universidad_grado_obtenido',
        'procedencia_universidad_especialidad',
        'laboral_institucion',
        'laboral_fecha_ingreso',
        'laboral_puesto',
        'laboral_telefono',
        'laboral_extension',
        'laboral_fax',
        'laboral_correo_electronico',
        'registro_fecha_matricula',
        'registro_doc_titulo_sec',
        'registro_doc_titulo_univ',
        'registro_doc_certificaciones',
        'registro_doc_cedula',
        'registro_doc_fotografia',
        'registro_observaciones',
        'fecha_registro',
        'estado'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'laboral_fecha_ingreso' => 'date',
        'registro_fecha_matricula' => 'date',
        'fecha_registro' => 'date',
        'registro_doc_titulo_sec' => 'boolean',
        'registro_doc_titulo_univ' => 'boolean',
        'registro_doc_certificaciones' => 'boolean',
        'registro_doc_cedula' => 'boolean',
        'registro_doc_fotografia' => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id');
    }

    public function archivos()
    {
        return $this->hasMany(ExpedienteArchivo::class, 'id_expediente', 'id_expediente')->orderBy('fecha_subida', 'desc');
    }

    public function observaciones()
    {
        return $this->hasMany(ExpedienteObservacion::class, 'id_expediente', 'id_expediente')->orderBy('fecha_registro', 'desc');
    }

    public function getPorcentajeCompletitudAttribute(): int
    {
        $campos = [
            'grado_a_matricular', 'especialidad_deseada', 'genero', 'fecha_nacimiento',
            'nacionalidad', 'domicilio_direccion', 'contacto_tel_celular',
            'procedencia_secundaria_institucion', 'laboral_institucion', 'fecha_registro'
        ];

        $completados = 0;
        foreach ($campos as $campo) {
            if (!empty($this->{$campo})) $completados++;
        }

        if (!empty($this->cedula_residencia) || !empty($this->pasaporte)) {
            $completados++;
        }

        if ($this->estado === 'Aprobado') {
            $completados++;
        }

        return min(100, (int)round(($completados / 12) * 100));
    }
}
