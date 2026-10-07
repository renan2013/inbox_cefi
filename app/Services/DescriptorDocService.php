<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DescriptorDocService
{
    /**
     * Genera y descarga el Descriptor Oficial de Curso en formato Microsoft Word (.doc).
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
        $logo_path = public_path(ltrim(config('cliente.logo_url', 'imgs/logo.png'), '/'));
        if (!file_exists($logo_path)) {
            $logo_path = public_path('imgs/logo.png');
        }

        $logo_base64 = '';
        if (file_exists($logo_path)) {
            $logo_data = file_get_contents($logo_path);
            $logo_base64 = 'data:image/png;base64,' . base64_encode($logo_data);
        }

        $codigo = htmlspecialchars($plan->codigo ?? '');
        $materia = htmlspecialchars($plan->materia ?? '');
        $programa = htmlspecialchars($plan->nombre_programa ?? '');
        $facultad = htmlspecialchars($plan->facultad ?: ($plan->facultad_programa ?? ''));

        $codigo_clean = preg_replace('/[^a-zA-Z0-9_-]/', '_', $plan->codigo ?? 'CURSO');
        $nombre_archivo = "descriptor_curso_{$id_plan}_{$codigo_clean}.doc";

        // Helper para secciones
        $render_seccion = function ($numero, $titulo, $contenido) {
            if (empty(trim(strip_tags($contenido ?? '')))) return '';
            
            // Reemplazo de emojis comunes
            $clean_content = str_replace(['⚠️', '⚠', '🚨'], '<b>[!]</b> ', $contenido);
            $clean_content = str_replace(['ℹ️', 'ℹ'], '<b>[i]</b> ', $clean_content);
            $clean_content = str_replace(['✅', '✔️', '☑️', '✓'], '<b>[OK]</b> ', $clean_content);

            $html = '<div style="margin-top: 18pt; margin-bottom: 8pt;">';
            $html .= '<table width="100%" cellpadding="6" cellspacing="0" style="background-color: #f1f5f9; border-left: 5pt solid #5fb230; margin-bottom: 8pt;">';
            $html .= '<tr><td style="font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; color: #0f172a; text-transform: uppercase;">' . $numero . '. ' . htmlspecialchars(mb_strtoupper($titulo, 'UTF-8')) . '</td></tr>';
            $html .= '</table>';
            $html .= '<div style="font-family: Calibri, Arial, sans-serif; font-size: 10.5pt; color: #334155; line-height: 1.45; margin-left: 4pt;">';
            $html .= $clean_content;
            $html .= '</div>';
            $html .= '</div>';
            return $html;
        };

        $doc_html = "<!DOCTYPE html>
<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
<head>
    <meta charset='utf-8'>
    <title>{$codigo} - {$materia} | Descriptor Oficial</title>
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
        div.Section1 {
            page: Section1;
        }
        body {
            font-family: 'Calibri', 'Arial', sans-serif;
            font-size: 11pt;
            color: #1e293b;
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
            color: #334155;
            width: 32%;
        }
        .table-ficha td.value {
            color: #0f172a;
        }
        ul, ol {
            margin-top: 4pt;
            margin-bottom: 4pt;
            padding-left: 20pt;
        }
        li {
            margin-bottom: 3pt;
        }
        p {
            margin-top: 4pt;
            margin-bottom: 6pt;
        }
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
        <h2 style='font-size: 14pt; font-weight: bold; color: #0f172a; margin-bottom: 2pt; text-transform: uppercase;'>{$nombre_inst}</h2>";
        if (!empty($facultad)) {
            $doc_html .= "<h3 style='font-size: 11.5pt; font-weight: bold; color: #475569; margin-bottom: 2pt;'>{$facultad}</h3>";
        }
        $doc_html .= "
        <h4 style='font-size: 11pt; font-weight: bold; color: #5fb230; margin-bottom: 4pt;'>{$programa}</h4>
        <p style='font-size: 9.5pt; color: #64748b; margin-top: 2pt;'>Descriptor Oficial del Curso</p>
    </div>

    <!-- FICHA TÉCNICA OFICIAL -->
    <table class='table-ficha' cellpadding='0' cellspacing='0'>
        <tr>
            <td class='label'>Código</td>
            <td class='value'><strong>{$codigo}</strong></td>
        </tr>
        <tr>
            <td class='label'>Nombre del Curso</td>
            <td class='value'><strong>{$materia}</strong></td>
        </tr>
        <tr>
            <td class='label'>Créditos</td>
            <td class='value'>" . htmlspecialchars($plan->creditos ?: '4') . "</td>
        </tr>
        <tr>
            <td class='label'>Duración</td>
            <td class='value'>" . htmlspecialchars($plan->duracion ?: '15 semanas') . "</td>
        </tr>
        <tr>
            <td class='label'>Distribución horas por semana</td>
            <td class='value'>" . htmlspecialchars($plan->distribucion_horas ?: 'Este curso comprende un total de 12 horas distribuidas en 3 horas teóricas, 1 hora práctica y 8 horas de estudio independiente.') . "</td>
        </tr>
        <tr>
            <td class='label'>Modalidad</td>
            <td class='value'>" . htmlspecialchars($plan->modalidad ?: 'Virtual (aprendizaje electrónico)') . "</td>
        </tr>
        <tr>
            <td class='label'>Naturaleza</td>
            <td class='value'>" . htmlspecialchars($plan->naturaleza ?: 'Teórico-práctica') . "</td>
        </tr>
        <tr>
            <td class='label'>Ubicación</td>
            <td class='value'>" . htmlspecialchars($plan->cuatrimestre ?? '') . "</td>
        </tr>
        <tr>
            <td class='label'>Requisitos</td>
            <td class='value'>" . htmlspecialchars($plan->requisitos ?: 'No tiene') . "</td>
        </tr>
        <tr>
            <td class='label'>Correquisitos</td>
            <td class='value'>" . htmlspecialchars($plan->correquisitos ?: 'No tiene') . "</td>
        </tr>
        <tr>
            <td class='label'>Nivel</td>
            <td class='value'>" . htmlspecialchars($plan->nivel ?: ($plan->categoria ?? 'Maestría')) . "</td>
        </tr>
        <tr>
            <td class='label'>Profesor</td>
            <td class='value'>" . htmlspecialchars($plan->profesor ?: '--') . "</td>
        </tr>
    </table>

    <!-- SECCIONES CURRICULARES I A XI -->
    " . $render_seccion('I', 'Descripción del curso', $plan->descripcion_curso ?? '') . "
    " . $render_seccion('II', 'Objetivos generales', $plan->objetivo_general ?? '') . "
    " . $render_seccion('III', 'Objetivos específicos', $plan->objetivos_especificos ?? '') . "
    " . $render_seccion('IV', 'Contenidos temáticos', $plan->contenidos_tematicos ?? '') . "
    " . $render_seccion('V', 'Metodología de enseñanza', $plan->metodologia_ensenanza ?? '') . "
    " . $render_seccion('VI', 'Estrategias de aprendizaje', $plan->estrategias_aprendizaje ?? '') . "
    " . $render_seccion('VII', 'Evaluación de los aprendizajes', $plan->evaluacion_aprendizajes ?? '') . "
    " . $render_seccion('VIII', 'Recursos didácticos', $plan->recursos_didacticos ?? '') . "
    " . $render_seccion('IX', 'Cronograma', $plan->cronograma ?? '') . "
    " . $render_seccion('X', 'Las guías de evaluación', $plan->guias_evaluacion ?? '') . "
    " . $render_seccion('XI', 'Bibliografía', $plan->bibliografia ?? '') . "

    <!-- PIE DE PÁGINA INSTITUCIONAL -->
    <div style='margin-top: 30pt; padding-top: 10pt; border-top: 1pt solid #cbd5e1; font-size: 8.5pt; color: #94a3b8; text-align: center;'>
        " . config('cliente.nombre', 'CEFI') . " — " . config('cliente.nombre_legal', config('cliente.nombre', 'CEFI')) . " | Documento Descriptor Oficial de Curso
    </div>

</div>
</body>
</html>";

        return response($doc_html, 200)
            ->header('Content-Type', 'application/vnd.ms-word; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $nombre_archivo . '"')
            ->header('Cache-Control', 'max-age=0, no-cache, must-revalidate, proxy-revalidate')
            ->header('Pragma', 'public');
    }
}
