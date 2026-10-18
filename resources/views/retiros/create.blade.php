@extends('layouts.app')

@section('title', 'Solicitar Retiro o Cambio de Asignatura - ' . config('cliente.nombre', 'CEFI'))

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
<div class="container-fluid px-4 py-4" style="max-width: 1000px;">

    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('retiros.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-1 btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver a la Lista
        </a>
    </div>

    <!-- Main Card -->
    <div class="form-container-card">
        <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
            <div>
                <span class="badge bg-warning bg-opacity-25 text-warning px-3 py-1 rounded-pill small mb-2">
                    <i class="bi bi-arrow-left-right me-1"></i> Control Académico (Fase 4)
                </span>
                <h3 class="h4 fw-bold mb-1">Registrar Retiro o Cambio de Asignatura</h3>
                <p class="text-muted small mb-0">Solicitud formal de desinscripción o traslado de grupo dentro del ciclo lectivo institucional.</p>
            </div>
            <i class="bi bi-arrow-left-right text-warning fs-1 opacity-50"></i>
        </div>

        @if($errors->any())
        <div class="alert alert-danger rounded-4 mb-4 border-0">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> Por favor verifique los siguientes campos:
            <ul class="mb-0 mt-2 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('retiros.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- SECCIÓN 1: ESTUDIANTE Y MATRÍCULA -->
            <div class="mb-4">
                <div class="section-title-custom">
                    <i class="bi bi-person-badge"></i> 1. Estudiante y Asignatura Cursada
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Seleccionar Estudiante <span class="text-danger">*</span></label>
                        <select name="id_estudiante" id="select_estudiante_retiro" class="form-select" required>
                            <option value="">-- Seleccione un estudiante --</option>
                            @foreach($estudiantes as $est)
                                <option value="{{ $est->id }}" {{ old('id_estudiante') == $est->id ? 'selected' : '' }}>
                                    {{ $est->apellidos }}, {{ $est->nombre }} (Cédula: {{ $est->cedula ?: 'S/C' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Asignatura a Retirar o Trasladar <span class="text-danger">*</span></label>
                        <select name="id_matricula" id="select_matricula" class="form-select" required>
                            <option value="">-- Seleccione primero un estudiante --</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: TIPO DE TRÁMITE -->
            <div class="mb-4">
                <div class="section-title-custom">
                    <i class="bi bi-gear-wide-connected"></i> 2. Modalidad del Trámite
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Tipo de Trámite <span class="text-danger">*</span></label>
                        <select name="tipo_tramite" id="select_tipo_tramite" class="form-select fw-semibold" required>
                            <option value="Retiro" selected>Retiro de Asignatura</option>
                            <option value="Cambio">Cambio de Curso / Grupo</option>
                        </select>
                    </div>
                    <div class="col-md-8" id="bloque_curso_nuevo" style="display: none;">
                        <label class="form-label fw-semibold small text-muted">Nuevo Curso / Grupo Destino <span class="text-danger">*</span></label>
                        <select name="id_curso_activo_nuevo" class="form-select">
                            <option value="">-- Seleccionar curso destino --</option>
                            @foreach($cursosActivos as $ca)
                                <option value="{{ $ca->id_curso_activo }}">
                                    [{{ $ca->planEstudio ? $ca->planEstudio->codigo : 'S/C' }}] {{ $ca->planEstudio ? $ca->planEstudio->materia : 'Curso #' . $ca->id_curso_activo }} - Período {{ $ca->periodo }} (Prof. {{ $ca->profesor ? $ca->profesor->nombre : 'Sin asignar' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Causal o Tipo de Retiro</label>
                        <select name="tipo_retiro" class="form-select">
                            <option value="Justificado">Justificado (Médico / Laboral)</option>
                            <option value="Ordinario">Ordinario / Voluntario</option>
                            <option value="Fuerza Mayor">Fuerza Mayor o Caso Fortuito</option>
                            <option value="Académico">Recomendación Académica</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Impacto Financiero</label>
                        <select name="impacto_financiero" class="form-select">
                            <option value="Mantiene Cobro">Mantiene Cobro Ordinario</option>
                            <option value="Sin Costo">Sin Costo / Ajuste Administrativo</option>
                            <option value="Nota de Credito">Aplica Nota de Crédito</option>
                            <option value="Cobro Diferencia">Cobro Diferencia de Arancel</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 3: MOTIVO Y CONSTANCIAS -->
            <div class="mb-4">
                <div class="section-title-custom">
                    <i class="bi bi-file-text"></i> 3. Justificación y Comprobante
                </div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold small text-muted">Motivo Detallado de la Solicitud <span class="text-danger">*</span></label>
                        <textarea name="motivo" rows="3" class="form-control" placeholder="Explique las razones personales, médicas, de horario o laborales por las cuales solicita el retiro o cambio..." required>{{ old('motivo') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Documento Probatorio / Constancia (Opcional)</label>
                        <input type="file" name="documento_probatorio" class="form-control" accept=".pdf,.png,.jpg,.jpeg">
                        <span class="text-muted small" style="font-size: 0.75rem;">Dictamen médico, carta laboral o solicitud firmada (PDF o imagen).</span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Estado Inicial del Trámite</label>
                        <select name="estado_inicial" class="form-select fw-semibold">
                            <option value="Pendiente" selected>Pendiente (Para revisión de Dirección)</option>
                            <option value="Aprobado">Aprobado Inmediato (Aplicar a la matrícula)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- BOTONES -->
            <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                <a href="{{ route('retiros.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary px-5 rounded-pill fw-semibold shadow-sm">
                    <i class="bi bi-check-circle me-2"></i> Procesar Solicitud
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectEstudiante = document.getElementById('select_estudiante_retiro');
    const selectMatricula = document.getElementById('select_matricula');
    const selectTipoTramite = document.getElementById('select_tipo_tramite');
    const bloqueCursoNuevo = document.getElementById('bloque_curso_nuevo');

    if (selectEstudiante) {
        selectEstudiante.addEventListener('change', function () {
            const idEst = this.value;
            selectMatricula.innerHTML = '<option value="">Cargando cursos matriculados...</option>';
            if (!idEst) {
                selectMatricula.innerHTML = '<option value="">-- Seleccione primero un estudiante --</option>';
                return;
            }

            fetch("{{ url('/retiros-asignaturas/estudiante') }}/" + idEst + "/matriculas-ajax")
                .then(res => res.json())
                .then(data => {
                    selectMatricula.innerHTML = '<option value="">-- Seleccione la Asignatura --</option>';
                    if (data.length === 0) {
                        selectMatricula.innerHTML = '<option value="">Sin matrículas activas encontradas</option>';
                        return;
                    }
                    data.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m.id_matricula;
                        opt.textContent = `[${m.codigo}] ${m.materia} (${m.periodo || 'Activo'}) - Estado: ${m.estado}`;
                        selectMatricula.appendChild(opt);
                    });
                })
                .catch(err => {
                    console.error('Error cargando matrículas:', err);
                    selectMatricula.innerHTML = '<option value="">Error al cargar matrículas</option>';
                });
        });
    }

    if (selectTipoTramite) {
        selectTipoTramite.addEventListener('change', function () {
            if (this.value === 'Cambio') {
                bloqueCursoNuevo.style.display = 'block';
            } else {
                bloqueCursoNuevo.style.display = 'none';
            }
        });
    }
});
</script>
@endsection
