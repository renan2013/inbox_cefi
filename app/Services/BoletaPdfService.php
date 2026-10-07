<?php

namespace App\Services;

require_once __DIR__ . '/FPDF/fpdf.php';

use FPDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Boleta;
use Carbon\Carbon;

class BoletaPDFEngine extends FPDF
{
    function Header()
    {
        // Logo institucional del cliente (Ajustado proporcionalmente para logos cuadrados o rectangulares)
        $logo_cliente = ClienteService::logoPath();
        if (file_exists($logo_cliente)) {
            $imgSize = @getimagesize($logo_cliente);
            $maxW = 40;
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

            $posY = 11 + (($maxH - $imgH) / 2);
            $this->Image($logo_cliente, 15, $posY, $imgW, $imgH);
        }

        // Títulos institucionales
        $this->SetY(12);
        $this->SetX(60);
        $this->SetFont('Arial', 'B', 13);
        $this->SetTextColor(16, 102, 173);
        $this->Cell(135, 6, $this->toPdf(mb_strtoupper(config('cliente.nombre_legal', config('cliente.nombre', 'CEFI')))), 0, 1, 'R');

        $this->SetX(60);
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(135, 5, $this->toPdf('DEPARTAMENTO DE FINANZAS Y REGISTRO'), 0, 1, 'R');

        $this->SetX(60);
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(30, 41, 59);
        $this->Cell(135, 6, $this->toPdf('BOLETA OFICIAL DE MATRÍCULA Y PLAN DE PAGO'), 0, 1, 'R');

        // Línea divisoria azul
        $this->SetDrawColor(16, 102, 173);
        $this->SetLineWidth(0.6);
        $this->Line(15, 33, 195, 33);
        $this->Ln(6);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(148, 163, 184);
        $this->Cell(100, 10, $this->toPdf('Documento Oficial de Matrícula - ' . config('cliente.nombre', 'CEFI')), 0, 0, 'L');
        $this->Cell(0, 10, $this->toPdf('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }

    public function toPdf($txt): string
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
}

class BoletaPdfService
{
    /**
     * Genera el PDF oficial de la boleta de pago y matrícula.
     *
     * @param int $id_boleta
     * @param string $dest 'I' inline, 'D' download, 'F' save
     * @return mixed
     */
    public static function generar(int $id_boleta, string $dest = 'I')
    {
        $boleta = Boleta::with(['estudiante', 'seguimientoPagos'])->findOrFail($id_boleta);
        $estudiante = $boleta->estudiante;

        if (!$estudiante) {
            abort(404, 'Estudiante asociado a la boleta no encontrado.');
        }

        // Cédula limpia oficial
        $cedula_raw = trim($estudiante->cedula ?? '');
        $cedula_mostrar = preg_match('/\d/', $cedula_raw) ? $cedula_raw : 'N/A';

        // Obtener cursos vinculados a la matrícula
        $cursos = DB::table('matriculas as m')
            ->join('cursos_activos as ca', 'm.id_curso_activo', '=', 'ca.id_curso_activo')
            ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->join('programas as p', 'pe.id_programa', '=', 'p.id_programa')
            ->where('m.id_boleta', $id_boleta)
            ->where('m.id_estudiante', $boleta->id_estudiante)
            ->select('pe.codigo', 'pe.materia', 'pe.precio', 'p.nombre_programa', 'p.costo_materia', 'p.costo_matricula', 'p.costo_biblioteca', 'p.costo_inscripcion_unica')
            ->groupBy('ca.id_plan', 'pe.codigo', 'pe.materia', 'pe.precio', 'p.nombre_programa', 'p.costo_materia', 'p.costo_matricula', 'p.costo_biblioteca', 'p.costo_inscripcion_unica')
            ->get();

        $pdf = new BoletaPDFEngine();
        $pdf->AliasNbPages();
        $pdf->SetMargins(15, 12, 15);
        $pdf->AddPage();

        // 1. Bloque de Datos Generales (Ficha Técnica)
        $pdf->SetY(38);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(241, 245, 249);
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.2);

        // Fila 1: Boleta y Fecha
        $pdf->Cell(35, 6, $pdf->toPdf('Boleta N°:'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(16, 102, 173);
        $pdf->Cell(55, 6, $boleta->numero_boleta, 1, 0, 'L');
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(35, 6, $pdf->toPdf('Fecha Emisión:'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(55, 6, $boleta->fecha_creacion->format('d/m/Y H:i'), 1, 1, 'L');

        // Fila 2: Estudiante y Cédula
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(35, 6, $pdf->toPdf('Estudiante:'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', '', 9);
        $nombre_completo = trim($estudiante->nombre . ' ' . ($estudiante->apellidos ?? ''));
        $pdf->Cell(55, 6, $pdf->toPdf(mb_substr($nombre_completo, 0, 30, 'UTF-8')), 1, 0, 'L');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(35, 6, $pdf->toPdf('Cédula / Pasaporte:'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(55, 6, $pdf->toPdf($cedula_mostrar), 1, 1, 'L');

        // Fila 3: Periodo y Estado
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(35, 6, $pdf->toPdf('Periodo:'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(55, 6, $pdf->toPdf($boleta->periodo), 1, 0, 'L');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(35, 6, $pdf->toPdf('Estado:'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', 'B', 9);

        $color_estado = ($boleta->estado === 'pagada') ? [16, 185, 129] : (($boleta->estado === 'anulada') ? [239, 68, 68] : [245, 158, 11]);
        $pdf->SetTextColor($color_estado[0], $color_estado[1], $color_estado[2]);
        $pdf->Cell(55, 6, $pdf->toPdf(strtoupper(str_replace('_', ' ', $boleta->estado))), 1, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);

        $pdf->Ln(5);

        // 2. Tabla de Materias / Cursos Matriculados
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(16, 102, 173);
        $pdf->Cell(180, 6, $pdf->toPdf('DETALLE DE ASIGNATURAS MATRICULADAS'), 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetFillColor(241, 245, 249);
        $pdf->Cell(25, 6, $pdf->toPdf('Código'), 1, 0, 'C', true);
        $pdf->Cell(115, 6, $pdf->toPdf('Curso / Materia'), 1, 0, 'L', true);
        $pdf->Cell(40, 6, $pdf->toPdf('Monto'), 1, 1, 'R', true);

        $pdf->SetFont('Arial', '', 8.5);
        $subtotal_materias = 0.0;

        $max_mat = 0;
        $max_bib = 0;
        $max_ins = 0;
        foreach ($cursos as $c) {
            if (floatval($c->costo_matricula ?? 0) > $max_mat) $max_mat = floatval($c->costo_matricula);
            if (floatval($c->costo_biblioteca ?? 0) > $max_bib) $max_bib = floatval($c->costo_biblioteca);
            if (floatval($c->costo_inscripcion_unica ?? 0) > $max_ins) $max_ins = floatval($c->costo_inscripcion_unica);
        }
        if ($boleta->cobrar_biblioteca && $max_bib == 0) $max_bib = 5000;
        if ($boleta->cobrar_inscripcion && $max_ins == 0) $max_ins = 8000;

        if ($boleta->cobrar_inscripcion && $max_ins > 0) {
            $pdf->Cell(25, 5.5, 'INS-01', 1, 0, 'C');
            $pdf->Cell(115, 5.5, $pdf->toPdf(" INSCRIPCIÓN ÚNICA"), 1, 0, 'L');
            $pdf->Cell(40, 5.5, 'CRC ' . number_format($max_ins, 2) . ' ', 1, 1, 'R');
            $subtotal_materias += $max_ins;
        }
        if ($boleta->cobrar_biblioteca && $max_bib > 0) {
            $pdf->Cell(25, 5.5, 'BIB-01', 1, 0, 'C');
            $pdf->Cell(115, 5.5, $pdf->toPdf(" USO DE BIBLIOTECA"), 1, 0, 'L');
            $pdf->Cell(40, 5.5, 'CRC ' . number_format($max_bib, 2) . ' ', 1, 1, 'R');
            $subtotal_materias += $max_bib;
        }
        if ($boleta->cobrar_matricula && $max_mat > 0) {
            $pdf->Cell(25, 5.5, 'ADM-01', 1, 0, 'C');
            $pdf->Cell(115, 5.5, $pdf->toPdf(" MATRÍCULA DEL PERÍODO"), 1, 0, 'L');
            $pdf->Cell(40, 5.5, 'CRC ' . number_format($max_mat, 2) . ' ', 1, 1, 'R');
            $subtotal_materias += $max_mat;
        }

        if ($cursos->count() > 0) {
            foreach ($cursos as $c) {
                $precio = floatval($c->precio > 0 ? $c->precio : ($c->costo_materia ?? 0));
                $subtotal_materias += $precio;
                $pdf->Cell(25, 5.5, $pdf->toPdf($c->codigo), 1, 0, 'C');
                $pdf->Cell(115, 5.5, $pdf->toPdf(" " . $c->materia), 1, 0, 'L');
                $pdf->Cell(40, 5.5, 'CRC ' . number_format($precio, 2) . ' ', 1, 1, 'R');
            }
        } else {
            $pdf->Cell(180, 6, $pdf->toPdf('Matrícula regular del período'), 1, 1, 'L');
            $subtotal_materias = floatval($boleta->total);
        }

        $pdf->Ln(4);

        // 3. Resumen Financiero y Plan de Pagos
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(16, 102, 173);
        $pdf->Cell(180, 6, $pdf->toPdf('PLAN DE PAGO Y CUOTAS (SEGUIMIENTO)'), 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetFillColor(241, 245, 249);
        $pdf->Cell(20, 6, $pdf->toPdf('Cuota'), 1, 0, 'C', true);
        $pdf->Cell(45, 6, $pdf->toPdf('Fecha Vencimiento'), 1, 0, 'C', true);
        $pdf->Cell(40, 6, $pdf->toPdf('Monto Capital'), 1, 0, 'R', true);
        $pdf->Cell(35, 6, $pdf->toPdf('Mora Acumulada'), 1, 0, 'R', true);
        $pdf->Cell(40, 6, $pdf->toPdf('Total a Pagar'), 1, 1, 'R', true);

        $pdf->SetFont('Arial', '', 8.5);
        $cuotas = $boleta->seguimientoPagos()->orderBy('numero_cuota', 'asc')->get();

        if ($cuotas->count() > 0) {
            foreach ($cuotas as $q) {
                $cap = floatval($q->monto_cuota);
                $mora = floatval($q->interes_acumulado ?? 0);
                $tot = $cap + $mora;

                $pdf->Cell(20, 5.5, '#' . $q->numero_cuota, 1, 0, 'C');
                $pdf->Cell(45, 5.5, $q->fecha_vencimiento ? $q->fecha_vencimiento->format('d/m/Y') : 'N/A', 1, 0, 'C');
                $pdf->Cell(40, 5.5, 'CRC ' . number_format($cap, 2) . ' ', 1, 0, 'R');
                $pdf->Cell(35, 5.5, ($mora > 0 ? 'CRC ' . number_format($mora, 2) : '-') . ' ', 1, 0, 'R');
                $pdf->Cell(40, 5.5, 'CRC ' . number_format($tot, 2) . ' ', 1, 1, 'R');
            }
        } else {
            $pdf->Cell(20, 5.5, '#1', 1, 0, 'C');
            $pdf->Cell(45, 5.5, $boleta->fecha_creacion ? $boleta->fecha_creacion->format('d/m/Y') : date('d/m/Y'), 1, 0, 'C');
            $pdf->Cell(40, 5.5, 'CRC ' . number_format($boleta->total, 2) . ' ', 1, 0, 'R');
            $pdf->Cell(35, 5.5, '- ', 1, 0, 'R');
            $pdf->Cell(40, 5.5, 'CRC ' . number_format($boleta->total, 2) . ' ', 1, 1, 'R');
        }

        // 4. Totales Finales
        $pdf->Ln(3);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(241, 245, 249);

        if ($boleta->descuento > 0) {
            $pdf->Cell(140, 6, $pdf->toPdf('DESCUENTO ESPECIAL APLICADO:'), 1, 0, 'R', true);
            $pdf->SetTextColor(16, 185, 129);
            $pdf->Cell(40, 6, '- CRC ' . number_format($boleta->descuento, 2) . ' ', 1, 1, 'R');
            $pdf->SetTextColor(0, 0, 0);
        }

        $pdf->Cell(140, 6, $pdf->toPdf('TOTAL ARANCEL MATRÍCULA:'), 1, 0, 'R', true);
        $pdf->Cell(40, 6, 'CRC ' . number_format($boleta->total, 2) . ' ', 1, 1, 'R');

        if ($boleta->pago_inicial > 0) {
            $pdf->Cell(140, 6, $pdf->toPdf('ABONO / PAGO INICIAL REGISTRADO:'), 1, 0, 'R', true);
            $pdf->SetTextColor(239, 68, 68);
            $pdf->Cell(40, 6, '- CRC ' . number_format($boleta->pago_inicial, 2) . ' ', 1, 1, 'R');
            $pdf->SetTextColor(0, 0, 0);
        }

        $pdf->Cell(140, 6, $pdf->toPdf('MONTO TOTAL PAGADO HASTA LA FECHA:'), 1, 0, 'R', true);
        $pdf->SetTextColor(16, 185, 129);
        $pdf->Cell(40, 6, 'CRC ' . number_format($boleta->monto_pagado, 2) . ' ', 1, 1, 'R');
        $pdf->SetTextColor(0, 0, 0);

        $tasa_label = '2.0';
        $firma_nombre = 'Merlin Silva';
        $firma_cargo = 'Administración General - CEFI';
        $firma_img = '';

        if (Schema::hasTable('configuracion_sistema_pagos')) {
            $confPagos = DB::table('configuracion_sistema_pagos')
                ->whereIn('clave', ['tasa_interes_mora', 'firma_oficial_nombre', 'firma_oficial_cargo', 'firma_oficial_imagen'])
                ->pluck('valor', 'clave');
            if (isset($confPagos['tasa_interes_mora']) && $confPagos['tasa_interes_mora'] !== '') {
                $tasa_label = $confPagos['tasa_interes_mora'];
            }
            if (!empty($confPagos['firma_oficial_nombre'])) {
                $firma_nombre = $confPagos['firma_oficial_nombre'];
            }
            if (!empty($confPagos['firma_oficial_cargo'])) {
                $firma_cargo = $confPagos['firma_oficial_cargo'];
            }
            if (stripos($firma_cargo, 'UNELA') !== false) {
                $firma_cargo = str_ireplace('UNELA', config('cliente.nombre', 'CEFI'), $firma_cargo);
            }
            if (!empty($confPagos['firma_oficial_imagen'])) {
                $firma_img = $confPagos['firma_oficial_imagen'];
            }
        }

        if ($boleta->interes_acumulado > 0) {
            $pdf->Cell(140, 6, $pdf->toPdf("INTERÉS POR MORA ACUMULADO ({$tasa_label}%):"), 1, 0, 'R', true);
            $pdf->SetTextColor(239, 68, 68);
            $pdf->Cell(40, 6, 'CRC ' . number_format($boleta->interes_acumulado, 2) . ' ', 1, 1, 'R');
            $pdf->SetTextColor(0, 0, 0);
        }

        $pdf->Cell(140, 6, $pdf->toPdf('SALDO PENDIENTE ACTUAL:'), 1, 0, 'R', true);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor($boleta->saldo_pendiente > 0 ? 239 : 16, $boleta->saldo_pendiente > 0 ? 68 : 185, $boleta->saldo_pendiente > 0 ? 68 : 129);
        $pdf->Cell(40, 6, 'CRC ' . number_format($boleta->saldo_pendiente, 2) . ' ', 1, 1, 'R');
        $pdf->SetTextColor(0, 0, 0);

        // 5. Bloque de Firmas y Sello Institucional
        $pdf->Ln(14);
        $y_firma = $pdf->GetY();

        if ($y_firma > 230) {
            $pdf->AddPage();
            $y_firma = 40;
            $pdf->SetY($y_firma);
        }

        $w_linea = 75;
        // Firma Estudiante (Izquierda)
        $x_est = 20;

        // Estampar firma digital manuscrita si existe
        if (!empty($boleta->ruta_firma)) {
            $ruta_img_firma = public_path(ltrim($boleta->ruta_firma, '/'));
            if (file_exists($ruta_img_firma)) {
                $pdf->Image($ruta_img_firma, $x_est + 15, $y_firma - 18, 45, 16);
            }
        }

        $pdf->Line($x_est, $y_firma, $x_est + $w_linea, $y_firma);
        $pdf->SetXY($x_est, $y_firma + 2);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($w_linea, 4, $pdf->toPdf('Firma del Estudiante / Aceptación Digital'), 0, 1, 'C');
        $pdf->SetX($x_est);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($w_linea, 4, $pdf->toPdf($nombre_completo), 0, 0, 'C');

        // Sello y Firma Oficial Institucional (Derecha)
        $x_fin = 105;

        // Estampar imagen de firma institucional si fue cargada en configuración
        if (!empty($firma_img)) {
            $ruta_img_oficial = public_path(ltrim($firma_img, '/'));
            if (file_exists($ruta_img_oficial)) {
                $pdf->Image($ruta_img_oficial, $x_fin + 15, $y_firma - 18, 45, 16);
            }
        }

        $pdf->Line($x_fin, $y_firma, $x_fin + $w_linea, $y_firma);
        $pdf->SetXY($x_fin, $y_firma + 2);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($w_linea, 4, $pdf->toPdf($firma_cargo), 0, 1, 'C');
        $pdf->SetX($x_fin);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($w_linea, 4, $pdf->toPdf($firma_nombre), 0, 0, 'C');

        // Guardar copia física en uploads/boletas si no existe
        $dir = public_path('uploads/boletas');
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }
        $ruta_disco = $dir . "/boleta_{$id_boleta}.pdf";
        @$pdf->Output('F', $ruta_disco);

        $filename = "boleta_{$boleta->numero_boleta}.pdf";

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
