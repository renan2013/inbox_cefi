@extends('layouts.app')

@section('title', 'Inbox BPM - Añadir Curso al Plan de Estudios')

@section('styles')
<style>
    :root {
        --primary-color: #5fb230;
        --primary-hover: #4e9a26;
        --text-dark: #2d3436;
        --bg-light: #f1f5f9;
    }
    
    .page-header-course {
        background: var(--card-dark, #ffffff);
        padding: 1.5rem 2rem;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
        border: 1px solid var(--border-dark, #e2e8f0);
    }
    .section-title {
        font-weight: 800;
        color: var(--text-light, #2d3436);
        letter-spacing: -0.025em;
    }
    .card-silabo {
        border-radius: 1rem;
        border: 1px solid var(--border-dark, #e2e8f0);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        background: var(--card-dark, #ffffff);
    }
    .nav-tabs .nav-link {
        color: var(--text-muted, #64748b);
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
        background: rgba(95, 178, 48, 0.06);
    }
    .nav-tabs .nav-link.active {
        color: var(--primary-color) !important;
        border-bottom: 3px solid var(--primary-color) !important;
        background: transparent !important;
        font-weight: 700;
    }
    .field-card {
        background-color: rgba(248, 250, 252, 0.5);
        border: 1px solid var(--border-dark, #e2e8f0);
        border-radius: 1rem;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    [data-theme="dark"] .field-card {
        background-color: rgba(15, 23, 42, 0.5);
    }
    .field-card-title {
        font-weight: 700;
        font-size: 0.95rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-save-float {
        position: fixed;
        bottom: 1.25rem;
        right: 1.5rem;
        z-index: 1050;
        box-shadow: 0 6px 16px rgba(95, 178, 48, 0.45);
        padding: 0.65rem 1.3rem;
        font-size: 0.85rem;
        font-weight: 700;
        border-radius: 1.5rem;
        transition: all 0.3s ease;
        background-color: var(--primary-color);
        border: 2px solid #ffffff;
        color: white;
    }
    .btn-save-float:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 8px 20px rgba(95, 178, 48, 0.55);
        background-color: var(--primary-hover);
        color: white;
    }
    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--text-light, #334155);
        margin-bottom: 0.35rem;
    }
    .badge-section-num {
        background-color: rgba(14, 165, 233, 0.15);
        color: #0284c7;
        font-weight: 800;
        padding: 0.25rem 0.6rem;
        border-radius: 0.5rem;
        font-size: 0.8rem;
    }
    
    /* Cronograma y Moodle */
    #tablaCronograma { border-collapse: collapse !important; border: 1px solid var(--border-dark, #e2e8f0) !important; }
    #tablaCronograma th { background-color: rgba(248, 250, 252, 0.8); color: var(--text-muted, #475569); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px; }
    [data-theme="dark"] #tablaCronograma th { background-color: rgba(30, 41, 59, 0.8); color: #94a3b8; }
    #tablaCronograma td { padding: 8px !important; vertical-align: top !important; }
    .crono-semana-num { padding-top: 14px !important; vertical-align: top !important; }
    .td-modalidad { transition: background-color 0.3s ease, color 0.3s ease; vertical-align: middle !important; text-align: center; padding: 6px 3px !important; width: 125px !important; min-width: 120px !important; position: relative; }
    .td-sincronico { background-color: #008dff !important; color: white !important; }
    .td-asincronico { background-color: rgba(248, 250, 252, 0.5) !important; color: #64748b !important; }
    [data-theme="dark"] .td-asincronico { background-color: rgba(15, 23, 42, 0.5) !important; color: #94a3b8 !important; }
    .modalidad-select { 
        font-size: 0.68rem; 
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
        color: var(--text-muted, #64748b) !important; 
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
    }
    .modalidad-select option { background-color: white; color: #1e293b; font-size: 0.8rem; }
    .zoom-logo-mini { width: 50px; max-width: 75%; height: auto; display: block; margin: 0 auto; border-radius: 3px; }
    .zoom-logo-container { padding: 3px 0 4px 0; width: 100%; margin: 0; }

    .btn-moodle { background-color: #f98012; color: white; border: none; font-size: 0.75rem; font-weight: 700; border-radius: 6px; padding: 6px 10px; }
    .btn-moodle:hover { background-color: #e06f0b; color: white; }
    
    /* Rúbricas */
    .rubrica-item { transition: all 0.3s ease; border: 1px solid var(--border-dark, #e2e8f0) !important; background: var(--card-dark, #ffffff); }
    .rubrica-item:hover { box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important; transform: translateY(-2px); }
    .tabla-rubrica-visual th { background-color: rgba(241, 245, 249, 0.7); color: var(--text-muted, #475569); font-size: 0.7rem; text-align: center; font-weight: 800; border: 1px solid var(--border-dark, #e2e8f0); }
    [data-theme="dark"] .tabla-rubrica-visual th { background-color: rgba(30, 41, 59, 0.7); color: #94a3b8; }
    .tabla-rubrica-visual td { background-color: transparent; border: 1px solid var(--border-dark, #e2e8f0); padding: 4px; }
    .btn-xs { padding: 2px 6px; font-size: 0.65rem; border-radius: 4px; }
    .tox-tinymce { border-radius: 8px !important; border: 1px solid var(--border-dark, #cbd5e1) !important; }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    
    <!-- Encabezado con Breadcrumb -->
    <div class="page-header-course d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted"><i class="bi bi-house-door"></i> Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cursos.index') }}" class="text-decoration-none text-muted">Oferta Académica</a></li>
                    <li class="breadcrumb-item active fw-semibold" aria-current="page">Añadir Curso</li>
                </ol>
            </nav>
            <h2 class="section-title mb-0 d-flex align-items-center">
                <i class="bi bi-journal-plus text-success me-2"></i> Añadir Curso al Plan de Estudios
            </h2>
            <p class="text-muted mb-0 small mt-1">Configure la ficha técnica oficial del curso, objetivos, contenidos, evaluación, cronograma y rúbricas</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('cursos.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i> Volver a Cursos
            </a>
        </div>
    </div>

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

    <form action="{{ route('cursos.store') }}" method="POST" enctype="multipart/form-data" id="formPlanEstudios">
        @csrf
        <input type="hidden" name="active_tab" id="active_tab_input" value="ficha_tecnica">

        <!-- Botón Flotante de Guardado Persistente -->
        <button type="button" onclick="validarYGuardarPlan()" class="btn btn-save-float">
            <i class="bi bi-plus-circle me-2"></i> REGISTRAR CURSO EN EL PLAN
        </button>

        <div class="card card-silabo border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom p-0">
                <ul class="nav nav-tabs border-0 flex-nowrap overflow-auto" id="cursoTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ficha_tecnica" type="button">
                            <i class="bi bi-table"></i> Ficha Técnica del Curso
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
                            <i class="bi bi-check2-square"></i> VII. Evaluación
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#cronograma_tab" type="button">
                            <i class="bi bi-calendar3"></i> VIII. Cronograma
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rubricas_tab" type="button">
                            <i class="bi bi-card-checklist"></i> IX. Rúbricas de Evaluación
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bibliografia_descriptor_tab" type="button">
                            <i class="bi bi-book-half"></i> X-XI. Recursos & Bibliografía
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
                                            <option value="{{ $prog->id_programa }}" data-costo="{{ $prog->costo_materia ?? 0 }}" {{ old('id_programa') == $prog->id_programa ? 'selected' : '' }}>
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
                                        @foreach (['I Cuatrimestre', 'II Cuatrimestre', 'III Cuatrimestre', 'IV Cuatrimestre', 'V Cuatrimestre', 'VI Cuatrimestre', 'VII Cuatrimestre', 'VIII Cuatrimestre'] as $c)
                                            <option value="{{ $c }}" {{ old('cuatrimestre') === $c ? 'selected' : '' }}>{{ $c }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="precio_display" class="form-label">Precio Sugerido Materia</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">₡</span>
                                        <input type="text" id="precio_display" class="form-control" placeholder="0.00" readonly>
                                        <input type="hidden" name="precio" id="precio_input" value="{{ old('precio', 0) }}">
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
                                    <input type="text" name="codigo" id="codigo" class="form-control" placeholder="Ej: MLSC - 01" value="{{ old('codigo') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="materia" class="form-label">Nombre de la Materia / Curso <span class="text-danger">*</span></label>
                                    <input type="text" name="materia" id="materia" class="form-control fw-bold" placeholder="Ej: Cultura, etnicidad y diversidad." value="{{ old('materia') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="creditos" class="form-label">Créditos</label>
                                    <input type="number" name="creditos" id="creditos" class="form-control" placeholder="Ej: 4" min="0" value="{{ old('creditos', '4') }}">
                                </div>

                                <div class="col-md-3">
                                    <label for="duracion" class="form-label">Duración <span class="text-muted">(semanas)</span></label>
                                    <div class="input-group">
                                        <input type="number" name="duracion" id="duracion" class="form-control text-center fw-bold" placeholder="15" value="{{ old('duracion', '15') }}" min="1" required>
                                        <span class="input-group-text bg-light text-muted">semanas</span>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label for="modalidad" class="form-label">Modalidad</label>
                                    <input type="text" name="modalidad" id="modalidad" class="form-control" placeholder="Ej: Virtual (aprendizaje electrónico)" value="{{ old('modalidad', 'Virtual (aprendizaje electrónico)') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="naturaleza" class="form-label">Naturaleza</label>
                                    <input type="text" name="naturaleza" id="naturaleza" class="form-control" placeholder="Ej: Teórico-práctica" value="{{ old('naturaleza', 'Teórico-práctica') }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="requisitos" class="form-label">Requisitos Previos</label>
                                    <input type="text" name="requisitos" id="requisitos" class="form-control" placeholder="Ej: No tiene" value="{{ old('requisitos', 'No tiene') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="correquisitos" class="form-label">Correquisitos</label>
                                    <input type="text" name="correquisitos" id="correquisitos" class="form-control" placeholder="Ej: No tiene" value="{{ old('correquisitos', 'No tiene') }}">
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
                                                    <input type="number" name="horas_teoricas" id="horas_teoricas" class="form-control text-center fw-bold" min="0" value="{{ old('horas_teoricas', 3) }}" oninput="calcularDistribucionHoras()">
                                                    <span class="input-group-text bg-white">hrs</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <label for="horas_practicas" class="form-label small mb-1 fw-bold text-secondary">Horas Prácticas</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="horas_practicas" id="horas_practicas" class="form-control text-center fw-bold" min="0" value="{{ old('horas_practicas', 1) }}" oninput="calcularDistribucionHoras()">
                                                    <span class="input-group-text bg-white">hrs</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <label for="horas_independientes" class="form-label small mb-1 fw-bold text-secondary">Estudio Independiente</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="horas_independientes" id="horas_independientes" class="form-control text-center fw-bold" min="0" value="{{ old('horas_independientes', 8) }}" oninput="calcularDistribucionHoras()">
                                                    <span class="input-group-text bg-white">hrs</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <label for="horas_totales" class="form-label small mb-1 fw-bold text-primary">Total Horas / Sem.</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="horas_totales" id="horas_totales" class="form-control text-center fw-bold text-primary bg-white border-primary" value="{{ old('horas_totales', 12) }}" readonly>
                                                    <span class="input-group-text bg-primary text-white">hrs</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <textarea name="distribucion_horas" id="distribucion_horas" class="form-control form-control-sm text-muted bg-white" rows="2" readonly style="font-size: 0.85rem;">{{ old('distribucion_horas', 'Este curso comprende un total de 12 horas distribuidas en 3 horas teóricas, 1 hora práctica y 8 horas de estudio independiente.') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- PESTAÑA 2: I-III. DESCRIPCIÓN Y OBJETIVOS -->
                    <div class="tab-pane fade" id="desc_objetivos_tab">
                        <div class="mb-4">
                            <label for="descripcion_curso" class="form-label fw-bold"><span class="badge-section-num me-1">I</span> Descripción del Curso</label>
                            <textarea name="descripcion_curso" id="descripcion_curso" class="form-control editor-rico" rows="8">{{ old('descripcion_curso') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="objetivo_general" class="form-label fw-bold"><span class="badge-section-num me-1">II</span> Objetivos Generales</label>
                            <textarea name="objetivo_general" id="objetivo_general" class="form-control editor-rico" rows="8">{{ old('objetivo_general') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="objetivos_especificos" class="form-label fw-bold"><span class="badge-section-num me-1">III</span> Objetivos Específicos</label>
                            <textarea name="objetivos_especificos" id="objetivos_especificos" class="form-control editor-rico" rows="8">{{ old('objetivos_especificos') }}</textarea>
                        </div>
                    </div>

                    <!-- PESTAÑA 3: IV-VI. CONTENIDOS Y METODOLOGÍA -->
                    <div class="tab-pane fade" id="contenidos_metodologia_tab">
                        <div class="mb-4">
                            <label for="contenidos_tematicos" class="form-label fw-bold"><span class="badge-section-num me-1">IV</span> Contenidos Temáticos</label>
                            <textarea name="contenidos_tematicos" id="contenidos_tematicos" class="form-control editor-rico" rows="8">{{ old('contenidos_tematicos') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="metodologia_ensenanza" class="form-label fw-bold"><span class="badge-section-num me-1">V</span> Metodología de Enseñanza</label>
                            <textarea name="metodologia_ensenanza" id="metodologia_ensenanza" class="form-control editor-rico" rows="8">{{ old('metodologia_ensenanza') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="estrategias_aprendizaje" class="form-label fw-bold"><span class="badge-section-num me-1">VI</span> Estrategias de Aprendizaje</label>
                            <textarea name="estrategias_aprendizaje" id="estrategias_aprendizaje" class="form-control editor-rico" rows="8">{{ old('estrategias_aprendizaje') }}</textarea>
                        </div>
                    </div>

                    <!-- PESTAÑA 4: VII. EVALUACIÓN -->
                    <div class="tab-pane fade" id="evaluacion_tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-check2-square me-2"></i>Tabla Oficial de Evaluación del Curso</h5>
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
                                        <td><input type="text" name="rubros[]" class="form-control fw-bold" value="{{ $ev['rubro'] }}" oninput="actualizarOpcionesNombresEnRubricas()"></td>
                                        <td><input type="number" name="porcentajes[]" class="form-control valor-p text-center fw-bold" value="{{ (int)$ev['porcentaje'] }}"></td>
                                        <td><input type="number" name="cantidades_eval[]" class="form-control text-center" value="{{ $ev['cantidad'] }}"></td>
                                        <td><input type="text" class="form-control text-center eval-unit-val" readonly value="0"></td>
                                        <td>
                                            <select name="tipos_moodle_eval[]" class="form-select form-select-sm">
                                                <option value="" {{ empty($ev['tipo_moodle']) ? 'selected' : '' }}>-- Sin actividad Moodle --</option>
                                                <option value="assign" {{ ($ev['tipo_moodle'] ?? '') === 'assign' ? 'selected' : '' }}>📄 Tarea</option>
                                                <option value="quiz" {{ ($ev['tipo_moodle'] ?? '') === 'quiz' ? 'selected' : '' }}>❓ Quiz / Examen</option>
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
                            <button type="button" class="btn btn-success rounded-pill px-4 mt-2" onclick="agregarFila()">+ Añadir Rubro</button>
                        </div>
                    </div>

                    <!-- PESTAÑA 5: VIII. CRONOGRAMA -->
                    <div class="tab-pane fade" id="cronograma_tab">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-calendar3 me-2"></i>Cronograma Semanal de Actividades (15 Semanas)</h5>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="agregarFilaCronograma()">
                                <i class="bi bi-plus-circle me-1"></i> Añadir Semana
                            </button>
                        </div>

                        <!-- Barra de Progreso de Actividades -->
                        <div class="card mb-4 border-0 shadow-sm" style="background-color: rgba(248, 250, 252, 0.8);">
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
                                        <th style="width: 10%;">Fecha</th>
                                        <th style="width: 33%;">Actividad / Tema</th>
                                        <th style="width: 35%;">Tareas / Entregables</th>
                                        <th style="width: 8%;">Moodle</th>
                                        <th style="width: 6%;">Modalidad</th>
                                        <th style="width: 4%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cronograma_data as $idx => $cro)
                                    @php
                                        $idActExistente = 'act_ex_' . $idx;
                                        $idTarExistente = 'tar_ex_' . $idx;
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
                                                <span class="text-muted small italic"><i class="bi bi-info-circle me-1"></i> Sin actividades configuradas. Utilice el botón "Config."</span>
                                            </div>
                                            <textarea id="{{ $idTarExistente }}" name="c_tareas[]" class="d-none">{{ $cro['tareas'] ?? '' }}</textarea>
                                        </td>
                                        <td class="text-center">
                                            <input type="hidden" name="c_moodle[]" class="moodle-data-input" value="{{ $cro['actividades_moodle'] ?? '' }}">
                                            <div class="moodle-summary mb-1"></div>
                                            <button type="button" class="btn btn-moodle w-100" onclick="abrirConfigMoodle(this)"><i class="bi bi-gear-fill me-1"></i> Config.</button>
                                        </td>
                                        <td class="text-center td-modalidad {{ ($cro['modalidad_trabajo'] ?? '') === 'Sincrónico' ? 'td-sincronico' : 'td-asincronico' }}">
                                            <select name="c_modalidad[]" class="form-select modalidad-select {{ ($cro['modalidad_trabajo'] ?? '') === 'Sincrónico' ? 'select-sincronico' : 'select-asincronico' }}" onchange="actualizarEstiloModalidad(this)">
                                                <option value="Sincrónico" {{ ($cro['modalidad_trabajo'] ?? '') === 'Sincrónico' ? 'selected' : '' }}>Sincrónico</option>
                                                <option value="Asincrónico" {{ ($cro['modalidad_trabajo'] ?? 'Asincrónico') === 'Asincrónico' ? 'selected' : '' }}>Asincrónico</option>
                                            </select>
                                            <div class="mt-1 zoom-logo-container {{ ($cro['modalidad_trabajo'] ?? '') === 'Sincrónico' ? '' : 'd-none' }}">
                                                <img src="{{ asset('imgs/zoom_logo.png') }}" class="zoom-logo-mini" alt="Zoom">
                                            </div>
                                        </td>
                                        <td class="text-center"><button type="button" class="btn btn-link text-danger p-0" onclick="eliminarFilaCronograma(this)"><i class="bi bi-trash fs-5"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- PESTAÑA 6: IX. RÚBRICAS DE EVALUACIÓN -->
                    <div class="tab-pane fade" id="rubricas_tab">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-card-checklist me-2"></i>Rúbricas Oficiales de Evaluación</h5>
                                <small class="text-muted">Diseñe los criterios, niveles y escalas de evaluación para cada rubro del curso.</small>
                            </div>
                        </div>

                        <div id="contenedorRubricas">
                            <!-- Inyectadas dinámicamente -->
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="nuevaRubrica()"><i class="bi bi-plus-circle me-2"></i> Crear Rúbrica</button>
                            <button type="button" class="btn btn-info text-white rounded-pill px-4 ms-2 shadow-sm fw-bold" onclick="abrirGeneradorRubricaIA()"><i class="bi bi-cpu-fill me-2"></i> Crear con IA (Gemini)</button>
                        </div>
                    </div>

                    <!-- PESTAÑA 7: X-XI. RECURSOS & BIBLIOGRAFÍA -->
                    <div class="tab-pane fade" id="bibliografia_descriptor_tab">
                        <div class="mb-4">
                            <label for="recursos_didacticos" class="form-label fw-bold"><span class="badge-section-num me-1">X</span> Recursos Didácticos y Plataformas</label>
                            <textarea name="recursos_didacticos" id="recursos_didacticos" class="form-control editor-rico" rows="8">{{ old('recursos_didacticos') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="bibliografia" class="form-label fw-bold"><span class="badge-section-num me-1">XI</span> Bibliografía Oficial</label>
                            <textarea name="bibliografia" id="bibliografia" class="form-control editor-rico" rows="8">{{ old('bibliografia') }}</textarea>
                        </div>
                    </div>

                </div>

                <!-- Botones al pie del formulario -->
                <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
                    <a href="{{ route('cursos.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </a>
                    <button type="button" onclick="validarYGuardarPlan()" class="btn btn-primary px-5 py-3 shadow rounded-pill fw-bold" style="background-color: var(--primary-color); border: none;">
                        <i class="bi bi-plus-circle me-2"></i> Registrar Curso en el Plan
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
                <p class="text-muted small">Pega aquí el código HTML de una tabla (<code>&lt;table&gt;...&lt;/table&gt;</code>). El sistema parseará la tabla para generar la rúbrica.</p>
                <textarea id="htmlRubricaInput" class="form-control" rows="10" placeholder="Pega tu código HTML aquí..."></textarea>
                <div class="alert alert-warning mt-3 small" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Solo se procesará la primera tabla encontrada. Asegúrate de que la estructura contenga encabezados y filas.
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary px-4" onclick="procesarHTMLRubrica()">Procesar y Aplicar</button>
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
    }

    function inicializarTinyMCE() {
        if (typeof tinymce === 'undefined') return;

        tinymce.init({
            license_key: 'gpl',
            selector: '.editor-rico',
            plugins: 'lists link autolink',
            toolbar: 'bold italic underline | bullist numlist | link | removeformat',
            menubar: false,
            statusbar: false,
            height: 250,
            branding: false,
            promotion: false,
            content_style: ".mce-item-anchor { display: none !important; width: 0 !important; height: 0 !important; visibility: hidden !important; }",
            setup: configurarLimpiezaTinyMCE
        });

        tinymce.init({
            license_key: 'gpl',
            selector: '.editor-rico-compacto',
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
    }

    function calcularDistribucionHoras() {
        const teo = parseInt(document.getElementById('horas_teoricas').value) || 0;
        const prac = parseInt(document.getElementById('horas_practicas').value) || 0;
        const ind = parseInt(document.getElementById('horas_independientes').value) || 0;
        const tot = teo + prac + ind;

        document.getElementById('horas_totales').value = tot;

        const textoPrac = (prac === 1) ? "1 hora práctica" : `${prac} horas prácticas`;
        const textoTeo = (teo === 1) ? "1 hora teórica" : `${teo} horas teóricas`;
        const textoIndep = (ind === 1) ? "1 hora de estudio independiente" : `${ind} horas de estudio independiente`;

        document.getElementById('distribucion_horas').value = `Este curso comprende un total de ${tot} horas distribuidas en ${textoTeo}, ${textoPrac} y ${textoIndep}.`;
    }

    function actualizarPrecioPrograma(select) {
        const selected = select.options[select.selectedIndex];
        const costo = selected.getAttribute('data-costo') || 0;
        const formatted = parseFloat(costo).toLocaleString('es-CR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('precio_display').value = formatted;
        document.getElementById('precio_input').value = costo;
    }

    function calcularTotal() {
        let totalP = 0;
        let totalC = 0;

        document.querySelectorAll('#tablaEvaluacion tbody tr').forEach((tr, index) => {
            tr.querySelector('.eval-num').textContent = index + 1;
            const p = parseFloat(tr.querySelector('.valor-p').value) || 0;
            const c = parseInt(tr.querySelector('input[name="cantidades_eval[]"]').value) || 0;
            totalP += p;
            totalC += c;

            const unit = c > 0 ? (p / c) : 0;
            tr.querySelector('.eval-unit-val').value = (Number.isInteger(unit) ? unit : unit.toFixed(2)) + '%';
        });

        const totalPorcEl = document.getElementById('totalPorcentaje');
        totalPorcEl.textContent = totalP + '%';
        totalPorcEl.className = 'text-center ' + (Math.round(totalP) === 100 ? 'text-success fw-bold' : 'text-danger fw-bold');
        document.getElementById('totalCantidad').textContent = totalC;
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
                    <option value="quiz">❓ Quiz / Examen</option>
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
                <div class="moodle-summary mb-1"></div>
                <button type="button" class="btn btn-moodle w-100" onclick="abrirConfigMoodle(this)"><i class="bi bi-gear-fill me-1"></i> Config.</button>
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

        if (typeof tinymce !== 'undefined') {
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
        }

        actualizarContadorReversoCronograma();
    }

    function eliminarFilaCronograma(btn) {
        const tr = btn.closest('tr');
        if (!tr) return;
        const semNum = tr.querySelector('.crono-semana-num')?.textContent?.trim() || '';

        Swal.fire({
            title: '¿Eliminar semana del cronograma?',
            text: `Se borrará la Semana ${semNum} y sus actividades configuradas.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
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
            }
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
                rubrosCantidades[nom] = cant;
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
                        usoActual[rubroKey] = (usoActual[rubroKey] || 0) + 1;
                    });
                } catch(e) {}
            }
        });

        return { rubrosSet, rubrosCantidades, rubroMoodleTypes, usoActual };
    }

    function actualizarContadorReversoCronograma() {
        const subtituloEl = document.getElementById('progresoActividadesSubtitulo');
        const badgeEl = document.getElementById('badgeProgresoActividades');
        const progressBar = document.getElementById('barraProgresoActividades');
        if (!badgeEl || !progressBar) return;

        let totalPlanificadas = 0;
        document.querySelectorAll('#tablaEvaluacion tbody tr').forEach(tr => {
            const nom = tr.querySelector('input[name="rubros[]"]')?.value.trim();
            const cant = parseInt(tr.querySelector('input[name="cantidades_eval[]"]')?.value) || 0;
            if (nom) totalPlanificadas += cant;
        });

        let totalAsignadas = 0;
        document.querySelectorAll('#tablaCronograma tbody tr').forEach(tr => {
            const input = tr.querySelector('.moodle-data-input');
            if (input && input.value) {
                try {
                    const acts = JSON.parse(input.value);
                    if (Array.isArray(acts)) totalAsignadas += acts.length;
                } catch(e) {}
            }
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

        if (totalAsignadas >= totalPlanificadas) {
            badgeEl.className = "badge bg-success px-3 py-2 rounded-pill fw-bold shadow-sm";
            badgeEl.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> ${totalAsignadas} de ${totalPlanificadas} Actividades (100% Completado)`;
            if (subtituloEl) subtituloEl.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check2-all me-1"></i> Todas las actividades han sido programadas en el cronograma.</span>`;
            progressBar.className = "progress-bar bg-success";
        } else {
            badgeEl.className = "badge bg-primary px-3 py-2 rounded-pill fw-bold shadow-sm";
            badgeEl.innerHTML = `<i class="bi bi-hourglass-split me-1"></i> ${totalAsignadas} de ${totalPlanificadas} (${pct}%) — Faltan ${faltantes}`;
            if (subtituloEl) subtituloEl.textContent = `Quedan ${faltantes} actividades pendientes por asignar en las semanas del curso.`;
            progressBar.className = "progress-bar bg-primary progress-bar-striped progress-bar-animated";
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
                btn.className = `btn btn-sm ${btnClass} rounded-pill px-3 shadow-sm`;
                btn.innerHTML = `<i class="bi ${icon} me-1"></i> + ${r} <span class="badge ${faltan <= 0 ? 'bg-secondary' : 'bg-dark'} ms-1">${faltan > 0 ? faltan : 0}</span>`;
                btn.onclick = () => {
                    agregarActividadMoodle(mType, r);
                };
                footer.appendChild(btn);
            });
        }

        const btnGuardar = document.createElement('button');
        btnGuardar.type = 'button';
        btnGuardar.className = 'btn btn-primary px-4 ms-auto rounded-pill';
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
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="form-label small mb-1 fw-bold">Rubro / Tipo:</label>
                        <select class="form-select form-select-sm moodle-tipo" onchange="actualizarBotonesFooterMoodle()">${optionsRubros}</select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1 fw-bold">Nombre de la Actividad:</label>
                        <input type="text" class="form-control form-control-sm moodle-nombre" value="${act.nombre || ''}" placeholder="Ej: Ensayo sobre el tema">
                    </div>
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
        const cont = document.getElementById('contenedorActividadesMoodle');
        if (cont.querySelector('p.text-muted')) cont.innerHTML = "";
        crearTarjetaActividadMoodle({
            tipo: rubroDefault || tipo,
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
            const n = it.querySelector('.moodle-nombre')?.value.trim();
            const i = it.querySelector('.moodle-intro')?.value.trim();
            if (t || n) acts.push({ tipo: t, nombre: n, intro: i });
        });

        const input = filaActualMoodle.querySelector('.moodle-data-input');
        if (input) input.value = JSON.stringify(acts);

        actualizarResumenFila(filaActualMoodle, acts);

        const modal = bootstrap.Modal.getInstance(document.getElementById('modalMoodle'));
        if (modal) modal.hide();

        actualizarContadorReversoCronograma();
    }

    function actualizarResumenFila(fila, acts) {
        const summary = fila.querySelector('.moodle-summary');
        const tareaDisplay = fila.querySelector('.tarea-display-only');
        if (summary) summary.innerHTML = "";

        if (acts.length > 0) {
            acts.forEach(a => {
                const tag = document.createElement('span');
                tag.className = 'badge bg-primary me-1 mb-1';
                tag.textContent = a.tipo || 'Actividad';
                if (summary) summary.appendChild(tag);
            });

            if (tareaDisplay) {
                let html = "";
                acts.forEach(a => {
                    const introHtml = a.intro ? a.intro.replace(/\r\n|\r|\n/g, '<br>') : '';
                    html += `<div class="mb-2 p-2 border-start border-4 border-primary bg-white rounded-2">
                        <p class="mb-0 fw-bold small text-primary">${a.tipo || 'Actividad'}: ${a.nombre || 'Sin título'}</p>
                        ${introHtml ? `<p class="mb-0 text-muted small mt-1" style="line-height: 1.4;">${introHtml}</p>` : ''}
                    </div>`;
                });
                tareaDisplay.innerHTML = html;
            }
        } else {
            if (tareaDisplay) {
                tareaDisplay.innerHTML = '<span class="text-muted small italic"><i class="bi bi-info-circle me-1"></i> Sin actividades configuradas. Utilice el botón "Config."</span>';
            }
        }
    }

    // --- RÚBRICAS JS ---
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
            const valorPrevio = select.dataset.seleccionado || (select.value !== 'CUSTOM' ? select.value : customInput.value);

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
                customInput.classList.add('d-none');
            } else if (valorPrevio && valorPrevio !== 'CUSTOM') {
                select.value = 'CUSTOM';
                customInput.value = valorPrevio;
                customInput.classList.remove('d-none');
            } else {
                select.value = '';
            }
        });
    }

    function actualizarSelectsRubricas() {
        const rubricasDeclaradas = [];
        document.querySelectorAll('.rubrica-item').forEach(item => {
            const sel = item.querySelector('select[name="rubrica_nombre_sel[]"]');
            const customInp = item.querySelector('input[name="rubrica_nombre_custom[]"]');
            let nombreFinal = sel ? (sel.value === 'CUSTOM' ? customInp.value.trim() : sel.value.trim()) : '';
            if (nombreFinal) rubricasDeclaradas.push(nombreFinal);
        });

        document.querySelectorAll('.select-anexo-rubrica').forEach(select => {
            const prev = select.dataset.seleccionado || select.value;
            select.innerHTML = '<option value="">Ninguno</option>';
            rubricasDeclaradas.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r;
                opt.textContent = r;
                if (r === prev) opt.selected = true;
                select.appendChild(opt);
            });
            if (select.value !== prev && prev) select.value = prev;
        });
    }

    function nuevaRubrica() {
        const contenedor = document.getElementById('contenedorRubricas');
        const item = document.createElement('div');
        item.className = 'card mb-4 rubrica-item border-0 shadow-sm rounded-4 overflow-hidden';
        
        const dataInicial = {
            columnas: ["Excelente (5)", "Bueno (4)", "Regular (3)", "Deficiente (2)"],
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
        customInput.classList.toggle('d-none', select.value !== 'CUSTOM');
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

    function renderizarTablaRubrica(item) {
        const jsonStr = item.querySelector('.rubrica-json-input').value;
        if (!jsonStr) return;
        let data;
        try {
            data = JSON.parse(jsonStr);
        } catch(e) {
            data = { columnas: ["Excelente (5)", "Bueno (4)", "Regular (3)", "Deficiente (2)"], filas: [{ criterio: "Criterio 1", valores: ["", "", "", ""] }] };
        }
        const table = item.querySelector('.tabla-rubrica-visual');
        if (!table) return;
        
        let html = `<thead><tr>`;
        html += `<th class="p-2 text-center" style="width: 20%; background-color: #f8fafc;"><div class="d-flex align-items-center justify-content-center">Criterio</div></th>`;
        (data.columnas || []).forEach((col, i) => {
            html += `<th class="p-2 text-center" style="background-color: #f8fafc;"><div class="d-flex align-items-center justify-content-center">
                <button type="button" class="btn btn-link text-danger btn-xs p-0 me-1" onclick="eliminarColumnaRubrica(this, ${i})"><i class="bi bi-x-circle"></i></button>
                <input type="text" class="form-control form-control-sm border-0 bg-transparent fw-bold text-center p-0" style="font-size: 0.75rem;" value="${col}" oninput="actualizarJSONRubrica(this, 'col', ${i})">
            </div></th>`;
        });
        html += `</tr></thead><tbody>`;
        
        (data.filas || []).forEach((fila, i) => {
            const crit = fila.criterio || fila.nombre || `Criterio ${i + 1}`;
            html += `<tr><td class="p-2" style="width: 20%; background-color: #fdfdfd;"><div class="d-flex align-items-start">
                <button type="button" class="btn btn-link text-danger btn-xs p-0 me-2 mt-1" onclick="eliminarFilaRubrica(this, ${i})"><i class="bi bi-dash-circle"></i></button>
                <textarea class="form-control form-control-sm border-0 fw-bold p-0 bg-transparent" rows="2" style="font-size: 0.8rem; resize: none;" oninput="actualizarJSONRubrica(this, 'row_name', ${i})">${crit}</textarea>
            </div></td>`;
            (fila.valores || []).forEach((val, j) => {
                html += `<td class="p-1"><textarea class="form-control form-control-sm border-0 small" rows="3" style="font-size: 0.75rem; line-height: 1.2;" placeholder="..." oninput="actualizarJSONRubrica(this, 'val', ${i}, ${j})">${val}</textarea></td>`;
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
            const lines = e.target.result.split('\n').filter(l => l.trim().length > 0);
            if (lines.length < 2) return;
            const sep = lines[0].includes(';') ? ';' : ',';
            const headers = lines[0].split(sep).map(h => h.trim().replace(/^"|"$/g, ''));
            const columnas = headers.slice(1);
            const filas = [];
            for (let i = 1; i < lines.length; i++) {
                const parts = lines[i].split(sep).map(p => p.trim().replace(/^"|"$/g, ''));
                if (parts.length > 0) {
                    filas.push({ criterio: parts[0], valores: parts.slice(1) });
                }
            }
            const item = input.closest('.rubrica-item');
            item.querySelector('.rubrica-json-input').value = JSON.stringify({ columnas, filas });
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
            Swal.fire('Error', 'No se encontró ninguna tabla en el código HTML suministrado.', 'error');
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
            for (let i = (isFirstHeaderCriterion ? 1 : 0); i < headerCells.length; i++) {
                newRubricaData.columnas.push(headerCells[i].textContent.trim());
            }
        } else {
            newRubricaData.columnas = ["Excelente (5)", "Bueno (4)", "Regular (3)", "Deficiente (2)"];
        }

        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length > 0) {
                const fila = {
                    criterio: cells[0]?.textContent.trim() || `Criterio ${newRubricaData.filas.length + 1}`,
                    valores: []
                };
                let isFirstHeaderCriterion = false;
                if (headerCells && headerCells.length > 1 && headerCells[0].textContent.trim().toLowerCase().includes('criterio')) {
                    isFirstHeaderCriterion = true;
                }
                let startCellVal = (isFirstHeaderCriterion ? 1 : 0);
                for (let i = startCellVal; i < cells.length; i++) {
                    fila.valores.push(cells[i]?.textContent.trim() || '');
                }
                newRubricaData.filas.push(fila);
            }
        });

        rubricaTargetInyeccion.querySelector('.rubrica-json-input').value = JSON.stringify(newRubricaData);
        renderizarTablaRubrica(rubricaTargetInyeccion);
        sincronizarSilabo();
        bootstrap.Modal.getInstance(document.getElementById('modalInyectarHTML')).hide();
        Swal.fire('Éxito', 'HTML procesado y rúbrica actualizada.', 'success');
    }

    function abrirGeneradorRubricaIA() {
        const rubrosDeclarados = [];
        document.querySelectorAll('input[name="rubros[]"]').forEach(input => {
            const val = input.value.trim();
            if (val && !rubrosDeclarados.includes(val)) rubrosDeclarados.push(val);
        });

        let opcionesHtml = '';
        rubrosDeclarados.forEach(r => {
            opcionesHtml += `<option value="${r}">${r}</option>`;
        });

        Swal.fire({
            title: '✨ Asistente de Rúbricas con IA (Gemini)',
            html: `
                <div class="text-start small text-muted mb-3">
                    Configura la actividad y generaremos un prompt listo para usar en Gemini.
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
                    <label class="form-label fw-bold">3. Escala y Puntuación (Columnas)</label>
                    <input type="text" id="ia_escala" class="form-control shadow-sm" value="Excelente (5), Bueno (4), Regular (3), Deficiente (2)">
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#64748b',
            confirmButtonText: '🚀 Generar Prompt para Gemini',
            cancelButtonText: 'Cancelar',
            didOpen: () => {
                const select = document.getElementById('ia_actividad_sel');
                if (select && select.options.length <= 2) { 
                    select.value = "CUSTOM";
                    toggleIaActividadCustom(select);
                }
            },
            preConfirm: () => {
                const select = document.getElementById('ia_actividad_sel');
                let actividad = select.value === "CUSTOM" ? document.getElementById('ia_actividad').value.trim() : select.value.trim();
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
Niveles de desempeño: "${escala}".
Devuélveme el resultado estrictamente formateado como código HTML (etiqueta <table>).`;
                navigator.clipboard.writeText(prompt);
                Swal.fire('¡Prompt Copiado!', 'El prompt se ha copiado al portapapeles. Pégalo en Gemini y luego importa el HTML resultante.', 'success');
            }
        });
    }

    function toggleIaActividadCustom(select) {
        const input = document.getElementById('ia_actividad');
        if (input) input.classList.toggle('d-none', select.value !== 'CUSTOM');
    }

    function validarYGuardarPlan() {
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }

        let totalP = 0;
        document.querySelectorAll('input[name="porcentajes[]"]').forEach(inp => {
            totalP += parseFloat(inp.value) || 0;
        });

        const form = document.getElementById('formPlanEstudios');

        if (document.querySelectorAll('#tablaEvaluacion tbody tr').length > 0 && Math.round(totalP) !== 100) {
            Swal.fire({
                title: 'Evaluación Incompleta',
                text: `El porcentaje total de los rubros suma ${Math.round(totalP)}% (debe sumar exactamente 100%). ¿Desea guardar el curso de todas formas?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, guardar de todas formas',
                cancelButtonText: 'Revisar evaluación',
                confirmButtonColor: '#5fb230',
                cancelButtonColor: '#6c757d'
            }).then((res) => {
                if (res.isConfirmed) {
                    form.submit();
                } else {
                    const tabEval = document.querySelector('button[data-bs-target="#evaluacion_tab"]');
                    if (tabEval) tabEval.click();
                }
            });
            return;
        }

        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        inicializarTinyMCE();
        calcularDistribucionHoras();
        sincronizarSilabo();

        const tablaEvaluacion = document.getElementById('tablaEvaluacion');
        if (tablaEvaluacion) {
            tablaEvaluacion.addEventListener('input', sincronizarSilabo);
        }

        const selProg = document.getElementById('id_programa');
        if (selProg && selProg.value) {
            actualizarPrecioPrograma(selProg);
        }
    });
</script>
@endsection
