@extends('layouts.app')

@section('title', 'Retiros y Cambios de Asignatura - ' . config('cliente.nombre', 'CEFI'))

@section('styles')
<style>
    .page-header-custom {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%);
        border: 1px solid var(--border-dark);
        border-radius: 1.5rem;
        padding: 2rem 2.2rem;
        margin-bottom: 2rem;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
    }
    [data-theme="light"] .page-header-custom {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.04) !important;
    }
    .kpi-card {
        background: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        padding: 1.4rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        transition: transform 0.2s;
    }
    .kpi-card:hover { transform: translateY(-3px); }
    [data-theme="light"] .kpi-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
    }
    .table-container-card {
        background: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        padding: 1.5rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    [data-theme="light"] .table-container-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
    }
    .filter-card {
        background: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1rem;
        padding: 1.2rem;
        margin-bottom: 1.5rem;
    }
    [data-theme="light"] .filter-card {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-3">

    <!-- Header -->
    <div class="page-header-custom d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="badge bg-warning bg-opacity-25 text-warning px-3 py-2 rounded-pill fw-bold fs-7">
                    <i class="bi bi-arrow-left-right me-1"></i> Fase 4: Control Académico
                </div>
                <span class="text-muted small">CEFI - Gestión de Matrícula</span>
            </div>
            <h2 class="h3 fw-bold mb-1">Retiros y Cambios de Asignaturas</h2>
            <p class="text-muted mb-0 small">Gestión formal de solicitudes de retiro justificado/ordinario y traslados de grupo o curso activo.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('retiros.create') }}" class="btn btn-primary fw-semibold px-4 py-2 rounded-pill shadow-sm">
                <i class="bi bi-plus-circle me-2"></i> Solicitar / Registrar Trámite
            </a>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4 border-0 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-primary bg-opacity-15 text-primary fs-3">
                    <i class="bi bi-journal-arrow-down"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Total Trámites</span>
                    <h3 class="fw-bold mb-0">{{ number_format($kpiTotal) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-warning bg-opacity-15 text-warning fs-3">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Pendientes de Resolución</span>
                    <h3 class="fw-bold mb-0 text-warning">{{ number_format($kpiPendientes) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-success bg-opacity-15 text-success fs-3">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Trámites Aprobados</span>
                    <h3 class="fw-bold mb-0 text-success">{{ number_format($kpiAprobados) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-info bg-opacity-15 text-info fs-3">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Cambios de Asignatura</span>
                    <h3 class="fw-bold mb-0 text-info">{{ number_format($kpiCambios) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filter-card">
        <form method="GET" action="{{ route('retiros.index') }}" class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ $busqueda }}" class="form-control border-start-0" placeholder="Buscar por estudiante, motivo o asignatura...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="estado" class="form-select">
                    <option value="">Todos los Estados</option>
                    <option value="Pendiente" {{ $estadoFiltro === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="Aprobado" {{ $estadoFiltro === 'Aprobado' ? 'selected' : '' }}>Aprobado</option>
                    <option value="Rechazado" {{ $estadoFiltro === 'Rechazado' ? 'selected' : '' }}>Rechazado</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="tipo_tramite" class="form-select">
                    <option value="">Todos los Tipos</option>
                    <option value="Retiro" {{ $tipoFiltro === 'Retiro' ? 'selected' : '' }}>Retiro de Asignatura</option>
                    <option value="Cambio" {{ $tipoFiltro === 'Cambio' ? 'selected' : '' }}>Cambio de Grupo/Curso</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
                @if($busqueda || $estadoFiltro || $tipoFiltro)
                    <a href="{{ route('retiros.index') }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla -->
    <div class="table-container-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th>Estudiante</th>
                        <th>Trámite & Tipo</th>
                        <th>Asignatura Afectada</th>
                        <th>Motivo & Justificación</th>
                        <th>Fecha Solicitud</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($retiros as $r)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $r->estudiante ? "{$r->estudiante->nombre} {$r->estudiante->apellidos}" : 'N/D' }}</div>
                            <span class="text-muted small">{{ $r->estudiante ? $r->estudiante->cedula : '' }}</span>
                        </td>
                        <td>
                            @if($r->tipo_tramite === 'Retiro')
                                <span class="badge bg-danger bg-opacity-20 text-danger fw-semibold px-2 py-1"><i class="bi bi-dash-circle me-1"></i> Retiro</span>
                            @else
                                <span class="badge bg-info bg-opacity-20 text-info fw-semibold px-2 py-1"><i class="bi bi-arrow-repeat me-1"></i> Cambio</span>
                            @endif
                            <div class="text-muted small mt-1">{{ $r->tipo_retiro }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">
                                {{ $r->cursoActivo && $r->cursoActivo->planEstudio ? $r->cursoActivo->planEstudio->materia : "Curso #{$r->id_curso_activo}" }}
                            </div>
                            @if($r->tipo_tramite === 'Cambio' && $r->cursoActivoNuevo)
                                <div class="text-success small mt-1">
                                    <i class="bi bi-arrow-right-short"></i> Traslado a: 
                                    <strong>{{ $r->cursoActivoNuevo->planEstudio ? $r->cursoActivoNuevo->planEstudio->materia : "Grupo #{$r->id_curso_activo_nuevo}" }}</strong>
                                </div>
                            @endif
                        </td>
                        <td style="max-width: 280px;">
                            <div class="text-truncate" title="{{ $r->motivo }}">{{ $r->motivo }}</div>
                            @if($r->documento_probatorio)
                                <a href="{{ asset('storage/' . $r->documento_probatorio) }}" target="_blank" class="badge bg-secondary bg-opacity-25 text-secondary text-decoration-none mt-1">
                                    <i class="bi bi-paperclip me-1"></i> Ver Constancia
                                </a>
                            @endif
                        </td>
                        <td>
                            <span class="small">{{ $r->fecha_solicitud ? $r->fecha_solicitud->format('d/m/Y H:i') : 'N/D' }}</span>
                        </td>
                        <td>
                            @if($r->estado === 'Aprobado')
                                <span class="badge bg-success bg-opacity-25 text-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i> Aprobado</span>
                            @elseif($r->estado === 'Pendiente')
                                <span class="badge bg-warning bg-opacity-25 text-warning px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split me-1"></i> Pendiente</span>
                            @else
                                <span class="badge bg-danger bg-opacity-25 text-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle me-1"></i> Rechazado</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    @if($r->estado === 'Pendiente')
                                    <li>
                                        <form method="POST" action="{{ route('retiros.resolver', $r->id_retiro) }}">
                                            @csrf
                                            <input type="hidden" name="estado" value="Aprobado">
                                            <button type="submit" class="dropdown-item text-success"><i class="bi bi-check-circle me-2"></i> Aprobar Trámite</button>
                                        </form>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('retiros.resolver', $r->id_retiro) }}">
                                            @csrf
                                            <input type="hidden" name="estado" value="Rechazado">
                                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-x-circle me-2"></i> Rechazar Trámite</button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    @endif
                                    <li>
                                        <form method="POST" action="{{ route('retiros.destroy', $r->id_retiro) }}" onsubmit="return confirm('¿Eliminar este registro de solicitud?');">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i> Eliminar Registro</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-arrow-left-right fs-1 d-block mb-2"></i>
                                No hay trámites de retiro o cambio de asignatura registrados.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($retiros->hasPages())
        <div class="mt-4 d-flex justify-content-end">
            {{ $retiros->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
