<?php

namespace App\Services;

require_once __DIR__ . '/FPDF/fpdf.php';

use FPDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ActaPDF extends FPDF
{
    function Header()
    {
        // Logo oficial del cliente (Superior Izquierda)
        $logo_cliente = public_path(ltrim(config('cliente.logo_url', 'imgs/logo.png'), '/'));
        if (!file_exists($logo_cliente)) {
            $logo_cliente = public_path('imgs/logo.png');
        }
        if (!file_exists($logo_cliente)) {
            $logo_cliente = public_path('imgs/logo_unela_color.png');
        }
        if (file_exists($logo_cliente)) {
            $this->Image($logo_cliente, 10, 10, 45);
        }

        // Título y Nombre Institución (Derecha del logo)
        $this->SetY(12);
        $this->SetX(60);
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(0, 51, 102);
        $this->Cell(0, 6, $this->toPdf(config('cliente.nombre_legal', config('cliente.nombre', 'CEFI'))), 0, 1, 'R');
        $this->SetX(60);
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(0, 10, $this->toPdf('ACTA DE CALIFICACIONES'), 0, 1, 'R');
        $this->SetTextColor(0, 0, 0);

        // Línea horizontal delgada debajo del logo (Margen a margen: 10mm a 287mm)
        $this->SetDrawColor(0, 51, 102);
        $this->SetLineWidth(0.25);
        $this->Line(10, 31, 287, 31);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.2);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $texto_footer = $this->toPdf('Design and developed by renangalvan.net - Sistema Inbox');
        $paginacion = $this->toPdf('Página ') . $this->PageNo() . '/{nb}';

        // Texto izquierda
        $this->Cell(0, 10, $texto_footer, 0, 0, 'L');

        // Logo INBOX (Centrado si existe)
        $logo_inbox = public_path('imgs/logo_inbox_color.png');
        if (!file_exists($logo_inbox)) {
            $logo_inbox = dirname(base_path()) . '/imgs/logo_inbox_color.png';
        }
        if (file_exists($logo_inbox)) {
            $x_logo = ($this->w / 2) - 10;
            $this->Image($logo_inbox, $x_logo, $this->GetY() + 1, 20);
        }

        // Paginación derecha
        $this->SetX(-40);
        $this->Cell(0, 10, $paginacion, 0, 0, 'R');
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

class ActaOficialPdfService
{
    /**
     * Genera el Acta Oficial de Calificaciones en formato apaisado (Landscape A4).
     *
     * @param int $id_curso ID del curso activo
     * @param string $dest 'I' para previsualizar en navegador, 'D' para descargar, 'F' para guardar
     * @return mixed Response o void
     */
    public static function generar(int $id_curso, string $dest = 'I')
    {
        // 1. Obtener datos del curso activo y programa
        $curso = DB::table('cursos_activos as ca')
            ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->join('programas as p', 'pe.id_programa', '=', 'p.id_programa')
            ->leftJoin('usuarios as u', 'ca.id_profesor', '=', 'u.id')
            ->select(
                'ca.id_curso_activo',
                'ca.periodo',
                'ca.id_profesor',
                'ca.titulo_prefijo',
                'ca.titulo_sufijo',
                'pe.id_plan',
                'pe.materia',
                'pe.codigo',
                'p.nombre_programa',
                'u.nombre as prof_nombre',
                'u.apellidos as prof_apellidos'
            )
            ->where('ca.id_curso_activo', $id_curso)
            ->first();

        if (!$curso) {
            abort(404, 'Curso no encontrado.');
        }

        $nombre_profesor = trim(($curso->titulo_prefijo ?? '') . ' ' . ($curso->prof_nombre ?? '') . ' ' . ($curso->prof_apellidos ?? '') . ' ' . ($curso->titulo_sufijo ?? ''));
        if (empty($nombre_profesor)) {
            $nombre_profesor = 'Sin asignar';
        }

        // 2. Obtener Rubros de Evaluación (Sílabo activo)
        $prof_id = $curso->id_profesor ?? 0;
        $silabo = DB::table('silabos')
            ->where('id_plan', $curso->id_plan)
            ->orderByRaw('(id_profesor = ?) DESC', [$prof_id])
            ->orderBy('id_silabo', 'desc')
            ->first();

        $rubros = [];
        if ($silabo) {
            $rubros = DB::table('silabo_evaluacion')
                ->where('id_silabo', $silabo->id_silabo)
                ->orderBy('id_evaluacion', 'asc')
                ->get()
                ->toArray();
        }

        // 3. Obtener alumnos matriculados y sus notas finales
        $alumnos = DB::table('matriculas as m')
            ->join('usuarios as u', 'm.id_estudiante', '=', 'u.id')
            ->select(
                'm.id_matricula',
                'u.nombre',
                'u.apellidos',
                'u.cedula',
                'm.calificacion'
            )
            ->where('m.id_curso_activo', $id_curso)
            ->where(function ($q) use ($curso) {
                if ($curso->id_profesor) {
                    $q->where('u.id', '!=', $curso->id_profesor);
                }
            })
            ->orderBy('u.apellidos', 'asc')
            ->orderBy('u.nombre', 'asc')
            ->get();

        // 4. Cargar notas parciales por rubro
        $notas_parciales = [];
        if (!empty($rubros)) {
            $npRows = DB::table('notas_rubros as nr')
                ->join('matriculas as m', 'nr.id_matricula', '=', 'm.id_matricula')
                ->select('nr.id_matricula', 'nr.id_rubro', 'nr.calificacion_obtenida')
                ->where('m.id_curso_activo', $id_curso)
                ->get();

            foreach ($npRows as $np) {
                $notas_parciales[$np->id_matricula][$np->id_rubro] = $np->calificacion_obtenida;
            }
        }

        // 5. Inicializar PDF Horizontal (Landscape A4: 297mm x 210mm, útil 277mm)
        $pdf = new ActaPDF();
        $pdf->AliasNbPages();
        $pdf->AddPage('L');

        // Información de Cabecera (Y = 34.5)
        $pdf->SetY(34.5);

        // Línea 1: Curso
        $pdf->SetX(10);
        $pdf->SetFont('Arial', 'B', 9);
        $w_lbl = $pdf->GetStringWidth($pdf->toPdf('Curso: '));
        $pdf->Cell($w_lbl, 4.5, $pdf->toPdf('Curso: '), 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 4.5, $pdf->toPdf($curso->materia . " (" . $curso->codigo . ")"), 0, 1);

        // Línea 2: Programa
        $pdf->SetX(10);
        $pdf->SetFont('Arial', 'B', 9);
        $w_lbl = $pdf->GetStringWidth($pdf->toPdf('Programa: '));
        $pdf->Cell($w_lbl, 4.5, $pdf->toPdf('Programa: '), 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 4.5, $pdf->toPdf($curso->nombre_programa), 0, 1);

        // Línea 3: Profesor (Izq), Periodo (Centro), Fecha (Der)
        $pdf->SetX(10);
        $pdf->SetFont('Arial', 'B', 9);
        $w_lbl_prof = $pdf->GetStringWidth($pdf->toPdf('Profesor: '));
        $pdf->Cell($w_lbl_prof, 4.5, $pdf->toPdf('Profesor: '), 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $w_val_prof = $pdf->GetStringWidth($pdf->toPdf($nombre_profesor));
        $pdf->Cell($w_val_prof, 4.5, $pdf->toPdf($nombre_profesor), 0, 0);

        // Periodo al centro
        $pdf->SetX(135);
        $pdf->SetFont('Arial', 'B', 9);
        $w_lbl_per = $pdf->GetStringWidth($pdf->toPdf('Periodo: '));
        $pdf->Cell($w_lbl_per, 4.5, $pdf->toPdf('Periodo: '), 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 4.5, $pdf->toPdf($curso->periodo ?? 'N/A'), 0, 0);

        // Fecha a la derecha alineada a 287mm
        $texto_fecha_val = date('d/m/Y H:i');
        $pdf->SetFont('Arial', 'B', 9);
        $w_lbl_fecha = $pdf->GetStringWidth($pdf->toPdf('Fecha: '));
        $pdf->SetFont('Arial', '', 9);
        $w_val_fecha = $pdf->GetStringWidth($pdf->toPdf($texto_fecha_val));
        $pdf->SetX(287 - ($w_lbl_fecha + $w_val_fecha));
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($w_lbl_fecha, 4.5, $pdf->toPdf('Fecha: '), 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell($w_val_fecha, 4.5, $pdf->toPdf($texto_fecha_val), 0, 1);

        $pdf->Ln(3.5);

        // --- Construcción de Tabla Horizontal ---
        $pdf->SetLineWidth(0.2);
        $pdf->SetDrawColor(0, 0, 0);

        $w_nro = 10;
        $w_nombre = 75;
        $w_cedula = 30;
        $w_final = 20;
        $w_condicion = 26;

        $ancho_total_disponible = 277;
        $espacio_rubros = $ancho_total_disponible - $w_nro - $w_nombre - $w_cedula - $w_final - $w_condicion;
        $num_rubros = count($rubros);
        $w_rubro = ($num_rubros > 0) ? ($espacio_rubros / $num_rubros) : 0;

        // Cabecera de 2 niveles (Altura 10mm)
        $y_hdr = $pdf->GetY();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(235, 235, 235);

        // Columnas fijas
        $pdf->Cell($w_nro, 10, 'No.', 1, 0, 'C', true);
        $pdf->Cell($w_nombre, 10, 'Estudiante', 1, 0, 'C', true);
        $pdf->Cell($w_cedula, 10, $pdf->toPdf('Cédula'), 1, 0, 'C', true);

        // Columnas de Rubros
        if ($w_rubro > 0) {
            foreach ($rubros as $rubro) {
                $x_col = $pdf->GetX();
                $pdf->SetFont('Arial', 'B', 7);
                $max_chars = ($w_rubro > 25) ? 22 : 14;
                $nombre_corto = mb_substr(trim($rubro->rubro), 0, $max_chars, 'UTF-8');
                $pdf->Cell($w_rubro, 5, $pdf->toPdf($nombre_corto), 'LTR', 0, 'C', true);

                $pdf->SetXY($x_col, $y_hdr + 5);
                $pdf->SetFont('Arial', '', 7);
                $pdf->Cell($w_rubro, 5, (float)$rubro->porcentaje . '%', 'LBR', 0, 'C', true);

                $pdf->SetXY($x_col + $w_rubro, $y_hdr);
            }
        }

        // Columna TOTAL
        $x_tot = $pdf->GetX();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($w_final, 5, 'TOTAL', 'LTR', 0, 'C', true);
        $pdf->SetXY($x_tot, $y_hdr + 5);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell($w_final, 5, '100%', 'LBR', 0, 'C', true);
        $pdf->SetXY($x_tot + $w_final, $y_hdr);

        // Columna Condición
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($w_condicion, 10, $pdf->toPdf('Condición'), 1, 1, 'C', true);

        $pdf->SetY($y_hdr + 10);

        // Filas de Estudiantes
        $pdf->SetFont('Arial', '', 7.5);
        $i = 1;
        foreach ($alumnos as $alum) {
            $id_mat = $alum->id_matricula;
            $nombre_estudiante = trim($alum->nombre . ' ' . ($alum->apellidos ?? ''));

            // Cédula limpia
            $cedula_val = trim($alum->cedula ?? '');
            if (!preg_match('/[0-9]/', $cedula_val)) {
                $cedula_val = '';
            }

            $pdf->Cell($w_nro, 6.5, $i++, 1, 0, 'C');
            $pdf->Cell($w_nombre, 6.5, $pdf->toPdf(mb_substr($nombre_estudiante, 0, 45, 'UTF-8')), 1, 0, 'L');
            $pdf->Cell($w_cedula, 6.5, $pdf->toPdf($cedula_val), 1, 0, 'C');

            // Notas Parciales
            if ($w_rubro > 0) {
                foreach ($rubros as $rubro) {
                    $val = isset($notas_parciales[$id_mat][$rubro->id_evaluacion]) && $notas_parciales[$id_mat][$rubro->id_evaluacion] !== ''
                        ? number_format((float)$notas_parciales[$id_mat][$rubro->id_evaluacion], 2)
                        : '-';
                    $pdf->Cell($w_rubro, 6.5, $val, 1, 0, 'C');
                }
            }

            // Nota Final y Condición
            $tiene_nota = ($alum->calificacion !== null && $alum->calificacion !== '' && is_numeric($alum->calificacion));
            if ($tiene_nota) {
                $calif = (float)$alum->calificacion;
                $nota_final = number_format($calif, 2);
                $cond = ($calif >= 70) ? 'APROBADO' : 'REPROBADO';
            } else {
                $nota_final = '-';
                $cond = 'OYENTE';
            }

            if ($tiene_nota && $alum->calificacion >= 70) {
                $pdf->SetFont('Arial', 'B', 7.5);
            }
            $pdf->Cell($w_final, 6.5, $nota_final, 1, 0, 'C');
            $pdf->SetFont('Arial', '', 7.5);

            if ($cond === 'APROBADO') {
                $pdf->SetFont('Arial', 'B', 7.5);
            }
            $pdf->Cell($w_condicion, 6.5, $pdf->toPdf($cond), 1, 1, 'C');
            $pdf->SetFont('Arial', '', 7.5);
        }

        // Firmas y Sellos Institucionales (Holgura adicional de 26mm)
        $pdf->Ln(26);
        $y_firma = $pdf->GetY();

        if ($y_firma > 175) {
            $pdf->AddPage('L');
            $y_firma = 45;
            $pdf->SetY($y_firma);
        }

        // Buscar firma del profesor
        $extensions = ['png', 'jpg', 'jpeg', 'gif'];
        $firma_path = "";
        foreach ($extensions as $ext) {
            $temp_path = public_path("uploads/firmas_profesores/firma_curso_{$id_curso}.{$ext}");
            if (!file_exists($temp_path)) {
                $temp_path = dirname(base_path()) . "/uploads/firmas_profesores/firma_curso_{$id_curso}.{$ext}";
            }
            if (file_exists($temp_path)) {
                $firma_path = $temp_path;
                break;
            }
        }

        // Firma Profesor (Izquierda)
        $x_prof_start = 35;
        $w_linea_firma = 85;
        if (!empty($firma_path)) {
            $pdf->Image($firma_path, $x_prof_start + 22.5, $y_firma - 14, 40, 14);
        }
        $pdf->Line($x_prof_start, $y_firma, $x_prof_start + $w_linea_firma, $y_firma);
        $pdf->SetXY($x_prof_start, $y_firma + 2);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($w_linea_firma, 4, $pdf->toPdf('Firma del Profesor'), 0, 1, 'C');
        $pdf->SetX($x_prof_start);
        $pdf->SetFont('Arial', 'B', 7.5);
        $pdf->Cell($w_linea_firma, 4, $pdf->toPdf($nombre_profesor), 0, 0, 'C');

        // Sello y Firma Registro Académico (Derecha)
        $x_reg_start = 165;
        $pdf->Line($x_reg_start, $y_firma, $x_reg_start + $w_linea_firma, $y_firma);
        $pdf->SetXY($x_reg_start, $y_firma + 2);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($w_linea_firma, 4, $pdf->toPdf('Sello y Firma de Registro Académico'), 0, 1, 'C');
        $pdf->SetX($x_reg_start);
        $pdf->SetFont('Arial', 'B', 7.5);
        $pdf->Cell($w_linea_firma, 4, $pdf->toPdf('Universidad Evangélica de las Américas'), 0, 0, 'C');

        // Guardar copia de respaldo en disco
        $uploads_dir = public_path('uploads/actas');
        if (!file_exists($uploads_dir)) {
            @mkdir($uploads_dir, 0777, true);
        }
        $ruta_disco = $uploads_dir . "/acta_curso_{$id_curso}.pdf";
        @$pdf->Output('F', $ruta_disco);

        // Actualizar check_acta en la base de datos
        DB::table('cursos_activos')
            ->where('id_curso_activo', $id_curso)
            ->update(['check_acta' => 1]);

        $filename = "acta_calificaciones_{$id_curso}_" . date('Ymd') . ".pdf";

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
