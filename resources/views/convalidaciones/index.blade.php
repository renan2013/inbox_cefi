@extends('layouts.app')

@section('title', 'Convalidaciones y Homologaciones Académicas - ' . config('cliente.nombre', 'CEFI'))

@section('styles')
<style>
    .page-header-custom {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%);
        border: 1px solid var(--border-dark, #334155);
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
        background: var(--card-dark, #1e293b);
        border: 1px solid var(--border-dark, #334155);
        border-radius: 1.25rem;
        padding: 1.35rem 1.4rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
    }
    [data-theme="light"] .kpi-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
    }
    .kpi-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        flex-shrink: 0;
    }
    .kpi-icon-total {
        background: rgba(95, 178, 48, 0.12) !important;
        color: #5fb230 !important;
        border: 1px solid rgba(95, 178, 48, 0.28) !important;
    }
    .kpi-icon-approved {
        background: rgba(16, 185, 129, 0.12) !important;
        color: #10b981 !important;
        border: 1px solid rgba(16, 185, 129, 0.28) !important;
    }
    .kpi-icon-pending {
        background: rgba(245, 158, 11, 0.12) !important;
        color: #f59e0b !important;
        border: 1px solid rgba(245, 158, 11, 0.28) !important;
    }
    .kpi-icon-credits {
        background: rgba(99, 102, 241, 0.12) !important;
        color: #6366f1 !important;
        border: 1px solid rgba(99, 102, 241, 0.28) !important;
    }
    .btn-cefi-primary {
        background: linear-gradient(135deg, #5fb230 0%, #4a9423 100%) !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 700;
        padding: 0.65rem 1.5rem;
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
    .table-container-card {
        background: var(--card-dark, #1e293b);
        border: 1px solid var(--border-dark, #334155);
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
        background: var(--card-dark, #1e293b);
        border: 1px solid var(--border-dark, #334155);
        border-radius: 1rem;
        padding: 1.2rem;
        margin-bottom: 1.5rem;
    }
    [data-theme="light"] .filter-card {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
    }
    .student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.85rem;
        background: #f1f5f9;
        color: #1e293b;
        border: 1px solid #cbd5e1;
        flex-shrink: 0;
    }
    [data-theme="dark"] .student-avatar {
        background: #0f172a;
        color: #f8fafc;
        border-color: #334155;
    }
    .custom-note-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        margin-top: 4px;
    }
    [data-theme="dark"] .custom-note-tag {
        background: rgba(255, 255, 255, 0.06);
        color: #94a3b8;
        border-color: #334155;
    }
    .badge-course-code {
        display: inline-flex;
        align-items: center;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        background: rgba(95, 178, 48, 0.12);
        color: #5fb230;
        border: 1px solid rgba(95, 178, 48, 0.28);
        font-family: var(--bs-font-monospace, monospace);
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.85rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 700;
    }
    .status-pill.approved {
        background: rgba(16, 185, 129, 0.12);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    .status-pill.pending {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }
    .status-pill.rejected {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    .badge-pdf-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.65rem;
        border-radius: 6px;
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.25);
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .badge-pdf-link:hover {
        background: rgba(239, 68, 68, 0.2);
        color: #b91c1c;
    }
    .badge-expediente {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        background: rgba(100, 116, 139, 0.12);
        color: #64748b;
        border: 1px solid rgba(100, 116, 139, 0.25);
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .badge-expediente:hover {
        background: rgba(100, 116, 139, 0.22);
        color: #334155;
    }
    [data-theme="dark"] .badge-expediente {
        color: #94a3b8;
        border-color: #334155;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Header -->
    <div class="page-header-custom d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="badge bg-success bg-opacity-25 text-success px-3 py-2 rounded-pill fw-bold fs-7 border border-success-subtle">
                    <i class="bi bi-patch-check-fill me-1"></i> Fase 4: Control Académico
                </div>
                <span class="text-muted small">CEFI - Gestión Curricular</span>
            </div>
            <h2 class="h3 fw-bold mb-1">Convalidaciones & Homologaciones</h2>
            <p class="text-muted mb-0 small">Reconocimiento oficial de asignaturas cursadas en otras instituciones universitarias o cambios internos de carrera.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('convalidaciones.create') }}" class="btn-cefi-primary">
                <i class="bi bi-plus-circle me-1"></i> Nueva Convalidación
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

    <!-- KPIs con Iconos Referenciales y Estilo Sofisticado -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card d-flex align-items-center gap-3">
                <div class="kpi-icon-box kpi-icon-total">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <div>
                    <span class="text-muted small d-block fw-semibold">Total Convalidaciones</span>
                    <h3 class="fw-bold mb-0">{{ number_format($kpiTotal) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card d-flex align-items-center gap-3">
                <div class="kpi-icon-box kpi-icon-approved">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <div>
                    <span class="text-muted small d-block fw-semibold">Aprobadas Oficialmente</span>
                    <h3 class="fw-bold mb-0 text-success">{{ number_format($kpiAprobadas) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card d-flex align-items-center gap-3">
                <div class="kpi-icon-box kpi-icon-pending">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <span class="text-muted small d-block fw-semibold">En Revisión / Pendientes</span>
                    <h3 class="fw-bold mb-0 text-warning">{{ number_format($kpiPendientes) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card d-flex align-items-center gap-3">
                <div class="kpi-icon-box kpi-icon-credits">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div>
                    <span class="text-muted small d-block fw-semibold">Créditos Reconocidos</span>
                    <h3 class="fw-bold mb-0" style="color: #6366f1;">{{ number_format($kpiCreditos, 1) }} cr.</h3>
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
                <button type="submit" class="btn-cefi-primary px-3 py-2 rounded-3 w-100" title="Filtrar"><i class="bi bi-funnel-fill"></i></button>
                @if($busqueda || $estadoFiltro || $programaFiltro)
                    <a href="{{ route('convalidaciones.index') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2" title="Limpiar"><i class="bi bi-x-lg"></i></a>
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
                            <div class="d-flex align-items-center gap-3">
                                <div class="student-avatar">
                                    {{ strtoupper(substr($c->estudiante ? $c->estudiante->nombre : 'U', 0, 1)) }}{{ strtoupper(substr($c->estudiante ? $c->estudiante->apellidos : '', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $c->estudiante ? "{$c->estudiante->nombre} {$c->estudiante->apellidos}" : 'N/D' }}</div>
                                    <span class="text-muted small">{{ $c->estudiante ? $c->estudiante->cedula : 'Sin cédula' }}</span>
                                    @if($c->expediente)
                                        <a href="{{ route('expedientes.ver', $c->expediente->id_expediente) }}" class="badge-expediente ms-1" title="Ver Expediente 360°">
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
                                <span class="custom-note-tag"><i class="bi bi-journal-bookmark me-1"></i> Nota origen: <strong>{{ $c->calificacion_origen }}</strong></span>
                            @endif
                        </td>
                        <td>
                            @if($c->planEstudio)
                                <div class="fw-bold">{{ $c->planEstudio->materia }}</div>
                                <span class="badge-course-code"><i class="bi bi-tag-fill me-1"></i>{{ $c->planEstudio->codigo ?: 'S/C' }}</span>
                            @else
                                <span class="text-muted fst-italic small">Créditos de libre configuración / No asignado</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold font-monospace fs-6">{{ number_format($c->creditos_convalidados, 1) }} cr.</div>
                            <span class="text-muted small">{{ $c->tipo_convalidacion }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $c->numero_resolucion ?: 'En trámite' }}</div>
                            <span class="text-muted small d-block">{{ $c->fecha_resolucion ? \Carbon\Carbon::parse($c->fecha_resolucion)->format('d/m/Y') : 'Pendiente' }}</span>
                            @if($c->archivo_resolucion)
                                <div class="mt-1">
                                    <a href="{{ asset('storage/' . $c->archivo_resolucion) }}" target="_blank" class="badge-pdf-link">
                                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Ver Acta / PDF
                                    </a>
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($c->estado === 'Aprobada')
                                <span class="status-pill approved"><i class="bi bi-check-circle-fill"></i> Aprobada</span>
                            @elseif($c->estado === 'Pendiente')
                                <span class="status-pill pending"><i class="bi bi-clock-history"></i> Pendiente</span>
                            @else
                                <span class="status-pill rejected"><i class="bi bi-x-circle-fill"></i> Rechazada</span>
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
                                <i class="bi bi-patch-question fs-1 d-block mb-2 text-success opacity-75"></i>
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
