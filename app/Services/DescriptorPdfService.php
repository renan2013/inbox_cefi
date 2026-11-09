<?php

namespace App\Services;

require_once __DIR__ . '/FPDF/fpdf.php';

use FPDF;
use Illuminate\Support\Facades\DB;

class DescriptorPDF extends FPDF
{
    public $codigo;
    public $materia;
    public $programa;
    public $facultad;
    public $azul_cefi = [11, 61, 112];
    public $verde_cefi = [95, 178, 48];
    public $oscuro_cefi = [15, 23, 42];
    public $azul_claro = [239, 246, 255];
    public $verde_claro = [240, 253, 244];
    public $gris_claro = [241, 245, 249];

    var $B = 0;
    var $I = 0;
    var $U = 0;
    var $HREF = '';
    var $list_level = 0;
    var $list_item_count = [];
    var $list_types = [];
    var $list_margins = [];
    var $current_text_color = null;
    var $current_bg_color = null;
    var $current_align = 'L';
    var $span_stack = [];
    var $initial_margin_write = 12;
    var $initial_font_size_write = 9;
    var $primera_linea_celda = true;

    function Header()
    {
        if ($this->PageNo() == 1) return;

        $this->SetY(10);
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor($this->azul_cefi[0], $this->azul_cefi[1], $this->azul_cefi[2]);
        $this->Cell(120, 5, $this->toPdf($this->codigo . ' - ' . $this->materia), 0, 0, 'L');
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, 5, $this->toPdf(config('cliente.nombre_legal', config('cliente.nombre', 'CEFI'))), 0, 1, 'R');

        // Línea divisoria bicolor institucional (Azul y Verde CEFI)
        $this->SetLineWidth(0.5);
        $this->SetDrawColor($this->azul_cefi[0], $this->azul_cefi[1], $this->azul_cefi[2]);
        $this->Line(12, 16, 135, 16);
        $this->SetDrawColor($this->verde_cefi[0], $this->verde_cefi[1], $this->verde_cefi[2]);
        $this->Line(135, 16, 198, 16);
        $this->SetLineWidth(0.2);
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-16);
        $y_line = $this->GetY();
        $this->SetLineWidth(0.3);
        $this->SetDrawColor($this->azul_cefi[0], $this->azul_cefi[1], $this->azul_cefi[2]);
        $this->Line(12, $y_line, 135, $y_line);
        $this->SetDrawColor($this->verde_cefi[0], $this->verde_cefi[1], $this->verde_cefi[2]);
        $this->Line(135, $y_line, 198, $y_line);
        $this->SetLineWidth(0.2);

        $y_text = $y_line + 1.5;

        // Izquierda: Documento Oficial
        $this->SetXY(12, $y_text);
        $this->SetFont('Arial', 'I', 7.5);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(65, 8, $this->toPdf('Documento Oficial - ' . config('cliente.nombre', 'CEFI')), 0, 0, 'L');

        // Centro: "Generado por [logo inbox]" pequeño y centrado
        $logo_inbox = public_path('imgs/logo_inbox_color.png');
        if (!file_exists($logo_inbox)) {
            $logo_inbox = dirname(base_path()) . '/imgs/logo_inbox_color.png';
        }

        $txt_gen = $this->toPdf('Generado por');
        $this->SetFont('Arial', 'I', 7.5);
        $w_txt = $this->GetStringWidth($txt_gen);

        if (file_exists($logo_inbox)) {
            $w_logo = 13;
            $gap = 1.5;
            $total_w = $w_txt + $gap + $w_logo;
            $start_x = ($this->w - $total_w) / 2;

            $this->SetXY($start_x, $y_text);
            $this->Cell($w_txt, 8, $txt_gen, 0, 0, 'L');
            @$this->Image($logo_inbox, $start_x + $w_txt + $gap, $y_text + 2.0, $w_logo);
        } else {
            $this->SetXY(($this->w - 40) / 2, $y_text);
            $this->Cell(40, 8, $this->toPdf('Generado por INBOX'), 0, 0, 'C');
        }

        // Derecha: Paginación
        $this->SetXY(150, $y_text);
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor($this->azul_cefi[0], $this->azul_cefi[1], $this->azul_cefi[2]);
        $this->Cell(48, 8, $this->toPdf('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }

    function NbLines($w, $txt): int
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") {
            $nb--;
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
            }
            $l += $cw[$c] ?? 500;
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }

    function CheckPageBreak($h)
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    function toPdf($txt): string
    {
        if ($txt === null) return '';
        $txt = str_replace('&nbsp;', ' ', $txt);
        $txt = str_replace("\xc2\xa0", ' ', $txt);
        $txt = html_entity_decode($txt, ENT_QUOTES, 'UTF-8');
        $special = [
            "\xe2\x80\x93" => "-", "\xe2\x80\x94" => "-", "\xe2\x80\x98" => "'", "\xe2\x80\x99" => "'",
            "\xe2\x80\x9c" => '"', "\xe2\x80\x9d" => '"', "\xe2\x80\xa6" => "...", "•" => "-", "●" => "-"
        ];
        $txt = strtr($txt, $special);
        return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
    }

    function renderSeccion($num, $titulo, $contenido)
    {
        if (empty(trim(strip_tags($contenido ?? '')))) return;

        $this->CheckPageBreak(18);
        $this->Ln(4);

        $y = $this->GetY();
        // Fondo suave azul institucional con borde sutil
        $this->SetFillColor($this->azul_claro[0], $this->azul_claro[1], $this->azul_claro[2]);
        $this->SetDrawColor(219, 234, 254);
        $this->SetLineWidth(0.2);
        $this->Rect(12, $y, 186, 7.5, 'DF');

        // Acento lateral bicolor (Verde CEFI + Azul Marino CEFI)
        $this->SetFillColor($this->verde_cefi[0], $this->verde_cefi[1], $this->verde_cefi[2]);
        $this->Rect(12, $y, 2.5, 7.5, 'F');
        $this->SetFillColor($this->azul_cefi[0], $this->azul_cefi[1], $this->azul_cefi[2]);
        $this->Rect(14.5, $y, 1.0, 7.5, 'F');

        // Texto del encabezado de sección en Azul Marino CEFI
        $this->SetXY(17, $y);
        $this->SetFont('Arial', 'B', 9.5);
        $this->SetTextColor($this->azul_cefi[0], $this->azul_cefi[1], $this->azul_cefi[2]);
        $this->Cell(181, 7.5, $this->toPdf("$num. " . mb_strtoupper($titulo, 'UTF-8')), 0, 1, 'L', false);

        $this->Ln(2.5);
        $this->SetFont('Arial', '', 9.5);
        $this->SetTextColor(30, 41, 59);

        // Si contiene tablas
        if (strpos((string)$contenido, '<table') !== false) {
            $parts = preg_split('/(<table.*?>.*?<\/table>)/is', (string)$contenido, -1, PREG_SPLIT_DELIM_CAPTURE);
            foreach ($parts as $part) {
                if (preg_match('/<table.*?>.*?<\/table>/is', $part)) {
                    $this->renderTableSimple($part);
                } else {
                    $clean = $this->cleanHtml($part);
                    if (!empty($clean)) {
                        $this->MultiCell(186, 5, $this->toPdf($clean), 0, 'L');
                    }
                }
            }
        } else {
            $clean = $this->cleanHtml($contenido);
            $this->MultiCell(186, 5, $this->toPdf($clean), 0, 'L');
        }
    }

    function cleanHtml($html)
    {
        $html = str_replace('&nbsp;', ' ', $html);
        $html = str_replace("\xc2\xa0", ' ', $html);
        $html = str_ireplace(['<br>', '<br/>', '<br />', '</p>', '</li>', '</div>'], "\n", $html);
        $html = str_ireplace('<li>', '- ', $html);
        $html = strip_tags($html);
        return trim(preg_replace('/\n{3,}/', "\n\n", $html));
    }

    function renderTableSimple($html)
    {
        preg_match_all('/<tr(.*?)>(.*?)<\/tr>/is', $html, $rows);
        if (empty($rows[2])) return;

        $max_cols = 0;
        foreach ($rows[2] as $row_html) {
            preg_match_all('/<(td|th).*?>(.*?)<\/\1>/is', $row_html, $cols);
            $max_cols = max($max_cols, count($cols[2]));
        }
        if ($max_cols == 0) return;

        // Proporciones óptimas según número de columnas
        if ($max_cols === 4) {
            $col_widths = [24, 66, 62, 34]; // Ideal para Cronograma (Semana, Actividades, Tareas, Modalidad)
        } elseif ($max_cols === 3) {
            $col_widths = [50, 96, 40];
        } elseif ($max_cols === 2) {
            $col_widths = [136, 50];
        } else {
            $w_col = 186 / $max_cols;
            $col_widths = array_fill(0, $max_cols, $w_col);
        }

        foreach ($rows[2] as $idx => $row_html) {
            preg_match_all('/<(td|th).*?>(.*?)<\/\1>/is', $row_html, $cols);
            $is_th = (strpos($row_html, '<th') !== false || $idx === 0);

            $this->SetFont('Arial', $is_th ? 'B' : '', 8.5);

            // Calcular el alto dinámico de la fila según la celda con más líneas
            $max_lines = 1;
            $cell_texts = [];
            for ($c = 0; $c < $max_cols; $c++) {
                $raw = $cols[2][$c] ?? '';
                $clean = trim(strip_tags(str_replace(['&nbsp;', '<br>', '<br/>', '<br />'], [' ', "\n", "\n", "\n"], $raw)));
                $pdf_txt = $this->toPdf($clean);
                $cell_texts[$c] = $pdf_txt;
                $w_c = $col_widths[$c] ?? (186 / $max_cols);
                $lines = $this->NbLines($w_c - 3, $pdf_txt);
                if ($lines > $max_lines) {
                    $max_lines = $lines;
                }
            }

            $line_h = $is_th ? 6.5 : 4.8;
            $h_row = ($max_lines === 1) ? ($is_th ? 7.0 : 6.0) : ($max_lines * $line_h + 2.0);

            $this->CheckPageBreak($h_row);

            $x_start = 12;
            $y_start = $this->GetY();

            for ($c = 0; $c < $max_cols; $c++) {
                $w_c = $col_widths[$c] ?? (186 / $max_cols);
                $txt = $cell_texts[$c];

                // Fondo y bordes de la celda
                if ($is_th) {
                    $this->SetFillColor($this->azul_cefi[0], $this->azul_cefi[1], $this->azul_cefi[2]);
                    $this->SetDrawColor($this->azul_cefi[0], $this->azul_cefi[1], $this->azul_cefi[2]);
                    $this->SetTextColor(255, 255, 255);
                } else {
                    if ($idx % 2 === 0) {
                        $this->SetFillColor(245, 249, 255);
                    } else {
                        $this->SetFillColor(255, 255, 255);
                    }
                    $this->SetDrawColor(203, 213, 225);
                    $this->SetTextColor(30, 41, 59);
                }

                $this->Rect($x_start, $y_start, $w_c, $h_row, 'DF');

                // Línea inferior verde institucional bajo el encabezado de la tabla
                if ($is_th) {
                    $this->SetDrawColor($this->verde_cefi[0], $this->verde_cefi[1], $this->verde_cefi[2]);
                    $this->SetLineWidth(0.6);
                    $this->Line($x_start, $y_start + $h_row, $x_start + $w_c, $y_start + $h_row);
                    $this->SetLineWidth(0.2);
                }

                // Imprimir texto con soporte multilínea
                $pad_y = ($max_lines === 1) ? (($h_row - $line_h) / 2) : 1.0;
                $this->SetXY($x_start + 1.5, $y_start + $pad_y);
                $align = ($max_cols === 4 && $c === 0 && !$is_th) ? 'C' : 'L';
                $this->MultiCell($w_c - 3, $line_h, $txt, 0, $align);

                $x_start += $w_c;
            }

            $this->SetY($y_start + $h_row);
        }
        $this->Ln(2);
    }
}

class DescriptorPdfService
{
    /**
     * Genera el Descriptor Oficial de Curso en PDF.
     *
     * @param int $id_plan ID del plan de estudios
     * @param string $dest 'I' inline, 'D' download, 'F' save
     * @return mixed Response o string ruta
     */
    public static function generar(int $id_plan, string $dest = 'I')
    {
        $plan = DB::table('plan_estudios as p')
            ->leftJoin('programas as pr', 'p.id_programa', '=', 'pr.id_programa')
            ->select('p.*', 'pr.nombre_programa', 'pr.categoria as facultad_programa')
            ->where('p.id_plan', $id_plan)
            ->first();

        if (!$plan) {
            abort(404, 'Plan de estudios no encontrado.');
        }

        $prop = function ($key, $default = '') use ($plan) {
            return (isset($plan->$key) && $plan->$key !== '' && $plan->$key !== null) ? $plan->$key : $default;
        };

        // Cargar sílabo de respaldo si algún campo del plan está vacío
        $silabo = null;
        try {
            $silabo = DB::table('silabos')->where('id_plan', $id_plan)->orderBy('id_silabo', 'desc')->first();
        } catch (\Throwable $e) {
            $silabo = null;
        }

        $pdf = new DescriptorPDF();
        $pdf->codigo = (string)$prop('codigo', '');
        $pdf->materia = (string)$prop('materia', '');
        $pdf->programa = (string)$prop('nombre_programa', '');
        $pdf->facultad = (string)$prop('facultad', $prop('facultad_programa', ''));

        $pdf->AliasNbPages();
        $pdf->SetMargins(12, 12, 12);
        $pdf->AddPage();

        // Logo oficial del cliente (Centrado y ajustado proporcionalmente)
        $logo_cliente = ClienteService::logoPath();
        $ext = strtolower(pathinfo($logo_cliente, PATHINFO_EXTENSION));
        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif']) && file_exists(public_path('imgs/logo.png'))) {
            $logo_cliente = public_path('imgs/logo.png');
        }
        if (file_exists($logo_cliente)) {
            try {
                $imgSize = @getimagesize($logo_cliente);
                $maxW = 100;
                $maxH = 44;
                $imgW = $maxW;
                $imgH = 0;

                if ($imgSize && $imgSize[0] > 0 && $imgSize[1] > 0) {
                    $ratio = $imgSize[0] / $imgSize[1];
                    if ($ratio < ($maxW / $maxH)) {
                        $imgH = $maxH;
                        $imgW = $maxH * $ratio;
                    } else {
                        $imgW = $maxW;
                        $imgH = $maxW / $ratio;
                    }
                } else {
                    $imgH = 44;
                    $imgW = 44;
                }

                $posX = 12 + ((186 - $imgW) / 2);
                $posY = 12 + (($maxH - $imgH) / 2);
                @$pdf->Image($logo_cliente, $posX, $posY, $imgW, $imgH);
            } catch (\Throwable $e) {
                // Continuar si el logo tiene un formato no soportado por FPDF
            }
        }

        $pdf->SetY(59);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor($pdf->azul_cefi[0], $pdf->azul_cefi[1], $pdf->azul_cefi[2]);
        $pdf->Cell(186, 6, $pdf->toPdf(mb_strtoupper(config('cliente.nombre_legal', config('cliente.nombre', 'CEFI')), 'UTF-8')), 0, 1, 'C');

        if (!empty($pdf->facultad)) {
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor(51, 65, 85);
            $pdf->Cell(186, 5, $pdf->toPdf($pdf->facultad), 0, 1, 'C');
        }

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor($pdf->verde_cefi[0], $pdf->verde_cefi[1], $pdf->verde_cefi[2]);
        $pdf->Cell(186, 5.5, $pdf->toPdf($pdf->programa), 0, 1, 'C');

        // Nombre del Curso en letras grandes con espacio superior e inferior ampliado al 100%
        $pdf->Ln(9);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor($pdf->oscuro_cefi[0], $pdf->oscuro_cefi[1], $pdf->oscuro_cefi[2]);
        $pdf->MultiCell(186, 7.5, $pdf->toPdf(mb_strtoupper($pdf->materia, 'UTF-8')), 0, 'C');
        $pdf->Ln(7);

        $pdf->SetFont('Arial', 'BI', 9.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(186, 5, $pdf->toPdf('Programa del Curso'), 0, 1, 'C');

        // Barra divisoria institucional bicolor (Azul Marino CEFI + Verde CEFI)
        $y_div = $pdf->GetY() + 2;
        $pdf->SetLineWidth(0.7);
        $pdf->SetDrawColor($pdf->azul_cefi[0], $pdf->azul_cefi[1], $pdf->azul_cefi[2]);
        $pdf->Line(45, $y_div, 105, $y_div);
        $pdf->SetDrawColor($pdf->verde_cefi[0], $pdf->verde_cefi[1], $pdf->verde_cefi[2]);
        $pdf->Line(105, $y_div, 165, $y_div);
        $pdf->SetLineWidth(0.2);

        $pdf->Ln(6.5);

        // Encabezado Institucional de la Tabla Ficha Técnica
        $y_head = $pdf->GetY();
        $pdf->SetFillColor($pdf->azul_cefi[0], $pdf->azul_cefi[1], $pdf->azul_cefi[2]);
        $pdf->SetDrawColor($pdf->azul_cefi[0], $pdf->azul_cefi[1], $pdf->azul_cefi[2]);
        $pdf->Rect(12, $y_head, 186, 7.5, 'DF');

        // Acento lateral verde en el encabezado de la Ficha Técnica
        $pdf->SetFillColor($pdf->verde_cefi[0], $pdf->verde_cefi[1], $pdf->verde_cefi[2]);
        $pdf->Rect(12, $y_head, 3.0, 7.5, 'F');

        $pdf->SetXY(16, $y_head);
        $pdf->SetFont('Arial', 'B', 9.5);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(182, 7.5, $pdf->toPdf('FICHA TÉCNICA E INFORMACIÓN GENERAL DEL CURSO'), 0, 1, 'L', false);

        $creditos_num = (int)$prop('creditos', 0);

        $ficha = [
            ['Código', (string)$prop('codigo', '')],
            ['Nombre del Curso', (string)$prop('materia', '')],
        ];
        if ($creditos_num > 0) {
            $ficha[] = ['Créditos', (string)$creditos_num];
        }
        $ficha = array_merge($ficha, [
            ['Duración', (string)$prop('duracion', '15 semanas')],
            ['Distribución horas por semana', (string)$prop('distribucion_horas', '12 horas (3 teóricas, 1 práctica, 8 independientes)')],
            ['Modalidad', (string)$prop('modalidad', 'Virtual')],
            ['Naturaleza', (string)$prop('naturaleza', 'Teórico-práctica')],
            ['Ubicación', (string)$prop('cuatrimestre', '')],
            ['Requisitos', (string)$prop('requisitos', 'No tiene')],
            ['Correquisitos', (string)$prop('correquisitos', 'No tiene')],
            ['Nivel', (string)$prop('nivel', $prop('facultad_programa', 'Carrera Técnica'))],
            ['Profesor', (string)$prop('profesor', '--')],
        ]);

        $w_label = 60;
        $w_value = 126;

        foreach ($ficha as $idx => $row) {
            $label_txt = $pdf->toPdf($row[0]);
            $val_txt = $pdf->toPdf($row[1]);

            $pdf->SetFont('Arial', 'B', 9);
            $lines_label = $pdf->NbLines($w_label - 6, $label_txt);

            $pdf->SetFont('Arial', ($idx <= 1) ? 'B' : '', 9);
            $lines_val = $pdf->NbLines($w_value - 6, $val_txt);

            $max_lines = max($lines_label, $lines_val);
            $line_h = 5.0;
            $row_h = ($max_lines === 1) ? 7.2 : ($max_lines * $line_h + 3.0);

            $pdf->CheckPageBreak($row_h);

            $x = 12;
            $y = $pdf->GetY();

            // Celda de etiqueta (columna izquierda con fondo azul claro institucional y borde suave)
            $pdf->SetDrawColor(191, 211, 238);
            $pdf->SetFillColor($pdf->azul_claro[0], $pdf->azul_claro[1], $pdf->azul_claro[2]);
            $pdf->Rect($x, $y, $w_label, $row_h, 'DF');

            // Línea de acento lateral verde/azul en cada fila de la ficha
            $acento = ($idx % 2 === 0) ? $pdf->verde_cefi : $pdf->azul_cefi;
            $pdf->SetFillColor($acento[0], $acento[1], $acento[2]);
            $pdf->Rect($x, $y, 1.2, $row_h, 'F');

            // Celda de valor (columna derecha con cebreado sutil)
            if ($idx % 2 === 1) {
                $pdf->SetFillColor(250, 252, 255);
            } else {
                $pdf->SetFillColor(255, 255, 255);
            }
            $pdf->Rect($x + $w_label, $y, $w_value, $row_h, 'DF');

            // Imprimir texto de la etiqueta (centrado verticalmente si la fila tiene más de 1 línea)
            $pad_label = ($row_h - ($lines_label * $line_h)) / 2;
            $pdf->SetXY($x + 3.5, $y + $pad_label);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor($pdf->azul_cefi[0], $pdf->azul_cefi[1], $pdf->azul_cefi[2]);
            $pdf->MultiCell($w_label - 6, $line_h, $label_txt, 0, 'L');

            // Imprimir texto del valor (con salto automático a segunda línea cuando es largo)
            $pad_val = ($row_h - ($lines_val * $line_h)) / 2;
            $pdf->SetXY($x + $w_label + 3, $y + $pad_val);
            $pdf->SetFont('Arial', ($idx <= 1) ? 'B' : '', 9);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->MultiCell($w_value - 6, $line_h, $val_txt, 0, 'L');

            $pdf->SetY($y + $row_h);
        }

        // Pasar las secciones curriculares a la segunda página para que la Ficha Técnica quede independiente en la primera página
        $secciones = [
            ['I', 'Descripción del curso', (string)$prop('descripcion_curso', $silabo->descripcion ?? '')],
            ['II', 'Objetivos generales', (string)$prop('objetivo_general', $silabo->objetivo_general ?? '')],
            ['III', 'Objetivos específicos', (string)$prop('objetivos_especificos', $silabo->objetivos_especificos ?? '')],
            ['IV', 'Contenidos temáticos', (string)$prop('contenidos_tematicos', $silabo->contenidos ?? '')],
            ['V', 'Metodología de enseñanza', (string)$prop('metodologia_ensenanza', $silabo->metodologia ?? '')],
            ['VI', 'Estrategias de aprendizaje', (string)$prop('estrategias_aprendizaje', $silabo->estrategias_aprendizaje ?? '')],
            ['VII', 'Evaluación de los aprendizajes', (string)$prop('evaluacion_aprendizajes', '')],
            ['VIII', 'Recursos didácticos', (string)$prop('recursos_didacticos', $silabo->recursos_aprendizaje ?? '')],
            ['IX', 'Cronograma', (string)$prop('cronograma', $silabo->cronograma ?? '')],
            ['X', 'Las guías de evaluación', (string)$prop('guias_evaluacion', '')],
            ['XI', 'Bibliografía', (string)$prop('bibliografia', $silabo->bibliografia ?? '')],
        ];

        $tieneSecciones = false;
        foreach ($secciones as $sec) {
            if (!empty(trim(strip_tags($sec[2] ?? '')))) {
                $tieneSecciones = true;
                break;
            }
        }

        if ($tieneSecciones) {
            $pdf->AddPage();
            foreach ($secciones as $sec) {
                $pdf->renderSeccion($sec[0], $sec[1], $sec[2]);
            }
        }

        $filename = "descriptor_curso_{$id_plan}.pdf";
        $pdfContent = $pdf->Output('S');

        try {
            DB::table('plan_estudios')->where('id_plan', $id_plan)->update(['fecha_descriptor_pdf' => now()]);
        } catch (\Throwable $e) {
            // Ignorar si no existe columna
        }

        if ($dest === 'F') {
            $dir = public_path('uploads/descriptores_cursos');
            if (!file_exists($dir)) {
                @mkdir($dir, 0777, true);
            }
            $ruta_disco = $dir . "/{$filename}";
            @file_put_contents($ruta_disco, $pdfContent);
            return $ruta_disco;
        }

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Length', (string)strlen($pdfContent))
            ->header('Content-Disposition', ($dest === 'D' ? 'attachment' : 'inline') . '; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }
}
