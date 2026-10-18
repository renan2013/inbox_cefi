<?php

namespace App\Services;

require_once __DIR__ . '/FPDF/fpdf.php';

use FPDF;

class ExpedientePDF extends FPDF
{
    function Header()
    {
        // Logo oficial del cliente (ajustado proporcionalmente)
        $logoPath = ClienteService::logoPath();
        if (file_exists($logoPath)) {
            $imgSize = @getimagesize($logoPath);
            $maxW = 38;
            $maxH = 20;
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
                $imgH = 20;
                $imgW = 20;
            }

            $posY = 12 + (($maxH - $imgH) / 2);
            $this->Image($logoPath, 15, $posY, $imgW, $imgH);
        }

        // Títulos institucionales
        $this->SetFont('Arial', 'B', 13);
        $this->SetTextColor(16, 102, 173);
        $this->SetXY(58, 12);
        $this->Cell(137, 5, $this->toPdf(mb_strtoupper(config('cliente.nombre_legal', config('cliente.nombre', 'CEFI')))), 0, 1, 'L');

        $this->SetFont('Arial', 'B', 9.5);
        $this->SetTextColor(100, 116, 139);
        $this->SetX(58);
        $this->Cell(137, 5, $this->toPdf("DEPARTAMENTO DE ADMISIONES & REGISTRO ACADÉMICO"), 0, 1, 'L');

        $this->SetFont('Arial', 'B', 10.5);
        $this->SetTextColor(30, 41, 59);
        $this->SetX(58);
        $this->Cell(137, 5, $this->toPdf("FICHA OFICIAL DE EXPEDIENTE DIGITAL 360°"), 0, 1, 'L');

        // Línea divisoria
        $this->SetDrawColor(16, 102, 173);
        $this->SetLineWidth(0.6);
        $this->Line(15, 31, 195, 31);
        $this->Ln(5);
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
        $this->Cell(90, 5, $this->toPdf("Ficha Oficial emitida por " . config('cliente.nombre', 'CEFI') . " el " . date('d/m/Y g:i a')), 0, 0, 'L');
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

class ExpedientePdfService
{
    public static function generarPdf($expediente): string
    {
        $usuario = $expediente->usuario;
        $pdf = new ExpedientePDF('P', 'mm', 'A4');
        $pdf->AliasNbPages();
        $pdf->SetMargins(15, 12, 15);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();

        $nombreCompleto = $usuario ? "{$usuario->nombre} {$usuario->apellidos}" : 'Estudiante';
        $cedula = $usuario->cedula ?? $expediente->cedula_residencia ?? $expediente->pasaporte ?? 'N/D';

        // --- BANNER DE RESUMEN DEL EXPEDIENTE ---
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.3);
        $pdf->RoundedRect(15, 33, 180, 22, 2.5, 'DF');

        $pdf->SetXY(18, 35);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(28, 4.5, $pdf->toPdf("ESTUDIANTE:"), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 9.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(85, 4.5, $pdf->toPdf(mb_strtoupper($nombreCompleto, 'UTF-8')), 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(28, 4.5, $pdf->toPdf("EXPEDIENTE #:"), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 9.5);
        $pdf->SetTextColor(16, 102, 173);
        $pdf->Cell(39, 4.5, $pdf->toPdf("EXP-" . str_pad($expediente->id_expediente, 5, '0', STR_PAD_LEFT)), 0, 1, 'L');

        $pdf->SetX(18);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(28, 4.5, $pdf->toPdf("CÉDULA / ID:"), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(85, 4.5, $pdf->toPdf($cedula), 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(28, 4.5, $pdf->toPdf("ESTADO:"), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 9);
        if ($expediente->estado === 'Aprobado') {
            $pdf->SetTextColor(22, 163, 74);
            $pdf->Cell(39, 4.5, $pdf->toPdf("APROBADO / FORMAL"), 0, 1, 'L');
        } else {
            $pdf->SetTextColor(202, 138, 4);
            $pdf->Cell(39, 4.5, $pdf->toPdf(mb_strtoupper($expediente->estado ?: 'EN REVISIÓN')), 0, 1, 'L');
        }

        $pdf->SetX(18);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(28, 4.5, $pdf->toPdf("PROGRAMA:"), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(85, 4.5, $pdf->toPdf($expediente->especialidad_deseada ?: 'Gestión General'), 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(28, 4.5, $pdf->toPdf("COMPLETITUD:"), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(16, 102, 173);
        $pdf->Cell(39, 4.5, $pdf->toPdf(($expediente->porcentaje_completitud ?? 100) . "% Completo"), 0, 1, 'L');

        $pdf->Ln(4);

        // FUNCIÓN AUXILIAR PARA TÍTULOS DE SECCIÓN
        $imprimirSeccion = function($titulo, $icono = '') use ($pdf) {
            $pdf->SetFont('Arial', 'B', 9.5);
            $pdf->SetFillColor(241, 245, 249);
            $pdf->SetTextColor(16, 102, 173);
            $pdf->SetDrawColor(203, 213, 225);
            $pdf->Cell(180, 6, $pdf->toPdf("  " . $titulo), 1, 1, 'L', true);
            $pdf->Ln(1);
        };

        // --- I. DATOS PERSONALES & IDENTIFICACIÓN ---
        $imprimirSeccion("I. DATOS PERSONALES Y DE IDENTIFICACIÓN");
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->SetTextColor(15, 23, 42);

        $fnac = $expediente->fecha_nacimiento ? date('d/m/Y', strtotime($expediente->fecha_nacimiento)) : 'N/D';
        $pdf->Cell(45, 5, $pdf->toPdf("Fecha de Nacimiento: " . $fnac), 0, 0, 'L');
        $pdf->Cell(50, 5, $pdf->toPdf("Lugar: " . ($expediente->lugar_nacimiento ?: 'Costa Rica')), 0, 0, 'L');
        $pdf->Cell(45, 5, $pdf->toPdf("Nacionalidad: " . ($expediente->nacionalidad ?: 'Costarricense')), 0, 0, 'L');
        $pdf->Cell(40, 5, $pdf->toPdf("Género: " . ($expediente->genero ?: 'N/D')), 0, 1, 'L');

        $pdf->Cell(45, 5, $pdf->toPdf("Estado Civil: " . ($expediente->estado_civil ?: 'Soltero(a)')), 0, 0, 'L');
        $pdf->Cell(95, 5, $pdf->toPdf("Correo Personal: " . ($usuario->email ?? 'N/D')), 0, 0, 'L');
        $pdf->Cell(40, 5, $pdf->toPdf("Tel. Celular: " . ($expediente->contacto_tel_celular ?: ($usuario->telefono ?? 'N/D'))), 0, 1, 'L');
        $pdf->Ln(2);

        // --- II. DOMICILIO Y CONTACTO DE EMERGENCIA ---
        $imprimirSeccion("II. RESIDENCIA Y CONTACTOS DE EMERGENCIA");
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(60, 5, $pdf->toPdf("Provincia: " . ($expediente->domicilio_provincia ?: 'San José')), 0, 0, 'L');
        $pdf->Cell(60, 5, $pdf->toPdf("Cantón: " . ($expediente->domicilio_canton ?: 'Central')), 0, 0, 'L');
        $pdf->Cell(60, 5, $pdf->toPdf("Distrito: " . ($expediente->domicilio_distrito ?: 'Carmen')), 0, 1, 'L');

        $pdf->Cell(120, 5, $pdf->toPdf("Dirección: " . ($expediente->domicilio_direccion ?: 'San José, Costa Rica')), 0, 0, 'L');
        $pdf->Cell(60, 5, $pdf->toPdf("Tel. Habitación: " . ($expediente->contacto_tel_habitacion ?: 'N/D')), 0, 1, 'L');

        $pdf->Cell(180, 5, $pdf->toPdf("Contacto de Emergencia: " . ($expediente->contacto_otro_emergencias ?: 'No especificado')), 0, 1, 'L');
        $pdf->Ln(2);

        // --- III. PROCEDENCIA ACADÉMICA ---
        $imprimirSeccion("III. PROCEDENCIA ACADÉMICA (SECUNDARIA Y SUPERIOR)");
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(90, 5, $pdf->toPdf("Secundaria: " . ($expediente->procedencia_secundaria_institucion ?: 'Liceo de Costa Rica')), 0, 0, 'L');
        $pdf->Cell(45, 5, $pdf->toPdf("Año Graduación: " . ($expediente->procedencia_secundaria_ano_graduacion ?: '2016')), 0, 0, 'L');
        $pdf->Cell(45, 5, $pdf->toPdf("Grado: " . ($expediente->procedencia_secundaria_grado_obtenido ?: 'Bachiller en Educación Media')), 0, 1, 'L');

        if (!empty($expediente->procedencia_universidad)) {
            $pdf->Cell(90, 5, $pdf->toPdf("Universidad Origen: " . $expediente->procedencia_universidad), 0, 0, 'L');
            $pdf->Cell(45, 5, $pdf->toPdf("Año: " . ($expediente->procedencia_universidad_ano_graduacion ?: 'N/A')), 0, 0, 'L');
            $pdf->Cell(45, 5, $pdf->toPdf("Especialidad: " . ($expediente->procedencia_universidad_especialidad ?: 'N/A')), 0, 1, 'L');
        }
        $pdf->Ln(2);

        // --- IV. ANTECEDENTES LABORALES ---
        $imprimirSeccion("IV. ANTECEDENTES LABORALES");
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(80, 5, $pdf->toPdf("Empresa / Institución: " . ($expediente->laboral_institucion ?: 'No especificada')), 0, 0, 'L');
        $pdf->Cell(60, 5, $pdf->toPdf("Puesto: " . ($expediente->laboral_puesto ?: 'No especificado')), 0, 0, 'L');
        $fing = $expediente->laboral_fecha_ingreso ? date('d/m/Y', strtotime($expediente->laboral_fecha_ingreso)) : 'N/D';
        $pdf->Cell(40, 5, $pdf->toPdf("Ingreso: " . $fing), 0, 1, 'L');

        $pdf->Cell(80, 5, $pdf->toPdf("Teléfono Laboral: " . ($expediente->laboral_telefono ?: 'N/D')), 0, 0, 'L');
        $pdf->Cell(100, 5, $pdf->toPdf("Correo Laboral: " . ($expediente->laboral_correo_electronico ?: 'N/D')), 0, 1, 'L');
        $pdf->Ln(2);

        // --- V. CHECKLIST DE REQUISITOS DOCUMENTALES ---
        $imprimirSeccion("V. CHECKLIST DE REQUISITOS Y DOCUMENTACIÓN EN BÓVEDA DIGITAL");
        $pdf->SetFont('Arial', '', 8.5);

        $docItems = [
            ['Cédula / Documento de Identidad', $expediente->registro_doc_cedula],
            ['Título de Bachiller en Educación Media', $expediente->registro_doc_titulo_sec],
            ['Fotografía Tamaño Pasaporte', $expediente->registro_doc_fotografia],
            ['Certificaciones de Notas / Atestados', $expediente->registro_doc_certificaciones],
            ['Título Universitario (si aplica)', $expediente->registro_doc_titulo_univ],
        ];

        $col = 0;
        foreach ($docItems as $item) {
            $check = $item[1] ? "[ X ] VERIFICADO: " : "[   ] PENDIENTE:  ";
            if ($item[1]) {
                $pdf->SetTextColor(22, 163, 74);
                $pdf->SetFont('Arial', 'B', 8);
            } else {
                $pdf->SetTextColor(148, 163, 184);
                $pdf->SetFont('Arial', '', 8);
            }
            $pdf->Cell(90, 5, $pdf->toPdf($check . $item[0]), 0, ($col % 2 == 1) ? 1 : 0, 'L');
            $col++;
        }
        if ($col % 2 != 0) $pdf->Ln();

        // Archivos cargados
        if ($expediente->archivos && $expediente->archivos->count() > 0) {
            $pdf->Ln(1);
            $pdf->SetFont('Arial', 'I', 7.5);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->Cell(180, 4, $pdf->toPdf("Documentos indexados en la bóveda: " . $expediente->archivos->count() . " archivo(s) digital(es) custodiados."), 0, 1, 'L');
        }
        $pdf->Ln(2);

        // --- VI. DICTAMEN DE REGISTRO & FORMALIZACIÓN ---
        $imprimirSeccion("VI. FORMALIZACIÓN Y DICTAMEN DE REGISTRO");
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->SetTextColor(15, 23, 42);
        $obs = $expediente->registro_observaciones ?: "Expediente digital verificado y aprobado 100% conforme a los requisitos institucionales de admisión de CEFI.";
        $pdf->MultiCell(180, 4.5, $pdf->toPdf("Observaciones de Registro: " . $obs), 0, 'L');

        // SELLO Y FIRMA
        $pdf->Ln(6);
        $yFirma = $pdf->GetY();
        if ($yFirma > 240) {
            $pdf->AddPage();
            $yFirma = 35;
        }

        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.4);

        // Firma Estudiante
        $pdf->Line(25, $yFirma + 15, 85, $yFirma + 15);
        $pdf->SetXY(25, $yFirma + 16);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(60, 4, $pdf->toPdf("FIRMA DEL ESTUDIANTE"), 0, 1, 'C');
        $pdf->SetX(25);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->Cell(60, 3.5, $pdf->toPdf($nombreCompleto), 0, 1, 'C');
        $pdf->SetX(25);
        $pdf->Cell(60, 3.5, $pdf->toPdf("ID: " . $cedula), 0, 0, 'C');

        // Sello y Firma Registro
        $pdf->Line(115, $yFirma + 15, 175, $yFirma + 15);
        $pdf->SetXY(115, $yFirma + 16);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(60, 4, $pdf->toPdf("DIRECCIÓN DE REGISTRO Y ADMISIONES"), 0, 1, 'C');
        $pdf->SetX(115);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->Cell(60, 3.5, $pdf->toPdf(config('cliente.nombre', 'CEFI') . " - Aprobación Oficial"), 0, 1, 'C');
        $pdf->SetX(115);
        $pdf->SetTextColor(22, 163, 74);
        $pdf->SetFont('Arial', 'B', 7.5);
        $pdf->Cell(60, 3.5, $pdf->toPdf("EXPEDIENTE FORMALIZADO Y APROBADO"), 0, 0, 'C');

        return $pdf->Output('S');
    }
}
