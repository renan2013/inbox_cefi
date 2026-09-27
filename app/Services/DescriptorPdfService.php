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
    public $verde_unela = [95, 178, 48];
    public $oscuro_unela = [30, 41, 59];
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
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(120, 5, $this->toPdf($this->codigo . ' - ' . $this->materia), 0, 0, 'L');
        $this->Cell(0, 5, $this->toPdf('Universidad Evangélica de las Américas'), 0, 1, 'R');
        $this->SetDrawColor(226, 232, 240);
        $this->SetLineWidth(0.3);
        $this->Line(12, 16, 198, 16);
        $this->Ln(4);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(148, 163, 184);
        $this->Cell(100, 10, $this->toPdf('Documento Oficial de Plan de Estudios - ' . config('cliente.nombre', 'CEFI')), 0, 0, 'L');
        $this->Cell(0, 10, $this->toPdf('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'R');
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

        $this->Ln(4);
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor($this->gris_claro[0], $this->gris_claro[1], $this->gris_claro[2]);
        $this->SetTextColor($this->oscuro_unela[0], $this->oscuro_unela[1], $this->oscuro_unela[2]);

        $this->Cell(186, 7, $this->toPdf("  $num. " . mb_strtoupper($titulo, 'UTF-8')), 0, 1, 'L', true);

        // Borde lateral verde
        $y = $this->GetY() - 7;
        $this->SetDrawColor($this->verde_unela[0], $this->verde_unela[1], $this->verde_unela[2]);
        $this->SetLineWidth(1.2);
        $this->Line(12, $y, 12, $y + 7);
        $this->SetLineWidth(0.2);

        $this->Ln(3);
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

        $w_col = 186 / $max_cols;
        $this->SetDrawColor(203, 213, 225);

        foreach ($rows[2] as $idx => $row_html) {
            preg_match_all('/<(td|th).*?>(.*?)<\/\1>/is', $row_html, $cols);
            $is_th = (strpos($row_html, '<th') !== false || $idx === 0);

            $this->SetFont('Arial', $is_th ? 'B' : '', 8.5);
            if ($is_th) {
                $this->SetFillColor(241, 245, 249);
            }

            $h_row = 6;
            foreach ($cols[2] as $cell_text) {
                $clean = trim(strip_tags(str_replace('&nbsp;', ' ', $cell_text)));
                $this->Cell($w_col, $h_row, $this->toPdf(mb_substr($clean, 0, 40, 'UTF-8')), 1, 0, 'L', $is_th);
            }
            $this->Ln($h_row);
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

        $pdf = new DescriptorPDF();
        $pdf->codigo = $plan->codigo ?? '';
        $pdf->materia = $plan->materia ?? '';
        $pdf->programa = $plan->nombre_programa ?? '';
        $pdf->facultad = $plan->facultad ?: ($plan->facultad_programa ?? '');

        $pdf->AliasNbPages();
        $pdf->SetMargins(12, 12, 12);
        $pdf->AddPage();

        // Logo oficial del cliente
        $logo_cliente = public_path(ltrim(config('cliente.logo_url', 'imgs/logo.png'), '/'));
        if (!file_exists($logo_cliente)) {
            $logo_cliente = public_path('imgs/logo.png');
        }
        if (!file_exists($logo_cliente)) {
            $logo_cliente = public_path('imgs/logo_unela_color.png');
        }
        if (file_exists($logo_cliente)) {
            $pdf->Image($logo_cliente, 80, 15, 50);
        }

        $pdf->SetY(38);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(186, 6, $pdf->toPdf('UNIVERSIDAD EVANGÉLICA DE LAS AMÉRICAS'), 0, 1, 'C');

        if (!empty($pdf->facultad)) {
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor(71, 85, 105);
            $pdf->Cell(186, 5, $pdf->toPdf($pdf->facultad), 0, 1, 'C');
        }

        $pdf->SetFont('Arial', 'B', 10.5);
        $pdf->SetTextColor(95, 178, 48);
        $pdf->Cell(186, 5, $pdf->toPdf($pdf->programa), 0, 1, 'C');

        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(186, 5, $pdf->toPdf('Descriptor Oficial del Curso'), 0, 1, 'C');

        $pdf->Ln(4);

        // Tabla Ficha Técnica
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.2);

        $ficha = [
            ['Código', $plan->codigo ?? ''],
            ['Nombre del Curso', $plan->materia ?? ''],
            ['Créditos', $plan->creditos ?: '4'],
            ['Duración', $plan->duracion ?: '15 semanas'],
            ['Distribución horas por semana', $plan->distribucion_horas ?: '12 horas (3 teóricas, 1 práctica, 8 independientes)'],
            ['Modalidad', $plan->modalidad ?: 'Virtual'],
            ['Naturaleza', $plan->naturaleza ?: 'Teórico-práctica'],
            ['Ubicación', $plan->cuatrimestre ?? ''],
            ['Requisitos', $plan->requisitos ?: 'No tiene'],
            ['Correquisitos', $plan->correquisitos ?: 'No tiene'],
            ['Nivel', $plan->nivel ?: ($plan->categoria ?? 'Maestría')],
            ['Profesor', $plan->profesor ?: '--'],
        ];

        foreach ($ficha as $row) {
            $pdf->SetFont('Arial', 'B', 8.5);
            $pdf->SetFillColor(248, 250, 252);
            $pdf->SetTextColor(51, 65, 85);
            $pdf->Cell(60, 5.5, $pdf->toPdf("  " . $row[0]), 1, 0, 'L', true);

            $pdf->SetFont('Arial', '', 8.5);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->Cell(126, 5.5, $pdf->toPdf("  " . $row[1]), 1, 1, 'L');
        }

        // Secciones Curriculares I a XI
        $pdf->renderSeccion('I', 'Descripción del curso', $plan->descripcion_curso ?? '');
        $pdf->renderSeccion('II', 'Objetivos generales', $plan->objetivo_general ?? '');
        $pdf->renderSeccion('III', 'Objetivos específicos', $plan->objetivos_especificos ?? '');
        $pdf->renderSeccion('IV', 'Contenidos temáticos', $plan->contenidos_tematicos ?? '');
        $pdf->renderSeccion('V', 'Metodología de enseñanza', $plan->metodologia_ensenanza ?? '');
        $pdf->renderSeccion('VI', 'Estrategias de aprendizaje', $plan->estrategias_aprendizaje ?? '');
        $pdf->renderSeccion('VII', 'Evaluación de los aprendizajes', $plan->evaluacion_aprendizajes ?? '');
        $pdf->renderSeccion('VIII', 'Recursos didácticos', $plan->recursos_didacticos ?? '');
        $pdf->renderSeccion('IX', 'Cronograma', $plan->cronograma ?? '');
        $pdf->renderSeccion('X', 'Las guías de evaluación', $plan->guias_evaluacion ?? '');
        $pdf->renderSeccion('XI', 'Bibliografía', $plan->bibliografia ?? '');

        // Guardar copia
        $dir = public_path('uploads/descriptores_cursos');
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }
        $ruta_disco = $dir . "/descriptor_curso_{$id_plan}.pdf";
        @$pdf->Output('F', $ruta_disco);

        $filename = "descriptor_curso_{$id_plan}.pdf";

        if ($dest === 'F') {
            return $ruta_disco;
        }

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', ($dest === 'D' ? 'attachment' : 'inline') . '; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }
}
