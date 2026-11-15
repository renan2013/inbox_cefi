@extends('layouts.app')

@section('title', 'Repositorio Oficial de Actas - Registro Académico')

@section('content')
<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-1 rounded-pill fw-bold">
                    <i class="bi bi-journal-bookmark-fill me-1"></i>Auditoría & Registro Oficial
                </span>
                <span class="text-muted small">{{ $actas->count() }} cursos listados</span>
            </div>
            <h2 class="fw-bold mb-0"><i class="bi bi-journal-bookmark-fill text-danger me-2"></i>Repositorio Oficial de Actas de Calificaciones</h2>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('supervision.index') }}" class="btn btn-outline-info rounded-pill px-4">
                <i class="bi bi-eye me-1"></i>Supervisión de Cursos
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtros -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--card-dark);">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('registro.repositorio_actas') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Buscar Curso / Código / Docente</label>
                    <input type="text" name="q" value="{{ $filtroBusqueda }}" class="form-control form-control-sm" placeholder="Ej: Fotografía, TDG-IA01...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Programa / Carrera</label>
                    <select name="programa" class="form-select form-select-sm">
                        <option value="">Todos los programas</option>
                        @foreach($programas as $prog)
                            <option value="{{ $prog->id_programa }}" {{ (string)$filtroPrograma === (string)$prog->id_programa ? 'selected' : '' }}>
                                {{ $prog->nombre_programa }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Año</label>
                    <select name="anio" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @for($y = date('Y') + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ (string)$filtroAnio === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Estado de Acta</label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="todos" {{ $filtroEstado === 'todos' ? 'selected' : '' }}>Todas las Actas</option>
                        <option value="selladas" {{ $filtroEstado === 'selladas' ? 'selected' : '' }}>🔒 Actas Selladas / Oficiales</option>
                        <option value="pendientes" {{ $filtroEstado === 'pendientes' ? 'selected' : '' }}>🔓 En Edición / Pendientes</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">
                        <i class="bi bi-search me-1"></i>Filtrar Actas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Actas -->
    <div class="card border-0 shadow-sm rounded-4" style="background: var(--card-dark);">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Código / Materia</th>
                            <th>Programa / Carrera</th>
                            <th>Período</th>
                            <th>Docente</th>
                            <th class="text-center">Matrícula / Rendimiento</th>
                            <th class="text-center">Estado Acta</th>
                            <th class="text-end pe-4">Acciones Oficiales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($actas as $a)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-primary bg-opacity-10 text-primary font-monospace mb-1">{{ $a->codigo }}</span>
                                    <div class="fw-bold">{{ $a->materia }}</div>
                                </td>
                                <td class="small">{{ $a->nombre_programa }}</td>
                                <td><span class="badge bg-secondary bg-opacity-25">{{ $a->periodo ?: 'Activo' }}</span></td>
                                <td>
                                    <i class="bi bi-person-badge text-info me-1"></i>
                                    {{ trim(($a->prof_nombre ?? '') . ' ' . ($a->prof_apellidos ?? '')) ?: 'Sin asignar' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border me-1" title="Matriculados">{{ $a->total_estudiantes }} est.</span>
                                    <span class="badge bg-success bg-opacity-10 text-success me-1" title="Aprobados">✔ {{ (int)$a->total_aprobados }}</span>
                                    <span class="badge bg-danger bg-opacity-10 text-danger" title="Reprobados">✖ {{ (int)$a->total_reprobados }}</span>
                                    @if($a->promedio_curso !== null)
                                        <div class="small text-muted mt-1">Promedio: <strong>{{ number_format((float)$a->promedio_curso, 1) }}</strong></div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($a->check_acta)
                                        <span class="badge bg-success rounded-pill px-3 py-1">
                                            <i class="bi bi-lock-fill me-1"></i>Sellada Oficial
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1">
                                            <i class="bi bi-unlock-fill me-1"></i>Abierta
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('mis_cursos.acta_pdf', $a->id_curso_activo) }}" target="_blank" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold" title="Descargar PDF del Acta Oficial">
                                            <i class="bi bi-file-earmark-pdf-fill me-1"></i>Acta PDF
                                        </a>
                                        <a href="{{ route('mis_cursos.ver', $a->id_curso_activo) }}" class="btn btn-sm btn-outline-info rounded-pill px-2" title="Abrir Aula del Curso">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                        <form method="POST" action="{{ route('registro.repositorio_actas.toggle', $a->id_curso_activo) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $a->check_acta ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-pill px-2"
                                                    title="{{ $a->check_acta ? 'Desbloquear Acta para edición' : 'Sellar Acta Oficialmente' }}">
                                                <i class="bi {{ $a->check_acta ? 'bi-unlock' : 'bi-lock-fill' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                                    No se encontraron cursos ni actas con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
