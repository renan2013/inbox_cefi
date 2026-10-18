@extends('layouts.app')

@section('title', 'Padrón de Graduaciones y Auditoría - ' . config('cliente.nombre', 'CEFI'))

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
                <div class="badge bg-primary bg-opacity-25 text-primary px-3 py-2 rounded-pill fw-bold fs-7">
                    <i class="bi bi-award-fill me-1"></i> Fase 4: Control Académico
                </div>
                <span class="text-muted small">CEFI - Culminación de Estudios</span>
            </div>
            <h2 class="h3 fw-bold mb-1">Padrón de Graduaciones & Auditoría Académica</h2>
            <p class="text-muted mb-0 small">Verificación integral de requisitos (Plan de estudios, TCU, Finanzas, Expediente) y emisión de dictámenes oficiales de grado.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('graduaciones.create') }}" class="btn btn-primary fw-semibold px-4 py-2 rounded-pill shadow-sm">
                <i class="bi bi-person-plus-fill me-2"></i> Postular Graduando
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
                    <i class="bi bi-mortarboard"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Total Postulados</span>
                    <h3 class="fw-bold mb-0">{{ number_format($kpiTotal) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-success bg-opacity-15 text-success fs-3">
                    <i class="bi bi-patch-check"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Aprobados / Graduados</span>
                    <h3 class="fw-bold mb-0 text-success">{{ number_format($kpiAprobados) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-warning bg-opacity-15 text-warning fs-3">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">En Auditoría Activa</span>
                    <h3 class="fw-bold mb-0 text-warning">{{ number_format($kpiAuditoria) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-info bg-opacity-15 text-info fs-3">
                    <i class="bi bi-stars"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Menciones de Honor</span>
                    <h3 class="fw-bold mb-0 text-info">{{ number_format($kpiCumLaude) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filter-card">
        <form method="GET" action="{{ route('graduaciones.index') }}" class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ $busqueda }}" class="form-control border-start-0" placeholder="Buscar por graduando, carrera, título o convocatoria...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="estado" class="form-select">
                    <option value="">Todos los Estados</option>
                    <option value="En Auditoría" {{ $estadoFiltro === 'En Auditoría' ? 'selected' : '' }}>En Auditoría</option>
                    <option value="Aprobado para Graduación" {{ $estadoFiltro === 'Aprobado para Graduación' ? 'selected' : '' }}>Aprobado para Graduación</option>
                    <option value="Graduado" {{ $estadoFiltro === 'Graduado' ? 'selected' : '' }}>Graduado</option>
                    <option value="Rechazado" {{ $estadoFiltro === 'Rechazado' ? 'selected' : '' }}>Rechazado</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="convocatoria" class="form-select">
                    <option value="">Todas las Convocatorias</option>
                    @foreach($convocatorias as $conv)
                        <option value="{{ $conv }}" {{ $convocatoriaFiltro === $conv ? 'selected' : '' }}>{{ $conv }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
                @if($busqueda || $estadoFiltro || $convocatoriaFiltro)
                    <a href="{{ route('graduaciones.index') }}" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-x-lg"></i></a>
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
                        <th>Graduando / Cédula</th>
                        <th>Programa Académico</th>
                        <th>Semáforo de Requisitos</th>
                        <th>Promedio & Honor</th>
                        <th>Libro / Título</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($graduaciones as $g)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $g->estudiante ? "{$g->estudiante->nombre} {$g->estudiante->apellidos}" : 'N/D' }}</div>
                            <span class="text-muted small">{{ $g->estudiante ? $g->estudiante->cedula : '' }}</span>
                            <div class="text-muted small mt-1 font-monospace"><i class="bi bi-calendar3 me-1"></i>{{ $g->convocatoria }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-primary">{{ $g->programa ? $g->programa->nombre_programa : 'N/D' }}</div>
                            <span class="text-muted small">{{ $g->total_creditos_aprobados ? number_format($g->total_creditos_aprobados, 1) . ' cr. aprobados' : '' }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $g->cumplio_plan_estudios ? 'bg-success' : 'bg-danger' }} rounded-circle p-1" title="Plan de Estudios: {{ $g->cumplio_plan_estudios ? 'Aprobado' : 'Pendiente' }}" style="width: 14px; height: 14px; display: inline-block;"></span>
                                <span class="badge {{ $g->cumplio_tcu ? 'bg-success' : 'bg-danger' }} rounded-circle p-1" title="TCU: {{ $g->cumplio_tcu ? 'Aprobado' : 'Pendiente' }}" style="width: 14px; height: 14px; display: inline-block;"></span>
                                <span class="badge {{ $g->cumplio_financiero ? 'bg-success' : 'bg-danger' }} rounded-circle p-1" title="Finanzas: {{ $g->cumplio_financiero ? 'Al día' : 'Pendiente' }}" style="width: 14px; height: 14px; display: inline-block;"></span>
                                <span class="badge {{ $g->cumplio_expediente ? 'bg-success' : 'bg-danger' }} rounded-circle p-1" title="Expediente: {{ $g->cumplio_expediente ? 'Completo' : 'Pendiente' }}" style="width: 14px; height: 14px; display: inline-block;"></span>
                            </div>
                            <a href="{{ route('graduaciones.auditar', $g->id_graduacion) }}" class="small text-decoration-none d-block mt-1">
                                Ver Auditoría 360° <i class="bi bi-chevron-right"></i>
                            </a>
                        </td>
                        <td>
                            <div class="fw-bold font-monospace fs-6">{{ number_format($g->promedio_ponderado_final, 2) }} / 100</div>
                            @if($g->mencion_honor && $g->mencion_honor !== 'Ninguna')
                                <span class="badge bg-warning bg-opacity-25 text-warning fw-semibold px-2 py-1"><i class="bi bi-star-fill me-1"></i>{{ $g->mencion_honor }}</span>
                            @else
                                <span class="text-muted small">Sin mención</span>
                            @endif
                        </td>
                        <td>
                            <div class="small">Título: <strong>{{ $g->numero_titulo ?: 'Pendiente' }}</strong></div>
                            <span class="text-muted small">Acta: {{ $g->numero_acta ?: 'N/D' }} | Tomo {{ $g->numero_tomo ?: '-' }} | Folio {{ $g->numero_folio ?: '-' }}</span>
                        </td>
                        <td>
                            @if($g->estado === 'Graduado' || $g->estado === 'Aprobado para Graduación')
                                <span class="badge bg-success bg-opacity-25 text-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i> {{ $g->estado }}</span>
                            @elseif($g->estado === 'En Auditoría')
                                <span class="badge bg-warning bg-opacity-25 text-warning px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split me-1"></i> En Auditoría</span>
                            @else
                                <span class="badge bg-danger bg-opacity-25 text-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle me-1"></i> {{ $g->estado }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('graduaciones.auditar', $g->id_graduacion) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Auditar Requisitos">
                                    <i class="bi bi-clipboard2-check me-1"></i> Auditar
                                </a>
                                <a href="{{ route('graduaciones.dictamen_pdf', $g->id_graduacion) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Dictamen Oficial PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-award fs-1 d-block mb-2"></i>
                                No hay estudiantes postulados para graduación actualmente.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($graduaciones->hasPages())
        <div class="mt-4 d-flex justify-content-end">
            {{ $graduaciones->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
