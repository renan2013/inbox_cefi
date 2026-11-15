<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DescriptorDocService
{
    /**
     * Genera y descarga el Descriptor Oficial de Curso en formato Microsoft Word (.doc) según Modelo CONESUP.
     *
     * @param int $id_plan ID del plan de estudios
     * @return \Illuminate\Http\Response
     */
    public static function generar(int $id_plan)
    {
        $plan = DB::table('plan_estudios as p')
            ->leftJoin('programas as pr', 'p.id_programa', '=', 'pr.id_programa')
            ->select('p.*', 'pr.nombre_programa', 'pr.categoria as facultad_programa')
            ->where('p.id_plan', $id_plan)
            ->first();

        if (!$plan) {
            abort(404, 'Plan de estudios no encontrado.');
        }

        // Convertir logo a base64 para que Word lo renderice offline
        $logo_path = ClienteService::logoPath();

        $logo_base64 = '';
        if (file_exists($logo_path)) {
            $ext = strtolower(pathinfo($logo_path, PATHINFO_EXTENSION));
            if ($ext === 'svg' && file_exists(public_path('imgs/logo.png'))) {
                $logo_path = public_path('imgs/logo.png');
                $ext = 'png';
            }
            $mime = ($ext === 'svg') ? 'image/svg+xml' : (($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg' : 'image/png');
            $logo_data = @file_get_contents($logo_path);
            if ($logo_data !== false) {
                $logo_base64 = "data:{$mime};base64," . base64_encode($logo_data);
            }
        }

        // Cargar sílabo y tablas relacionales CONESUP
        $silabo = null;
        $id_silabo = null;
        try {
            $silabo = DB::table('silabos')->where('id_plan', $id_plan)->orderBy('id_silabo', 'desc')->first();
            if ($silabo) {
                $id_silabo = (int)$silabo->id_silabo;
            }
        } catch (\Throwable $e) {
            $silabo = null;
        }

        $evaluaciones_doc = [];
        if (!empty($plan->evaluaciones_json)) {
            $decEv = json_decode($plan->evaluaciones_json, true);
            if (is_array($decEv)) $evaluaciones_doc = $decEv;
        }
        if (empty($evaluaciones_doc) && $id_silabo) {
            try {
                $evaluaciones_doc = DB::table('silabo_evaluacion')
                    ->where('id_silabo', $id_silabo)
                    ->orderBy('id_evaluacion', 'asc')
                    ->get()
                    ->map(fn($r) => (array)$r)
                    ->toArray();
            } catch (\Throwable $e) {}
        }

        $cronograma_doc = [];
        if (!empty($plan->cronograma_json)) {
            $decCro = json_decode($plan->cronograma_json, true);
            if (is_array($decCro)) $cronograma_doc = $decCro;
        }
        if (empty($cronograma_doc) && $id_silabo) {
            try {
                $cronograma_doc = DB::table('silabo_cronograma')
                    ->where('id_silabo', $id_silabo)
                    ->orderByRaw('CAST(semana AS UNSIGNED) ASC, id_cronograma ASC')
                    ->get()
                    ->map(fn($r) => (array)$r)
                    ->toArray();
            } catch (\Throwable $e) {}
        }
        if (!empty($cronograma_doc)) {
            usort($cronograma_doc, function ($a, $b) {
                $semA = intval(preg_replace('/\D/', '', (string)($a['semana'] ?? '0'))) ?: intval($a['semana'] ?? 0);
                $semB = intval(preg_replace('/\D/', '', (string)($b['semana'] ?? '0'))) ?: intval($b['semana'] ?? 0);
                if ($semA === $semB) {
                    return intval($a['id_cronograma'] ?? 0) <=> intval($b['id_cronograma'] ?? 0);
                }
                return $semA <=> $semB;
            });
        }

        $rubricas_doc = [];
        if (!empty($plan->rubricas_json)) {
            $decRub = json_decode($plan->rubricas_json, true);
            if (is_array($decRub)) $rubricas_doc = $decRub;
        }
        if (empty($rubricas_doc) && $id_silabo) {
            try {
                $rubricas_doc = DB::table('silabo_rubricas')
                    ->where('id_silabo', $id_silabo)
                    ->orderBy('codigo', 'asc')
                    ->get()
                    ->map(fn($r) => (array)$r)
                    ->toArray();
            } catch (\Throwable $e) {}
        }

        $prop = function ($key, $default = '') use ($plan) {
            return (isset($plan->$key) && $plan->$key !== '' && $plan->$key !== null) ? $plan->$key : $default;
        };

        $codigo = htmlspecialchars($prop('codigo', ''));
        $materia = htmlspecialchars($prop('materia', ''));
        $programa = htmlspecialchars($prop('nombre_programa', ''));
        $facultad = htmlspecialchars($prop('facultad', $prop('facultad_programa', '')));

        $codigo_clean = preg_replace('/[^a-zA-Z0-9_-]/', '_', $plan->codigo ?? 'CURSO');
        $nombre_archivo = "descriptor_curso_{$id_plan}_{$codigo_clean}.doc";

        $limpiar_html_doc = function ($html) {
            if (empty(trim(strip_tags((string)$html)))) {
                return '<p style="color: #64748b; font-style: italic; font-size: 10pt; margin: 4pt 0;">No especificado.</p>';
            }
            $html = str_replace(['⚠️', '⚠', '🚨'], '<b>[!]</b> ', (string)$html);
            $html = str_replace(['ℹ️', 'ℹ'], '<b>[i]</b> ', $html);
            $html = str_replace(['✅', '✔️', '☑️', '✓'], '<b>[OK]</b> ', $html);
            return $html;
        };

        $render_seccion = function ($numero, $titulo, $contenido) use ($limpiar_html_doc) {
            if (empty(trim(strip_tags((string)$contenido)))) return '';

            $html = '<div style="margin-top: 18pt; margin-bottom: 8pt;">';
            $html .= '<table width="100%" cellpadding="6" cellspacing="0" style="background-color: #f1f5f9; border-left: 5pt solid #0B3D70; margin-bottom: 8pt;">';
            $html .= '<tr><td style="font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; color: #000000; text-transform: uppercase;">' . $numero . '. ' . htmlspecialchars(mb_strtoupper($titulo, 'UTF-8')) . '</td></tr>';
            $html .= '</table>';
            $html .= '<div style="font-family: Calibri, Arial, sans-serif; font-size: 10.5pt; color: #000000; line-height: 1.45; margin-left: 4pt;">';
            $html .= $limpiar_html_doc($contenido);
            $html .= '</div>';
            $html .= '</div>';
            return $html;
        };

        $render_rubrica_doc_table = function ($json_str) {
            if (empty($json_str)) return '';
            $data = json_decode($json_str, true);
            if (!is_array($data) || empty($data['columnas'])) return '';
            $columnas = $data['columnas'] ?? [];
            $filas = $data['filas'] ?? [];

            $html = '<table width="100%" cellpadding="5" cellspacing="0" style="border-collapse: collapse; margin-top: 6pt; margin-bottom: 14pt; border: 1pt solid #cbd5e1;">';
            $html .= '<tr style="background-color: #f1f5f9;">';
            $html .= '<td style="border: 1pt solid #cbd5e1; font-weight: bold; font-size: 9pt; width: 22%; background-color: #e2e8f0; color: #000000;">Criterio</td>';
            foreach ($columnas as $col) {
                $html .= '<td style="border: 1pt solid #cbd5e1; font-weight: bold; font-size: 9pt; text-align: center; background-color: #f8fafc; color: #000000;">' . htmlspecialchars($col) . '</td>';
            }
            $html .= '</tr>';
            foreach ($filas as $f) {
                $crit = $f['criterio'] ?? ($f['nombre'] ?? 'Criterio');
                $html .= '<tr>';
                $html .= '<td style="border: 1pt solid #cbd5e1; font-weight: bold; font-size: 8.5pt; background-color: #f8fafc; vertical-align: top; color: #000000;">' . htmlspecialchars($crit) . '</td>';
                $vals = $f['valores'] ?? [];
                foreach ($columnas as $idx => $col) {
                    $val = $vals[$idx] ?? '';
                    $html .= '<td style="border: 1pt solid #cbd5e1; font-size: 8pt; line-height: 1.35; vertical-align: top; color: #000000;">' . nl2br(htmlspecialchars($val)) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</table>';
            return $html;
        };

        $extraer_columnas_crono = function ($cro, &$counters_moodle) {
            $semana = $cro['semana'] ?? '';
            $fecha_raw = trim($cro['fecha'] ?? '');
            $fecha_fmt = '';
            if ($fecha_raw !== '' && $fecha_raw !== '0000-00-00') {
                $ts = strtotime($fecha_raw);
                $fecha_fmt = $ts ? date('d/m/Y', $ts) : $fecha_raw;
            }

            $actividad_html = trim($cro['actividad'] ?? '');
            $tareas_html = trim($cro['tareas'] ?? '');
            $contenidos_html = $actividad_html;
            $actividades_ea_html = '';

            $moodle_html = '';
            if (!empty($cro['actividades_moodle']) && $cro['actividades_moodle'] !== '[]') {
                $acts = json_decode($cro['actividades_moodle'], true);
                if (is_array($acts) && !empty($acts)) {
                    foreach ($acts as $act) {
                        $tipo = trim($act['tipo'] ?? 'Actividad');
                        $label = mb_strtoupper($tipo, 'UTF-8');
                        $counters_moodle[$label] = ($counters_moodle[$label] ?? 0) + 1;
                        $num = $counters_moodle[$label];
                        $nombre_act = trim($act['nombre'] ?? '');
                        $intro_act = trim(strip_tags($act['intro'] ?? ''));

                        $moodle_html .= "<div style='margin-top:4px; margin-bottom:4px;'><strong>{$label} ({$num})" . ($nombre_act !== '' ? ": " . htmlspecialchars($nombre_act) : "") . "</strong>";
                        if ($intro_act !== '') {
                            $moodle_html .= "<br>" . nl2br(htmlspecialchars($intro_act));
                        }
                        $moodle_html .= "</div>";
                    }
                }
            }

            if ($moodle_html !== '') {
                $actividades_ea_html = ($actividades_ea_html !== '') ? ($actividades_ea_html . "<br>" . $moodle_html) : $moodle_html;
            } elseif ($tareas_html !== '') {
                $tareas_fmt = (strpos($tareas_html, '<') !== false) ? $tareas_html : nl2br(htmlspecialchars($tareas_html));
                $actividades_ea_html = $tareas_fmt;
            }

            $modalidad_raw = trim($cro['modalidad_trabajo'] ?? 'Asincrónico') ?: 'Asincrónico';
            $es_sincronico = (mb_stripos($modalidad_raw, 'sincr') !== false && mb_stripos($modalidad_raw, 'asincr') === false);

            return [
                'semana' => $semana,
                'fecha_fmt' => $fecha_fmt,
                'contenidos' => $contenidos_html !== '' ? $contenidos_html : '--',
                'actividades' => $actividades_ea_html !== '' ? $actividades_ea_html : '--',
                'es_sincronico' => $es_sincronico,
            ];
        };

        $doc_html = "<!DOCTYPE html>
<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
<head>
    <meta charset='utf-8'>
    <title>{$codigo} - {$materia} | Descriptor Oficial CONESUP</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
            <w:DoNotOptimizeForBrowser/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page Section1 {
            size: 21.0cm 29.7cm;
            margin: 2.0cm 2.0cm 2.0cm 2.0cm;
            mso-header-margin: 36.0pt;
            mso-footer-margin: 36.0pt;
            mso-paper-source: 0;
        }
        div.Section1 { page: Section1; }
        body {
            font-family: 'Calibri', 'Arial', sans-serif;
            font-size: 11pt;
            color: #000000;
            line-height: 1.4;
        }
        h1, h2, h3, h4 {
            font-family: 'Calibri', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        .table-ficha {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10pt;
            margin-bottom: 15pt;
        }
        .table-ficha td {
            border: 1pt solid #cbd5e1;
            padding: 5pt 8pt;
            font-size: 10pt;
            vertical-align: top;
        }
        .table-ficha td.label {
            background-color: #f8fafc;
            font-weight: bold;
            color: #000000;
            width: 32%;
        }
        .table-ficha td.value {
            color: #000000;
        }
        ul, ol { margin-top: 4pt; margin-bottom: 4pt; padding-left: 20pt; }
        li { margin-bottom: 3pt; }
        p { margin-top: 4pt; margin-bottom: 6pt; }
    </style>
</head>
<body>
<div class='Section1'>

    <!-- ENCABEZADO INSTITUCIONAL CON LOGO -->
    <div style='text-align: center; margin-bottom: 16pt;'>";
        if (!empty($logo_base64)) {
            $doc_html .= "<img src='{$logo_base64}' width='200' style='width: 200px; max-width: 100%; height: auto; margin-bottom: 8pt;' alt='" . htmlspecialchars(config('cliente.nombre', 'CEFI')) . "'><br>";
        }
        $nombre_inst = htmlspecialchars(mb_strtoupper(config('cliente.nombre_legal', config('cliente.nombre', 'CEFI')), 'UTF-8'));
        $doc_html .= "
        <h2 style='font-size: 14pt; font-weight: bold; color: #000000; margin-bottom: 2pt; text-transform: uppercase;'>{$nombre_inst}</h2>";
        if (!empty($facultad)) {
            $doc_html .= "<h3 style='font-size: 11.5pt; font-weight: bold; color: #000000; margin-bottom: 2pt;'>{$facultad}</h3>";
        }
        $doc_html .= "
        <h4 style='font-size: 11pt; font-weight: bold; color: #0B3D70; margin-bottom: 4pt;'>{$programa}</h4>
        <p style='font-size: 9.5pt; color: #000000; margin-top: 2pt;'>Programa de Curso Oficial - Modelo CONESUP</p>
    </div>

    <!-- FICHA TÉCNICA OFICIAL (12 CAMPOS CONESUP) -->
    <table class='table-ficha' cellpadding='0' cellspacing='0'>
        <tr>
            <td class='label'>Código</td>
            <td class='value'><strong>{$codigo}</strong></td>
        </tr>
        <tr>
            <td class='label'>Nombre</td>
            <td class='value' style='background-color: #0B3D70; color: #ffffff;'><strong>{$materia}</strong></td>
        </tr>
        <tr>
            <td class='label'>Créditos</td>
            <td class='value'>" . htmlspecialchars((string)$prop('creditos', '4')) . "</td>
        </tr>
        <tr>
            <td class='label'>Duración</td>
            <td class='value'>" . htmlspecialchars((string)$prop('duracion', '15 semanas')) . "</td>
        </tr>
        <tr>
            <td class='label'>Distribución horas virtuales por semana</td>
            <td class='value'>" . htmlspecialchars((string)$prop('distribucion_horas', 'Este curso comprende un total de 12 horas distribuidas en 3 horas teóricas, 1 hora práctica y 8 horas de estudio independiente.')) . "</td>
        </tr>
        <tr>
            <td class='label'>Modalidad</td>
            <td class='value'>" . htmlspecialchars((string)$prop('modalidad', 'Virtual (aprendizaje electrónico)')) . "</td>
        </tr>
        <tr>
            <td class='label'>Naturaleza</td>
            <td class='value'>" . htmlspecialchars((string)$prop('naturaleza', 'Teórico-práctica')) . "</td>
        </tr>
        <tr>
            <td class='label'>Ubicación</td>
            <td class='value'>" . htmlspecialchars((string)$prop('cuatrimestre', '')) . "</td>
        </tr>
        <tr>
            <td class='label'>Requisitos</td>
            <td class='value'>" . htmlspecialchars((string)$prop('requisitos', 'No tiene')) . "</td>
        </tr>
        <tr>
            <td class='label'>Correquisitos</td>
            <td class='value'>" . htmlspecialchars((string)$prop('correquisitos', 'No tiene')) . "</td>
        </tr>
        <tr>
            <td class='label'>Nivel</td>
            <td class='value'>" . htmlspecialchars((string)$prop('nivel', $prop('facultad_programa', 'Maestría'))) . "</td>
        </tr>
        <tr>
            <td class='label'>Profesor</td>
            <td class='value'>" . htmlspecialchars((string)$prop('profesor', '--')) . "</td>
        </tr>
    </table>

    <!-- SECCIONES CURRICULARES I A VI -->
    " . $render_seccion('I', 'Descripción general del curso', $prop('descripcion_curso', $silabo->descripcion ?? '')) . "
    " . $render_seccion('II', 'Objetivo general', $prop('objetivo_general', $silabo->objetivo_general ?? '')) . "
    " . $render_seccion('III', 'Objetivos específicos', $prop('objetivos_especificos', $silabo->objetivos_especificos ?? '')) . "
    " . $render_seccion('IV', 'Contenido conceptual', $prop('contenidos_tematicos', $silabo->contenidos ?? '')) . "
    " . $render_seccion('V', 'Metodología de enseñanza', $prop('metodologia_ensenanza', $silabo->metodologia ?? '')) . "
    " . $render_seccion('VI', 'Estrategias de aprendizaje', $prop('estrategias_aprendizaje', $silabo->estrategias_aprendizaje ?? '')) . "

    <!-- SECCIÓN VII: EVALUACIÓN DE LOS APRENDIZAJES -->
    <div style='margin-top: 18pt; margin-bottom: 8pt;'>
        <table width='100%' cellpadding='6' cellspacing='0' style='background-color: #f1f5f9; border-left: 5pt solid #0B3D70; margin-bottom: 8pt;'>
            <tr><td style='font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; color: #000000; text-transform: uppercase;'>VII. EVALUACIÓN DE LOS APRENDIZAJES</td></tr>
        </table>
        <div style='font-family: Calibri, Arial, sans-serif; font-size: 10.5pt; color: #000000; line-height: 1.45; margin-left: 4pt;'>";

        $eval_texto = $prop('evaluacion_aprendizajes', '');
        if (!empty($eval_texto) && (empty($evaluaciones_doc) || stripos($eval_texto, '<table') === false)) {
            $doc_html .= $limpiar_html_doc($eval_texto);
        }

        if (!empty($evaluaciones_doc)) {
            $doc_html .= "
            <table width='85%' cellpadding='6' cellspacing='0' style='border-collapse: collapse; margin-top: 8pt; margin-bottom: 12pt; border: 1.5pt solid #000000;'>
                <tr style='background-color: #f1f5f9; border-bottom: 2pt solid #000000;'>
                    <td style='font-weight: bold; font-size: 10pt; width: 70%; border: 1pt solid #cbd5e1; color: #000000;'>Estrategia evaluativa</td>
                    <td style='font-weight: bold; font-size: 10pt; width: 30%; text-align: center; border: 1pt solid #cbd5e1; color: #000000;'>Porcentaje</td>
                </tr>";
            $tot_p = 0;
            foreach ($evaluaciones_doc as $ev) {
                $p = (float)($ev['porcentaje'] ?? 0);
                $tot_p += $p;
                $nom_ev = trim($ev['rubro'] ?? '');
                $cant_ev = (int)($ev['cantidad'] ?? 1);
                $extra_cant = ($cant_ev > 1) ? " ({$cant_ev} entregables)" : "";
                $doc_html .= "
                <tr>
                    <td style='font-size: 9.5pt; padding: 5pt 8pt; border: 1pt solid #cbd5e1; font-weight: 600; color: #000000;'>" . htmlspecialchars($nom_ev . $extra_cant) . "</td>
                    <td style='font-size: 9.5pt; padding: 5pt 8pt; border: 1pt solid #cbd5e1; text-align: center; font-weight: bold; color: #000000;'>" . (int)$p . "%</td>
                </tr>";
            }
            $doc_html .= "
                <tr style='background-color: #f8fafc; font-weight: bold; border-top: 1.5pt solid #000000;'>
                    <td style='font-size: 10pt; padding: 5pt 8pt; text-align: right; border: 1pt solid #cbd5e1; color: #000000;'>Total</td>
                    <td style='font-size: 10pt; padding: 5pt 8pt; text-align: center; border: 1pt solid #cbd5e1; color: #0B3D70; font-weight: bold;'>" . round($tot_p) . "%</td>
                </tr>
            </table>";
        }

        $doc_html .= "
        </div>
    </div>

    <!-- SECCIÓN VIII: RECURSOS DIDÁCTICOS -->
    " . $render_seccion('VIII', 'Recursos didácticos', $prop('recursos_didacticos', $silabo->recursos_aprendizaje ?? '')) . "

    <!-- SECCIÓN IX: CRONOGRAMA DE 15 SEMANAS -->
    <div style='margin-top: 18pt; margin-bottom: 8pt;'>
        <table width='100%' cellpadding='6' cellspacing='0' style='background-color: #f1f5f9; border-left: 5pt solid #0B3D70; margin-bottom: 8pt;'>
            <tr><td style='font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; color: #000000; text-transform: uppercase;'>IX. CRONOGRAMA</td></tr>
        </table>
        <div style='font-family: Calibri, Arial, sans-serif; font-size: 10.5pt; color: #000000; line-height: 1.45; margin-left: 4pt;'>";

        $crono_texto = $prop('cronograma', $silabo->cronograma ?? '');
        if (!empty($crono_texto) && (empty($cronograma_doc) || stripos($crono_texto, '<table') === false)) {
            $doc_html .= $limpiar_html_doc($crono_texto);
        }

        if (!empty($cronograma_doc)) {
            $doc_html .= "
            <table width='100%' cellpadding='6' cellspacing='0' style='border-collapse: collapse; margin-top: 8pt; margin-bottom: 12pt; border: 1.5pt solid #000000;'>
                <tr style='background-color: #f1f5f9; border-bottom: 2pt solid #000000;'>
                    <td style='font-weight: bold; font-size: 9.5pt; width: 12%; text-align: center; border: 1pt solid #cbd5e1; color: #000000;'>Semana</td>
                    <td style='font-weight: bold; font-size: 9.5pt; width: 37%; border: 1pt solid #cbd5e1; color: #000000;'>Contenidos</td>
                    <td style='font-weight: bold; font-size: 9.5pt; width: 37%; border: 1pt solid #cbd5e1; color: #000000;'>Actividades de enseñanza-aprendizaje</td>
                    <td style='font-weight: bold; font-size: 9.5pt; width: 14%; text-align: center; border: 1pt solid #cbd5e1; color: #000000;'>Modalidad</td>
                </tr>";
            $counters_doc_moodle = [];
            foreach ($cronograma_doc as $idx => $cro) {
                $cols_doc = $extraer_columnas_crono($cro, $counters_doc_moodle);
                $fec_txt = !empty($cols_doc['fecha_fmt']) ? "<br><span style='font-size: 8pt; color: #000000;'>(" . htmlspecialchars($cols_doc['fecha_fmt']) . ")</span>" : "";
                $doc_html .= "
                <tr>
                    <td style='font-size: 9pt; padding: 6pt; border: 1pt solid #cbd5e1; text-align: center; vertical-align: top; background-color: #f8fafc; font-weight: bold; color: #000000;'>
                        Semana " . ($cols_doc['semana'] ?: ($idx + 1)) . "{$fec_txt}
                    </td>
                    <td style='font-size: 9pt; padding: 6pt; border: 1pt solid #cbd5e1; vertical-align: top; color: #000000;'>
                        {$cols_doc['contenidos']}
                    </td>
                    <td style='font-size: 9pt; padding: 6pt; border: 1pt solid #cbd5e1; vertical-align: top; color: #000000;'>
                        {$cols_doc['actividades']}
                    </td>";
                if ($cols_doc['es_sincronico']) {
                    $doc_html .= "
                    <td style='font-size: 8.5pt; padding: 6pt; border: 1pt solid #cbd5e1; text-align: center; vertical-align: middle; background-color: #0e71eb; color: #ffffff; font-weight: bold;'>
                        SINCRÓNICO<br><span style='font-size: 7.5pt; font-weight: normal;'>(Zoom)</span>
                    </td>";
                } else {
                    $doc_html .= "
                    <td style='font-size: 8.5pt; padding: 6pt; border: 1pt solid #cbd5e1; text-align: center; vertical-align: middle; background-color: #f8fafc; color: #000000; font-weight: bold;'>
                        ASINCRÓNICO
                    </td>";
                }
                $doc_html .= "</tr>";
            }
            $doc_html .= "</table>";
        }

        $doc_html .= "
        </div>
    </div>";

        // DESCRIPCIÓN DE LAS ESTRATEGIAS EVALUATIVAS Y RÚBRICAS
        if (!empty($rubricas_doc)) {
            $doc_html .= "
    <div style='margin-top: 22pt; margin-bottom: 8pt;'>
        <table width='100%' cellpadding='6' cellspacing='0' style='background-color: #f1f5f9; border-left: 5pt solid #0B3D70; margin-bottom: 8pt;'>
            <tr><td style='font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; color: #000000; text-transform: uppercase;'>DESCRIPCIÓN DE LAS ESTRATEGIAS EVALUATIVAS Y RÚBRICAS</td></tr>
        </table>
        <div style='font-family: Calibri, Arial, sans-serif; font-size: 10.5pt; color: #000000; line-height: 1.45; margin-left: 4pt;'>";
            foreach ($rubricas_doc as $idx_r => $rub) {
                $num_r = $idx_r + 1;
                $nom_r = htmlspecialchars($rub['nombre'] ?? 'Rúbrica');
                $porc_r = (float)($rub['porcentaje_total'] ?? 0);
                $desc_ent = $rub['descripcion_entregable'] ?? '';
                if (empty($desc_ent) && !empty($rub['contenido_json'])) {
                    $dec = json_decode($rub['contenido_json'], true);
                    if (is_array($dec) && !empty($dec['descripcion_entregable'])) {
                        $desc_ent = $dec['descripcion_entregable'];
                    }
                }
                $doc_html .= "
            <div style='margin-bottom: 16pt;'>
                <p style='font-size: 10.5pt; font-weight: bold; color: #000000; margin-bottom: 4pt;'>
                    {$num_r}. {$nom_r} (" . (int)$porc_r . "%)
                </p>";
                if (!empty($desc_ent)) {
                    $doc_html .= "
                <p style='font-size: 9.5pt; color: #000000; margin-bottom: 6pt; line-height: 1.45;'>
                    " . nl2br(htmlspecialchars($desc_ent)) . "
                </p>";
                }
                $doc_html .= $render_rubrica_doc_table($rub['contenido_json'] ?? '');
                $doc_html .= "
            </div>";
            }
            $doc_html .= "
        </div>
    </div>";
        } elseif (!empty($prop('guias_evaluacion', ''))) {
            $doc_html .= $render_seccion('X', 'Descripción de las estrategias evaluativas y rúbricas', $prop('guias_evaluacion', ''));
        }

        // SECCIÓN X: BIBLIOGRAFÍA OBLIGATORIA
        $biblio_obligatoria = $prop('bibliografia', $silabo->bibliografia ?? '');
        if (!empty($biblio_obligatoria)) {
            $doc_html .= $render_seccion('X', 'Bibliografía obligatoria: ' . trim(($plan->codigo ?? '') . ' ' . ($plan->materia ?? '')), $biblio_obligatoria);
        }

        // SECCIÓN XI: BIBLIOGRAFÍA COMPLEMENTARIA
        $biblio_comp = $prop('bibliografia_complementaria', '');
        if (!empty($biblio_comp)) {
            $doc_html .= $render_seccion('XI', 'Bibliografía complementaria: ' . trim(($plan->codigo ?? '') . ' ' . ($plan->materia ?? '')), $biblio_comp);
        }

        $doc_html .= "
    <!-- PIE DE PÁGINA INSTITUCIONAL -->
    <div style='margin-top: 30pt; padding-top: 10pt; border-top: 1pt solid #cbd5e1; font-size: 8.5pt; color: #94a3b8; text-align: center;'>
        " . config('cliente.nombre', 'CEFI') . " — " . config('cliente.nombre_legal', config('cliente.nombre', 'CEFI')) . " | Documento Descriptor Oficial de Curso (Modelo CONESUP)
    </div>

</div>
</body>
</html>";

        return response($doc_html, 200)
            ->header('Content-Type', 'application/vnd.ms-word; charset=utf-8')
            ->header('Content-Length', (string)strlen($doc_html))
            ->header('Content-Disposition', 'attachment; filename="' . $nombre_archivo . '"')
            ->header('Cache-Control', 'max-age=0, no-cache, must-revalidate, proxy-revalidate')
            ->header('Pragma', 'public');
    }
}
