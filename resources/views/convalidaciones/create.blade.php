@extends('layouts.app')

@section('title', 'Registrar Convalidación Académica - ' . config('cliente.nombre', 'CEFI'))

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
        color: #5fb230;
        margin-bottom: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-cefi-primary {
        background: linear-gradient(135deg, #5fb230 0%, #4a9423 100%) !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 700;
        padding: 0.65rem 1.8rem;
        border-radius: 50px;
        box-shadow: 0 4px 14px rgba(95, 178, 48, 0.35);
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        text-decoration: none;
    }
    .btn-cefi-primary:hover {
        background: linear-gradient(135deg, #6ec838 0%, #52a327 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(95, 178, 48, 0.45);
        color: #ffffff !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1100px;">

    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('convalidaciones.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-1 btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver a la Lista
        </a>
    </div>

    <!-- Main Card -->
    <div class="form-container-card">
        <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
            <div>
                <span class="badge bg-success bg-opacity-25 text-success px-3 py-1 rounded-pill small mb-2 border border-success-subtle">
                    <i class="bi bi-patch-check-fill me-1"></i> Control Académico (Fase 4)
                </span>
                <h3 class="h4 fw-bold mb-1">Registrar Convalidación / Homologación</h3>
                <p class="text-muted small mb-0">Reconocimiento y equivalencia de asignaturas aprobadas para el historial académico del estudiante.</p>
            </div>
            <i class="bi bi-patch-check-fill text-success fs-1 opacity-75"></i>
        </div>

        @if($errors->any())
        <div class="alert alert-danger rounded-4 mb-4 border-0">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> Por favor verifique los campos del formulario:
            <ul class="mb-0 mt-2 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('convalidaciones.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- SECCIÓN 1: ESTUDIANTE -->
            <div class="mb-4">
                <div class="section-title-custom">
                    <i class="bi bi-person-badge"></i> 1. Estudiante Beneficiario
                </div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold small text-muted">Seleccionar Estudiante <span class="text-danger">*</span></label>
                        <select name="id_estudiante" id="select_estudiante" class="form-select" required>
                            <option value="">-- Seleccione un estudiante --</option>
                            @foreach($estudiantes as $est)
                                <option value="{{ $est->id }}" {{ (old('id_estudiante', $estudianteSeleccionado ? $estudianteSeleccionado->id : '') == $est->id) ? 'selected' : '' }}>
                                    {{ $est->apellidos }}, {{ $est->nombre }} (Cédula: {{ $est->cedula ?: 'S/C' }} | {{ $est->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: DATOS DE ORIGEN -->
            <div class="mb-4">
                <div class="section-title-custom">
                    <i class="bi bi-bank"></i> 2. Institución y Asignatura de Procedencia
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Institución de Procedencia <span class="text-danger">*</span></label>
                        <input type="text" name="institucion_origen" value="{{ old('institucion_origen') }}" class="form-control" placeholder="Ej: Universidad de Costa Rica (UCR)" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Carrera de Procedencia</label>
                        <input type="text" name="carrera_origen" value="{{ old('carrera_origen') }}" class="form-control" placeholder="Ej: Bachillerato en Administración de Empresas">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Nombre de Asignatura de Origen <span class="text-danger">*</span></label>
                        <input type="text" name="materia_origen" value="{{ old('materia_origen') }}" class="form-control" placeholder="Ej: Contabilidad Financiera I" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted">Código Origen</label>
                        <input type="text" name="codigo_origen" value="{{ old('codigo_origen') }}" class="form-control font-monospace" placeholder="Ej: CO-101">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted">Créditos Origen</label>
                        <input type="number" step="0.5" name="creditos_origen" value="{{ old('creditos_origen', 3) }}" class="form-control" placeholder="3.0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted">Nota Origen</label>
                        <input type="text" name="calificacion_origen" value="{{ old('calificacion_origen') }}" class="form-control font-monospace" placeholder="Ej: 85.0">
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 3: HOMOLOGACIÓN CEFI -->
            <div class="mb-4">
                <div class="section-title-custom">
                    <i class="bi bi-mortarboard"></i> 3. Equivalencia y Reconocimiento en CEFI
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Programa de CEFI</label>
                        <select name="id_programa" id="select_programa" class="form-select">
                            <option value="">-- Seleccionar Programa (Opcional) --</option>
                            @foreach($programas as $p)
                                <option value="{{ $p->id_programa }}" {{ old('id_programa') == $p->id_programa ? 'selected' : '' }}>
                                    {{ $p->nombre_programa }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Materia Homologada en CEFI</label>
                        <select name="id_plan" id="select_materia_cefi" class="form-select">
                            <option value="">-- Seleccione primero un programa o elija materia --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted">Créditos Reconocidos en CEFI <span class="text-danger">*</span></label>
                        <input type="number" step="0.5" name="creditos_convalidados" value="{{ old('creditos_convalidados', 3.0) }}" class="form-control fw-bold text-primary font-monospace" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted">Nota Homologada (Opcional)</label>
                        <input type="number" step="0.01" name="calificacion_homologada" value="{{ old('calificacion_homologada') }}" class="form-control font-monospace" placeholder="Ej: 85.00">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted">Tipo de Convalidación</label>
                        <select name="tipo_convalidacion" class="form-select">
                            <option value="Externa">Externa (Otra Universidad / Instituto)</option>
                            <option value="Interna">Interna (Cambio de Carrera en CEFI)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted">Estado Inicial <span class="text-danger">*</span></label>
                        <select name="estado" class="form-select fw-semibold" required>
                            <option value="Aprobada" selected>Aprobada (Oficializar)</option>
                            <option value="Pendiente">Pendiente (En Revisión de Comité)</option>
                            <option value="Rechazada">Rechazada</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 4: RESOLUCIÓN Y ADJUNTOS -->
            <div class="mb-4">
                <div class="section-title-custom">
                    <i class="bi bi-file-earmark-check"></i> 4. Respaldo Oficial y Acta Probatoria
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Nº de Resolución / Oficio</label>
                        <input type="text" name="numero_resolucion" value="{{ old('numero_resolucion', 'RES-CONV-' . date('Y') . '-') }}" class="form-control font-monospace" placeholder="Ej: RES-CONV-2026-001">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Fecha de Resolución</label>
                        <input type="date" name="fecha_resolucion" value="{{ old('fecha_resolucion', date('Y-m-d')) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Acta / Certificación de Notas (PDF / Imagen)</label>
                        <input type="file" name="archivo_resolucion" class="form-control" accept=".pdf,.png,.jpg,.jpeg">
                        <span class="text-muted small" style="font-size: 0.75rem;">Se guardará en la carpeta 04. Convalidaciones del expediente.</span>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold small text-muted">Observaciones o Dictamen del Comité Académico</label>
                        <textarea name="observaciones" rows="3" class="form-control" placeholder="Anotaciones sobre programas de estudio analizados, contenidos aprobados o condiciones de la convalidación...">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- BOTONES -->
            <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                <a href="{{ route('convalidaciones.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                    Cancelar
                </a>
                <button type="submit" class="btn-cefi-primary px-5 shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> Guardar Convalidación
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectPrograma = document.getElementById('select_programa');
    const selectMateria = document.getElementById('select_materia_cefi');

    if (selectPrograma) {
        selectPrograma.addEventListener('change', function () {
            const idPrograma = this.value;
            selectMateria.innerHTML = '<option value="">Cargando materias...</option>';
            if (!idPrograma) {
                selectMateria.innerHTML = '<option value="">-- Seleccione primero un programa --</option>';
                return;
            }

            fetch("{{ url('/convalidaciones/programa') }}/" + idPrograma + "/cursos-ajax")
                .then(res => res.json())
                .then(data => {
                    selectMateria.innerHTML = '<option value="">-- Seleccionar Materia del Plan --</option>';
                    data.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.id_plan;
                        opt.textContent = `[${item.codigo || 'S/C'}] ${item.materia} (${item.creditos || 3} cr.) - Cuatrimestre ${item.cuatrimestre || 'N/D'}`;
                        selectMateria.appendChild(opt);
                    });
                })
                .catch(err => {
                    console.error('Error cargando materias:', err);
                    selectMateria.innerHTML = '<option value="">Error al cargar materias</option>';
                });
        });
    }
});
</script>
@endsection
