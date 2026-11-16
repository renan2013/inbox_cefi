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
        'duracion',
        'modalidad',
        'informacion',
        'objetivo_curso',
        'oferta',
        'opciones_laborales',
        'perfil',
        'lo_que_obtendras',
        'costo_materia',
        'costo_matricula',
        'costo_biblioteca',
        'costo_inscripcion_unica',
        'imagen_principal',
        'imagen_secundaria',
        'imagen_encabezado',
        'imagen_footer',
        'duracion_plan_estudios',
        'perfil_docentes',
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

    /**
     * Obtiene la ruta del encabezado institucional estándar de CEFI.
     */
    public static function getHeaderInstitucional(): ?string
    {
        // 1. Configuración del sistema en BD
        $val = \App\Models\ConfiguracionSistema::where('clave', 'programa_header_default')->value('valor');
        if ($val && file_exists(public_path($val))) {
            return $val;
        }

        // 2. Archivo fijo en imgs/
        if (file_exists(public_path('imgs/cefi_header_programa.png'))) {
            return 'imgs/cefi_header_programa.png';
        }

        // 3. Fallback inteligente: buscar en programas existentes que ya tengan encabezado
        $first = self::whereNotNull('imagen_encabezado')
            ->where('imagen_encabezado', '!=', '')
            ->first();
        if ($first && !empty($first->imagen_encabezado) && file_exists(public_path($first->imagen_encabezado))) {
            try {
                \App\Models\ConfiguracionSistema::updateOrCreate(
                    ['clave' => 'programa_header_default'],
                    ['valor' => $first->imagen_encabezado]
                );
            } catch (\Throwable $e) {}
            return $first->imagen_encabezado;
        }

        return null;
    }

    /**
     * Obtiene la ruta del pie de página (footer) institucional estándar de CEFI.
     */
    public static function getFooterInstitucional(): ?string
    {
        // 1. Configuración del sistema en BD
        $val = \App\Models\ConfiguracionSistema::where('clave', 'programa_footer_default')->value('valor');
        if ($val && file_exists(public_path($val))) {
            return $val;
        }

        // 2. Archivo fijo en imgs/
        if (file_exists(public_path('imgs/cefi_footer_programa.png'))) {
            return 'imgs/cefi_footer_programa.png';
        }

        // 3. Fallback inteligente: buscar en programas existentes que ya tengan pie de página
        $first = self::whereNotNull('imagen_footer')
            ->where('imagen_footer', '!=', '')
            ->first();
        if ($first && !empty($first->imagen_footer) && file_exists(public_path($first->imagen_footer))) {
            try {
                \App\Models\ConfiguracionSistema::updateOrCreate(
                    ['clave' => 'programa_footer_default'],
                    ['valor' => $first->imagen_footer]
                );
            } catch (\Throwable $e) {}
            return $first->imagen_footer;
        }

        return null;
    }
}
