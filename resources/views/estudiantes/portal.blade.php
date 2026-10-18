@extends('layouts.app')

@section('title', 'Portal del Estudiante - ' . config('cliente.nombre', 'CEFI'))

@section('styles')
<style>
    .student-hero-banner {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.98) 0%, rgba(15, 23, 42, 0.95) 100%);
        border: 1px solid var(--border-dark);
        border-radius: 1.75rem;
        padding: 2.2rem 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
        position: relative;
        overflow: hidden;
    }
    .student-hero-banner::after {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(95, 178, 48, 0.18) 0%, transparent 70%);
        pointer-events: none;
    }
    [data-theme="light"] .student-hero-banner {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05) !important;
    }
    .kpi-student-card {
        background: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        padding: 1.3rem 1.5rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kpi-student-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
    }
    [data-theme="light"] .kpi-student-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
    }
    .course-card-custom {
        background: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        padding: 1.5rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.2s;
    }
    .course-card-custom:hover {
        transform: translateY(-3px);
    }
    [data-theme="light"] .course-card-custom {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
    }
    .content-tab-card {
        background: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.5rem;
        padding: 2rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }
    [data-theme="light"] .content-tab-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
    }
    .nav-pills-student .nav-link {
        color: var(--text-muted);
        font-weight: 600;
        border-radius: 50rem;
        padding: 0.65rem 1.4rem;
        transition: all 0.2s ease;
    }
    .nav-pills-student .nav-link.active {
        background-color: var(--primary) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(95, 178, 48, 0.35);
    }
    [data-theme="light"] .nav-pills-student .nav-link:not(.active) {
        color: #475569 !important;
        background-color: #f1f5f9;
    }
    [data-theme="light"] .nav-pills-student .nav-link:not(.active):hover {
        background-color: #e2e8f0;
        color: #0f172a !important;
    }

    /* Iconos KPI Estudiantes */
    .kpi-icon-avatar {
        width: 52px;
        height: 52px;
        min-width: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.65rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-student-card:hover .kpi-icon-avatar {
        transform: scale(1.06);
    }
    .kpi-avatar-blue {
        background-color: #eff6ff !important;
        color: #2563eb !important;
        border: 1px solid #bfdbfe !important;
    }
    .kpi-avatar-green {
        background-color: #f0fdf4 !important;
        color: #16a34a !important;
        border: 1px solid #bbf7d0 !important;
    }
    .kpi-avatar-cyan {
        background-color: #f0f9ff !important;
        color: #0284c7 !important;
        border: 1px solid #bae6fd !important;
    }
    .kpi-avatar-red {
        background-color: #fef2f2 !important;
        color: #dc2626 !important;
        border: 1px solid #fecaca !important;
    }

    [data-theme="dark"] .kpi-avatar-blue {
        background-color: rgba(37, 99, 235, 0.16) !important;
        color: #60a5fa !important;
        border: 1px solid rgba(59, 130, 246, 0.3) !important;
    }
    [data-theme="dark"] .kpi-avatar-green {
        background-color: rgba(22, 163, 74, 0.16) !important;
        color: #4ade80 !important;
        border: 1px solid rgba(34, 197, 94, 0.3) !important;
    }
    [data-theme="dark"] .kpi-avatar-cyan {
        background-color: rgba(2, 132, 199, 0.16) !important;
        color: #38bdf8 !important;
        border: 1px solid rgba(14, 165, 233, 0.3) !important;
    }
    [data-theme="dark"] .kpi-avatar-red {
        background-color: rgba(220, 38, 38, 0.16) !important;
        color: #f87171 !important;
        border: 1px solid rgba(239, 68, 68, 0.3) !important;
    }

    /* Badges con contraste perfecto */
    .badge-periodo-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.45rem 1.15rem;
        border-radius: 50rem;
        font-size: 0.85rem;
        font-weight: 700;
        background-color: #eff6ff !important;
        color: #1d4ed8 !important;
        border: 1px solid #bfdbfe !important;
    }
    [data-theme="dark"] .badge-periodo-pill {
        background-color: rgba(37, 99, 235, 0.2) !important;
        color: #93c5fd !important;
        border: 1px solid rgba(96, 165, 250, 0.35) !important;
    }
    .badge-code-pill {
        display: inline-block;
        padding: 0.3rem 0.65rem;
        border-radius: 8px;
        font-family: monospace;
        font-size: 0.85rem;
        font-weight: 700;
        background-color: #eff6ff !important;
        color: #1d4ed8 !important;
        border: 1px solid #bfdbfe !important;
    }
    [data-theme="dark"] .badge-code-pill {
        background-color: rgba(37, 99, 235, 0.2) !important;
        color: #93c5fd !important;
        border: 1px solid rgba(96, 165, 250, 0.35) !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-3">

    <!-- HERO BANNER DEL ESTUDIANTE -->
    <div class="student-hero-banner">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
            <div class="d-flex align-items-center gap-4">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-lg" style="width: 75px; height: 75px; font-size: 1.8rem; background-color: var(--primary) !important;">
                    {{ strtoupper(substr($user->nombre, 0, 1)) }}{{ strtoupper(substr($user->apellidos, 0, 1)) }}
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-success bg-opacity-25 text-success px-3 py-1 rounded-pill small fw-bold">
                            <i class="bi bi-person-check-fill me-1"></i> Estudiante Activo
                        </span>
                        <span class="text-muted small">ID #{{ $user->id }}</span>
                    </div>
                    <h2 class="h3 fw-bold mb-1">{{ $user->nombre }} {{ $user->apellidos }}</h2>
                    <div class="d-flex flex-wrap gap-3 text-muted small mt-1">
                        <span><i class="bi bi-person-vcard me-1"></i> Cédula: <strong>{{ $user->cedula ?: 'Sin registrar' }}</strong></span>
                        <span><i class="bi bi-envelope me-1"></i> {{ $user->email }}</span>
                        @if($user->telefono)
                            <span><i class="bi bi-telephone me-1"></i> {{ $user->telefono }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Accesos rápidos oficiales -->
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('estudiante.mi_record_pdf') }}" target="_blank" class="btn btn-outline-primary rounded-pill px-4 fw-semibold shadow-sm">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Mi Récord Académico (PDF)
                </a>
                @if(config('cliente.campus_url'))
                    <a href="{{ config('cliente.campus_url') }}" target="_blank" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-mortarboard me-2"></i> Campus Virtual Moodle
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- ALERTAS IMPORTANTES (Firmas de Boleta o Documentos Pendientes) -->
    @if($boletasPorFirmar->count() > 0)
        @foreach($boletasPorFirmar as $bpf)
        <div class="alert alert-warning alert-dismissible fade show rounded-4 mb-4 border-0 shadow-sm d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-pen-fill fs-3 text-warning"></i>
                <div>
                    <strong>Firma Digital Requerida:</strong> Tienes la Boleta de Matrícula <strong>#{{ $bpf->id }}</strong> pendiente de firma de aceptación oficial.
                </div>
            </div>
            <a href="{{ route('boletas.firmar_publico', $bpf->token_firma ?: 'token') }}" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm btn-sm">
                Firmar Ahora <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        @endforeach
    @endif

    <!-- 4 KPIS CLAVE DEL ESTUDIANTE -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-student-card d-flex align-items-center gap-3">
                <div class="kpi-icon-avatar kpi-avatar-blue">
                    <i class="bi bi-book-half"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Asignaturas Cursando</span>
                    <h3 class="fw-bold mb-0 text-primary">{{ $matriculas->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-student-card d-flex align-items-center gap-3">
                <div class="kpi-icon-avatar kpi-avatar-green">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Créditos Aprobados</span>
                    <h3 class="fw-bold mb-0 text-success">{{ number_format($totalCreditosAprobados, 1) }} cr.</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-student-card d-flex align-items-center gap-3">
                <div class="kpi-icon-avatar kpi-avatar-cyan">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Promedio Ponderado</span>
                    <h3 class="fw-bold mb-0" style="color: #0284c7;">{{ number_format($promedioPonderado, 2) }} / 100</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-student-card d-flex align-items-center gap-3">
                <div class="kpi-icon-avatar {{ $saldoPendienteTotal > 0 ? 'kpi-avatar-red' : 'kpi-avatar-green' }}">
                    <i class="bi {{ $saldoPendienteTotal > 0 ? 'bi-exclamation-triangle-fill' : 'bi-shield-check' }}"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Estado Financiero</span>
                    <h4 class="fw-bold mb-0 {{ $saldoPendienteTotal > 0 ? 'text-danger' : 'text-success' }}">
                        {{ $saldoPendienteTotal > 0 ? '₡' . number_format($saldoPendienteTotal, 2) : 'Al Día' }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- PESTAÑAS PRINCIPALES DEL PORTAL -->
    <ul class="nav nav-pills nav-pills-student gap-2 mb-4" id="portalTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="tab-cursos-btn" data-bs-toggle="pill" data-bs-target="#tab-cursos" type="button">
                <i class="bi bi-mortarboard me-1"></i> Mis Cursos & Horarios
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tab-calificaciones-btn" data-bs-toggle="pill" data-bs-target="#tab-calificaciones" type="button">
                <i class="bi bi-card-checklist me-1"></i> Calificaciones & Récord
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tab-boletas-btn" data-bs-toggle="pill" data-bs-target="#tab-boletas" type="button">
                <i class="bi bi-receipt me-1"></i> Boletas & Pagos
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tab-expediente-btn" data-bs-toggle="pill" data-bs-target="#tab-expediente" type="button">
                <i class="bi bi-folder2-open me-1"></i> Mi Expediente Digital
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tab-tcu-btn" data-bs-toggle="pill" data-bs-target="#tab-tcu" type="button">
                <i class="bi bi-journal-text me-1"></i> Mi TCU
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tab-oferta-btn" data-bs-toggle="pill" data-bs-target="#tab-oferta" type="button">
                <i class="bi bi-calendar3 me-1"></i> Oferta & Calendario
            </button>
        </li>
    </ul>

    <!-- CONTENIDO DE LAS PESTAÑAS -->
    <div class="tab-content" id="portalTabsContent">

        <!-- TAB 1: MIS CURSOS ACTIVOS & HORARIOS -->
        <div class="tab-pane fade show active" id="tab-cursos" role="tabpanel">
            <div class="row g-4">
                @forelse($matriculas as $m)
                @php
                    $ca = $m->cursoActivo;
                    $pe = $ca ? $ca->planEstudio : null;
                @endphp
                <div class="col-md-6 col-xl-4">
                    <div class="course-card-custom">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge-code-pill">
                                    {{ $pe ? $pe->codigo : 'S/C' }}
                                </span>
                                <span class="badge bg-secondary bg-opacity-25 text-secondary small">
                                    {{ $ca ? $ca->periodo : 'Activo' }}
                                </span>
                            </div>
                            <h5 class="fw-bold mb-2">{{ $pe ? $pe->materia : "Curso #{$m->id_curso_activo}" }}</h5>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-person-workspace me-1 text-primary"></i> 
                                Docente: <strong>{{ ($ca && $ca->profesor) ? "{$ca->profesor->nombre} {$ca->profesor->apellidos}" : 'Docente Asignado' }}</strong>
                            </p>

                            <div class="p-3 rounded-4 bg-light bg-opacity-50 border mb-3 small">
                                <div class="mb-1"><i class="bi bi-clock me-1 text-muted"></i> <strong>Horario:</strong> {{ $ca ? ($ca->horario ?: ($ca->hora_inicio ? "{$ca->hora_inicio} - {$ca->hora_final}" : 'Virtual Asincrónico')) : 'Virtual' }}</div>
                                <div><i class="bi bi-award me-1 text-muted"></i> <strong>Créditos:</strong> {{ $pe ? ($pe->creditos ?: 3) : 3 }} créditos</div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                            @if($ca && $ca->enlace_zoom)
                                <a href="{{ $ca->enlace_zoom }}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 flex-fill">
                                    <i class="bi bi-camera-video me-1"></i> Clase en Vivo
                                </a>
                            @endif
                            @if(config('cliente.campus_url'))
                                <a href="{{ config('cliente.campus_url') }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 flex-fill">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Aula Virtual
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="content-tab-card text-center py-5">
                        <i class="bi bi-mortarboard fs-1 text-muted d-block mb-3"></i>
                        <h5 class="fw-bold">No tienes cursos matriculados en este momento</h5>
                        <p class="text-muted small">Consulta la oferta académica del período o contacta al Departamento de Registro para inscribirte.</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- TAB 2: HISTORIAL ACADÉMICO & CALIFICACIONES -->
        <div class="tab-pane fade" id="tab-calificaciones" role="tabpanel">
            <div class="content-tab-card">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 border-bottom pb-3">
                    <div>
                        <h4 class="h5 fw-bold mb-1">Historial Curricular y Calificaciones</h4>
                        <p class="text-muted small mb-0">Detalle de asignaturas cursadas, convalidaciones y calificaciones oficiales registradas.</p>
                    </div>
                    <a href="{{ route('estudiante.mi_record_pdf') }}" target="_blank" class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-semibold">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Descargar Récord Académico Oficial (PDF)
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>Código</th>
                                <th>Asignatura</th>
                                <th>Período</th>
                                <th>Créditos</th>
                                <th>Nota Final</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historialCursos as $hc)
                            <tr>
                                <td><span class="badge bg-secondary bg-opacity-25 text-secondary font-monospace">{{ $hc['codigo'] }}</span></td>
                                <td>
                                    <div class="fw-bold">{{ $hc['materia'] }}</div>
                                    <span class="text-muted small">{{ $hc['profesor'] }}</span>
                                </td>
                                <td><span class="text-muted small">{{ $hc['periodo'] }}</span></td>
                                <td><span class="font-monospace fw-semibold">{{ number_format($hc['creditos'], 1) }}</span></td>
                                <td>
                                    <span class="fw-bold font-monospace fs-6">
                                        {{ is_numeric($hc['calificacion']) ? number_format($hc['calificacion'], 1) : ($hc['calificacion'] ?: '-') }}
                                    </span>
                                </td>
                                <td>
                                    @if($hc['estado'] === 'APROBADO' || $hc['estado'] === 'CONVALIDADO')
                                        <span class="badge bg-success bg-opacity-25 text-success px-3 py-1 rounded-pill"><i class="bi bi-check-circle me-1"></i> {{ $hc['estado'] }}</span>
                                    @elseif($hc['estado'] === 'EN CURSO')
                                        <span class="badge bg-primary bg-opacity-25 text-primary px-3 py-1 rounded-pill"><i class="bi bi-hourglass-split me-1"></i> En Curso</span>
                                    @elseif($hc['estado'] === 'RETIRADO')
                                        <span class="badge bg-warning bg-opacity-25 text-warning px-3 py-1 rounded-pill"><i class="bi bi-dash-circle me-1"></i> Retirado</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-25 text-danger px-3 py-1 rounded-pill"><i class="bi bi-x-circle me-1"></i> Reprobado</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No hay registros académicos disponibles.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 3: MIS BOLETAS & FINANZAS -->
        <div class="tab-pane fade" id="tab-boletas" role="tabpanel">
            <div class="content-tab-card">
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                    <div>
                        <h4 class="h5 fw-bold mb-1">Estado de Cuenta y Boletas de Matrícula</h4>
                        <p class="text-muted small mb-0">Control financiero, pagos aplicados y firmas electrónicas de aceptación de matrícula.</p>
                    </div>
                    <div class="text-end">
                        <span class="text-muted small d-block">Saldo Pendiente Acumulado</span>
                        <h4 class="fw-bold mb-0 {{ $saldoPendienteTotal > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $saldoPendienteTotal > 0 ? '₡' . number_format($saldoPendienteTotal, 2) : 'Al Día (₡0.00)' }}
                        </h4>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>Nº Boleta</th>
                                <th>Período</th>
                                <th>Total</th>
                                <th>Saldo Pendiente</th>
                                <th>Firma de Matrícula</th>
                                <th>Estado de Pago</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($boletas as $b)
                            <tr>
                                <td><span class="badge-code-pill">#{{ $b->id }}</span></td>
                                <td><span class="fw-semibold">{{ $b->periodo ?: 'Activo' }}</span></td>
                                <td><span class="font-monospace fw-bold">₡{{ number_format((float)($b->total ?? 0), 2) }}</span></td>
                                <td>
                                    @php
                                        $saldoVal = (float)($b->saldo_pendiente ?? $b->saldo ?? 0);
                                    @endphp
                                    <span class="font-monospace fw-bold {{ $saldoVal > 0 ? 'text-danger' : 'text-success' }}">
                                        ₡{{ number_format($saldoVal, 2) }}
                                    </span>
                                </td>
                                <td>
                                    @if(!empty($b->ruta_firma) || !empty($b->fecha_firma))
                                        <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-2 py-1"><i class="bi bi-pen-fill me-1"></i> Firmada</span>
                                    @elseif(!empty($b->token_firma))
                                        <a href="{{ route('boletas.firmar_publico', $b->token_firma) }}" class="btn btn-warning btn-sm rounded-pill px-3 py-1 fw-bold">
                                            Firmar <i class="bi bi-pencil-square ms-1"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">No requerida</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $estadoPago = $b->estado ?? 'Pendiente';
                                    @endphp
                                    @if($estadoPago === 'Pagada')
                                        <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-2 py-1">Pagada</span>
                                    @elseif($estadoPago === 'Parcial')
                                        <span class="badge bg-warning bg-opacity-25 text-warning rounded-pill px-2 py-1">Pago Parcial</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-25 text-danger rounded-pill px-2 py-1">{{ $estadoPago }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('boletas.pdf', $b->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Descargar Boleta PDF">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No se han emitido boletas de pago para tu cuenta.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 4: MI EXPEDIENTE DIGITAL -->
        <div class="tab-pane fade" id="tab-expediente" role="tabpanel">
            <div class="content-tab-card">
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                    <div>
                        <h4 class="h5 fw-bold mb-1">Mi Expediente Digital Unificado</h4>
                        <p class="text-muted small mb-0">Documentación legal y requisitos de ingreso archivados centralizadamente.</p>
                    </div>
                    @if($expediente)
                        <span class="badge {{ $expediente->estado === 'Aprobado' ? 'bg-success' : 'bg-warning' }} bg-opacity-25 {{ $expediente->estado === 'Aprobado' ? 'text-success' : 'text-warning' }} px-3 py-2 rounded-pill fs-7">
                            Expediente #{{ $expediente->id_expediente }} ({{ $expediente->estado }})
                        </span>
                    @endif
                </div>

                @if($expediente)
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 bg-light bg-opacity-50 border">
                                <span class="text-muted small d-block">Porcentaje de Completitud</span>
                                <h3 class="fw-bold mb-2 text-primary">{{ $expediente->porcentaje_completitud }}%</h3>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $expediente->porcentaje_completitud }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="p-3 rounded-4 bg-light bg-opacity-50 border">
                                <span class="text-muted small d-block mb-1">Requisitos Pendientes con Registro</span>
                                <p class="mb-0 fw-semibold text-secondary">{{ $expediente->campos_pendientes_texto }}</p>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3"><i class="bi bi-files me-1"></i> Documentos Cargados en el Expediente:</h6>
                    <div class="row g-3">
                        @forelse($expediente->archivos as $arch)
                        <div class="col-md-6 col-xl-4">
                            <div class="p-3 rounded-4 border d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2 text-truncate me-2">
                                    <i class="bi bi-file-earmark-check fs-3 text-primary"></i>
                                    <div class="text-truncate">
                                        <div class="fw-bold small text-truncate">{{ $arch->nombre_original ?? $arch->nombre_servidor ?? 'Documento' }}</div>
                                        <span class="text-muted small" style="font-size: 0.75rem;">{{ $arch->categoria ?? 'Expediente' }}</span>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $arch->ruta_archivo) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-circle" title="Ver documento">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-muted small">No hay archivos adjuntos actualmente en tu expediente.</div>
                        @endforelse
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                        Aún no tienes un expediente digital registrado. Consulta con el Departamento de Admisiones.
                    </div>
                @endif
            </div>
        </div>

        <!-- TAB 5: TRABAJO COMUNAL (TCU) -->
        <div class="tab-pane fade" id="tab-tcu" role="tabpanel">
            <div class="content-tab-card">
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                    <div>
                        <h4 class="h5 fw-bold mb-1">Trabajo Comunal Universitario (TCU)</h4>
                        <p class="text-muted small mb-0">Seguimiento de bitácora y cumplimiento de horas comunitarias obligatorias.</p>
                    </div>
                    @if($tcu)
                        <span class="badge {{ $tcu->estado === 'Finalizado' || $tcu->estado === 'Aprobado' ? 'bg-success' : 'bg-warning' }} bg-opacity-25 {{ $tcu->estado === 'Finalizado' || $tcu->estado === 'Aprobado' ? 'text-success' : 'text-warning' }} px-3 py-2 rounded-pill fs-7">
                            Estado: {{ $tcu->estado }}
                        </span>
                    @endif
                </div>

                @if($tcu)
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 rounded-4 bg-light bg-opacity-50 border">
                                <span class="text-muted small d-block">Proyecto Registrado</span>
                                <h5 class="fw-bold text-primary mb-1">{{ $tcu->nombre_proyecto }}</h5>
                                <div class="text-muted small"><i class="bi bi-building me-1"></i> Institución: <strong>{{ $tcu->institucion_beneficiada }}</strong></div>
                                <div class="text-muted small"><i class="bi bi-person me-1"></i> Supervisor: {{ $tcu->nombre_supervisor }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-4 bg-light bg-opacity-50 border">
                                <span class="text-muted small d-block">Progreso de Horas</span>
                                <h3 class="fw-bold text-success mb-2">{{ $horasTcuCompletadas }} / 150 hrs</h3>
                                <div class="progress" style="height: 6px;">
                                    @php $pctTcu = min(100, round(($horasTcuCompletadas / 150) * 100)); @endphp
                                    <div class="progress-bar bg-success" style="width: {{ $pctTcu }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('tcu.index') }}" class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-semibold">
                            <i class="bi bi-journal-text me-1"></i> Ir a Gestionar Bitácora de TCU
                        </a>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                        No tienes una bitácora de TCU abierta.
                        <div class="mt-2">
                            <a href="{{ route('tcu.create') }}" class="btn btn-primary rounded-pill px-4 btn-sm fw-semibold">
                                Abrir Bitácora de TCU
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- TAB 6: OFERTA & CALENDARIO INSTITUCIONAL -->
        <div class="tab-pane fade" id="tab-oferta" role="tabpanel">
            <div class="content-tab-card">
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                    <div>
                        <h4 class="h5 fw-bold mb-1">Oferta Académica & Calendario Institucional</h4>
                        <p class="text-muted small mb-0">Cursos disponibles para matrícula y fechas clave del cuatrimestre.</p>
                    </div>
                    <span class="badge-periodo-pill">
                        <i class="bi bi-calendar-event me-2"></i> Período Activo {{ date('Y') }}
                    </span>
                </div>

                <!-- Calendario de Fechas Importantes -->
                <div class="p-3 rounded-4 bg-light bg-opacity-50 border mb-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-calendar-check me-2 text-primary"></i> Fechas Importantes del Ciclo Lectivo:</h6>
                    <div class="row g-3 small">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-circle-fill text-success" style="font-size: 8px;"></i>
                                <span><strong>Período de Matrícula:</strong> Abierto en línea</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-circle-fill text-warning" style="font-size: 8px;"></i>
                                <span><strong>Límite de Retiro:</strong> Semana 4 del ciclo</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-circle-fill text-info" style="font-size: 8px;"></i>
                                <span><strong>Próxima Graduación:</strong> Convocatoria {{ date('Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cursos Disponibles -->
                <h6 class="fw-bold mb-3"><i class="bi bi-collection me-1"></i> Oferta de Cursos Disponibles:</h6>
                <div class="row g-3">
                    @forelse($ofertaCursos as $oc)
                    <div class="col-md-6 col-xl-3">
                        <div class="p-3 rounded-4 border h-100 d-flex flex-column justify-content-between">
                            <div>
                                <span class="badge bg-secondary bg-opacity-25 text-secondary small font-monospace mb-1">
                                    {{ $oc->planEstudio ? $oc->planEstudio->codigo : 'S/C' }}
                                </span>
                                <h6 class="fw-bold mb-1">{{ $oc->planEstudio ? $oc->planEstudio->materia : "Curso #{$oc->id_curso_activo}" }}</h6>
                                <p class="text-muted small mb-2"><i class="bi bi-person me-1"></i> {{ $oc->profesor ? $oc->profesor->nombre : 'Docente asignado' }}</p>
                            </div>
                            <div class="pt-2 border-top small text-muted">
                                <strong>Período:</strong> {{ $oc->periodo ?: 'Activo' }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-muted small">No hay cursos adicionales en oferta en este momento.</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
