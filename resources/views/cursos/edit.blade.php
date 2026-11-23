@extends('layouts.app')

@section('title', 'Editar Curso: ' . $curso->materia)

@php
if (!function_exists('generarHtmlTareasMoodle')) {
    function generarHtmlTareasMoodle($json_data, &$counters, $rubros_cantidades = []) {
        $acts = json_decode($json_data, true);
        if (empty($acts)) {
            return "";
        }
        
        $normalized_cants = [];
        foreach ($rubros_cantidades as $nom => $cant) {
            $n = trim(mb_strtoupper($nom, 'UTF-8'));
            $normalized_cants[$n] = $cant;
            if (mb_substr($n, -1, 1, 'UTF-8') === 'S') {
                $n_sin_s = mb_substr($n, 0, -1, 'UTF-8');
                if (!isset($normalized_cants[$n_sin_s])) $normalized_cants[$n_sin_s] = $cant;
            } else {
                $n_con_s = $n . 'S';
                if (!isset($normalized_cants[$n_con_s])) $normalized_cants[$n_con_s] = $cant;
            }
        }

        $html = "";
        foreach ($acts as $act) {
            $tipo = $act['tipo'] ?? 'tarea';
            $tipo_norm = mb_strtolower(trim($tipo), 'UTF-8');
            $tipo_norm = str_replace(['á','é','í','ó','ú'], ['a','e','i','o','u'], $tipo_norm);
            
            $act_name_norm = mb_strtolower(trim($act['nombre'] ?? ''), 'UTF-8');
            $act_name_norm = str_replace(['á','é','í','ó','ú'], ['a','e','i','o','u'], $act_name_norm);
            
            $is_quiz = in_array($tipo_norm, ['quiz', 'quizzes', 'prueba', 'pruebas', 'examen', 'examenes', 'corto', 'cortos', 'cuestionario', 'cuestionarios', 'autoevaluacion', 'autoevaluaciones'])
                       || (strpos($act_name_norm, 'quiz') !== false || strpos($act_name_norm, 'prueba') !== false || strpos($act_name_norm, 'cuestionario') !== false || strpos($act_name_norm, 'examen') !== false || strpos($act_name_norm, 'corto') !== false || strpos($act_name_norm, 'evaluacion') !== false);
                       
            $is_foro = !$is_quiz && (in_array($tipo_norm, ['foro', 'foros', 'forum', 'debate', 'debates', 'discusion'])
                       || (strpos($act_name_norm, 'foro') !== false || strpos($act_name_norm, 'forum') !== false || strpos($act_name_norm, 'debate') !== false));
                       
            $tipo_final = 'tarea';
            if ($is_quiz) {
                $tipo_final = 'quiz';
                $label = "CUESTIONARIO";
                $color = "#ff9800";
            } elseif ($is_foro) {
                $tipo_final = 'foro';
                $label = "FORO";
                $color = "#007bff";
            } else {
                $tipo_final = 'tarea';
                $label = "TAREA";
                $color = "#dc3545";
                if (!in_array($tipo_norm, ['tarea', 'tareas', 'assign', 'assignment']) && !empty($act['tipo'])) {
                    $label = mb_strtoupper($act['tipo'], 'UTF-8');
                }
            }
            
            $counters[$label] = ($counters[$label] ?? 0) + 1;
            $num = $counters[$label];

            $labelDisplay = mb_strtoupper($label, 'UTF-8') . " (" . $num . ")";

            $html .= "<div style='margin-bottom: 15px; border-left: 4px solid {$color}; padding-left: 12px;'>";
            $html .= "<p style='margin: 0; font-weight: 800; font-size: 0.75rem; text-transform: uppercase;'><span style='background-color: #ffff00; color: #000000; padding: 1px 4px; border-radius: 3px;'>{$labelDisplay}</span></p>";
            if (!empty($act['intro'])) {
                $html .= "<p style='margin: 5px 0 0 0; font-weight: 400; font-size: 0.9rem; color: #000000; line-height: 1.4;'>".nl2br(htmlspecialchars($act['intro']))."</p>";
            }
            $html .= "</div>";
        }
        return $html;
    }
}
@endphp

@section('styles')
<style>
    :root {
        --primary-color: #5fb230;
        --primary-hover: #4e9a26;
        --text-dark: #2d3436;
        --bg-light: #f1f5f9;
    }
    body {
        background-color: var(--bg-light);
    }
    .main-content-container {
        padding-top: 1.5rem;
        padding-bottom: 5rem;
    }
    .page-header {
        background: #ffffff;
        padding: 1.5rem 2rem;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
        border: 1px solid #e2e8f0;
    }
    [data-theme="dark"] .page-header {
        background: #1e293b;
        border-color: #334155;
    }
    .section-title {
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.025em;
    }
    [data-theme="dark"] .section-title {
        color: #f8fafc;
    }
    .card-silabo {
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        background: white;
    }
    [data-theme="dark"] .card-silabo {
        background: #1e293b;
        border-color: #334155;
    }
    .nav-tabs .nav-link {
        color: #64748b;
        font-weight: 600;
        border: none;
        padding: 1rem 1.25rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        background: transparent;
    }
    .nav-tabs .nav-link:hover {
        color: var(--primary-color);
        background: rgba(95, 178, 48, 0.04);
    }
    .nav-tabs .nav-link.active {
        color: var(--primary-color) !important;
        border-bottom: 3px solid var(--primary-color) !important;
        background: transparent !important;
        font-weight: 700;
    }
    .field-card {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    [data-theme="dark"] .field-card {
        background-color: rgba(15, 23, 42, 0.5);
        border-color: #334155;
    }
    .field-card-title {
        color: #1e293b;
        font-weight: 700;
        font-size: 0.95rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    [data-theme="dark"] .field-card-title {
        color: #f1f5f9;
    }
    
    /* Botón Flotante Persistente Azul Oficial */
    .btn-save-float {
        position: fixed;
        bottom: 1.25rem;
        right: 1.5rem;
        z-index: 1050;
        box-shadow: 0 6px 18px rgba(13, 110, 253, 0.45);
        padding: 0.65rem 1.35rem;
        font-size: 0.85rem;
        font-weight: 700;
        border-radius: 2rem;
        transition: all 0.3s ease;
        background-color: #0d6efd !important;
        border: 2px solid #ffffff !important;
        color: #ffffff !important;
    }
    .btn-save-float:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 8px 24px rgba(13, 110, 253, 0.6);
        background-color: #0b5ed7 !important;
        color: #ffffff !important;
    }
    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #334155;
        margin-bottom: 0.35rem;
    }
    [data-theme="dark"] .form-label {
        color: #cbd5e1;
    }
    .form-control, .form-select {
        border-radius: 0.75rem;
        padding: 0.65rem 0.9rem;
        border: 1.5px solid #e2e8f0;
        font-size: 0.9rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(95, 178, 48, 0.15);
    }
    .badge-section-num {
        background-color: #e0f2fe;
        color: #0369a1;
        font-weight: 800;
        padding: 0.25rem 0.6rem;
        border-radius: 0.5rem;
        font-size: 0.8rem;
    }
    
    /* Cronograma y Moodle */
    #tablaCronograma { border-collapse: collapse !important; border: 1px solid #e2e8f0 !important; }
    #tablaCronograma th { background-color: #f8fafc; color: #475569; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px; }
    [data-theme="dark"] #tablaCronograma th { background-color: #1e293b; color: #94a3b8; }
    #tablaCronograma td { padding: 8px !important; vertical-align: top !important; }
    .crono-semana-num { padding-top: 14px !important; vertical-align: top !important; }
    .td-modalidad { transition: background-color 0.3s ease, color 0.3s ease; vertical-align: middle !important; text-align: center; padding: 6px 3px !important; width: 125px !important; min-width: 120px !important; position: relative; }
    .td-sincronico { background-color: #008dff !important; color: white !important; }
    .td-asincronico { background-color: #f8fafc !important; color: #64748b !important; }
    [data-theme="dark"] .td-asincronico { background-color: #0f172a !important; color: #94a3b8 !important; }
    .modalidad-select { 
        font-size: 0.64rem; 
        font-weight: 700; 
        letter-spacing: 0.02em;
        border: none !important;
        padding: 4px 16px 4px 4px !important; 
        width: 100%; 
        text-transform: uppercase;
        background-color: transparent !important;
        background-repeat: no-repeat !important;
        background-position: right 3px center !important;
        background-size: 10px 8px !important;
        color: inherit !important;
        text-align: center;
        text-align-last: center;
        cursor: pointer;
        outline: none !important;
        box-shadow: none !important;
        display: block;
        border-radius: 6px;
    }
    .modalidad-select:hover { background-color: rgba(0,0,0,0.04) !important; }
    .td-sincronico .modalidad-select:hover { background-color: rgba(255,255,255,0.15) !important; }
    .td-sincronico .modalidad-select { 
        color: #ffffff !important; 
        text-shadow: 0 1px 2px rgba(0,0,0,0.2); 
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
    }
    .td-asincronico .modalidad-select { 
        color: #475569 !important; 
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
    }
    .modalidad-select option { background-color: white; color: #1e293b; font-size: 0.8rem; }
    .zoom-logo-mini { width: 50px; max-width: 75%; height: auto; display: block; margin: 0 auto; border-radius: 3px; }
    .zoom-logo-container { padding: 3px 0 4px 0; width: 100%; margin: 0; }

    .btn-moodle { background-color: #f98012; color: white; border: none; font-size: 0.75rem; font-weight: 700; border-radius: 6px; padding: 6px 10px; }
    .btn-moodle:hover { background-color: #e06f0b; color: white; }
    .moodle-tag { font-size: 0.7rem; font-weight: 700; border-radius: 4px; padding: 2px 6px; display: inline-block; margin-bottom: 2px; }
    .badge-quiz { background-color: #ff9800; color: white; }
    .badge-forum { background-color: #007bff; color: white; }
    .badge-assign { background-color: #dc3545; color: white; }

    /* Rúbricas */
    .rubrica-item { transition: all 0.3s ease; border: 1px solid #e2e8f0 !important; background: #ffffff; }
    [data-theme="dark"] .rubrica-item { background: #1e293b; border-color: #334155 !important; }
    .rubrica-item:hover { box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important; transform: translateY(-2px); }
    .tabla-rubrica-visual th { background-color: #f1f5f9; color: #475569; font-size: 0.7rem; text-align: center; font-weight: 800; border: 1px solid #e2e8f0; }
    [data-theme="dark"] .tabla-rubrica-visual th { background-color: #0f172a; color: #94a3b8; border-color: #334155; }
    .tabla-rubrica-visual td { background-color: white; border: 1px solid #e2e8f0; padding: 4px; }
    [data-theme="dark"] .tabla-rubrica-visual td { background-color: transparent; border-color: #334155; }
    .rubrica-item .form-select-sm, .rubrica-item .form-control-sm { font-size: 0.75rem; border-radius: 6px; }
    .btn-xs { padding: 2px 6px; font-size: 0.65rem; border-radius: 4px; }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 main-content-container">
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house-door"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cursos.index') }}">Oferta Académica</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cursos.show', $curso->id_plan) }}">Ver Curso</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Curso: <span id="breadcrumbCursoNombre" class="fw-semibold">{{ $curso->materia }}</span></li>
                </ol>
            </nav>
            <h2 class="section-title mb-0 d-flex flex-wrap align-items-center gap-2">
                <span><i class="bi bi-pencil-square text-success me-1"></i> Editar Curso en el Plan:</span>
                <span class="text-primary fw-bold" id="headerNombreCurso">{{ $curso->materia }}</span>
                @if(!empty($curso->codigo))
                    <span class="badge bg-light text-dark border ms-1 fs-6" id="headerCodigoCurso">{{ $curso->codigo }}</span>
                @else
                    <span class="badge bg-light text-dark border ms-1 fs-6 d-none" id="headerCodigoCurso"></span>
                @endif
            </h2>
            <p class="text-muted mb-0 small mt-1">Programa: <strong class="text-dark text-uppercase" id="subHeaderNombrePrograma">{{ $curso->programa->nombre_programa ?? '' }}</strong></p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('cursos.show', $curso->id_plan) }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-eye me-2"></i> Ver Curso
            </a>
            <a href="{{ route('cursos.index') }}" class="btn btn-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i> Volver a Cursos
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
            <div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('cursos.update', $curso->id_plan) }}" method="POST" enctype="multipart/form-data" id="formPlanEstudios">
        @csrf
        <input type="hidden" name="active_tab" id="active_tab_input" value="{{ session('active_tab', 'ficha_tecnica') }}">

        <!-- Botón Flotante de Guardado Persistente Azul Oficial -->
        <button type="button" onclick="validarYGuardarPlan()" class="btn btn-primary btn-save-float shadow-lg border-white border-2">
            <i class="bi bi-check2-circle me-2"></i> GUARDAR CAMBIOS
        </button>

        <div class="card card-silabo border-0 shadow-sm">
            <!-- Barra Superior de Identificación Permanente del Curso -->
            <div class="px-4 py-2 bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success px-2 py-1"><i class="bi bi-book-half me-1"></i> Curso en Edición</span>
                    <span class="fw-bold text-dark fs-6" id="cardNombreCurso">{{ $curso->materia }}</span>
                    @if(!empty($curso->codigo))
                        <span class="badge bg-white text-muted border" id="cardCodigoCurso">{{ $curso->codigo }}</span>
                    @else
                        <span class="badge bg-white text-muted border d-none" id="cardCodigoCurso"></span>
                    @endif
                </div>
                <div class="text-muted small">
                    <i class="bi bi-mortarboard me-1"></i> <strong id="cardCuatrimestreCurso">{{ $curso->cuatrimestre }}</strong>
                </div>
            </div>

            <div class="card-header bg-white p-0">
                <ul class="nav nav-tabs border-0 flex-nowrap overflow-auto" id="cursoTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ficha_tecnica" type="button">
                            <i class="bi bi-table"></i> Ficha Técnica
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#desc_objetivos_tab" type="button">
                            <i class="bi bi-bullseye"></i> I-III. Descripción & Objetivos
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#contenidos_metodologia_tab" type="button">
                            <i class="bi bi-journal-bookmark-fill"></i> IV-VI. Contenidos & Metodología
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#evaluacion_tab" type="button">
                            <i class="bi bi-check2-square"></i> VII. Evaluación de los Aprendizajes
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#cronograma_tab" type="button">
                            <i class="bi bi-calendar3"></i> VIII-IX. Recursos & Cronograma
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rubricas_tab" type="button">
                            <i class="bi bi-card-checklist"></i> Rúbricas Analíticas Oficiales
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bibliografia_descriptor_tab" type="button">
                            <i class="bi bi-book-half"></i> X-XI. Bibliografía
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4 p-md-5">
                <div class="tab-content">
                    
                    <!-- PESTAÑA 1: FICHA TÉCNICA DEL CURSO -->
                    <div class="tab-pane fade show active" id="ficha_tecnica">
                        
                        <div class="field-card mb-4">
                            <div class="field-card-title text-success">
                                <i class="bi bi-mortarboard-fill"></i> Vinculación del Programa y Ubicación Curricular
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="id_programa" class="form-label">Programa Académico / Carrera <span class="text-danger">*</span></label>
                                    <select name="id_programa" id="id_programa" class="form-select form-select-lg fw-bold" required onchange="actualizarPrecioPrograma(this)">
                                        <option value="">Seleccione un programa...</option>
                                        @php $curr_cat = ""; @endphp
                                        @foreach ($programas as $prog)
                                            @if ($curr_cat !== $prog->categoria)
                                                @if ($curr_cat !== "") </optgroup> @endif
                                                @php $curr_cat = $prog->categoria; @endphp
                                                <optgroup label="{{ $curr_cat ?: 'Programas' }}">
                                            @endif
                                            <option value="{{ $prog->id_programa }}" data-costo="{{ $prog->costo_materia ?? 0 }}" data-categoria="{{ $prog->categoria ?? '' }}" {{ (old('id_programa', $curso->id_programa) == $prog->id_programa) ? 'selected' : '' }}>
                                                {{ $prog->nombre_programa }}
                                            </option>
                                        @endforeach
                                        @if ($curr_cat !== "") </optgroup> @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="cuatrimestre" class="form-label">Periodo / Ubicación Curricular <span class="text-danger">*</span></label>
                                    <select name="cuatrimestre" id="cuatrimestre" class="form-select form-select-lg" required>
                                        <option value="">Seleccione...</option>
                                        @php
                                            $opcionesPeriodo = [
                                                'I Cuatrimestre', 'II Cuatrimestre', 'III Cuatrimestre', 'IV Cuatrimestre',
                                                'V Cuatrimestre', 'VI Cuatrimestre', 'VII Cuatrimestre', 'VIII Cuatrimestre',
                                                'I Bimestre', 'II Bimestre', 'III Bimestre', 'IV Bimestre',
                                                'V Bimestre', 'VI Bimestre',
                                                'Módulo 1', 'Módulo 2', 'Módulo 3', 'Módulo 4', 'Módulo 5', 'Módulo 6',
                                                'Mes', 'Semana', 'Seminario', 'Taller', 'Fecha Específica',
                                            ];
                                            $valPeriodo = old('cuatrimestre', $curso->cuatrimestre);
                                        @endphp
                                        @foreach ($opcionesPeriodo as $c)
                                            <option value="{{ $c }}" {{ $valPeriodo === $c ? 'selected' : '' }}>{{ $c }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="precio_input" class="form-label">Costo del Curso (₡)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">₡</span>
                                        <input type="number" name="precio" id="precio_input" class="form-control" min="0" step="0.01" placeholder="0.00" value="{{ old('precio', $curso->precio ?? 0) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field-card mb-4">
                            <div class="field-card-title text-primary">
                                <i class="bi bi-card-checklist"></i> Datos Oficiales del Curso
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="codigo" class="form-label">Código del Curso <span class="text-danger">*</span></label>
                                    <input type="text" name="codigo" id="codigo" class="form-control" placeholder="Ej: MLSC - 01" value="{{ old('codigo', $curso->codigo) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="materia" class="form-label">Nombre de la Materia / Curso <span class="text-danger">*</span></label>
                                    <input type="text" name="materia" id="materia" class="form-control fw-bold" placeholder="Ej: Cultura, etnicidad y diversidad." value="{{ old('materia', $curso->materia) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="creditos" class="form-label">Créditos</label>
                                    <input type="number" name="creditos" id="creditos" class="form-control" placeholder="Ej: 4" min="0" value="{{ old('creditos', $curso->creditos ?? 4) }}">
                                </div>

                                <div class="col-md-3">
                                    <label for="duracion" class="form-label">Duración <span class="text-muted">(semanas)</span></label>
                                    @php
                                        $durNum = (int)preg_replace('/\D/', '', (string)($curso->duracion ?? '15')) ?: 15;
                                    @endphp
                                    <div class="input-group">
                                        <input type="number" name="duracion" id="duracion" class="form-control text-center fw-bold" placeholder="15" value="{{ old('duracion', $durNum) }}" min="1" required>
                                        <span class="input-group-text bg-light text-muted">semanas</span>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label for="modalidad" class="form-label">Modalidad</label>
                                    <input type="text" name="modalidad" id="modalidad" class="form-control" placeholder="Ej: Virtual (aprendizaje electrónico)" value="{{ old('modalidad', $curso->modalidad ?? 'Virtual (aprendizaje electrónico)') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="naturaleza" class="form-label">Naturaleza</label>
                                    <input type="text" name="naturaleza" id="naturaleza" class="form-control" placeholder="Ej: Teórico-práctica" value="{{ old('naturaleza', $curso->naturaleza ?? 'Teórico-práctica') }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="requisitos" class="form-label">Requisitos Previos</label>
                                    <input type="text" name="requisitos" id="requisitos" class="form-control" placeholder="Ej: No tiene" value="{{ old('requisitos', $curso->requisitos ?? 'No tiene') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="correquisitos" class="form-label">Correquisitos</label>
                                    <input type="text" name="correquisitos" id="correquisitos" class="form-control" placeholder="Ej: No tiene" value="{{ old('correquisitos', $curso->correquisitos ?? 'No tiene') }}">
                                </div>

                                <div class="col-12 mt-3">
                                    <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                        <i class="bi bi-clock-history text-primary"></i> Distribución de Horas por Semana
                                    </label>
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="row g-2 align-items-center mb-3">
                                            <div class="col-md-3 col-6">
                                                <label for="horas_teoricas" class="form-label small mb-1 fw-bold text-secondary">Horas Teóricas</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="horas_teoricas" id="horas_teoricas" class="form-control text-center fw-bold" min="0" value="{{ old('horas_teoricas', $curso->horas_teoricas ?? 3) }}" oninput="calcularDistribucionHoras()">
                                                    <span class="input-group-text bg-white">hrs</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <label for="horas_practicas" class="form-label small mb-1 fw-bold text-secondary">Horas Prácticas</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="horas_practicas" id="horas_practicas" class="form-control text-center fw-bold" min="0" value="{{ old('horas_practicas', $curso->horas_practicas ?? 1) }}" oninput="calcularDistribucionHoras()">
                                                    <span class="input-group-text bg-white">hrs</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <label for="horas_independientes" class="form-label small mb-1 fw-bold text-secondary">Estudio Independiente</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="horas_independientes" id="horas_independientes" class="form-control text-center fw-bold" min="0" value="{{ old('horas_independientes', $curso->horas_independientes ?? 8) }}" oninput="calcularDistribucionHoras()">
                                                    <span class="input-group-text bg-white">hrs</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <label for="horas_totales" class="form-label small mb-1 fw-bold text-primary">Total Horas / Sem.</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="horas_totales" id="horas_totales" class="form-control text-center fw-bold text-primary bg-white border-primary" value="{{ old('horas_totales', $curso->horas_totales ?? 12) }}" readonly>
                                                    <span class="input-group-text bg-primary text-white">hrs</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <textarea name="distribucion_horas" id="distribucion_horas" class="form-control form-control-sm text-muted bg-white" rows="2" readonly style="font-size: 0.85rem;">{{ old('distribucion_horas', $curso->distribucion_horas ?? 'Este curso comprende un total de 12 horas distribuidas en 3 horas teóricas, 1 hora práctica y 8 horas de estudio independiente.') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- PESTAÑA 2: I-III. DESCRIPCIÓN Y OBJETIVOS -->
                    <div class="tab-pane fade" id="desc_objetivos_tab">
                        <div class="mb-4">
                            <label for="descripcion_curso" class="form-label fw-bold"><span class="badge-section-num me-1">I</span> Descripción general del curso</label>
                            <textarea name="descripcion_curso" id="descripcion_curso" class="form-control editor-rico" rows="8" style="min-height: 250px;">{{ old('descripcion_curso', $curso->descripcion_curso) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="objetivo_general" class="form-label fw-bold"><span class="badge-section-num me-1">II</span> Objetivo general</label>
                            <textarea name="objetivo_general" id="objetivo_general" class="form-control editor-rico" rows="8" style="min-height: 250px;">{{ old('objetivo_general', $curso->objetivo_general) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="objetivos_especificos" class="form-label fw-bold"><span class="badge-section-num me-1">III</span> Objetivos específicos</label>
                            <textarea name="objetivos_especificos" id="objetivos_especificos" class="form-control editor-rico" rows="8" style="min-height: 250px;">{{ old('objetivos_especificos', $curso->objetivos_especificos) }}</textarea>
                        </div>
                    </div>

                    <!-- PESTAÑA 3: IV-VI. CONTENIDOS Y METODOLOGÍA -->
                    <div class="tab-pane fade" id="contenidos_metodologia_tab">
                        <div class="mb-4">
                            <label for="contenidos_tematicos" class="form-label fw-bold"><span class="badge-section-num me-1">IV</span> Contenido conceptual</label>
                            <textarea name="contenidos_tematicos" id="contenidos_tematicos" class="form-control editor-rico" rows="8" style="min-height: 250px;">{{ old('contenidos_tematicos', $curso->contenidos_tematicos) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="metodologia_ensenanza" class="form-label fw-bold"><span class="badge-section-num me-1">V</span> Metodología de enseñanza</label>
                            <textarea name="metodologia_ensenanza" id="metodologia_ensenanza" class="form-control editor-rico" rows="8" style="min-height: 250px;">{{ old('metodologia_ensenanza', $curso->metodologia_ensenanza) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="estrategias_aprendizaje" class="form-label fw-bold"><span class="badge-section-num me-1">VI</span> Estrategias de aprendizaje</label>
                            <textarea name="estrategias_aprendizaje" id="estrategias_aprendizaje" class="form-control editor-rico" rows="8" style="min-height: 250px;">{{ old('estrategias_aprendizaje', $curso->estrategias_aprendizaje) }}</textarea>
                        </div>
                    </div>

                    <!-- PESTAÑA 4: VII. EVALUACIÓN DE LOS APRENDIZAJES -->
                    <div class="tab-pane fade" id="evaluacion_tab">
                        <div class="mb-4">
                            <label for="evaluacion_aprendizajes" class="form-label fw-bold"><span class="badge-section-num me-1">VII</span> Marco / Descripción de la Evaluación de los Aprendizajes</label>
                            <textarea name="evaluacion_aprendizajes" id="evaluacion_aprendizajes" class="form-control editor-rico" rows="4" style="min-height: 140px;">{{ old('evaluacion_aprendizajes', $curso->evaluacion_aprendizajes) }}</textarea>
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Texto introductorio según el modelo CONESUP sobre el enfoque de evaluación formativa y sumativa.</small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-check2-square me-2"></i>Tabla Oficial de Estrategias Evaluativas y Porcentajes</h5>
                            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill small">
                                <i class="bi bi-info-circle me-1"></i> La suma de porcentajes debe ser exactamente 100%
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="tablaEvaluacion">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 4%;">#</th>
                                        <th style="width: 28%;">Rubro de Evaluación</th>
                                        <th style="width: 8%;">%</th>
                                        <th style="width: 8%;">Cant.</th>
                                        <th style="width: 10%;">Valor Unit.</th>
                                        <th style="width: 14%;"><i class="bi bi-mortarboard-fill me-1" style="color: #f98012;"></i>Tipo Moodle</th>
                                        <th style="width: 18%;">Anexos / Rúbricas</th>
                                        <th style="width: 10%;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($evaluaciones as $index => $ev)
                                    <tr>
                                        <td class="text-center fw-bold eval-num">{{ $index + 1 }}</td>
                                        <td><input type="text" name="rubros[]" class="form-control fw-bold" value="{{ $ev['rubro'] ?? '' }}" oninput="actualizarOpcionesNombresEnRubricas()"></td>
                                        <td><input type="number" name="porcentajes[]" class="form-control valor-p text-center fw-bold" value="{{ (int)($ev['porcentaje'] ?? 0) }}"></td>
                                        <td><input type="number" name="cantidades_eval[]" class="form-control text-center" value="{{ $ev['cantidad'] ?? 1 }}"></td>
                                        <td><input type="text" class="form-control text-center eval-unit-val" readonly value="0"></td>
                                        <td>
                                            <select name="tipos_moodle_eval[]" class="form-select form-select-sm">
                                                <option value="" {{ empty($ev['tipo_moodle']) ? 'selected' : '' }}>-- Sin actividad Moodle --</option>
                                                <option value="assign" {{ ($ev['tipo_moodle'] ?? '') === 'assign' ? 'selected' : '' }}>📄 Tarea</option>
                                                <option value="quiz" {{ ($ev['tipo_moodle'] ?? '') === 'quiz' ? 'selected' : '' }}>📝 Quiz / Examen</option>
                                                <option value="forum" {{ ($ev['tipo_moodle'] ?? '') === 'forum' ? 'selected' : '' }}>💬 Foro</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="anexos_eval[]" class="form-select form-select-sm select-anexo-rubrica" data-seleccionado="{{ $ev['anexo'] ?? '' }}">
                                                <option value="">Ninguno</option>
                                            </select>
                                        </td>
                                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); calcularTotal(); actualizarOpcionesNombresEnRubricas();">Eliminar</button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="2" class="text-end">TOTAL:</td>
                                        <td id="totalPorcentaje" class="text-center">0%</td>
                                        <td id="totalCantidad" class="text-center">0</td>
                                        <td id="totalValorUnitario" class="text-center"></td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <button type="button" class="btn btn-success rounded-pill px-4" onclick="agregarFila()">+ Añadir Rubro</button>
                        </div>
                    </div>

                    <!-- PESTAÑA 5: VIII-IX. RECURSOS DIDÁCTICOS Y CRONOGRAMA -->
                    <div class="tab-pane fade" id="cronograma_tab">
                        <div class="mb-4 pb-3 border-bottom">
                            <label for="recursos_didacticos" class="form-label fw-bold"><span class="badge-section-num me-1">VIII</span> Recursos didácticos</label>
                            <textarea name="recursos_didacticos" id="recursos_didacticos" class="form-control editor-rico" rows="6" style="min-height: 180px;">{{ old('recursos_didacticos', $curso->recursos_didacticos) }}</textarea>
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Plataformas, entorno virtual Moodle, bibliotecas digitales y recursos multimedia oficiales CONESUP.</small>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-calendar3 me-2"></i>IX. Cronograma de 15 Semanas</h5>
                            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill small">
                                <i class="bi bi-info-circle me-1"></i> Contenidos y actividades de enseñanza-aprendizaje por semana según modelo CONESUP
                            </span>
                        </div>

                        <!-- Barra de Progreso de Actividades del Cronograma -->
                        <div class="card border-0 shadow-sm rounded-3 mb-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-left: 5px solid #008dff !important;">
                            <div class="card-body p-3">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calendar-check-fill text-primary fs-5"></i>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark" id="progresoActividadesTitulo">
                                                Progreso de Actividades del Cronograma
                                            </h6>
                                            <small class="text-muted" id="progresoActividadesSubtitulo">Calculando actividades implementadas...</small>
                                        </div>
                                    </div>
                                    <div>
                                        <span id="badgeProgresoActividades" class="badge bg-primary px-3 py-2 rounded-pill fw-bold" style="font-size: 0.85rem;">
                                            0 / 0 Actividades (0%)
                                        </span>
                                    </div>
                                </div>
                                <div class="progress" style="height: 10px; border-radius: 6px; background-color: #e9ecef;" title="Progreso de actividades programadas">
                                    <div id="barraProgresoActividades" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="tablaCronograma">
                                <thead class="text-center">
                                    <tr>
                                        <th style="width: 4%;">Sem.</th>
                                        <th style="width: 8%;">Fecha</th>
                                        <th style="width: 35%;">Actividad / Tema</th>
                                        <th style="width: 35%;">Tareas / Entregables</th>
                                        <th style="width: 7%;">Moodle</th>
                                        <th style="width: 7%;">Modalidad</th>
                                        <th style="width: 4%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $renderCounters = [];
                                        $rubros_cantidades_render = [];
                                        foreach($evaluaciones as $ev) {
                                            $nom = $ev['rubro'] ?? '';
                                            $cant = $ev['cantidad'] ?? 1;
                                            if ($nom) $rubros_cantidades_render[$nom] = $cant;
                                        }
                                    @endphp
                                    @foreach($cronograma_data as $idx => $cro)
                                        @php
                                            $idActExistente = 'act_ex_' . $idx;
                                            $idTarExistente = 'tar_ex_' . $idx;

                                            $has_moodle_content = !empty($cro['actividades_moodle']) && $cro['actividades_moodle'] !== '[]';
                                            $tareas_display_content = "";
                                            if ($has_moodle_content) {
                                                $tareas_display_content = generarHtmlTareasMoodle($cro['actividades_moodle'], $renderCounters, $rubros_cantidades_render);
                                            } else {
                                                $tareas_display_content = $cro['tareas'] ?? '';
                                            }
                                            $modTrabajo = $cro['modalidad_trabajo'] ?? 'Asincrónico';
                                        @endphp
                                        <tr>
                                            <td class="text-center fw-bold crono-semana-num">
                                                {{ $idx + 1 }}
                                                <input type="hidden" name="c_semana[]" value="{{ $idx + 1 }}">
                                            </td>
                                            <td><input type="date" name="c_fecha[]" class="form-control form-control-sm" value="{{ $cro['fecha'] ?? '' }}"></td>
                                            <td><textarea id="{{ $idActExistente }}" name="c_actividad[]" class="form-control form-control-sm editor-rico-compacto">{{ $cro['actividad'] ?? '' }}</textarea></td>
                                            <td>
                                                <div class="form-control form-control-sm tarea-display-only bg-light border border-light-subtle rounded-3" style="min-height: 120px; overflow-y: auto; padding: 0.5rem 0.75rem;">
                                                    {!! !empty($tareas_display_content) ? $tareas_display_content : '<span class="text-muted small italic"><i class="bi bi-info-circle me-1"></i> Sin actividades configuradas. Utilice el botón "Config."</span>' !!}
                                                </div>
                                                <textarea id="{{ $idTarExistente }}" name="c_tareas[]" class="d-none">{{ $cro['tareas'] ?? '' }}</textarea>
                                            </td>
                                            <td class="text-center">
                                                <input type="hidden" name="c_moodle[]" class="moodle-data-input" value="{{ $cro['actividades_moodle'] ?? '' }}">
                                                <button type="button" class="btn btn-moodle w-100" onclick="abrirConfigMoodle(this)"><i class="bi bi-gear-fill me-1"></i> Config.</button>
                                                <div class="moodle-summary mt-2 d-flex flex-wrap justify-content-center gap-1"></div>
                                            </td>
                                            <td class="text-center td-modalidad {{ ($modTrabajo === 'Sincrónico') ? 'td-sincronico' : 'td-asincronico' }}">
                                                <select name="c_modalidad[]" class="form-select modalidad-select {{ ($modTrabajo === 'Sincrónico') ? 'select-sincronico' : 'select-asincronico' }}" onchange="actualizarEstiloModalidad(this)">
                                                    <option value="Sincrónico" {{ ($modTrabajo === 'Sincrónico') ? 'selected' : '' }}>Sincrónico</option>
                                                    <option value="Asincrónico" {{ ($modTrabajo === 'Asincrónico' || empty($modTrabajo)) ? 'selected' : '' }}>Asincrónico</option>
                                                </select>
                                                <div class="mt-1 zoom-logo-container {{ ($modTrabajo === 'Sincrónico') ? '' : 'd-none' }}">
                                                    <img src="{{ asset('imgs/zoom_logo.png') }}" class="zoom-logo-mini" alt="Zoom">
                                                </div>
                                            </td>
                                            <td class="text-center"><button type="button" class="btn btn-link text-danger p-0" onclick="eliminarFilaCronograma(this)"><i class="bi bi-trash fs-5"></i></button></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" onclick="agregarFilaCronograma()">
                                <i class="bi bi-plus-circle me-2"></i> + Fila
                            </button>
                        </div>
                    </div>

                    <!-- PESTAÑA 6: RÚBRICAS ANALÍTICAS OFICIALES CONESUP -->
                    <div class="tab-pane fade" id="rubricas_tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-card-checklist me-2"></i>Descripción de las Estrategias Evaluativas y Rúbricas</h5>
                                <small class="text-muted">Estructura analítica oficial CONESUP con descripción de entregables y niveles (Excelente, Bueno, En proceso, Insuficiente).</small>
                            </div>
                            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill small">
                                <i class="bi bi-info-circle me-1"></i> Diseñe o importe las rúbricas analíticas oficiales
                            </span>
                        </div>
                        <div class="alert alert-info border-0 shadow-sm rounded-4 p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                    <i class="bi bi-calculator fs-5"></i>
                                </div>
                                <div>
                                    <strong class="text-dark">Distribución de Puntajes según el Peso de Cada Entregable (Moodle):</strong>
                                    <div class="small text-muted">Moodle califica sumando los puntos de cada criterio. Los puntos se dividen exactamente entre los criterios (<code>Puntos Totales / Cant. Criterios</code>) para que la suma en nivel <strong>Excelente</strong> coincida al 100% con la nota máxima del trabajo.</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-warning text-dark fw-bold btn-sm rounded-pill px-3 shadow-sm text-nowrap" onclick="asignarPuntajesMoodleTodas()">
                                <i class="bi bi-magic me-1"></i> Distribuir Puntajes a Todas
                            </button>
                        </div>
                        <div id="contenedorRubricas">
                            @foreach($rubricas_data as $rub)
                                @php
                                    $desc_existente = $rub['descripcion_entregable'] ?? '';
                                    if (empty($desc_existente) && !empty($rub['contenido_json'])) {
                                        $dec_js = json_decode($rub['contenido_json'], true);
                                        if (is_array($dec_js) && !empty($dec_js['descripcion_entregable'])) {
                                            $desc_existente = $dec_js['descripcion_entregable'];
                                        }
                                    }
                                    $rubNom = $rub['nombre'] ?? '';
                                @endphp
                                <div class="card mb-4 rubrica-item border-0 shadow-sm rounded-4 overflow-hidden" data-id="rub_{{ $rub['id_rubrica'] ?? uniqid() }}">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-3 px-4">
                                        <div class="d-flex gap-3 align-items-center w-75">
                                            <select name="rubrica_nombre_sel[]" class="form-select form-select-sm w-50 fw-bold border-primary shadow-sm" onchange="toggleNombreRubrica(this)" data-seleccionado="{{ $rubNom }}">
                                                <option value="">Asociar a un Rubro...</option>
                                                <option value="CUSTOM" {{ ($rubNom === "CUSTOM") ? 'selected' : '' }}>-- Nombre Personalizado --</option>
                                                @if ($rubNom !== "CUSTOM" && !empty($rubNom))
                                                    <option value="{{ $rubNom }}" selected>{{ $rubNom }}</option>
                                                @endif
                                            </select>
                                            <input type="text" name="rubrica_nombre_custom[]" class="form-control form-control-sm w-50 {{ ($rubNom === 'CUSTOM') ? '' : 'd-none' }} shadow-sm" value="{{ $rubNom }}" placeholder="Nombre de la Rúbrica">
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportarRubricaCSV(this)" title="Exportar CSV"><i class="bi bi-download"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="abrirModalInyectarHTML(this)" title="Cargar HTML"><i class="bi bi-code-slash"></i></button>
                                            <button type="button" class="btn btn-sm btn-danger rounded-circle" onclick="this.closest('.rubrica-item').remove(); sincronizarSilabo();"><i class="bi bi-x"></i></button>
                                        </div>
                                    </div>
                                    <div class="px-4 py-2 bg-white border-bottom">
                                        <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-file-text me-1"></i>Descripción de la Estrategia Evaluativa (Entregable según formato CONESUP):</label>
                                        <textarea name="rubrica_descripcion[]" class="form-control form-control-sm" rows="2" placeholder="Describa en qué consiste el entregable, propósito formativo, alcance y condiciones de entrega...">{{ $desc_existente }}</textarea>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-bordered mb-0 tabla-rubrica-visual">
                                            @php
                                                $r_data = !empty($rub['contenido_json']) ? json_decode($rub['contenido_json'], true) : null;
                                            @endphp
                                            @if (is_array($r_data) && !empty($r_data['columnas']))
                                            <thead>
                                                <tr>
                                                    <th class="p-2 text-center" style="width: 20%; background-color: #f8fafc;"><div class="d-flex align-items-center justify-content-center">Criterio</div></th>
                                                    @foreach ($r_data['columnas'] as $i_c => $col_n)
                                                    <th class="p-2 text-center" style="background-color: #f8fafc;">
                                                        <div class="d-flex align-items-center justify-content-center">
                                                            <button type="button" class="btn btn-link text-danger btn-xs p-0 me-1" onclick="eliminarColumnaRubrica(this, {{ $i_c }})"><i class="bi bi-x-circle"></i></button>
                                                            <input type="text" class="form-control form-control-sm border-0 bg-transparent fw-bold text-center p-0" style="font-size: 0.75rem;" value="{{ $col_n }}" oninput="actualizarJSONRubrica(this, 'col', {{ $i_c }})">
                                                        </div>
                                                    </th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($r_data['filas'] ?? [] as $i_f => $fila)
                                                @php
                                                    $crit_t = $fila['criterio'] ?? ($fila['nombre'] ?? ('Criterio ' . ($i_f + 1)));
                                                @endphp
                                                <tr>
                                                    <td class="p-2" style="width: 20%; background-color: #fdfdfd;">
                                                        <div class="d-flex align-items-start">
                                                            <button type="button" class="btn btn-link text-danger btn-xs p-0 me-2 mt-1" onclick="eliminarFilaRubrica(this, {{ $i_f }})"><i class="bi bi-dash-circle"></i></button>
                                                            <textarea class="form-control form-control-sm border-0 fw-bold p-0 bg-transparent" rows="2" style="font-size: 0.8rem; resize: none;" oninput="actualizarJSONRubrica(this, 'row_name', {{ $i_f }})">{{ $crit_t }}</textarea>
                                                        </div>
                                                    </td>
                                                    @foreach ($r_data['columnas'] as $i_c => $col_n)
                                                    @php
                                                        $val_t = $fila['valores'][$i_c] ?? '';
                                                    @endphp
                                                    <td class="p-1">
                                                        <textarea class="form-control form-control-sm border-0 small" rows="3" style="font-size: 0.75rem; line-height: 1.2;" placeholder="..." oninput="actualizarJSONRubrica(this, 'val', {{ $i_f }}, {{ $i_c }})">{{ $val_t }}</textarea>
                                                    </td>
                                                    @endforeach
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            @endif
                                        </table>
                                        <textarea name="rubrica_json[]" class="d-none rubrica-json-input">{{ $rub['contenido_json'] ?? '' }}</textarea>
                                        <div class="p-3 bg-light border-top d-flex gap-3 align-items-center">
                                            <div class="input-group input-group-sm" style="width: 150px;">
                                                <span class="input-group-text">Peso %</span>
                                                <input type="number" step="0.1" name="rubrica_porcentaje[]" class="form-control" value="{{ $rub['porcentaje_total'] ?? 0 }}">
                                            </div>
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <span class="input-group-text">Cant.</span>
                                                <input type="number" name="rubrica_cantidad[]" class="form-control" value="{{ $rub['cantidad'] ?? 1 }}">
                                            </div>
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <span class="input-group-text">Puntos</span>
                                                <input type="number" name="rubrica_valor[]" class="form-control" value="{{ $rub['valor'] ?? 100 }}">
                                            </div>
                                            <div class="d-flex gap-2 ms-auto">
                                                <button type="button" class="btn btn-xs btn-outline-warning text-dark fw-semibold" onclick="asignarPuntajesRubrica(this)" title="Asignar o calcular puntajes para Moodle"><i class="bi bi-calculator me-1"></i>Puntajes Moodle</button>
                                                <button type="button" class="btn btn-xs btn-outline-primary" onclick="agregarColumnaRubrica(this)">+ Nivel</button>
                                                <button type="button" class="btn btn-xs btn-outline-success" onclick="agregarFilaRubrica(this)">+ Criterio</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-primary rounded-pill px-4" onclick="nuevaRubrica()"><i class="bi bi-plus-circle me-2"></i> Crear Rúbrica</button>
                        <button type="button" class="btn btn-outline-warning text-dark rounded-pill px-4 ms-2 shadow-sm fw-bold" onclick="asignarPuntajesMoodleTodas()"><i class="bi bi-calculator me-2"></i> Asignar Puntajes Moodle a Todas</button>
                        <button type="button" class="btn btn-info text-white rounded-pill px-4 ms-2 shadow-sm fw-bold" onclick="abrirGeneradorRubricaIA()"><i class="bi bi-cpu-fill me-2"></i> Crear con IA (Gemini)</button>
                    </div>

                    <!-- PESTAÑA 7: X-XI. BIBLIOGRAFÍA OFICIAL CONESUP -->
                    <div class="tab-pane fade" id="bibliografia_descriptor_tab">
                        <div class="mb-4 pb-3 border-bottom">
                            <label for="bibliografia" class="form-label fw-bold"><span class="badge-section-num me-1">X</span> Bibliografía obligatoria</label>
                            <textarea name="bibliografia" id="bibliografia" class="form-control editor-rico" rows="8" style="min-height: 250px;">{{ old('bibliografia', $curso->bibliografia) }}</textarea>
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Referencias bibliográficas obligatorias en formato APA 7 con texto explicativo del enlace para consultar o descargar.</small>
                        </div>
                        <div class="mb-3">
                            <label for="bibliografia_complementaria" class="form-label fw-bold"><span class="badge-section-num me-1">XI</span> Bibliografía complementaria</label>
                            <textarea name="bibliografia_complementaria" id="bibliografia_complementaria" class="form-control editor-rico" rows="8" style="min-height: 250px;">{{ old('bibliografia_complementaria', $curso->bibliografia_complementaria) }}</textarea>
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Referencias bibliográficas complementarias en formato APA 7 con enlaces a repositorios y bibliotecas digitales.</small>
                        </div>
                    </div>

                </div>

                <!-- Botones al final del formulario -->
                <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
                    <a href="{{ route('cursos.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </a>
                    <button type="button" onclick="validarYGuardarPlan()" class="btn btn-primary px-5 py-3 shadow rounded-pill fw-bold" style="background-color: var(--primary-color); border: none;">
                        <i class="bi bi-check-circle me-2"></i> Guardar Cambios del Curso
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<!-- Modal Configuración Moodle -->
<div class="modal fade" id="modalMoodle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-mortarboard-fill me-2"></i>Configurar Actividades Moodle del Cronograma</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="contenedorActividadesMoodle"></div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-outline-primary" onclick="agregarActividadMoodle('forum')">+ Foro</button>
                <button type="button" class="btn btn-outline-danger" onclick="agregarActividadMoodle('assign')">+ Tarea</button>
                <button type="button" class="btn btn-primary px-4" onclick="guardarConfigMoodle()">Guardar y Sincronizar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Inyectar HTML en Rúbricas -->
<div class="modal fade" id="modalInyectarHTML" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="bi bi-code-slash me-2"></i>Inyectar HTML en Rúbrica</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small">Pega aquí el código HTML de una tabla (<code>&lt;table&gt;...&lt;/table&gt;</code>). El sistema intentará parsearlo para generar la rúbrica.</p>
                <textarea id="htmlRubricaInput" class="form-control" rows="10" placeholder="Pega tu HTML aquí..."></textarea>
                <div class="alert alert-warning mt-3 small" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Solo se procesará la primera tabla encontrada. Asegúrate de que la estructura sea simple y clara.
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="procesarHTMLRubrica()">Procesar HTML</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  let filaActualMoodle = null;
  let rubricaTargetInyeccion = null;

  function configurarLimpiezaTinyMCE(editor) {
      editor.on('PreInit', function() {
          editor.parser.addNodeFilter('a', function(nodes) {
              for (let i = nodes.length - 1; i >= 0; i--) {
                  const node = nodes[i];
                  if (!node.firstChild || (node.firstChild.type === 3 && !node.firstChild.value.trim())) {
                      node.unwrap();
                  }
              }
          });
          editor.serializer.addNodeFilter('a', function(nodes) {
              for (let i = nodes.length - 1; i >= 0; i--) {
                  const node = nodes[i];
                  if (!node.firstChild || (node.firstChild.type === 3 && !node.firstChild.value.trim())) {
                      node.unwrap();
                  }
              }
          });
      });

      editor.on('init SetContent', function() {
          try {
              const doc = editor.getDoc();
              if (doc) {
                  const emptyAnchors = doc.querySelectorAll('a, .mce-item-anchor');
                  emptyAnchors.forEach(a => {
                      if (!a.textContent.trim() && !a.querySelector('img')) {
                          a.remove();
                      }
                  });
              }
          } catch(e) {}
      });

      editor.on('PastePreProcess', function(e) {
          if (e.content) {
              e.content = e.content.replace(/<a\b[^>]*>(?:\s|&nbsp;|<br\s*\/?>)*<\/a>/gi, '');
          }
      });

      editor.on('change keyup', function () {
          tinymce.triggerSave();
      });
  }

  function inicializarTinyMCE() {
      if (typeof tinymce === 'undefined') return;
      tinymce.init({
        license_key: 'gpl',
        selector: '.editor-rico, .editor-rico-normal',
        min_height: 380,
        language: 'es',
        plugins: 'code table lists link autoresize',
        toolbar: 'undo redo | blocks | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | indent outdent | bullist numlist | code | table | link | removeformat',
        branding: false,
        promotion: false,
        content_style: ".mce-item-anchor { display: none !important; width: 0 !important; height: 0 !important; visibility: hidden !important; }",
        autoresize_bottom_margin: 20,
        setup: configurarLimpiezaTinyMCE
      });

      tinymce.init({
        license_key: 'gpl',
        selector: '.editor-rico-compacto',
        min_height: 150,
        menubar: false,
        language: 'es',
        plugins: 'lists link code autoresize',
        toolbar: 'bold italic underline | bullist numlist | code | removeformat',
        branding: false,
        promotion: false,
        content_style: ".mce-item-anchor { display: none !important; width: 0 !important; height: 0 !important; visibility: hidden !important; }",
        autoresize_bottom_margin: 10,
        setup: configurarLimpiezaTinyMCE
      });
  }

  // Guardar pestaña activa y refrescar editores al cambiar de pestaña
  const triggerTabList = [].slice.call(document.querySelectorAll('#cursoTab button'));
  triggerTabList.forEach(function (triggerEl) {
    triggerEl.addEventListener('shown.bs.tab', function (event) {
      const targetId = event.target.getAttribute('data-bs-target').replace('#', '');
      const activeInput = document.getElementById('active_tab_input');
      if (activeInput) activeInput.value = targetId;

      if (typeof tinymce !== 'undefined') {
          setTimeout(() => {
              tinymce.editors.forEach(ed => {
                  ed.fire('ResizeEditor');
              });
          }, 100);
      }
    });
  });

  // Cálculo reactivo de distribución de horas
  function calcularDistribucionHoras() {
    const teo = parseInt(document.getElementById('horas_teoricas').value) || 0;
    const prac = parseInt(document.getElementById('horas_practicas').value) || 0;
    const indep = parseInt(document.getElementById('horas_independientes').value) || 0;
    const tot = teo + prac + indep;

    document.getElementById('horas_totales').value = tot;
    
    const textoPrac = (prac === 1) ? "1 hora práctica" : (prac + " horas prácticas");
    const textoTeo = (teo === 1) ? "1 hora teórica" : (teo + " horas teóricas");
    const textoIndep = (indep === 1) ? "1 hora de estudio independiente" : (indep + " horas de estudio independiente");
    
    const textoFinal = `Este curso comprende un total de ${tot} horas distribuidas en ${textoTeo}, ${textoPrac} y ${textoIndep}.`;
    document.getElementById('distribucion_horas').value = textoFinal;
  }

  // --- SINCRONIZACIÓN CENTRALIZADA ---
  function sincronizarSilabo() {
      actualizarOpcionesNombresEnRubricas();
      actualizarSelectsRubricas();
      calcularTotal();
      actualizarContadorReversoCronograma();
  }

  function actualizarOpcionesNombresEnRubricas() {
      const rubrosDeEvaluacion = [];
      document.querySelectorAll('#tablaEvaluacion tbody tr').forEach(tr => {
          const inputRubro = tr.querySelector('input[name="rubros[]"]');
          if (inputRubro && inputRubro.value.trim() !== '') {
              rubrosDeEvaluacion.push(inputRubro.value.trim());
          }
      });

      document.querySelectorAll('.rubrica-item').forEach(item => {
          const select = item.querySelector('select[name="rubrica_nombre_sel[]"]');
          const customInput = item.querySelector('input[name="rubrica_nombre_custom[]"]');
          if (!select) return;
          const valorPrevio = select.dataset.seleccionado || (select.value !== 'CUSTOM' ? select.value : (customInput ? customInput.value : ''));

          select.innerHTML = '<option value="">Asociar a un Rubro...</option>';
          select.innerHTML += '<option value="CUSTOM">-- Nombre Personalizado --</option>';

          let rubroEncontrado = false;
          rubrosDeEvaluacion.forEach(rubro => {
              const opt = document.createElement('option');
              opt.value = rubro;
              opt.textContent = rubro;
              select.appendChild(opt);
              if (rubro === valorPrevio) rubroEncontrado = true;
          });

          if (rubroEncontrado) {
              select.value = valorPrevio;
              if (customInput) customInput.classList.add('d-none');
          } else if (valorPrevio && valorPrevio !== 'CUSTOM') {
              select.value = 'CUSTOM';
              if (customInput) {
                  customInput.value = valorPrevio;
                  customInput.classList.remove('d-none');
              }
          } else {
              select.value = '';
              if (customInput) customInput.classList.add('d-none');
          }
      });
  }

  function actualizarSelectsRubricas() {
      const nombresDeRubricas = new Set();
      document.querySelectorAll('.rubrica-item').forEach(item => {
          const sel = item.querySelector('select[name="rubrica_nombre_sel[]"]');
          const custom = item.querySelector('input[name="rubrica_nombre_custom[]"]');
          let nombre = '';

          if (sel && sel.value === "CUSTOM" && custom && !custom.classList.contains('d-none')) {
              nombre = custom.value.trim();
          } else if (sel && sel.value && sel.value !== "CUSTOM") {
              nombre = sel.value.trim();
          }
          if (nombre) nombresDeRubricas.add(nombre);
      });

      document.querySelectorAll('.select-anexo-rubrica').forEach(select => {
          const valorPrevio = select.dataset.seleccionado || select.value;
          select.innerHTML = '<option value="">Ninguno</option>';
          nombresDeRubricas.forEach(nombre => {
              const opt = document.createElement('option');
              opt.value = nombre;
              opt.textContent = nombre;
              select.appendChild(opt);
          });
          select.value = valorPrevio;
          if (!select.value) select.value = "";
      });
  }

  function calcularTotal() {
      let totalP = 0;
      let totalC = 0;
      document.querySelectorAll('#tablaEvaluacion tbody tr').forEach((tr, i) => {
          const numEl = tr.querySelector('.eval-num');
          if (numEl) numEl.textContent = i + 1;
          const p = parseFloat(tr.querySelector('.valor-p')?.value) || 0;
          const c = parseInt(tr.querySelector('input[name="cantidades_eval[]"]')?.value) || 0;
          totalP += p;
          totalC += c;

          const unitValInput = tr.querySelector('.eval-unit-val');
          if (unitValInput) {
              unitValInput.value = (c > 0 && p > 0) ? (p / c).toFixed(2) + '%' : '0%';
          }
      });

      const totalPEl = document.getElementById('totalPorcentaje');
      const totalCEl = document.getElementById('totalCantidad');
      if (totalPEl) {
          totalPEl.textContent = Math.round(totalP) + '%';
          totalPEl.className = Math.round(totalP) === 100 ? 'text-center text-success fw-bold' : 'text-center text-danger fw-bold';
      }
      if (totalCEl) totalCEl.textContent = totalC;
  }

  function agregarFila() {
      const tbody = document.querySelector('#tablaEvaluacion tbody');
      const tr = document.createElement('tr');
      tr.innerHTML = `
          <td class="text-center fw-bold eval-num"></td>
          <td><input type="text" name="rubros[]" class="form-control fw-bold" placeholder="Nuevo Rubro" oninput="actualizarOpcionesNombresEnRubricas()"></td>
          <td><input type="number" name="porcentajes[]" class="form-control valor-p text-center fw-bold" value="0"></td>
          <td><input type="number" name="cantidades_eval[]" class="form-control text-center" value="1"></td>
          <td><input type="text" class="form-control text-center eval-unit-val" readonly value="0%"></td>
          <td>
              <select name="tipos_moodle_eval[]" class="form-select form-select-sm">
                  <option value="">-- Sin actividad Moodle --</option>
                  <option value="assign">📄 Tarea</option>
                  <option value="quiz">📝 Quiz / Examen</option>
                  <option value="forum">💬 Foro</option>
              </select>
          </td>
          <td>
              <select name="anexos_eval[]" class="form-select form-select-sm select-anexo-rubrica">
                  <option value="">Ninguno</option>
              </select>
          </td>
          <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); calcularTotal(); actualizarOpcionesNombresEnRubricas();">Eliminar</button></td>
      `;
      tbody.appendChild(tr);
      sincronizarSilabo();
  }

  function actualizarEstiloModalidad(select) {
      const td = select.closest('td');
      const container = td ? td.querySelector('.zoom-logo-container') : null;
      const isSincronico = (select.value === 'Sincrónico');
      if (td) {
          td.classList.toggle('td-sincronico', isSincronico);
          td.classList.toggle('td-asincronico', !isSincronico);
      }
      select.classList.toggle('select-sincronico', isSincronico);
      select.classList.toggle('select-asincronico', !isSincronico);
      if (container) container.classList.toggle('d-none', !isSincronico);
  }

  function agregarFilaCronograma() {
      const tbody = document.querySelector('#tablaCronograma tbody');
      const count = tbody.querySelectorAll('tr').length + 1;
      const uniqueIdAct = 'act_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7);
      const uniqueIdTar = 'tar_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7);

      const tr = document.createElement('tr');
      tr.innerHTML = `
          <td class="text-center fw-bold crono-semana-num">
              ${count}
              <input type="hidden" name="c_semana[]" value="${count}">
          </td>
          <td><input type="date" name="c_fecha[]" class="form-control form-control-sm"></td>
          <td><textarea id="${uniqueIdAct}" name="c_actividad[]" class="form-control form-control-sm editor-rico-compacto"></textarea></td>
          <td>
              <div class="form-control form-control-sm tarea-display-only bg-light border border-light-subtle rounded-3" style="min-height: 120px; overflow-y: auto; padding: 0.5rem 0.75rem;">
                  <span class="text-muted small italic"><i class="bi bi-info-circle me-1"></i> Sin actividades configuradas. Utilice el botón "Config."</span>
              </div>
              <textarea id="${uniqueIdTar}" name="c_tareas[]" class="d-none"></textarea>
          </td>
          <td class="text-center">
              <input type="hidden" name="c_moodle[]" class="moodle-data-input" value=''>
              <button type="button" class="btn btn-moodle w-100" onclick="abrirConfigMoodle(this)"><i class="bi bi-gear-fill me-1"></i> Config.</button>
              <div class="moodle-summary mt-2 d-flex flex-wrap justify-content-center gap-1"></div>
          </td>
          <td class="text-center td-modalidad td-asincronico">
              <select name="c_modalidad[]" class="form-select modalidad-select select-asincronico" onchange="actualizarEstiloModalidad(this)">
                  <option value="Sincrónico">Sincrónico</option>
                  <option value="Asincrónico" selected>Asincrónico</option>
              </select>
              <div class="mt-1 zoom-logo-container d-none">
                  <img src="{{ asset('imgs/zoom_logo.png') }}" class="zoom-logo-mini" alt="Zoom">
              </div>
          </td>
          <td class="text-center"><button type="button" class="btn btn-link text-danger p-0" onclick="eliminarFilaCronograma(this)"><i class="bi bi-trash fs-5"></i></button></td>
      `;
      tbody.appendChild(tr);

      tinymce.init({
          license_key: 'gpl',
          selector: '#' + uniqueIdAct,
          plugins: 'lists link autolink',
          toolbar: 'bold italic underline | bullist numlist | link | removeformat',
          menubar: false,
          statusbar: false,
          height: 120,
          branding: false,
          promotion: false,
          content_style: ".mce-item-anchor { display: none !important; width: 0 !important; height: 0 !important; visibility: hidden !important; }",
          setup: configurarLimpiezaTinyMCE
      });

      actualizarContadorReversoCronograma();
  }

  function eliminarFilaCronograma(btn) {
      const tr = btn.closest('tr');
      if (!tr) return;
      const semNum = tr.querySelector('.crono-semana-num')?.textContent?.trim() || '';

      const ejecutarEliminacion = () => {
          const txtAct = tr.querySelector('textarea[name="c_actividad[]"]');
          if (txtAct && typeof tinymce !== 'undefined' && tinymce.get(txtAct.id)) {
              tinymce.get(txtAct.id).remove();
          }
          tr.remove();

          document.querySelectorAll('#tablaCronograma tbody tr').forEach((r, idx) => {
              const semNumEl = r.querySelector('.crono-semana-num');
              if (semNumEl) {
                  semNumEl.innerHTML = `${idx + 1}<input type="hidden" name="c_semana[]" value="${idx + 1}">`;
              }
          });
          actualizarContadorReversoCronograma();
      };

      if (typeof Swal !== 'undefined') {
          Swal.fire({
              title: '¿Eliminar semana del cronograma?',
              text: `Se borrará la Semana ${semNum} junto con todo su contenido, fechas y actividades configuradas. Esta acción no se puede deshacer.`,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#dc3545',
              cancelButtonColor: '#64748b',
              confirmButtonText: '<i class="bi bi-trash me-1"></i> Sí, eliminar semana',
              cancelButtonText: 'Cancelar',
              customClass: {
                  confirmButton: 'btn btn-danger px-4 rounded-3 me-2 fw-bold',
                  cancelButton: 'btn btn-secondary px-4 rounded-3'
              },
              buttonsStyling: false
          }).then((result) => {
              if (result.isConfirmed) {
                  ejecutarEliminacion();
              }
          });
      } else {
          if (confirm(`¿Está seguro de eliminar la Semana ${semNum}? Se borrará todo su contenido.`)) {
              ejecutarEliminacion();
          }
      }
  }

  function depurarActividadesCronogramaSegunEvaluacion() {
      const rubrosSet = new Set();
      const rubrosCanonMap = {};
      const rubrosCantidades = {};

      document.querySelectorAll('#tablaEvaluacion tbody tr').forEach(tr => {
          const nom = tr.querySelector('input[name="rubros[]"]')?.value.trim();
          const cant = parseInt(tr.querySelector('input[name="cantidades_eval[]"]')?.value) || 0;
          if (nom && cant > 0) {
              rubrosSet.add(nom);
              rubrosCanonMap[nom.toLowerCase()] = nom;
              rubrosCantidades[nom] = (rubrosCantidades[nom] || 0) + cant;
          }
      });

      if (rubrosSet.size === 0) return;

      const conteoAcumulado = {};
      document.querySelectorAll('#tablaCronograma tbody tr').forEach(tr => {
          const input = tr.querySelector('.moodle-data-input');
          if (!input || !input.value) return;
          try {
              const acts = JSON.parse(input.value);
              if (!Array.isArray(acts)) return;

              const actsValidas = [];
              let modificado = false;

              acts.forEach(a => {
                  const rawTipo = (a.tipo || '').trim();
                  const rawNom = (a.nombre || '').trim();
                  const canon = rubrosCanonMap[rawTipo.toLowerCase()] || rubrosCanonMap[rawNom.toLowerCase()] || null;
                  const maxPermitido = canon ? (rubrosCantidades[canon] || 0) : 0;
                  const usado = canon ? (conteoAcumulado[canon] || 0) : 0;

                  if (canon && usado < maxPermitido) {
                      conteoAcumulado[canon] = usado + 1;
                      actsValidas.push({
                          tipo: canon,
                          nombre: '',
                          intro: a.intro || ''
                      });
                  } else {
                      modificado = true;
                  }
              });

              if (modificado) {
                  input.value = actsValidas.length > 0 ? JSON.stringify(actsValidas) : '';
                  actualizarResumenFila(tr, actsValidas);
              }
          } catch (e) {}
      });
  }

  function obtenerEstadisticasUsoMoodle(excluirFilaActual = false) {
      const rubrosSet = new Set();
      const rubrosCantidades = {};
      const rubroMoodleTypes = {};
      
      document.querySelectorAll('#tablaEvaluacion tbody tr').forEach(tr => {
          const nom = tr.querySelector('input[name="rubros[]"]')?.value.trim();
          const cant = parseInt(tr.querySelector('input[name="cantidades_eval[]"]')?.value) || 0;
          const mType = tr.querySelector('select[name="tipos_moodle_eval[]"]')?.value || '';
          if (nom) {
              rubrosSet.add(nom);
              rubrosCantidades[nom] = (rubrosCantidades[nom] || 0) + cant;
              rubroMoodleTypes[nom] = mType;
          }
      });

      const usoActual = {};
      document.querySelectorAll('#tablaCronograma tbody tr').forEach(tr => {
          if (excluirFilaActual && filaActualMoodle && tr === filaActualMoodle) return;
          const input = tr.querySelector('.moodle-data-input');
          if (input && input.value) {
              try {
                  JSON.parse(input.value).forEach(a => {
                      const rubroKey = a.tipo;
                      if (rubrosSet.has(rubroKey)) {
                          usoActual[rubroKey] = (usoActual[rubroKey] || 0) + 1;
                      }
                  });
              } catch(e) {}
          }
      });

      return { rubrosSet, rubrosCantidades, rubroMoodleTypes, usoActual };
  }

  function actualizarContadorReversoCronograma() {
      depurarActividadesCronogramaSegunEvaluacion();

      const subtituloEl = document.getElementById('progresoActividadesSubtitulo');
      const badgeEl = document.getElementById('badgeProgresoActividades');
      const progressBar = document.getElementById('barraProgresoActividades');
      if (!badgeEl || !progressBar) return;

      const stats = obtenerEstadisticasUsoMoodle(false);
      let totalPlanificadas = 0;
      stats.rubrosSet.forEach(r => {
          totalPlanificadas += (stats.rubrosCantidades[r] || 0);
      });

      let totalAsignadas = 0;
      Object.keys(stats.usoActual).forEach(r => {
          totalAsignadas += (stats.usoActual[r] || 0);
      });

      if (totalPlanificadas === 0) {
          if (subtituloEl) subtituloEl.textContent = "Defina los rubros y cantidades en la pestaña de Evaluación.";
          badgeEl.className = "badge bg-secondary px-3 py-2 rounded-pill fw-bold";
          badgeEl.textContent = "0 Actividades definidas";
          progressBar.style.width = "0%";
          progressBar.className = "progress-bar bg-secondary";
          return;
      }

      const pct = Math.min(100, Math.round((totalAsignadas / totalPlanificadas) * 100));
      const faltantes = Math.max(0, totalPlanificadas - totalAsignadas);

      progressBar.style.width = pct + "%";
      progressBar.setAttribute('aria-valuenow', pct);

      if (totalAsignadas === totalPlanificadas) {
          badgeEl.className = "badge bg-success px-3 py-2 rounded-pill fw-bold shadow-sm";
          badgeEl.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> ${totalAsignadas} de ${totalPlanificadas} Actividades (100% Completado)`;
          if (subtituloEl) subtituloEl.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check2-all me-1"></i> Todas las actividades han sido programadas correctamente en el cronograma.</span>`;
          progressBar.className = "progress-bar bg-success";
      } else if (totalAsignadas > totalPlanificadas) {
          const exceso = totalAsignadas - totalPlanificadas;
          badgeEl.className = "badge bg-danger px-3 py-2 rounded-pill fw-bold shadow-sm";
          badgeEl.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> ${totalAsignadas} de ${totalPlanificadas} Actividades (Exceso de ${exceso})`;
          if (subtituloEl) subtituloEl.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-exclamation-circle me-1"></i> Hay ${exceso} actividad(es) de más en el cronograma respecto a los rubros declarados.</span>`;
          progressBar.className = "progress-bar bg-danger";
      } else if (pct >= 50) {
          badgeEl.className = "badge bg-primary px-3 py-2 rounded-pill fw-bold shadow-sm";
          badgeEl.innerHTML = `<i class="bi bi-hourglass-split me-1"></i> ${totalAsignadas} de ${totalPlanificadas} (${pct}%) — Faltan ${faltantes}`;
          if (subtituloEl) subtituloEl.textContent = `Quedan ${faltantes} actividades pendientes por asignar en las semanas del curso.`;
          progressBar.className = "progress-bar bg-primary progress-bar-striped progress-bar-animated";
      } else {
          badgeEl.className = "badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold shadow-sm";
          badgeEl.innerHTML = `<i class="bi bi-hourglass-bottom me-1"></i> ${totalAsignadas} de ${totalPlanificadas} (${pct}%) — Faltan ${faltantes}`;
          if (subtituloEl) subtituloEl.textContent = `Quedan ${faltantes} actividades pendientes por asignar en las semanas del curso.`;
          progressBar.className = "progress-bar bg-warning progress-bar-striped progress-bar-animated";
      }
  }

  function abrirConfigMoodle(btn) {
      filaActualMoodle = btn.closest('tr');
      const cont = document.getElementById('contenedorActividadesMoodle');
      cont.innerHTML = "";

      const input = filaActualMoodle.querySelector('.moodle-data-input');
      let acts = [];
      if (input && input.value) {
          try { acts = JSON.parse(input.value); } catch(e) {}
      }

      if (acts.length === 0) {
          cont.innerHTML = '<p class="text-muted text-center py-4">No hay actividades Moodle en esta semana. Añada una usando los botones inferiores.</p>';
      } else {
          acts.forEach((act, idx) => {
              crearTarjetaActividadMoodle(act, idx);
          });
      }

      actualizarBotonesFooterMoodle();
      const modal = new bootstrap.Modal(document.getElementById('modalMoodle'));
      modal.show();
  }

  function actualizarBotonesFooterMoodle() {
      const footer = document.querySelector('#modalMoodle .modal-footer');
      if (!footer) return;
      footer.innerHTML = "";

      const stats = obtenerEstadisticasUsoMoodle(true);
      const rubros = Array.from(stats.rubrosSet);

      if (rubros.length === 0) {
          footer.innerHTML = `<div class="alert alert-warning w-100 mb-0 py-2 small shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i> Debe definir rubros en la pestaña de <b>Evaluación</b> para añadir actividades.</div>`;
      } else {
          rubros.forEach(r => {
              const cantMax = stats.rubrosCantidades[r] || 0;
              const modalActs = document.querySelectorAll('#contenedorActividadesMoodle .moodle-item');
              let cantEnModal = 0;
              modalActs.forEach(ma => {
                  const selTipo = ma.querySelector('.moodle-tipo');
                  if (selTipo && selTipo.value === r) cantEnModal++;
              });

              const cantEnUso = (stats.usoActual[r] || 0) + cantEnModal;
              const faltan = cantMax - cantEnUso;
              const mType = stats.rubroMoodleTypes[r] || 'assign';

              let btnClass = "btn-outline-danger";
              let icon = "bi-file-earmark-text";
              if (mType === 'forum') { btnClass = "btn-outline-primary"; icon = "bi-chat-left-text"; }
              else if (mType === 'quiz') { btnClass = "btn-outline-warning text-dark"; icon = "bi-question-circle"; }

              const btn = document.createElement('button');
              btn.type = 'button';
              btn.className = `btn btn-sm ${btnClass} rounded-pill px-3 shadow-sm ${faltan <= 0 ? 'opacity-75' : ''}`;
              btn.disabled = (faltan <= 0);
              btn.innerHTML = `<i class="bi ${icon} me-1"></i> + ${r} <span class="badge ms-1" style="background-color: ${faltan <= 0 ? '#64748b' : '#0f172a'}; color: #ffffff !important; font-weight: 700; padding: 0.25em 0.55em; border-radius: 999px;">${faltan > 0 ? faltan : 0}</span>`;
              btn.onclick = () => {
                  if (faltan > 0) {
                      agregarActividadMoodle(mType, r);
                  }
              };
              footer.appendChild(btn);
          });
      }

      document.querySelectorAll('#contenedorActividadesMoodle .moodle-tipo').forEach(sel => {
          const valActual = sel.value;
          Array.from(sel.options).forEach(opt => {
              if (!opt.value) return;
              const r = opt.value;
              const cantMax = stats.rubrosCantidades[r] || 0;
              let cantEnModal = 0;
              document.querySelectorAll('#contenedorActividadesMoodle .moodle-tipo').forEach(s2 => {
                  if (s2.value === r) cantEnModal++;
              });
              const totalUsado = (stats.usoActual[r] || 0) + cantEnModal;
              opt.disabled = (r !== valActual && totalUsado >= cantMax);
          });
      });

      const btnGuardar = document.createElement('button');
      btnGuardar.type = 'button';
      btnGuardar.className = 'btn btn-primary rounded-pill px-4 ms-auto shadow-sm fw-bold';
      btnGuardar.textContent = 'Guardar y Sincronizar';
      btnGuardar.onclick = guardarConfigMoodle;
      footer.appendChild(btnGuardar);
  }

  function crearTarjetaActividadMoodle(act, idx) {
      const cont = document.getElementById('contenedorActividadesMoodle');
      const div = document.createElement('div');
      div.className = "card mb-3 moodle-item border-0 shadow-sm rounded-3 overflow-hidden";
      div.dataset.index = idx;

      const stats = obtenerEstadisticasUsoMoodle(true);
      let optionsRubros = `<option value="">-- Seleccionar Rubro --</option>`;
      stats.rubrosSet.forEach(r => {
          optionsRubros += `<option value="${r}" ${act.tipo === r ? 'selected' : ''}>${r}</option>`;
      });

      div.innerHTML = `
          <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
              <span class="fw-bold small text-primary"><i class="bi bi-mortarboard-fill me-1"></i> Actividad Moodle</span>
              <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('.moodle-item').remove(); actualizarBotonesFooterMoodle();"><i class="bi bi-trash fs-6"></i></button>
          </div>
          <div class="card-body p-3">
              <div class="mb-2">
                  <label class="form-label small mb-1 fw-bold">Rubro / Tipo:</label>
                  <select class="form-select form-select-sm moodle-tipo" onchange="actualizarBotonesFooterMoodle()">${optionsRubros}</select>
              </div>
              <div class="mb-0">
                  <label class="form-label small mb-1 fw-bold">Instrucciones / Descripción:</label>
                  <textarea class="form-control form-control-sm moodle-intro" rows="2" placeholder="Instrucciones para el estudiante...">${act.intro || ''}</textarea>
              </div>
          </div>
      `;
      cont.appendChild(div);
  }

  function agregarActividadMoodle(tipo, rubroDefault = '') {
      const rubro = rubroDefault || tipo;
      const stats = obtenerEstadisticasUsoMoodle(true);
      if (rubro && stats.rubrosSet.has(rubro)) {
          const cantMax = stats.rubrosCantidades[rubro] || 0;
          let cantEnModal = 0;
          document.querySelectorAll('#contenedorActividadesMoodle .moodle-tipo').forEach(sel => {
              if (sel.value === rubro) cantEnModal++;
          });
          const usado = (stats.usoActual[rubro] || 0) + cantEnModal;
          if (usado >= cantMax) {
              return;
          }
      }

      const cont = document.getElementById('contenedorActividadesMoodle');
      if (cont.querySelector('p.text-muted')) cont.innerHTML = "";
      crearTarjetaActividadMoodle({
          tipo: rubro,
          nombre: '',
          intro: ''
      }, cont.querySelectorAll('.moodle-item').length);
      actualizarBotonesFooterMoodle();
  }

  function guardarConfigMoodle() {
      if (!filaActualMoodle) return;
      const items = document.querySelectorAll('#contenedorActividadesMoodle .moodle-item');
      const acts = [];

      items.forEach(it => {
          const t = it.querySelector('.moodle-tipo')?.value.trim();
          const i = it.querySelector('.moodle-intro')?.value.trim();
          if (t) acts.push({ tipo: t, nombre: '', intro: i });
      });

      const input = filaActualMoodle.querySelector('.moodle-data-input');
      if (input) input.value = JSON.stringify(acts);

      actualizarResumenFila(filaActualMoodle, acts);

      const modal = bootstrap.Modal.getInstance(document.getElementById('modalMoodle'));
      if (modal) modal.hide();

      actualizarContadorReversoCronograma();
  }

  function resolverEstiloActividadMoodle(act) {
      const stats = obtenerEstadisticasUsoMoodle(false);
      const rawTipo = String(act?.tipo || '').trim();
      const rawNom = String(act?.nombre || '').trim();
      const mType = (stats.rubroMoodleTypes && stats.rubroMoodleTypes[rawTipo]) ? stats.rubroMoodleTypes[rawTipo] : '';
      const combined = (mType + ' ' + rawTipo + ' ' + rawNom).toLowerCase();

      if (mType === 'quiz' || /quiz|prueba|examen|cuestionario|corto|autoevaluaci/.test(combined)) {
          return { tipo: 'quiz', colorClass: 'badge-quiz', bgColor: '#ff9800', textColor: '#212529', icon: 'question-circle-fill', borderColor: '#ff9800' };
      }
      if (mType === 'forum' || /foro|forum|debate|discusi/.test(combined)) {
          return { tipo: 'forum', colorClass: 'badge-forum', bgColor: '#007bff', textColor: '#ffffff', icon: 'chat-dots-fill', borderColor: '#007bff' };
      }
      return { tipo: 'assign', colorClass: 'badge-assign', bgColor: '#dc3545', textColor: '#ffffff', icon: 'file-earmark-arrow-up-fill', borderColor: '#dc3545' };
  }

  function renderizarIconosMoodleFila(fila, acts) {
      const summary = fila.querySelector('.moodle-summary');
      if (!summary) return;
      summary.innerHTML = "";

      if (!Array.isArray(acts) || acts.length === 0) return;

      acts.forEach(a => {
          const info = resolverEstiloActividadMoodle(a);
          const span = document.createElement('span');
          span.className = `moodle-activity-badge ${info.colorClass} shadow-sm`;
          span.title = a.nombre ? `${a.tipo}: ${a.nombre}` : (a.tipo || 'Actividad Moodle');
          span.style.cssText = `border-radius: 6px; padding: 4px 8px; font-size: 0.85rem; display: inline-flex; align-items: center; justify-content: center; background-color: ${info.bgColor}; color: ${info.textColor};`;
          span.innerHTML = `<i class="bi bi-${info.icon}"></i>`;
          summary.appendChild(span);
      });
  }

  function inicializarResumenesMoodle() {
      document.querySelectorAll('#tablaCronograma tbody tr').forEach(fila => {
          const input = fila.querySelector('.moodle-data-input');
          if (!input || !input.value) {
              renderizarIconosMoodleFila(fila, []);
              return;
          }
          try {
              const acts = JSON.parse(input.value);
              renderizarIconosMoodleFila(fila, Array.isArray(acts) ? acts : []);
          } catch (e) {
              renderizarIconosMoodleFila(fila, []);
          }
      });
  }

  function actualizarResumenFila(fila, acts) {
      renderizarIconosMoodleFila(fila, acts);

      const tareaDisplay = fila.querySelector('.tarea-display-only');
      const tareaInput = fila.querySelector('textarea[name="c_tareas[]"]');

      if (acts.length > 0) {
          if (tareaDisplay) {
              let html = "";
              acts.forEach((a, idx) => {
                  const info = resolverEstiloActividadMoodle(a);
                  const introHtml = a.intro ? a.intro.replace(/\r\n|\r|\n/g, '<br>') : '';
                  const labelDisplay = (a.tipo || 'TAREA').toUpperCase() + ' (' + (idx + 1) + ')';
                  html += `<div style="margin-bottom: 15px; border-left: 4px solid ${info.borderColor}; padding-left: 12px;">
                      <p style="margin: 0; font-weight: 800; font-size: 0.75rem; text-transform: uppercase;"><span style="background-color: #ffff00; color: #000000; padding: 1px 4px; border-radius: 3px;">${labelDisplay}</span></p>
                      ${introHtml ? `<p style="margin: 5px 0 0 0; font-weight: 400; font-size: 0.9rem; color: #000000; line-height: 1.4;">${introHtml}</p>` : ''}
                  </div>`;
              });
              tareaDisplay.innerHTML = html;
              if (tareaInput) tareaInput.value = html;
          }
      } else {
          if (tareaDisplay) {
              tareaDisplay.innerHTML = '<span class="text-muted small italic"><i class="bi bi-info-circle me-1"></i> Sin actividades configuradas. Utilice el botón "Config."</span>';
          }
          if (tareaInput) tareaInput.value = '';
      }
  }

  // --- RÚBRICAS JS ---
  function inicializarRubricasExistentes() {
      document.querySelectorAll('.rubrica-item').forEach(item => renderizarTablaRubrica(item));
  }

  function nuevaRubrica() {
      const contenedor = document.getElementById('contenedorRubricas');
      const item = document.createElement('div');
      item.className = 'card mb-4 rubrica-item border-0 shadow-sm rounded-4 overflow-hidden';
      
      const dataInicial = {
          columnas: ["Excelente (5)", "Bueno (3.5)", "En proceso (2)", "Insuficiente (0)"],
          filas: [{ criterio: "Criterio 1", valores: ["", "", "", ""] }]
      };

      item.innerHTML = `
          <div class="card-header bg-light d-flex justify-content-between align-items-center py-3 px-4">
              <div class="d-flex gap-3 align-items-center w-75">
                  <select name="rubrica_nombre_sel[]" class="form-select form-select-sm w-50 fw-bold border-primary shadow-sm" onchange="toggleNombreRubrica(this)">
                      <option value="">Asociar a un Rubro...</option>
                      <option value="CUSTOM">-- Nombre Personalizado --</option>
                  </select>
                  <input type="text" name="rubrica_nombre_custom[]" class="form-control form-control-sm w-50 d-none shadow-sm" placeholder="Nombre de la Rúbrica">
              </div>
              <div class="d-flex gap-2">
                  <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportarRubricaCSV(this)" title="Exportar CSV"><i class="bi bi-download"></i></button>
                  <button type="button" class="btn btn-sm btn-outline-info" onclick="this.nextElementSibling.click()" title="Importar CSV"><i class="bi bi-upload"></i></button>
                  <input type="file" class="d-none" accept=".csv" onchange="importarRubricaCSV(this)">
                  <button type="button" class="btn btn-sm btn-outline-secondary" onclick="abrirModalInyectarHTML(this)" title="Cargar HTML"><i class="bi bi-code-slash"></i></button>
                  <button type="button" class="btn btn-sm btn-danger rounded-circle shadow-sm" onclick="this.closest('.rubrica-item').remove(); sincronizarSilabo();"><i class="bi bi-x"></i></button>
              </div>
          </div>
          <div class="px-4 py-2 bg-white border-bottom">
              <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-file-text me-1"></i>Descripción de la Estrategia Evaluativa (Entregable según formato CONESUP):</label>
              <textarea name="rubrica_descripcion[]" class="form-control form-control-sm" rows="2" placeholder="Describa en qué consiste el entregable, propósito formativo, alcance y condiciones de entrega..."></textarea>
          </div>
          <div class="card-body p-0">
              <div class="table-responsive"><table class="table table-sm table-bordered mb-0 tabla-rubrica-visual"></table></div>
              <textarea name="rubrica_json[]" class="d-none rubrica-json-input">${JSON.stringify(dataInicial)}</textarea>
              <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center">
                  <div class="d-flex gap-3 align-items-center">
                      <div class="input-group input-group-sm shadow-sm" style="width: 150px;"><span class="input-group-text bg-white fw-bold text-muted">% Peso</span><input type="number" step="0.1" name="rubrica_porcentaje[]" class="form-control" placeholder="0"></div>
                      <div class="input-group input-group-sm shadow-sm" style="width: 120px;"><span class="input-group-text bg-white fw-bold text-muted">Cant.</span><input type="number" name="rubrica_cantidad[]" class="form-control" value="1"></div>
                      <div class="input-group input-group-sm shadow-sm" style="width: 120px;"><span class="input-group-text bg-white fw-bold text-muted">Puntos</span><input type="number" name="rubrica_valor[]" class="form-control" value="100"></div>
                  </div>
                  <div class="d-flex gap-2">
                      <button type="button" class="btn btn-xs btn-outline-warning text-dark fw-semibold" onclick="asignarPuntajesRubrica(this)" title="Asignar o calcular puntajes para Moodle"><i class="bi bi-calculator me-1"></i>Puntajes Moodle</button>
                      <button type="button" class="btn btn-xs btn-outline-primary" onclick="agregarColumnaRubrica(this)">+ Nivel</button>
                      <button type="button" class="btn btn-xs btn-outline-success" onclick="agregarFilaRubrica(this)">+ Criterio</button>
                  </div>
              </div>
          </div>`;
      contenedor.appendChild(item);
      renderizarTablaRubrica(item);
      sincronizarSilabo();
  }

  function toggleNombreRubrica(select) {
      const customInput = select.closest('.d-flex').querySelector('input[name="rubrica_nombre_custom[]"]');
      if (customInput) customInput.classList.toggle('d-none', select.value !== 'CUSTOM');
      if (select.value !== 'CUSTOM') {
          select.dataset.seleccionado = select.value;
          
          const rubroSeleccionado = select.value;
          let porcentaje = 0;
          let cantidad = 1;
          
          document.querySelectorAll('#tablaEvaluacion tbody tr').forEach(tr => {
              const nom = tr.querySelector('input[name="rubros[]"]')?.value.trim();
              if (nom === rubroSeleccionado) {
                  porcentaje = parseFloat(tr.querySelector('input[name="porcentajes[]"]')?.value) || 0;
                  cantidad = parseInt(tr.querySelector('input[name="cantidades_eval[]"]')?.value) || 1;
              }
          });
          
          const valorActividad = cantidad > 0 ? (porcentaje / cantidad) : 0;
          
          const cardBody = select.closest('.rubrica-item');
          const inputPorc = cardBody.querySelector('input[name="rubrica_porcentaje[]"]');
          const inputCant = cardBody.querySelector('input[name="rubrica_cantidad[]"]');
          const inputValor = cardBody.querySelector('input[name="rubrica_valor[]"]');
          
          if (inputPorc) inputPorc.value = porcentaje;
          if (inputCant) inputCant.value = cantidad;
          if (inputValor) inputValor.value = Number.isInteger(valorActividad) ? valorActividad : valorActividad.toFixed(2);
      }
      sincronizarSilabo();
  }

  function escapeHtmlRubrica(str) {
      if (str === null || str === undefined) return '';
      return String(str)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
  }

  function renderizarTablaRubrica(item) {
      const jsonInput = item.querySelector('.rubrica-json-input');
      if (!jsonInput) return;
      const jsonStr = jsonInput.value;
      if (!jsonStr) return;
      let data;
      try {
          data = JSON.parse(jsonStr);
      } catch(e) {
          data = { columnas: ["Excelente", "Bueno", "En proceso", "Insuficiente"], filas: [{ criterio: "Criterio 1", valores: ["", "", "", ""] }] };
      }
      const table = item.querySelector('.tabla-rubrica-visual');
      if (!table) return;
      
      let html = `<thead><tr>`;
      html += `<th class="p-2 text-center" style="width: 20%; background-color: #f8fafc;"><div class="d-flex align-items-center justify-content-center">Criterio</div></th>`;
      (data.columnas || []).forEach((col, i) => {
          const colSafe = escapeHtmlRubrica(col);
          html += `<th class="p-2 text-center" style="background-color: #f8fafc;"><div class="d-flex align-items-center justify-content-center">
              <button type="button" class="btn btn-link text-danger btn-xs p-0 me-1" onclick="eliminarColumnaRubrica(this, ${i})"><i class="bi bi-x-circle"></i></button>
              <input type="text" class="form-control form-control-sm border-0 bg-transparent fw-bold text-center p-0" style="font-size: 0.75rem;" value="${colSafe}" oninput="actualizarJSONRubrica(this, 'col', ${i})">
          </div></th>`;
      });
      html += `</tr></thead><tbody>`;
      
      (data.filas || []).forEach((fila, i) => {
          const crit = fila.criterio || fila.nombre || `Criterio ${i + 1}`;
          const critSafe = escapeHtmlRubrica(crit);
          html += `<tr><td class="p-2" style="width: 20%; background-color: #fdfdfd;"><div class="d-flex align-items-start">
              <button type="button" class="btn btn-link text-danger btn-xs p-0 me-2 mt-1" onclick="eliminarFilaRubrica(this, ${i})"><i class="bi bi-dash-circle"></i></button>
              <textarea class="form-control form-control-sm border-0 fw-bold p-0 bg-transparent" rows="2" style="font-size: 0.8rem; resize: none;" oninput="actualizarJSONRubrica(this, 'row_name', ${i})">${critSafe}</textarea>
          </div></td>`;
          (data.columnas || []).forEach((_, j) => {
              const rawVal = (fila.valores && fila.valores[j] !== undefined && fila.valores[j] !== null) ? fila.valores[j] : '';
              const valSafe = escapeHtmlRubrica(rawVal);
              html += `<td class="p-1"><textarea class="form-control form-control-sm border-0 small" rows="3" style="font-size: 0.75rem; line-height: 1.2;" placeholder="..." oninput="actualizarJSONRubrica(this, 'val', ${i}, ${j})">${valSafe}</textarea></td>`;
          });
          html += `</tr>`;
      });
      
      html += `</tbody>`;
      table.innerHTML = html;
  }

  function actualizarJSONRubrica(el, type, idx, idx2) {
      const item = el.closest('.rubrica-item');
      const data = JSON.parse(item.querySelector('.rubrica-json-input').value);
      if (type === 'col') data.columnas[idx] = el.value;
      if (type === 'row_name') {
          if (!data.filas[idx]) data.filas[idx] = { criterio: el.value, valores: [] };
          data.filas[idx].criterio = el.value;
      }
      if (type === 'val') {
          if (!data.filas[idx]) data.filas[idx] = { criterio: '', valores: [] };
          if (!data.filas[idx].valores) data.filas[idx].valores = [];
          data.filas[idx].valores[idx2] = el.value;
      }
      item.querySelector('.rubrica-json-input').value = JSON.stringify(data);
  }

  function agregarFilaRubrica(btn) {
      const item = btn.closest('.rubrica-item');
      const data = JSON.parse(item.querySelector('.rubrica-json-input').value);
      data.filas.push({ criterio: "Nuevo Criterio", valores: new Array(data.columnas.length).fill("") });
      item.querySelector('.rubrica-json-input').value = JSON.stringify(data);
      renderizarTablaRubrica(item);
  }

  function eliminarFilaRubrica(btn, idx) {
      const item = btn.closest('.rubrica-item');
      const data = JSON.parse(item.querySelector('.rubrica-json-input').value);
      data.filas.splice(idx, 1);
      item.querySelector('.rubrica-json-input').value = JSON.stringify(data);
      renderizarTablaRubrica(item);
  }

  function agregarColumnaRubrica(btn) {
      const item = btn.closest('.rubrica-item');
      const data = JSON.parse(item.querySelector('.rubrica-json-input').value);
      data.columnas.push("Nuevo Nivel");
      data.filas.forEach(f => {
          if (!f.valores) f.valores = [];
          f.valores.push("");
      });
      item.querySelector('.rubrica-json-input').value = JSON.stringify(data);
      renderizarTablaRubrica(item);
  }

  function eliminarColumnaRubrica(btn, idx) {
      const item = btn.closest('.rubrica-item');
      const data = JSON.parse(item.querySelector('.rubrica-json-input').value);
      if (data.columnas.length <= 1) return;
      data.columnas.splice(idx, 1);
      data.filas.forEach(f => {
          if (f.valores && f.valores.length > idx) f.valores.splice(idx, 1);
      });
      item.querySelector('.rubrica-json-input').value = JSON.stringify(data);
      renderizarTablaRubrica(item);
  }

  function formatPuntajeLimpio(num) {
      if (isNaN(num)) return 0;
      const r = Math.round(num * 100) / 100;
      return Number.isInteger(r) ? r : parseFloat(r.toFixed(2));
  }

  function aplicarPuntajesAItemRubrica(item) {
      const jsonInput = item.querySelector('.rubrica-json-input');
      if (!jsonInput || !jsonInput.value) return null;
      
      let data;
      try {
          data = JSON.parse(jsonInput.value);
      } catch(e) {
          return null;
      }
      
      if (!data.columnas || data.columnas.length === 0) return null;
      
      const numCols = data.columnas.length;
      const numFilas = (data.filas && data.filas.length > 0) ? data.filas.length : 1;
      const inputValor = item.querySelector('input[name="rubrica_valor[]"]');
      const inputPorc = item.querySelector('input[name="rubrica_porcentaje[]"]');
      let totalPuntos = parseFloat(inputValor?.value);
      if (isNaN(totalPuntos) || totalPuntos <= 0) {
          totalPuntos = parseFloat(inputPorc?.value) || 100;
      }
      
      const ptsMax = formatPuntajeLimpio(totalPuntos / numFilas);
      const scores = [];
      
      if (numCols === 4) {
          // 4 niveles estándar CONESUP / Moodle: 100%, 75%, 50%, 0%
          // REGLA MOODLE: El último nivel (Insuficiente) DEBE ser 0 absoluto para permitir calificar
          scores.push(ptsMax);
          scores.push(formatPuntajeLimpio(ptsMax * 0.75));
          scores.push(formatPuntajeLimpio(ptsMax * 0.50));
          scores.push(0);
      } else {
          for (let i = 0; i < numCols; i++) {
              if (i === numCols - 1) {
                  scores.push(0); // Último nivel siempre 0 en Moodle
              } else {
                  const factor = (numCols > 1) ? (1 - (i / (numCols - 1))) : 1.0;
                  scores.push(formatPuntajeLimpio(ptsMax * factor));
              }
          }
      }
      
      data.columnas = data.columnas.map((col, idx) => {
          const baseName = col.replace(/\s*\([^)]*\)/g, '').trim() || `Nivel ${idx + 1}`;
          const sc = scores[idx] !== undefined ? scores[idx] : (idx === numCols - 1 ? 0 : formatPuntajeLimpio(ptsMax * (1 - (idx / (numCols - 1)))));
          return `${baseName} (${sc})`;
      });
      
      jsonInput.value = JSON.stringify(data);
      renderizarTablaRubrica(item);
      sincronizarSilabo();
      
      return { totalPuntos, numFilas, ptsMax };
  }

  function asignarPuntajesRubrica(btn) {
      const item = btn.closest('.rubrica-item');
      if (!item) return;
      
      const res = aplicarPuntajesAItemRubrica(item);
      if (res) {
          const totalExcelente = formatPuntajeLimpio(res.ptsMax * res.numFilas);
          Swal.fire({
              icon: 'success',
              title: 'Puntajes Distribuidos por Peso',
              html: `
                  <p class="mb-3 text-muted">El puntaje se calculó dividiendo el valor del entregable entre los criterios:</p>
                  <div class="p-3 bg-light rounded-3 text-start small border">
                      <div class="d-flex justify-content-between mb-1"><span>Puntaje total del entregable:</span> <strong>${res.totalPuntos} pts</strong></div>
                      <div class="d-flex justify-content-between mb-1"><span>Cantidad de criterios:</span> <strong>${res.numFilas} criterios</strong></div>
                      <div class="d-flex justify-content-between mb-1"><span>Valor por criterio (Excelente):</span> <span class="badge bg-success fs-6">${res.ptsMax} pts</span></div>
                      <hr class="my-2">
                      <div class="d-flex justify-content-between text-success"><span>Suma total en Excelente (${res.numFilas} × ${res.ptsMax}):</span> <strong>${totalExcelente} pts</strong></div>
                  </div>
              `,
              timer: 3500,
              showConfirmButton: false
          });
      }
  }

  function asignarPuntajesMoodleTodas() {
      const items = document.querySelectorAll('.rubrica-item');
      if (!items.length) {
          Swal.fire('Atención', 'No hay rúbricas registradas en este curso.', 'info');
          return;
      }
      
      let count = 0;
      items.forEach(item => {
          if (aplicarPuntajesAItemRubrica(item)) count++;
      });
      
      Swal.fire({
          icon: 'success',
          title: '¡Puntajes Distribuidos por Peso!',
          text: `Se calcularon y asignaron los puntajes proporcionales exactos a las ${count} rúbricas según el peso y la cantidad de criterios de cada entregable.`,
          timer: 3500,
          showConfirmButton: false
      });
  }

  function exportarRubricaCSV(btn) {
      const item = btn.closest('.rubrica-item');
      const data = JSON.parse(item.querySelector('.rubrica-json-input').value);
      let csv = "Criterio;" + data.columnas.join(';') + "\n";
      data.filas.forEach(f => csv += (f.criterio || f.nombre) + ';' + (f.valores || []).join(';') + "\n");
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.setAttribute("download", "rubrica.csv");
      link.click();
  }

  function importarRubricaCSV(input) {
      const file = input.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = function(e) {
          const text = e.target.result;
          const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
          if (lines.length < 2) return;
          const headers = lines[0].split(';');
          headers.shift();
          const filas = [];
          for (let i = 1; i < lines.length; i++) {
              const parts = lines[i].split(';');
              const criterio = parts.shift();
              filas.push({ criterio: criterio, valores: parts });
          }
          const item = input.closest('.rubrica-item');
          const data = { columnas: headers, filas: filas };
          item.querySelector('.rubrica-json-input').value = JSON.stringify(data);
          renderizarTablaRubrica(item);
          sincronizarSilabo();
      };
      reader.readAsText(file);
      input.value = '';
  }

  function abrirModalInyectarHTML(btn) {
      rubricaTargetInyeccion = btn.closest('.rubrica-item');
      document.getElementById('htmlRubricaInput').value = '';
      const modal = new bootstrap.Modal(document.getElementById('modalInyectarHTML'));
      modal.show();
  }

  function procesarHTMLRubrica() {
      if (!rubricaTargetInyeccion) return;
      const htmlInput = document.getElementById('htmlRubricaInput').value;
      const parser = new DOMParser();
      const doc = parser.parseFromString(htmlInput, 'text/html');
      const table = doc.querySelector('table');

      if (!table) {
          Swal.fire('Error', 'No se encontró ninguna tabla en el HTML proporcionado.', 'error');
          return;
      }

      const newRubricaData = { columnas: [], filas: [] };
      let headerCells = table.querySelectorAll('thead th');
      if (headerCells.length === 0) headerCells = table.querySelector('tr')?.querySelectorAll('th');
      if (headerCells.length === 0) headerCells = table.querySelector('tr')?.querySelectorAll('td');

      if (headerCells && headerCells.length > 0) {
          let isFirstHeaderCriterion = false;
          if (headerCells.length > 1 && headerCells[0].textContent.trim().toLowerCase().includes('criterio')) {
              isFirstHeaderCriterion = true;
          }

          headerCells.forEach((th, idx) => {
              if (idx === 0 && isFirstHeaderCriterion) return;
              let colText = th.textContent.trim() || `Nivel ${idx}`;
              newRubricaData.columnas.push(colText);
          });
      }

      const rows = table.querySelectorAll('tbody tr').length > 0 ? table.querySelectorAll('tbody tr') : table.querySelectorAll('tr');
      let startRow = (headerCells && headerCells.length > 0 && table.querySelectorAll('tbody tr').length === 0) ? 1 : 0;

      for (let i = startRow; i < rows.length; i++) {
          const cells = rows[i].querySelectorAll('td, th');
          if (cells.length > 0) {
              const criterio = cells[0].textContent.trim() || `Criterio ${i}`;
              const valores = [];
              for (let j = 1; j < cells.length; j++) {
                  valores.push(cells[j].textContent.trim());
              }
              while (valores.length < newRubricaData.columnas.length) {
                  valores.push("");
              }
              newRubricaData.filas.push({ criterio, valores });
          }
      }

      if (newRubricaData.columnas.length === 0) {
          newRubricaData.columnas = ["Excelente (5)", "Bueno (3.5)", "En proceso (2)", "Insuficiente (0)"];
      }

      rubricaTargetInyeccion.querySelector('.rubrica-json-input').value = JSON.stringify(newRubricaData);
      renderizarTablaRubrica(rubricaTargetInyeccion);
      sincronizarSilabo();
      bootstrap.Modal.getInstance(document.getElementById('modalInyectarHTML')).hide();
      Swal.fire('Éxito', 'HTML procesado y rúbrica actualizada.', 'success');
  }

  // --- ASISTENTE DE RÚBRICAS CON IA (GEMINI) ---
  function abrirGeneradorRubricaIA() {
      const rubrosDeclarados = [];
      document.querySelectorAll('input[name="rubros[]"]').forEach(input => {
          const val = input.value.trim();
          if (val && !rubrosDeclarados.includes(val)) {
              rubrosDeclarados.push(val);
          }
      });

      let opcionesHtml = '';
      rubrosDeclarados.forEach(r => {
          opcionesHtml += `<option value="${r}">${r}</option>`;
      });

      Swal.fire({
          title: '🤖 Asistente de Rúbricas con IA (Gemini)',
          html: `
              <div class="text-start small text-muted mb-3">
                  Completa los detalles de la actividad y generaremos un prompt listo para usar en Gemini.
              </div>
              <div class="mb-3 text-start">
                  <label class="form-label fw-bold">1. Actividad o Tarea a Evaluar</label>
                  <select id="ia_actividad_sel" class="form-select shadow-sm mb-2" onchange="toggleIaActividadCustom(this)">
                      <option value="">Seleccione una actividad declarada...</option>
                      ${opcionesHtml}
                      <option value="CUSTOM">-- Escribir Actividad Personalizada --</option>
                  </select>
                  <input type="text" id="ia_actividad" class="form-control shadow-sm d-none" placeholder="Ej: Ensayo de 3 páginas sobre el tema">
              </div>
              <div class="mb-3 text-start">
                  <label class="form-label fw-bold">2. Criterios de Evaluación clave</label>
                  <input type="text" id="ia_criterios" class="form-control shadow-sm" placeholder="Ej: Introducción, Desarrollo, Ortografía, Bibliografía">
              </div>
              <div class="mb-3 text-start">
                  <label class="form-label fw-bold">3. Escala y Puntuación (Columnas CONESUP)</label>
                  <input type="text" id="ia_escala" class="form-control shadow-sm" value="Excelente, Bueno, En proceso, Insuficiente">
              </div>
          `,
          showCancelButton: true,
          confirmButtonColor: '#0d6efd',
          cancelButtonColor: '#64748b',
          confirmButtonText: '🚀 Generar Prompt para Gemini',
          cancelButtonText: 'Cancelar',
          customClass: {
              confirmButton: 'btn btn-primary px-4 rounded-3 me-2',
              cancelButton: 'btn btn-secondary px-4 rounded-3'
          },
          buttonsStyling: false,
          didOpen: () => {
              const select = document.getElementById('ia_actividad_sel');
              if (select && select.options.length <= 2) { 
                  select.value = "CUSTOM";
                  toggleIaActividadCustom(select);
              }
          },
          preConfirm: () => {
              const select = document.getElementById('ia_actividad_sel');
              let actividad = "";
              if (select.value === "CUSTOM") {
                  actividad = document.getElementById('ia_actividad').value.trim();
              } else {
                  actividad = select.value.trim();
              }
              
              const criterios = document.getElementById('ia_criterios').value.trim();
              const escala = document.getElementById('ia_escala').value.trim();
              
              if (!actividad || !criterios) {
                  Swal.showValidationMessage('Por favor completa la Actividad y los Criterios');
                  return false;
              }
              
              return { actividad, criterios, escala };
          }
      }).then((result) => {
          if (result.isConfirmed) {
              const { actividad, criterios, escala } = result.value;
              
              const prompt = `Diseña una rúbrica de evaluación para la siguiente actividad: "${actividad}".
Criterios a evaluar: "${criterios}".
Los niveles de desempeño (columnas) deben ser estrictamente: "${escala}".

Por favor, devuélveme el resultado formateado ÚNICAMENTE como código HTML limpio (etiqueta <table>).
IMPORTANTE: Muestra el código HTML plano estrictamente dentro de un bloque de código markdown de tipo 'html' (por ejemplo: \`\`\`html [código aquí] \`\`\`) para que pueda copiarlo con el botón de copiar código que proporciona Gemini en su interfaz web.

Sigue este formato exacto de estructura HTML de ejemplo dentro del bloque de código:
\`\`\`html
<table>
  <thead>
    <tr>
      <th>Criterio</th>
      <th>Excelente (5)</th>
      <th>Bueno (4)</th>
      <th>Regular (3)</th>
      <th>Deficiente (2)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Criterio 1</td>
      <td>Descripción de nivel excelente...</td>
      <td>Descripción de nivel bueno...</td>
      <td>Descripción de nivel regular...</td>
      <td>Descripción de nivel deficiente...</td>
    </tr>
  </tbody>
</table>
\`\`\`

No incluyas explicaciones adicionales, introducciones ni bloques de texto fuera del bloque de código, solo el bloque de código con la tabla HTML.`;

              Swal.fire({
                  title: '🤖 Copiar Prompt & Abrir Gemini',
                  html: `
                      <p class="small text-muted text-start mb-3">Sigue estos sencillos pasos:</p>
                      <ol class="small text-start mb-4 text-muted">
                          <li>Copia el prompt generado abajo presionando el botón azul.</li>
                          <li>Haz clic en "Ir a Gemini" para abrir el chat de IA en otra pestaña.</li>
                          <li>Pega el prompt, copia la tabla HTML que te responda Gemini y luego pégala en el botón de código de tu rúbrica (<i class="bi bi-code-slash"></i>).</li>
                      </ol>
                      <textarea id="prompt_textarea" class="form-control mb-3 bg-light text-dark fw-semibold small" style="font-family: monospace;" rows="8" readonly>${prompt}</textarea>
                      <button class="btn btn-outline-primary btn-sm mb-3 rounded-pill px-3 shadow-sm" onclick="copiarPromptIA()">
                          <i class="bi bi-clipboard2-check-fill me-1"></i> Copiar Prompt al Portapapeles
                      </button>
                  `,
                  showCancelButton: true,
                  confirmButtonColor: '#f98012',
                  cancelButtonColor: '#64748b',
                  confirmButtonText: '🤖 Ir a Gemini',
                  cancelButtonText: 'Cerrar',
                  customClass: {
                      confirmButton: 'btn btn-warning text-dark fw-bold px-4 rounded-3 me-2',
                      cancelButton: 'btn btn-secondary px-4 rounded-3'
                  },
                  buttonsStyling: false
              }).then((res) => {
                  if (res.isConfirmed) {
                      window.open('https://gemini.google.com', '_blank');
                  }
              });
          }
      });
  }

  function toggleIaActividadCustom(select) {
      const customInput = document.getElementById('ia_actividad');
      const escalaInput = document.getElementById('ia_escala');
      
      if (select.value === 'CUSTOM') {
          customInput.classList.remove('d-none');
          customInput.focus();
          if (escalaInput) {
              escalaInput.value = "Excelente (5), Bueno (4), Regular (3), Deficiente (2)";
          }
      } else {
          customInput.classList.add('d-none');
          
          const rubroSeleccionado = select.value;
          let porcentaje = 0;
          let cantidad = 1;
          
          document.querySelectorAll('#tablaEvaluacion tbody tr').forEach(tr => {
              const nom = tr.querySelector('input[name="rubros[]"]')?.value.trim();
              if (nom === rubroSeleccionado) {
                  porcentaje = parseFloat(tr.querySelector('input[name="porcentajes[]"]')?.value) || 0;
                  cantidad = parseInt(tr.querySelector('input[name="cantidades_eval[]"]')?.value) || 1;
              }
          });
          
          const valorActividad = cantidad > 0 ? (porcentaje / cantidad) : 0;
          const v = Number.isInteger(valorActividad) ? valorActividad : parseFloat(valorActividad.toFixed(2));
          
          if (escalaInput && v > 0) {
              const exc = v;
              const bue = Number.isInteger(v * 0.8) ? (v * 0.8) : parseFloat((v * 0.8).toFixed(2));
              const reg = Number.isInteger(v * 0.5) ? (v * 0.5) : parseFloat((v * 0.5).toFixed(2));
              const def = Number.isInteger(v * 0.2) ? (v * 0.2) : parseFloat((v * 0.2).toFixed(2));
              escalaInput.value = `Excelente (${exc}), Bueno (${bue}), Regular (${reg}), Deficiente (${def})`;
          }
      }
  }

  function copiarPromptIA() {
      const copyText = document.getElementById("prompt_textarea");
      if (!copyText) return;
      copyText.select();
      copyText.setSelectionRange(0, 99999);
      navigator.clipboard.writeText(copyText.value);
      
      Swal.showValidationMessage('¡Prompt Copiado con éxito!');
      setTimeout(() => {
          const msg = document.querySelector('.swal2-validation-message');
          if (msg) msg.style.display = 'none';
      }, 2000);
  }

  // --- VALIDACIÓN Y GUARDADO DEL PLAN ---
  function validarYGuardarPlan() {
      if (typeof tinymce !== 'undefined') {
          tinymce.triggerSave();
      }

      let totalP = 0;
      document.querySelectorAll('input[name="porcentajes[]"]').forEach(inp => {
          totalP += parseFloat(inp.value) || 0;
      });

      if (Math.round(totalP) !== 100) {
          Swal.fire({
              title: 'Evaluación Incompleta',
              text: `El porcentaje total de los rubros suma ${Math.round(totalP)}% (debe sumar exactamente 100%). ¿Desea continuar guardando los cambios de todas formas?`,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Sí, guardar cambios',
              cancelButtonText: 'Revisar evaluación',
              confirmButtonColor: '#0d6efd',
              cancelButtonColor: '#64748b'
          }).then((res) => {
              if (res.isConfirmed) {
                  document.getElementById('formPlanEstudios').submit();
              } else {
                  const tabEval = document.querySelector('button[data-bs-target="#evaluacion_tab"]');
                  if (tabEval) tabEval.click();
              }
          });
          return;
      }

      document.getElementById('formPlanEstudios').submit();
  }

  // Mantener costo propio del curso (no sobrescribir con el del programa)
  function actualizarPrecioPrograma(select) {
      if (!select) return;
      const input = document.getElementById('precio_input');
      if (!input || input.value !== '') return;
      const selected = select.options[select.selectedIndex];
      const costo = selected ? (selected.getAttribute('data-costo') || 0) : 0;
      input.value = costo;
  }

  document.addEventListener('DOMContentLoaded', function() {
    inicializarTinyMCE();
    calcularDistribucionHoras();
    sincronizarSilabo();
    inicializarResumenesMoodle();
    inicializarRubricasExistentes();

    // Activar pestaña según sesión
    const activeTab = document.getElementById('active_tab_input')?.value || 'ficha_tecnica';
    const tabBtn = document.querySelector(`button[data-bs-target="#${activeTab}"]`);
    if (tabBtn) tabBtn.click();

    // Re-renderizar rúbricas al mostrar pestaña de rúbricas
    const tabRubricas = document.querySelector('button[data-bs-target="#rubricas_tab"]');
    if (tabRubricas) {
        tabRubricas.addEventListener('shown.bs.tab', function() {
            inicializarRubricasExistentes();
        });
    }

    const tablaEvaluacion = document.getElementById('tablaEvaluacion');
    if (tablaEvaluacion) {
        tablaEvaluacion.addEventListener('input', sincronizarSilabo);
        tablaEvaluacion.addEventListener('change', sincronizarSilabo);
    }

    // Sincronización reactiva del Nombre, Código, Programa y Cuatrimestre del Curso en tiempo real
    const inputMateria = document.querySelector('input[name="materia"]');
    const inputCodigo = document.querySelector('input[name="codigo"]');
    const selectPrograma = document.getElementById('id_programa');
    const selectCuatrimestre = document.getElementById('cuatrimestre');

    function actualizarNombreCursoReactivo() {
        const valMateria = inputMateria ? (inputMateria.value.trim() || 'Sin Nombre') : '';
        const valCodigo = inputCodigo ? inputCodigo.value.trim() : '';

        const elH = document.getElementById('headerNombreCurso');
        const elB = document.getElementById('breadcrumbCursoNombre');
        const elC = document.getElementById('cardNombreCurso');

        if (elH) elH.textContent = valMateria;
        if (elB) elB.textContent = valMateria;
        if (elC) elC.textContent = valMateria;

        const elCodH = document.getElementById('headerCodigoCurso');
        const elCodC = document.getElementById('cardCodigoCurso');
        if (elCodH) {
            elCodH.textContent = valCodigo;
            elCodH.classList.toggle('d-none', !valCodigo);
        }
        if (elCodC) {
            elCodC.textContent = valCodigo;
            elCodC.classList.toggle('d-none', !valCodigo);
        }

        document.title = 'Editar Curso: ' + valMateria + (valCodigo ? ' (' + valCodigo + ')' : '');
    }

    function actualizarProgramaReactivo() {
        const elProg = document.getElementById('subHeaderNombrePrograma');
        if (elProg && selectPrograma && selectPrograma.selectedIndex >= 0) {
            const opt = selectPrograma.options[selectPrograma.selectedIndex];
            if (opt && opt.value) {
                elProg.textContent = opt.textContent.trim().toUpperCase();
            }
        }
    }

    function actualizarCuatrimestreReactivo() {
        const elCuat = document.getElementById('cardCuatrimestreCurso');
        if (elCuat && selectCuatrimestre) {
            elCuat.textContent = selectCuatrimestre.value || '';
        }
    }

    if (inputMateria) {
        inputMateria.addEventListener('input', actualizarNombreCursoReactivo);
        inputMateria.addEventListener('change', actualizarNombreCursoReactivo);
    }
    if (inputCodigo) {
        inputCodigo.addEventListener('input', actualizarNombreCursoReactivo);
        inputCodigo.addEventListener('change', actualizarNombreCursoReactivo);
    }
    if (selectPrograma) {
        selectPrograma.addEventListener('change', actualizarProgramaReactivo);
    }
    if (selectCuatrimestre) {
        selectCuatrimestre.addEventListener('change', actualizarCuatrimestreReactivo);
    }
  });
</script>
@endsection
