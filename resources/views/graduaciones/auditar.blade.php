@extends('layouts.app')

@section('title', 'Auditoría de Graduación - ' . config('cliente.nombre', 'CEFI'))

@section('styles')
<style>
    .audit-header-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%);
        border: 1px solid var(--border-dark);
        border-radius: 1.5rem;
        padding: 2.2rem;
        margin-bottom: 2rem;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
    }
    [data-theme="light"] .audit-header-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.04) !important;
    }
    .requirement-card {
        background: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        padding: 1.6rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        height: 100%;
        transition: transform 0.2s;
    }
    .requirement-card:hover { transform: translateY(-3px); }
    [data-theme="light"] .requirement-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
    }
    .resolution-card {
        background: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.5rem;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    [data-theme="light"] .resolution-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04) !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-3">

    <!-- Back Button & Actions -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <a href="{{ route('graduaciones.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-1 btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver al Padrón
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('graduaciones.dictamen_pdf', $graduacion->id_graduacion) }}" target="_blank" class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-semibold">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Descargar Dictamen Oficial PDF
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4 border-0 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Header Card -->
    <div class="audit-header-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary bg-opacity-25 text-primary px-3 py-1 rounded-pill small">
                        <i class="bi bi-mortarboard-fill me-1"></i> Expediente de Graduación #{{ $graduacion->id_graduacion }}
                    </span>
                    <span class="text-muted small">Convocatoria: <strong>{{ $graduacion->convocatoria }}</strong></span>
                </div>
                <h2 class="h3 fw-bold mb-1">
                    {{ $graduacion->estudiante ? "{$graduacion->estudiante->nombre} {$graduacion->estudiante->apellidos}" : 'Estudiante N/D' }}
                </h2>
                <div class="d-flex flex-wrap gap-4 text-muted small mt-2">
                    <span><i class="bi bi-person-vcard me-1"></i> Cédula: <strong>{{ $graduacion->estudiante ? $graduacion->estudiante->cedula : 'S/C' }}</strong></span>
                    <span><i class="bi bi-envelope me-1"></i> {{ $graduacion->estudiante ? $graduacion->estudiante->email : '' }}</span>
                    <span><i class="bi bi-mortarboard me-1"></i> Carrera: <strong>{{ $graduacion->programa ? $graduacion->programa->nombre_programa : 'N/D' }}</strong></span>
                </div>
            </div>
            <div class="text-md-end">
                <span class="text-muted small d-block mb-1">Estado de la Postulación</span>
                @if($graduacion->estado === 'Graduado' || $graduacion->estado === 'Aprobado para Graduación')
                    <span class="badge bg-success bg-opacity-25 text-success fs-6 px-4 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> {{ $graduacion->estado }}</span>
                @elseif($graduacion->estado === 'En Auditoría')
                    <span class="badge bg-warning bg-opacity-25 text-warning fs-6 px-4 py-2 rounded-pill"><i class="bi bi-hourglass-split me-1"></i> En Auditoría</span>
                @else
                    <span class="badge bg-danger bg-opacity-25 text-danger fs-6 px-4 py-2 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i> {{ $graduacion->estado }}</span>
                @endif
                <div class="mt-2 text-muted small">
                    Promedio General: <strong class="fs-6 text-primary">{{ number_format($graduacion->promedio_ponderado_final, 2) }} / 100</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- SEMÁFORO DE LOS 4 REQUISITOS -->
    <div class="row g-4 mb-4">
        <!-- Requisito 1: Plan de Estudios -->
        <div class="col-md-6 col-xl-3">
            <div class="requirement-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="fw-bold small text-muted text-uppercase">1. Plan de Estudios</span>
                    @if($graduacion->cumplio_plan_estudios)
                        <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i> Cumple</span>
                    @else
                        <span class="badge bg-danger bg-opacity-25 text-danger rounded-pill px-2 py-1"><i class="bi bi-x-circle-fill me-1"></i> Pendiente</span>
                    @endif
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <h3 class="fw-bold mb-0 text-primary">{{ $auditoria['materias_aprobadas'] }} / {{ $auditoria['materias_totales'] }}</h3>
                    <span class="text-muted small">materias</span>
                </div>
                <div class="progress mb-2" style="height: 6px;">
                    @php
                        $pctMaterias = ($auditoria['materias_totales'] > 0) ? min(100, round(($auditoria['materias_aprobadas'] / $auditoria['materias_totales']) * 100)) : 0;
                    @endphp
                    <div class="progress-bar {{ $graduacion->cumplio_plan_estudios ? 'bg-success' : 'bg-primary' }}" style="width: {{ $pctMaterias }}%"></div>
                </div>
                <span class="text-muted small d-block">
                    {{ number_format($auditoria['creditos_aprobados'], 1) }} créditos aprobados (incluye convalidaciones oficiales).
                </span>
            </div>
        </div>

        <!-- Requisito 2: TCU -->
        <div class="col-md-6 col-xl-3">
            <div class="requirement-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="fw-bold small text-muted text-uppercase">2. Trabajo Comunal</span>
                    @if($graduacion->cumplio_tcu)
                        <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i> Cumple</span>
                    @else
                        <span class="badge bg-danger bg-opacity-25 text-danger rounded-pill px-2 py-1"><i class="bi bi-x-circle-fill me-1"></i> Pendiente</span>
                    @endif
                </div>
                <div class="fs-4 mb-2">
                    <i class="bi bi-journal-check text-info"></i>
                </div>
                <h6 class="fw-bold mb-1">Bitácora de TCU</h6>
                <p class="text-muted small mb-0">{{ $auditoria['tcu_detalle'] }}</p>
            </div>
        </div>

        <!-- Requisito 3: Finanzas -->
        <div class="col-md-6 col-xl-3">
            <div class="requirement-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="fw-bold small text-muted text-uppercase">3. Solvencia Financiera</span>
                    @if($graduacion->cumplio_financiero)
                        <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i> Cumple</span>
                    @else
                        <span class="badge bg-danger bg-opacity-25 text-danger rounded-pill px-2 py-1"><i class="bi bi-x-circle-fill me-1"></i> Pendiente</span>
                    @endif
                </div>
                <div class="fs-4 mb-2">
                    <i class="bi bi-cash-coin text-success"></i>
                </div>
                <h6 class="fw-bold mb-1">Estado de Cuenta</h6>
                <p class="text-muted small mb-0">{{ $auditoria['finanzas_detalle'] }}</p>
            </div>
        </div>

        <!-- Requisito 4: Expediente Digital -->
        <div class="col-md-6 col-xl-3">
            <div class="requirement-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="fw-bold small text-muted text-uppercase">4. Expediente Digital</span>
                    @if($graduacion->cumplio_expediente)
                        <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i> Cumple</span>
                    @else
                        <span class="badge bg-danger bg-opacity-25 text-danger rounded-pill px-2 py-1"><i class="bi bi-x-circle-fill me-1"></i> Pendiente</span>
                    @endif
                </div>
                <div class="fs-4 mb-2">
                    <i class="bi bi-folder2-open text-warning"></i>
                </div>
                <h6 class="fw-bold mb-1">Documentos Legales</h6>
                <p class="text-muted small mb-0">{{ $auditoria['expediente_detalle'] }}</p>
            </div>
        </div>
    </div>

    <!-- RESOLUCIÓN OFICIAL Y REGISTRO EN LIBRO -->
    <div class="resolution-card">
        <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
            <div>
                <h4 class="h5 fw-bold mb-1">Dictamen y Oficialización en Libro de Actas</h4>
                <p class="text-muted small mb-0">Asignación de folios, tomos, número de título y menciones honoríficas oficiales de CEFI.</p>
            </div>
            <i class="bi bi-award fs-2 text-primary opacity-50"></i>
        </div>

        <form method="POST" action="{{ route('graduaciones.aprobar', $graduacion->id_graduacion) }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-muted">Estado del Trámite <span class="text-danger">*</span></label>
                    <select name="estado" class="form-select fw-semibold" required>
                        <option value="En Auditoría" {{ $graduacion->estado === 'En Auditoría' ? 'selected' : '' }}>En Auditoría</option>
                        <option value="Aprobado para Graduación" {{ $graduacion->estado === 'Aprobado para Graduación' ? 'selected' : '' }}>Aprobado para Graduación (Idóneo)</option>
                        <option value="Graduado" {{ $graduacion->estado === 'Graduado' ? 'selected' : '' }}>Graduado (Título Entregado)</option>
                        <option value="Rechazado" {{ $graduacion->estado === 'Rechazado' ? 'selected' : '' }}>Rechazado (No idóneo)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-muted">Mención de Honor <span class="text-danger">*</span></label>
                    <select name="mencion_honor" class="form-select" required>
                        <option value="Ninguna" {{ $graduacion->mencion_honor === 'Ninguna' ? 'selected' : '' }}>Ninguna / Ordinaria</option>
                        <option value="Cum Laude" {{ $graduacion->mencion_honor === 'Cum Laude' ? 'selected' : '' }}>Cum Laude (Promedio >= 90)</option>
                        <option value="Magna Cum Laude" {{ $graduacion->mencion_honor === 'Magna Cum Laude' ? 'selected' : '' }}>Magna Cum Laude (Promedio >= 95)</option>
                        <option value="Summa Cum Laude" {{ $graduacion->mencion_honor === 'Summa Cum Laude' ? 'selected' : '' }}>Summa Cum Laude (Promedio 100)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-muted">Nº de Título / Diploma</label>
                    <input type="text" name="numero_titulo" value="{{ old('numero_titulo', $graduacion->numero_titulo) }}" class="form-control font-monospace" placeholder="Ej: TIT-CEFI-2026-042">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-muted">Nº de Acta</label>
                    <input type="text" name="numero_acta" value="{{ old('numero_acta', $graduacion->numero_acta) }}" class="form-control font-monospace" placeholder="Ej: ACTA-GRAD-12">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-muted">Tomo</label>
                    <input type="text" name="numero_tomo" value="{{ old('numero_tomo', $graduacion->numero_tomo) }}" class="form-control font-monospace" placeholder="Ej: I">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-muted">Folio</label>
                    <input type="text" name="numero_folio" value="{{ old('numero_folio', $graduacion->numero_folio) }}" class="form-control font-monospace" placeholder="Ej: 145">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold small text-muted">Observaciones o Dictamen del Consejo Académico</label>
                    <textarea name="observaciones" rows="3" class="form-control" placeholder="Anotaciones sobre el acuerdo del Consejo Académico, modalidad de graduación o resoluciones oficiales...">{{ old('observaciones', $graduacion->observaciones) }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-5 rounded-pill fw-semibold shadow-sm">
                    <i class="bi bi-save me-2"></i> Guardar Resolución y Actualizar Libro
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
