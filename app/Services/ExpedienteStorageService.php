<?php

namespace App\Services;

use ZipArchive;
use Illuminate\Support\Str;

class ExpedienteStorageService
{
    /**
     * Categorías oficiales del expediente digital.
     */
    public const CATEGORIAS = [
        '01_Identificacion'         => '01. Documentos de Identidad & Fotografías',
        '02_Titulos_y_Grados'       => '02. Títulos, Diplomas & Grados Académicos',
        '03_Admision_y_Matricula'   => '03. Admisión, Solicitudes & Boletas',
        '04_Convalidaciones'        => '04. Convalidaciones & Homologaciones',
        '05_TCU_y_Practicas'        => '05. TCU & Prácticas Profesionales',
        '06_Cartas_y_Recomendaciones'=> '06. Cartas de Recomendación & Atestados',
        '07_Comprobantes_Financieros'=> '07. Comprobantes de Pago & Financieros',
        '08_Justificaciones'        => '08. Justificaciones Médicas & Laborales',
        '09_General_y_Otros'        => '09. Otros Documentos Generales',
    ];

    /**
     * Obtiene la ruta base del directorio de subidas de expedientes.
     */
    public static function getBasePath(): string
    {
        // Si existe uploads/expedientes en public de Laravel o en el raíz padre
        $localUploads = public_path('uploads/expedientes');
        if (!is_dir($localUploads)) {
            @mkdir($localUploads, 0777, true);
        }
        return $localUploads;
    }

    /**
     * Sanitiza el nombre para crear la carpeta física del estudiante.
     */
    public static function sanitizarNombre(string $txt): string
    {
        $txt = Str::ascii($txt);
        $txt = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $txt);
        $txt = preg_replace('/[\s\-]+/', '_', $txt);
        return trim($txt, '_');
    }

    /**
     * Genera el nombre de la carpeta raíz del estudiante: EXP_00012_Nombre_Apellido
     */
    public static function getStudentFolderName(int $idExpediente, string $nombreCompleto = ''): string
    {
        $idPad = str_pad($idExpediente, 5, '0', STR_PAD_LEFT);
        $cleanName = self::sanitizarNombre($nombreCompleto);
        if (empty($cleanName)) {
            $cleanName = "Estudiante_{$idExpediente}";
        }
        return "EXP_{$idPad}_{$cleanName}";
    }

    /**
     * Determina la subcarpeta correspondiente según el tipo o categoría de documento.
     */
    public static function getCategoriaSubfolder(string $tipoDocumento = '', string $categoria = ''): string
    {
        $t = mb_strtolower(Str::ascii($tipoDocumento . ' ' . $categoria), 'UTF-8');

        if (str_contains($t, 'identidad') || str_contains($t, 'cedula') || str_contains($t, 'pasaporte') || str_contains($t, 'foto') || str_contains($t, 'identificacion')) {
            return '01_Identificacion';
        }
        if (str_contains($t, 'titulo') || str_contains($t, 'grado') || str_contains($t, 'bachiller') || str_contains($t, 'licenciatura') || str_contains($t, 'maestria')) {
            return '02_Titulos_y_Grados';
        }
        if (str_contains($t, 'matricula') || str_contains($t, 'admision') || str_contains($t, 'firma') || str_contains($t, 'boleta') || str_contains($t, 'inscripcion')) {
            return '03_Admision_y_Matricula';
        }
        if (str_contains($t, 'convalida') || str_contains($t, 'homologa') || str_contains($t, 'suficiencia')) {
            return '04_Convalidaciones';
        }
        if (str_contains($t, 'tcu') || str_contains($t, 'comunitario') || str_contains($t, 'practica')) {
            return '05_TCU_y_Practicas';
        }
        if (str_contains($t, 'pastoral') || str_contains($t, 'carta') || str_contains($t, 'recomenda') || str_contains($t, 'atestado')) {
            return '06_Cartas_y_Recomendaciones';
        }
        if (str_contains($t, 'comprobante') || str_contains($t, 'pago') || str_contains($t, 'deposito') || str_contains($t, 'financier') || str_contains($t, 'sinpe') || str_contains($t, 'factura')) {
            return '07_Comprobantes_Financieros';
        }
        if (str_contains($t, 'justifica') || str_contains($t, 'medic') || str_contains($t, 'incapacidad')) {
            return '08_Justificaciones';
        }

        return '09_General_y_Otros';
    }

    /**
     * Resuelve y asegura el directorio físico de almacenamiento.
     */
    public static function getTargetDir(int $idExpediente, string $nombreCompleto, string $tipoDocumento = '', string $categoria = ''): array
    {
        $base = self::getBasePath();
        $studentFolder = self::getStudentFolderName($idExpediente, $nombreCompleto);
        $catFolder = self::getCategoriaSubfolder($tipoDocumento, $categoria);

        $relativeDir = "{$studentFolder}/{$catFolder}/";
        $fullDir = rtrim($base, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir);

        if (!is_dir($fullDir)) {
            @mkdir($fullDir, 0777, true);
        }

        return [
            'full_dir'       => $fullDir,
            'relative_dir'   => $relativeDir,
            'student_folder' => $studentFolder,
            'cat_folder'     => $catFolder,
        ];
    }

    /**
     * Resuelve la ruta física y web de un archivo almacenado, con fallback a uploads raíz.
     */
    public static function resolveFilePath(string $nombreServidor): array
    {
        if (empty($nombreServidor)) {
            return ['full_path' => '', 'web_url' => '', 'exists' => false];
        }

        // Buscar primero en public de Laravel
        $base = self::getBasePath();
        $path = rtrim($base, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $nombreServidor);

        if (file_exists($path)) {
            return [
                'full_path' => $path,
                'web_url'   => asset('uploads/expedientes/' . $nombreServidor),
                'exists'    => true,
            ];
        }

        // Fallback: verificar en la instalación nativa ../uploads/expedientes/
        $parentPath = dirname(base_path()) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'expedientes' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $nombreServidor);
        if (file_exists($parentPath)) {
            return [
                'full_path' => $parentPath,
                'web_url'   => asset('uploads/expedientes/' . $nombreServidor),
                'exists'    => true,
            ];
        }

        return [
            'full_path' => $path,
            'web_url'   => asset('uploads/expedientes/' . $nombreServidor),
            'exists'    => false,
        ];
    }

    /**
     * Genera un archivo ZIP con todos los documentos de la bóveda clasificados en carpetas.
     */
    public static function generarZipExpediente($expediente): ?string
    {
        $archivos = $expediente->archivos;
        if ($archivos->isEmpty()) {
            return null;
        }

        $usuario = $expediente->usuario;
        $nombreEstudiante = $usuario ? "{$usuario->nombre} {$usuario->apellidos}" : "Estudiante_{$expediente->id_expediente}";
        $studentFolder = self::getStudentFolderName($expediente->id_expediente, $nombreEstudiante);

        $tempDir = storage_path('app/temp_zip');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        $zipFileName = "{$studentFolder}_Boveda_Digital.zip";
        $zipFilePath = "{$tempDir}/{$zipFileName}";

        if (file_exists($zipFilePath)) {
            @unlink($zipFilePath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        $archivosAgregados = 0;
        foreach ($archivos as $archivo) {
            $res = self::resolveFilePath($archivo->nombre_servidor);
            if ($res['exists'] && is_readable($res['full_path'])) {
                $subFolder = self::getCategoriaSubfolder($archivo->tipo_documento, $archivo->categoria);
                $nombreEnZip = "{$subFolder}/" . ($archivo->nombre_original ?: basename($res['full_path']));
                $zip->addFile($res['full_path'], $nombreEnZip);
                $archivosAgregados++;
            }
        }

        // Agregar ficha informativa de texto
        $nombreInstitucion = mb_strtoupper(config('cliente.nombre_legal', config('cliente.nombre', 'CEFI')));
        $infoText = "========================================================\n" .
                    "{$nombreInstitucion} - EXPEDIENTE DIGITAL ESTUDIANTIL 360°\n" .
                    "========================================================\n" .
                    "Estudiante: {$nombreEstudiante}\n" .
                    "Identificación: " . ($usuario->cedula ?? $expediente->cedula_residencia ?? 'N/D') . "\n" .
                    "Correo: " . ($usuario->email ?? 'N/D') . "\n" .
                    "Teléfono: " . ($usuario->telefono ?? $expediente->contacto_tel_celular ?? 'N/D') . "\n" .
                    "Grado / Carrera: " . ($expediente->grado_a_matricular ?? 'N/D') . " - " . ($expediente->especialidad_deseada ?? 'N/D') . "\n" .
                    "Estado Formalizado: " . ($expediente->estado ?? 'Pendiente') . "\n" .
                    "Total de Archivos Adjuntos: {$archivosAgregados}\n" .
                    "Fecha de Generación: " . date('d/m/Y H:i:s') . "\n" .
                    "========================================================\n";
        $zip->addFromString("FICHA_EXPEDIENTE_{$expediente->id_expediente}.txt", $infoText);

        $zip->close();

        return ($archivosAgregados > 0 && file_exists($zipFilePath)) ? $zipFilePath : null;
    }

    /**
     * Elimina físicamente la carpeta del estudiante y todos sus archivos del disco.
     */
    public static function eliminarCarpetaExpediente($expediente): bool
    {
        $usuario = $expediente->usuario;
        $nombreEstudiante = $usuario ? "{$usuario->nombre} {$usuario->apellidos}" : "Estudiante_{$expediente->id_expediente}";
        $folderName = self::getStudentFolderName($expediente->id_expediente, $nombreEstudiante);
        $fullPath = self::getBasePath() . DIRECTORY_SEPARATOR . $folderName;

        return self::eliminarDirectorioRecursivo($fullPath);
    }

    /**
     * Elimina recursivamente un directorio y todos sus subdirectorios/archivos.
     */
    private static function eliminarDirectorioRecursivo(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $items = @scandir($dir);
        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $itemPath = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($itemPath)) {
                self::eliminarDirectorioRecursivo($itemPath);
            } else {
                @unlink($itemPath);
            }
        }

        return @rmdir($dir);
    }
}
