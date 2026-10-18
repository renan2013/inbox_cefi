@extends('layouts.app')

@section('title', 'Postular Graduando - ' . config('cliente.nombre', 'CEFI'))

@section('styles')
<style>
    .form-container-card {
        background: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.5rem;
        padding: 2.2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    [data-theme="light"] .form-container-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04) !important;
    }
    .section-title-custom {
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--primary);
        margin-bottom: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 950px;">

    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('graduaciones.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-1 btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver a la Lista
        </a>
    </div>

    <!-- Main Card -->
    <div class="form-container-card">
        <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
            <div>
                <span class="badge bg-primary bg-opacity-25 text-primary px-3 py-1 rounded-pill small mb-2">
                    <i class="bi bi-award-fill me-1"></i> Control Académico (Fase 4)
                </span>
                <h3 class="h4 fw-bold mb-1">Postular Estudiante a Graduación</h3>
                <p class="text-muted small mb-0">Apertura del expediente de grado y auditoría de requisitos curriculares, comunales y financieros.</p>
            </div>
            <i class="bi bi-mortarboard text-primary fs-1 opacity-50"></i>
        </div>

        @if($errors->any())
        <div class="alert alert-danger rounded-4 mb-4 border-0">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> Verifique los campos requeridos:
            <ul class="mb-0 mt-2 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('graduaciones.store') }}">
            @csrf

            <!-- SECCIÓN 1: ESTUDIANTE Y PROGRAMA -->
            <div class="mb-4">
                <div class="section-title-custom">
                    <i class="bi bi-person-lines-fill"></i> 1. Información del Graduando
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Seleccionar Estudiante <span class="text-danger">*</span></label>
                        <select name="id_estudiante" class="form-select" required>
                            <option value="">-- Seleccionar estudiante --</option>
                            @foreach($estudiantes as $est)
                                <option value="{{ $est->id }}" {{ old('id_estudiante') == $est->id ? 'selected' : '' }}>
                                    {{ $est->apellidos }}, {{ $est->nombre }} (Cédula: {{ $est->cedula ?: 'S/C' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Programa o Carrera a Graduar <span class="text-danger">*</span></label>
                        <select name="id_programa" class="form-select" required>
                            <option value="">-- Seleccionar programa --</option>
                            @foreach($programas as $p)
                                <option value="{{ $p->id_programa }}" {{ old('id_programa') == $p->id_programa ? 'selected' : '' }}>
                                    {{ $p->nombre_programa }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: CONVOCATORIA Y FECHA -->
            <div class="mb-4">
                <div class="section-title-custom">
                    <i class="bi bi-calendar-event"></i> 2. Convocatoria y Acto de Graduación
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Nombre de la Convocatoria <span class="text-danger">*</span></label>
                        <input type="text" name="convocatoria" value="{{ old('convocatoria', 'I Convocatoria Ordinaria ' . date('Y')) }}" class="form-control" placeholder="Ej: I Convocatoria Ordinaria 2026" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted">Fecha del Acto</label>
                        <input type="date" name="fecha_graduacion" value="{{ old('fecha_graduacion') }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted">Mención de Honor (Propuesta)</label>
                        <select name="mencion_honor" class="form-select">
                            <option value="Ninguna">Ninguna / Ordinaria</option>
                            <option value="Cum Laude">Cum Laude (Prom. 90-94)</option>
                            <option value="Magna Cum Laude">Magna Cum Laude (Prom. 95-99)</option>
                            <option value="Summa Cum Laude">Summa Cum Laude (Prom. 100)</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold small text-muted">Observaciones o Notas de Secretaría</label>
                        <textarea name="observaciones" rows="3" class="form-control" placeholder="Anotaciones administrativas sobre modalidades de graduación (Tesis, Examen de Grado, Práctica Profesional)...">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- BOTONES -->
            <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                <a href="{{ route('graduaciones.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary px-5 rounded-pill fw-semibold shadow-sm">
                    <i class="bi bi-shield-check me-2"></i> Guardar y Ejecutar Auditoría
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
