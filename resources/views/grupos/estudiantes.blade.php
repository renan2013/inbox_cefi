@extends('layouts.app')

@section('title', 'Gestión de Matrícula - ' . ($grupo->planEstudio->codigo ?? 'Grupo'))

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .glass-card {
        background: var(--card-bg);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    }
    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 0.75rem;
        min-height: 44px;
        display: flex;
        align-items: center;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('grupos.index') }}" class="text-decoration-none">Grupos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Matrícula: {{ $grupo->planEstudio->codigo ?? ('#' . $grupo->id_curso_activo) }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-people-fill text-primary me-2"></i> Gestión de Matrícula
            </h2>
            <p class="text-white-50 mb-0">
                Curso: <strong class="text-white">{{ $grupo->planEstudio->materia ?? 'N/A' }}</strong>
                @if (!empty($grupo->nombre_grupo))
                    <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 rounded-pill ms-1">{{ $grupo->nombre_grupo }}</span>
                @endif
                &nbsp;·&nbsp; {{ $grupo->periodo }}
                @if ($grupo->profesor)
                    &nbsp;·&nbsp; <i class="bi bi-person-fill text-success me-1"></i>Docente: <strong class="text-white">{{ $grupo->profesor->apellidos }}, {{ $grupo->profesor->nombre }}</strong>
                @endif
            </p>
        </div>
        <a href="{{ route('grupos.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Volver a Grupos
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4">
            <i class="bi bi-check-circle-fill fs-5 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
            <div>{{ implode(' · ', $errors->all()) }}</div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Panel de Búsqueda y Matrícula -->
        <div class="col-lg-4">
            <div class="glass-card p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-person-plus-fill text-primary me-2"></i> Matricular Estudiante</h5>
                <form action="{{ route('grupos.estudiantes.matricular', $grupo->id_curso_activo) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="select-estudiante" class="form-label small fw-bold">Buscar Estudiante</label>
                        <select name="id_estudiante" id="select-estudiante" class="form-select" required>
                            <option value="">Escriba nombre, apellidos, cédula o correo...</option>
                            @foreach ($estudiantesDisponibles as $est)
                                <option value="{{ $est->id }}">
                                    {{ $est->apellidos }}, {{ $est->nombre }}
                                    @if (!empty($est->cedula)) — {{ $est->cedula }} @endif
                                    ({{ $est->email }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-white-50 mt-2 small">
                            <i class="bi bi-info-circle me-1"></i> Busque por nombre, correo electrónico o número de cédula.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm">
                        <i class="bi bi-plus-circle me-2"></i> Agregar al Grupo
                    </button>
                </form>

                <hr class="my-4 opacity-25">
                <div class="text-center">
                    <p class="small text-muted mb-2">¿Necesitas inscribir varios estudiantes de un programa o grupo anterior?</p>
                    <button type="button" class="btn btn-outline-primary w-100 py-2 rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalMatricularBloque">
                        <i class="bi bi-people-fill me-2"></i> Matricular en Bloque
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de Estudiantes Matriculados -->
        <div class="col-lg-8">
            <div class="glass-card overflow-hidden">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center" style="border-color: var(--border-dark) !important;">
                    <h5 class="fw-bold mb-0"><i class="bi bi-mortarboard-fill text-success me-2"></i> Estudiantes Matriculados</h5>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold">
                        {{ count($matriculados) }} estudiante(s)
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Estudiante</th>
                                <th>Cédula</th>
                                <th>Correo</th>
                                <th class="text-center">Boleta</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($matriculados as $idx => $m)
                                <tr>
                                    <td class="ps-4 text-white-50 fw-semibold">{{ $idx + 1 }}</td>
                                    <td class="fw-bold">{{ $m->apellidos }}, {{ $m->nombre }}</td>
                                    <td class="text-white-50 small">{{ $m->cedula ?: '—' }}</td>
                                    <td class="text-white-50 small">{{ $m->email }}</td>
                                    <td class="text-center">
                                        @if (!empty($m->id_boleta))
                                            @if ($m->estado_boleta === 'pendiente_firma')
                                                <a href="{{ route('boletas.pdf', $m->id_boleta) }}" target="_blank" class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-2 text-decoration-none" title="Ver PDF de Boleta">
                                                    <i class="bi bi-clock-history me-1"></i> Boleta Generada (Pend. Firma) · {{ $m->numero_boleta }}
                                                </a>
                                            @else
                                                <a href="{{ route('boletas.pdf', $m->id_boleta) }}" target="_blank" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2 text-decoration-none" title="Ver PDF de Boleta">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Boleta Generada: {{ $m->numero_boleta }}
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ route('boletas.generar', ['id_estudiante' => $m->id_estudiante, 'id_plan_auto' => $grupo->id_plan, 'periodo_auto' => $grupo->periodo, 'id_curso_retorno' => $grupo->id_curso_activo]) }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 fw-bold">
                                                <i class="bi bi-receipt me-1"></i> Sin Boleta · Generar
                                            </a>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('grupos.estudiantes.eliminar', $grupo->id_curso_activo) }}" method="POST" class="d-inline form-eliminar-matricula">
                                            @csrf
                                            <input type="hidden" name="id_matricula" value="{{ $m->id_matricula }}">
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-confirmar-eliminar" data-nombre="{{ $m->apellidos }}, {{ $m->nombre }}">
                                                <i class="bi bi-trash3 me-1"></i> Quitar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-white-50">
                                        <i class="bi bi-people fs-2 d-block mb-2 opacity-50"></i>
                                        No hay estudiantes matriculados en este grupo todavía.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Matricular en Bloque -->
<div class="modal fade" id="modalMatricularBloque" tabindex="-1" aria-labelledby="modalMatricularBloqueLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <form action="{{ route('grupos.estudiantes.matricular_bloque', $grupo->id_curso_activo) }}" method="POST" id="form-matricular-bloque">
                @csrf
                <div class="modal-header border-bottom">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalMatricularBloqueLabel">
                            <i class="bi bi-people-fill text-primary me-2"></i>Matrícula en Bloque
                        </h5>
                        <small class="text-muted">Seleccione estudiantes por grupo anterior, programa académico o búsqueda rápida.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">1. Traer de un Grupo anterior</label>
                            <select id="bloque-filtro-grupo" class="form-select form-select-sm">
                                <option value="">— Ninguno (solo filtrar) —</option>
                                @foreach ($gruposOrigen as $go)
                                    <option value="{{ $go->id_curso_activo }}">
                                        {{ $go->codigo }} · {{ $go->materia }} ({{ $go->nombre_grupo ?: $go->periodo }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">2. Filtrar por Programa</label>
                            <select id="bloque-filtro-programa" class="form-select form-select-sm">
                                <option value="all">Todos los estudiantes disponibles</option>
                                @foreach ($programas as $prog)
                                    <option value="{{ $prog->id_programa }}" {{ (int)($grupo->planEstudio->id_programa ?? 0) === (int)$prog->id_programa ? 'data-programa-actual="1"' : '' }}>
                                        {{ $prog->nombre_programa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">3. Buscar por nombre, cédula o correo</label>
                            <input type="text" id="bloque-buscar" class="form-control form-control-sm" placeholder="Escriba para filtrar la lista...">
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" id="btn-bloque-marcar-visibles">
                                <i class="bi bi-check2-all me-1"></i> Marcar visibles
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="btn-bloque-desmarcar">
                                <i class="bi bi-x-lg me-1"></i> Desmarcar todos
                            </button>
                        </div>
                        <span class="badge bg-primary rounded-pill px-3 py-2" id="bloque-contador">0 seleccionado(s)</span>
                    </div>

                    <div class="table-responsive border rounded-3" style="max-height: 360px; overflow-y: auto;">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3" style="width: 45px;">Sel.</th>
                                    <th>Estudiante</th>
                                    <th>Cédula</th>
                                    <th>Correo</th>
                                </tr>
                            </thead>
                            <tbody id="bloque-tabla-estudiantes">
                                @forelse ($estudiantesDisponibles as $est)
                                    @php
                                        $searchBlob = strtolower(trim(($est->apellidos ?? '') . ' ' . ($est->nombre ?? '') . ' ' . ($est->cedula ?? '') . ' ' . ($est->email ?? '')));
                                    @endphp
                                    <tr class="bloque-fila-est" data-id="{{ $est->id }}" data-search="{{ $searchBlob }}">
                                        <td class="ps-3">
                                            <input type="checkbox" class="form-check-input bloque-est-check" name="estudiantes_ids[]" value="{{ $est->id }}" id="bloque-est-{{ $est->id }}">
                                        </td>
                                        <td>
                                            <label class="fw-semibold cursor-pointer mb-0 d-block" for="bloque-est-{{ $est->id }}">
                                                {{ $est->apellidos }}, {{ $est->nombre }}
                                            </label>
                                        </td>
                                        <td class="small text-muted">{{ $est->cedula ?: '—' }}</td>
                                        <td class="small text-muted">{{ $est->email }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No hay estudiantes disponibles para agregar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btn-bloque-enviar" disabled>
                        <i class="bi bi-people-fill me-1"></i> Matricular Seleccionados
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#select-estudiante').select2({
            theme: 'bootstrap-5',
            placeholder: 'Escriba nombre, apellidos, cédula o correo...',
            allowClear: true,
            width: '100%'
        });

        $('.btn-confirmar-eliminar').on('click', function() {
            const btn = $(this);
            const form = btn.closest('form');
            const nombre = btn.data('nombre');

            Swal.fire({
                title: '¿Quitar estudiante del grupo?',
                text: 'Se eliminará la matrícula de ' + nombre + ' en este grupo.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Lógica de Matrícula en Bloque
        const programasEstudiantesMap = @json($programasEstudiantesMap ?? []);
        const gruposEstudiantesMap = @json($gruposEstudiantesMap ?? []);

        function actualizarContadorBloque() {
            const count = $('.bloque-est-check:checked').length;
            $('#bloque-contador').text(count + ' seleccionado(s)');
            $('#btn-bloque-enviar').prop('disabled', count === 0);
        }

        function filtrarTablaBloque() {
            const progId = $('#bloque-filtro-programa').val();
            const grupoId = $('#bloque-filtro-grupo').val();
            const q = ($('#bloque-buscar').val() || '').trim().toLowerCase();

            const allowedByProg = (progId && progId !== 'all')
                ? new Set((programasEstudiantesMap[progId] || []).map(Number))
                : null;
            const allowedByGrupo = grupoId
                ? new Set((gruposEstudiantesMap[grupoId] || []).map(Number))
                : null;

            $('.bloque-fila-est').each(function() {
                const row = $(this);
                const estId = Number(row.data('id'));
                const blob = String(row.data('search') || '');

                let visible = true;
                if (allowedByGrupo && !allowedByGrupo.has(estId)) visible = false;
                if (visible && allowedByProg && !allowedByProg.has(estId)) visible = false;
                if (visible && q !== '' && !blob.includes(q)) visible = false;

                row.toggle(visible);
            });
        }

        $('#bloque-filtro-grupo').on('change', function() {
            const grupoId = $(this).val();
            $('.bloque-est-check').prop('checked', false);
            if (grupoId) {
                $('#bloque-filtro-programa').val('all');
                const ids = new Set((gruposEstudiantesMap[grupoId] || []).map(Number));
                $('.bloque-est-check').each(function() {
                    if (ids.has(Number($(this).val()))) {
                        $(this).prop('checked', true);
                    }
                });
            }
            filtrarTablaBloque();
            actualizarContadorBloque();
        });

        $('#bloque-filtro-programa, #bloque-buscar').on('change input', filtrarTablaBloque);

        $('#btn-bloque-marcar-visibles').on('click', function() {
            $('.bloque-fila-est:visible .bloque-est-check').prop('checked', true);
            actualizarContadorBloque();
        });

        $('#btn-bloque-desmarcar').on('click', function() {
            $('.bloque-est-check').prop('checked', false);
            actualizarContadorBloque();
        });

        $(document).on('change', '.bloque-est-check', actualizarContadorBloque);

        // Preseleccionar programa actual si tiene estudiantes disponibles
        const optProgActual = $('#bloque-filtro-programa option[data-programa-actual="1"]');
        if (optProgActual.length && (programasEstudiantesMap[optProgActual.val()] || []).length > 0) {
            $('#bloque-filtro-programa').val(optProgActual.val());
            filtrarTablaBloque();
        }
    });
</script>
@endsection
