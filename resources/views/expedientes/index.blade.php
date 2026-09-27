@extends('layouts.app')

@section('title', 'Expedientes Digitales 360° - ' . config('cliente.nombre', 'CEFI'))

@section('styles')
<style>
    .exp-container {
        padding-top: 1.5rem;
        padding-bottom: 3.5rem;
    }

    .stat-card-item {
        background-color: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        padding: 1.25rem 1.5rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        height: 100%;
    }

    .stat-card-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    .card-kpi-label {
        color: #ffffff !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        opacity: 0.95;
    }

    .page-subtitle {
        color: #e2e8f0 !important;
        font-size: 0.95rem;
    }

    .card-panel {
        background-color: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .filter-input-addon {
        background-color: var(--card-dark) !important;
        border-color: var(--border-dark) !important;
        color: #cbd5e1 !important;
    }

    .filter-input, .filter-select {
        background-color: var(--card-dark) !important;
        border-color: var(--border-dark) !important;
        color: #ffffff !important;
    }

    .filter-input::placeholder {
        color: #94a3b8 !important;
    }

    .table-custom {
        --bs-table-bg: transparent !important;
        --bs-table-color: var(--text-light) !important;
        --bs-table-border-color: var(--border-dark) !important;
        --bs-table-hover-bg: rgba(95, 178, 48, 0.05) !important;
        --bs-table-hover-color: var(--text-light) !important;
        color: var(--text-light) !important;
        background-color: transparent !important;
    }

    .table-custom th, .table-custom td {
        background-color: transparent !important;
        color: inherit !important;
        border-color: var(--border-dark) !important;
    }

    .table-header-row th {
        color: #ffffff !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        background-color: rgba(255, 255, 255, 0.05) !important;
        border-bottom: 1px solid var(--border-dark) !important;
    }

    .empty-title {
        color: #ffffff !important;
    }

    .empty-subtitle {
        color: #e2e8f0 !important;
        font-size: 0.9rem;
    }

    /* Light theme adaptaciones */
    [data-theme="light"] .card-kpi-label {
        color: #475569 !important;
    }

    [data-theme="light"] .page-subtitle {
        color: #64748b !important;
    }

    [data-theme="light"] .filter-input-addon {
        color: #64748b !important;
    }

    [data-theme="light"] .filter-input, [data-theme="light"] .filter-select {
        color: #0f172a !important;
    }

    [data-theme="light"] .table-header-row th {
        color: #475569 !important;
        background-color: rgba(0, 0, 0, 0.02) !important;
    }

    [data-theme="light"] .empty-title {
        color: #0f172a !important;
    }

    [data-theme="light"] .empty-subtitle {
        color: #64748b !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-md-5 exp-container">
    <!-- Encabezado con Botón de Creación -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h2 fw-bold text-white mb-1">
                <i class="bi bi-folder-symlink-fill text-primary me-2"></i>Expedientes Digitales 360°
            </h1>
            <p class="page-subtitle mb-0">Gestión integral de expedientes estudiantiles, récord académico, finanzas y bóveda documental.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('expedientes.create') }}" class="btn btn-primary px-4 py-2 fw-bold rounded-pill shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> Crear Expediente
            </a>
        </div>
    </div>

    <!-- Tarjetas de Métricas KPI -->
    <div class="row g-3 mb-4">
        <!-- Total Expedientes -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-item border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="card-kpi-label d-block">Total Expedientes</span>
                        <h3 class="mb-0 fw-bold mt-1" style="color: #60a5fa;">{{ $totalExpedientes }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: rgba(59, 130, 246, 0.15);">
                        <i class="bi bi-folder2-open fs-3" style="color: #60a5fa;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formalizados / Aprobados -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-item border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="card-kpi-label d-block">Formalizados / Aprobados</span>
                        <h3 class="mb-0 fw-bold mt-1" style="color: #34d399;">{{ $totalAprobados }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: rgba(16, 185, 129, 0.15);">
                        <i class="bi bi-check-circle-fill fs-3" style="color: #34d399;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- En Revisión / Pendientes -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-item border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="card-kpi-label d-block">En Revisión / Pendientes</span>
                        <h3 class="mb-0 fw-bold mt-1" style="color: #fbbf24;">{{ $totalPendientes }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: rgba(245, 158, 11, 0.18);">
                        <i class="bi bi-clock-history fs-3" style="color: #fbbf24;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rechazados / Incompletos -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-item border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="card-kpi-label d-block">Rechazados / Incompletos</span>
                        <h3 class="mb-0 fw-bold mt-1" style="color: #f87171;">{{ $totalRechazados }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: rgba(239, 68, 68, 0.15);">
                        <i class="bi bi-x-circle-fill fs-3" style="color: #f87171;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Barra de Búsqueda -->
    <div class="card-panel p-3 mb-4">
        <form method="GET" action="{{ route('expedientes.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text border-end-0 filter-input-addon"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ $busqueda }}" class="form-control border-start-0 filter-input" placeholder="Buscar por nombre, cédula, correo o carrera...">
                </div>
            </div>
            <div class="col-md-4">
                <select name="estado" class="form-select filter-select" onchange="this.form.submit()">
                    <option value="" {{ $estadoFiltro === '' ? 'selected' : '' }}>Todos los estados</option>
                    <option value="Aprobado" {{ $estadoFiltro === 'Aprobado' ? 'selected' : '' }}>Aprobados / Formalizados</option>
                    <option value="Pendiente" {{ $estadoFiltro === 'Pendiente' ? 'selected' : '' }}>Pendientes de Revisión</option>
                    <option value="Rechazado" {{ $estadoFiltro === 'Rechazado' ? 'selected' : '' }}>Rechazados</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4 w-100 fw-semibold rounded-pill d-flex align-items-center justify-content-center gap-1 shadow-sm">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                @if(!empty($busqueda) || !empty($estadoFiltro))
                    <a href="{{ route('expedientes.index') }}" class="btn btn-outline-secondary rounded-pill d-flex align-items-center justify-content-center px-3" title="Limpiar Filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla Principal de Expedientes -->
    <div class="card-panel overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-custom">
                <thead class="table-header-row">
                    <tr>
                        <th class="ps-4 py-3">ID</th>
                        <th class="py-3">Estudiante</th>
                        <th class="py-3">Identificación</th>
                        <th class="py-3">Programa / Grado</th>
                        <th class="py-3 text-center">Completitud</th>
                        <th class="py-3 text-center">Estado</th>
                        <th class="pe-4 py-3 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expedientes as $exp)
                    @php
                        $user = $exp->usuario;
                        $porc = $exp->porcentaje_completitud;
                        $colorPorc = $porc >= 80 ? 'bg-success' : ($porc >= 50 ? 'bg-warning' : 'bg-danger');
                    @endphp
                    <tr>
                        <td class="ps-4 fw-bold" style="font-family: monospace; color: #cbd5e1;">
                            #{{ str_pad($exp->id_expediente, 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size: 0.95rem;">
                                    {{ mb_substr($user->nombre ?? 'E', 0, 1) }}{{ mb_substr($user->apellidos ?? '', 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold" style="color: var(--text-light);">
                                        {{ $user ? "{$user->apellidos}, {$user->nombre}" : 'Sin usuario asociado' }}
                                    </div>
                                    <div class="small" style="color: #cbd5e1;">{{ $user->email ?? 'N/D' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-25 text-light px-2 py-1 rounded-2 font-monospace">
                                {{ $user->cedula ?? $exp->cedula_residencia ?? $exp->pasaporte ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <div class="small fw-semibold" style="color: var(--text-light);">{{ $exp->especialidad_deseada ?: 'Programa General' }}</div>
                            <div class="small" style="color: #cbd5e1;">{{ $exp->grado_a_matricular ?: 'N/D' }}</div>
                        </td>
                        <td class="text-center" style="min-width: 130px;">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px; background-color: rgba(125,125,125,0.2); border-radius: 4px;">
                                    <div class="progress-bar {{ $colorPorc }}" role="progressbar" style="width: {{ $porc }}%;"></div>
                                </div>
                                <span class="small fw-bold" style="color: #cbd5e1;">{{ $porc }}%</span>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($exp->estado === 'Aprobado')
                                <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i> Aprobado
                                </span>
                            @elseif($exp->estado === 'Rechazado')
                                <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1">
                                    <i class="bi bi-x-circle-fill me-1"></i> Rechazado
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-1">
                                    <i class="bi bi-clock-fill me-1"></i> Pendiente
                                </span>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('expedientes.ver', $exp->id_expediente) }}" class="btn btn-sm btn-outline-success px-3 py-1 fw-semibold rounded-pill d-inline-flex align-items-center gap-1">
                                <span>Ver Expediente 360°</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="p-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background: rgba(95, 178, 48, 0.15);">
                                <i class="bi bi-folder2-open text-primary fs-1"></i>
                            </div>
                            <h5 class="fw-bold mb-1 empty-title">No se encontraron expedientes digitales</h5>
                            <p class="empty-subtitle mb-3">Comience registrando un nuevo expediente o ajuste los filtros de búsqueda.</p>
                            <a href="{{ route('expedientes.create') }}" class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                                <i class="bi bi-plus-lg me-1"></i> Crear Primer Expediente
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($expedientes->hasPages())
        <div class="p-3 border-top border-secondary border-opacity-25 d-flex justify-content-end">
            {{ $expedientes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
