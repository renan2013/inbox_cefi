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
        $txt = str_replace('&nbsp;', ' ', (string)$txt);
        $txt = html_entity_decode($txt, ENT_QUOTES, 'UTF-8');
        $txt = strtr($txt, [
            '“' => '"', '”' => '"', '‘' => "'", '’' => "'",
            '–' => '-', '—' => '-', '•' => '-', '…' => '...',
        ]);
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
        $pdf->Cell(50, 5, $pdf->toPdf("Lugar: " . mb_substr((string)($expediente->lugar_nacimiento ?: 'N/D'), 0, 22, 'UTF-8')), 0, 0, 'L');
        $pdf->Cell(45, 5, $pdf->toPdf("Nacionalidad: " . mb_substr((string)($expediente->nacionalidad ?: 'N/D'), 0, 18, 'UTF-8')), 0, 0, 'L');
        $pdf->Cell(40, 5, $pdf->toPdf("Género: " . ($expediente->genero ?: 'N/D')), 0, 1, 'L');

        $pdf->Cell(45, 5, $pdf->toPdf("Estado Civil: " . ($expediente->estado_civil ?: 'N/D')), 0, 0, 'L');
        $pdf->Cell(95, 5, $pdf->toPdf("Correo Personal: " . mb_substr((string)($usuario->email ?? 'N/D'), 0, 42, 'UTF-8')), 0, 0, 'L');
        $pdf->Cell(40, 5, $pdf->toPdf("Tel. Celular: " . ($expediente->contacto_tel_celular ?: ($usuario->telefono ?? 'N/D'))), 0, 1, 'L');
        $pdf->Ln(2);

        // --- II. DOMICILIO Y CONTACTO DE EMERGENCIA ---
        $imprimirSeccion("II. RESIDENCIA Y CONTACTOS DE EMERGENCIA");
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(60, 5, $pdf->toPdf("Provincia: " . mb_substr((string)($expediente->domicilio_provincia ?: 'N/D'), 0, 24, 'UTF-8')), 0, 0, 'L');
        $pdf->Cell(60, 5, $pdf->toPdf("Cantón: " . mb_substr((string)($expediente->domicilio_canton ?: 'N/D'), 0, 24, 'UTF-8')), 0, 0, 'L');
        $pdf->Cell(60, 5, $pdf->toPdf("Distrito: " . mb_substr((string)($expediente->domicilio_distrito ?: 'N/D'), 0, 24, 'UTF-8')), 0, 1, 'L');

        $pdf->MultiCell(180, 5, $pdf->toPdf("Dirección: " . ($expediente->domicilio_direccion ?: 'N/D')), 0, 'L');
        $pdf->Cell(180, 5, $pdf->toPdf("Tel. Habitación: " . ($expediente->contacto_tel_habitacion ?: 'N/D')), 0, 1, 'L');
        $pdf->MultiCell(180, 5, $pdf->toPdf("Contacto de Emergencia: " . ($expediente->contacto_otro_emergencias ?: 'No especificado')), 0, 'L');
        $pdf->Ln(2);

        // --- III. PROCEDENCIA ACADÉMICA ---
        $imprimirSeccion("III. PROCEDENCIA ACADÉMICA (SECUNDARIA Y SUPERIOR)");
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(90, 5, $pdf->toPdf("Secundaria: " . mb_substr((string)($expediente->procedencia_secundaria_institucion ?: 'N/D'), 0, 38, 'UTF-8')), 0, 0, 'L');
        $pdf->Cell(45, 5, $pdf->toPdf("Año Graduación: " . ($expediente->procedencia_secundaria_ano_graduacion ?: 'N/D')), 0, 0, 'L');
        $pdf->Cell(45, 5, $pdf->toPdf("Grado: " . mb_substr((string)($expediente->procedencia_secundaria_grado_obtenido ?: 'N/D'), 0, 20, 'UTF-8')), 0, 1, 'L');

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

        // --- VI. MÓDULOS / CURSOS MATRICULADOS ---
        $cursosPdf = \Illuminate\Support\Facades\DB::table('matriculas as m')
            ->join('cursos_activos as ca', 'm.id_curso_activo', '=', 'ca.id_curso_activo')
            ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->leftJoin('programas as p', 'pe.id_programa', '=', 'p.id_programa')
            ->leftJoin('usuarios as u_prof', 'ca.id_profesor', '=', 'u_prof.id')
            ->where('m.id_estudiante', $expediente->id_usuario)
            ->where(function ($q) {
                $q->whereNull('m.estado')->orWhere('m.estado', '!=', 'Retirado');
            })
            ->select(
                'p.nombre_programa',
                'pe.codigo', 'pe.materia', 'pe.modalidad as pe_modalidad', 'pe.duracion',
                'ca.fecha_inicio', 'ca.fecha_final', 'ca.modalidad as ca_modalidad',
                'ca.horario', 'ca.hora_inicio', 'ca.hora_final', 'ca.nombre_grupo',
                'u_prof.nombre as prof_nombre', 'u_prof.apellidos as prof_apellidos',
                'm.calificacion'
            )
            ->orderBy('p.nombre_programa')
            ->orderByDesc('ca.fecha_inicio')
            ->get();

        if ($cursosPdf->count() > 0) {
            if ($pdf->GetY() > 235) {
                $pdf->AddPage();
            }
            $imprimirSeccion("VI. MÓDULOS / CURSOS MATRICULADOS");
            $programaActual = null;
            foreach ($cursosPdf as $cp) {
                if ($pdf->GetY() > 245) {
                    $pdf->AddPage();
                }
                $nombreProg = $cp->nombre_programa ?: 'Programa General';
                if ($nombreProg !== $programaActual) {
                    $programaActual = $nombreProg;
                    $pdf->SetFont('Arial', 'B', 8.5);
                    $pdf->SetFillColor(239, 246, 255);
                    $pdf->SetTextColor(30, 64, 175);
                    $pdf->Cell(180, 5.5, $pdf->toPdf("Programa: {$programaActual}"), 0, 1, 'L', true);
                    $pdf->Ln(1);
                }
                $mod = $cp->ca_modalidad ?: ($cp->pe_modalidad ?: 'N/D');
                $hor = trim((string) ($cp->horario ?? ''));
                if ($hor === '' && ($cp->hora_inicio || $cp->hora_final)) {
                    $hor = trim(($cp->hora_inicio ?? '') . ($cp->hora_inicio && $cp->hora_final ? ' - ' : '') . ($cp->hora_final ?? ''));
                }
                $hor = $hor ?: 'N/D';
                $dur = $cp->duracion ?: 'N/D';
                $grp = $cp->nombre_grupo ?: 'N/D';
                $doc = $cp->prof_nombre ? trim("{$cp->prof_nombre} {$cp->prof_apellidos}") : 'Docente Asignado';
                $fIni = $cp->fecha_inicio ? date('d/m/Y', strtotime($cp->fecha_inicio)) : 'N/D';
                $fFin = $cp->fecha_final ? date('d/m/Y', strtotime($cp->fecha_final)) : 'N/D';

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetTextColor(15, 23, 42);
                $pdf->Cell(180, 4.5, $pdf->toPdf("{$cp->codigo} - {$cp->materia}"), 0, 1, 'L');

                $pdf->SetFont('Arial', '', 7.5);
                $pdf->SetTextColor(51, 65, 85);
                $pdf->Cell(90, 4, $pdf->toPdf("Docente: {$doc}"), 0, 0, 'L');
                $pdf->Cell(90, 4, $pdf->toPdf("Grupo: {$grp}"), 0, 1, 'L');
                $pdf->Cell(60, 4, $pdf->toPdf("Modalidad: {$mod}"), 0, 0, 'L');
                $pdf->Cell(60, 4, $pdf->toPdf("Duración: {$dur}"), 0, 0, 'L');
                $pdf->Cell(60, 4, $pdf->toPdf("Horario: {$hor}"), 0, 1, 'L');
                $pdf->Cell(90, 4, $pdf->toPdf("Fecha de inicio: {$fIni}"), 0, 0, 'L');
                $pdf->Cell(90, 4, $pdf->toPdf("Fecha de finalización: {$fFin}"), 0, 1, 'L');
                $pdf->Ln(1.5);
            }
            $pdf->Ln(1);
        }

        // --- VII. DICTAMEN DE REGISTRO & FORMALIZACIÓN ---
        if ($pdf->GetY() > 240) {
            $pdf->AddPage();
        }
        $imprimirSeccion("VII. FORMALIZACIÓN Y DICTAMEN DE REGISTRO");
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->SetTextColor(15, 23, 42);
        $estadoExp = $expediente->estado ?: 'Pendiente';
        $esAprobado = strtolower($estadoExp) === 'aprobado';
        $obsDefault = $esAprobado
            ? "Expediente digital verificado y aprobado conforme a los requisitos institucionales de admisión."
            : "Expediente en estado: {$estadoExp}. Pendiente de verificación final de requisitos.";
        $obs = $expediente->registro_observaciones ?: $obsDefault;
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
        $pdf->Cell(60, 3.5, $pdf->toPdf(config('cliente.nombre', 'CEFI') . " - Estado: " . $estadoExp), 0, 1, 'C');
        $pdf->SetX(115);
        if ($esAprobado) {
            $pdf->SetTextColor(22, 163, 74);
            $selloTxt = "EXPEDIENTE FORMALIZADO Y APROBADO";
        } else {
            $pdf->SetTextColor(217, 119, 6);
            $selloTxt = "ESTADO: " . mb_strtoupper($estadoExp, 'UTF-8');
        }
        $pdf->SetFont('Arial', 'B', 7.5);
        $pdf->Cell(60, 3.5, $pdf->toPdf($selloTxt), 0, 0, 'C');

        return $pdf->Output('S');
    }
}
