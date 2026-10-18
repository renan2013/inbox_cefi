@extends('layouts.app')

@section('title', 'Convalidaciones y Homologaciones Académicas - ' . config('cliente.nombre', 'CEFI'))

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
                <div class="badge bg-success bg-opacity-25 text-success px-3 py-2 rounded-pill fw-bold fs-7">
                    <i class="bi bi-patch-check-fill me-1"></i> Fase 4: Control Académico
                </div>
                <span class="text-muted small">CEFI - Gestión Curricular</span>
            </div>
            <h2 class="h3 fw-bold mb-1">Convalidaciones & Homologaciones</h2>
            <p class="text-muted mb-0 small">Reconocimiento oficial de asignaturas cursadas en otras instituciones universitarias o cambios internos de carrera.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('convalidaciones.create') }}" class="btn btn-primary fw-semibold px-4 py-2 rounded-pill shadow-sm">
                <i class="bi bi-plus-circle me-2"></i> Nueva Convalidación
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
                    <i class="bi bi-journals"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Total Convalidaciones</span>
                    <h3 class="fw-bold mb-0">{{ number_format($kpiTotal) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-success bg-opacity-15 text-success fs-3">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Aprobadas Oficialmente</span>
                    <h3 class="fw-bold mb-0 text-success">{{ number_format($kpiAprobadas) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-warning bg-opacity-15 text-warning fs-3">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">En Revisión / Pendientes</span>
                    <h3 class="fw-bold mb-0 text-warning">{{ number_format($kpiPendientes) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-info bg-opacity-15 text-info fs-3">
                    <i class="bi bi-award"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Créditos Reconocidos</span>
                    <h3 class="fw-bold mb-0 text-info">{{ number_format($kpiCreditos, 1) }} cr.</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de búsqueda -->
    <div class="filter-card">
        <form method="GET" action="{{ route('convalidaciones.index') }}" class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ $busqueda }}" class="form-control border-start-0" placeholder="Buscar por estudiante, materia, universidad o resolución...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="estado" class="form-select">
                    <option value="">Todos los Estados</option>
                    <option value="Aprobada" {{ $estadoFiltro === 'Aprobada' ? 'selected' : '' }}>Aprobada</option>
                    <option value="Pendiente" {{ $estadoFiltro === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="Rechazada" {{ $estadoFiltro === 'Rechazada' ? 'selected' : '' }}>Rechazada</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="id_programa" class="form-select">
                    <option value="">Todos los Programas</option>
                    @foreach($programas as $prog)
                        <option value="{{ $prog->id_programa }}" {{ $programaFiltro == $prog->id_programa ? 'selected' : '' }}>{{ $prog->nombre_programa }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
                @if($busqueda || $estadoFiltro || $programaFiltro)
                    <a href="{{ route('convalidaciones.index') }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-x-lg"></i></a>
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
                        <th>Estudiante / ID</th>
                        <th>Institución & Materia de Origen</th>
                        <th>Equivalencia CEFI</th>
                        <th>Créditos Reconocidos</th>
                        <th>Resolución & Fecha</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($convalidaciones as $c)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary bg-opacity-20 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 0.85rem;">
                                    {{ strtoupper(substr($c->estudiante ? $c->estudiante->nombre : 'U', 0, 1)) }}{{ strtoupper(substr($c->estudiante ? $c->estudiante->apellidos : '', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $c->estudiante ? "{$c->estudiante->nombre} {$c->estudiante->apellidos}" : 'N/D' }}</div>
                                    <span class="text-muted small">{{ $c->estudiante ? $c->estudiante->cedula : 'Sin cédula' }}</span>
                                    @if($c->expediente)
                                        <a href="{{ route('expedientes.ver', $c->expediente->id_expediente) }}" class="badge bg-secondary bg-opacity-25 text-secondary text-decoration-none ms-1" title="Ver Expediente 360°">
                                            <i class="bi bi-folder-symlink"></i> Exp #{{ $c->expediente->id_expediente }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $c->materia_origen }}</div>
                            <span class="text-muted small d-block"><i class="bi bi-building me-1"></i>{{ $c->institucion_origen }}</span>
                            @if($c->calificacion_origen)
                                <span class="badge bg-info bg-opacity-15 text-info font-monospace small">Nota origen: {{ $c->calificacion_origen }}</span>
                            @endif
                        </td>
                        <td>
                            @if($c->planEstudio)
                                <div class="fw-semibold text-primary">{{ $c->planEstudio->materia }}</div>
                                <span class="badge bg-secondary bg-opacity-25 text-secondary font-monospace small">{{ $c->planEstudio->codigo ?: 'S/C' }}</span>
                            @else
                                <span class="text-muted italic">Créditos de libre configuración / No asignado</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold font-monospace fs-6">{{ number_format($c->creditos_convalidados, 1) }} cr.</div>
                            <span class="text-muted small">{{ $c->tipo_convalidacion }}</span>
                        </td>
                        <td>
                            <div>{{ $c->numero_resolucion ?: 'En trámite' }}</div>
                            <span class="text-muted small">{{ $c->fecha_resolucion ? \Carbon\Carbon::parse($c->fecha_resolucion)->format('d/m/Y') : 'Pendiente' }}</span>
                            @if($c->archivo_resolucion)
                                <div class="mt-1">
                                    <a href="{{ asset('storage/' . $c->archivo_resolucion) }}" target="_blank" class="badge bg-primary bg-opacity-20 text-primary text-decoration-none">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Ver Acta / PDF
                                    </a>
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($c->estado === 'Aprobada')
                                <span class="badge bg-success bg-opacity-25 text-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i> Aprobada</span>
                            @elseif($c->estado === 'Pendiente')
                                <span class="badge bg-warning bg-opacity-25 text-warning px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split me-1"></i> Pendiente</span>
                            @else
                                <span class="badge bg-danger bg-opacity-25 text-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle me-1"></i> Rechazada</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    @if($c->estado !== 'Aprobada')
                                    <li>
                                        <form method="POST" action="{{ route('convalidaciones.cambiar_estado', $c->id_convalidacion) }}">
                                            @csrf
                                            <input type="hidden" name="estado" value="Aprobada">
                                            <button type="submit" class="dropdown-item text-success"><i class="bi bi-check-circle me-2"></i> Aprobar Convalidación</button>
                                        </form>
                                    </li>
                                    @endif
                                    @if($c->estado !== 'Rechazada')
                                    <li>
                                        <form method="POST" action="{{ route('convalidaciones.cambiar_estado', $c->id_convalidacion) }}">
                                            @csrf
                                            <input type="hidden" name="estado" value="Rechazada">
                                            <button type="submit" class="dropdown-item text-warning"><i class="bi bi-x-circle me-2"></i> Marcar Rechazada</button>
                                        </form>
                                    </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('convalidaciones.destroy', $c->id_convalidacion) }}" onsubmit="return confirm('¿Confirma que desea eliminar este registro de convalidación?');">
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
                                <i class="bi bi-patch-question fs-1 d-block mb-2"></i>
                                No se encontraron registros de convalidación académica registrados.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($convalidaciones->hasPages())
        <div class="mt-4 d-flex justify-content-end">
            {{ $convalidaciones->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
