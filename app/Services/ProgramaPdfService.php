<?php

namespace App\Services;

require_once __DIR__ . '/FPDF/fpdf.php';

use FPDF;
use Illuminate\Support\Facades\DB;

class ProgramaPDF extends FPDF
{
    public string $nombre_programa = '';
    public string $categoria = '';
    public array $verde_cefi = [95, 178, 48];
    public array $oscuro_cefi = [30, 41, 59];
    public array $gris_claro = [241, 245, 249];

    function Header()
    {
        if ($this->PageNo() == 1) {
            return;
        }

        $this->SetY(10);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(120, 5, $this->toPdf($this->nombre_programa . ($this->categoria ? ' (' . $this->categoria . ')' : '')), 0, 0, 'L');
        $this->Cell(0, 5, $this->toPdf(config('cliente.nombre_legal', config('cliente.nombre', 'CEFI'))), 0, 1, 'R');
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
        $this->Cell(120, 10, $this->toPdf('Programa Académico Oficial - ' . config('cliente.nombre', 'CEFI')), 0, 0, 'L');
        $this->Cell(0, 10, $this->toPdf('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'R');
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
            "\xe2\x80\x93" => "-", "\xe2\x80\x94" => "-", "\xe2\x80\x98" => "'", "\xe2\x80\x99" => "'",
            "\xe2\x80\x9c" => '"', "\xe2\x80\x9d" => '"', "\xe2\x80\xa6" => "...", "•" => "-", "●" => "-",
            "₡" => "CRC "
        ];
        $txt = strtr($txt, $special);
        return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
    }

    function cleanHtml($html): string
    {
        $html = str_replace('&nbsp;', ' ', (string)$html);
        $html = str_replace("\xc2\xa0", ' ', $html);
        $html = str_ireplace(['<br>', '<br/>', '<br />', '</p>', '</li>', '</div>'], "\n", $html);
        $html = str_ireplace('<li>', '- ', $html);
        $html = strip_tags($html);
        return trim(preg_replace('/\n{3,}/', "\n\n", $html));
    }

    function renderSeccion(string $num, string $titulo, ?string $contenido): void
    {
        $clean = $this->cleanHtml($contenido ?? '');
        if ($clean === '') {
            return;
        }

        $this->Ln(4);
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor($this->gris_claro[0], $this->gris_claro[1], $this->gris_claro[2]);
        $this->SetTextColor($this->oscuro_cefi[0], $this->oscuro_cefi[1], $this->oscuro_cefi[2]);

        $this->Cell(186, 7, $this->toPdf("  {$num}. " . mb_strtoupper($titulo, 'UTF-8')), 0, 1, 'L', true);

        $y = $this->GetY() - 7;
        $this->SetDrawColor($this->verde_cefi[0], $this->verde_cefi[1], $this->verde_cefi[2]);
        $this->SetLineWidth(1.2);
        $this->Line(12, $y, 12, $y + 7);
        $this->SetLineWidth(0.2);

        $this->Ln(2.5);
        $this->SetFont('Arial', '', 9.5);
        $this->SetTextColor(30, 41, 59);
        $this->MultiCell(186, 5, $this->toPdf($clean), 0, 'L');
    }
}

class ProgramaPdfService
{
    /**
     * Genera el PDF Oficial de un Programa Académico y su Plan de Estudios.
     *
     * @param int $id_programa
     * @param string $dest 'I' inline, 'D' download
     * @return \Illuminate\Http\Response
     */
    public static function generar(int $id_programa, string $dest = 'I')
    {
        $programa = DB::table('programas')
            ->where('id_programa', $id_programa)
            ->first();

        if (!$programa) {
            abort(404, 'Programa académico no encontrado.');
        }

        $materias = DB::table('plan_estudios')
            ->where('id_programa', $id_programa)
            ->orderBy('cuatrimestre', 'asc')
            ->orderBy('id_plan', 'asc')
            ->get();

        $totalMaterias = $materias->count();
        $totalCreditos = $materias->sum(fn($m) => (int)($m->creditos ?? 0));

        $pdf = new ProgramaPDF();
        $pdf->nombre_programa = (string)($programa->nombre_programa ?? '');
        $pdf->categoria = (string)($programa->categoria ?? '');

        $pdf->AliasNbPages();
        $pdf->SetMargins(12, 12, 12);
        $pdf->AddPage();

        // Logo oficial del cliente
        $logo_cliente = ClienteService::logoPath();
        $ext = strtolower(pathinfo($logo_cliente, PATHINFO_EXTENSION));
        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif']) && file_exists(public_path('imgs/logo.png'))) {
            $logo_cliente = public_path('imgs/logo.png');
        }
        if (file_exists($logo_cliente)) {
            try {
                $imgSize = @getimagesize($logo_cliente);
                $maxW = 50;
                $maxH = 22;
                $imgW = $maxW;
                $imgH = 22;

                if ($imgSize && $imgSize[0] > 0 && $imgSize[1] > 0) {
                    $ratio = $imgSize[0] / $imgSize[1];
                    if ($ratio < ($maxW / $maxH)) {
                        $imgH = $maxH;
                        $imgW = $maxH * $ratio;
                    } else {
                        $imgW = $maxW;
                        $imgH = $maxW / $ratio;
                    }
                }

                $posX = 12 + ((186 - $imgW) / 2);
                $posY = 14 + (($maxH - $imgH) / 2);
                @$pdf->Image($logo_cliente, $posX, $posY, $imgW, $imgH);
            } catch (\Throwable $e) {
                // Continuar si el logo no es compatible con FPDF
            }
        }

        $pdf->SetY(38);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(186, 6, $pdf->toPdf(mb_strtoupper(config('cliente.nombre_legal', config('cliente.nombre', 'CEFI')), 'UTF-8')), 0, 1, 'C');

        if (!empty($pdf->categoria)) {
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor(71, 85, 105);
            $pdf->Cell(186, 5, $pdf->toPdf(mb_strtoupper($pdf->categoria, 'UTF-8')), 0, 1, 'C');
        }

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(95, 178, 48);
        $pdf->Cell(186, 6, $pdf->toPdf($pdf->nombre_programa), 0, 1, 'C');

        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(186, 5, $pdf->toPdf('Ficha Oficial del Programa Académico y Plan de Estudios'), 0, 1, 'C');

        $pdf->Ln(4);

        // Tabla de Datos Generales y Costos
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.2);

        $ficha = [
            ['Programa Académico', $pdf->nombre_programa, 'Nivel / Categoría', $pdf->categoria ?: 'General'],
            ['Total de Materias', $totalMaterias . ' asignaturas', 'Total de Créditos', (string)$totalCreditos . ' créditos'],
            ['Costo por Materia', 'CRC ' . number_format((float)($programa->costo_materia ?? 0), 2), 'Costo de Matrícula', 'CRC ' . number_format((float)($programa->costo_matricula ?? 0), 2)],
            ['Costo de Biblioteca', 'CRC ' . number_format((float)($programa->costo_biblioteca ?? 0), 2), 'Inscripción Única', 'CRC ' . number_format((float)($programa->costo_inscripcion_unica ?? 0), 2)],
        ];

        foreach ($ficha as $row) {
            $pdf->SetFont('Arial', 'B', 8.5);
            $pdf->SetFillColor(248, 250, 252);
            $pdf->SetTextColor(51, 65, 85);
            $pdf->Cell(42, 6, $pdf->toPdf('  ' . $row[0]), 1, 0, 'L', true);

            $pdf->SetFont('Arial', '', 8.5);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->Cell(51, 6, $pdf->toPdf('  ' . mb_substr($row[1], 0, 32, 'UTF-8')), 1, 0, 'L');

            $pdf->SetFont('Arial', 'B', 8.5);
            $pdf->SetFillColor(248, 250, 252);
            $pdf->SetTextColor(51, 65, 85);
            $pdf->Cell(42, 6, $pdf->toPdf('  ' . $row[2]), 1, 0, 'L', true);

            $pdf->SetFont('Arial', '', 8.5);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->Cell(51, 6, $pdf->toPdf('  ' . mb_substr($row[3], 0, 32, 'UTF-8')), 1, 1, 'L');
        }

        // Secciones descriptivas del programa
        $pdf->renderSeccion('I', 'Información General del Programa', $programa->informacion ?? '');
        $pdf->renderSeccion('II', 'Perfil Profesional del Egresado', $programa->perfil ?? '');
        $pdf->renderSeccion('III', 'Detalles del Plan y Requisitos', $programa->detalles_programa ?? ($programa->requisitos_ingreso ?? ''));

        // Malla Curricular / Plan de Estudios
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(241, 245, 249);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(186, 7, $pdf->toPdf('  MALLA CURRICULAR / PLAN DE ESTUDIOS OFICIAL'), 0, 1, 'L', true);

        $y = $pdf->GetY() - 7;
        $pdf->SetDrawColor(95, 178, 48);
        $pdf->SetLineWidth(1.2);
        $pdf->Line(12, $y, 12, $y + 7);
        $pdf->SetLineWidth(0.2);
        $pdf->Ln(2.5);

        if ($materias->count() > 0) {
            // Encabezado de tabla
            $pdf->SetDrawColor(203, 213, 225);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(30, 41, 59);
            $pdf->SetTextColor(255, 255, 255);

            $pdf->Cell(24, 6.5, $pdf->toPdf('  CÓDIGO'), 1, 0, 'L', true);
            $pdf->Cell(74, 6.5, $pdf->toPdf('  NOMBRE DE LA MATERIA / CURSO'), 1, 0, 'L', true);
            $pdf->Cell(34, 6.5, $pdf->toPdf('CUATRIMESTRE'), 1, 0, 'C', true);
            $pdf->Cell(16, 6.5, $pdf->toPdf('CRÉD.'), 1, 0, 'C', true);
            $pdf->Cell(38, 6.5, $pdf->toPdf('REQUISITOS'), 1, 1, 'C', true);

            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(15, 23, 42);

            foreach ($materias as $idx => $mat) {
                $fill = ($idx % 2 === 1);
                if ($fill) {
                    $pdf->SetFillColor(248, 250, 252);
                } else {
                    $pdf->SetFillColor(255, 255, 255);
                }

                $codigo = (string)($mat->codigo ?? '');
                $nombre = mb_substr((string)($mat->materia ?? ''), 0, 45, 'UTF-8');
                $cuatri = mb_substr((string)($mat->cuatrimestre ?? ''), 0, 20, 'UTF-8');
                $cred = (string)($mat->creditos ?? 0);
                $req = mb_substr((string)(!empty($mat->requisitos) ? $mat->requisitos : 'Sin requisitos'), 0, 22, 'UTF-8');

                $pdf->Cell(24, 6, $pdf->toPdf('  ' . $codigo), 1, 0, 'L', $fill);
                $pdf->Cell(74, 6, $pdf->toPdf('  ' . $nombre), 1, 0, 'L', $fill);
                $pdf->Cell(34, 6, $pdf->toPdf($cuatri), 1, 0, 'C', $fill);
                $pdf->Cell(16, 6, $pdf->toPdf($cred), 1, 0, 'C', $fill);
                $pdf->Cell(38, 6, $pdf->toPdf($req), 1, 1, 'C', $fill);
            }

            // Fila resumen
            $pdf->SetFont('Arial', 'B', 8.5);
            $pdf->SetFillColor(241, 245, 249);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->Cell(132, 6.5, $pdf->toPdf("  TOTAL DEL PROGRAMA ({$totalMaterias} materias)  "), 1, 0, 'R', true);
            $pdf->Cell(16, 6.5, $pdf->toPdf((string)$totalCreditos), 1, 0, 'C', true);
            $pdf->Cell(38, 6.5, '', 1, 1, 'C', true);
        } else {
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->Cell(186, 6, $pdf->toPdf('No hay materias registradas en este programa académico.'), 0, 1, 'L');
        }

        // Normas de Netiqueta si existen
        if (!empty($programa->normas_netiqueta)) {
            $pdf->renderSeccion('IV', 'Normas de Netiqueta y Convivencia Virtual', $programa->normas_netiqueta);
        }

        $filename = "programa_academico_{$id_programa}.pdf";
        $pdfContent = $pdf->Output('S');

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Length', (string)strlen($pdfContent))
            ->header('Content-Disposition', ($dest === 'D' ? 'attachment' : 'inline') . '; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }
}

