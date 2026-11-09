<?php

namespace App\Services;

require_once __DIR__ . '/DescriptorPdfService.php';

use Illuminate\Support\Facades\DB;

class SilaboPdfService
{
    /**
     * Genera y transmite el Programa / Sílabo Oficial del Curso en PDF,
     * utilizando exactamente el mismo diseño institucional que DescriptorPdfService.
     *
     * @param int $id_curso ID del curso activo
     * @param int|null $id_plan ID del plan de estudios (opcional)
     * @param string $dest 'I' inline, 'D' download, 'F' save
     * @return mixed
     */
    public static function generar(int $id_curso, ?int $id_plan = null, string $dest = 'I')
    {
        // 1. Obtener curso activo y plan de estudios
        $curso = DB::table('cursos_activos as ca')
            ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
            ->leftJoin('programas as p', 'pe.id_programa', '=', 'p.id_programa')
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
                'pe.facultad as plan_facultad',
                'pe.profesor as plan_profesor',
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
                'p.categoria as facultad_programa',
                'p.normas_netiqueta',
                'u.nombre as prof_nombre',
                'u.apellidos as prof_apellidos',
                'u.email as prof_correo'
            )
            ->where('ca.id_curso_activo', $id_curso)
            ->first();

        if (!$curso) {
            if ($id_plan) {
                return DescriptorPdfService::generar($id_plan, $dest);
            }
            abort(404, 'Curso activo no encontrado.');
        }

        $prof_id = $curso->id_profesor ?? 0;

        // 2. Buscar sílabo activo (cascada: profesor asignado -> plantilla del plan)
        $silabo = null;
        try {
            $silabo = DB::table('silabos')
                ->where('id_plan', $curso->id_plan)
                ->orderByRaw('(id_profesor = ?) DESC', [$prof_id])
                ->orderBy('id_silabo', 'desc')
                ->first();
        } catch (\Throwable $e) {
            $silabo = null;
        }

        // Helper con prioridad: sílabo (si tiene contenido real) -> plan_estudios -> default
        $pickText = function (?string $silaboVal, ?string $planVal, string $default = '') {
            if ($silaboVal !== null && trim(strip_tags($silaboVal)) !== '') {
                return $silaboVal;
            }
            if ($planVal !== null && trim(strip_tags($planVal)) !== '') {
                return $planVal;
            }
            return $default;
        };

        // Construir HTML de Evaluación si el sílabo tiene filas propias y el texto HTML está vacío
        $evaluacionHtml = (string)($curso->plan_evaluacion ?? '');
        if ($silabo) {
            try {
                $evalRows = DB::table('silabo_evaluacion')
                    ->where('id_silabo', $silabo->id_silabo)
                    ->orderBy('id_evaluacion', 'asc')
                    ->get();
                if ($evalRows->isNotEmpty()) {
                    $tableEval = '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse:collapse;">'
                        . '<thead><tr style="background-color:#f2f2f2;">'
                        . '<th style="width:75%; text-align:left;">Rubro de Evaluación</th>'
                        . '<th style="width:25%; text-align:center;">Porcentaje</th>'
                        . '</tr></thead><tbody>';
                    $totalPct = 0;
                    foreach ($evalRows as $ev) {
                        $rub = htmlspecialchars((string)$ev->rubro, ENT_QUOTES, 'UTF-8');
                        $cant = (int)($ev->cantidad ?? 1);
                        $pct = (float)($ev->porcentaje ?? 0);
                        $totalPct += $pct;
                        $cantTxt = $cant > 1 ? " ({$cant} entregas)" : '';
                        $tableEval .= "<tr><td>{$rub}{$cantTxt}</td><td style=\"text-align:center;\">{$pct}%</td></tr>";
                    }
                    $tableEval .= "<tr style=\"font-weight:bold; background-color:#e9ecef;\"><td style=\"text-align:right;\">TOTAL:</td><td style=\"text-align:center;\">{$totalPct}%</td></tr></tbody></table>";
                    $evaluacionHtml = $tableEval;
                }
            } catch (\Throwable $e) {
                // Conservar $evaluacionHtml del plan
            }
        }

        // Construir HTML de Cronograma si el sílabo tiene semanas con actividades
        $cronogramaHtml = (string)($curso->plan_cronograma ?? '');
        if ($silabo) {
            try {
                $cronRows = DB::table('silabo_cronograma')
                    ->where('id_silabo', $silabo->id_silabo)
                    ->orderByRaw('CAST(semana AS UNSIGNED) ASC')
                    ->get();
                $tieneContenidoCron = $cronRows->contains(function ($r) {
                    return trim(strip_tags((string)($r->actividad ?? ''))) !== '' || trim(strip_tags((string)($r->tareas ?? ''))) !== '';
                });
                if ($tieneContenidoCron) {
                    $tableCron = '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse:collapse;">'
                        . '<thead><tr style="background-color:#f2f2f2;">'
                        . '<th style="width:12%; text-align:center;">Semana</th>'
                        . '<th style="width:38%; text-align:left;">Actividad / Tema</th>'
                        . '<th style="width:32%; text-align:left;">Tareas / Asignaciones</th>'
                        . '<th style="width:18%; text-align:center;">Modalidad</th>'
                        . '</tr></thead><tbody>';
                    foreach ($cronRows as $cr) {
                        $semTxt = 'Semana ' . $cr->semana;
                        if (!empty($cr->fecha)) {
                            $ts = strtotime($cr->fecha);
                            $semTxt .= "\n(" . ($ts ? date('d/m/Y', $ts) : $cr->fecha) . ')';
                        }
                        $act = (string)($cr->actividad ?? '');
                        $tar = (string)($cr->tareas ?? '');
                        $mod = htmlspecialchars((string)($cr->modalidad_trabajo ?: 'Presencial'), ENT_QUOTES, 'UTF-8');
                        $tableCron .= "<tr><td style=\"text-align:center;\">{$semTxt}</td><td>{$act}</td><td>{$tar}</td><td style=\"text-align:center;\">{$mod}</td></tr>";
                    }
                    $tableCron .= '</tbody></table>';
                    $cronogramaHtml = $tableCron;
                }
            } catch (\Throwable $e) {
                // Conservar $cronogramaHtml del plan
            }
        }

        // 3. Instanciar el mismo motor visual DescriptorPDF
        $pdf = new DescriptorPDF();
        $pdf->codigo = (string)($curso->codigo ?? '');
        $pdf->materia = (string)($curso->materia ?? '');
        $pdf->programa = (string)($curso->nombre_programa ?? '');
        $pdf->facultad = (string)(!empty($curso->plan_facultad) ? $curso->plan_facultad : ($curso->facultad_programa ?? ''));

        $pdf->AliasNbPages();
        $pdf->SetMargins(12, 12, 12);
        $pdf->AddPage();

        // Logo oficial del cliente al 200% centrado
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

        $nombre_profesor = trim(($curso->prof_nombre ?? '') . ' ' . ($curso->prof_apellidos ?? ''));
        if (empty($nombre_profesor)) {
            $nombre_profesor = !empty($curso->plan_profesor) ? $curso->plan_profesor : '--';
        }

        $creditos_num = (int)($curso->creditos ?? 0);

        $ficha = [
            ['Código', (string)($curso->codigo ?? '')],
            ['Nombre del Curso', (string)($curso->materia ?? '')],
        ];
        if ($creditos_num > 0) {
            $ficha[] = ['Créditos', (string)$creditos_num];
        }
        $ficha = array_merge($ficha, [
            ['Duración', (string)(!empty($curso->plan_duracion) ? $curso->plan_duracion : '15 semanas')],
            ['Distribución horas por semana', (string)(!empty($curso->plan_distribucion_horas) ? $curso->plan_distribucion_horas : '12 horas (3 teóricas, 1 práctica, 8 independientes)')],
            ['Modalidad', (string)(!empty($curso->modalidad) ? $curso->modalidad : (!empty($curso->plan_modalidad) ? $curso->plan_modalidad : 'Presencial'))],
            ['Naturaleza', (string)(!empty($curso->plan_naturaleza) ? $curso->plan_naturaleza : 'Teórico-práctica')],
            ['Ubicación', (string)(!empty($curso->cuatrimestre) ? $curso->cuatrimestre : ($curso->periodo ?? ''))],
            ['Requisitos', (string)(!empty($curso->requisitos) ? $curso->requisitos : 'No tiene')],
            ['Correquisitos', (string)(!empty($curso->correquisitos) ? $curso->correquisitos : 'No tiene')],
            ['Nivel', (string)(!empty($curso->plan_nivel) ? $curso->plan_nivel : ($curso->facultad_programa ?? 'Carrera Técnica'))],
            ['Profesor', (string)$nombre_profesor],
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

            $pdf->SetDrawColor(191, 211, 238);
            $pdf->SetFillColor($pdf->azul_claro[0], $pdf->azul_claro[1], $pdf->azul_claro[2]);
            $pdf->Rect($x, $y, $w_label, $row_h, 'DF');

            $acento = ($idx % 2 === 0) ? $pdf->verde_cefi : $pdf->azul_cefi;
            $pdf->SetFillColor($acento[0], $acento[1], $acento[2]);
            $pdf->Rect($x, $y, 1.2, $row_h, 'F');

            if ($idx % 2 === 1) {
                $pdf->SetFillColor(250, 252, 255);
            } else {
                $pdf->SetFillColor(255, 255, 255);
            }
            $pdf->Rect($x + $w_label, $y, $w_value, $row_h, 'DF');

            $pad_label = ($row_h - ($lines_label * $line_h)) / 2;
            $pdf->SetXY($x + 3.5, $y + $pad_label);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor($pdf->azul_cefi[0], $pdf->azul_cefi[1], $pdf->azul_cefi[2]);
            $pdf->MultiCell($w_label - 6, $line_h, $label_txt, 0, 'L');

            $pad_val = ($row_h - ($lines_val * $line_h)) / 2;
            $pdf->SetXY($x + $w_label + 3, $y + $pad_val);
            $pdf->SetFont('Arial', ($idx <= 1) ? 'B' : '', 9);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->MultiCell($w_value - 6, $line_h, $val_txt, 0, 'L');

            $pdf->SetY($y + $row_h);
        }

        // Pasar las secciones curriculares a la segunda página para que la Ficha Técnica quede independiente
        $secciones = [
            ['I', 'Descripción del curso', $pickText($silabo->descripcion ?? null, $curso->plan_descripcion ?? null)],
            ['II', 'Objetivos generales', $pickText($silabo->objetivo_general ?? null, $curso->plan_objetivo_general ?? null)],
            ['III', 'Objetivos específicos', $pickText($silabo->objetivos_especificos ?? null, $curso->plan_objetivos_especificos ?? null)],
            ['IV', 'Contenidos temáticos', $pickText($silabo->contenidos ?? null, $curso->plan_contenidos ?? null)],
            ['V', 'Metodología de enseñanza', $pickText($silabo->metodologia ?? null, $curso->plan_metodologia ?? null)],
            ['VI', 'Estrategias de aprendizaje', $pickText($silabo->estrategias_aprendizaje ?? null, $curso->plan_estrategias ?? null)],
            ['VII', 'Evaluación de los aprendizajes', $evaluacionHtml],
            ['VIII', 'Recursos didácticos', $pickText($silabo->recursos_aprendizaje ?? null, $curso->plan_recursos ?? null)],
            ['IX', 'Cronograma', $cronogramaHtml],
            ['X', 'Las guías de evaluación', (string)($curso->plan_guias ?? '')],
            ['XI', 'Bibliografía', $pickText($silabo->bibliografia ?? null, $curso->plan_bibliografia ?? null)],
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

        $filename = "silabo_curso_{$id_curso}.pdf";
        $pdfContent = $pdf->Output('S');

        if ($dest === 'F') {
            $dir = public_path('uploads/silabos');
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
