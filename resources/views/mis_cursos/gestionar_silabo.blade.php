@extends('layouts.app')

@section('title', 'Gestionar Sílabo - ' . $curso->planEstudio->materia)

@section('styles')
<style>
    :root { 
        --primary-color: #5fb230; 
        --primary-hover: #4e9a26; 
        --text-dark: #2d3436; 
        --bg-light: #e2e8f0; 
    }
    
    .main-content-container { 
        padding-top: 1.5rem; 
        padding-bottom: 3rem; 
        color: #1e293b;
    }
    
    .page-header { 
        background: white; 
        padding: 1.5rem 2rem; 
        border-radius: 1rem; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); 
        margin-bottom: 2rem; 
        border: 1px solid #e2e8f0; 
    }
    
    .section-title { 
        font-weight: 800; 
        color: var(--text-dark); 
        letter-spacing: -0.025em; 
    }
    
    .card { 
        border-radius: 1rem; 
        border: 1px solid #e2e8f0; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); 
        overflow: hidden; 
        background-color: white;
    }
    
    .nav-tabs .nav-link { 
        color: #64748b; 
        font-weight: 600; 
        border: none; 
        padding: 1rem 1.5rem; 
    }
    
    .nav-tabs .nav-link.active { 
        color: var(--primary-color) !important; 
        border-bottom: 3px solid var(--primary-color) !important; 
        background: transparent !important; 
    }
    
    .btn-moodle { 
        background-color: #f98012 !important; 
        color: white !important; 
        border: none; 
        font-size: 0.75rem; 
        padding: 4px 8px; 
        border-radius: 6px; 
    }
    
    .moodle-activity-badge { 
        font-size: 0.65rem; 
        padding: 2px 6px; 
        border-radius: 4px; 
        margin-right: 3px; 
        color: white; 
    }
    
    .badge-forum { background-color: #7d5fff; } 
    .badge-assign { background-color: #ff4d4d; }
    
    /* Mejoras de Tabla Cronograma */
    #tablaCronograma th { 
        background-color: #f8fafc; 
        color: #475569; 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        letter-spacing: 0.05em; 
        padding: 12px; 
    }
    
    #tablaCronograma td { 
        padding: 8px; 
        vertical-align: top; 
    }
    
    .tox-tinymce { 
        border-radius: 8px !important; 
        border: 1px solid #e2e8f0 !important; 
        min-height: 150px !important; 
    }
    
    .btn-save-float { 
        position: fixed; 
        bottom: 2rem; 
        right: 5.5rem; 
        z-index: 1000; 
        box-shadow: 0 10px 25px rgba(95, 178, 48, 0.4); 
        padding: 1rem 2rem; 
        font-weight: 800; 
        border-radius: 2rem; 
        transition: all 0.3s ease;
    }
    
    /* Estilos Modalidad */
    #tablaCronograma { 
        border-collapse: collapse !important; 
        border: 1px solid #e2e8f0 !important; 
    }
    
    .td-modalidad { 
        transition: all 0.3s; 
        vertical-align: middle !important; 
        text-align: center; 
        padding: 0 !important; 
        width: 120px; 
        position: relative; 
        border: none !important; 
    }
    
    .td-sincronico { 
        background-color: #2D8CFF !important; 
        color: white !important; 
    }
    
    .td-asincronico { 
        background-color: #f8fafc !important; 
        color: #64748b !important; 
    }

    .modalidad-select { 
        font-size: 0.7rem; 
        font-weight: 800; 
        border: none !important;
        padding: 12px 5px 0px 5px; 
        width: 100%; 
        text-transform: uppercase;
        background-color: transparent !important;
        color: inherit !important;
        text-align: center;
        cursor: pointer;
        appearance: none; 
        outline: none !important;
        box-shadow: none !important;
        display: block;
    }
    
    .modalidad-select option { 
        background-color: white; 
        color: black; 
    }
    
    .zoom-logo-mini { 
        width: 75%; 
        height: auto; 
        display: block; 
        margin: 0 auto; 
    }
    
    .zoom-logo-container { 
        padding: 5px 0 12px 0; 
        width: 100%; 
        margin: 0; 
    }

    /* Estilos Rúbricas Mejorados */
    .rubrica-item { 
        transition: all 0.3s ease; 
        border: 1px solid #e2e8f0 !important; 
    }
    
    .rubrica-item:hover { 
        box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important; 
        transform: translateY(-2px); 
    }
    
    .tabla-rubrica-visual th { 
        background-color: #f1f5f9; 
        color: #475569; 
        font-size: 0.7rem; 
        text-align: center; 
        font-weight: 800; 
        border: 1px solid #e2e8f0; 
    }
    
    .tabla-rubrica-visual td { 
        background-color: white; 
        border: 1px solid #e2e8f0; 
        padding: 4px; 
    }
    
    .rubrica-item .form-select-sm, .rubrica-item .form-control-sm { 
        font-size: 0.75rem; 
        border-radius: 6px; 
    }
    
    .btn-xs { 
        padding: 2px 6px; 
        font-size: 0.65rem; 
        border-radius: 4px; 
    }

    /* Estilo para el botón rojo parpadeante de advertencia de observaciones */
    @keyframes alertPulse {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
    
    .btn-danger.alert-pulse {
        animation: alertPulse 1.8s infinite;
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
    }
    
    .btn-danger.alert-pulse:hover {
        background-color: #bb2d3b !important;
    }

    /* Animaciones y estilos para las observaciones */
    .revision-item {
        transition: all 0.3s ease;
        border-left: 4px solid #ffc107;
    }
    
    .revision-item.resuelto {
        border-left: 4px solid #198754;
        background-color: #f8f9fa;
        opacity: 0.85;
    }
    
    .revision-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.05);
    }
    
    /* Contenedor del botón flotante */
    .academic-float-btn-container {
        position: fixed;
        bottom: 2rem;
        right: 20rem;
        z-index: 1040;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .academic-float-btn-container:hover {
        transform: scale(1.05);
    }
    
    .academic-float-btn-container .btn {
        border: none;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.25) !important;
    }
    
    @media (max-width: 768px) {
        .btn-save-float {
            bottom: 1.5rem;
            right: 5.5rem;
            padding: 0.8rem 1.5rem;
            font-size: 0.9rem;
        }
        .academic-float-btn-container {
            bottom: 5.5rem;
            right: 2rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 main-content-container">
    
    <!-- PAGE HEADER -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('mis_cursos.index') }}" class="text-decoration-none">Mis Cursos</a></li>
                    <li class="breadcrumb-item active">Gestionar Sílabo</li>
                </ol>
            </nav>
            <h2 class="section-title mb-0"><i class="bi bi-journal-text text-success me-2"></i> {{ $curso->planEstudio->materia }}</h2>
            <p class="mb-0 mt-1" style="font-size: 0.95rem; color: #475569;">
                Código: <span class="fw-bold text-dark">{{ $curso->planEstudio->codigo }}</span> | 
                Programa Académico (Sílabo) | 
                Profesor: <span class="fw-bold text-dark"><i class="bi bi-person-fill ms-1 me-1 text-secondary"></i>{{ $curso->profesor->nombre ?? 'Docente' }} {{ $curso->profesor->apellidos ?? '' }}</span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('mis_cursos.silabo_pdf', $curso->id_curso_activo) }}" target="_blank" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-file-earmark-pdf-fill me-2"></i> Generar Silabo PDF
            </a>
            @if (Auth::user()->id_rol == 1)
                <button type="button" class="btn btn-outline-primary rounded-pill px-4" onclick="abrirModalImportar()"><i class="bi bi-cloud-download me-2"></i> Importar</button>
            @endif
            <a href="{{ route('mis_cursos.ver', $curso->id_curso_activo) }}" class="btn btn-secondary rounded-pill px-4"><i class="bi bi-arrow-left me-2"></i> Volver</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2) !important;">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- MAIN FORM -->
    <form action="{{ route('mis_cursos.gestionar_silabo.guardar', $curso->id_curso_activo) }}" method="POST" id="formSilabo">
        @csrf
        <input type="hidden" name="active_tab" id="active_tab_input" value="{{ request('tab', 'general') }}">
        
        <!-- Botón Flotante de Guardado -->
        <button type="button" onclick="validarYGuardar()" class="btn btn-primary btn-save-float shadow-lg border-white border-2" style="background-color: var(--primary-color); border: none;">
            <i class="bi bi-save-fill me-2"></i> GUARDAR CAMBIOS
        </button>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-0">
                <ul class="nav nav-tabs border-0" id="silaboTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general" type="button">General</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#academic" type="button">Académico</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#content" type="button">Contenidos</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#eval" type="button">Evaluación</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cronograma_tab" type="button">Cronograma</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rubricas_tab" type="button">Rúbricas</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#biblio_tab" type="button">Referencias.</button></li>
                    <li class="nav-item ms-auto pe-3 pt-2">
                        <a href="{{ route('mis_cursos.silabo_pdf', $curso->id_curso_activo) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill fw-bold">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Generar Silabo PDF
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-4">
                <div class="tab-content">
                    
                    <!-- Tab General -->
                    <div class="tab-pane fade show active" id="general">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Descripción del Curso</label>
                            <textarea name="descripcion" id="descripcion" class="form-control editor-rico-normal">{{ $silabo->descripcion ?? '' }}</textarea>
                        </div>
                    </div>
                    
                    <!-- Tab Académico -->
                    <div class="tab-pane fade" id="academic">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Objetivo General</label>
                            <textarea name="objetivo_general" id="objetivo_general" class="form-control editor-rico-normal">{{ $silabo->objetivo_general ?? $curso->planEstudio->objetivo_general }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Objetivos Específicos</label>
                            <textarea name="objetivos_especificos" id="objetivos_especificos" class="form-control editor-rico-normal">{{ $silabo->objetivos_especificos ?? $curso->planEstudio->objetivos_especificos }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Metodología</label>
                            <textarea name="metodologia" id="metodologia" class="form-control editor-rico-normal">{{ $silabo->metodologia ?? '' }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Estrategias de Aprendizaje</label>
                            <textarea name="estrategias_aprendizaje" id="estrategias_aprendizaje" class="form-control editor-rico-normal">{{ $silabo->estrategias_aprendizaje ?? '' }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Recursos de Aprendizaje</label>
                            <textarea name="recursos_aprendizaje" id="recursos_aprendizaje" class="form-control editor-rico-normal">{{ $silabo->recursos_aprendizaje ?? '' }}</textarea>
                        </div>
                    </div>
                    
                    <!-- Tab Contenidos -->
                    <div class="tab-pane fade" id="content">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Contenidos Temáticos</label>
                            <textarea name="contenidos" id="contenidos" class="form-control editor-rico-normal">{{ $silabo->contenidos ?? '' }}</textarea>
                        </div>
                    </div>
                    
                    <!-- Tab Cronograma -->
                    <div class="tab-pane fade" id="cronograma_tab">
                        <div class="mb-4">
                            <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-calendar3 me-2"></i>Cronograma de Actividades</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="tablaCronograma">
                                <thead class="text-center">
                                    <tr>
                                        <th style="width: 4%;">Sem.</th>
                                        <th style="width: 12%;">Fecha</th>
                                        <th style="width: 31%;">Actividad / Tema</th>
                                        <th style="width: 31%;">Tareas / Entregables</th>
                                        <th style="width: 9%;">Moodle</th>
                                        <th style="width: 12%;">Modalidad</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cronograma as $idx => $cro)
                                        @php
                                            $cro_array = (array)$cro;
                                            $idActExistente = 'act_ex_' . $idx;
                                            $idTarExistente = 'tar_ex_' . $idx;
                                        @endphp
                                        <tr>
                                            <td class="text-center fw-bold crono-semana-num">
                                                {{ $idx + 1 }}
                                                <input type="hidden" name="c_semana[]" value="{{ $idx + 1 }}">
                                            </td>
                                            <td><input type="date" name="c_fecha[]" class="form-control form-control-sm" value="{{ $cro_array['fecha'] }}"></td>
                                            <td><textarea id="{{ $idActExistente }}" name="c_actividad[]" class="form-control form-control-sm editor-rico-compacto">{{ $cro_array['actividad'] }}</textarea></td>
                                            <td>
                                                <div class="form-control form-control-sm tarea-display-only bg-light border border-light-subtle rounded-3" style="min-height: 120px; overflow-y: auto; padding: 0.5rem 0.75rem;">
                                                    {!! !empty($cro_array['tareas']) ? $cro_array['tareas'] : '<span class="text-muted small italic"><i class="bi bi-info-circle me-1"></i> Sin actividades configuradas. Utilice el botón "Config."</span>' !!}
                                                </div>
                                                <textarea id="{{ $idTarExistente }}" name="c_tareas[]" class="d-none">{{ $cro_array['tareas'] }}</textarea>
                                            </td>
                                            <td class="text-center">
                                                <input type="hidden" name="c_moodle[]" class="moodle-data-input" value="{{ $cro_array['actividades_moodle'] }}">
                                                <div class="moodle-summary mb-1"></div>
                                                <button type="button" class="btn btn-moodle w-100" onclick="abrirConfigMoodle(this)"><i class="bi bi-gear-fill me-1"></i> Config.</button>
                                            </td>
                                            <td class="text-center">
                                                <select name="c_modalidad[]" class="form-select modalidad-select {{ ($cro_array['modalidad_trabajo'] ?? '') == 'Sincrónico' ? 'select-sincronico' : 'select-asincronico' }}" onchange="actualizarEstiloModalidad(this)">
                                                    <option value="Sincrónico" {{ ($cro_array['modalidad_trabajo'] ?? '') == 'Sincrónico' ? 'selected' : '' }}>Sincrónico</option>
                                                    <option value="Asincrónico" {{ (($cro_array['modalidad_trabajo'] ?? '') == 'Asincrónico' || empty($cro_array['modalidad_trabajo'])) ? 'selected' : '' }}>Asincrónico</option>
                                                </select>
                                                <div class="mt-1 zoom-logo-container {{ ($cro_array['modalidad_trabajo'] ?? '') == 'Sincrónico' ? '' : 'd-none' }}">
                                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c4/Zoom_Video_Communications_Logo.svg/1200px-Zoom_Video_Communications_Logo.svg.png" class="zoom-logo-mini" alt="Zoom">
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
                    
                    <!-- Tab Evaluación -->
                    <div class="tab-pane fade" id="eval">
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
                                        @php $ev_array = (array)$ev; @endphp
                                        <tr>
                                            <td class="text-center fw-bold eval-num">{{ $index + 1 }}</td>
                                            <td><input type="text" name="rubros[]" class="form-control fw-bold" value="{{ $ev_array['rubro'] }}" oninput="actualizarOpcionesNombresEnRubricas()"></td>
                                            <td><input type="number" name="porcentajes[]" class="form-control valor-p text-center fw-bold" value="{{ (int)$ev_array['porcentaje'] }}"></td>
                                            <td><input type="number" name="cantidades_eval[]" class="form-control text-center" value="{{ $ev_array['cantidad'] }}"></td>
                                            <td><input type="text" class="form-control text-center eval-unit-val" readonly value="0"></td>
                                            <td>
                                                <select name="tipos_moodle_eval[]" class="form-select form-select-sm">
                                                    <option value="" {{ empty($ev_array['tipo_moodle']) ? 'selected' : '' }}>-- Sin actividad Moodle --</option>
                                                    <option value="assign" {{ ($ev_array['tipo_moodle'] ?? '') === 'assign' ? 'selected' : '' }}>📄 Tarea</option>
                                                    <option value="quiz" {{ ($ev_array['tipo_moodle'] ?? '') === 'quiz' ? 'selected' : '' }}>📝 Quiz / Examen</option>
                                                    <option value="forum" {{ ($ev_array['tipo_moodle'] ?? '') === 'forum' ? 'selected' : '' }}>💬 Foro</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="anexos_eval[]" class="form-select form-select-sm select-anexo-rubrica" data-seleccionado="{{ $ev_array['anexo'] }}">
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
                            <button type="button" class="btn btn-success rounded-pill px-4 mt-3" onclick="agregarFila()">+ Añadir Rubro</button>
                        </div>
                    </div>
                    
                    <!-- Tab Rúbricas -->
                    <div class="tab-pane fade" id="rubricas_tab">
                        <div id="contenedorRubricas">
                            @foreach($rubricas as $rub)
                                @php $rub_array = (array)$rub; @endphp
                                <div class="card mb-4 rubrica-item border-0 shadow-sm rounded-4 overflow-hidden" data-id="rub_{{ $rub_array['id_rubrica'] }}">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-3 px-4">
                                        <div class="d-flex gap-3 align-items-center w-75">
                                            <select name="rubrica_nombre_sel[]" class="form-select form-select-sm w-50 fw-bold border-primary shadow-sm" onchange="toggleNombreRubrica(this)" data-seleccionado="{{ $rub_array['nombre'] }}">
                                                <option value="">Asociar a un Rubro...</option>
                                                <option value="CUSTOM" {{ ($rub_array['nombre'] === "CUSTOM") ? 'selected' : '' }}>-- Nombre Personalizado --</option>
                                                @if ($rub_array['nombre'] !== "CUSTOM")
                                                    <option value="{{ $rub_array['nombre'] }}" selected>{{ $rub_array['nombre'] }}</option>
                                                @endif
                                            </select>
                                            <input type="text" name="rubrica_nombre_custom[]" class="form-control form-control-sm w-50 {{ ($rub_array['nombre'] === "CUSTOM") ? '' : 'd-none' }} shadow-sm" value="{{ $rub_array['nombre'] }}" placeholder="Nombre de la Rúbrica">
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportarRubricaCSV(this)" title="Exportar CSV"><i class="bi bi-download"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="abrirModalInyectarHTML(this)" title="Cargar HTML"><i class="bi bi-code-slash"></i></button>
                                            <button type="button" class="btn btn-sm btn-danger rounded-circle" onclick="this.closest('.rubrica-item').remove()"><i class="bi bi-x"></i></button>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-bordered mb-0 tabla-rubrica-visual"></table>
                                        <textarea name="rubrica_json[]" class="d-none rubrica-json-input">{{ $rub_array['contenido_json'] }}</textarea>
                                        <div class="p-3 bg-light border-top d-flex gap-3 align-items-center">
                                            <div class="input-group input-group-sm" style="width: 150px;">
                                                <span class="input-group-text">Peso %</span>
                                                <input type="number" step="0.1" name="rubrica_porcentaje[]" class="form-control" value="{{ $rub_array['porcentaje_total'] }}">
                                            </div>
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <span class="input-group-text">Cant.</span>
                                                <input type="number" name="rubrica_cantidad[]" class="form-control" value="{{ $rub_array['cantidad'] }}">
                                            </div>
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <span class="input-group-text">Puntos</span>
                                                <input type="number" name="rubrica_valor[]" class="form-control" value="{{ $rub_array['valor'] }}">
                                            </div>
                                            <button type="button" class="btn btn-xs btn-outline-success ms-auto" onclick="agregarFilaRubrica(this)">+ Fila</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-primary rounded-pill px-4 mt-3" onclick="nuevaRubrica()"><i class="bi bi-plus-circle me-2"></i> Crear Rúbrica</button>
                        <button type="button" class="btn btn-info text-white rounded-pill px-4 ms-2 mt-3 shadow-sm fw-bold" onclick="abrirGeneradorRubricaIA()"><i class="bi bi-cpu-fill me-2"></i> Crear con IA (Gemini)</button>
                    </div>
                    
                    <!-- Tab Referencias -->
                    <div class="tab-pane fade" id="biblio_tab">
                        <textarea name="bibliografia" id="bibliografia" class="form-control editor-rico-normal">{{ $silabo->bibliografia ?? '' }}</textarea>
                    </div>

                </div>
            </div>
            
            <div class="card-footer bg-white p-4">
                <button type="button" onclick="validarYGuardar()" class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm fw-bold" style="background-color: var(--primary-color); border: none;">
                    <i class="bi bi-save-fill me-2"></i> GUARDAR TODO EL PROGRAMA
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Modal para Configurar Moodle -->
<div class="modal fade" id="modalMoodle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background-color: white; color: #333;">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-mortarboard-fill me-2"></i>Configurar Actividades Moodle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-dark" id="contenedorActividadesMoodle"></div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-primary px-4" onclick="guardarConfigMoodle()">Guardar y Sincronizar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Inyectar HTML en Rúbrica -->
<div class="modal fade" id="modalInyectarHTML" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background-color: white; color: #333;">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="bi bi-code-slash me-2"></i>Inyectar HTML en Rúbrica</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-dark">
                <p class="text-muted small">Pega aquí el código HTML de una tabla (<code>&lt;table&gt;...&lt;/table&gt;</code>). El sistema intentará parsearlo para generar la rúbrica.</p>
                <textarea id="htmlRubricaInput" class="form-control text-dark" rows="10" placeholder="Pega tu HTML aquí..."></textarea>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnProcesarHTMLRubrica">Procesar HTML</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
const IS_ADMIN = {{ Auth::user()->id_rol == 1 ? 'true' : 'false' }};
let filaActualMoodle = null;

function sincronizarSilabo() {
    actualizarOpcionesNombresEnRubricas();
    actualizarSelectsRubricas();
    calcularTotal();
}

function actualizarOpcionesNombresEnRubricas() {
    const rubrosMap = new Map();
    document.querySelectorAll('#tablaEvaluacion tbody tr').forEach(tr => {
        const nom = tr.querySelector('input[name="rubros[]"]')?.value.trim();
        if (nom) {
            const porc = parseFloat(tr.querySelector('input[name="porcentajes[]"]')?.value) || 0;
            const cant = parseInt(tr.querySelector('input[name="cantidades_eval[]"]')?.value) || 1;
            rubrosMap.set(nom, { porcentaje: porc, cantidad: cant, puntos: (cant > 0 ? (porc / cant).toFixed(1) : 0) });
        }
    });

    document.querySelectorAll('.rubrica-item').forEach(item => {
        const select = item.querySelector('select[name="rubrica_nombre_sel[]"]');
        if (!select) return;
        
        const customInput = item.querySelector('input[name="rubrica_nombre_custom[]"]');
        const valActual = select.dataset.seleccionado || select.value;
        
        let infoDiv = item.querySelector('.rubrica-calc-info');
        if (!infoDiv) {
            infoDiv = document.createElement('div');
            infoDiv.className = 'rubrica-calc-info alert alert-primary py-2 px-3 mb-3 mx-4 mt-2 d-flex align-items-center justify-content-between rounded-3 border-0 shadow-sm';
            infoDiv.style.borderLeft = '4px solid #0056b3 !important';
            infoDiv.style.fontSize = '0.85rem';
            item.querySelector('.card-body').prepend(infoDiv);
        }

        select.innerHTML = '<option value="">-- Vincular a un Rubro de Evaluación --</option><option value="CUSTOM">-- Nombre Personalizado --</option>';
        rubrosMap.forEach((data, nombre) => {
            const opt = document.createElement('option');
            opt.value = nombre;
            opt.textContent = nombre;
            if (nombre === valActual) opt.selected = true;
            select.appendChild(opt);
        });
        select.value = valActual;

        const rData = rubrosMap.get(select.value);
        if (rData && select.value !== "CUSTOM") {
            item.querySelector('input[name="rubrica_porcentaje[]"]').value = rData.porcentaje;
            item.querySelector('input[name="rubrica_cantidad[]"]').value = rData.cantidad;
            item.querySelector('input[name="rubrica_valor[]"]').value = rData.puntos;
            customInput.value = select.value; 

            infoDiv.classList.remove('d-none');
            infoDiv.innerHTML = `
                <div class="d-flex align-items-center"><i class="bi bi-calculator-fill me-2 fs-5 text-primary"></i><div><div class="small opacity-75 text-dark">Sincronizado con Evaluación</div><div class="fw-bold text-dark">${select.value}</div></div></div>
                <div class="text-end"><span class="badge bg-white text-primary px-3 py-2 fs-6 shadow-sm border">${rData.porcentaje}% total ÷ ${rData.cantidad} und. = <b class="ms-1">${rData.puntos} Ptos. c/u</b></span></div>`;
        } else {
            infoDiv.classList.add('d-none');
        }
    });
}

function actualizarSelectsRubricas() {
    const nombresDeRubricas = new Set();
    document.querySelectorAll('.rubrica-item').forEach(item => {
        const sel = item.querySelector('select[name="rubrica_nombre_sel[]"]');
        const custom = item.querySelector('input[name="rubrica_nombre_custom[]"]');
        let nombre = '';

        if (sel.value === "CUSTOM" && custom && !custom.classList.contains('d-none')) {
            nombre = custom.value.trim();
        } else if (sel.value && sel.value !== "CUSTOM") {
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

document.addEventListener('DOMContentLoaded', () => {
    inicializarTinyMCE();
    inicializarRubricasExistentes();
    inicializarResumenesMoodle();
    configurarTabs();
    sincronizarSilabo();

    const tablaEvaluacion = document.getElementById('tablaEvaluacion');
    const contenedorRubricas = document.getElementById('contenedorRubricas');

    if (tablaEvaluacion) {
        tablaEvaluacion.addEventListener('input', debounce(sincronizarSilabo, 300));
        tablaEvaluacion.addEventListener('change', sincronizarSilabo);
    }
    if (contenedorRubricas) {
        contenedorRubricas.addEventListener('input', debounce(sincronizarSilabo, 300));
        contenedorRubricas.addEventListener('change', sincronizarSilabo);
    }

    const observer = new MutationObserver(() => sincronizarSilabo());
    if (tablaEvaluacion?.querySelector('tbody')) observer.observe(tablaEvaluacion.querySelector('tbody'), { childList: true });
    if (contenedorRubricas) observer.observe(contenedorRubricas, { childList: true });

    window.validarYGuardar = function() {
        let totalPct = 0;
        document.querySelectorAll('input[name="porcentajes[]"]').forEach(inp => totalPct += parseFloat(inp.value) || 0);

        const totalPctRedondeado = Math.round(totalPct * 100) / 100;
        if (Math.round(totalPctRedondeado) !== 100) {
            Swal.fire({
                title: 'Evaluación Incompleta',
                text: `El porcentaje total de los rubros es ${totalPctRedondeado.toFixed(2)}% (debe sumar exactamente 100% para estar completo). ¿Deseas guardar los cambios como borrador de todas formas?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, guardar borrador',
                cancelButtonText: 'Volver a configurar',
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    _doSubmit();
                } else {
                    document.querySelector('button[data-bs-target="#eval"]')?.click();
                }
            });
            return;
        }

        _doSubmit();
    };

    function _doSubmit() {
        if (typeof tinymce !== 'undefined') tinymce.triggerSave();
        document.getElementById('formSilabo').submit();
    }

    // Autocompletar fechas
    const cronoTable = document.getElementById('tablaCronograma');
    if (cronoTable) {
        cronoTable.querySelector('tbody').addEventListener('change', function(e) {
            if (e.target.name === 'c_fecha[]') {
                const inputs = Array.from(document.querySelectorAll('input[name="c_fecha[]"]'));
                const index = inputs.indexOf(e.target);
                
                if (index === 0 && e.target.value) {
                    Swal.fire({
                        title: '📆 Autocompletar Fechas',
                        text: '¿Deseas completar automáticamente las fechas de las semanas siguientes sumando de 7 en 7 días?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, autocompletar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let fechaBase = new Date(e.target.value + 'T00:00:00');
                            for (let i = 1; i < inputs.length; i++) {
                                let nuevaFecha = new Date(fechaBase.getTime());
                                nuevaFecha.setDate(nuevaFecha.getDate() + (i * 7));
                                inputs[i].value = nuevaFecha.toISOString().split('T')[0];
                            }
                            Swal.fire({ icon: 'success', title: '¡Completado!', timer: 1500, showConfirmButton: false });
                        }
                    });
                }
            }
        });
    }

    const btnProcesarHTML = document.getElementById('btnProcesarHTMLRubrica');
    if (btnProcesarHTML) btnProcesarHTML.addEventListener('click', procesarHTMLRubrica);
});

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => { clearTimeout(timeout); func(...args); };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function inicializarTinyMCE() {
    if (typeof tinymce === 'undefined') return;
    tinymce.init({
        license_key: 'gpl', selector: '.editor-rico-normal', min_height: 400, language: 'es',
        plugins: 'code table lists link autoresize', toolbar: 'undo redo | blocks | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | indent outdent | bullist numlist | code | table | link | removeformat',
        branding: false, promotion: false, autoresize_bottom_margin: 20
    });
    tinymce.init({
        license_key: 'gpl', selector: '.editor-rico-compacto', min_height: 150, menubar: false, language: 'es',
        plugins: 'lists link code autoresize', toolbar: 'bold italic | bullist numlist | code | removeformat', branding: false, promotion: false, autoresize_bottom_margin: 10
    });
}

function configurarTabs() {
    document.querySelectorAll('#silaboTab button').forEach(btn => {
        btn.addEventListener('shown.bs.tab', (e) => {
            document.getElementById('active_tab_input').value = e.target.getAttribute('data-bs-target').replace('#', '');
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    const tabId = urlParams.get('tab');
    if (tabId) {
        const tabEl = document.querySelector(`button[data-bs-target="#${tabId}"]`);
        if (tabEl) bootstrap.Tab.getOrCreateInstance(tabEl).show();
    }
}

function agregarFila() {
    const table = document.getElementById('tablaEvaluacion').getElementsByTagName('tbody')[0];
    const row = table.insertRow();
    row.innerHTML = `
        <td class="text-center fw-bold eval-num"></td>
        <td><input type="text" name="rubros[]" class="form-control fw-bold"></td>
        <td><input type="number" step="0.01" name="porcentajes[]" class="form-control valor-p text-center fw-bold" value="0"></td>
        <td><input type="number" name="cantidades_eval[]" class="form-control text-center" value="1"></td>
        <td><input type="text" class="form-control text-center eval-unit-val" readonly value="0"></td>
        <td>
            <select name="tipos_moodle_eval[]" class="form-select form-select-sm">
                <option value="">-- Sin actividad Moodle --</option>
                <option value="assign">📄 Tarea</option>
                <option value="quiz">📝 Quiz / Examen</option>
                <option value="forum">💬 Foro</option>
            </select>
        </td>
        <td><select name="anexos_eval[]" class="form-select form-select-sm select-anexo-rubrica"><option value="">Ninguno</option></select></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); calcularTotal(); actualizarOpcionesNombresEnRubricas();">Eliminar</button></td>
    `;
}

function calcularTotal() {
    let total = 0;
    let totalCant = 0;
    document.querySelectorAll('#tablaEvaluacion tbody tr').forEach(tr => {
        const porcentajeInput = tr.querySelector('input[name="porcentajes[]"]');
        const cantidadInput = tr.querySelector('input[name="cantidades_eval[]"]');
        const unitValInput = tr.querySelector('.eval-unit-val');
        
        if (!porcentajeInput) return;
        const pct = parseFloat(porcentajeInput.value) || 0;
        const cant = parseInt(cantidadInput?.value) || 1;
        
        total += pct;
        totalCant += cant;
        if (unitValInput) unitValInput.value = cant > 0 ? (pct / cant).toFixed(1) : 0;
    });

    const totalEl = document.getElementById('totalPorcentaje');
    if (totalEl) {
        totalEl.textContent = total.toFixed(1) + '%';
        totalEl.className = 'text-center ' + (Math.round(total) === 100 ? 'text-success' : 'text-danger');
    }
    if (document.getElementById('totalCantidad')) document.getElementById('totalCantidad').textContent = totalCant;

    document.querySelectorAll('.eval-num').forEach((td, idx) => td.textContent = idx + 1);
}

function inicializarRubricasExistentes() {
    document.querySelectorAll('.rubrica-item').forEach(item => renderizarTablaRubrica(item));
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
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="abrirModalInyectarHTML(this)" title="Cargar HTML"><i class="bi bi-code-slash"></i></button>
                <button type="button" class="btn btn-sm btn-danger rounded-circle shadow-sm" onclick="this.closest('.rubrica-item').remove()"><i class="bi bi-x"></i></button>
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
}

function toggleNombreRubrica(select) {
    const customInput = select.closest('.d-flex').querySelector('input[name="rubrica_nombre_custom[]"]');
    customInput.classList.toggle('d-none', select.value !== 'CUSTOM');
    sincronizarSilabo();
}

function renderizarTablaRubrica(item) {
    const jsonStr = item.querySelector('.rubrica-json-input').value;
    const data = JSON.parse(jsonStr);
    const table = item.querySelector('.tabla-rubrica-visual');
    
    let html = `<thead><tr><th class="p-2 text-center" style="width: 20%; background-color: #f8fafc;">Criterio</th>`;
    data.columnas.forEach((col, i) => {
        html += `<th class="p-2 text-center" style="background-color: #f8fafc;"><div class="d-flex align-items-center justify-content-center">
            <button type="button" class="btn btn-link text-danger btn-xs p-0 me-1" onclick="eliminarColumnaRubrica(this, ${i})"><i class="bi bi-x-circle"></i></button>
            <input type="text" class="form-control form-control-sm border-0 bg-transparent fw-bold text-center p-0" style="font-size: 0.75rem;" value="${col}" oninput="actualizarJSONRubrica(this, 'col', ${i})">
        </div></th>`;
    });
    html += `</tr></thead><tbody>`;
    
    data.filas.forEach((fila, i) => {
        html += `<tr><td class="p-2" style="width: 20%;"><div class="d-flex align-items-start">
            <button type="button" class="btn btn-link text-danger btn-xs p-0 me-2 mt-1" onclick="eliminarFilaRubrica(this, ${i})"><i class="bi bi-dash-circle"></i></button>
            <textarea class="form-control form-control-sm border-0 fw-bold p-0 bg-transparent" rows="2" style="font-size: 0.8rem; resize: none;" oninput="actualizarJSONRubrica(this, 'row_name', ${i})">${fila.criterio}</textarea>
        </div></td>`;
        fila.valores.forEach((val, j) => {
            html += `<td class="p-1"><textarea class="form-control form-control-sm border-0 small" rows="3" style="font-size: 0.75rem;" oninput="actualizarJSONRubrica(this, 'val', ${i}, ${j})">${val}</textarea></td>`;
        });
        html += `</tr>`;
    });
    table.innerHTML = html + `</tbody>`;
}

function actualizarJSONRubrica(el, type, idx, idx2) {
    const item = el.closest('.rubrica-item');
    const data = JSON.parse(item.querySelector('.rubrica-json-input').value);
    if (type === 'col') data.columnas[idx] = el.value;
    if (type === 'row_name') data.filas[idx].criterio = el.value;
    if (type === 'val') data.filas[idx].valores[idx2] = el.value;
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
    data.filas.forEach(f => f.valores.push(""));
    item.querySelector('.rubrica-json-input').value = JSON.stringify(data);
    renderizarTablaRubrica(item);
}

function eliminarColumnaRubrica(btn, idx) {
    const item = btn.closest('.rubrica-item');
    const data = JSON.parse(item.querySelector('.rubrica-json-input').value);
    if (data.columnas.length <= 2) return;
    data.columnas.splice(idx, 1);
    data.filas.forEach(f => f.valores.splice(idx, 1));
    item.querySelector('.rubrica-json-input').value = JSON.stringify(data);
    renderizarTablaRubrica(item);
}

function exportarRubricaCSV(btn) {
    const item = btn.closest('.rubrica-item');
    const data = JSON.parse(item.querySelector('.rubrica-json-input').value);
    let csv = data.columnas.join(';') + "\n";
    data.filas.forEach(f => csv += f.criterio + ';' + f.valores.join(';') + "\n");
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.setAttribute("download", "rubrica.csv");
    link.click();
}

function agregarFilaCronograma() {
    const table = document.getElementById('tablaCronograma').getElementsByTagName('tbody')[0];
    const newIndex = table.rows.length + 1;
    const row = table.insertRow();
    const idAct = 'act_' + Date.now();
    row.innerHTML = `
        <td class="text-center fw-bold crono-semana-num">${newIndex}<input type="hidden" name="c_semana[]" value="${newIndex}"></td>
        <td><input type="date" name="c_fecha[]" class="form-control form-control-sm"></td>
        <td><textarea id="${idAct}" name="c_actividad[]" class="form-control form-control-sm editor-rico-compacto"></textarea></td>
        <td>
            <div class="form-control form-control-sm tarea-display-only bg-light border border-light-subtle rounded-3" style="min-height: 120px; overflow-y: auto; padding: 0.5rem 0.75rem;">
                <span class="text-muted small italic"><i class="bi bi-info-circle me-1"></i> Sin actividades configuradas. Utilice el botón "Config."</span>
            </div>
            <textarea name="c_tareas[]" class="d-none"></textarea>
        </td>
        <td class="text-center"><input type="hidden" name="c_moodle[]" class="moodle-data-input" value=""><div class="moodle-summary mb-1"></div><button type="button" class="btn btn-moodle w-100" onclick="abrirConfigMoodle(this)"><i class="bi bi-gear-fill me-1"></i> Config.</button></td>
        <td><select name="c_modalidad[]" class="form-select modalidad-select select-asincronico" onchange="actualizarEstiloModalidad(this)"><option value="Sincrónico">Sincrónico</option><option value="Asincrónico" selected>Asincrónico</option></select><div class="mt-1 zoom-logo-container d-none"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c4/Zoom_Video_Communications_Logo.svg/1200px-Zoom_Video_Communications_Logo.svg.png" class="zoom-logo-mini" alt="Zoom"></div></td>
        <td class="text-center"><button type="button" class="btn btn-link text-danger p-0" onclick="eliminarFilaCronograma(this)"><i class="bi bi-trash fs-5"></i></button></td>
    `;
    tinymce.init({
        license_key: 'gpl', selector: `#${idAct}`, min_height: 150, menubar: false, language: 'es',
        plugins: 'lists link code autoresize', toolbar: 'bold italic | bullist numlist | code | removeformat', branding: false, promotion: false, autoresize_bottom_margin: 10
    });
}

function eliminarFilaCronograma(btn) {
    const row = btn.closest('tr');
    row.querySelectorAll('textarea').forEach(t => { if (t.id && typeof tinymce !== 'undefined') tinymce.get(t.id)?.remove(); });
    row.remove();
    document.querySelectorAll('#tablaCronograma tbody tr').forEach((row, index) => {
        row.querySelector('.crono-semana-num').innerHTML = `${index + 1}<input type="hidden" name="c_semana[]" value="${index + 1}">`;
    });
}

function actualizarEstiloModalidad(select) {
    const td = select.closest('td');
    const container = select.nextElementSibling;
    td.classList.toggle('td-sincronico', select.value === 'Sincrónico');
    td.classList.toggle('td-asincronico', select.value !== 'Sincrónico');
    container.classList.toggle('d-none', select.value !== 'Sincrónico');
}

let currentMoodleInput = null;
function abrirConfigMoodle(btn) {
    filaActualMoodle = btn.closest('tr');
    currentMoodleInput = filaActualMoodle.querySelector('.moodle-data-input');
    
    const contenedor = document.getElementById('contenedorActividadesMoodle');
    contenedor.innerHTML = "";
    
    // Simplificado
    const val = currentMoodleInput.value || '[]';
    let acts = [];
    try { acts = JSON.parse(val); } catch(e) {}
    
    if (acts.length === 0) {
        acts.push({ tipo: 'assign', nombre: '', intro: '' });
    }
    
    acts.forEach(act => {
        const div = document.createElement('div');
        div.className = "mb-3 p-3 bg-light rounded-3 text-dark";
        div.innerHTML = `
            <div class="mb-2">
                <label class="form-label small fw-bold text-dark">Tipo de Actividad</label>
                <select class="form-select form-select-sm val-tipo text-dark bg-white">
                    <option value="assign" ${act.tipo === 'assign' ? 'selected' : ''}>📄 Tarea</option>
                    <option value="quiz" ${act.tipo === 'quiz' ? 'selected' : ''}>📝 Quiz / Examen</option>
                    <option value="forum" ${act.tipo === 'forum' ? 'selected' : ''}>💬 Foro</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold text-dark">Título</label>
                <input type="text" class="form-control form-control-sm val-nombre text-dark bg-white" value="${act.nombre || ''}">
            </div>
            <div>
                <label class="form-label small fw-bold text-dark">Instrucciones</label>
                <textarea class="form-control form-control-sm val-intro text-dark bg-white" rows="3">${act.intro || ''}</textarea>
            </div>
        `;
        contenedor.appendChild(div);
    });
    
    new bootstrap.Modal(document.getElementById('modalMoodle')).show();
}

function guardarConfigMoodle() {
    const acts = [];
    document.querySelectorAll('#contenedorActividadesMoodle > div').forEach(div => {
        const t = div.querySelector('.val-tipo').value;
        const n = div.querySelector('.val-nombre').value.trim();
        const d = div.querySelector('.val-intro').value.trim();
        if (n) {
            acts.push({ tipo: t, nombre: n, intro: d });
        }
    });

    currentMoodleInput.value = JSON.stringify(acts);
    
    // Update summary badge
    const summary = filaActualMoodle.querySelector('.moodle-summary');
    summary.innerHTML = "";
    acts.forEach(a => {
        const span = document.createElement('span');
        span.className = `moodle-activity-badge ${a.tipo === 'forum' ? 'badge-forum' : 'badge-assign'}`;
        span.innerHTML = `<i class="bi bi-${a.tipo === 'forum' ? 'chat' : 'file-earmark-arrow-up'}"></i>`;
        summary.appendChild(span);
    });

    // Generate text in display area
    let htmlText = "";
    acts.forEach((a, i) => {
        const color = a.tipo === 'forum' ? '#007bff' : '#dc3545';
        htmlText += `<div style="margin-bottom: 12px; border-left: 4px solid ${color}; padding-left: 8px;">
            <span class="badge bg-warning text-dark mb-1">${a.tipo.toUpperCase()}</span>
            <p class="mb-0 fw-bold text-dark">${a.nombre}</p>
            <p class="mb-0 small text-muted">${a.intro}</p>
        </div>`;
    });
    
    filaActualMoodle.querySelector('.tarea-display-only').innerHTML = htmlText || '<span class="text-muted small italic">Sin actividades.</span>';
    filaActualMoodle.querySelector('textarea[name="c_tareas[]"]').value = htmlText;

    bootstrap.Modal.getInstance(document.getElementById('modalMoodle')).hide();
}

function inicializarResumenesMoodle() {
    document.querySelectorAll('#tablaCronograma tbody tr').forEach(tr => {
        const inp = tr.querySelector('.moodle-data-input');
        if (inp && inp.value) {
            let acts = [];
            try { acts = JSON.parse(inp.value); } catch(e) {}
            const summary = tr.querySelector('.moodle-summary');
            if (summary) {
                acts.forEach(a => {
                    const span = document.createElement('span');
                    span.className = `moodle-activity-badge ${a.tipo === 'forum' ? 'badge-forum' : 'badge-assign'}`;
                    span.innerHTML = `<i class="bi bi-${a.tipo === 'forum' ? 'chat' : 'file-earmark-arrow-up'}"></i>`;
                    summary.appendChild(span);
                });
            }
        }
    });
}

let currentRubricaItem = null;
function abrirModalInyectarHTML(btn) {
    currentRubricaItem = btn.closest('.rubrica-item');
    document.getElementById('htmlRubricaInput').value = "";
    new bootstrap.Modal(document.getElementById('modalInyectarHTML')).show();
}

function procesarHTMLRubrica() {
    const html = document.getElementById('htmlRubricaInput').value;
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const table = doc.querySelector('table');

    if (!table) {
        Swal.fire('Error', 'No se encontró una tabla válida en el HTML.', 'error');
        return;
    }

    const columns = [];
    table.querySelectorAll('thead th, tr:first-child th').forEach((th, i) => {
        if (i > 0) columns.push(th.textContent.trim());
    });

    const rows = [];
    table.querySelectorAll('tbody tr, tr:not(:first-child)').forEach(tr => {
        const cells = tr.querySelectorAll('td, th');
        if (cells.length > 1) {
            const criterio = cells[0].textContent.trim();
            const valores = [];
            for (let i = 1; i < cells.length; i++) {
                valores.push(cells[i].textContent.trim());
            }
            rows.push({ criterio, valores });
        }
    });

    const json = { columnas: columns, filas: rows };
    currentRubricaItem.querySelector('.rubrica-json-input').value = JSON.stringify(json);
    renderizarTablaRubrica(currentRubricaItem);
    sincronizarSilabo();

    bootstrap.Modal.getInstance(document.getElementById('modalInyectarHTML')).hide();
    Swal.fire('Éxito', 'Rúbrica importada desde HTML.', 'success');
}

function abrirGeneradorRubricaIA() {
    // Obtener los rubros declarados por el profesor en la tabla de evaluación
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
                <input type="text" id="ia_actividad" class="form-control shadow-sm d-none" placeholder="Ej: Ensayo de 3 páginas sobre cambio climático">
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

            // Mostrar el prompt generado y el botón para copiar y redirigir
            Swal.fire({
                title: '🤖 Copiar Prompt & Abrir Gemini',
                html: `
                    <p class="small text-muted text-start mb-3">Sigue estos sencillos pasos:</p>
                    <ol class="small text-start mb-4 text-muted">
                        <li>Copia el prompt generado abajo presionando el botón azul.</li>
                        <li>Haz clic en "Ir a Gemini" para abrir el chat de IA en otra pestaña.</li>
                        <li>Pega el prompt, copia la tabla HTML que te responda Gemini y luego pégala en el botón de código de tu rúbrica.</li>
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
                    confirmButton: 'btn btn-primary px-4 rounded-3 me-2',
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
        
        // Calcular los puntos de la actividad basado en la evaluación
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
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    
    Swal.showValidationMessage('¡Prompt Copiado con éxito!');
    setTimeout(() => {
        const msg = document.querySelector('.swal2-validation-message');
        if (msg) msg.style.display = 'none';
    }, 2000);
}

// --- REVISIÓN ACADÉMICA / SUPERVISIÓN ---
let modalRevisionesObj = null;

function abrirModalRevisiones() {
    if (!modalRevisionesObj) {
        modalRevisionesObj = new bootstrap.Modal(document.getElementById('modalRevisiones'));
    }
    cargarRevisiones();
    modalRevisionesObj.show();
}

function cargarRevisiones() {
    const listContainer = document.getElementById('listaRevisiones');
    listContainer.innerHTML = `
        <div class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
            <span class="d-block small">Cargando observaciones...</span>
        </div>`;

    fetch("{{ route('mis_cursos.revisiones.list', $curso->id_curso_activo) }}")
    .then(r => r.json())
    .then(res => {
        if (!res.success) {
            listContainer.innerHTML = `<div class="alert alert-danger small p-2">${res.message}</div>`;
            return;
        }

        if (res.data.length === 0) {
            listContainer.innerHTML = `
                <div class="text-center py-4 p-3 rounded-4 shadow-sm" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="d-inline-flex p-3 rounded-circle bg-success bg-opacity-10 mb-2">
                        <i class="bi bi-emoji-smile-fill text-success fs-2"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">¡Felicidades!</h6>
                    <p class="small text-muted mb-0">No hay observaciones ni sugerencias pendientes para este curso.</p>
                </div>`;
            return;
        }

        let html = '';
        res.data.forEach(item => {
            const esResuelto = (item.estado === 'resuelto');
            const classResuelto = esResuelto ? 'resuelto' : '';
            
            html += `
                <div class="card shadow-sm border-0 rounded-3 revision-item ${classResuelto} p-3 position-relative" style="border-left-width: 5px !important; background-color: #fff;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge rounded-pill bg-light text-dark border px-2 py-1 x-small me-2" style="font-size: 0.7rem;">
                                <i class="bi bi-person me-1"></i> ${item.creador}
                            </span>
                            <span class="badge rounded-pill bg-light text-dark border px-2 py-1 x-small" style="font-size: 0.7rem;">
                                <i class="bi bi-calendar-event me-1"></i> ${item.fecha_creacion}
                            </span>
                        </div>
                        <div>
                            ${esResuelto ? 
                                `<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 border border-success" style="font-size: 0.75rem;"><i class="bi bi-check-circle-fill me-1"></i> Resuelto</span>` : 
                                `<span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-1 border border-warning" style="font-size: 0.75rem;"><i class="bi bi-clock-history me-1"></i> Pendiente</span>`
                            }
                        </div>
                    </div>
                    
                    <p class="mb-3 text-dark small" style="white-space: pre-line; font-size: 0.9rem;">${item.sugerencia}</p>
                    
                    <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                        ${item.captura_pantalla ? `
                            <div class="w-100 mb-2">
                                <a href="${item.captura_pantalla}" target="_blank" class="d-inline-block border rounded p-1 bg-white" style="max-width: 120px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" title="Clic para ampliar captura">
                                    <img src="${item.captura_pantalla}" class="img-fluid rounded" alt="Miniatura de captura" style="max-height: 70px; object-fit: contain;">
                                </a>
                            </div>
                        ` : ''}
                        
                        ${!esResuelto ? `
                            <button type="button" class="btn btn-success btn-xs rounded-pill px-3 ms-auto" style="font-size: 0.75rem;" onclick="resolverRevision(${item.id})">
                                <i class="bi bi-check-lg me-1"></i> Marcar como Solucionado
                            </button>
                        ` : `
                            <div class="ms-auto x-small text-muted" style="font-size: 0.75rem;">
                                <i class="bi bi-check-all text-success"></i> Solucionado por <b>${item.resolutor}</b> el ${item.fecha_resolucion}
                            </div>
                        `}
                        
                        @if ($es_admin)
                            <button type="button" class="btn btn-outline-danger btn-xs rounded-circle p-1 ms-2" style="width: 28px; height: 28px; font-size: 0.75rem; display: inline-flex; align-items: center; justify-content: center;" onclick="eliminarRevision(${item.id})" title="Eliminar Observación">
                                <i class="bi bi-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>
            `;
        });
        listContainer.innerHTML = html;
    });
}

function resolverRevision(id) {
    Swal.fire({
        title: '¿Confirmar Solución?',
        text: '¿Has completado o solucionado la recomendación realizada por el departamento académico?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, solucionado',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('mis_cursos.revisiones.resolver') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(res) {
                    if (res.success) {
                        Swal.fire('Solucionado', res.message, 'success');
                        cargarRevisiones();
                        if (res.todo_resuelto) {
                            setTimeout(() => location.reload(), 1500);
                        }
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }
            });
        }
    });
}

function eliminarRevision(id) {
    Swal.fire({
        title: '¿Eliminar Observación?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('mis_cursos.revisiones.delete') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(res) {
                    if (res.success) {
                        Swal.fire('Eliminado', res.message, 'success');
                        cargarRevisiones();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }
            });
        }
    });
}

$(document).on('submit', '#formNuevaRevision', function(e) {
    e.preventDefault();
    const btn = $('#btnGuardarRevision');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Guardando...');

    const fd = new FormData(this);
    $.ajax({
        url: "{{ route('mis_cursos.revisiones.store', $curso->id_curso_activo) }}",
        type: "POST",
        data: fd,
        contentType: false,
        processData: false,
        success: function(data) {
            btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Guardar Observación');
            if (data.success) {
                Swal.fire('Guardado', data.message, 'success');
                $('#formNuevaRevision')[0].reset();
                cargarRevisiones();
                setTimeout(() => location.reload(), 1500);
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        }
    });
});

@if ($revisiones_pendientes > 0)
document.addEventListener('DOMContentLoaded', function() { abrirModalRevisiones(); });
@endif
</script>

<!-- Botón Flotante de Supervisión Académica / Observaciones -->
<div class="academic-float-btn-container">
    @if ($revisiones_pendientes > 0)
        <button type="button" class="btn btn-danger btn-lg rounded-pill shadow-lg alert-pulse px-4 py-3 d-flex align-items-center gap-2 text-white" onclick="abrirModalRevisiones()" title="Hay observaciones académicas pendientes">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <span class="fw-bold">Observaciones ({{ $revisiones_pendientes }})</span>
        </button>
    @else
        <button type="button" class="btn btn-warning text-dark btn-lg rounded-pill shadow-lg px-4 py-3 d-flex align-items-center gap-2 fw-bold" style="border: none; background-color: #ffc107;" onclick="abrirModalRevisiones()" title="Revisiones y Sugerencias Académicas">
            <i class="bi bi-pencil-square fs-5"></i>
            <span>Revisión Acad.</span>
        </button>
    @endif
</div>

<!-- Modal de Revisiones Académicas -->
<div class="modal fade" id="modalRevisiones" tabindex="-1" aria-labelledby="modalRevisionesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold" id="modalRevisionesLabel">
                    <i class="bi bi-shield-check text-warning me-2"></i> Supervisión Académica / Revisiones del Sílabo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                
                <!-- Alerta informativa -->
                <div class="alert alert-info rounded-3 mb-4 d-flex align-items-center">
                    <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                    <div>
                        <small class="fw-bold">Instrucciones:</small><br>
                        <span class="small">El departamento académico coloca observaciones y capturas para mejorar la calidad del curso. El profesor debe revisar cada punto y marcarlo como resuelto.</span>
                    </div>
                </div>

                <!-- FORMULARIO DE CREACIÓN (SOLO ADMINS) -->
                @if ($es_admin)
                <div class="card border-0 bg-light p-3 rounded-3 mb-4 text-start">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-plus-circle me-1"></i> Nueva Observación o Recomendación</h6>
                    <form id="formNuevaRevision" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Detalle de la Recomendación</label>
                            <textarea name="sugerencia" class="form-control" rows="3" placeholder="Describa el problema o sugerencia..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Adjuntar Captura de Pantalla (Opcional)</label>
                            <input type="file" name="captura" class="form-control" accept="image/*">
                            <div class="form-text x-small">Se permiten imágenes png, jpg, jpeg, gif.</div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4" id="btnGuardarRevision">
                            <i class="bi bi-save me-1"></i> Guardar Observación
                        </button>
                    </form>
                </div>
                @endif

                <!-- LISTADO DE OBSERVACIONES -->
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-task me-1"></i> Lista de Recomendaciones</h6>
                <div id="listaRevisiones" class="d-flex flex-column gap-3">
                    <div class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                        <span class="d-block small">Cargando observaciones...</span>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection
