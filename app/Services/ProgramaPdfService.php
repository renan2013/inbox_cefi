<?php

namespace App\Services;

require_once __DIR__ . '/FPDF/fpdf.php';

use FPDF;
use Illuminate\Support\Facades\DB;

class ProgramaPDF extends FPDF
{
    public string $nombre_programa = '';
    public string $categoria = '';
    public string $duracion = '12 meses.';
    public string $modalidad = 'Presencial.';
    public ?string $banner_path = null;
    public ?string $footer_path = null;

    public array $teal_cefi = [48, 108, 130];
    public array $azul_titulo = [0, 96, 156];
    public array $azul_texto = [20, 65, 100];
    public array $amarillo_cefi = [235, 215, 0];
    public array $verde_cefi = [0, 160, 77];
    public array $gris_texto = [85, 90, 95];

    function Header()
    {
        // 1. Fondo del Banner Superior (x=0, y=0, w=210, h=63)
        $bannerDrawn = false;
        if (!empty($this->banner_path) && file_exists($this->banner_path)) {
            try {
                @$this->Image($this->banner_path, 0, 0, 210, 63);
                $bannerDrawn = true;
            } catch (\Throwable $e) {
                $bannerDrawn = false;
            }
        }

        if (!$bannerDrawn) {
            // Banner institucional oscuro/tecnológico por defecto
            $this->SetFillColor(30, 48, 68);
            $this->Rect(0, 0, 210, 63, 'F');

            // Franjas decorativas sutiles estilo moderno
            $this->SetFillColor(38, 65, 90);
            $this->Rect(0, 0, 75, 63, 'F');
            $this->SetFillColor(45, 82, 110);
            $this->Rect(75, 0, 55, 63, 'F');
            $this->SetFillColor(25, 42, 60);
            $this->Rect(130, 0, 80, 63, 'F');
        }

        // 2. Emblema Circular CEFI Parauniversitario (esquina superior izquierda)
        $this->drawCefiBadge(26, 23);

        // 3. Título del Programa a la derecha
        [$lineaSuperior, $lineaPrincipal] = $this->splitTituloPrograma($this->nombre_programa, $this->categoria);

        $this->SetTextColor(255, 255, 255);
        if ($lineaSuperior !== '') {
            $this->SetXY(60, 22);
            $this->SetFont('Arial', 'B', 19);
            $this->Cell(138, 8, $this->toPdf($lineaSuperior), 0, 1, 'R');

            $this->SetXY(52, 31);
            $fontSize = mb_strlen($lineaPrincipal, 'UTF-8') > 26 ? 18 : 22;
            $this->SetFont('Arial', 'B', $fontSize);
            $this->MultiCell(146, 8.5, $this->toPdf($lineaPrincipal), 0, 'R');
        } else {
            $this->SetXY(52, 26);
            $fontSize = mb_strlen($lineaPrincipal, 'UTF-8') > 26 ? 18 : 22;
            $this->SetFont('Arial', 'B', $fontSize);
            $this->MultiCell(146, 9, $this->toPdf($lineaPrincipal), 0, 'R');
        }

        // 4. Duración y Modalidad debajo del título (alineado a la derecha)
        $durTxt = trim($this->duracion) !== '' ? trim($this->duracion) : '12 meses.';
        if (!str_ends_with($durTxt, '.')) {
            $durTxt .= '.';
        }
        $modTxt = trim($this->modalidad) !== '' ? trim($this->modalidad) : 'Presencial.';
        if (!str_ends_with($modTxt, '.')) {
            $modTxt .= '.';
        }

        $this->SetFont('Arial', '', 9.5);
        $this->SetTextColor(240, 244, 248);
        $this->SetXY(110, 50);
        $this->Cell(88, 4.5, $this->toPdf('Duración: ' . $durTxt), 0, 1, 'R');
        $this->SetXY(110, 54.8);
        $this->Cell(88, 4.5, $this->toPdf('Modalidad: ' . $modTxt), 0, 1, 'R');

        // 5. Línea divisoria amarilla institucional
        $this->SetFillColor($this->amarillo_cefi[0], $this->amarillo_cefi[1], $this->amarillo_cefi[2]);
        $this->Rect(0, 63, 210, 1.2, 'F');

        $this->SetY(66);
    }

    function drawCefiBadge(float $cx, float $cy): void
    {
        // Círculo exterior blanco y verde CEFI
        $this->SetFillColor(0, 160, 77);
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(0.8);

        // Fondo rectangular redondeado simulado para el sello institucional
        $this->SetFillColor(255, 255, 255);
        $this->Rect($cx - 17, $cy - 16, 34, 32, 'DF');

        // Borde interior verde/azul CEFI
        $this->SetDrawColor(0, 160, 77);
        $this->SetLineWidth(1.2);
        $this->Rect($cx - 15.5, $cy - 14.5, 31, 29, 'D');

        // Siglas CEFI
        $this->SetXY($cx - 15, $cy - 10);
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(0, 160, 77);
        $this->Cell(30, 8, $this->toPdf('CEFI'), 0, 1, 'C');

        // Subtítulo Centro de Enseñanza y Formación Integral
        $this->SetXY($cx - 15, $cy - 1.5);
        $this->SetFont('Arial', 'B', 5.2);
        $this->SetTextColor(11, 68, 103);
        $this->Cell(30, 2.8, $this->toPdf('Centro de Enseñanza'), 0, 1, 'C');
        $this->SetX($cx - 15);
        $this->Cell(30, 2.8, $this->toPdf('y Formación Integral'), 0, 1, 'C');

        // Franja PARAUNIVERSITARIO
        $this->SetFillColor(11, 68, 103);
        $this->Rect($cx - 15.5, $cy + 6.5, 31, 6, 'F');
        $this->SetXY($cx - 15.5, $cy + 7.2);
        $this->SetFont('Arial', 'B', 6);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(31, 4.5, $this->toPdf('PARAUNIVERSITARIO'), 0, 1, 'C');

        $this->SetLineWidth(0.2);
    }

    function splitTituloPrograma(string $nombre, string $categoria): array
    {
        $clean = mb_strtoupper(trim($nombre), 'UTF-8');
        if ($clean === '') {
            return ['', 'PROGRAMA ACADÉMICO'];
        }

        $prefijos = [
            'TÉCNICO PARAUNIVERSITARIO EN ',
            'TECNICO PARAUNIVERSITARIO EN ',
            'CARRERA TÉCNICA EN ',
            'CARRERA TECNICA EN ',
            'TÉCNICO EN ',
            'TECNICO EN ',
            'DIPLOMADO EN ',
            'BACHILLERATO EN ',
            'LICENCIATURA EN ',
            'MAESTRÍA EN ',
            'MAESTRIA EN ',
            'DOCTORADO EN ',
            'CERTIFICACIÓN EN ',
            'CERTIFICACION EN ',
            'CURSO LIBRE DE ',
            'CURSO DE ',
        ];

        foreach ($prefijos as $pref) {
            if (str_starts_with($clean, $pref)) {
                $resto = trim(mb_substr($clean, mb_strlen($pref, 'UTF-8'), null, 'UTF-8'));
                if ($resto !== '') {
                    return [trim($pref), $resto];
                }
            }
        }

        if ($categoria !== '' && !str_contains($clean, mb_strtoupper($categoria, 'UTF-8'))) {
            return [mb_strtoupper($categoria, 'UTF-8'), $clean];
        }

        return ['', $clean];
    }

    function Footer()
    {
        if (!empty($this->footer_path) && file_exists($this->footer_path)) {
            try {
                @$this->Image($this->footer_path, 0, 280, 210, 17);
                return;
            } catch (\Throwable $e) {
                // Fallback a barra de redes institucional
            }
        }

        $y = 284;
        $this->SetDrawColor(100, 100, 100);
        $this->SetLineWidth(0.3);

        $items = [
            ['icon' => 'f',  'text' => 'CEFI.',           'w' => 22],
            ['icon' => 'IG', 'text' => 'cefi_cr',         'w' => 25],
            ['icon' => 'WA', 'text' => '+506 8992 9180',  'w' => 35],
            ['icon' => 'T',  'text' => '2711 1010',       'w' => 26],
            ['icon' => 'M',  'text' => 'info@ceficr.com', 'w' => 36],
            ['icon' => 'YT', 'text' => '@ceficr',         'w' => 24],
            ['icon' => 'TK', 'text' => '@cefi_cr',        'w' => 24],
        ];

        $x = 9;
        foreach ($items as $it) {
            // Icono redondo/cuadrado
            $this->SetFillColor(90, 90, 90);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Arial', 'B', 6.5);
            $this->SetXY($x, $y);
            $this->Cell(6, 6, $this->toPdf($it['icon']), 1, 0, 'C', true);

            // Texto de red social / contacto
            $this->SetTextColor(95, 95, 95);
            $this->SetFont('Arial', '', 7.5);
            $this->SetXY($x + 6.8, $y + 0.5);
            $this->Cell($it['w'] - 7, 5, $this->toPdf($it['text']), 0, 0, 'L');

            $x += $it['w'];
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
        // Eliminar emojis o caracteres fuera de ISO-8859-1
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
     * Genera el PDF Oficial de 3 Páginas de un Programa Académico según el Modelo Institucional CEFI.
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
        $pdf->SetMargins(10, 10, 10);

        $pdf->nombre_programa = (string)($programa->nombre_programa ?? '');
        $pdf->categoria = (string)($programa->categoria ?? '');
        $pdf->duracion = (string)($programa->duracion ?? '12 meses.');
        $pdf->modalidad = (string)($programa->modalidad ?? 'Presencial.');

        // Resolver imagen de encabezado si fue cargada
        if (!empty($programa->imagen_encabezado) && file_exists(public_path($programa->imagen_encabezado))) {
            $pdf->banner_path = public_path($programa->imagen_encabezado);
        } elseif (!empty($programa->imagen_principal) && file_exists(public_path($programa->imagen_principal))) {
            $pdf->banner_path = public_path($programa->imagen_principal);
        }

        // Resolver imagen de footer si fue cargada
        if (!empty($programa->imagen_footer) && file_exists(public_path($programa->imagen_footer))) {
            $pdf->footer_path = public_path($programa->imagen_footer);
        }

        // =====================================================================
        // PÁGINA 1: DESCRIPCIÓN DEL CURSO Y OBJETIVO CURSO
        // =====================================================================
        $pdf->AddPage();

        $descripcion = $pdf->cleanText($programa->informacion ?? '');
        if ($descripcion === '') {
            $descripcion = 'Sin descripción registrada para este programa académico.';
        }

        $objetivo = $pdf->cleanText($programa->objetivo_curso ?? '');
        if ($objetivo === '') {
            $objetivo = 'Desarrollar en el estudiante las competencias esenciales, técnicas y prácticas requeridas por el entorno profesional actual.';
        }

        // Bloque Azul-Teal para DESCRIPCIÓN DEL CURSO
        $boxStartY = 64.2;
        $boxHeight = 96;
        $pdf->SetFillColor($pdf->teal_cefi[0], $pdf->teal_cefi[1], $pdf->teal_cefi[2]);
        $pdf->Rect(0, $boxStartY, 202, $boxHeight, 'F');

        // Línea amarilla inferior del bloque de descripción
        $pdf->SetFillColor($pdf->amarillo_cefi[0], $pdf->amarillo_cefi[1], $pdf->amarillo_cefi[2]);
        $pdf->Rect(0, $boxStartY + $boxHeight, 120, 1.0, 'F');

        // Título DESCRIPCIÓN DEL CURSO:
        $pdf->SetXY(10, $boxStartY + 10);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(182, 8, $pdf->toPdf('DESCRIPCIÓN DEL CURSO:'), 0, 1, 'L');

        // Texto de Descripción
        $pdf->SetXY(10, $boxStartY + 21);
        $pdf->SetFont('Arial', '', 10.5);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->MultiCell(182, 5.8, $pdf->toPdf($descripcion), 0, 'J');

        // Sección OBJETIVO CURSO:
        $objStartY = $boxStartY + $boxHeight + 16;
        $pdf->SetXY(10, $objStartY);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor($pdf->azul_titulo[0], $pdf->azul_titulo[1], $pdf->azul_titulo[2]);
        $pdf->Cell(186, 8, $pdf->toPdf('OBJETIVO CURSO:'), 0, 1, 'L');

        $pdf->SetXY(10, $objStartY + 10);
        $pdf->SetFont('Arial', '', 10.5);
        $pdf->SetTextColor($pdf->azul_texto[0], $pdf->azul_texto[1], $pdf->azul_texto[2]);
        $pdf->MultiCell(186, 5.8, $pdf->toPdf($objetivo), 0, 'J');

        // =====================================================================
        // PÁGINA 2: OPCIONES LABORALES Y LO QUE OBTENDRÁS (2 COLUMNAS)
        // =====================================================================
        $pdf->AddPage();

        $colTopY = 72;

        // --- COLUMNA IZQUIERDA: OPCIONES LABORALES ---
        $pdf->SetXY(6, $colTopY);
        $pdf->SetFont('Arial', 'B', 15);
        $pdf->SetTextColor($pdf->azul_titulo[0], $pdf->azul_titulo[1], $pdf->azul_titulo[2]);
        $pdf->Cell(92, 7, $pdf->toPdf('OPCIONES LABORALES:'), 0, 1, 'L');

        $opcionesTxt = $pdf->cleanText($programa->opciones_laborales ?? ($programa->oferta ?? ''));
        $yLeft = $colTopY + 9;
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

                $pdf->SetXY(6, $yLeft);
                // Detectar si es título numerado (ej: "1. Agencias...")
                if (preg_match('/^\d+[\.\-\)]\s+/', $line)) {
                    $pdf->SetFont('Arial', 'B', 9.2);
                    $pdf->SetTextColor($pdf->azul_titulo[0], $pdf->azul_titulo[1], $pdf->azul_titulo[2]);
                    $pdf->MultiCell(92, 4.4, $pdf->toPdf($line), 0, 'L');
                } else {
                    $pdf->SetFont('Arial', '', 8.8);
                    $pdf->SetTextColor($pdf->azul_texto[0], $pdf->azul_texto[1], $pdf->azul_texto[2]);
                    $pdf->MultiCell(92, 4.3, $pdf->toPdf($line), 0, 'J');
                }
                $yLeft = $pdf->GetY() + 0.6;
            }
        } else {
            $pdf->SetXY(6, $yLeft);
            $pdf->SetFont('Arial', 'I', 9.5);
            $pdf->SetTextColor(120, 130, 140);
            $pdf->MultiCell(92, 5, $pdf->toPdf('No se han especificado opciones laborales para este programa.'), 0, 'L');
        }

        // --- COLUMNA DERECHA: LO QUE OBTENDRÁS ---
        $pdf->SetXY(106, $colTopY);
        $pdf->SetFont('Arial', 'B', 15);
        $pdf->SetTextColor($pdf->azul_titulo[0], $pdf->azul_titulo[1], $pdf->azul_titulo[2]);
        $pdf->Cell(98, 7, $pdf->toPdf('LO QUE OBTENDRÁS'), 0, 1, 'L');

        $obtendrasTxt = $pdf->cleanText($programa->lo_que_obtendras ?? ($programa->perfil ?? ''));
        $yRight = $colTopY + 9;
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
                    $pdf->SetTextColor($pdf->azul_texto[0], $pdf->azul_texto[1], $pdf->azul_texto[2]);
                    $pdf->Cell(4, 4.3, $pdf->toPdf('•'), 0, 0, 'L');

                    $pdf->SetXY(111, $yRight);
                    $pdf->SetFont('Arial', '', 8.8);
                    $pdf->MultiCell(93, 4.3, $pdf->toPdf($cleanBullet), 0, 'J');
                } elseif ($isIntro) {
                    $pdf->SetXY(106, $yRight);
                    $pdf->SetFont('Arial', '', 9);
                    $pdf->SetTextColor($pdf->azul_texto[0], $pdf->azul_texto[1], $pdf->azul_texto[2]);
                    $pdf->MultiCell(98, 4.4, $pdf->toPdf($line), 0, 'L');
                } else {
                    // Subtítulo de competencia
                    $pdf->SetXY(106, $yRight + 0.5);
                    $pdf->SetFont('Arial', 'B', 9.2);
                    $pdf->SetTextColor($pdf->azul_titulo[0], $pdf->azul_titulo[1], $pdf->azul_titulo[2]);
                    $pdf->MultiCell(98, 4.6, $pdf->toPdf($line), 0, 'L');
                }
                $yRight = $pdf->GetY() + 0.6;
            }
        } else {
            $pdf->SetXY(106, $yRight);
            $pdf->SetFont('Arial', 'I', 9.5);
            $pdf->SetTextColor(120, 130, 140);
            $pdf->MultiCell(98, 5, $pdf->toPdf('No se han registrado competencias de salida para este programa.'), 0, 'L');
        }

        // =====================================================================
        // PÁGINA 3: DURACIÓN DEL PLAN, PLAN DE ESTUDIOS, DOCENTES Y REQUISITOS
        // =====================================================================
        $pdf->AddPage();

        // 1. Franja Azul-Teal: DURACIÓN DEL PLAN DE ESTUDIOS
        $stripY = 64.2;
        $stripH = 23;
        $pdf->SetFillColor($pdf->teal_cefi[0], $pdf->teal_cefi[1], $pdf->teal_cefi[2]);
        $pdf->Rect(0, $stripY, 210, $stripH, 'F');

        $duracionPlan = $pdf->cleanText($programa->duracion_plan_estudios ?? ($programa->detalles_programa ?? ''));
        if ($duracionPlan === '') {
            $duracionPlan = 'El programa tiene una duración de ' . ($pdf->duracion ?: '12 meses') . ', con dos horas y media de clase y dos horas y media de trabajo/estudio en casa por semana.';
        }

        $pdf->SetXY(9, $stripY + 5);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor(255, 255, 255);
        $labelW = $pdf->GetStringWidth($pdf->toPdf('DURACIÓN DEL PLAN DE ESTUDIOS: ')) + 2;
        $pdf->Cell($labelW, 6, $pdf->toPdf('DURACIÓN DEL PLAN DE ESTUDIOS: '), 0, 0, 'L');

        $pdf->SetFont('Arial', '', 10.5);
        $restW = 192 - $labelW;
        // Primera línea junto al título y resto debajo
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
            $pdf->SetXY(9, $stripY + 11.5);
            $pdf->MultiCell(192, 5.2, $pdf->toPdf(implode(' ', $remainWords)), 0, 'L');
        }

        // 2. Título PLAN DE ESTUDIOS
        $pdf->SetXY(10, 94);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor($pdf->azul_titulo[0], $pdf->azul_titulo[1], $pdf->azul_titulo[2]);
        $pdf->Cell(190, 7, $pdf->toPdf('PLAN DE ESTUDIOS'), 0, 1, 'C');

        $pdf->SetX(10);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor($pdf->azul_texto[0], $pdf->azul_texto[1], $pdf->azul_texto[2]);
        $pdf->Cell(190, 6, $pdf->toPdf(mb_strtoupper($pdf->nombre_programa, 'UTF-8')), 0, 1, 'C');

        // 3. Tabla de Cursos (Código | Nombre del Curso | Duración | Requisito)
        $tableY = 111;
        $pdf->SetXY(11, $tableY);
        $pdf->SetFillColor($pdf->teal_cefi[0], $pdf->teal_cefi[1], $pdf->teal_cefi[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 10);

        $pdf->Cell(38, 8.5, $pdf->toPdf('Código'), 0, 0, 'C', true);
        $pdf->Cell(78, 8.5, $pdf->toPdf('Nombre del Curso'), 0, 0, 'L', true);
        $pdf->Cell(36, 8.5, $pdf->toPdf('Duración'), 0, 0, 'C', true);
        $pdf->Cell(36, 8.5, $pdf->toPdf('Requisito'), 0, 1, 'C', true);

        $rowY = $tableY + 8.5;
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->SetTextColor($pdf->azul_texto[0], $pdf->azul_texto[1], $pdf->azul_texto[2]);
        $pdf->SetDrawColor(140, 150, 160);
        $pdf->SetLineWidth(0.3);

        if ($materias->count() > 0) {
            foreach ($materias as $mat) {
                if ($rowY > 220) {
                    $pdf->AddPage();
                    $rowY = 72;
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
        $bottomY = max($rowY + 12, 232);
        if ($bottomY > 258) {
            $pdf->AddPage();
            $bottomY = 75;
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
        $pdf->SetXY(9, $bottomY);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor($pdf->gris_texto[0], $pdf->gris_texto[1], $pdf->gris_texto[2]);
        $pdf->Cell(90, 5.5, $pdf->toPdf('Perfil de los docentes a cargo del programa:'), 0, 1, 'L');

        $pdf->SetXY(9, $bottomY + 7);
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->MultiCell(90, 4.8, $pdf->toPdf($perfilDocentes), 0, 'J');

        // Columna Derecha: Requisitos de ingreso
        $pdf->SetXY(110, $bottomY);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor($pdf->gris_texto[0], $pdf->gris_texto[1], $pdf->gris_texto[2]);
        $pdf->Cell(90, 5.5, $pdf->toPdf('Requisitos de ingreso:'), 0, 1, 'L');

        $pdf->SetXY(110, $bottomY + 7);
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->MultiCell(90, 4.8, $pdf->toPdf($requisitosIngreso), 0, 'L');

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
