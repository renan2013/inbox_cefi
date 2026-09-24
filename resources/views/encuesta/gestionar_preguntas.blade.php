@extends('layouts.app')

@section('title', 'Gestionar Preguntas de Encuesta')

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #1a2e1a 0%, #2d5a1b 60%, #5fb230 100%);
        border-radius: 1.25rem;
        padding: 1.75rem 2rem;
        color: #fff;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .page-header::after {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 180px; height: 180px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }

    .kpi-card {
        background: var(--card-dark);
        border-radius: 14px;
        border: 1px solid var(--border-dark);
        padding: 1.25rem 1rem;
        text-align: center;
        transition: transform .2s, box-shadow .2s;
    }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
    .kpi-val { font-size: 2.2rem; font-weight: 800; line-height: 1; }
    .kpi-lbl { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; font-weight: 600; margin-top: 4px; }

    .pregunta-card {
        background: var(--card-dark);
        border-radius: 16px;
        border: 1px solid var(--border-dark);
        margin-bottom: .75rem;
        transition: box-shadow .2s, border-color .2s;
        overflow: hidden;
    }
    .pregunta-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.2); border-color: #475569; }
    .pregunta-card.inactiva { opacity: .55; }

    .preg-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: .9rem 1.25rem;
        cursor: pointer;
    }
    .preg-num {
        min-width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 800; color: #fff;
        flex-shrink: 0;
    }
    .cat-badge-inst { background: rgba(37,99,235,0.15); color: #60a5fa; border: 1px solid rgba(37,99,235,0.3); border-radius: 50px; padding: 2px 10px; font-size: 11px; font-weight: 700; }
    .cat-badge-doc  { background: rgba(22,163,74,0.15); color: #4ade80; border: 1px solid rgba(22,163,74,0.3); border-radius: 50px; padding: 2px 10px; font-size: 11px; font-weight: 700; }

    .opcion-tag {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid var(--border-dark);
        border-radius: 8px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('encuestas.resultados') }}" class="text-white-50">Encuestas</a></li>
            <li class="breadcrumb-item active text-white" aria-current="page">Banco de Preguntas</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge" style="background: rgba(255,255,255,0.2); font-size: 11px;">Módulo de Calidad</span>
                <span class="badge" style="background: rgba(0,0,0,0.25); font-size: 11px;"><i class="bi bi-shield-check me-1"></i> Administración</span>
            </div>
            <h1 class="h3 fw-bold mb-1">Banco de Preguntas de Encuesta</h1>
            <p class="mb-0 text-white-50 small">Administre y personalice los reactivos de evaluación docente y calidad institucional.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('encuestas.resultados') }}" class="btn btn-outline-light rounded-pill px-4">
                <i class="bi bi-bar-chart-line-fill me-1"></i> Ver Resultados
            </a>
            <button class="btn btn-light rounded-pill px-4 fw-bold text-success" data-bs-toggle="modal" data-bs-target="#modalNuevaPregunta">
                <i class="bi bi-plus-circle-fill me-1"></i> Nueva Pregunta
            </button>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card text-white">
                <div class="kpi-val text-primary" id="kpi-total">{{ $total }}</div>
                <div class="kpi-lbl">Total Preguntas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card text-white">
                <div class="kpi-val text-success" id="kpi-activas">{{ $total_activas }}</div>
                <div class="kpi-lbl">Activas en Formulario</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card text-white">
                <div class="kpi-val text-info" id="kpi-inst">{{ $total_inst }}</div>
                <div class="kpi-lbl">Sección Institución</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card text-white">
                <div class="kpi-val text-warning" id="kpi-doc">{{ $total_doc }}</div>
                <div class="kpi-lbl">Sección Docente</div>
            </div>
        </div>
    </div>

    <!-- Lista de Preguntas -->
    <div class="card bg-dark border border-secondary border-opacity-25 rounded-4 p-4 text-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-list-check text-success me-2"></i> Preguntas Configuradas</h5>
            <span class="text-white-50 x-small">Haga clic en una pregunta para expandir y editar sus opciones</span>
        </div>

        <div id="contenedorPreguntas">
            @forelse ($preguntas as $idx => $p)
                <div class="pregunta-card {{ $p->activo ? '' : 'inactiva' }}" id="preg-card-{{ $p->id }}" data-id="{{ $p->id }}">
                    <div class="preg-header" onclick="toggleDetalle({{ $p->id }})">
                        <div class="preg-num" style="background: {{ $p->categoria === 'institucion' ? '#2563eb' : '#16a34a' }};">
                            {{ $idx + 1 }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="{{ $p->categoria === 'institucion' ? 'cat-badge-inst' : 'cat-badge-doc' }}">
                                    {{ $p->categoria === 'institucion' ? 'Institucional' : 'Docente' }}
                                </span>
                                @if (!$p->activo)
                                    <span class="badge bg-secondary x-small">Inactiva</span>
                                @endif
                            </div>
                            <div class="fw-bold text-white small" id="preg-texto-preview-{{ $p->id }}">
                                {{ $p->texto }}
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2" onclick="event.stopPropagation();">
                            <button class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="toggleActivo({{ $p->id }})" title="Activar / Desactivar">
                                <i class="bi bi-{{ $p->activo ? 'toggle-on text-success' : 'toggle-off text-muted' }} fs-5"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="eliminarPregunta({{ $p->id }})" title="Eliminar">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Detalle / Edición de la Pregunta -->
                    <div class="p-3 border-top border-secondary border-opacity-25" id="detalle-{{ $p->id }}" style="display: none; background: rgba(15,23,42,0.4);">
                        <form id="form-edit-{{ $p->id }}" onsubmit="guardarEdicion(event, {{ $p->id }})">
                            <div class="row g-3 mb-3">
                                <div class="col-md-9">
                                    <label class="form-label x-small text-white-50 fw-bold">Texto del Reactivo / Pregunta</label>
                                    <input type="text" name="texto" class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ $p->texto }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label x-small text-white-50 fw-bold">Categoría / Sección</label>
                                    <select name="categoria" class="form-select form-select-sm bg-dark text-white border-secondary">
                                        <option value="institucion" {{ $p->categoria === 'institucion' ? 'selected' : '' }}>Institución / Plataforma</option>
                                        <option value="docente" {{ $p->categoria === 'docente' ? 'selected' : '' }}>Docente / Curso</option>
                                    </select>
                                </div>
                            </div>

                            <label class="form-label x-small text-white-50 fw-bold mb-2">Escala de Opciones y Puntajes</label>
                            <div class="row g-2 mb-3">
                                @foreach ($p->opciones as $op)
                                    <div class="col-md-3 col-6">
                                        <div class="p-2 rounded-3 border border-secondary border-opacity-50 bg-dark">
                                            <input type="hidden" name="opciones[{{ $op->id }}][id]" value="{{ $op->id }}">
                                            <div class="d-flex gap-2 mb-1">
                                                <input type="text" name="opciones[{{ $op->id }}][emoji]" class="form-control form-control-sm bg-dark text-white border-secondary text-center" style="width: 45px;" value="{{ $op->emoji }}" required>
                                                <input type="text" name="opciones[{{ $op->id }}][texto]" class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ $op->texto }}" required>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between px-1">
                                                <span class="x-small text-white-50">Puntaje:</span>
                                                <input type="number" name="opciones[{{ $op->id }}][puntaje]" class="form-control form-control-sm bg-dark text-white border-secondary text-end" style="width: 70px;" value="{{ $op->puntaje }}" required>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="toggleDetalle({{ $p->id }})">Cerrar</button>
                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-clipboard-x fs-1 d-block mb-2"></i>
                    No hay preguntas registradas en el banco de encuestas.
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Modal Nueva Pregunta -->
<div class="modal fade" id="modalNuevaPregunta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary text-white rounded-4">
            <form id="formNuevaPregunta" onsubmit="crearPregunta(event)">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-success me-2"></i> Nueva Pregunta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label x-small text-white-50 fw-bold">Texto de la Pregunta *</label>
                        <input type="text" name="texto" class="form-control bg-dark text-white border-secondary" placeholder="Ej: El docente dominó los contenidos de la materia..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label x-small text-white-50 fw-bold">Categoría *</label>
                        <select name="categoria" class="form-select bg-dark text-white border-secondary">
                            <option value="docente" selected>Docente / Curso</option>
                            <option value="institucion">Institución / Plataforma</option>
                        </select>
                    </div>
                    <div class="p-3 bg-secondary bg-opacity-10 border border-secondary border-opacity-25 rounded-3 x-small text-white-50">
                        <i class="bi bi-info-circle me-1"></i> Se crearán automáticamente las 4 opciones Likert estándar (Malo: 25, Regular: 50, Bueno: 75, Excelente: 100). Podrás personalizarlas después de crear la pregunta.
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Crear Pregunta</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleDetalle(id) {
        const div = document.getElementById(`detalle-${id}`);
        if (div) {
            div.style.display = (div.style.display === 'none' || div.style.display === '') ? 'block' : 'none';
        }
    }

    function crearPregunta(e) {
        e.preventDefault();
        const form = document.getElementById('formNuevaPregunta');
        const formData = new FormData(form);
        formData.append('action', 'crear');

        fetch("{{ route('encuestas.preguntas.ajax') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Pregunta creada', text: data.message, timer: 1500, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        });
    }

    function guardarEdicion(e, id) {
        e.preventDefault();
        const form = document.getElementById(`form-edit-${id}`);
        const formData = new FormData(form);
        formData.append('action', 'actualizar');
        formData.append('id', id);

        fetch("{{ route('encuestas.preguntas.ajax') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Cambios guardados', timer: 2000, showConfirmButton: false });
                toggleDetalle(id);
                document.getElementById(`preg-texto-preview-${id}`).innerText = form.querySelector('input[name="texto"]').value;
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        });
    }

    function toggleActivo(id) {
        const formData = new FormData();
        formData.append('action', 'toggle_activo');
        formData.append('id', id);

        fetch("{{ route('encuestas.preguntas.ajax') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function eliminarPregunta(id) {
        Swal.fire({
            title: '¿Eliminar pregunta?',
            text: 'Esta acción no se puede deshacer y eliminará las opciones asociadas.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'eliminar');
                formData.append('id', id);

                fetch("{{ route('encuestas.preguntas.ajax') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Eliminada', text: data.message, timer: 1500, showConfirmButton: false })
                            .then(() => location.reload());
                    }
                });
            }
        });
    }
</script>
@endsection
