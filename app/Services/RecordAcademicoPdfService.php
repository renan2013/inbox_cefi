<?php

namespace App\Services;

require_once __DIR__ . '/FPDF/fpdf.php';

use FPDF;

class RecordPDF extends FPDF
{
    function Header()
    {
        // Logo oficial del cliente
        $logoPath = ClienteService::logoPath();
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 15, 12, 38);
        }

        // Títulos institucionales
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(16, 102, 173);
        $this->SetXY(58, 12);
        $this->Cell(137, 6, $this->toPdf(mb_strtoupper(config('cliente.nombre_legal', config('cliente.nombre', 'CEFI')))), 0, 1, 'L');

        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(100, 116, 139);
        $this->SetX(58);
        $this->Cell(137, 5, $this->toPdf("DEPARTAMENTO DE REGISTRO ACADÉMICO Y CONTROL CURRICULAR"), 0, 1, 'L');

        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(30, 41, 59);
        $this->SetX(58);
        $this->Cell(137, 6, $this->toPdf("CERTIFICACIÓN OFICIAL DE RÉCORD ACADÉMICO"), 0, 1, 'L');

        // Línea divisoria
        $this->SetDrawColor(16, 102, 173);
        $this->SetLineWidth(0.6);
        $this->Line(15, 33, 195, 33);
        $this->Ln(7);
    }

    function Footer()
    {
        $this->SetY(-18);
        $this->SetDrawColor(226, 232, 240);
        $this->SetLineWidth(0.3);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(148, 163, 184);
        $this->Cell(90, 5, $this->toPdf("Documento Oficial Emitido por " . config('cliente.nombre', 'CEFI') . " el " . date('d/m/Y g:i a')), 0, 0, 'L');
        $this->Cell(90, 5, $this->toPdf("Página " . $this->PageNo() . " de {nb}"), 0, 0, 'R');
    }

    public function toPdf($txt): string
    {
        if ($txt === null) return '';
        $txt = str_replace('&nbsp;', ' ', $txt);
        $txt = html_entity_decode($txt, ENT_QUOTES, 'UTF-8');
        return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
    }

    public function RoundedRect($x, $y, $w, $h, $r, $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));

        $xc = $x+$w-$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);

        $xc = $x+$w-$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);

        $xc = $x+$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);

        $xc = $x+$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);

        $this->_out($op);
    }

    public function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }
}

class RecordAcademicoPdfService
{
    public static function generarPdf($expediente, array $cursosData): string
    {
        $usuario = $expediente->usuario;
        $pdf = new RecordPDF('P', 'mm', 'A4');
        $pdf->AliasNbPages();
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 22);
        $pdf->AddPage();

        // --- TARJETA INFORMATIVA DEL ESTUDIANTE ---
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.3);
        $pdf->RoundedRect(15, 36, 180, 28, 2.5, 'DF');

        $nombreCompleto = $usuario ? "{$usuario->nombre} {$usuario->apellidos}" : 'Estudiante';

        $pdf->SetXY(18, 38);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(25, 5, $pdf->toPdf("ESTUDIANTE:"), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(85, 5, $pdf->toPdf(mb_strtoupper($nombreCompleto, 'UTF-8')), 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(28, 5, $pdf->toPdf("IDENTIFICACIÓN:"), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(42, 5, $pdf->toPdf($usuario->cedula ?? $expediente->cedula_residencia ?? $expediente->pasaporte ?? 'N/A'), 0, 1, 'L');

        $pdf->SetX(18);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(25, 5, $pdf->toPdf("PROGRAMA:"), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->SetTextColor(16, 102, 173);
        $pdf->Cell(85, 5, $pdf->toPdf($expediente->especialidad_deseada ?: 'Carrera General'), 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(28, 5, $pdf->toPdf("GRADO:"), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(42, 5, $pdf->toPdf($expediente->grado_a_matricular ?: 'N/A'), 0, 1, 'L');

        $pdf->SetX(18);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(25, 5, $pdf->toPdf("CORREO:"), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(85, 5, $pdf->toPdf($usuario->email ?? 'N/A'), 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(28, 5, $pdf->toPdf("FECHA INGRESO:"), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(15, 23, 42);
        $fechaIngreso = $expediente->fecha_registro ? date('d/m/Y', strtotime($expediente->fecha_registro)) : 'N/A';
        $pdf->Cell(42, 5, $pdf->toPdf($fechaIngreso), 0, 1, 'L');

        // --- RESUMEN KPI ACADÉMICO ---
        $pdf->Ln(6);
        $pdf->SetFillColor(241, 245, 249);
        $pdf->RoundedRect(15, 67, 180, 12, 2, 'DF');
        $pdf->SetXY(18, 70);

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(45, 6, $pdf->toPdf("CRÉDITOS APROBADOS: ") . $cursosData['total_creditos_aprobados'], 0, 0, 'L');
        $pdf->Cell(45, 6, $pdf->toPdf("ASIGNATURAS APROBADAS: ") . $cursosData['cursos_aprobados'], 0, 0, 'L');
        $pdf->Cell(45, 6, $pdf->toPdf("ASIGNATURAS EN CURSO: ") . $cursosData['cursos_en_curso'], 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(16, 102, 173);
        $pdf->Cell(45, 6, $pdf->toPdf("PROMEDIO GPA: ") . number_format($cursosData['promedio_ponderado'], 2) . " / 100", 0, 1, 'R');

        $pdf->Ln(5);

        // --- TABLA DE CALIFICACIONES ---
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetFillColor(16, 102, 173);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor(203, 213, 225);

        $pdf->Cell(18, 7, $pdf->toPdf("CÓDIGO"), 1, 0, 'C', true);
        $pdf->Cell(68, 7, $pdf->toPdf("ASIGNATURA"), 1, 0, 'L', true);
        $pdf->Cell(25, 7, $pdf->toPdf("PERÍODO"), 1, 0, 'C', true);
        $pdf->Cell(14, 7, $pdf->toPdf("CRÉD."), 1, 0, 'C', true);
        $pdf->Cell(18, 7, $pdf->toPdf("NOTA"), 1, 0, 'C', true);
        $pdf->Cell(37, 7, $pdf->toPdf("ESTADO"), 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 8);
        $fill = false;

        foreach ($cursosData['cursos'] as $c) {
            $pdf->SetFillColor($fill ? 248 : 255, $fill ? 250 : 255, $fill ? 252 : 255);
            $pdf->SetTextColor(15, 23, 42);

            $pdf->Cell(18, 6.5, $pdf->toPdf($c['codigo']), 1, 0, 'C', true);
            $pdf->Cell(68, 6.5, $pdf->toPdf(mb_substr($c['materia'], 0, 42, 'UTF-8')), 1, 0, 'L', true);
            $pdf->Cell(25, 6.5, $pdf->toPdf($c['periodo'] ?: 'N/A'), 1, 0, 'C', true);
            $pdf->Cell(14, 6.5, $c['creditos_calc'], 1, 0, 'C', true);

            $notaStr = ($c['calificacion'] !== null && $c['calificacion'] !== '') ? number_format((float)$c['calificacion'], 1) : '-';
            
            // Color de nota
            if ($c['estado_calc'] === 'APROBADO') {
                $pdf->SetTextColor(22, 163, 74); // Verde
            } elseif ($c['estado_calc'] === 'REPROBADO') {
                $pdf->SetTextColor(220, 38, 38); // Rojo
            } else {
                $pdf->SetTextColor(100, 116, 139);
            }
            $pdf->SetFont('Arial', 'B', 8.5);
            $pdf->Cell(18, 6.5, $notaStr, 1, 0, 'C', true);

            $pdf->Cell(37, 6.5, $pdf->toPdf($c['estado_calc']), 1, 1, 'C', true);
            $pdf->SetFont('Arial', '', 8);

            $fill = !$fill;
        }

        // Bloque de Firmas y Sello Institucional
        $pdf->Ln(15);
        $yFirmas = $pdf->GetY();
        if ($yFirmas > 240) {
            $pdf->AddPage();
            $yFirmas = $pdf->GetY() + 10;
        }

        $pdf->SetDrawColor(100, 116, 139);
        $pdf->SetLineWidth(0.4);

        // Firma Registro Académico
        $pdf->Line(25, $yFirmas + 15, 85, $yFirmas + 15);
        $pdf->SetXY(25, $yFirmas + 16);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(60, 4, $pdf->toPdf("Dirección de Registro Académico"), 0, 1, 'C');
        $pdf->SetX(25);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(60, 4, $pdf->toPdf(config('cliente.nombre_legal', config('cliente.nombre', 'CEFI'))), 0, 0, 'C');

        // Sello Secretaría General
        $pdf->Line(125, $yFirmas + 15, 185, $yFirmas + 15);
        $pdf->SetXY(125, $yFirmas + 16);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(60, 4, $pdf->toPdf("Secretaría General / Sello Oficial"), 0, 1, 'C');
        $pdf->SetX(125);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(60, 4, $pdf->toPdf(config('cliente.nombre', 'CEFI') . " - " . config('cliente.direccion', 'Costa Rica')), 0, 0, 'C');

        return $pdf->Output('S');
    }
}
