<?php

namespace App\Services;

require_once __DIR__ . '/FPDF/fpdf.php';

use FPDF;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SilaboPDFEngine extends FPDF
{
    public $links_rubricas = [];
    public $materia;
    public $codigo;
    public $azul_institucional = [16, 102, 173];
    public $azul_zoom = [0, 141, 255];
    public $celeste_claro = [227, 242, 253];
    public $gris_bordes = [230, 230, 230];
    protected $extgstates = array();
    var $widths;
    var $aligns;
    var $B = 0;
    var $I = 0;
    var $U = 0;
    var $curr_fill_rgb = [255, 255, 255];
    var $curr_text_rgb = [0, 0, 0];

    function SetFillColor($r, $g = null, $b = null)
    {
        parent::SetFillColor($r, $g, $b);
        if ($g === null) $this->curr_fill_rgb = [$r, $r, $r];
        else $this->curr_fill_rgb = [$r, $g, $b];
    }

    function SetTextColor($r, $g = null, $b = null)
    {
        parent::SetTextColor($r, $g, $b);
        if ($g === null) $this->curr_text_rgb = [$r, $r, $r];
        else $this->curr_text_rgb = [$r, $g, $b];
    }

    function SetWidths($w)
    {
        $this->widths = $w;
        $this->cMargin = 0.4;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function Header()
    {
        if ($this->PageNo() == 1) return;

        $this->SetY(10);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(120, 5, $this->toPdf($this->codigo . ' - ' . $this->materia), 0, 0, 'L');
        $this->Cell(0, 5, $this->toPdf('Universidad Evangélica de las Américas'), 0, 1, 'R');
        $this->SetDrawColor(220, 220, 220);
        $this->SetLineWidth(0.2);
        $this->Line(10, 16, 200, 16);
        $this->Ln(4);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(100, 10, $this->toPdf('Sílabo Académico Oficial - UNELA'), 0, 0, 'L');
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

    function SectionTitle($title)
    {
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor($this->azul_institucional[0], $this->azul_institucional[1], $this->azul_institucional[2]);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(190, 7, $this->toPdf("  " . $title), 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(2);
    }

    function SectionBody($content)
    {
        if (empty(trim(strip_tags((string)$content)))) {
            $this->SetFont('Arial', 'I', 9.5);
            $this->SetTextColor(100, 116, 139);
            $this->Cell(190, 5, $this->toPdf('No especificado.'), 0, 1, 'L');
            $this->SetTextColor(0, 0, 0);
            return;
        }

        $this->SetFont('Arial', '', 9.5);
        $this->SetTextColor(30, 41, 59);

        // Si contiene tablas HTML
        if (strpos((string)$content, '<table') !== false) {
            $parts = preg_split('/(<table.*?>.*?<\/table>)/is', (string)$content, -1, PREG_SPLIT_DELIM_CAPTURE);
            foreach ($parts as $part) {
                if (preg_match('/<table.*?>.*?<\/table>/is', $part)) {
                    $this->renderHTMLTable($part);
                } else {
                    $clean = $this->cleanHtml($part);
                    if (!empty($clean)) {
                        $this->MultiCell(190, 5, $this->toPdf($clean), 0, 'L');
                    }
                }
            }
        } else {
            $clean = $this->cleanHtml($content);
            $this->MultiCell(190, 5, $this->toPdf($clean), 0, 'L');
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

    function renderHTMLTable($html)
    {
        preg_match_all('/<tr(.*?)>(.*?)<\/tr>/is', $html, $rows);
        if (empty($rows[2])) return;

        $max_cols = 0;
        foreach ($rows[2] as $row_html) {
            preg_match_all('/<(td|th).*?>(.*?)<\/\1>/is', $row_html, $cols);
            $max_cols = max($max_cols, count($cols[2]));
        }
        if ($max_cols == 0) return;

        $w_col = 190 / $max_cols;
        $this->SetDrawColor(203, 213, 225);

        foreach ($rows[2] as $idx => $row_html) {
            preg_match_all('/<(td|th).*?>(.*?)<\/\1>/is', $row_html, $cols);
            $is_th = (strpos($row_html, '<th') !== false || $idx === 0);

            $this->SetFont('Arial', $is_th ? 'B' : '', 8);
            if ($is_th) {
                $this->SetFillColor(240, 244, 248);
            }

            $h_row = 6;
            foreach ($cols[2] as $cell_text) {
                $clean = trim(strip_tags(str_replace('&nbsp;', ' ', $cell_text)));
                $this->Cell($w_col, $h_row, $this->toPdf(mb_substr($clean, 0, 42, 'UTF-8')), 1, 0, 'L', $is_th);
            }
            $this->Ln($h_row);
        }
        $this->Ln(2);
    }
}

class SilaboPdfService
{
    /**
     * Genera y transmite el Sílabo Académico Oficial en PDF.
     *
     * @param int $id_curso ID del curso activo
     * @param int|null $id_plan ID del plan de estudios (opcional)
     * @param string $dest 'I' inline, 'D' download, 'F' save
     * @return mixed
     */
    public static function generar(int $id_curso, ?int $id_plan = null, string $dest = 'I')
    {
        // 1. Obtener curso activo y plan
        $curso = DB::table('cursos_activos as ca')
            ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->join('programas as p', 'pe.id_programa', '=', 'p.id_programa')
            ->leftJoin('usuarios as u', 'ca.id_profesor', '=', 'u.id')
            ->select(
                'ca.*',
                'pe.materia',
                'pe.codigo',
                'pe.creditos',
                'pe.cuatrimestre',
                'pe.requisitos',
                'pe.correquisitos',
                'pe.modalidad as plan_modalidad',
                'pe.duracion as plan_duracion',
                'pe.distribucion_horas as plan_distribucion_horas',
                'pe.naturaleza as plan_naturaleza',
                'pe.nivel as plan_nivel',
                'pe.descripcion_curso as plan_descripcion',
                'pe.objetivo_general as plan_objetivo_general',
                'pe.objetivos_especificos as plan_objetivos_especificos',
                'pe.contenidos_tematicos as plan_contenidos',
                'pe.metodologia_ensenanza as plan_metodologia',
                'pe.estrategias_aprendizaje as plan_estrategias',
                'pe.evaluacion_aprendizajes as plan_evaluacion',
                'pe.recursos_didacticos as plan_recursos',
                'pe.cronograma as plan_cronograma',
                'pe.guias_evaluacion as plan_guias',
                'pe.bibliografia as plan_bibliografia',
                'p.nombre_programa',
                'p.categoria as facultad',
                'p.normas_netiqueta',
                'u.nombre as prof_nombre',
                'u.apellidos as prof_apellidos',
                'u.correo as prof_correo'
            )
            ->where('ca.id_curso_activo', $id_curso)
            ->first();

        if (!$curso) {
            abort(404, 'Curso activo no encontrado.');
        }

        $prof_id = $curso->id_profesor ?? 0;

        // 2. Buscar sílabo activo (cascada: profesor asignado -> maestro 0 -> cualquier sílabo)
        $silabo = DB::table('silabos')
            ->where('id_plan', $curso->id_plan)
            ->orderByRaw('(id_profesor = ?) DESC', [$prof_id])
            ->orderBy('id_silabo', 'desc')
            ->first();

        // 3. Evaluaciones, cronograma y rúbricas
        $evaluaciones = [];
        $cronograma = [];
        $rubricas = [];

        if ($silabo) {
            $evaluaciones = DB::table('silabo_evaluacion')
                ->where('id_silabo', $silabo->id_silabo)
                ->orderBy('id_evaluacion', 'asc')
                ->get()
                ->toArray();

            $cronograma = DB::table('silabo_cronograma')
                ->where('id_silabo', $silabo->id_silabo)
                ->orderByRaw('CAST(semana AS UNSIGNED) ASC')
                ->get()
                ->toArray();

            $rubricas = DB::table('silabo_rubricas')
                ->where('id_silabo', $silabo->id_silabo)
                ->orderBy('codigo', 'asc')
                ->get()
                ->toArray();
        }

        // 4. Instanciar motor PDF
        $pdf = new SilaboPDFEngine();
        $pdf->materia = $curso->materia;
        $pdf->codigo = $curso->codigo;

        $pdf->AliasNbPages();
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        // Franja lateral institucional
        $pdf->SetFillColor($pdf->azul_institucional[0], $pdf->azul_institucional[1], $pdf->azul_institucional[2]);
        $pdf->Rect(0, 0, 8, 297, 'F');

        // Logo oficial
        $logo_unela = public_path('imgs/logo_unela_color.png');
        if (!file_exists($logo_unela)) {
            $logo_unela = dirname(base_path()) . '/imgs/logo_unela_color.png';
        }
        if (file_exists($logo_unela)) {
            $pdf->Image($logo_unela, 15, 12, 45);
        }

        $pdf->SetY(14);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, $pdf->toPdf('SÍLABO ACADÉMICO'), 0, 1, 'R');

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor($pdf->azul_institucional[0], $pdf->azul_institucional[1], $pdf->azul_institucional[2]);
        $pdf->Cell(0, 6, $pdf->toPdf('UNIVERSIDAD EVANGÉLICA DE LAS AMÉRICAS'), 0, 1, 'R');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(70, 70, 70);
        $pdf->Cell(0, 5, $pdf->toPdf($curso->nombre_programa), 0, 1, 'R');

        $pdf->Ln(12);

        // Título del Curso
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(190, 7, $pdf->toPdf($curso->materia . " (" . $curso->codigo . ")"), 0, 1, 'L');
        $pdf->SetDrawColor($pdf->azul_institucional[0], $pdf->azul_institucional[1], $pdf->azul_institucional[2]);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(4);

        // Ficha Técnica
        $nombre_profesor = trim(($curso->titulo_prefijo ?? '') . ' ' . ($curso->prof_nombre ?? '') . ' ' . ($curso->prof_apellidos ?? '') . ' ' . ($curso->titulo_sufijo ?? ''));
        if (empty($nombre_profesor)) $nombre_profesor = 'Sin asignar';

        $ficha = [
            ['Código:', $curso->codigo, 'Créditos:', (string)($curso->creditos ?? '4')],
            ['Periodo / Cuatrimestre:', $curso->periodo ?? 'N/A', 'Modalidad:', $curso->modalidad ?: ($curso->plan_modalidad ?: 'Virtual')],
            ['Profesor / Catedrático:', $nombre_profesor, 'Horario:', $curso->horario ?: 'Asincrónico'],
            ['Requisitos:', $curso->requisitos ?: 'No tiene', 'Correquisitos:', $curso->correquisitos ?: 'No tiene'],
        ];

        $pdf->SetDrawColor(220, 220, 220);
        $pdf->SetLineWidth(0.2);

        foreach ($ficha as $row) {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(245, 247, 250);
            $pdf->Cell(40, 5.5, $pdf->toPdf("  " . $row[0]), 1, 0, 'L', true);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(55, 5.5, $pdf->toPdf("  " . $row[1]), 1, 0, 'L');

            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(40, 5.5, $pdf->toPdf("  " . $row[2]), 1, 0, 'L', true);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(55, 5.5, $pdf->toPdf("  " . $row[3]), 1, 1, 'L');
        }

        // Secciones I a XI
        $pdf->SectionTitle('1. DESCRIPCIÓN DEL CURSO');
        $pdf->SectionBody($silabo->descripcion_curso ?? $curso->plan_descripcion);

        $pdf->SectionTitle('2. OBJETIVO GENERAL');
        $pdf->SectionBody($silabo->objetivo_general ?? $curso->plan_objetivo_general);

        $pdf->SectionTitle('3. OBJETIVOS ESPECÍFICOS');
        $pdf->SectionBody($silabo->objetivos_especificos ?? $curso->plan_objetivos_especificos);

        $pdf->SectionTitle('4. CONTENIDOS TEMÁTICOS');
        $pdf->SectionBody($silabo->contenidos_tematicos ?? $curso->plan_contenidos);

        $pdf->SectionTitle('5. METODOLOGÍA DE ENSEÑANZA');
        $pdf->SectionBody($silabo->metodologia_ensenanza ?? $curso->plan_metodologia);

        $pdf->SectionTitle('6. ESTRATEGIAS DE APRENDIZAJE');
        $pdf->SectionBody($silabo->estrategias_aprendizaje ?? $curso->plan_estrategias);

        // 7. Evaluación
        $pdf->SectionTitle('7. EVALUACIÓN DE LOS APRENDIZAJES');
        if (!empty($evaluaciones)) {
            $pdf->SetFont('Arial', 'B', 8.5);
            $pdf->SetFillColor(240, 244, 248);
            $pdf->Cell(130, 6, $pdf->toPdf('  Rubro de Evaluación'), 1, 0, 'L', true);
            $pdf->Cell(30, 6, $pdf->toPdf('Cantidad'), 1, 0, 'C', true);
            $pdf->Cell(30, 6, $pdf->toPdf('Porcentaje'), 1, 1, 'C', true);

            $pdf->SetFont('Arial', '', 8.5);
            $total_pct = 0;
            foreach ($evaluaciones as $ev) {
                $total_pct += (float)$ev->porcentaje;
                $pdf->Cell(130, 5.5, $pdf->toPdf("  " . $ev->rubro), 1, 0, 'L');
                $pdf->Cell(30, 5.5, (string)($ev->cantidad ?? 1), 1, 0, 'C');
                $pdf->Cell(30, 5.5, (float)$ev->porcentaje . '%', 1, 1, 'C');
            }

            $pdf->SetFont('Arial', 'B', 8.5);
            $pdf->SetFillColor(230, 235, 245);
            $pdf->Cell(160, 6, $pdf->toPdf('  TOTAL'), 1, 0, 'L', true);
            $pdf->Cell(30, 6, $total_pct . '%', 1, 1, 'C', true);
            $pdf->Ln(3);
        } else {
            $pdf->SectionBody($silabo->evaluacion_aprendizajes ?? $curso->plan_evaluacion);
        }

        // 8. Cronograma
        $pdf->SectionTitle('8. CRONOGRAMA DE ACTIVIDADES (15 SEMANAS)');
        if (!empty($cronograma)) {
            $pdf->SetFont('Arial', 'B', 7.5);
            $pdf->SetFillColor(240, 244, 248);
            $pdf->Cell(16, 6, $pdf->toPdf('Sem.'), 1, 0, 'C', true);
            $pdf->Cell(24, 6, $pdf->toPdf('Fecha'), 1, 0, 'C', true);
            $pdf->Cell(75, 6, $pdf->toPdf('Tema / Contenido'), 1, 0, 'L', true);
            $pdf->Cell(50, 6, $pdf->toPdf('Actividades / Tareas'), 1, 0, 'L', true);
            $pdf->Cell(25, 6, $pdf->toPdf('Modalidad'), 1, 1, 'C', true);

            $pdf->SetFont('Arial', '', 7.5);
            foreach ($cronograma as $cr) {
                $sem = "Sem " . $cr->semana;
                $fecha = $cr->fecha ? date('d/m/Y', strtotime($cr->fecha)) : '-';
                $tema = mb_substr(trim(strip_tags($cr->actividad ?? '')), 0, 55, 'UTF-8');
                $tareas = mb_substr(trim(strip_tags($cr->tareas ?? '')), 0, 35, 'UTF-8');
                $mod = $cr->modalidad_trabajo ?: 'Asincrónico';

                $pdf->Cell(16, 5.5, $pdf->toPdf($sem), 1, 0, 'C');
                $pdf->Cell(24, 5.5, $pdf->toPdf($fecha), 1, 0, 'C');
                $pdf->Cell(75, 5.5, $pdf->toPdf(" " . $tema), 1, 0, 'L');
                $pdf->Cell(50, 5.5, $pdf->toPdf(" " . $tareas), 1, 0, 'L');
                $pdf->Cell(25, 5.5, $pdf->toPdf($mod), 1, 1, 'C');
            }
            $pdf->Ln(3);
        } else {
            $pdf->SectionBody($silabo->cronograma ?? $curso->plan_cronograma);
        }

        // 9. Recursos Didácticos
        $pdf->SectionTitle('9. RECURSOS DIDÁCTICOS');
        $pdf->SectionBody($silabo->recursos_didacticos ?? $curso->plan_recursos);

        // 10. Rúbricas
        if (!empty($rubricas)) {
            $pdf->SectionTitle('10. RÚBRICAS DE EVALUACIÓN');
            foreach ($rubricas as $idx => $rub) {
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(190, 6, $pdf->toPdf("  10." . ($idx + 1) . " " . ($rub->nombre ?? 'Rúbrica')), 0, 1, 'L');
                $pdf->Ln(1);
            }
        }

        // 11. Normas de Netiqueta
        if (!empty($curso->normas_netiqueta)) {
            $pdf->SectionTitle('11. NORMAS DE NETIQUETA');
            $pdf->SectionBody($curso->normas_netiqueta);
        }

        // 12. Referencias
        $pdf->SectionTitle('12. REFERENCIAS Y BIBLIOGRAFÍA');
        $pdf->SectionBody($silabo->bibliografia ?? $curso->plan_bibliografia);

        // Guardar copia
        $dir = public_path('uploads/silabos');
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }
        $ruta_disco = $dir . "/silabo_curso_{$id_curso}.pdf";
        @$pdf->Output('F', $ruta_disco);

        $filename = "silabo_curso_{$id_curso}.pdf";

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
