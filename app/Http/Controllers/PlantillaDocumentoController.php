<?php

namespace App\Http\Controllers;

use App\Models\PlantillaDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use ZipArchive;

require_once app_path('Services/FPDF/fpdf.php');

class PlantillaDocumentoController extends Controller
{
    /**
     * Convierte un código hexadecimal a RGB.
     */
    private function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return [$r, $g, $b];
    }

    /**
     * Convierte texto UTF-8 a windows-1252 para compatibilidad con FPDF estándar.
     */
    private function cleanUtf8($text)
    {
        $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT', $text);
        if ($converted === false) {
            $converted = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
        }
        return $converted;
    }

    /**
     * Resuelve la ruta física del archivo de imagen de fondo.
     */
    private function resolverRutaFondo($relPath)
    {
        if (empty($relPath)) return null;

        $p1 = public_path($relPath);
        if (file_exists($p1)) return $p1;

        $cleanRel = ltrim($relPath, '/\\');
        $p2 = public_path($cleanRel);
        if (file_exists($p2)) return $p2;

        // Fallback a ruta externa si existe
        $p3 = base_path('../' . $cleanRel);
        if (file_exists($p3)) return $p3;

        $p4 = dirname(base_path()) . '/' . $cleanRel;
        if (file_exists($p4)) return $p4;

        return null;
    }

    /**
     * Lista todas las plantillas configuradas (Automatizaciones).
     */
    public function index()
    {
        $plantillas = PlantillaDocumento::orderBy('id', 'desc')->get();
        return view('plantillas.index', compact('plantillas'));
    }

    /**
     * Formulario interactivo para crear una nueva plantilla.
     */
    public function create()
    {
        return view('plantillas.create');
    }

    /**
     * Almacena una nueva plantilla de documento con su configuración de campos JSON.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ancho_mm' => 'required|numeric',
            'alto_mm' => 'required|numeric',
            'imagen_fondo' => 'required|image|max:8192',
            'campo_label' => 'nullable|array',
            'campo_x' => 'nullable|array',
            'campo_y' => 'nullable|array',
            'campo_size' => 'nullable|array',
            'campo_tipo' => 'nullable|array',
            'campo_color' => 'nullable|array',
            'campo_font' => 'nullable|array',
            'campo_align' => 'nullable|array'
        ]);

        $configuracion = [];
        $labels = $request->input('campo_label', []);
        $xs = $request->input('campo_x', []);
        $ys = $request->input('campo_y', []);
        $sizes = $request->input('campo_size', []);
        $tipos = $request->input('campo_tipo', []);
        $colores = $request->input('campo_color', []);
        $fonts = $request->input('campo_font', []);
        $aligns = $request->input('campo_align', []);

        for ($i = 0; $i < count($labels); $i++) {
            $configuracion[] = [
                'label' => $labels[$i],
                'x' => floatval($xs[$i] ?? 0),
                'y' => floatval($ys[$i] ?? 0),
                'size' => floatval($sizes[$i] ?? 14),
                'tipo' => $tipos[$i] ?? 'dinamico',
                'color' => $colores[$i] ?? '#000000',
                'font' => $fonts[$i] ?? 'Arial',
                'align' => $aligns[$i] ?? 'L'
            ];
        }

        $imagen_path = null;
        if ($request->hasFile('imagen_fondo')) {
            $file = $request->file('imagen_fondo');
            $filename = uniqid('tpl_') . '.' . $file->getClientOriginalExtension();
            $targetDir = public_path('uploads/documentos');
            if (!File::isDirectory($targetDir)) {
                File::makeDirectory($targetDir, 0777, true, true);
            }
            $file->move($targetDir, $filename);
            $imagen_path = 'uploads/documentos/' . $filename;
        }

        PlantillaDocumento::create([
            'nombre' => $request->nombre,
            'ancho_mm' => floatval($request->ancho_mm),
            'alto_mm' => floatval($request->alto_mm),
            'imagen_fondo' => $imagen_path,
            'configuracion_campos' => json_encode($configuracion, JSON_UNESCAPED_UNICODE)
        ]);

        return redirect()->route('plantillas.index')->with('success', 'Plantilla de documento creada con éxito.');
    }

    /**
     * Formulario interactivo para editar plantilla.
     */
    public function edit($id)
    {
        $plantilla = PlantillaDocumento::findOrFail($id);
        $campos = json_decode($plantilla->configuracion_campos, true) ?: [];
        return view('plantillas.edit', compact('plantilla', 'campos'));
    }

    /**
     * Actualiza la plantilla de documento y su estructura JSON.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ancho_mm' => 'required|numeric',
            'alto_mm' => 'required|numeric',
            'imagen_fondo' => 'nullable|image|max:8192',
            'campo_label' => 'nullable|array',
            'campo_x' => 'nullable|array',
            'campo_y' => 'nullable|array',
            'campo_size' => 'nullable|array',
            'campo_tipo' => 'nullable|array',
            'campo_color' => 'nullable|array',
            'campo_font' => 'nullable|array',
            'campo_align' => 'nullable|array'
        ]);

        $plantilla = PlantillaDocumento::findOrFail($id);

        $configuracion = [];
        $labels = $request->input('campo_label', []);
        $xs = $request->input('campo_x', []);
        $ys = $request->input('campo_y', []);
        $sizes = $request->input('campo_size', []);
        $tipos = $request->input('campo_tipo', []);
        $colores = $request->input('campo_color', []);
        $fonts = $request->input('campo_font', []);
        $aligns = $request->input('campo_align', []);

        for ($i = 0; $i < count($labels); $i++) {
            $configuracion[] = [
                'label' => $labels[$i],
                'x' => floatval($xs[$i] ?? 0),
                'y' => floatval($ys[$i] ?? 0),
                'size' => floatval($sizes[$i] ?? 14),
                'tipo' => $tipos[$i] ?? 'dinamico',
                'color' => $colores[$i] ?? '#000000',
                'font' => $fonts[$i] ?? 'Arial',
                'align' => $aligns[$i] ?? 'L'
            ];
        }

        $imagen_path = $plantilla->imagen_fondo;
        if ($request->hasFile('imagen_fondo')) {
            $oldPath = public_path($plantilla->imagen_fondo);
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
            $file = $request->file('imagen_fondo');
            $filename = uniqid('tpl_') . '.' . $file->getClientOriginalExtension();
            $targetDir = public_path('uploads/documentos');
            if (!File::isDirectory($targetDir)) {
                File::makeDirectory($targetDir, 0777, true, true);
            }
            $file->move($targetDir, $filename);
            $imagen_path = 'uploads/documentos/' . $filename;
        }

        $plantilla->update([
            'nombre' => $request->nombre,
            'ancho_mm' => floatval($request->ancho_mm),
            'alto_mm' => floatval($request->alto_mm),
            'imagen_fondo' => $imagen_path,
            'configuracion_campos' => json_encode($configuracion, JSON_UNESCAPED_UNICODE)
        ]);

        return redirect()->route('plantillas.index')->with('success', 'Plantilla de documento actualizada con éxito.');
    }

    /**
     * Elimina una plantilla.
     */
    public function destroy($id)
    {
        $plantilla = PlantillaDocumento::findOrFail($id);
        if ($plantilla->imagen_fondo) {
            $path = public_path($plantilla->imagen_fondo);
            if (File::exists($path)) {
                File::delete($path);
            }
        }
        $plantilla->delete();

        return redirect()->route('plantillas.index')->with('success', 'Plantilla eliminada exitosamente.');
    }

    /**
     * Vista interactiva para preparar y emitir documento (individual o por lotes).
     */
    public function preparar($id)
    {
        $plantilla = PlantillaDocumento::findOrFail($id);
        $campos = json_decode($plantilla->configuracion_campos, true) ?: [];

        return view('plantillas.preparar', compact('plantilla', 'campos'));
    }

    /**
     * Genera un documento individual (PDF o PNG).
     */
    public function generar(Request $request)
    {
        $plantilla_id = $request->input('plantilla_id');
        $plantilla = PlantillaDocumento::findOrFail($plantilla_id);

        $valores_dinamicos = $request->input('campo_valor', []);
        $formato_descarga = $request->input('formato', 'pdf');
        $modo = $request->input('modo', 'download'); // 'download' o 'preview'

        $ruta_fondo = $this->resolverRutaFondo($plantilla->imagen_fondo);
        if (!$ruta_fondo) {
            abort(404, 'La imagen de fondo de la plantilla no se encuentra disponible.');
        }

        $campos = json_decode($plantilla->configuracion_campos, true) ?: [];
        $ancho_mm = floatval($plantilla->ancho_mm ?: 279);
        $alto_mm = floatval($plantilla->alto_mm ?: 216);

        // Si se solicitó PNG
        if ($formato_descarga === 'png') {
            return $this->generarPng($request);
        }

        // Generación de PDF mediante FPDF
        $orientacion = ($ancho_mm >= $alto_mm) ? 'L' : 'P';
        $pdf = new \FPDF($orientacion, 'mm', [$ancho_mm, $alto_mm]);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        // 1. Imagen de fondo
        $pdf->Image($ruta_fondo, 0, 0, $ancho_mm, $alto_mm);

        // 2. Renderizado de campos
        $dinamico_idx = 0;
        foreach ($campos as $campo) {
            $tipo = $campo['tipo'] ?? 'fijo';
            $align = $campo['align'] ?? 'L';
            $size = floatval($campo['size'] ?? 14);
            $font = $campo['font'] ?? 'Arial';
            $x = floatval($campo['x'] ?? 0);
            $y = floatval($campo['y'] ?? 0);
            $color = $campo['color'] ?? '#000000';

            if ($tipo === 'foto' && $request->hasFile('foto_estudiante')) {
                $fotoFile = $request->file('foto_estudiante');
                $dest_w = $size;
                $dest_h = $dest_w * 1.25;
                $pdf->Image($fotoFile->getRealPath(), $x, $y, $dest_w, $dest_h);
                continue;
            }

            if ($tipo === 'qr' || $tipo === 'qr_info') {
                $nombreInst = config('cliente.nombre', 'CEFI');
                $txt_qr = ($tipo === 'qr') ? ($valores_dinamicos[0] ?? $nombreInst) :
                    "Nombre: " . ($valores_dinamicos[0] ?? '') . "\nUniv: " . ($request->input('qr_universidad', $nombreInst)) . "\nPer: " . ($request->input('qr_periodo', '')) . "\nEst: " . ($request->input('qr_estado', ''));
                $url_qr = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($txt_qr);
                $qr_data = @file_get_contents($url_qr);
                if ($qr_data) {
                    $tmp_qr = tempnam(sys_get_temp_dir(), 'qr_');
                    file_put_contents($tmp_qr, $qr_data);
                    $pdf->Image($tmp_qr, $x, $y, $size, $size, 'PNG');
                    @unlink($tmp_qr);
                }
                continue;
            }

            if ($tipo === 'dinamico') {
                $texto = $valores_dinamicos[$dinamico_idx] ?? ($campo['label'] ?? '');
                $dinamico_idx++;
            } else {
                $texto = $campo['label'] ?? '';
            }

            [$r, $g, $b] = $this->hex2rgb($color);
            $pdf->SetTextColor($r, $g, $b);
            $pdf->SetFont($font, 'B', $size);

            $texto_iso = $this->cleanUtf8($texto);
            $texto_width = $pdf->GetStringWidth($texto_iso);

            if ($align === 'C') {
                $draw_x = $x - ($texto_width / 2);
            } elseif ($align === 'R') {
                $draw_x = $x - $texto_width;
            } else {
                $draw_x = $x;
            }

            $pdf->SetXY($draw_x, $y);
            $pdf->Cell($texto_width, $size * 0.35, $texto_iso, 0, 0, 'L');
        }

        $estudiante = $valores_dinamicos[0] ?? 'Documento';
        $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $estudiante);
        $filename = $safe_name . '_' . date('Ymd_His') . '.pdf';

        $disposition = ($modo === 'preview') ? 'inline' : 'attachment';

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $disposition . '; filename="' . $filename . '"');
    }

    /**
     * Genera un archivo PNG de alta resolución.
     */
    public function generarPng(Request $request)
    {
        $plantilla_id = $request->input('plantilla_id');
        $plantilla = PlantillaDocumento::findOrFail($plantilla_id);

        $valores_dinamicos = $request->input('campo_valor', []);
        $ruta_fondo = $this->resolverRutaFondo($plantilla->imagen_fondo);
        if (!$ruta_fondo) {
            abort(404, 'Imagen de fondo no disponible.');
        }

        $info_img = getimagesize($ruta_fondo);
        $img_w_px = $info_img[0];
        $img_h_px = $info_img[1];

        switch ($info_img['mime']) {
            case 'image/jpeg':
                $img_base = imagecreatefromjpeg($ruta_fondo);
                break;
            case 'image/png':
                $img_base = imagecreatefrompng($ruta_fondo);
                break;
            default:
                $img_base = null;
        }

        if (!$img_base) {
            abort(500, 'Error al decodificar la imagen de fondo.');
        }

        $img_png = imagecreatetruecolor($img_w_px, $img_h_px);
        imagecopy($img_png, $img_base, 0, 0, 0, 0, $img_w_px, $img_h_px);
        imagedestroy($img_base);
        imagealphablending($img_png, true);
        imagesavealpha($img_png, true);

        $font_candidates = [
            public_path('fonts/Roboto-Regular.ttf'),
            base_path('../includes/fonts/Roboto-Regular.ttf'),
            'C:\\Windows\\Fonts\\arial.ttf'
        ];
        $font_path = null;
        foreach ($font_candidates as $fc) {
            if (file_exists($fc)) {
                $font_path = $fc;
                break;
            }
        }

        $campos = json_decode($plantilla->configuracion_campos, true) ?: [];
        $doc_w_mm = floatval($plantilla->ancho_mm ?: 279);
        $doc_h_mm = floatval($plantilla->alto_mm ?: ($doc_w_mm * ($img_h_px / $img_w_px)));

        $dinamico_idx = 0;
        foreach ($campos as $campo) {
            $tipo = $campo['tipo'] ?? 'fijo';
            $align = $campo['align'] ?? 'L';
            $size = floatval($campo['size'] ?? 14);
            $x_px = ($campo['x'] * $img_w_px) / $doc_w_mm;
            $y_px = ($campo['y'] * $img_h_px) / $doc_h_mm;

            if ($tipo === 'foto' && $request->hasFile('foto_estudiante')) {
                $f_raw = imagecreatefromstring(file_get_contents($request->file('foto_estudiante')->getRealPath()));
                if ($f_raw) {
                    $fw = imagesx($f_raw);
                    $fh = imagesy($f_raw);
                    $dest_w = ($size * $img_w_px) / $doc_w_mm;
                    $dest_h = $dest_w * 1.25;
                    imagecopyresampled($img_png, $f_raw, $x_px, $y_px, 0, 0, $dest_w, $dest_h, $fw, $fh);
                    imagedestroy($f_raw);
                }
                continue;
            }

            if ($tipo === 'qr' || $tipo === 'qr_info') {
                $nombreInst = config('cliente.nombre', 'CEFI');
                $txt_qr = ($tipo === 'qr') ? ($valores_dinamicos[0] ?? $nombreInst) :
                    "Nombre: " . ($valores_dinamicos[0] ?? '') . "\nUniv: " . ($request->input('qr_universidad', $nombreInst)) . "\nPer: " . ($request->input('qr_periodo', '')) . "\nEst: " . ($request->input('qr_estado', ''));
                $url_qr = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($txt_qr);
                $qr_data = @file_get_contents($url_qr);
                if ($qr_data) {
                    $qr_img = imagecreatefromstring($qr_data);
                    if ($qr_img) {
                        $qr_w = imagesx($qr_img);
                        $dest_w = ($size * $img_w_px) / $doc_w_mm;
                        imagecopyresampled($img_png, $qr_img, $x_px, $y_px, 0, 0, $dest_w, $dest_w, $qr_w, $qr_w);
                        imagedestroy($qr_img);
                    }
                }
                continue;
            }

            if ($tipo === 'dinamico') {
                $texto = $valores_dinamicos[$dinamico_idx] ?? ($campo['label'] ?? '');
                $dinamico_idx++;
            } else {
                $texto = $campo['label'] ?? '';
            }

            [$r, $g, $b] = $this->hex2rgb($campo['color'] ?? '#000000');
            $text_color = imagecolorallocate($img_png, $r, $g, $b);
            $font_size_px = ($size * $img_w_px) / $doc_w_mm * 0.75;

            if ($font_path && function_exists('imagettftext')) {
                $bbox = imagettfbbox($font_size_px, 0, $font_path, $texto);
                $text_w = abs($bbox[4] - $bbox[0]);
                if ($align === 'C') {
                    $draw_x = $x_px - ($text_w / 2);
                } elseif ($align === 'R') {
                    $draw_x = $x_px - $text_w;
                } else {
                    $draw_x = $x_px;
                }
                imagettftext($img_png, $font_size_px, 0, $draw_x, $y_px + $font_size_px, $text_color, $font_path, $texto);
            } else {
                imagestring($img_png, 5, $x_px, $y_px, $texto, $text_color);
            }
        }

        $estudiante = $valores_dinamicos[0] ?? 'Documento';
        $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $estudiante);
        $filename = $safe_name . '_' . date('Ymd_His') . '.png';

        ob_start();
        imagepng($img_png);
        $png_data = ob_get_clean();
        imagedestroy($img_png);

        return response($png_data, 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Genera un lote masivo en archivo ZIP comprimido sin timeouts.
     */
    public function generarLoteZip(Request $request)
    {
        // Ampliar memoria y tiempo para procesamiento masivo
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $plantilla_id = $request->input('plantilla_id');
        $plantilla = PlantillaDocumento::findOrFail($plantilla_id);

        $datos_raw = trim($request->input('datos_lote', ''));
        if (empty($datos_raw)) {
            return back()->with('error', 'No se proporcionaron datos de alumnos o participantes para el lote.');
        }

        $ruta_fondo = $this->resolverRutaFondo($plantilla->imagen_fondo);
        if (!$ruta_fondo) {
            return back()->with('error', 'No se encuentra el fondo de la plantilla.');
        }

        $campos = json_decode($plantilla->configuracion_campos, true) ?: [];
        $ancho_mm = floatval($plantilla->ancho_mm ?: 279);
        $alto_mm = floatval($plantilla->alto_mm ?: 216);

        // Identificar campos dinámicos
        $campos_dinamicos_keys = [];
        foreach ($campos as $k => $c) {
            if (($c['tipo'] ?? 'fijo') === 'dinamico') {
                $campos_dinamicos_keys[] = $k;
            }
        }

        // Fallback inteligente si no hay campos marcados como dinámicos
        if (empty($campos_dinamicos_keys)) {
            foreach ($campos as $k => $c) {
                $lbl = strtolower($c['label'] ?? '');
                if (str_contains($lbl, 'nombre') || str_contains($lbl, 'alumno') || str_contains($lbl, 'estudiante')) {
                    $campos_dinamicos_keys[] = $k;
                    break;
                }
            }
            if (empty($campos_dinamicos_keys) && count($campos) > 0) {
                $campos_dinamicos_keys[] = 0;
            }
        }

        // Detección automática del delimitador
        $primeras_lineas = array_slice(explode("\n", $datos_raw), 0, 5);
        $sample = implode("\n", $primeras_lineas);
        $tabs = substr_count($sample, "\t");
        $semicolons = substr_count($sample, ";");
        $commas = substr_count($sample, ",");

        $delimitador = ";";
        if ($tabs >= $semicolons && $tabs >= $commas && $tabs > 0) {
            $delimitador = "\t";
        } elseif ($semicolons >= $commas && $semicolons > 0) {
            $delimitador = ";";
        } elseif ($commas > 0) {
            $delimitador = ",";
        }

        $lineas = explode("\n", str_replace("\r", "", $datos_raw));
        $lineas = array_filter(array_map('trim', $lineas));

        if (empty($lineas)) {
            return back()->with('error', 'El formato de texto no contiene filas válidas.');
        }

        $zipFileName = 'Lote_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $plantilla->nombre) . '_' . date('Ymd_His') . '.zip';
        $tempZipPath = tempnam(sys_get_temp_dir(), 'zip_lote_');

        $zip = new ZipArchive();
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'No se pudo crear el archivo ZIP temporal en el servidor.');
        }

        $orientacion = ($ancho_mm >= $alto_mm) ? 'L' : 'P';
        $contador = 1;

        foreach ($lineas as $linea) {
            $columnas = array_map('trim', explode($delimitador, $linea));
            if (empty($columnas[0])) continue;

            $pdf = new \FPDF($orientacion, 'mm', [$ancho_mm, $alto_mm]);
            $pdf->SetAutoPageBreak(false);
            $pdf->AddPage();
            $pdf->Image($ruta_fondo, 0, 0, $ancho_mm, $alto_mm);

            $din_idx = 0;
            foreach ($campos as $k => $campo) {
                $tipo = $campo['tipo'] ?? 'fijo';
                $is_dynamic = in_array($k, $campos_dinamicos_keys);
                $size = floatval($campo['size'] ?? 14);
                $font = $campo['font'] ?? 'Arial';
                $x = floatval($campo['x'] ?? 0);
                $y = floatval($campo['y'] ?? 0);
                $align = $campo['align'] ?? 'L';
                $color = $campo['color'] ?? '#000000';

                if ($tipo === 'foto' || $tipo === 'qr' || $tipo === 'qr_info') {
                    continue;
                }

                if ($is_dynamic) {
                    $texto = $columnas[$din_idx] ?? ($campo['label'] ?? '');
                    $din_idx++;
                } else {
                    $texto = $campo['label'] ?? '';
                }

                [$r, $g, $b] = $this->hex2rgb($color);
                $pdf->SetTextColor($r, $g, $b);
                $pdf->SetFont($font, 'B', $size);

                $texto_iso = $this->cleanUtf8($texto);
                $texto_width = $pdf->GetStringWidth($texto_iso);

                if ($align === 'C') {
                    $draw_x = $x - ($texto_width / 2);
                } elseif ($align === 'R') {
                    $draw_x = $x - $texto_width;
                } else {
                    $draw_x = $x;
                }

                $pdf->SetXY($draw_x, $y);
                $pdf->Cell($texto_width, $size * 0.35, $texto_iso, 0, 0, 'L');
            }

            $primaryName = $columnas[0] ?? ('Documento_' . $contador);
            $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $primaryName);
            $item_filename = sprintf('%03d_%s.pdf', $contador, $safe_name);

            $zip->addFromString($item_filename, $pdf->Output('S'));
            $contador++;
        }

        $zip->close();

        return response()->download($tempZipPath, $zipFileName)->deleteFileAfterSend(true);
    }
}
