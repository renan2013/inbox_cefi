<?php

namespace App\Services;

require_once __DIR__ . '/FPDF/fpdf.php';

use FPDF;
use App\Models\Graduacion;

class DictamenGraduacionPDF extends FPDF
{
    function toPdf($str)
    {
        return mb_convert_encoding($str ?? '', 'ISO-8859-1', 'UTF-8');
    }

    function Header()
    {
        $logoPath = ClienteService::logoPath();
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 15, 12, 35);
        }

        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(16, 102, 173);
        $this->SetXY(55, 12);
        $this->Cell(140, 6, $this->toPdf(mb_strtoupper(config('cliente.nombre_legal', config('cliente.nombre', 'CEFI')))), 0, 1, 'L');

        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(100, 116, 139);
        $this->SetX(55);
        $this->Cell(140, 5, $this->toPdf("DIRECCIÓN DE ASUNTOS ACADÉMICOS Y REGISTRO"), 0, 1, 'L');

        $this->SetFont('Arial', 'I', 8.5);
        $this->SetTextColor(148, 163, 184);
        $this->SetX(55);
        $this->Cell(140, 4, $this->toPdf("Dictamen Oficial de Idoneidad para Graduación"), 0, 1, 'L');

        $this->SetDrawColor(203, 213, 225);
        $this->SetLineWidth(0.5);
        $this->Line(15, 34, 195, 34);
        $this->Ln(12);
    }

    function Footer()
    {
        $this->SetY(-18);
        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(148, 163, 184);
        $texto = "Documento oficial emitido por el Sistema Inbox Online para " . config('cliente.nombre', 'CEFI') . " | " . date('d/m/Y H:i:s');
        $this->Cell(0, 5, $this->toPdf($texto), 0, 0, 'C');
    }
}

class DictamenGraduacionPdfService
{
    public static function generarPdf(Graduacion $graduacion, array $auditoria): string
    {
        $pdf = new DictamenGraduacionPDF('P', 'mm', 'A4');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 22);
        $pdf->AddPage();

        $estudiante = $graduacion->estudiante;
        $programa = $graduacion->programa;

        // Título del Dictamen
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(0, 7, $pdf->toPdf("DICTAMEN OFICIAL DE APROBACIÓN DE GRADO"), 0, 1, 'C');
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(16, 102, 173);
        $convocatoriaStr = "CONVOCATORIA: " . mb_strtoupper($graduacion->convocatoria ?: 'ORDINARIA');
        $pdf->Cell(0, 5, $pdf->toPdf($convocatoriaStr), 0, 1, 'C');
        $pdf->Ln(4);

        // Bloque de Datos del Graduando
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.3);
        $pdf->RoundedRect(15, $pdf->GetY(), 180, 28, 2, 'DF');

        $yInicio = $pdf->GetY() + 3;
        $pdf->SetXY(18, $yInicio);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(45, 4.5, $pdf->toPdf("NOMBRE COMPLETO:"), 0, 0);
        $pdf->SetFont('Arial', 'B', 9.5);
        $pdf->SetTextColor(15, 23, 42);
        $nombreCompleto = $estudiante ? "{$estudiante->nombre} {$estudiante->apellidos}" : "N/D";
        $pdf->Cell(70, 4.5, $pdf->toPdf(mb_strtoupper($nombreCompleto)), 0, 0);

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(25, 4.5, $pdf->toPdf("CÉDULA / ID:"), 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(35, 4.5, $pdf->toPdf($estudiante ? $estudiante->cedula : 'N/D'), 0, 1);

        $pdf->SetX(18);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(45, 4.5, $pdf->toPdf("PROGRAMA / CARRERA:"), 0, 0);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(16, 102, 173);
        $pdf->Cell(125, 4.5, $pdf->toPdf($programa ? $programa->nombre_programa : 'N/D'), 0, 1);

        $pdf->SetX(18);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(45, 4.5, $pdf->toPdf("PROMEDIO PONDERADO:"), 0, 0);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(40, 4.5, $pdf->toPdf(number_format($graduacion->promedio_ponderado_final ?: $auditoria['promedio'], 2) . " / 100"), 0, 0);

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(40, 4.5, $pdf->toPdf("MENCIÓN DE HONOR:"), 0, 0);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(217, 119, 6);
        $pdf->Cell(45, 4.5, $pdf->toPdf($graduacion->mencion_honor ?: 'Ninguna'), 0, 1);

        $pdf->SetY($yInicio + 26);
        $pdf->Ln(4);

        // Tabla de Auditoría de los 4 Requisitos Oficiales
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(0, 6, $pdf->toPdf("AUDITORÍA DE REQUISITOS INSTITUCIONALES PARA GRADUACIÓN"), 0, 1, 'L');

        $pdf->SetFillColor(16, 102, 173);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(60, 6.5, $pdf->toPdf("Requisito Evaluado"), 1, 0, 'L', true);
        $pdf->Cell(80, 6.5, $pdf->toPdf("Detalle y Verificación"), 1, 0, 'L', true);
        $pdf->Cell(40, 6.5, $pdf->toPdf("Dictamen"), 1, 1, 'C', true);

        $requisitos = [
            [
                'nombre' => '1. Plan de Estudios y Créditos',
                'detalle' => "{$auditoria['materias_aprobadas']} de {$auditoria['materias_totales']} materias aprobadas ({$auditoria['creditos_aprobados']} créditos)",
                'cumple' => $graduacion->cumplio_plan_estudios
            ],
            [
                'nombre' => '2. Trabajo Comunal Universitario (TCU)',
                'detalle' => $auditoria['tcu_detalle'] ?: 'Bitácora oficial de horas comunitarias',
                'cumple' => $graduacion->cumplio_tcu
            ],
            [
                'nombre' => '3. Solvencia Financiera',
                'detalle' => $auditoria['finanzas_detalle'] ?: 'Estado de cuenta sin mora y aranceles cancelados',
                'cumple' => $graduacion->cumplio_financiero
            ],
            [
                'nombre' => '4. Expediente Digital Completo',
                'detalle' => $auditoria['expediente_detalle'] ?: 'Títulos previos, cédula y fotografías verificadas',
                'cumple' => $graduacion->cumplio_expediente
            ]
        ];

        $pdf->SetFont('Arial', '', 8.5);
        $fill = false;
        foreach ($requisitos as $req) {
            $pdf->SetFillColor($fill ? 248 : 255, $fill ? 250 : 255, $fill ? 252 : 255);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->Cell(60, 6.5, $pdf->toPdf($req['nombre']), 1, 0, 'L', true);
            $pdf->Cell(80, 6.5, $pdf->toPdf($req['detalle']), 1, 0, 'L', true);

            if ($req['cumple']) {
                $pdf->SetTextColor(22, 101, 52);
                $pdf->SetFont('Arial', 'B', 8.5);
                $pdf->Cell(40, 6.5, $pdf->toPdf("CUMPLE (APROBADO)"), 1, 1, 'C', true);
            } else {
                $pdf->SetTextColor(220, 38, 38);
                $pdf->SetFont('Arial', 'B', 8.5);
                $pdf->Cell(40, 6.5, $pdf->toPdf("PENDIENTE"), 1, 1, 'C', true);
            }
            $pdf->SetFont('Arial', '', 8.5);
            $fill = !$fill;
        }

        $pdf->Ln(6);

        // Cuadro de Registro de Títulos
        $pdf->SetFillColor(241, 245, 249);
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->RoundedRect(15, $pdf->GetY(), 180, 16, 1.5, 'DF');

        $yLibro = $pdf->GetY() + 2.5;
        $pdf->SetXY(20, $yLibro);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(40, 4, $pdf->toPdf("Nº DE TÍTULO / DIPLOMA"), 0, 0, 'C');
        $pdf->Cell(40, 4, $pdf->toPdf("Nº DE ACTA"), 0, 0, 'C');
        $pdf->Cell(40, 4, $pdf->toPdf("TOMO"), 0, 0, 'C');
        $pdf->Cell(40, 4, $pdf->toPdf("FOLIO"), 0, 1, 'C');

        $pdf->SetX(20);
        $pdf->SetFont('Arial', 'B', 9.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(40, 5, $pdf->toPdf($graduacion->numero_titulo ?: 'POR ASIGNAR'), 0, 0, 'C');
        $pdf->Cell(40, 5, $pdf->toPdf($graduacion->numero_acta ?: 'N/D'), 0, 0, 'C');
        $pdf->Cell(40, 5, $pdf->toPdf($graduacion->numero_tomo ?: 'N/D'), 0, 0, 'C');
        $pdf->Cell(40, 5, $pdf->toPdf($graduacion->numero_folio ?: 'N/D'), 0, 1, 'C');

        $pdf->Ln(15);

        // Bloque de Firmas Oficiales
        $yFirmas = $pdf->GetY();
        if ($yFirmas > 235) {
            $pdf->AddPage();
            $yFirmas = $pdf->GetY() + 10;
        }

        $pdf->SetDrawColor(100, 116, 139);
        $pdf->SetLineWidth(0.4);

        // Firma Rectoría / Dirección
        $pdf->Line(20, $yFirmas + 16, 85, $yFirmas + 16);
        $pdf->SetXY(20, $yFirmas + 17);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(65, 4, $pdf->toPdf("Dirección Académica / Rectoría"), 0, 1, 'C');
        $pdf->SetX(20);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(65, 4, $pdf->toPdf(config('cliente.nombre_legal', config('cliente.nombre', 'CEFI'))), 0, 0, 'C');

        // Firma Registro
        $pdf->Line(115, $yFirmas + 16, 180, $yFirmas + 16);
        $pdf->SetXY(115, $yFirmas + 17);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(65, 4, $pdf->toPdf("Departamento de Registro y Admisiones"), 0, 1, 'C');
        $pdf->SetX(115);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(65, 4, $pdf->toPdf("Firma Autorizada y Sello Oficial"), 0, 0, 'C');

        return $pdf->Output('S');
    }
}
