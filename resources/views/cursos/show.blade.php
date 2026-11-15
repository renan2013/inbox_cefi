@extends('layouts.app')

@section('title', 'Inbox BPM - Descriptor y Ficha Técnica: ' . ($curso->materia ?? ''))

@section('styles')
<style>
    :root {
        --primary-color: #1066AD;
        --primary-hover: #0d5490;
        --text-dark: #000000;
        --bg-light: #f8fafc;
    }

    body {
        background-color: var(--bg-light) !important;
        color: #000000 !important;
    }

    .main-content-container {
        padding-top: 2rem;
        padding-bottom: 5rem;
    }

    .page-header {
        background: white !important;
        padding: 1.5rem 2rem;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
        border: 1px solid #e2e8f0 !important;
    }

    .section-title {
        font-weight: 800;
        color: #1e293b !important;
        letter-spacing: -0.025em;
    }

    .document-card {
        background: #ffffff !important;
        border-radius: 1rem;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        padding: 3.5rem 4rem;
        max-width: 920px;
        margin: 0 auto;
        color: #000000 !important;
    }

    .header-institucional {
        text-align: center;
        margin-bottom: 35px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e2e8f0;
    }

    .logo-inst-silabo {
        max-height: 85px;
        width: auto;
        object-fit: contain;
        margin-bottom: 1rem;
    }

    .header-institucional h3 {
        font-size: 1.25rem;
        font-weight: 800;
        margin-bottom: 6px;
        color: #000000 !important;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .header-institucional h4 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 6px;
        color: #000000 !important;
    }
    .header-institucional h5 {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--primary-color) !important;
    }

    .tabla-descriptor {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 35px;
        border: 1.5px solid #000000;
    }
    .tabla-descriptor th, .tabla-descriptor td {
        border: 1px solid #000000;
        padding: 9px 14px;
        font-size: 0.92rem;
        vertical-align: middle;
    }
    .tabla-descriptor th {
        background-color: #f1f5f9 !important;
        font-weight: 700;
        text-align: right;
        width: 32%;
        color: #000000 !important;
    }
    .tabla-descriptor td {
        background-color: #ffffff;
        color: #000000;
    }

    .seccion-curricular {
        margin-bottom: 28px;
        padding-bottom: 16px;
        border-bottom: 1px dashed #e2e8f0;
    }
    .seccion-curricular:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .seccion-titulo {
        font-weight: 800;
        font-size: 1.02rem;
        margin-bottom: 10px;
        color: #000000 !important;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .seccion-contenido {
        padding-left: 10px;
        font-size: 0.94rem;
        color: #000000 !important;
        line-height: 1.65;
    }
    .seccion-contenido p:last-child {
        margin-bottom: 0;
    }

    .btn-action {
        border-radius: 2rem;
        padding: 0.65rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s;
    }

    @media print {
        body {
            background: white !important;
            color: black !important;
        }
        .page-header, .no-print, nav, header, footer, .navbar {
            display: none !important;
        }
        .document-card {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            max-width: 100% !important;
            margin: 0 !important;
        }
        .main-content-container {
            padding: 0 !important;
        }
        .tabla-descriptor {
            border: 1.5px solid black !important;
        }
        .tabla-descriptor th, .tabla-descriptor td {
            border: 1px solid black !important;
        }
        .tabla-descriptor th {
            background-color: #f0f0f0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .seccion-curricular {
            page-break-inside: avoid;
            border-bottom: 1px solid #ccc !important;
        }
    }
</style>
@endsection

@php
    if (!function_exists('extraerColumnasCronogramaConesupBlade')) {
        function extraerColumnasCronogramaConesupBlade($cro, &$counters_moodle = []) {
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
                if ($actividades_ea_html === '') {
                    $actividades_ea_html = $tareas_fmt;
                } elseif (mb_stripos(strip_tags($actividades_ea_html), strip_tags($tareas_html)) === false) {
                    $actividades_ea_html .= "<br>" . $tareas_fmt;
                }
            }

            $modalidad_raw = trim($cro['modalidad_trabajo'] ?? 'Asincrónico') ?: 'Asincrónico';
            $es_sincronico = (mb_stripos($modalidad_raw, 'sincr') !== false && mb_stripos($modalidad_raw, 'asincr') === false);

            return [
                'semana'         => $semana,
                'fecha_fmt'      => $fecha_fmt,
                'contenidos'     => $contenidos_html !== '' ? $contenidos_html : '--',
                'actividades'    => $actividades_ea_html !== '' ? $actividades_ea_html : '--',
                'modalidad'      => $es_sincronico ? 'Sincrónico' : 'Asincrónico',
                'es_sincronico'  => $es_sincronico
            ];
        }
    }

    if (!function_exists('renderizarTablaRubricaVisualHTMLBlade')) {
        function renderizarTablaRubricaVisualHTMLBlade($json_str) {
            if (empty($json_str)) return "";
            $data = json_decode($json_str, true);
            if (!is_array($data) || empty($data['columnas'])) return "";
            $columnas = $data['columnas'] ?? [];
            $filas = $data['filas'] ?? [];

            $html = '<div class="table-responsive my-3">';
            $html .= '<table class="table table-bordered table-sm align-middle" style="font-size: 0.85rem; border: 1.5px solid #cbd5e1; color: #000000;">';
            $html .= '<thead class="table-light text-center" style="background-color: #f1f5f9;">';
            $html .= '<tr><th style="width: 22%; font-weight: 800; background-color: #e2e8f0; color: #000000;">Criterio</th>';
            foreach ($columnas as $col) {
                $html .= '<th style="font-weight: 800; background-color: #f8fafc; color: #000000;">' . htmlspecialchars($col) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($filas as $f) {
                $crit = $f['criterio'] ?? ($f['nombre'] ?? 'Criterio');
                $html .= '<tr>';
                $html .= '<td class="fw-bold" style="background-color: #f8fafc; color: #000000;">' . htmlspecialchars($crit) . '</td>';
                $vals = $f['valores'] ?? [];
                foreach ($columnas as $idx => $col) {
                    $val = $vals[$idx] ?? '';
                    $html .= '<td style="font-size: 0.82rem; line-height: 1.4; color: #000000;">' . nl2br(htmlspecialchars($val)) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table></div>';
            return $html;
        }
    }

    $nombrePrograma = $curso->programa->nombre_programa ?? '';
    $categoriaPrograma = $curso->programa->categoria ?? '';
    $facultadCurso = $curso->facultad ?? $categoriaPrograma;
@endphp

@section('content')
<div class="container-fluid px-4 main-content-container">
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center no-print">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house-door"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cursos.index') }}">Oferta Académica</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Ver Curso</li>
                </ol>
            </nav>
            <h2 class="section-title mb-0"><i class="bi bi-journal-text me-2" style="color: var(--primary-color);"></i> {{ $curso->materia }}</h2>
            <p class="text-muted mb-0 small mt-1">Código: <strong class="text-dark">{{ $curso->codigo }}</strong> | {{ $nombrePrograma }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
            <a href="{{ route('cursos.descriptor_doc', $curso->id_plan) }}" class="btn btn-outline-primary btn-action shadow-sm fw-bold">
                <i class="bi bi-file-earmark-word-fill me-1"></i> Descargar Word
            </a>
            <a href="{{ route('cursos.descriptor_pdf', $curso->id_plan) }}" target="_blank" class="btn btn-primary btn-action shadow-sm fw-bold" style="background-color: var(--primary-color); border: none;">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Descargar PDF
            </a>
            <a href="{{ route('cursos.edit', $curso->id_plan) }}" class="btn btn-warning btn-action text-dark fw-bold shadow-sm">
                <i class="bi bi-pencil-square me-1"></i> Editar Curso
            </a>
            <a href="{{ route('cursos.index') }}" class="btn btn-secondary btn-action shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver a Cursos
            </a>
        </div>
    </div>

    <div class="document-card">
        <!-- CABECERA INSTITUCIONAL -->
        <div class="header-institucional">
            @php
                $logoUrl = \App\Services\ClienteService::logoUrl();
                if (stripos($logoUrl, 'logo.png') !== false && file_exists(public_path('imgs/logo_cefi.svg'))) {
                    $logoUrl = asset('imgs/logo_cefi.svg');
                }
            @endphp
            <img src="{{ $logoUrl }}" alt="{{ \App\Services\ClienteService::nombre() }}" class="logo-inst-silabo" onerror="this.style.display='none'">
            <h3>{{ \App\Services\ClienteService::nombreLegal() }}</h3>
            @if(!empty($facultadCurso))
                <h4>{{ $facultadCurso }}</h4>
            @endif
            <h5>{{ $nombrePrograma }}</h5>
        </div>

        <!-- TABLA DE FICHA TÉCNICA OFICIAL (12 FILAS CONESUP) -->
        <table class="tabla-descriptor">
            <tbody>
                <tr>
                    <th>Código</th>
                    <td><strong>{{ $curso->codigo }}</strong></td>
                </tr>
                <tr>
                    <th>Nombre</th>
                    <td style="background-color: var(--primary-color); color: #ffffff;"><strong>{{ $curso->materia }}</strong></td>
                </tr>
                <tr>
                    <th>Créditos</th>
                    <td>{{ $curso->creditos ?: '4' }}</td>
                </tr>
                <tr>
                    <th>Duración</th>
                    <td>{{ $curso->duracion ?: '15 semanas' }}</td>
                </tr>
                <tr>
                    <th>Distribución horas virtuales por semana</th>
                    <td>{!! nl2br(e($curso->distribucion_horas ?: 'Este curso comprende un total de 12 horas distribuidas en 3 horas teóricas, 1 hora práctica y 8 horas de estudio independiente.')) !!}</td>
                </tr>
                <tr>
                    <th>Modalidad</th>
                    <td>{{ $curso->modalidad ?: 'Virtual (aprendizaje electrónico)' }}</td>
                </tr>
                <tr>
                    <th>Naturaleza</th>
                    <td>{{ $curso->naturaleza ?: 'Teórico-práctica' }}</td>
                </tr>
                <tr>
                    <th>Ubicación</th>
                    <td>{{ $curso->cuatrimestre }}</td>
                </tr>
                <tr>
                    <th>Requisitos</th>
                    <td>{{ $curso->requisitos ?: 'No tiene' }}</td>
                </tr>
                <tr>
                    <th>Correquisitos</th>
                    <td>{{ $curso->correquisitos ?: 'No tiene' }}</td>
                </tr>
                <tr>
                    <th>Nivel</th>
                    <td>{{ $curso->nivel ?: ($categoriaPrograma ?: 'Maestría') }}</td>
                </tr>
                <tr>
                    <th>Profesor</th>
                    <td>{{ $curso->profesor ?: '--' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- SECCIONES I A XI OFICIALES CONESUP -->
        @php
            $descCurso = $curso->descripcion_curso ?: ($silabo->descripcion ?? '');
            $objGeneral = $curso->objetivo_general ?: ($silabo->objetivo_general ?? '');
            $objEspecificos = $curso->objetivos_especificos ?: ($silabo->objetivos_especificos ?? '');
            $contenidosTem = $curso->contenidos_tematicos ?: ($silabo->contenidos ?? '');
            $metodologiaEns = $curso->metodologia_ensenanza ?: ($silabo->metodologia ?? '');
            $estrategiasApr = $curso->estrategias_aprendizaje ?: ($silabo->estrategias_aprendizaje ?? '');
            $recursosDid = $curso->recursos_didacticos ?: ($silabo->recursos_aprendizaje ?? '');
            $biblioOblig = $curso->bibliografia ?: ($silabo->bibliografia ?? '');
        @endphp

        @if(!empty($descCurso))
            <div class="seccion-curricular">
                <div class="seccion-titulo">I. Descripción general del curso</div>
                <div class="seccion-contenido">{!! $descCurso !!}</div>
            </div>
        @endif

        @if(!empty($objGeneral))
            <div class="seccion-curricular">
                <div class="seccion-titulo">II. Objetivo general</div>
                <div class="seccion-contenido">{!! $objGeneral !!}</div>
            </div>
        @endif

        @if(!empty($objEspecificos))
            <div class="seccion-curricular">
                <div class="seccion-titulo">III. Objetivos específicos</div>
                <div class="seccion-contenido">{!! $objEspecificos !!}</div>
            </div>
        @endif

        @if(!empty($contenidosTem))
            <div class="seccion-curricular">
                <div class="seccion-titulo">IV. Contenido conceptual</div>
                <div class="seccion-contenido">{!! $contenidosTem !!}</div>
            </div>
        @endif

        @if(!empty($metodologiaEns))
            <div class="seccion-curricular">
                <div class="seccion-titulo">V. Metodología de enseñanza</div>
                <div class="seccion-contenido">{!! $metodologiaEns !!}</div>
            </div>
        @endif

        @if(!empty($estrategiasApr))
            <div class="seccion-curricular">
                <div class="seccion-titulo">VI. Estrategias de aprendizaje</div>
                <div class="seccion-contenido">{!! $estrategiasApr !!}</div>
            </div>
        @endif

        <!-- SECCIÓN VII: EVALUACIÓN DE LOS APRENDIZAJES -->
        <div class="seccion-curricular">
            <div class="seccion-titulo">VII. Evaluación de los aprendizajes</div>
            <div class="seccion-contenido">
                @if(!empty($curso->evaluacion_aprendizajes) && (empty($evaluaciones_ver) || stripos($curso->evaluacion_aprendizajes, '<table') === false))
                    <div class="mb-3">{!! $curso->evaluacion_aprendizajes !!}</div>
                @endif

                @if(!empty($evaluaciones_ver))
                    <div class="table-responsive my-2">
                        <table class="table table-bordered table-sm align-middle" style="max-width: 750px; font-size: 0.92rem; border: 1.5px solid #0f172a;">
                            <thead style="background-color: #f1f5f9; border-bottom: 2px solid #0f172a;">
                                <tr>
                                    <th style="font-weight: 800; padding: 6px 12px; width: 70%; color: #000000;">Estrategia evaluativa</th>
                                    <th style="font-weight: 800; padding: 6px 12px; width: 30%; text-align: center; color: #000000;">Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $tot_p = 0; @endphp
                                @foreach($evaluaciones_ver as $ev)
                                    @php
                                        $p = (float)($ev['porcentaje'] ?? 0);
                                        $tot_p += $p;
                                        $nom_ev = trim($ev['rubro'] ?? '');
                                        $cant_ev = (int)($ev['cantidad'] ?? 1);
                                        $extra_cant = ($cant_ev > 1) ? " ({$cant_ev} entregables)" : "";
                                    @endphp
                                    <tr>
                                        <td style="padding: 6px 12px; font-weight: 600; color: #000000;">{{ $nom_ev . $extra_cant }}</td>
                                        <td style="padding: 6px 12px; text-align: center; font-weight: 700; color: #000000;">{{ (int)$p }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background-color: #f8fafc; font-weight: 800; border-top: 1.5px solid #0f172a;">
                                <tr>
                                    <td style="padding: 6px 12px; text-align: right; color: #000000;">Total</td>
                                    <td style="padding: 6px 12px; text-align: center; color: #1066AD; font-weight: bold;">{{ round($tot_p) }}%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- SECCIÓN VIII: RECURSOS DIDÁCTICOS -->
        @if(!empty($recursosDid))
            <div class="seccion-curricular">
                <div class="seccion-titulo">VIII. Recursos didácticos</div>
                <div class="seccion-contenido">{!! $recursosDid !!}</div>
            </div>
        @endif

        <!-- SECCIÓN IX: CRONOGRAMA DE 15 SEMANAS -->
        <div class="seccion-curricular">
            <div class="seccion-titulo">IX. Cronograma</div>
            <div class="seccion-contenido">
                @if(!empty($curso->cronograma) && (empty($cronograma_ver) || stripos($curso->cronograma, '<table') === false))
                    <div class="mb-3">{!! $curso->cronograma !!}</div>
                @endif

                @if(!empty($cronograma_ver))
                    <div class="table-responsive my-2">
                        <table class="table table-bordered table-sm align-middle mb-0" style="font-size: 0.88rem; border: 1.5px solid #0f172a;">
                            <thead style="background-color: #f1f5f9; border-bottom: 2px solid #0f172a;">
                                <tr>
                                    <th style="width: 11%; font-weight: 800; text-align: center; padding: 6px 8px; color: #000000;">Semana</th>
                                    <th style="width: 38%; font-weight: 800; padding: 6px 10px; color: #000000;">Contenidos</th>
                                    <th style="width: 38%; font-weight: 800; padding: 6px 10px; color: #000000;">Actividades de enseñanza-aprendizaje</th>
                                    <th style="width: 13%; font-weight: 800; text-align: center; padding: 6px 8px; color: #000000;">Modalidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $counters_desc = []; @endphp
                                @foreach($cronograma_ver as $idx => $cro)
                                    @php
                                        $cols_ver = extraerColumnasCronogramaConesupBlade($cro, $counters_desc);
                                        $fec_txt = !empty($cols_ver['fecha_fmt']) ? "<br><span class='text-muted fw-normal' style='font-size:0.78rem;'>(" . e($cols_ver['fecha_fmt']) . ")</span>" : "";
                                    @endphp
                                    <tr>
                                        <td class="text-center fw-bold" style="background-color: #f8fafc; vertical-align: top; padding-top: 8px; color: #000000;">
                                            Semana {{ $cols_ver['semana'] ?: ($idx + 1) }}{!! $fec_txt !!}
                                        </td>
                                        <td style="vertical-align: top; padding: 8px 10px; color: #000000;">
                                            {!! $cols_ver['contenidos'] !== '--' ? $cols_ver['contenidos'] : '<span class="text-muted small">--</span>' !!}
                                        </td>
                                        <td style="vertical-align: top; padding: 8px 10px; color: #000000;">
                                            {!! $cols_ver['actividades'] !== '--' ? $cols_ver['actividades'] : '<span class="text-muted small">--</span>' !!}
                                        </td>
                                        @if($cols_ver['es_sincronico'])
                                            <td class="text-center fw-bold" style="background-color: #0e71eb; color: #ffffff; vertical-align: middle; padding: 8px 6px;">
                                                <div style="font-size: 0.78rem; letter-spacing: 0.5px;">SINCRÓNICO</div>
                                                <img src="{{ asset('imgs/zoom_logo.png') }}" alt="Zoom" style="max-width: 48px; height: auto; margin-top: 4px; border-radius: 3px;">
                                            </td>
                                        @else
                                            <td class="text-center fw-bold" style="background-color: #f8fafc; color: #000000; vertical-align: middle; padding: 8px 6px; font-size: 0.78rem; letter-spacing: 0.5px;">
                                                ASINCRÓNICO
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- DESCRIPCIÓN DE LAS ESTRATEGIAS EVALUATIVAS Y RÚBRICAS -->
        @if(!empty($rubricas_ver))
            <div class="seccion-curricular">
                <div class="seccion-titulo" style="font-size: 1.1rem; text-transform: uppercase; color: #000000;">DESCRIPCIÓN DE LAS ESTRATEGIAS EVALUATIVAS Y RÚBRICAS</div>
                <div class="seccion-contenido">
                    @foreach($rubricas_ver as $idx_r => $rub)
                        @php
                            $num_rub = $idx_r + 1;
                            $nom_rub = $rub['nombre'] ?? 'Rúbrica';
                            $porc_rub = (float)($rub['porcentaje_total'] ?? 0);

                            $desc_entregable = $rub['descripcion_entregable'] ?? '';
                            if (empty($desc_entregable) && !empty($rub['contenido_json'])) {
                                $dec = json_decode($rub['contenido_json'], true);
                                if (is_array($dec) && !empty($dec['descripcion_entregable'])) {
                                    $desc_entregable = $dec['descripcion_entregable'];
                                }
                            }
                        @endphp
                        <div class="mb-4 pb-3" style="border-bottom: 1px dashed #cbd5e1;">
                            <h6 class="fw-bold mb-2 text-dark" style="font-size: 1rem;">
                                {{ $num_rub . '. ' . $nom_rub . ' (' . (int)$porc_rub . '%)' }}
                            </h6>
                            @if(!empty($desc_entregable))
                                <div class="text-secondary small mb-3" style="line-height: 1.55; color: #000000 !important;">
                                    {!! nl2br(e($desc_entregable)) !!}
                                </div>
                            @endif

                            @if(!empty($rub['contenido_json']))
                                {!! renderizarTablaRubricaVisualHTMLBlade($rub['contenido_json']) !!}
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif(!empty($curso->guias_evaluacion))
            <div class="seccion-curricular">
                <div class="seccion-titulo">DESCRIPCIÓN DE LAS ESTRATEGIAS EVALUATIVAS Y RÚBRICAS</div>
                <div class="seccion-contenido">{!! $curso->guias_evaluacion !!}</div>
            </div>
        @endif

        <!-- SECCIÓN X: BIBLIOGRAFÍA OBLIGATORIA -->
        @if(!empty($biblioOblig))
            <div class="seccion-curricular">
                <div class="seccion-titulo">X. Bibliografía obligatoria: {{ trim(($curso->codigo ?? '') . ' ' . ($curso->materia ?? '')) }}</div>
                <div class="seccion-contenido">{!! $biblioOblig !!}</div>
            </div>
        @endif

        <!-- SECCIÓN XI: BIBLIOGRAFÍA COMPLEMENTARIA -->
        @if(!empty($curso->bibliografia_complementaria))
            <div class="seccion-curricular">
                <div class="seccion-titulo">XI. Bibliografía complementaria: {{ trim(($curso->codigo ?? '') . ' ' . ($curso->materia ?? '')) }}</div>
                <div class="seccion-contenido">{!! $curso->bibliografia_complementaria !!}</div>
            </div>
        @endif

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.seccion-contenido a, .seccion-curricular a').forEach(function(link) {
        link.setAttribute('target', '_blank');
        link.setAttribute('rel', 'noopener noreferrer');
    });
});
</script>
@endsection

