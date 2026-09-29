@extends('layouts.app')

@section('title', 'Inbox BPM - Generar Boleta de Matrícula')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<style>
    /* Estilos oficiales del sistema de boletas UNELA adaptados a CEFI */
    .boleta-preview {
        max-width: 900px;
        background: #ffffff;
        color: #1e293b;
        border-radius: 1.5rem;
        box-shadow: 0 20px 50px rgba(0,0,0,0.12) !important;
        border: none !important;
        position: relative;
        overflow: hidden;
    }
    .boleta-preview::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 8px;
        background: var(--primary, #5fb230);
    }
    .boleta-header-box {
        background-color: rgba(95, 178, 48, 0.06);
        border: 2px solid rgba(95, 178, 48, 0.15);
        border-radius: 1rem;
        padding: 1.25rem;
    }
    .boleta-section-header {
        background-color: #1e293b !important;
        color: #ffffff !important;
        padding: 0.65rem 1.25rem !important;
        border-radius: 0.5rem;
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        margin-bottom: 1.25rem;
    }
    .table-boleta thead th {
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        padding: 0.85rem 1rem;
    }
    .table-boleta td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #1e293b;
    }
    .signature-area {
        height: 140px;
        background-color: #f8fafc;
        border: 2px dashed #cbd5e0;
        border-radius: 1rem;
        position: relative;
    }
    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 0.85rem !important;
        padding: 0.6rem !important;
        border: 2px solid #e2e8f0 !important;
    }
    .cursor-pointer {
        cursor: pointer;
    }

    /* Botón flotante para preparar boleta */
    #floating-prepare-btn {
        background-color: #5fb230 !important;
        border-color: #5fb230 !important;
        box-shadow: 0 10px 25px rgba(95, 178, 48, 0.4) !important;
        transition: all 0.3s ease;
    }
    #floating-prepare-btn:hover {
        transform: scale(1.05) translateY(-2px);
        box-shadow: 0 15px 30px rgba(95, 178, 48, 0.55) !important;
    }

    /* Adaptación para modo oscuro en el contenedor de pasos */
    [data-bs-theme="dark"] .glass-card {
        background: var(--card-dark, #1e293b);
        border: 1px solid var(--border-dark, #334155);
    }
    [data-bs-theme="dark"] .glass-card .text-dark {
        color: #f8fafc !important;
    }

    @media print {
        body * { visibility: hidden; }
        #boleta-container, #boleta-container * { visibility: visible; }
        #boleta-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .btn, .form-check, .accordion { display: none !important; }
    }
</style>
@endsection

@section('content')
<div class="container mt-4 mb-5">
    <input type="hidden" id="boleta_id_estudiante" value="{{ $id_estudiante_auto }}">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-custom">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('boletas.index') }}">Boletas</a></li>
            <li class="breadcrumb-item active" aria-current="page">Generar Boleta de Matrícula</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-receipt me-2 text-success"></i>Generar Boleta de Matrícula</h2>
            <p class="text-muted mb-0">Emisión de cargos de matrícula, aranceles institucionales y fraccionamiento de mensualidades.</p>
        </div>
        <div>
            <a href="{{ route('boletas.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Volver a la Bandeja
            </a>
        </div>
    </div>

    <!-- PASO 1: BUSCADOR DE ESTUDIANTE -->
    <div class="card glass-card mb-4" id="user-search-card">
        <div class="card-body p-4 p-md-5">
            <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-search me-2 text-primary"></i> 1. Seleccionar Estudiante</h5>
            <div class="row">
                <div class="col-md-12">
                    <label for="user-search" class="form-label fw-bold small text-muted">Estudiante (Nombre, Cédula o Email):</label>
                    <div class="input-group">
                        <select id="user-search" class="form-select"></select>
                        <button id="reset-user-search" class="btn btn-outline-danger border-2 ms-2 rounded-3" type="button" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> Reiniciar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RESUMEN FINANCIERO COMPACTO EN UNA SOLA LÍNEA (IDÉNTICO A UNELA) -->
    <div class="card glass-card mb-4 animate__animated animate__fadeIn" id="student-financial-summary-card" style="display: none;">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <!-- Info Estudiante (Compacto) -->
                <div>
                    <h5 class="fw-bold mb-0 text-dark" id="summary-student-name" style="font-size: 1.05rem;"></h5>
                    <p class="text-muted mb-0" id="summary-student-email" style="font-size: 0.8rem;"></p>
                </div>
                
                <!-- Datos Financieros y Botón (En una sola línea horizontal) -->
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <!-- Deuda Total Compacta -->
                    <div class="px-3 py-1.5 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-10 d-flex align-items-center gap-2">
                        <span class="text-danger fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Deuda:</span>
                        <span class="fw-bold text-danger" id="summary-deuda-total" style="font-size: 0.95rem;">¢0.00</span>
                    </div>
                    
                    <!-- Boletas Pendientes Compacta -->
                    <div class="px-3 py-1.5 rounded-3 bg-warning bg-opacity-10 border border-warning border-opacity-10 d-flex align-items-center gap-2">
                        <span class="text-warning fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Pendientes:</span>
                        <span class="fw-bold text-warning" id="summary-boletas-pendientes" style="font-size: 0.95rem;">0</span>
                    </div>
                    
                    <!-- Botón Ver Boletas Compacto -->
                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-bold" onclick="window.location.href='{{ route('boletas.estado_cuenta') }}?id_estudiante=' + $('#boleta_id_estudiante').val()" style="font-size: 0.75rem;">
                        <i class="bi bi-file-earmark-text me-1"></i> Ver Boletas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- BANNER DE BOLETAS FIRMADAS O PENDIENTES DE OFICIALIZAR -->
    <div id="student-signed-boletas-banner" class="mb-4 animate__animated animate__fadeIn" style="display: none;"></div>

    <!-- PASO 2: PROGRAMAS Y CURSOS -->
    <div class="card glass-card mb-4 animate__animated animate__fadeIn" id="programa-select-card" style="display: none;">
        <div class="card-body p-4 p-md-5">
            <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-journal-check me-2 text-primary"></i> 2. Seleccionar Programa y Cursos</h5>
            
            <div class="mb-4 shadow-sm p-3 border rounded-4 bg-white">
                <label class="form-label fw-bold fs-5 text-dark mb-3"><i class="bi bi-calendar-event me-2 text-primary"></i>Período Lectivo para la Boleta:</label>
                
                <!-- Selector de tipo de período: Automático vs Manual -->
                <div class="btn-group w-100 mb-3" role="group" aria-label="Tipo de Período">
                    <input type="radio" class="btn-check" name="tipo_periodo" id="periodo_auto" value="auto" checked>
                    <label class="btn btn-outline-primary fw-bold py-2" for="periodo_auto">
                        <i class="bi bi-cpu me-1"></i> Cuatrimestre Automático
                    </label>
                    
                    <input type="radio" class="btn-check" name="tipo_periodo" id="periodo_manual" value="manual">
                    <label class="btn btn-outline-primary fw-bold py-2" for="periodo_manual">
                        <i class="bi bi-calendar-range me-1"></i> Rango de Fechas Manual
                    </label>
                </div>

                <!-- Contenedor Automático (Cuatrimestre + Año) -->
                <div id="contenedor-periodo-auto" class="p-3 border rounded-3 bg-light mb-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="select-cuatrimestre" class="form-label small fw-bold text-muted">Cuatrimestre</label>
                            <select id="select-cuatrimestre" class="form-select border-2 rounded-3">
                                <option value="I Cuatrimestre" {{ $cuatrimestre_auto_get == 'I Cuatrimestre' ? 'selected' : '' }}>I Cuatrimestre</option>
                                <option value="II Cuatrimestre" {{ $cuatrimestre_auto_get == 'II Cuatrimestre' ? 'selected' : '' }}>II Cuatrimestre</option>
                                <option value="III Cuatrimestre" {{ $cuatrimestre_auto_get == 'III Cuatrimestre' ? 'selected' : '' }}>III Cuatrimestre</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="select-anio" class="form-label small fw-bold text-muted">Año</label>
                            <select id="select-anio" class="form-select border-2 rounded-3">
                                @php
                                    $curYear = (int)date('Y');
                                    for ($y = $curYear - 2; $y <= $curYear + 5; $y++) {
                                        $selected = ($y == (int)$anio_auto_get) ? 'selected' : '';
                                        echo "<option value=\"$y\" $selected>$y</option>";
                                    }
                                @endphp
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Contenedor Manual (Fecha Inicio + Fecha Fin) -->
                <div id="contenedor-periodo-manual" class="p-3 border rounded-3 bg-light mb-3" style="display: none;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="fecha-inicio" class="form-label small fw-bold text-muted">Fecha de Inicio</label>
                            <input type="date" id="fecha-inicio" class="form-control border-2 rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label for="fecha-fin" class="form-label small fw-bold text-muted">Fecha de Fin</label>
                            <input type="date" id="fecha-fin" class="form-control border-2 rounded-3">
                        </div>
                    </div>
                </div>

                <!-- Campo oculto para guardar el valor final que se envía -->
                <input type="hidden" id="periodo-lectivo-input" value="">
                
                <!-- Vista previa del período generado en tiempo real -->
                <div class="alert alert-secondary py-2 px-3 d-flex align-items-center justify-content-between mb-0 rounded-3">
                    <span class="small fw-bold text-muted"><i class="bi bi-info-circle-fill me-1 text-primary"></i> Período generado:</span>
                    <span id="periodo-preview-badge" class="badge bg-primary px-3 py-2 fs-6">[Generando...]</span>
                </div>
            </div>

            <!-- Acordeón de Programas por Categoría -->
            <div class="accordion" id="programas-accordion">
                @foreach ($programas_agrupados as $categoria => $programas)
                    @php $catId = md5($categoria); @endphp
                    <div class="accordion-item mb-2 border rounded-3 overflow-hidden shadow-sm">
                        <h2 class="accordion-header" id="heading-{{ $catId }}">
                            <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $catId }}">
                                <i class="bi bi-mortarboard me-2 text-success"></i> {{ $categoria }}
                            </button>
                        </h2>
                        <div id="collapse-{{ $catId }}" class="accordion-collapse collapse" data-bs-parent="#programas-accordion">
                            <div class="accordion-body p-3">
                                <div class="list-group">
                                    @foreach ($programas as $programa)
                                        <div class="list-group-item list-group-item-action border rounded-3 mb-2 p-3">
                                            <div class="d-flex w-100 justify-content-between align-items-center flex-wrap gap-2">
                                                <h6 class="mb-0 fw-bold">{{ $programa->nombre_programa }}</h6>
                                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="toggleCursos(this, {{ $programa->id_programa }})">
                                                    <i class="bi bi-chevron-down me-1"></i> Ver Cursos
                                                </button>
                                            </div>
                                            <div class="cursos-container mt-3" id="cursos-programa-{{ $programa->id_programa }}" style="display: none;"></div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 text-center">
                <button id="prepare-boleta-btn" class="btn btn-success btn-lg rounded-pill px-5 py-3 shadow border-0 fw-bold transition-all" style="display: none; background-color: #5fb230; font-size: 1.05rem;">
                    <i class="bi bi-arrow-right-circle me-2 fs-5"></i> Preparar Boleta con Cursos Seleccionados
                </button>
            </div>
        </div>
    </div>

    <!-- PASO 3: FICHA TÉCNICA OFICIAL DE LA BOLETA (PREVISUALIZACIÓN IDÉNTICA A UNELA) -->
    <div id="boleta-container" style="display: none;" class="animate__animated animate__zoomIn">
        <div class="boleta-preview p-4 p-md-5 mx-auto">
            <!-- Encabezado Estilo Oficial -->
            <div class="row border-bottom pb-4 mb-4 align-items-center">
                <div class="col-md-7 text-center text-md-start mb-4 mb-md-0">
                    <img src="{{ asset('imgs/logo.png') }}" onerror="this.onerror=null; this.src='{{ asset('imgs/logo_unela_color.png') }}';" alt="Logo Institucional" style="max-height: 80px;" class="mb-3">
                    <h5 class="fw-bold mb-1" style="font-size: 1.15rem; color: #1e293b;">{{ config('cliente.nombre_legal', config('cliente.nombre', 'CENTRO DE FORMACIÓN INTEGRAL CEFI')) }}</h5>
                    <p class="mb-0 text-muted small">Cédula Jurídica: 3-002-066646</p>
                    <p class="mb-0 text-muted small">Tel: 2211-1200 | www.ceficr.com</p>
                </div>
                <div class="col-md-5">
                    <div class="boleta-header-box text-center">
                        <h4 class="fw-bold text-success mb-2">BOLETA DE MATRÍCULA</h4>
                        <div class="d-flex justify-content-center gap-3 small text-muted">
                            <span><strong>No:</strong> <span id="boleta-numero" class="text-danger fw-bold">[NUEVA]</span></span>
                            <span><strong>Fecha:</strong> {{ date('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Datos Estudiante -->
            <div class="mb-4">
                <div class="boleta-section-header">Datos del Estudiante</div>
                <div class="row g-3 px-2">
                    <div class="col-md-6">
                        <span class="text-muted small fw-bold d-block text-uppercase">Nombre Completo</span>
                        <div id="boleta-nombre-estudiante" class="fw-bold border-bottom pb-1 text-dark"></div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small fw-bold d-block text-uppercase">Cédula / ID</span>
                        <div id="boleta-cedula-estudiante" class="fw-bold border-bottom pb-1 text-dark"></div>
                    </div>
                    <div class="col-12">
                        <span class="text-muted small fw-bold d-block text-uppercase">Carrera / Programa Académico</span>
                        <div id="boleta-carrera" class="fw-bold border-bottom pb-1 text-dark"></div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small fw-bold d-block text-uppercase">Correo Electrónico</span>
                        <div id="boleta-email-estudiante" class="fw-bold border-bottom pb-1 text-dark"></div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small fw-bold d-block text-uppercase">Período Lectivo</span>
                        <div id="boleta-cuatrimestre" class="fw-bold text-primary"></div>
                    </div>
                </div>
            </div>

            <!-- Detalle de Cargos -->
            <div class="mb-4">
                <div class="boleta-section-header">Detalle de Matrícula y Cursos</div>
                <div class="table-responsive">
                    <table class="table table-boleta mb-0">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Código</th>
                                <th style="width: 55%;">Descripción de Cargo</th>
                                <th style="width: 30%;" class="text-end pe-4">Monto</th>
                            </tr>
                        </thead>
                        <tbody id="boleta-detalle-cargos">
                            <!-- Dinámico vía JS -->
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="2" class="text-end fw-bold py-3">SUBTOTAL</td>
                                <td class="text-end fw-bold pe-4 py-3" id="boleta-subtotal">¢0.00</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-end text-success fw-bold py-3">DESCUENTO ESPECIAL</td>
                                <td class="text-end pe-4 py-2">
                                    <div class="input-group input-group-sm ms-auto" style="max-width: 160px;">
                                        <span class="input-group-text bg-white border-success text-success fw-bold">¢</span>
                                        <input type="number" id="boleta-descuento" class="form-control border-success text-success fw-bold text-end" value="0" min="0" step="500">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-end fw-bold h5 py-3 mb-0">TOTAL MATRÍCULA</td>
                                <td class="text-end fw-bold h5 text-primary pe-4 py-3 mb-0" id="boleta-total">¢0.00</td>
                            </tr>
                            <tr id="preview-row-abono" style="display: none;">
                                <td colspan="2" class="text-end text-danger fw-bold py-2">ABONO / PAGO INICIAL</td>
                                <td class="text-end text-danger fw-bold pe-4 py-2" id="boleta-preview-abono">-¢0.00</td>
                            </tr>
                            <tr id="preview-row-saldo" class="table-success border-top border-success border-2" style="display: none;">
                                <td colspan="2" class="text-end fw-bold h5 py-3">SALDO PENDIENTE A FINANCIAR</td>
                                <td class="text-end fw-bold h5 text-success pe-4 py-3" id="boleta-preview-saldo">¢0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Opciones Financieras y Proyección -->
            <div class="row mb-4 g-4">
                <div class="col-md-6">
                    <div class="p-3 rounded-4 border-2 border-dashed border-primary bg-primary bg-opacity-10 h-100">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-gear-fill me-1"></i> Opciones de Cargo y Pago</h6>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="cobrar-inscripcion">
                            <label class="form-check-label fw-bold small text-dark" for="cobrar-inscripcion">Incluir Inscripción Única (Nuevo Ingreso)</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="cobrar-biblioteca">
                            <label class="form-check-label fw-bold small text-dark" for="cobrar-biblioteca">Incluir Uso de Biblioteca Virtual</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="cobrar-matricula" checked>
                            <label class="form-check-label fw-bold small text-danger" for="cobrar-matricula">Cobrar Matrícula de Período</label>
                        </div>
                        
                        <label class="small fw-bold text-muted mb-2 d-block">Plan de Fraccionamiento:</label>
                        <select id="boleta-cuotas" class="form-select form-select-sm fw-bold rounded-3">
                            <option value="1">Pago Único (100% de contado)</option>
                            <option value="2">2 Cuotas (50% c/u)</option>
                            <option value="3">3 Cuotas (33.3% c/u)</option>
                            <option value="4" selected>4 Cuotas (Mensualidades)</option>
                        </select>

                        <label class="small fw-bold text-muted mt-3 mb-2 d-block">Pago Inicial (Matrícula / Abono en ventanilla):</label>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-white border-2 border-end-0 text-muted fw-bold">¢</span>
                            <input type="number" id="boleta-pago-inicial" class="form-control form-control-sm border-2 fw-bold" 
                                   value="0" min="0" step="500">
                        </div>

                        <div id="pago-inicial-detalles-container" class="mt-2" style="display: none;">
                            <label class="small fw-bold text-muted mb-2 d-block">Método del Pago Inicial:</label>
                            <select id="boleta-pago-inicial-metodo" class="form-select form-select-sm fw-bold rounded-3 mb-2">
                                <option value="Sinpe Móvil">Sinpe Móvil</option>
                                <option value="Transferencia">Transferencia Bancaria</option>
                                <option value="Efectivo" selected>Efectivo</option>
                                <option value="Tarjeta">Tarjeta de Crédito / Débito</option>
                            </select>
                            <label class="small fw-bold text-muted mb-2 d-block">Referencia / Comprobante:</label>
                            <input type="text" id="boleta-pago-inicial-referencia" class="form-control form-control-sm border-2 rounded-3" placeholder="Opcional: No. Comprobante">
                        </div>

                        <div id="fechas-cuotas-container" class="mt-3"></div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 rounded-4 border bg-light h-100 d-flex flex-column justify-content-between">
                        <div>
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-graph-up me-1 text-primary"></i> Proyección de Mensualidad</h6>
                            <div class="d-flex align-items-center justify-content-between p-3 bg-white rounded-3 border">
                                <span class="text-muted small fw-bold">Monto estimado por cuota:</span>
                                <span class="h4 fw-bold text-success mb-0" id="boleta-mensualidad">¢0.00</span>
                            </div>
                            <p class="small text-muted mt-3 mb-0" id="boleta-mensualidad-exp">
                                <i class="bi bi-info-circle me-1"></i> Basado en un plan de 4 mensualidades durante el período lectivo.
                            </p>
                        </div>

                        <!-- Canvas Opcional para Firma en Ventanilla -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="small fw-bold text-muted mb-0"><i class="bi bi-pen me-1"></i>Firma Manuscrita en Pantalla (Opcional):</label>
                                <button type="button" id="clear-signature-btn" class="btn btn-outline-danger btn-sm py-0 px-2 rounded-pill" style="font-size: 0.75rem;">
                                    <i class="bi bi-eraser"></i> Limpiar
                                </button>
                            </div>
                            <div class="signature-area position-relative">
                                <canvas id="signature-canvas" class="w-100 h-100 rounded-3"></canvas>
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem;">Si el estudiante está en ventanilla, puede firmar aquí directamente.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notas Legales e Información de Firma Digital -->
            <div class="row align-items-end g-4">
                <div class="col-12 text-start">
                    <div class="p-3 bg-light rounded-4 small text-muted border">
                        <div class="d-flex align-items-center gap-2 mb-2 text-primary fw-bold">
                            <i class="bi bi-shield-lock-fill fs-5"></i>
                            <span>PROCESO DE FIRMA DIGITAL Y OFICIALIZACIÓN:</span>
                        </div>
                        <ul class="mb-0 ps-3">
                            <li>Al presionar <strong>"Enviar al Estudiante para Firma"</strong>, se enviará un enlace privado y seguro al correo del estudiante.</li>
                            <li>El estudiante revisará el desglose y estampará su firma manuscrita digital desde su dispositivo móvil o computadora.</li>
                            <li>Si se encuentra en ventanilla, puede firmar en pantalla y hacer clic en <strong>"Guardar y Oficializar Directamente"</strong>.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción (Enviar / Oficializar / Volver) -->
        <div class="row mt-4 g-3 justify-content-center">
            <div class="col-md-5">
                <button type="button" id="send-boleta-link" class="btn btn-warning text-dark btn-lg w-100 shadow fw-bold rounded-pill" style="background-color: #f39c12; border-color: #f39c12; font-size: 1.05rem;">
                    <i class="bi bi-send-fill me-2 fs-5"></i> Enviar al Estudiante para Firma
                </button>
            </div>
            <div class="col-md-4">
                <button type="button" id="direct-officialize-btn" class="btn btn-success btn-lg w-100 shadow fw-bold rounded-pill" style="background-color: #5fb230; border-color: #5fb230; font-size: 1.05rem;">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i> Oficializar Directamente
                </button>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-outline-secondary btn-lg w-100 rounded-pill" onclick="goBackOrReset()">
                    <i class="bi bi-arrow-left me-2"></i> Volver
                </button>
            </div>
        </div>
    </div>

    <!-- Botón Flotante para Preparar Boleta (Móvil y Escritorio) -->
    <div id="floating-prepare-container" style="position: fixed; bottom: 2rem; right: 2rem; z-index: 1050; display: none;">
        <button id="floating-prepare-btn" class="btn btn-success btn-lg rounded-pill px-4 py-3 shadow-lg border-0 d-flex align-items-center gap-2 animate__animated animate__bounceIn">
            <i class="bi bi-arrow-right-circle-fill fs-4"></i>
            <span class="fw-bold">Preparar Boleta</span>
            <span class="badge bg-white text-success rounded-circle px-2 py-1" id="floating-badge-count" style="font-size: 0.85rem;">0</span>
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const autoEstudiante = {{ $id_estudiante_auto }};
    const autoEstudianteNombre = @json($student_name_auto);
    const autoEstudianteEmail = @json($student_email_auto);
    const autoPlan = {{ $id_plan_auto }};
    const autoPrograma = {{ $id_programa_auto }};
    const autoCategoria = "{{ md5($categoria_auto) }}";
    const autoCuatrimestre = @json($cuatrimestre_auto_get);
    const autoAnio = @json($anio_auto_get);
    const idCursoRetorno = {{ $id_curso_retorno }};

    function goBackOrReset() {
        if (idCursoRetorno > 0) {
            window.location.href = '{{ route('cursos.index') }}';
        } else {
            resetToProgramSelection();
        }
    }

    let preloadedPlanIds = [];
    let pagoInicialManual = false;
    let dataActual = null;
    const currencyFormatter = new Intl.NumberFormat('es-CR', { style: 'currency', currency: 'CRC' });

    function detectAndAutoLoadCursos() {
        const id_estudiante = $('#boleta_id_estudiante').val();
        const periodo = $('#periodo-lectivo-input').val();
        
        if (!id_estudiante || !periodo || periodo === 'Seleccione ambas fechas' || periodo === 'Rango de fechas inválido') {
            return;
        }
        
        $.ajax({
            url: '{{ route('boletas.matriculas_pendientes_ajax') }}',
            type: 'GET',
            data: { id_estudiante: id_estudiante, periodo: periodo },
            dataType: 'json',
            success: function(res) {
                if (res.success && res.plan_ids && res.plan_ids.length > 0) {
                    preloadedPlanIds = res.plan_ids;
                    
                    Swal.fire({
                        title: 'Cursos Detectados',
                        text: `Se detectaron ${res.plan_ids.length} curso(s) matriculado(s) sin facturar para este período. Cargando boleta...`,
                        icon: 'info',
                        timer: 2000,
                        showConfirmButton: false,
                        position: 'top-end',
                        toast: true
                    });
                    
                    loadBoletaData(res.plan_ids, periodo);
                } else {
                    preloadedPlanIds = [];
                }
            }
        });
    }

    $(document).ready(function() {
        // Inicializar Select2 para búsqueda de estudiantes
        $('#user-search').select2({
            theme: 'bootstrap-5',
            placeholder: 'Busque un estudiante por nombre, cédula o email',
            ajax: {
                url: '{{ route('boletas.buscar_estudiante_ajax') }}',
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data.results }),
                cache: true
            },
            minimumInputLength: 2
        }).on('select2:select', e => selectUser(e.params.data.id));

        // Listener para checkboxes de cursos
        $(document).on('change', '.curso-checkbox', function() {
            const count = $('.curso-checkbox:checked').length;
            if (count > 0) {
                $('#prepare-boleta-btn').fadeIn();
                $('#floating-prepare-container').fadeIn();
                $('#floating-badge-count').text(count);
            } else {
                $('#prepare-boleta-btn').fadeOut();
                $('#floating-prepare-container').fadeOut();
            }
        });

        // Botón flotante
        $('#floating-prepare-btn').click(function() {
            $('#prepare-boleta-btn').trigger('click');
        });

        // Actualizar período lectivo
        function actualizarPeriodoLectivo() {
            const tipo = $('input[name="tipo_periodo"]:checked').val();
            let periodo = '';
            
            if (tipo === 'auto') {
                const cuatrimestre = $('#select-cuatrimestre').val();
                const anio = $('#select-anio').val();
                periodo = `${cuatrimestre} ${anio}`;
            } else {
                const fInicio = $('#fecha-inicio').val();
                const fFin = $('#fecha-fin').val();
                
                if (fInicio && fFin) {
                    const partsInicio = fInicio.split('-');
                    const partsFin = fFin.split('-');
                    if (partsInicio.length === 3 && partsFin.length === 3) {
                        const fInicioFormateada = `${partsInicio[2]}/${partsInicio[1]}/${partsInicio[0]}`;
                        const fFinFormateada = `${partsFin[2]}/${partsFin[1]}/${partsFin[0]}`;
                        periodo = `Del ${fInicioFormateada} al ${fFinFormateada}`;
                    } else {
                        periodo = 'Rango de fechas inválido';
                    }
                } else {
                    periodo = 'Seleccione ambas fechas';
                }
            }
            
            $('#periodo-lectivo-input').val(periodo);
            $('#periodo-preview-badge').text(periodo);
        }

        // Toggle período
        $('input[name="tipo_periodo"]').change(function() {
            const tipo = $(this).val();
            if (tipo === 'auto') {
                $('#contenedor-periodo-auto').slideDown();
                $('#contenedor-periodo-manual').slideUp();
            } else {
                $('#contenedor-periodo-auto').slideUp();
                $('#contenedor-periodo-manual').slideDown();
            }
            actualizarPeriodoLectivo();
        });

        $('#select-cuatrimestre, #select-anio, #fecha-inicio, #fecha-fin').on('change input', function() {
            actualizarPeriodoLectivo();
            if ($('#boleta_id_estudiante').val()) {
                detectAndAutoLoadCursos();
            }
        });

        actualizarPeriodoLectivo();

        // Botón de Preparar Boleta
        $('#prepare-boleta-btn').click(function() {
            const selectedCursos = $('.curso-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            
            if (selectedCursos.length === 0) {
                Swal.fire('Atención', 'Debe seleccionar al menos un curso para generar la boleta.', 'warning');
                return;
            }
            
            const tipoPeriodo = $('input[name="tipo_periodo"]:checked').val();
            if (tipoPeriodo === 'manual') {
                const fInicio = $('#fecha-inicio').val();
                const fFin = $('#fecha-fin').val();
                if (!fInicio || !fFin) {
                    Swal.fire('Atención', 'Debe especificar la Fecha de Inicio y de Fin para el período manual.', 'warning');
                    return;
                }
            }
            
            const periodo = $('#periodo-lectivo-input').val();
            if (!periodo || periodo === 'Seleccione ambas fechas' || periodo === 'Rango de fechas inválido') {
                Swal.fire('Atención', 'Por favor complete correctamente el Período Lectivo.', 'warning');
                return;
            }

            loadBoletaData(selectedCursos, periodo);
        });

        // Precargar si viene de parámetros GET
        if (autoEstudiante > 0) {
            const text = `${autoEstudianteNombre} (${autoEstudianteEmail})`;
            const newOption = new Option(text, autoEstudiante, true, true);
            $('#user-search').append(newOption).trigger('change');
            selectUser(autoEstudiante);
        }

        if (autoPlan > 0) {
            const accordionBtn = $(`#heading-${autoCategoria} button`);
            if (accordionBtn.hasClass('collapsed')) {
                accordionBtn.trigger('click');
            }
            
            setTimeout(() => {
                const verCursosBtn = $(`button[onclick*="toggleCursos(this, ${autoPrograma})"]`);
                if (verCursosBtn.length > 0) {
                    verCursosBtn.trigger('click');
                    
                    let checkExist = setInterval(() => {
                        const checkbox = $(`#curso-${autoPlan}`);
                        if (checkbox.length > 0) {
                            checkbox.prop('checked', true).trigger('change');
                            clearInterval(checkExist);
                            
                            setTimeout(() => {
                                $('html, body').animate({
                                    scrollTop: $("#prepare-boleta-btn").offset().top - 200
                                }, 500);
                            }, 200);
                        }
                    }, 100);
                }
            }, 300);
        }

        // Lógica de Canvas para Firma Manuscrita
        initSignatureCanvas();
    });

    function selectUser(userId) {
        pagoInicialManual = false;
        $('#boleta-pago-inicial').val(0);
        $('#boleta_id_estudiante').val(userId);
        $('#user-search-card').hide();
        $('#student-financial-summary-card').show();
        $('#programa-select-card').show();
        
        fetch(`{{ url('boletas/estado-cuenta') }}/${userId}/ajax?_t=` + Date.now())
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    const d = result.data;
                    $('#summary-student-name').text(`${d.estudiante.nombre} ${d.estudiante.apellidos || ''}`);
                    $('#summary-student-email').text(d.estudiante.email);
                    $('#summary-deuda-total').text(currencyFormatter.format(d.resumen.deuda_total));
                    $('#summary-boletas-pendientes').text(d.boletas.filter(b => b.estado !== 'pagada' && b.estado !== 'anulada').length);
                    
                    const boletasFirmadas = d.boletas.filter(b => b.estado === 'firmada');
                    const boletasPendFirma = d.boletas.filter(b => b.estado === 'pendiente_firma');
                    
                    let bannersHtml = '';
                    if (boletasFirmadas.length > 0) {
                        boletasFirmadas.forEach(bf => {
                            bannersHtml += `
                            <div class="alert alert-success d-flex flex-wrap align-items-center justify-content-between p-3 rounded-4 shadow-sm border-2 border-success mb-2">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-pen-fill fs-2 text-success"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-success">¡Boleta Firmada por el Estudiante! (${bf.numero_boleta})</h6>
                                        <p class="mb-0 text-muted small">El estudiante ya estampó su firma digital. Haga clic para revisar y oficializar.</p>
                                    </div>
                                </div>
                                <a href="{{ url('boletas') }}/${bf.id}/procesar" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm mt-2 mt-md-0">
                                    <i class="bi bi-file-earmark-check-fill me-1"></i> Procesar y Oficializar
                                </a>
                            </div>`;
                        });
                    }
                    if (boletasPendFirma.length > 0) {
                        boletasPendFirma.forEach(bpf => {
                            bannersHtml += `
                            <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between p-3 rounded-4 shadow-sm border-2 border-warning mb-2">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-hourglass-split fs-2 text-warning"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">Boleta Pendiente de Firma (${bpf.numero_boleta})</h6>
                                        <p class="mb-0 text-muted small">Enviada al correo del estudiante a la espera de firma.</p>
                                    </div>
                                </div>
                                <a href="{{ url('boletas') }}/${bpf.id}/procesar" class="btn btn-outline-dark rounded-pill px-3 btn-sm fw-bold mt-2 mt-md-0">
                                    <i class="bi bi-eye me-1"></i> Ver Detalles
                                </a>
                            </div>`;
                        });
                    }

                    if (bannersHtml !== '') {
                        $('#student-signed-boletas-banner').html(bannersHtml).show();
                    } else {
                        $('#student-signed-boletas-banner').empty().hide();
                    }

                    detectAndAutoLoadCursos();
                }
            });
    }

    function toggleCursos(btn, id_programa) {
        const container = $(`#cursos-programa-${id_programa}`);
        const button = $(btn);

        if (container.is(':visible')) {
            container.slideUp();
            button.html('<i class="bi bi-chevron-down me-1"></i> Ver Cursos');
        } else {
            container.html('<div class="text-center p-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>').slideDown();
            button.html('<i class="bi bi-chevron-up me-1"></i> Ocultar Cursos');
            
            $.ajax({
                url: `{{ url('boletas/cursos-programa') }}/${id_programa}/ajax`,
                type: 'GET',
                cache: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.cursos) {
                        let content = '';
                        for (const cuatrimestre in response.cursos) {
                            content += `<h6 class="fw-bold text-muted mt-3 mb-2">${cuatrimestre}</h6>`;
                            content += '<ul class="list-group mb-2">';
                            response.cursos[cuatrimestre].forEach(curso => {
                                const isChecked = preloadedPlanIds.includes(parseInt(curso.id_plan)) ? 'checked' : '';
                                content += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input curso-checkbox" type="checkbox" value="${curso.id_plan}" id="curso-${curso.id_plan}" ${isChecked}>
                                        <label class="form-check-label cursor-pointer text-dark" for="curso-${curso.id_plan}">
                                            <strong>${curso.codigo}</strong> - ${curso.materia}
                                        </label>
                                    </div>
                                    <span class="badge bg-success-subtle text-success fs-6 fw-bold border border-success-subtle">${currencyFormatter.format(curso.precio)}</span>
                                </li>`;
                            });
                            content += '</ul>';
                        }
                        container.html(content);
                    } else {
                        container.html('<p class="text-danger small">No se pudieron cargar los cursos.</p>');
                    }
                }
            });
        }
    }

    function loadBoletaData(selectedCursos, periodo) {
        const id_estudiante = $('#boleta_id_estudiante').val();
        $.ajax({
            url: '{{ route('boletas.datos_ajax') }}',
            type: 'POST',
            data: { 
                _token: '{{ csrf_token() }}',
                id_estudiante: id_estudiante, 
                cursos_ids: selectedCursos
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    dataActual = response.data;
                    const u = response.data.usuario;
                    
                    if (response.data.es_primera_boleta) {
                        $('#cobrar-inscripcion').prop('checked', true);
                        $('#cobrar-biblioteca').prop('checked', true);
                        Swal.fire({
                            title: 'Estudiante de Nuevo Ingreso',
                            html: `Se han activado automáticamente los aranceles de <b>Inscripción Única</b> y <b>Biblioteca</b>.`,
                            icon: 'info',
                            confirmButtonColor: '#5fb230'
                        });
                    } else {
                        $('#cobrar-inscripcion').prop('checked', false);
                        $('#cobrar-biblioteca').prop('checked', false);
                    }

                    $('#boleta-nombre-estudiante').text(`${u.nombre} ${u.apellidos || ''}`);
                    $('#boleta-cedula-estudiante').text(u.cedula || 'N/A');
                    $('#boleta-email-estudiante').text(u.email);
                    $('#boleta-cuatrimestre').text(periodo);
                    
                    renderBoletaDetalle();
                    updateFechasCuotasInputs();
                    $('#programa-select-card').hide();
                    $('#floating-prepare-container').hide();
                    $('#boleta-container').show();
                    if (typeof resizeCanvas === 'function') resizeCanvas();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        });
    }

    function renderBoletaDetalle() {
        $('#boleta-detalle-cargos').empty();
        let costoCursosBase = 0;
        let valorMatricula = 0;
        let valorBiblioteca = 0;
        let valorInscripcion = 0;
        let programasNombres = new Set();

        if (dataActual && dataActual.cursos.length > 0) {
            dataActual.cursos.forEach(curso => {
                programasNombres.add(curso.nombre_programa);
                if ($('#cobrar-matricula').is(':checked')) {
                    const vm = parseFloat(curso.costo_matricula) || 0;
                    if (vm > valorMatricula) valorMatricula = vm;
                }
                if ($('#cobrar-biblioteca').is(':checked')) {
                    const vb = parseFloat(curso.costo_biblioteca) || 0;
                    if (vb > valorBiblioteca) valorBiblioteca = vb;
                }
                if ($('#cobrar-inscripcion').is(':checked')) {
                    const vi = parseFloat(curso.costo_inscripcion_unica) || 0;
                    if (vi > valorInscripcion) valorInscripcion = vi;
                }
                
                const p = parseFloat(curso.precio) || 0;
                costoCursosBase += p;
                $('#boleta-detalle-cargos').append(`<tr><td class="ps-4 fw-bold text-primary">${curso.codigo}</td><td>${curso.materia}</td><td class="text-end pe-4 fw-bold">${currencyFormatter.format(p)}</td></tr>`);
            });
            
            $('#boleta-carrera').text(Array.from(programasNombres).join(', '));
            if ($('#cobrar-biblioteca').is(':checked') && valorBiblioteca === 0) valorBiblioteca = 5000;
            if ($('#cobrar-inscripcion').is(':checked') && valorInscripcion === 0) valorInscripcion = 8000;
        }

        let currentSubtotal = costoCursosBase;

        // Se agregan en orden inverso con prepend para que queden arriba: INS-01, BIB-01, ADM-01
        if (valorMatricula > 0) {
            $('#boleta-detalle-cargos').prepend(`<tr><td class="ps-4 fw-bold text-primary">ADM-01</td><td>MATRÍCULA DEL PERÍODO</td><td class="text-end pe-4 fw-bold">${currencyFormatter.format(valorMatricula)}</td></tr>`);
            currentSubtotal += valorMatricula;
        }

        if (valorBiblioteca > 0) {
            $('#boleta-detalle-cargos').prepend(`<tr><td class="ps-4 fw-bold text-primary">BIB-01</td><td>USO DE BIBLIOTECA VIRTUAL</td><td class="text-end pe-4 fw-bold">${currencyFormatter.format(valorBiblioteca)}</td></tr>`);
            currentSubtotal += valorBiblioteca;
        }

        if (valorInscripcion > 0) {
            $('#boleta-detalle-cargos').prepend(`<tr><td class="ps-4 fw-bold text-primary">INS-01</td><td>INSCRIPCIÓN ÚNICA</td><td class="text-end pe-4 fw-bold">${currencyFormatter.format(valorInscripcion)}</td></tr>`);
            currentSubtotal += valorInscripcion;
        }

        recalculateTotal(currentSubtotal);
    }

    function recalculateTotal(subtotal) {
        const desc = parseFloat($('#boleta-descuento').val()) || 0;
        const total = Math.max(0, subtotal - desc);
        $('#boleta-subtotal').text(currencyFormatter.format(subtotal));
        $('#boleta-total').text(currencyFormatter.format(total));
        
        const pagoInicial = parseFloat($('#boleta-pago-inicial').val()) || 0;
        if (pagoInicial > 0) {
            $('#pago-inicial-detalles-container').slideDown();
            $('#preview-row-abono').show();
            $('#boleta-preview-abono').text('-' + currencyFormatter.format(pagoInicial));
            $('#preview-row-saldo').show();
            const saldoFinanciado = Math.max(0, total - pagoInicial);
            $('#boleta-preview-saldo').text(currencyFormatter.format(saldoFinanciado));
        } else {
            $('#pago-inicial-detalles-container').slideUp();
            $('#preview-row-abono').hide();
            $('#preview-row-saldo').hide();
        }
        const totalFinanciado = Math.max(0, total - pagoInicial);
        
        const cuotas = parseInt($('#boleta-cuotas').val()) || 1;
        const mensualidadMonto = totalFinanciado / cuotas;
        
        $('#boleta-mensualidad').text(currencyFormatter.format(mensualidadMonto));
        
        const expText = pagoInicial > 0 
            ? `<i class="bi bi-info-circle me-1"></i> Basado en un pago inicial de ¢${pagoInicial.toLocaleString('es-CR')} hoy y ${cuotas} mensualidades de ¢${mensualidadMonto.toLocaleString('es-CR')}.`
            : `<i class="bi bi-info-circle me-1"></i> Basado en un plan de ${cuotas} mensualidades durante el período lectivo.`;
        $('#boleta-mensualidad-exp').html(expText);
    }

    $(document).on('change', '#cobrar-inscripcion, #cobrar-biblioteca, #cobrar-matricula, #boleta-cuotas', renderBoletaDetalle);
    $('#boleta-descuento').on('input', renderBoletaDetalle);
    $('#boleta-pago-inicial').on('input change', function() {
        pagoInicialManual = true;
        renderBoletaDetalle();
    });

    function addMonthsToDate(dateObj, months) {
        const d = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate());
        const originalDay = dateObj.getDate();
        d.setMonth(d.getMonth() + months);
        if (d.getDate() !== originalDay) {
            d.setDate(0);
        }
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function getDefaultDueDate(index, baseDate = null) {
        const base = baseDate ? new Date(baseDate.getFullYear(), baseDate.getMonth(), baseDate.getDate()) : new Date();
        return addMonthsToDate(base, index - 1);
    }

    function updateFechasCuotasInputs() {
        const cuotas = parseInt($('#boleta-cuotas').val()) || 1;
        const container = $('#fechas-cuotas-container');
        
        let baseDate = new Date();
        const existingP1 = $('#fecha-cuota-1').val();
        if (existingP1) {
            const p = existingP1.split('-');
            if (p.length === 3) {
                baseDate = new Date(parseInt(p[0]), parseInt(p[1]) - 1, parseInt(p[2]));
            }
        }
        
        container.empty();
        container.append('<label class="small fw-bold text-muted mb-2 d-block"><i class="bi bi-calendar3 me-1 text-primary"></i>Fechas de Vencimiento de Cuotas:</label>');
        for (let i = 1; i <= cuotas; i++) {
            const defaultDate = getDefaultDueDate(i, baseDate);
            container.append(`
                <div class="mb-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-2 border-end-0 text-muted fw-bold" style="font-size: 0.75rem;">Pago #${i}</span>
                        <input type="date" class="form-control form-control-sm border-2 fecha-cuota-input" 
                               id="fecha-cuota-${i}" data-index="${i}" value="${defaultDate}" 
                               style="font-size: 0.8rem; font-weight: bold;" required>
                    </div>
                </div>
            `);
        }
    }

    $(document).on('change', '#boleta-cuotas', updateFechasCuotasInputs);

    $(document).on('change input', '.fecha-cuota-input', function() {
        const changedIndex = parseInt($(this).data('index'));
        const changedVal = $(this).val();
        if (!changedVal) return;
        
        const parts = changedVal.split('-');
        if (parts.length === 3) {
            const baseDate = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
            const cuotas = parseInt($('#boleta-cuotas').val()) || 1;
            
            for (let i = changedIndex + 1; i <= cuotas; i++) {
                const monthsAhead = i - changedIndex;
                const newDateStr = addMonthsToDate(baseDate, monthsAhead);
                $(`#fecha-cuota-${i}`).val(newDateStr);
            }
        }
    });

    // Acción 1: Enviar al Estudiante para Firma Digital
    $('#send-boleta-link').click(function() {
        const btn = $(this);
        let datesValid = true;
        const fechas_vencimiento = [];
        $('.fecha-cuota-input').each(function() {
            const val = $(this).val();
            if (!val) datesValid = false;
            fechas_vencimiento.push(val);
        });
        if (!datesValid) {
            Swal.fire('Atención', 'Por favor complete todas las fechas de vencimiento de las cuotas.', 'warning');
            return;
        }

        const cursos_seleccionados = (dataActual && dataActual.cursos) ? dataActual.cursos.map(c => c.id_plan) : [];
        const studentId = (dataActual && dataActual.usuario ? dataActual.usuario.id : $('#boleta_id_estudiante').val()) || autoEstudiante;

        if (!studentId) {
            Swal.fire('Atención', 'No se ha detectado el estudiante seleccionado.', 'warning');
            return;
        }

        if (cursos_seleccionados.length === 0) {
            Swal.fire('Atención', 'Debe seleccionar al menos un curso para generar la boleta.', 'warning');
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Enviando al Estudiante...');

        $.ajax({
            url: '{{ route('boletas.enviar_enlace_firma') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id_estudiante: studentId,
                total: $('#boleta-total').text(),
                periodo: $('#boleta-cuatrimestre').text(),
                descuento: $('#boleta-descuento').val(),
                cuotas: $('#boleta-cuotas').val(),
                cobrar_inscripcion: $('#cobrar-inscripcion').is(':checked') ? 1 : 0,
                cobrar_biblioteca: $('#cobrar-biblioteca').is(':checked') ? 1 : 0,
                cobrar_matricula: $('#cobrar-matricula').is(':checked') ? 1 : 0,
                fechas_vencimiento: fechas_vencimiento,
                cursos_ids: cursos_seleccionados
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        title: '¡Boleta Enviada para Firma!',
                        html: `Se ha generado el borrador de matrícula y el enlace de firma.<br><br>
                               <div class="p-3 bg-light rounded-3 border text-start mb-3">
                                   <label class="small text-muted fw-bold d-block mb-1">Enlace de firma digital para el estudiante:</label>
                                   <div class="input-group input-group-sm">
                                       <input type="text" id="swal_link_firma" class="form-control" value="${res.enlace_firma}" readonly>
                                       <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('swal_link_firma').value); this.innerHTML='¡Copiado!';">Copiar</button>
                                   </div>
                               </div>
                               <span class="text-muted small">Una vez que el estudiante estampe su firma digital, aparecerá en su <b>Bandeja de Boletas</b> para procesarla y oficializarla.</span>`,
                        icon: 'success',
                        confirmButtonColor: '#5fb230',
                        confirmButtonText: '<i class="bi bi-receipt me-1"></i> Ir a Bandeja de Boletas'
                    }).then(() => {
                        window.location.href = '{{ route('boletas.index') }}';
                    });
                } else {
                    Swal.fire('Error', res.message || 'Error al enviar la boleta.', 'error');
                    btn.prop('disabled', false).html('<i class="bi bi-send-fill me-2 fs-5"></i> Enviar al Estudiante para Firma');
                }
            },
            error: function(xhr) {
                let msg = 'Ocurrió un error al enviar la boleta al estudiante.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Error', msg, 'error');
                btn.prop('disabled', false).html('<i class="bi bi-send-fill me-2 fs-5"></i> Enviar al Estudiante para Firma');
            }
        });
    });

    // Acción 2: Guardar y Oficializar Directamente (Atención en ventanilla)
    $('#direct-officialize-btn').click(function() {
        const btn = $(this);
        let datesValid = true;
        const fechas_vencimiento = [];
        $('.fecha-cuota-input').each(function() {
            const val = $(this).val();
            if (!val) datesValid = false;
            fechas_vencimiento.push(val);
        });
        if (!datesValid) {
            Swal.fire('Atención', 'Por favor complete todas las fechas de vencimiento de las cuotas.', 'warning');
            return;
        }

        const cursos_seleccionados = (dataActual && dataActual.cursos) ? dataActual.cursos.map(c => c.id_plan) : [];
        const studentId = (dataActual && dataActual.usuario ? dataActual.usuario.id : $('#boleta_id_estudiante').val()) || autoEstudiante;

        if (!studentId) {
            Swal.fire('Atención', 'No se ha detectado el estudiante seleccionado.', 'warning');
            return;
        }

        if (cursos_seleccionados.length === 0) {
            Swal.fire('Atención', 'Debe seleccionar al menos un curso para generar la boleta.', 'warning');
            return;
        }

        const signatureBase64 = window.isCanvasEmpty && !window.isCanvasEmpty() ? document.getElementById('signature-canvas').toDataURL() : '';

        Swal.fire({
            title: '¿Confirmar Oficialización Directa?',
            text: 'La boleta será emitida como documento oficial con su PDF generado de inmediato.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5fb230',
            confirmButtonText: 'Sí, Oficializar Ahora',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Oficializando...');

                $.ajax({
                    url: '{{ route('boletas.guardar_oficializar') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id_estudiante: studentId,
                        total: $('#boleta-total').text(),
                        periodo: $('#boleta-cuatrimestre').text(),
                        descuento: $('#boleta-descuento').val(),
                        cuotas: $('#boleta-cuotas').val(),
                        cobrar_inscripcion: $('#cobrar-inscripcion').is(':checked') ? 1 : 0,
                        cobrar_biblioteca: $('#cobrar-biblioteca').is(':checked') ? 1 : 0,
                        cobrar_matricula: $('#cobrar-matricula').is(':checked') ? 1 : 0,
                        pago_inicial: $('#boleta-pago-inicial').val(),
                        pago_inicial_metodo: $('#boleta-pago-inicial-metodo').val(),
                        pago_inicial_referencia: $('#boleta-pago-inicial-referencia').val(),
                        fechas_vencimiento: fechas_vencimiento,
                        cursos_ids: cursos_seleccionados,
                        signature: signatureBase64
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                title: '¡Boleta Oficializada con Éxito!',
                                text: 'El documento ha sido creado y el PDF generado.',
                                icon: 'success',
                                confirmButtonColor: '#5fb230',
                                confirmButtonText: '<i class="bi bi-file-earmark-pdf me-1"></i> Ver PDF Oficial'
                            }).then(() => {
                                if (res.pdf_url) {
                                    window.open(res.pdf_url, '_blank');
                                }
                                window.location.href = '{{ route('boletas.index') }}';
                            });
                        } else {
                            Swal.fire('Error', res.message || 'Error al oficializar.', 'error');
                            btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-2 fs-5"></i> Oficializar Directamente');
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Ocurrió un error al oficializar la boleta.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', msg, 'error');
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-2 fs-5"></i> Oficializar Directamente');
                    }
                });
            }
        });
    });

    function resetToProgramSelection() {
        $('#boleta-container').hide();
        $('#programa-select-card').show();
        if ($('.curso-checkbox:checked').length > 0) {
            $('#floating-prepare-container').show();
        }
        $('#boleta-descuento').val(0);
        dataActual = null;
        
        const canvas = document.getElementById('signature-canvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
    }

    function initSignatureCanvas() {
        const canvas = document.getElementById('signature-canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        let drawing = false;

        window.resizeCanvas = function() {
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = 140;
            
            ctx.strokeStyle = '#2d3436';
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }

        window.addEventListener('resize', window.resizeCanvas);

        function getPointerPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function startDrawing(e) {
            drawing = true;
            const pos = getPointerPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            e.preventDefault();
        }

        function draw(e) {
            if (!drawing) return;
            const pos = getPointerPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            e.preventDefault();
        }

        function stopDrawing() {
            drawing = false;
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        window.addEventListener('mouseup', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        window.addEventListener('touchend', stopDrawing);

        $('#clear-signature-btn').click(function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });

        window.isCanvasEmpty = function() {
            const blank = document.createElement('canvas');
            blank.width = canvas.width;
            blank.height = canvas.height;
            return canvas.toDataURL() === blank.toDataURL();
        }
    }
</script>
@endsection
