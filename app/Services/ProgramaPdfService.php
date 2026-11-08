<?php

namespace App\Services;

require_once __DIR__ . '/FPDF/fpdf.php';

use FPDF;
use Illuminate\Support\Facades\DB;

class ProgramaPDF extends FPDF
{
    public string $nombre_programa = '';
    public string $categoria = '';
    public string $duracion = '12 meses';
    public string $modalidad = 'Presencial';
    public ?string $header_path = null;
    public ?string $hero_path = null;
    public ?string $secondary_path = null;
    public ?string $footer_path = null;

    // Paleta de colores del nuevo diseño CEFI
    public array $verde_oscuro_hdr = [24, 74, 44];     // #184a2c (fondo izquierdo header)
    public array $verde_curva_hdr  = [76, 166, 76];    // #4ca64c (acento curva header)
    public array $azul_hdr         = [10, 92, 150];    // #0a5c96 (fondo derecho header y acentos)
    public array $azul_marino      = [8, 41, 71];      // #082947 (bloque descripción del curso)
    public array $verde_lima       = [120, 184, 51];   // #78b833 (cuadro acento título descripción)
    public array $verde_cefi       = [0, 160, 77];     // #00a04d (footer WhatsApp tab y sello)
    public array $teal_tabla       = [45, 106, 128];   // #2d6a80 (tabla plan de estudios)
    public array $gris_texto       = [75, 85, 99];

    function Header()
    {
        $headerH = 38;
        $headerDrawn = false;

        // 1. Si existe imagen_encabezado personalizada o plantilla global, dibujarla
        if (!empty($this->header_path) && file_exists($this->header_path)) {
            try {
                @$this->Image($this->header_path, 0, 0, 210, $headerH);
                $headerDrawn = true;
            } catch (\Throwable $e) {
                $headerDrawn = false;
            }
        }

        if (!$headerDrawn) {
            // Fondo izquierdo verde oscuro (#184a2c)
            $this->SetFillColor($this->verde_oscuro_hdr[0], $this->verde_oscuro_hdr[1], $this->verde_oscuro_hdr[2]);
            $this->Rect(0, 0, 134, $headerH, 'F');

            // Franja de transición verde claro y turquesa (simulando la curva del diseño)
            $this->SetFillColor($this->verde_curva_hdr[0], $this->verde_curva_hdr[1], $this->verde_curva_hdr[2]);
            $this->Rect(130, 0, 8, $headerH, 'F');

            $this->SetFillColor(28, 140, 160);
            $this->Rect(136, 0, 6, $headerH, 'F');

            // Fondo derecho azul institucional (#0a5c96)
            $this->SetFillColor($this->azul_hdr[0], $this->azul_hdr[1], $this->azul_hdr[2]);
            $this->Rect(140, 0, 70, $headerH, 'F');

            // Texto "CENTRO DE ENSEÑANZA Y FORMACION INTEGRAL" a la izquierda de la línea divisoria
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Arial', '', 7.5);
            $this->SetXY(140, 9.5);
            $this->Cell(31, 3.8, $this->toPdf('CENTRO DE'), 0, 1, 'R');
            $this->SetX(140);
            $this->Cell(31, 3.8, $this->toPdf('ENSEÑANZA'), 0, 1, 'R');
            $this->SetX(140);
            $this->Cell(31, 3.8, $this->toPdf('Y FORMACION'), 0, 1, 'R');
            $this->SetX(140);
            $this->Cell(31, 3.8, $this->toPdf('INTEGRAL'), 0, 1, 'R');

            // Línea vertical blanca divisoria
            $this->SetDrawColor(255, 255, 255);
            $this->SetLineWidth(0.35);
            $this->Line(174, 5.5, 174, 32.5);

            // Sello circular CEFI PARAUNIVERSITARIO a la derecha
            $this->drawCefiBadgeRight(191, 19);
        }

        // 2. Textos dinámicos del Programa a la izquierda (sobre el bloque verde oscuro)
        [$prefijoCat, $tituloLimpio] = $this->splitTituloModerno($this->nombre_programa, $this->categoria);

        $this->SetTextColor(255, 255, 255);
        $this->SetXY(7.5, 4.5);
        $this->SetFont('Arial', '', 12.5);
        $this->Cell(122, 5.5, $this->toPdf($prefijoCat), 0, 1, 'L');

        // Título principal grande (ej. "Diseño Gráfico Digital con IA")
        $this->SetXY(7.5, 11.5);
        $lenTitulo = mb_strlen($tituloLimpio, 'UTF-8');
        $fontSizeTitulo = $lenTitulo > 34 ? 15.5 : ($lenTitulo > 26 ? 18.5 : 21);
        $this->SetFont('Arial', 'B', $fontSizeTitulo);
        $this->Cell(124, 9.5, $this->toPdf($tituloLimpio), 0, 1, 'L');

        // Duración y Modalidad en una sola línea debajo
        $durClean = rtrim(trim($this->duracion), '.');
        if ($durClean === '') {
            $durClean = '12 meses';
        }
        $modClean = rtrim(trim($this->modalidad), '.');
        if ($modClean === '') {
            $modClean = 'Presencial';
        }

        $this->SetXY(7.5, 27.5);
        $this->SetFont('Arial', '', 9.5);
        $this->SetTextColor(240, 248, 242);
        $this->Cell(124, 5, $this->toPdf("Duración: {$durClean}  -  Modalidad: {$modClean}"), 0, 1, 'L');

        $this->SetY($headerH);
    }

    function drawCefiBadgeRight(float $cx, float $cy): void
    {
        // Sello blanco con borde verde CEFI
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(0, 160, 77);
        $this->SetLineWidth(0.9);
        $this->Rect($cx - 12, $cy - 12, 24, 24, 'DF');

        // Siglas CEFI
        $this->SetXY($cx - 11, $cy - 8.5);
        $this->SetFont('Arial', 'B', 13);
        $this->SetTextColor(0, 160, 77);
        $this->Cell(22, 6, $this->toPdf('CEFI'), 0, 1, 'C');

        // Subtítulo interno
        $this->SetXY($cx - 11, $cy - 2);
        $this->SetFont('Arial', 'B', 3.8);
        $this->SetTextColor(11, 68, 103);
        $this->Cell(22, 2, $this->toPdf('Centro de Enseñanza'), 0, 1, 'C');
        $this->SetX($cx - 11);
        $this->Cell(22, 2, $this->toPdf('y Formación Integral'), 0, 1, 'C');

        // Cinta PARAUNIVERSITARIO
        $this->SetFillColor(11, 68, 103);
        $this->Rect($cx - 12, $cy + 4.5, 24, 4.8, 'F');
        $this->SetXY($cx - 12, $cy + 5);
        $this->SetFont('Arial', 'B', 4.6);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(24, 3.8, $this->toPdf('PARAUNIVERSITARIO'), 0, 1, 'C');

        $this->SetLineWidth(0.2);
    }

    function splitTituloModerno(string $nombre, string $categoria): array
    {
        $raw = trim($nombre);
        if ($raw === '') {
            return ['Programa en:', 'Formación Profesional'];
        }

        // Detectar prefijos comunes sin importar mayúsculas/minúsculas
        $patrones = [
            '/^t[ée]cnico\s+parauniversitario\s+en[:\s]+(.+)$/iu' => 'Técnico en:',
            '/^carrera\s+t[ée]cnica\s+en[:\s]+(.+)$/iu'           => 'Técnico en:',
            '/^t[ée]cnico\s+en[:\s]+(.+)$/iu'                     => 'Técnico en:',
            '/^diplomado\s+en[:\s]+(.+)$/iu'                      => 'Diplomado en:',
            '/^certificaci[óo]n\s+en[:\s]+(.+)$/iu'               => 'Certificación en:',
            '/^curso\s+libre\s+de[:\s]+(.+)$/iu'                  => 'Curso Libre:',
            '/^curso\s+en[:\s]+(.+)$/iu'                          => 'Curso en:',
            '/^bachillerato\s+en[:\s]+(.+)$/iu'                   => 'Bachillerato en:',
            '/^licenciatura\s+en[:\s]+(.+)$/iu'                   => 'Licenciatura en:',
            '/^maestr[íi]a\s+en[:\s]+(.+)$/iu'                    => 'Maestría en:',
        ];

        foreach ($patrones as $regex => $label) {
            if (preg_match($regex, $raw, $m)) {
                $resto = trim($m[1]);
                // Si viene todo en mayúsculas, convertir a Title Case elegante preservando siglas como IA
                if (mb_strtoupper($resto, 'UTF-8') === $resto) {
                    $resto = $this->formatearTituloElegante($resto);
                }
                return [$label, $resto];
            }
        }

        $catLabel = 'Técnico en:';
        $catLower = mb_strtolower(trim($categoria), 'UTF-8');
        if (str_contains($catLower, 'diplomado')) {
            $catLabel = 'Diplomado en:';
        } elseif (str_contains($catLower, 'curso')) {
            $catLabel = 'Curso Libre:';
        } elseif (str_contains($catLower, 'certificaci')) {
            $catLabel = 'Certificación en:';
        } elseif (str_contains($catLower, 'bachillerato')) {
            $catLabel = 'Bachillerato en:';
        } elseif (str_contains($catLower, 'licenciatura')) {
            $catLabel = 'Licenciatura en:';
        } elseif (str_contains($catLower, 'maestr')) {
            $catLabel = 'Maestría en:';
        }

        $tituloFinal = (mb_strtoupper($raw, 'UTF-8') === $raw) ? $this->formatearTituloElegante($raw) : $raw;
        return [$catLabel, $tituloFinal];
    }

    function formatearTituloElegante(string $txt): string
    {
        $converted = mb_convert_case(mb_strtolower($txt, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        $reemplazos = [
            ' En ' => ' en ',
            ' De ' => ' de ',
            ' Del ' => ' del ',
            ' Con ' => ' con ',
            ' Y ' => ' y ',
            ' Para ' => ' para ',
            ' Ia' => ' IA',
            ' Ti' => ' TI',
            ' Ui' => ' UI',
            ' Ux' => ' UX',
        ];
        return strtr($converted, $reemplazos);
    }

    function drawHeadingWithSquare(float $y, string $titulo, array $squareRgb, array $textRgb, float $fontSize = 16.5): void
    {
        // Cuadro de color pegado al borde izquierdo (x=0, w=7, h=7.5)
        $this->SetFillColor($squareRgb[0], $squareRgb[1], $squareRgb[2]);
        $this->Rect(0, $y, 7, 7.5, 'F');

        // Texto del título al lado del cuadro
        $this->SetXY(9, $y - 0.3);
        $this->SetFont('Arial', 'B', $fontSize);
        $this->SetTextColor($textRgb[0], $textRgb[1], $textRgb[2]);
        $this->Cell(190, 8, $this->toPdf($titulo), 0, 1, 'L');
    }

    function Footer()
    {
        if (!empty($this->footer_path) && file_exists($this->footer_path)) {
            try {
                @$this->Image($this->footer_path, 0, 281, 210, 16);
                return;
            } catch (\Throwable $e) {
                // Fallback a barra dibujada
            }
        }

        // Línea superior sutil del footer
        $this->SetDrawColor(210, 215, 220);
        $this->SetLineWidth(0.3);
        $this->Line(0, 281, 210, 281);

        $y = 285.2;
        $items = [
            ['img' => 'imgs/social/facebook.png',  'icon' => 'f',  'text' => 'CEFI.',           'w' => 19],
            ['img' => 'imgs/social/instagram.png', 'icon' => 'IG', 'text' => 'cefi_cr',         'w' => 22],
            ['img' => 'imgs/social/whatsapp.png',  'icon' => 'WA', 'text' => '+506 8992 9180',  'w' => 31],
            ['img' => 'imgs/social/phone.png',     'icon' => 'T',  'text' => '2711 1010',       'w' => 23],
            ['img' => 'imgs/social/email.png',     'icon' => 'M',  'text' => 'info@ceficr.com', 'w' => 31],
            ['img' => 'imgs/social/youtube.png',   'icon' => 'YT', 'text' => '@ceficr',         'w' => 21],
            ['img' => 'imgs/social/tiktok.png',    'icon' => 'TK', 'text' => '@cefi_cr',        'w' => 21],
        ];

        $x = 6.5;
        foreach ($items as $it) {
            $iconPath = public_path($it['img']);
            $iconDrawn = false;
            if (file_exists($iconPath)) {
                try {
                    @$this->Image($iconPath, $x, $y - 0.4, 6.2, 6.2);
                    $iconDrawn = true;
                } catch (\Throwable $e) {
                    $iconDrawn = false;
                }
            }

            if (!$iconDrawn) {
                $this->SetFillColor(90, 95, 100);
                $this->SetDrawColor(90, 95, 100);
                $this->SetTextColor(255, 255, 255);
                $this->SetFont('Arial', 'B', 6);
                $this->SetXY($x, $y);
                $this->Cell(5.5, 5.5, $this->toPdf($it['icon']), 1, 0, 'C', true);
            }

            $this->SetTextColor(95, 100, 105);
            $this->SetFont('Arial', '', 6.8);
            $this->SetXY($x + 6.8, $y + 0.4);
            $this->Cell($it['w'] - 7, 5, $this->toPdf($it['text']), 0, 0, 'L');

            $x += $it['w'];
        }

        // Pestaña derecha verde CEFI con botón blanco de WhatsApp (igual al nuevo diseño)
        $this->SetFillColor($this->verde_cefi[0], $this->verde_cefi[1], $this->verde_cefi[2]);
        $this->Rect(164, 281.2, 46, 15.8, 'F');

        // Recuadro blanco interno para el WhatsApp destacado
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(255, 255, 255);
        $this->Rect(169.5, 283.5, 35, 9.5, 'DF');

        $waGreenPath = public_path('imgs/social/whatsapp_green.png');
        if (file_exists($waGreenPath)) {
            try {
                @$this->Image($waGreenPath, 171, 285.1, 6.2, 6.2);
            } catch (\Throwable $e) {
                // Continuar
            }
            $this->SetXY(177.8, 285.6);
            $this->SetFont('Arial', 'B', 7.2);
            $this->SetTextColor($this->verde_cefi[0], $this->verde_cefi[1], $this->verde_cefi[2]);
            $this->Cell(25.5, 5.5, $this->toPdf('+506 8992 9180'), 0, 0, 'L');
        } else {
            $this->SetXY(171, 285.5);
            $this->SetFont('Arial', 'B', 7.2);
            $this->SetTextColor($this->verde_cefi[0], $this->verde_cefi[1], $this->verde_cefi[2]);
            $this->Cell(32, 5.5, $this->toPdf('WA +506 8992 9180'), 0, 0, 'C');
        }
    }

    function toPdf($txt): string
    {
        if ($txt === null) {
            return '';
        }
        $txt = str_replace('&nbsp;', ' ', (string)$txt);
        $txt = str_replace("\xc2\xa0", ' ', $txt);
        $txt = html_entity_decode($txt, ENT_QUOTES, 'UTF-8');
        $special = [
            "\xe2\x80\x93" => "-",
            "\xe2\x80\x94" => "-",
            "\xe2\x80\x98" => "'",
            "\xe2\x80\x99" => "'",
            "\xe2\x80\x9c" => '"',
            "\xe2\x80\x9d" => '"',
            "\xe2\x80\xa6" => "...",
            "•" => "\xb7",
            "●" => "\xb7",
            "▪" => "-",
            "₡" => "CRC ",
        ];
        $txt = strtr($txt, $special);
        $txt = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $txt);
        return @mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8') ?: '';
    }

    function cleanText(?string $html): string
    {
        if ($html === null) {
            return '';
        }
        $html = str_replace('&nbsp;', ' ', $html);
        $html = str_replace("\xc2\xa0", ' ', $html);
        $html = str_ireplace(['<br>', '<br/>', '<br />', '</p>', '</li>', '</div>'], "\n", $html);
        $html = str_ireplace('<li>', "• ", $html);
        $html = strip_tags($html);
        $html = str_replace("\r\n", "\n", $html);
        $html = str_replace("\r", "\n", $html);
        return trim(preg_replace('/\n{3,}/', "\n\n", $html));
    }
}

class ProgramaPdfService
{
    /**
     * Genera el PDF Oficial de 3 Páginas de un Programa Académico según el nuevo Diseño Institucional CEFI.
     *
     * @param int $id_programa
     * @param string $dest 'I' inline, 'D' download
     * @return \Illuminate\Http\Response
     */
    public static function generar(int $id_programa, string $dest = 'I')
    {
        \App\Http\Controllers\ProgramaController::ensureProgramasColumns();

        $programa = DB::table('programas')
            ->where('id_programa', $id_programa)
            ->first();

        if (!$programa) {
            abort(404, 'Programa académico no encontrado.');
        }

        $materias = DB::table('plan_estudios')
            ->where('id_programa', $id_programa)
            ->orderBy('id_plan', 'asc')
            ->get();

        $pdf = new ProgramaPDF('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->AliasNbPages();
        $pdf->SetMargins(9, 10, 9);

        $pdf->nombre_programa = (string)($programa->nombre_programa ?? '');
        $pdf->categoria = (string)($programa->categoria ?? '');
        $pdf->duracion = (string)($programa->duracion ?? '12 meses');
        $pdf->modalidad = (string)($programa->modalidad ?? 'Presencial');

        // 1. Imagen de Encabezado (personalizada del programa o plantilla global si existe)
        if (!empty($programa->imagen_encabezado) && file_exists(public_path($programa->imagen_encabezado))) {
            $pdf->header_path = public_path($programa->imagen_encabezado);
        } elseif (file_exists(public_path('imgs/cefi_header_programa.png'))) {
            $pdf->header_path = public_path('imgs/cefi_header_programa.png');
        }

        // 2. Imagen Principal (Hero central de Página 1)
        if (!empty($programa->imagen_principal) && file_exists(public_path($programa->imagen_principal))) {
            $pdf->hero_path = public_path($programa->imagen_principal);
        }

        // 3. Imagen Secundaria (Opcional)
        if (!empty($programa->imagen_secundaria) && file_exists(public_path($programa->imagen_secundaria))) {
            $pdf->secondary_path = public_path($programa->imagen_secundaria);
        }

        // 4. Imagen de Footer (personalizada del programa o plantilla global si existe)
        if (!empty($programa->imagen_footer) && file_exists(public_path($programa->imagen_footer))) {
            $pdf->footer_path = public_path($programa->imagen_footer);
        } elseif (file_exists(public_path('imgs/cefi_footer_programa.png'))) {
            $pdf->footer_path = public_path('imgs/cefi_footer_programa.png');
        }

        // =====================================================================
        // PÁGINA 1: HERO IMAGE + DESCRIPCIÓN DEL CURSO + OBJETIVO DEL CURSO
        // =====================================================================
        $pdf->AddPage();

        $currentY = 38;

        // Si el programa tiene Imagen Principal (Hero), se dibuja de borde a borde debajo del encabezado
        if (!empty($pdf->hero_path) && file_exists($pdf->hero_path)) {
            $heroH = 82;
            try {
                @$pdf->Image($pdf->hero_path, 0, $currentY, 210, $heroH);
                $currentY += $heroH;
            } catch (\Throwable $e) {
                // Continuar si el formato de imagen no es soportado por FPDF
            }
        } else {
            // Franja decorativa tecnológica compacta cuando aún no se ha subido imagen_principal
            $heroH = 42;
            $pdf->SetFillColor(18, 30, 48);
            $pdf->Rect(0, $currentY, 210, $heroH, 'F');

            // Cuadrícula decorativa estilo estudio digital
            $pdf->SetDrawColor(35, 55, 80);
            $pdf->SetLineWidth(0.2);
            for ($gx = 15; $gx < 210; $gx += 15) {
                $pdf->Line($gx, $currentY, $gx, $currentY + $heroH);
            }
            for ($gy = $currentY + 10; $gy < $currentY + $heroH; $gy += 10) {
                $pdf->Line(0, $gy, 210, $gy);
            }

            // Tarjetas de íconos decorativos a la derecha
            $pdf->SetFillColor(30, 58, 95);
            $pdf->Rect(142, $currentY + 6, 26, 13, 'F');
            $pdf->Rect(172, $currentY + 6, 26, 13, 'F');
            $pdf->Rect(142, $currentY + 22, 26, 13, 'F');
            $pdf->Rect(172, $currentY + 22, 26, 13, 'F');

            $pdf->SetTextColor(56, 189, 248);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetXY(142, $currentY + 9.5);
            $pdf->Cell(26, 6, $pdf->toPdf('AI'), 0, 0, 'C');
            $pdf->SetXY(172, $currentY + 9.5);
            $pdf->Cell(26, 6, $pdf->toPdf('PRO'), 0, 0, 'C');
            $pdf->SetXY(142, $currentY + 25.5);
            $pdf->Cell(26, 6, $pdf->toPdf('LAB'), 0, 0, 'C');
            $pdf->SetXY(172, $currentY + 25.5);
            $pdf->Cell(26, 6, $pdf->toPdf('CEFI'), 0, 0, 'C');

            $currentY += $heroH;
        }

        // Bloque Azul Marino (#082947): Descripción del curso
        $descripcion = $pdf->cleanText($programa->informacion ?? '');
        if ($descripcion === '') {
            $descripcion = 'Sin descripción registrada para este programa académico.';
        }

        $navyBoxH = (!empty($pdf->hero_path)) ? 86 : 104;
        $pdf->SetFillColor($pdf->azul_marino[0], $pdf->azul_marino[1], $pdf->azul_marino[2]);
        $pdf->Rect(0, $currentY, 210, $navyBoxH, 'F');

        // Título con cuadro verde lima a la izquierda
        $pdf->drawHeadingWithSquare($currentY + 9, 'Descripción del curso:', $pdf->verde_lima, [255, 255, 255], 17);

        // Texto de la descripción en blanco
        $pdf->SetXY(9, $currentY + 21);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(245, 248, 252);
        $pdf->MultiCell(190, 5.2, $pdf->toPdf($descripcion), 0, 'L');

        $currentY += $navyBoxH;

        // Bloque Inferior Fondo Blanco: Objetivo del curso
        $objetivo = $pdf->cleanText($programa->objetivo_curso ?? '');
        if ($objetivo === '') {
            $objetivo = 'Desarrollar en el estudiante las competencias esenciales y avanzadas del programa para responder a las necesidades del entorno profesional actual.';
        }

        $pdf->drawHeadingWithSquare($currentY + 10, 'Objetivo del curso:', $pdf->azul_hdr, $pdf->azul_marino, 17);

        $pdf->SetXY(9, $currentY + 22);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(25, 35, 50);
        $pdf->MultiCell(190, 5.3, $pdf->toPdf($objetivo), 0, 'L');

        // =====================================================================
        // PÁGINA 2: OPCIONES LABORALES Y LO QUE OBTENDRÁS (2 COLUMNAS)
        // =====================================================================
        $pdf->AddPage();

        $colTopY = 46;

        // --- COLUMNA IZQUIERDA: OPCIONES LABORALES ---
        $pdf->drawHeadingWithSquare($colTopY, 'Opciones laborales:', $pdf->verde_lima, $pdf->azul_marino, 15);

        $opcionesTxt = $pdf->cleanText($programa->opciones_laborales ?? ($programa->oferta ?? ''));
        $yLeft = $colTopY + 11;
        if ($opcionesTxt !== '') {
            $linesLeft = explode("\n", $opcionesTxt);
            foreach ($linesLeft as $line) {
                $line = trim($line);
                if ($line === '') {
                    $yLeft += 1.5;
                    continue;
                }
                if ($yLeft > 272) {
                    break;
                }

                $pdf->SetXY(9, $yLeft);
                if (preg_match('/^\d+[\.\-\)]\s+/', $line)) {
                    $pdf->SetFont('Arial', 'B', 9.2);
                    $pdf->SetTextColor($pdf->azul_hdr[0], $pdf->azul_hdr[1], $pdf->azul_hdr[2]);
                    $pdf->MultiCell(90, 4.5, $pdf->toPdf($line), 0, 'L');
                } else {
                    $pdf->SetFont('Arial', '', 8.8);
                    $pdf->SetTextColor(30, 45, 65);
                    $pdf->MultiCell(90, 4.4, $pdf->toPdf($line), 0, 'L');
                }
                $yLeft = $pdf->GetY() + 0.8;
            }
        } else {
            $pdf->SetXY(9, $yLeft);
            $pdf->SetFont('Arial', 'I', 9.5);
            $pdf->SetTextColor(120, 130, 140);
            $pdf->MultiCell(90, 5, $pdf->toPdf('No se han especificado opciones laborales para este programa.'), 0, 'L');
        }

        // --- COLUMNA DERECHA: LO QUE OBTENDRÁS ---
        $pdf->SetFillColor($pdf->azul_hdr[0], $pdf->azul_hdr[1], $pdf->azul_hdr[2]);
        $pdf->Rect(106, $colTopY, 5.5, 7.2, 'F');
        $pdf->SetXY(113.5, $colTopY - 0.3);
        $pdf->SetFont('Arial', 'B', 15);
        $pdf->SetTextColor($pdf->azul_marino[0], $pdf->azul_marino[1], $pdf->azul_marino[2]);
        $pdf->Cell(90, 8, $pdf->toPdf('Lo que obtendrás:'), 0, 1, 'L');

        $obtendrasTxt = $pdf->cleanText($programa->lo_que_obtendras ?? ($programa->perfil ?? ''));
        $yRight = $colTopY + 11;
        if ($obtendrasTxt !== '') {
            $linesRight = explode("\n", $obtendrasTxt);
            foreach ($linesRight as $line) {
                $line = trim($line);
                if ($line === '') {
                    $yRight += 1.5;
                    continue;
                }
                if ($yRight > 272) {
                    break;
                }

                $isBullet = str_starts_with($line, '•') || str_starts_with($line, '-') || str_starts_with($line, '*');
                $isIntro = str_ends_with($line, ':');

                if ($isBullet) {
                    $cleanBullet = ltrim($line, "•-* \t");
                    $pdf->SetXY(106, $yRight);
                    $pdf->SetFont('Arial', 'B', 9);
                    $pdf->SetTextColor($pdf->verde_cefi[0], $pdf->verde_cefi[1], $pdf->verde_cefi[2]);
                    $pdf->Cell(4, 4.4, $pdf->toPdf('•'), 0, 0, 'L');

                    $pdf->SetXY(110.5, $yRight);
                    $pdf->SetFont('Arial', '', 8.8);
                    $pdf->SetTextColor(30, 45, 65);
                    $pdf->MultiCell(91, 4.4, $pdf->toPdf($cleanBullet), 0, 'L');
                } elseif ($isIntro) {
                    $pdf->SetXY(106, $yRight);
                    $pdf->SetFont('Arial', '', 9);
                    $pdf->SetTextColor(30, 45, 65);
                    $pdf->MultiCell(95, 4.5, $pdf->toPdf($line), 0, 'L');
                } else {
                    $pdf->SetXY(106, $yRight + 0.5);
                    $pdf->SetFont('Arial', 'B', 9.2);
                    $pdf->SetTextColor($pdf->azul_hdr[0], $pdf->azul_hdr[1], $pdf->azul_hdr[2]);
                    $pdf->MultiCell(95, 4.6, $pdf->toPdf($line), 0, 'L');
                }
                $yRight = $pdf->GetY() + 0.8;
            }
        } else {
            $pdf->SetXY(106, $yRight);
            $pdf->SetFont('Arial', 'I', 9.5);
            $pdf->SetTextColor(120, 130, 140);
            $pdf->MultiCell(95, 5, $pdf->toPdf('No se han registrado competencias de salida para este programa.'), 0, 'L');
        }

        // =====================================================================
        // PÁGINA 3: DURACIÓN DEL PLAN, PLAN DE ESTUDIOS, DOCENTES Y REQUISITOS
        // =====================================================================
        $pdf->AddPage();

        // 1. Franja Azul Marino (#082947): DURACIÓN DEL PLAN DE ESTUDIOS
        $stripY = 38;
        $stripH = 24;
        $pdf->SetFillColor($pdf->azul_marino[0], $pdf->azul_marino[1], $pdf->azul_marino[2]);
        $pdf->Rect(0, $stripY, 210, $stripH, 'F');

        // Acento verde lima a la izquierda de la franja
        $pdf->SetFillColor($pdf->verde_lima[0], $pdf->verde_lima[1], $pdf->verde_lima[2]);
        $pdf->Rect(0, $stripY + 5, 7, 6.5, 'F');

        $duracionPlan = $pdf->cleanText($programa->duracion_plan_estudios ?? ($programa->detalles_programa ?? ''));
        if ($duracionPlan === '') {
            $duracionPlan = 'El programa tiene una duración de ' . ($pdf->duracion ?: '12 meses') . ', con dos horas y media de clase y dos horas y media de trabajo/estudio en casa por semana.';
        }

        $pdf->SetXY(9, $stripY + 5);
        $pdf->SetFont('Arial', 'B', 12.5);
        $pdf->SetTextColor(255, 255, 255);
        $labelW = $pdf->GetStringWidth($pdf->toPdf('DURACIÓN DEL PLAN DE ESTUDIOS: ')) + 2;
        $pdf->Cell($labelW, 6, $pdf->toPdf('DURACIÓN DEL PLAN DE ESTUDIOS: '), 0, 0, 'L');

        $pdf->SetFont('Arial', '', 10);
        $restW = 192 - $labelW;
        $words = explode(' ', $duracionPlan);
        $firstLine = '';
        $remainWords = [];
        $inFirst = true;
        foreach ($words as $w) {
            if ($inFirst && $pdf->GetStringWidth($pdf->toPdf($firstLine . ($firstLine ? ' ' : '') . $w)) < $restW) {
                $firstLine .= ($firstLine ? ' ' : '') . $w;
            } else {
                $inFirst = false;
                $remainWords[] = $w;
            }
        }
        $pdf->Cell($restW, 6, $pdf->toPdf($firstLine), 0, 1, 'L');
        if (!empty($remainWords)) {
            $pdf->SetXY(9, $stripY + 12);
            $pdf->MultiCell(192, 5, $pdf->toPdf(implode(' ', $remainWords)), 0, 'L');
        }

        // 2. Título PLAN DE ESTUDIOS
        $pdf->SetXY(10, 70);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor($pdf->azul_hdr[0], $pdf->azul_hdr[1], $pdf->azul_hdr[2]);
        $pdf->Cell(190, 7, $pdf->toPdf('PLAN DE ESTUDIOS'), 0, 1, 'C');

        $pdf->SetX(10);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor($pdf->azul_marino[0], $pdf->azul_marino[1], $pdf->azul_marino[2]);
        $pdf->Cell(190, 6, $pdf->toPdf(mb_strtoupper($pdf->nombre_programa, 'UTF-8')), 0, 1, 'C');

        // 3. Tabla de Cursos (Código | Nombre del Curso | Duración | Requisito)
        $tableY = 86;
        $pdf->SetXY(11, $tableY);
        $pdf->SetFillColor($pdf->teal_tabla[0], $pdf->teal_tabla[1], $pdf->teal_tabla[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 10);

        $pdf->Cell(38, 8.5, $pdf->toPdf('Código'), 0, 0, 'C', true);
        $pdf->Cell(78, 8.5, $pdf->toPdf('Nombre del Curso'), 0, 0, 'L', true);
        $pdf->Cell(36, 8.5, $pdf->toPdf('Duración'), 0, 0, 'C', true);
        $pdf->Cell(36, 8.5, $pdf->toPdf('Requisito'), 0, 1, 'C', true);

        $rowY = $tableY + 8.5;
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->SetTextColor($pdf->azul_marino[0], $pdf->azul_marino[1], $pdf->azul_marino[2]);
        $pdf->SetDrawColor(148, 163, 184);
        $pdf->SetLineWidth(0.3);

        if ($materias->count() > 0) {
            foreach ($materias as $mat) {
                if ($rowY > 218) {
                    $pdf->AddPage();
                    $rowY = 48;
                }

                $codigo = (string)($mat->codigo ?? '');
                $nombre = mb_substr((string)($mat->materia ?? ''), 0, 48, 'UTF-8');
                $durCurso = trim((string)($mat->duracion ?? ''));
                if ($durCurso === '') {
                    $durCurso = !empty($mat->cuatrimestre) ? (string)$mat->cuatrimestre : '8 semanas';
                }
                $req = trim((string)($mat->requisitos ?? ''));
                if ($req === '' || strtolower($req) === 'sin requisitos') {
                    $req = 'N/A';
                }

                $pdf->SetXY(11, $rowY);
                $pdf->Cell(38, 10.5, $pdf->toPdf('    ' . $codigo), 0, 0, 'L');
                $pdf->Cell(78, 10.5, $pdf->toPdf($nombre), 0, 0, 'L');
                $pdf->Cell(36, 10.5, $pdf->toPdf($durCurso), 0, 0, 'C');
                $pdf->Cell(36, 10.5, $pdf->toPdf($req), 0, 1, 'L');

                $rowY += 10.5;
                $pdf->Line(11, $rowY, 199, $rowY);
            }
        } else {
            $pdf->SetXY(11, $rowY + 2);
            $pdf->SetFont('Arial', 'I', 9.5);
            $pdf->SetTextColor(120, 130, 140);
            $pdf->Cell(188, 10, $pdf->toPdf('No se han registrado cursos en el plan de estudios de este programa.'), 0, 1, 'C');
            $rowY += 12;
        }

        // 4. Sección Inferior Página 3: Perfil Docente y Requisitos de Ingreso
        $bottomY = max($rowY + 12, 224);
        if ($bottomY > 255) {
            $pdf->AddPage();
            $bottomY = 50;
        }

        $perfilDocentes = $pdf->cleanText($programa->perfil_docentes ?? '');
        if ($perfilDocentes === '') {
            $perfilDocentes = 'Los docentes que participan de este programa poseen un grado académico de licenciatura e ingenierías, especializados en su área profesional.';
        }

        $requisitosIngreso = $pdf->cleanText($programa->requisitos_ingreso ?? '');
        if ($requisitosIngreso === '') {
            $requisitosIngreso = "1- Leer y aceptar las políticas institucionales.\n2- Completar el formulario de matrícula.\n3- Foto tamaño pasaporte física o digital.\n4- Copia de la cédula de identidad.";
        }

        // Columna Izquierda: Perfil de los docentes a cargo del programa
        $pdf->SetFillColor($pdf->verde_lima[0], $pdf->verde_lima[1], $pdf->verde_lima[2]);
        $pdf->Rect(0, $bottomY, 6, 5.5, 'F');
        $pdf->SetXY(9, $bottomY);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor($pdf->azul_marino[0], $pdf->azul_marino[1], $pdf->azul_marino[2]);
        $pdf->Cell(90, 5.5, $pdf->toPdf('Perfil de los docentes a cargo del programa:'), 0, 1, 'L');

        $pdf->SetXY(9, $bottomY + 7);
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->SetTextColor($pdf->gris_texto[0], $pdf->gris_texto[1], $pdf->gris_texto[2]);
        $pdf->MultiCell(90, 4.8, $pdf->toPdf($perfilDocentes), 0, 'L');

        // Columna Derecha: Requisitos de ingreso
        $pdf->SetFillColor($pdf->azul_hdr[0], $pdf->azul_hdr[1], $pdf->azul_hdr[2]);
        $pdf->Rect(106, $bottomY, 4.5, 5.5, 'F');
        $pdf->SetXY(112.5, $bottomY);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor($pdf->azul_marino[0], $pdf->azul_marino[1], $pdf->azul_marino[2]);
        $pdf->Cell(88, 5.5, $pdf->toPdf('Requisitos de ingreso:'), 0, 1, 'L');

        $pdf->SetXY(112.5, $bottomY + 7);
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->SetTextColor($pdf->gris_texto[0], $pdf->gris_texto[1], $pdf->gris_texto[2]);
        $pdf->MultiCell(88, 4.8, $pdf->toPdf($requisitosIngreso), 0, 'L');

        $filename = "programa_cefi_{$id_programa}.pdf";
        $pdfContent = $pdf->Output('S');

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Length', (string)strlen($pdfContent))
            ->header('Content-Disposition', ($dest === 'D' ? 'attachment' : 'inline') . '; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }
}
