@extends('layouts.app')

@section('title', 'Expedientes Digitales 360° - ' . config('cliente.nombre', 'CEFI'))

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado con Botón de Creación -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold text-white mb-1">
                <i class="bi bi-folder-symlink-fill text-success me-2"></i>Expedientes Digitales 360°
            </h2>
            <p class="text-muted mb-0">Gestión integral de expedientes estudiantiles, récord académico, finanzas y bóveda documental.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('expedientes.create') }}" class="btn btn-success px-4 py-2 fw-semibold rounded-3 shadow-sm d-flex align-items-center gap-2" style="background-color: var(--primary, #5fb230); border: none;">
                <i class="bi bi-person-plus-fill"></i> Crear Expediente
            </a>
        </div>
    </div>

    <!-- Tarjetas de Métricas KPI -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-dark text-white border-0 shadow-sm p-3 h-100 rounded-3" style="background: rgba(30, 41, 59, 0.7) !important; border-left: 4px solid #3b82f6 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Total Expedientes</div>
                        <h3 class="fw-bold text-white mb-0 mt-1">{{ $totalExpedientes }}</h3>
                    </div>
                    <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary fs-3">
                        <i class="bi bi-folder2-open"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-dark text-white border-0 shadow-sm p-3 h-100 rounded-3" style="background: rgba(30, 41, 59, 0.7) !important; border-left: 4px solid #10b981 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Formalizados / Aprobados</div>
                        <h3 class="fw-bold text-white mb-0 mt-1">{{ $totalAprobados }}</h3>
                    </div>
                    <div class="p-3 rounded-circle bg-success bg-opacity-10 text-success fs-3">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-dark text-white border-0 shadow-sm p-3 h-100 rounded-3" style="background: rgba(30, 41, 59, 0.7) !important; border-left: 4px solid #f59e0b !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">En Revisión / Pendientes</div>
                        <h3 class="fw-bold text-white mb-0 mt-1">{{ $totalPendientes }}</h3>
                    </div>
                    <div class="p-3 rounded-circle bg-warning bg-opacity-10 text-warning fs-3">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-dark text-white border-0 shadow-sm p-3 h-100 rounded-3" style="background: rgba(30, 41, 59, 0.7) !important; border-left: 4px solid #ef4444 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Rechazados / Incompletos</div>
                        <h3 class="fw-bold text-white mb-0 mt-1">{{ $totalRechazados }}</h3>
                    </div>
                    <div class="p-3 rounded-circle bg-danger bg-opacity-10 text-danger fs-3">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Barra de Búsqueda -->
    <div class="card border-0 shadow-sm p-3 mb-4 rounded-3" style="background: rgba(30, 41, 59, 0.7);">
        <form method="GET" action="{{ route('expedientes.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-secondary text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ $busqueda }}" class="form-control bg-transparent border-secondary text-white" placeholder="Buscar por nombre, cédula, correo o carrera...">
                </div>
            </div>
            <div class="col-md-4">
                <select name="estado" class="form-select bg-transparent border-secondary text-white" onchange="this.form.submit()">
                    <option value="" class="bg-dark text-white" {{ $estadoFiltro === '' ? 'selected' : '' }}>Todos los estados</option>
                    <option value="Aprobado" class="bg-dark text-white" {{ $estadoFiltro === 'Aprobado' ? 'selected' : '' }}>Aprobados / Formalizados</option>
                    <option value="Pendiente" class="bg-dark text-white" {{ $estadoFiltro === 'Pendiente' ? 'selected' : '' }}>Pendientes de Revisión</option>
                    <option value="Rechazado" class="bg-dark text-white" {{ $estadoFiltro === 'Rechazado' ? 'selected' : '' }}>Rechazados</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4 w-100 fw-semibold rounded-3">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                @if(!empty($busqueda) || !empty($estadoFiltro))
                    <a href="{{ route('expedientes.index') }}" class="btn btn-outline-secondary text-muted" title="Limpiar Filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla Principal de Expedientes -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden" style="background: rgba(30, 41, 59, 0.7);">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-white" style="border-color: rgba(255, 255, 255, 0.08);">
                <thead style="background: rgba(15, 23, 42, 0.6); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3 text-muted">ID</th>
                        <th class="py-3 text-muted">Estudiante</th>
                        <th class="py-3 text-muted">Identificación</th>
                        <th class="py-3 text-muted">Programa / Grado</th>
                        <th class="py-3 text-muted text-center">Completitud</th>
                        <th class="py-3 text-muted text-center">Estado</th>
                        <th class="pe-4 py-3 text-end text-muted">Acciones</th>
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
                        <td class="ps-4 fw-bold text-muted" style="font-family: monospace;">
                            #{{ str_pad($exp->id_expediente, 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size: 0.95rem;">
                                    {{ mb_substr($user->nombre ?? 'E', 0, 1) }}{{ mb_substr($user->apellidos ?? '', 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-white">
                                        {{ $user ? "{$user->apellidos}, {$user->nombre}" : 'Sin usuario asociado' }}
                                    </div>
                                    <div class="small text-muted">{{ $user->email ?? 'N/D' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-25 text-light px-2 py-1 rounded-2 font-monospace">
                                {{ $user->cedula ?? $exp->cedula_residencia ?? $exp->pasaporte ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <div class="text-white small fw-semibold">{{ $exp->especialidad_deseada ?: 'Programa General' }}</div>
                            <div class="text-muted small">{{ $exp->grado_a_matricular ?: 'N/D' }}</div>
                        </td>
                        <td class="text-center" style="min-width: 130px;">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px; background-color: rgba(255,255,255,0.1); border-radius: 4px;">
                                    <div class="progress-bar {{ $colorPorc }}" role="progressbar" style="width: {{ $porc }}%;"></div>
                                </div>
                                <span class="small text-muted fw-bold">{{ $porc }}%</span>
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
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="fs-1 mb-2"><i class="bi bi-inbox"></i></div>
                            <div class="fw-semibold">No se encontraron expedientes digitales.</div>
                            <small>Ajuste los criterios de búsqueda o cree un nuevo expediente.</small>
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
