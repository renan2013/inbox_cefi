@extends('layouts.app')

@section('title', 'Expediente 360° - ' . ($usuario ? $usuario->nombre . ' ' . $usuario->apellidos : 'Estudiante'))

@section('styles')
<style>
    .hero-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%);
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
    .tabs-card {
        background: rgba(30, 41, 59, 0.8);
        border: 1px solid var(--border-dark) !important;
    }

    /* Tabs Header & Buttons */
    .exp-tabs-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        background: transparent;
    }
    .exp-tab-btn {
        border-radius: 0;
        color: rgba(255, 255, 255, 0.7) !important;
        border: 0 !important;
        border-bottom: 3px solid transparent !important;
        padding: 1rem 1.25rem;
        font-weight: 600;
        transition: all 0.2s ease;
        background: transparent;
    }
    .exp-tab-btn:hover {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.03) !important;
    }
    .exp-tab-btn.active {
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.06) !important;
        border-bottom: 3px solid var(--primary, #5fb230) !important;
    }

    /* Info Cards (Ficha Personal) */
    .info-box-card {
        background: rgba(15, 23, 42, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 0.85rem;
        padding: 1.25rem;
        transition: all 0.2s ease;
    }
    .info-label {
        color: #94a3b8;
    }
    .info-value {
        color: #f8fafc;
    }
    .firma-img {
        background: #ffffff;
        border: 1px solid #cbd5e1;
    }

    /* Tables (Kardex / Finanzas) */
    .exp-table {
        border-color: rgba(255, 255, 255, 0.08) !important;
        color: #ffffff;
    }
    .exp-thead {
        background: rgba(15, 23, 42, 0.6);
        font-size: 0.8rem;
        text-transform: uppercase;
        color: #94a3b8;
    }
    .exp-thead th {
        color: #94a3b8 !important;
        border-bottom-width: 1px !important;
    }

    /* Finanzas */
    .finance-kpi-card {
        background: rgba(15, 23, 42, 0.6);
        border-radius: 0.85rem;
        padding: 1.25rem;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .finance-kpi-label {
        color: #94a3b8;
    }
    .finance-kpi-value {
        color: #f8fafc;
    }

    /* Boveda */
    .vault-cat-card {
        background: rgba(15, 23, 42, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 0.85rem;
        padding: 1.25rem;
    }
    .vault-cat-title {
        color: #f8fafc;
    }
    .vault-count-badge {
        background: rgba(255, 255, 255, 0.1);
        color: #f8fafc;
    }
    .vault-file-item {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
    .vault-doc-link {
        color: #f8fafc;
    }
    .vault-doc-link:hover {
        color: var(--primary, #5fb230);
    }
    .vault-doc-meta {
        color: #94a3b8;
    }

    /* Bitacora */
    .bitacora-form-card {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 0.85rem;
        padding: 1.25rem;
    }
    .bitacora-title {
        color: #f8fafc;
    }
    .note-item-card {
        background: rgba(15, 23, 42, 0.5);
        border-radius: 0.85rem;
        padding: 1.1rem;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .note-text {
        color: #f8fafc;
    }
    .note-meta {
        color: #94a3b8;
    }

    /* Inputs & Modal */
    .exp-input {
        background-color: rgba(15, 23, 42, 0.7) !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        color: #f8fafc !important;
    }
    .exp-input:focus {
        background-color: rgba(15, 23, 42, 0.9) !important;
        border-color: var(--primary, #5fb230) !important;
        color: #f8fafc !important;
        box-shadow: 0 0 0 3px rgba(95, 178, 48, 0.2) !important;
    }
    .modal-doc-upload {
        background: #1e293b;
        color: #f8fafc;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .modal-doc-upload .modal-header,
    .modal-doc-upload .modal-footer {
        border-color: rgba(255, 255, 255, 0.1);
    }
    .modal-close-btn {
        filter: invert(1);
    }
    .hero-kpi-col {
        border-color: rgba(255, 255, 255, 0.1);
    }
    .badge-student-id-pill {
        background-color: #0f172a;
        color: #94a3b8;
        border: 1px solid #334155;
    }

    /* Badges de Estado del Expediente con Alto Contraste */
    .badge-estado-aprobado {
        background-color: rgba(16, 185, 129, 0.2) !important;
        color: #34d399 !important;
        border: 1px solid rgba(16, 185, 129, 0.4) !important;
        font-weight: 600 !important;
    }
    .badge-estado-pendiente {
        background-color: rgba(245, 158, 11, 0.2) !important;
        color: #fbbf24 !important;
        border: 1px solid rgba(245, 158, 11, 0.4) !important;
        font-weight: 600 !important;
    }
    .badge-estado-rechazado {
        background-color: rgba(239, 68, 68, 0.2) !important;
        color: #f87171 !important;
        border: 1px solid rgba(239, 68, 68, 0.4) !important;
        font-weight: 600 !important;
    }

    /* ==============================================================
       LIGHT THEME COMPREHENSIVE OVERRIDES [data-theme="light"]
       ============================================================== */
    [data-theme="light"] .hero-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
    }
    [data-theme="light"] .hero-card h2 {
        color: #0f172a !important;
    }
    [data-theme="light"] .tabs-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
    }
    [data-theme="light"] .exp-tabs-header {
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    [data-theme="light"] .exp-tab-btn {
        color: #64748b !important;
        background: transparent !important;
        border-bottom: 3px solid transparent !important;
    }
    [data-theme="light"] .exp-tab-btn:hover {
        color: #0f172a !important;
        background: #f1f5f9 !important;
    }
    [data-theme="light"] .exp-tab-btn.active {
        color: #0f172a !important;
        background: #ffffff !important;
        border-bottom: 3px solid var(--primary, #5fb230) !important;
    }

    /* Light Info Cards */
    [data-theme="light"] .info-box-card {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02) !important;
    }
    [data-theme="light"] .info-label {
        color: #64748b !important;
        font-weight: 500;
    }
    [data-theme="light"] .info-value {
        color: #0f172a !important;
        font-weight: 600;
    }
    [data-theme="light"] .info-box-card h5.text-primary {
        color: #2563eb !important;
    }
    [data-theme="light"] .info-box-card h5.text-warning {
        color: #d97706 !important;
    }
    [data-theme="light"] .info-box-card h5.text-success {
        color: #16a34a !important;
    }
    [data-theme="light"] .info-box-card h5.text-info {
        color: #0284c7 !important;
    }

    /* Light Tables */
    [data-theme="light"] .exp-table {
        border-color: #e2e8f0 !important;
        color: #0f172a !important;
    }
    [data-theme="light"] .exp-thead {
        background: #f1f5f9 !important;
        color: #475569 !important;
    }
    [data-theme="light"] .exp-thead th {
        background: #f1f5f9 !important;
        color: #475569 !important;
        border-bottom: 2px solid #cbd5e1 !important;
    }
    [data-theme="light"] .exp-table tbody tr {
        background: #ffffff !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    [data-theme="light"] .exp-table tbody tr:hover {
        background: #f8fafc !important;
    }
    [data-theme="light"] .exp-table tbody td {
        color: #1e293b !important;
        border-color: #e2e8f0 !important;
    }
    [data-theme="light"] .exp-table tbody td.text-muted {
        color: #64748b !important;
    }

    /* Light Finanzas */
    [data-theme="light"] .finance-kpi-card {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03) !important;
    }
    [data-theme="light"] .finance-kpi-label {
        color: #64748b !important;
    }
    [data-theme="light"] .finance-kpi-value {
        color: #0f172a !important;
    }

    /* Light Bóveda */
    [data-theme="light"] .vault-cat-card {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03) !important;
    }
    [data-theme="light"] .vault-cat-title {
        color: #0f172a !important;
    }
    [data-theme="light"] .vault-count-badge {
        background: #e2e8f0 !important;
        color: #334155 !important;
    }
    [data-theme="light"] .vault-file-item {
        border-bottom: 1px solid #e2e8f0 !important;
    }
    [data-theme="light"] .vault-doc-link {
        color: #0f172a !important;
    }
    [data-theme="light"] .vault-doc-link:hover {
        color: #16a34a !important;
    }
    [data-theme="light"] .vault-doc-meta {
        color: #64748b !important;
    }

    /* Light Bitácora */
    [data-theme="light"] .bitacora-form-card {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03) !important;
    }
    [data-theme="light"] .bitacora-title {
        color: #0f172a !important;
    }
    [data-theme="light"] .note-item-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-left: 4px solid #3b82f6 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03) !important;
    }
    [data-theme="light"] .note-text {
        color: #1e293b !important;
    }
    [data-theme="light"] .note-meta {
        color: #64748b !important;
    }
    [data-theme="light"] .note-meta strong {
        color: #0f172a !important;
    }

    /* Light Inputs & Modal */
    [data-theme="light"] .exp-input {
        background-color: #ffffff !important;
        color: #0f172a !important;
        border-color: #cbd5e1 !important;
    }
    [data-theme="light"] .exp-input:focus {
        background-color: #ffffff !important;
        border-color: var(--primary, #5fb230) !important;
        box-shadow: 0 0 0 3px rgba(95, 178, 48, 0.15) !important;
        color: #0f172a !important;
    }
    [data-theme="light"] .modal-doc-upload {
        background-color: #ffffff !important;
        color: #0f172a !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
    }
    [data-theme="light"] .modal-doc-upload .modal-header,
    [data-theme="light"] .modal-doc-upload .modal-footer {
        border-color: #e2e8f0 !important;
    }
    [data-theme="light"] .modal-doc-upload .modal-title {
        color: #0f172a !important;
    }
    [data-theme="light"] .modal-doc-upload .form-label {
        color: #475569 !important;
    }
    [data-theme="light"] .modal-close-btn {
        filter: none !important;
    }

    /* Light Hero Card Controls */
    [data-theme="light"] .hero-kpi-col {
        border-color: #e2e8f0 !important;
    }
    [data-theme="light"] .badge-student-id-pill {
        background-color: #f1f5f9 !important;
        color: #334155 !important;
        border-color: #cbd5e1 !important;
    }
    [data-theme="light"] .hero-card .btn-outline-info {
        color: #0284c7 !important;
        border-color: #bae6fd !important;
        background-color: #f0f9ff !important;
    }
    [data-theme="light"] .hero-card .btn-outline-info:hover {
        color: #ffffff !important;
        background-color: #0284c7 !important;
        border-color: #0284c7 !important;
    }
    [data-theme="light"] .hero-card .btn-outline-secondary {
        color: #475569 !important;
        border-color: #cbd5e1 !important;
        background-color: #f8fafc !important;
    }
    [data-theme="light"] .hero-card .btn-outline-secondary:hover {
        color: #0f172a !important;
        background-color: #e2e8f0 !important;
        border-color: #94a3b8 !important;
    }
    [data-theme="light"] .hero-card .dropdown-menu {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
    }
    [data-theme="light"] .hero-card .dropdown-item {
        color: #334155 !important;
    }
    [data-theme="light"] .hero-card .dropdown-item:hover {
        background-color: #f1f5f9 !important;
    }
    [data-theme="light"] .hero-card .dropdown-divider {
        border-color: #e2e8f0 !important;
    }
    [data-theme="light"] .exp-section-title {
        color: #0f172a !important;
    }

    [data-theme="light"] .badge-estado-aprobado {
        background-color: #dcfce7 !important;
        color: #15803d !important;
        border: 1px solid #86efac !important;
        font-weight: 700 !important;
    }
    [data-theme="light"] .badge-estado-pendiente {
        background-color: #fef3c7 !important;
        color: #b45309 !important;
        border: 1px solid #fcd34d !important;
        font-weight: 700 !important;
    }
    [data-theme="light"] .badge-estado-rechazado {
        background-color: #fee2e2 !important;
        color: #b91c1c !important;
        border: 1px solid #fca5a5 !important;
        font-weight: 700 !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- ALERTAS DE SESIÓN -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- PERFIL HERO SUPERIOR -->
    <div class="card hero-card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-column flex-lg-row align-items-center align-items-lg-start gap-4">
                
                <!-- Avatar / Fotografía Oficial -->
                <div class="position-relative">
                    @if($fotoUrl)
                        <img src="{{ $fotoUrl }}" alt="Foto Oficial" class="rounded-4 object-fit-cover shadow-lg border border-2 border-success" style="width: 130px; height: 130px;">
                    @else
                        <div class="rounded-4 d-flex align-items-center justify-content-center shadow-lg border border-secondary border-opacity-25" style="width: 130px; height: 130px; background: rgba(95, 178, 48, 0.15); color: var(--primary, #5fb230); font-size: 3rem; font-weight: bold;">
                            {{ mb_substr($usuario->nombre ?? 'E', 0, 1) }}{{ mb_substr($usuario->apellidos ?? '', 0, 1) }}
                        </div>
                    @endif
                    <span class="position-absolute bottom-0 end-0 translate-middle-y badge rounded-pill badge-student-id-pill px-2 py-1 small" style="font-family: monospace;">
                        #{{ str_pad($expediente->id_expediente, 5, '0', STR_PAD_LEFT) }}
                    </span>
                </div>

                <!-- Datos del Estudiante -->
                <div class="flex-grow-1 text-center text-lg-start">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-2 mb-2">
                        <h2 class="fw-bold text-white mb-0">
                            {{ $usuario ? "{$usuario->nombre} {$usuario->apellidos}" : 'Estudiante' }}
                        </h2>
                        
                        <!-- Badge Estado Expediente -->
                        @if($expediente->estado === 'Aprobado')
                            <span class="badge badge-estado-aprobado rounded-pill px-3 py-1 fw-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> Expediente Formalizado
                            </span>
                        @elseif($expediente->estado === 'Rechazado')
                            <span class="badge badge-estado-rechazado rounded-pill px-3 py-1 fw-semibold">
                                <i class="bi bi-x-circle-fill me-1"></i> Expediente Rechazado
                            </span>
                        @else
                            <span class="badge badge-estado-pendiente rounded-pill px-3 py-1 fw-semibold">
                                <i class="bi bi-clock-fill me-1"></i> En Revisión
                            </span>
                        @endif
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-3 text-muted mb-3" style="font-size: 0.95rem;">
                        <span><i class="bi bi-card-text text-primary me-1"></i> {{ $usuario->cedula ?? $expediente->cedula_residencia ?? $expediente->pasaporte ?? 'Sin ID' }}</span>
                        <span>•</span>
                        <span><i class="bi bi-mortarboard-fill text-warning me-1"></i> {{ $expediente->especialidad_deseada ?: 'Programa General' }}</span>
                        <span>•</span>
                        <span><i class="bi bi-envelope-fill text-info me-1"></i> {{ $usuario->email ?? 'N/D' }}</span>
                    </div>

                    <!-- Botones de Acción Rápida -->
                    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-start">
                        <a href="{{ route('expedientes.create', ['id_usuario' => $expediente->id_usuario]) }}" class="btn btn-primary btn-sm px-3 rounded-pill fw-semibold d-flex align-items-center gap-2">
                            <i class="bi bi-pencil-square"></i> Editar Expediente
                        </a>

                        @if(!empty($linkWa) && $linkWa !== '#')
                            <a href="{{ $linkWa }}" target="_blank" class="btn btn-success btn-sm px-3 rounded-pill fw-semibold d-flex align-items-center gap-2">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                        @endif

                        <a href="{{ route('expedientes.record_pdf', $expediente->id_expediente) }}" target="_blank" class="btn btn-outline-info btn-sm px-3 rounded-pill fw-semibold d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-pdf-fill"></i> Certificación Récord PDF
                        </a>

                        <a href="{{ route('expedientes.descargar_zip', $expediente->id_expediente) }}" class="btn btn-outline-secondary btn-sm px-3 rounded-pill fw-semibold d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-zip-fill"></i> Descargar Bóveda ZIP
                        </a>

                        <!-- Dropdown Cambio de Estado -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm px-3 rounded-pill dropdown-toggle border-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-shield-check me-1"></i> Cambiar Estado
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li>
                                    <form action="{{ route('expedientes.cambiar_estado', $expediente->id_expediente) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action_expediente" value="aprobar">
                                        <button type="submit" class="dropdown-item text-success"><i class="bi bi-check-lg me-2"></i>Aprobar / Formalizar</button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('expedientes.cambiar_estado', $expediente->id_expediente) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action_expediente" value="pendiente">
                                        <button type="submit" class="dropdown-item text-warning"><i class="bi bi-clock me-2"></i>Poner en Revisión</button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('expedientes.cambiar_estado', $expediente->id_expediente) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action_expediente" value="rechazar">
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-x-lg me-2"></i>Rechazar Expediente</button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li>
                                    <form action="{{ route('expedientes.destroy', $expediente->id_expediente) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este expediente? Se borrarán todos los documentos físicos de la bóveda del servidor.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger fw-semibold"><i class="bi bi-trash-fill me-2"></i>Eliminar Expediente</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Métricas KPI Rápidas -->
                <div class="d-flex flex-row flex-lg-column gap-3 justify-content-center border-start-lg border-secondary border-opacity-25 hero-kpi-col ps-lg-4" style="min-width: 220px;">
                    <div class="p-2 text-center text-lg-start">
                        <span class="text-muted small fw-bold text-uppercase d-block">Promedio Ponderado</span>
                        <h3 class="fw-bold text-white mb-0">{{ number_format($promedioGpa, 2) }} <span class="fs-6 text-muted">/100</span></h3>
                    </div>
                    <div class="p-2 text-center text-lg-start">
                        <span class="text-muted small fw-bold text-uppercase d-block">Materias Aprobadas</span>
                        <h4 class="fw-bold text-success mb-0">{{ $cntAprobados }} <span class="fs-6 text-muted">({{ $totalCreditosAprobados }} créditos)</span></h4>
                    </div>
                    <div class="p-2 text-center text-lg-start">
                        <span class="text-muted small fw-bold text-uppercase d-block">Estado Financiero</span>
                        <div>
                            @if($cuotasMoraCount > 0)
                                <span class="badge bg-danger rounded-pill px-3 py-1">En Cobro / Mora ({{ $cuotasMoraCount }})</span>
                            @elseif($saldoPendiente > 0)
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Saldo Pendiente</span>
                            @else
                                <span class="badge bg-success rounded-pill px-3 py-1">Solvente (Al Día)</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- PESTAÑAS 360° -->
    <div class="card tabs-card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header exp-tabs-header p-0">
            <ul class="nav nav-tabs nav-fill border-0 flex-nowrap" id="expedienteTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link exp-tab-btn active" id="ficha-tab" data-bs-toggle="tab" data-bs-target="#ficha" type="button" role="tab">
                        <i class="bi bi-person-badge-fill me-2 text-primary"></i>1. Ficha Personal & Biométrica
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link exp-tab-btn" id="record-tab" data-bs-toggle="tab" data-bs-target="#record" type="button" role="tab">
                        <i class="bi bi-mortarboard-fill me-2 text-warning"></i>2. Récord Académico (Kardex)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link exp-tab-btn" id="finanzas-tab" data-bs-toggle="tab" data-bs-target="#finanzas" type="button" role="tab">
                        <i class="bi bi-wallet2 me-2 text-info"></i>3. Estado de Cuenta & Finanzas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link exp-tab-btn" id="boveda-tab" data-bs-toggle="tab" data-bs-target="#boveda" type="button" role="tab">
                        <i class="bi bi-archive-fill me-2 text-success"></i>4. Bóveda Documental
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link exp-tab-btn" id="bitacora-tab" data-bs-toggle="tab" data-bs-target="#bitacora" type="button" role="tab">
                        <i class="bi bi-journal-text me-2 text-danger"></i>5. Bitácora & Notas
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4 exp-card-body">
            <div class="tab-content" id="expedienteTabsContent">

                <!-- 1. FICHA PERSONAL & BIOMÉTRICA -->
                <div class="tab-pane fade show active" id="ficha" role="tabpanel">
                    <div class="row g-4">
                        <!-- Identidad y Personales -->
                        <div class="col-lg-6">
                            <div class="info-box-card mb-4">
                                <h5 class="fw-bold text-primary mb-3"><i class="bi bi-person-circle me-2"></i>Datos de Identidad</h5>
                                <div class="row g-2 small">
                                    <div class="col-sm-4 info-label">Nombre Completo:</div>
                                    <div class="col-sm-8 info-value fw-semibold">{{ $usuario->nombre ?? '' }} {{ $usuario->apellidos ?? '' }}</div>
                                    <div class="col-sm-4 info-label">Identificación:</div>
                                    <div class="col-sm-8 info-value fw-semibold">{{ $usuario->cedula ?? $expediente->cedula_residencia ?? $expediente->pasaporte ?? 'N/D' }}</div>
                                    <div class="col-sm-4 info-label">Género:</div>
                                    <div class="col-sm-8 info-value">{{ $expediente->genero ?: 'No especificado' }}</div>
                                    <div class="col-sm-4 info-label">Nacionalidad:</div>
                                    <div class="col-sm-8 info-value">{{ $expediente->nacionalidad ?: 'Costarricense' }}</div>
                                    <div class="col-sm-4 info-label">Fecha Nacimiento:</div>
                                    <div class="col-sm-8 info-value">{{ $expediente->fecha_nacimiento ? date('d/m/Y', strtotime($expediente->fecha_nacimiento)) : 'N/D' }}</div>
                                    <div class="col-sm-4 info-label">Lugar Nacimiento:</div>
                                    <div class="col-sm-8 info-value">{{ $expediente->lugar_nacimiento ?: 'N/D' }}</div>
                                    <div class="col-sm-4 info-label">Estado Civil:</div>
                                    <div class="col-sm-8 info-value">{{ $expediente->estado_civil ?: 'N/D' }}</div>
                                </div>
                            </div>

                            <div class="info-box-card">
                                <h5 class="fw-bold text-warning mb-3"><i class="bi bi-geo-alt-fill me-2"></i>Contacto y Domicilio</h5>
                                <div class="row g-2 small">
                                    <div class="col-sm-4 info-label">Dirección Exacta:</div>
                                    <div class="col-sm-8 info-value">{{ $expediente->domicilio_direccion ?: 'N/D' }}</div>
                                    <div class="col-sm-4 info-label">Provincia/Cantón:</div>
                                    <div class="col-sm-8 info-value">{{ $expediente->domicilio_provincia ?: 'N/D' }} / {{ $expediente->domicilio_canton ?: 'N/D' }}</div>
                                    <div class="col-sm-4 info-label">Celular:</div>
                                    <div class="col-sm-8 info-value fw-semibold">{{ $expediente->contacto_tel_celular ?: ($usuario->telefono ?? 'N/D') }}</div>
                                    <div class="col-sm-4 info-label">Tel. Habitación:</div>
                                    <div class="col-sm-8 info-value">{{ $expediente->contacto_tel_habitacion ?: 'N/D' }}</div>
                                    <div class="col-sm-4 info-label">Contacto Emergencia:</div>
                                    <div class="col-sm-8 info-value text-danger fw-semibold">{{ $expediente->contacto_otro_emergencias ?: 'No registrado' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Procedencia y Laboral -->
                        <div class="col-lg-6">
                            <div class="info-box-card mb-4">
                                <h5 class="fw-bold text-success mb-3"><i class="bi bi-building me-2"></i>Procedencia Académica</h5>
                                <div class="row g-2 small">
                                    <div class="col-sm-4 info-label">Colegio / Secundaria:</div>
                                    <div class="col-sm-8 info-value">{{ $expediente->procedencia_secundaria_institucion ?: 'N/D' }} ({{ $expediente->procedencia_secundaria_ano_graduacion ?: 'N/D' }})</div>
                                    <div class="col-sm-4 info-label">Título Secundaria:</div>
                                    <div class="col-sm-8 info-value">{{ $expediente->procedencia_secundaria_grado_obtenido ?: 'Bachiller en Educación Media' }}</div>
                                    <div class="col-sm-4 info-label">Universidad Previa:</div>
                                    <div class="col-sm-8 info-value">{{ $expediente->procedencia_universidad ?: 'Ninguna / Primer Ingreso' }}</div>
                                    <div class="col-sm-4 info-label">Título Universitario:</div>
                                    <div class="col-sm-8 info-value">{{ $expediente->procedencia_universidad_grado_obtenido ?: 'N/D' }}</div>
                                </div>
                            </div>

                            <div class="info-box-card mb-4">
                                <h5 class="fw-bold text-info mb-3"><i class="bi bi-briefcase-fill me-2"></i>Información Laboral</h5>
                                <div class="row g-2 small">
                                    <div class="col-sm-4 info-label">Empresa / Organización:</div>
                                    <div class="col-sm-8 info-value fw-semibold">{{ $expediente->laboral_institucion ?: 'N/D' }}</div>
                                    <div class="col-sm-4 info-label">Puesto / Cargo:</div>
                                    <div class="col-sm-8 info-value">{{ $expediente->laboral_puesto ?: 'N/D' }}</div>
                                    <div class="col-sm-4 info-label">Teléfono Laboral:</div>
                                    <div class="col-sm-8 info-value">{{ $expediente->laboral_telefono ?: 'N/D' }}</div>
                                </div>
                            </div>

                            <!-- Firma Digitalizada -->
                            @if($firmaUrl)
                            <div class="info-box-card text-center">
                                <h6 class="info-label small fw-bold text-uppercase mb-2">Firma Manuscrita Digitalizada</h6>
                                <img src="{{ $firmaUrl }}" alt="Firma del Estudiante" class="img-fluid rounded p-2 firma-img" style="max-height: 80px;">
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 2. RÉCORD ACADÉMICO (KARDEX) -->
                <div class="tab-pane fade" id="record" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold exp-section-title mb-0">Récord de Calificaciones Oficiales</h5>
                            <small class="text-muted">Historial completo de cursos cursados, créditos y notas registradas en Actas.</small>
                        </div>
                        <a href="{{ route('expedientes.record_pdf', $expediente->id_expediente) }}" target="_blank" class="btn btn-primary btn-sm px-3 rounded-pill fw-semibold">
                            <i class="bi bi-printer-fill me-1"></i> Imprimir Certificación Oficial
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 exp-table">
                            <thead class="exp-thead">
                                <tr>
                                    <th class="py-3 text-muted">Código</th>
                                    <th class="py-3 text-muted">Asignatura</th>
                                    <th class="py-3 text-muted">Período</th>
                                    <th class="py-3 text-muted">Docente</th>
                                    <th class="py-3 text-muted text-center">Créd.</th>
                                    <th class="py-3 text-muted text-center">Nota</th>
                                    <th class="py-3 text-muted text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cursosHistorial as $ch)
                                <tr>
                                    <td class="font-monospace fw-bold text-muted">{{ $ch['codigo'] }}</td>
                                    <td class="fw-semibold">{{ $ch['materia'] }}</td>
                                    <td><span class="badge bg-secondary bg-opacity-25 text-light">{{ $ch['periodo'] ?: 'N/D' }}</span></td>
                                    <td class="small text-muted">{{ $ch['prof_nombre'] }}</td>
                                    <td class="text-center">{{ $ch['creditos_calc'] }}</td>
                                    <td class="text-center font-monospace fw-bold fs-6">
                                        @if($ch['calificacion'] !== null && $ch['calificacion'] !== '')
                                            {{ number_format((float)$ch['calificacion'], 1) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($ch['estado_calc'] === 'Aprobado')
                                            <span class="badge bg-success rounded-pill px-3 py-1">Aprobado</span>
                                        @elseif($ch['estado_calc'] === 'Reprobado')
                                            <span class="badge bg-danger rounded-pill px-3 py-1">Reprobado</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-3 py-1">En Curso</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-book fs-1 d-block mb-2"></i>
                                        El estudiante no tiene asignaturas matriculadas o registradas en el sistema.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. ESTADO DE CUENTA & FINANZAS -->
                <div class="tab-pane fade" id="finanzas" role="tabpanel">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="finance-kpi-card" style="border-left: 4px solid #3b82f6;">
                                <div class="finance-kpi-label small text-uppercase fw-bold">Total Facturado</div>
                                <h3 class="finance-kpi-value fw-bold mb-0">₡{{ number_format($totalFacturado, 2) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="finance-kpi-card" style="border-left: 4px solid #10b981;">
                                <div class="finance-kpi-label small text-uppercase fw-bold">Total Pagado</div>
                                <h3 class="text-success fw-bold mb-0">₡{{ number_format($totalPagado, 2) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="finance-kpi-card" style="border-left: 4px solid {{ $saldoPendiente > 0 ? '#ef4444' : '#10b981' }};">
                                <div class="finance-kpi-label small text-uppercase fw-bold">Saldo Pendiente</div>
                                <h3 class="{{ $saldoPendiente > 0 ? 'text-danger' : 'text-success' }} fw-bold mb-0">₡{{ number_format($saldoPendiente, 2) }}</h3>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold exp-section-title mb-3">Historial de Boletas Emitidas</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 exp-table">
                            <thead class="exp-thead">
                                <tr>
                                    <th class="py-3 text-muted">Boleta #</th>
                                    <th class="py-3 text-muted">Período</th>
                                    <th class="py-3 text-muted">Fecha Emisión</th>
                                    <th class="py-3 text-muted text-end">Total</th>
                                    <th class="py-3 text-muted text-end">Pagado</th>
                                    <th class="py-3 text-muted text-end">Saldo</th>
                                    <th class="py-3 text-muted text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($boletasHistorial as $item)
                                @php $b = $item['boleta']; @endphp
                                <tr>
                                    <td class="font-monospace fw-bold text-primary">{{ $b->numero_boleta }}</td>
                                    <td>{{ $b->periodo ?: 'N/D' }}</td>
                                    <td>{{ $b->fecha_creacion ? date('d/m/Y', strtotime($b->fecha_creacion)) : 'N/D' }}</td>
                                    <td class="text-end fw-bold">₡{{ number_format((float)$b->total, 2) }}</td>
                                    <td class="text-end text-success">₡{{ number_format((float)$b->monto_pagado, 2) }}</td>
                                    <td class="text-end text-danger fw-bold">₡{{ number_format((float)$b->saldo_pendiente, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-pill px-3 py-1">{{ ucfirst($b->estado) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-wallet2 fs-1 d-block mb-2"></i>
                                        No hay boletas de pago registradas para este estudiante.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 4. BÓVEDA DOCUMENTAL DINÁMICA -->
                <div class="tab-pane fade" id="boveda" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold exp-section-title mb-0">Bóveda Documental Jerárquica (EDMS)</h5>
                            <small class="text-muted">Almacenamiento clasificado por categorías para identificación, títulos, comprobantes y cartas.</small>
                        </div>
                        <button type="button" class="btn btn-success btn-sm px-4 rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#modalSubirDoc">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Subir Documento
                        </button>
                    </div>

                    <div class="row g-4">
                        @foreach($categoriasBoveda as $catKey => $catNombre)
                        @php
                            $docsCat = $expediente->archivos->filter(function($arch) use ($catKey) {
                                return \App\Services\ExpedienteStorageService::getCategoriaSubfolder($arch->tipo_documento, $arch->categoria) === $catKey;
                            });
                        @endphp
                        <div class="col-lg-6">
                            <div class="vault-cat-card h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold vault-cat-title mb-0">{{ $catNombre }}</h6>
                                    <span class="badge vault-count-badge rounded-pill">{{ $docsCat->count() }}</span>
                                </div>
                                
                                @if($docsCat->isEmpty())
                                    <div class="text-muted small py-2 fst-italic">Sin documentos en esta categoría.</div>
                                @else
                                    <div class="list-group list-group-flush">
                                        @foreach($docsCat as $doc)
                                        @php $resDoc = \App\Services\ExpedienteStorageService::resolveFilePath($doc->nombre_servidor); @endphp
                                        <div class="list-group-item bg-transparent vault-file-item px-0 py-2 d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 75%;">
                                                <i class="bi bi-file-earmark-text-fill text-primary"></i>
                                                <div class="text-truncate">
                                                    <a href="{{ $resDoc['web_url'] }}" target="_blank" class="vault-doc-link text-decoration-none fw-semibold small">
                                                        {{ $doc->nombre_original }}
                                                    </a>
                                                    <div class="vault-doc-meta" style="font-size: 0.75rem;">{{ $doc->fecha_subida ? $doc->fecha_subida->format('d/m/Y g:i a') : '' }} • {{ $doc->subido_por }}</div>
                                                </div>
                                            </div>
                                            <button onclick="eliminarDocumento({{ $doc->id_archivo }})" class="btn btn-link text-danger p-0 ms-2" title="Eliminar archivo">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- 5. BITÁCORA & NOTAS INTERNAS -->
                <div class="tab-pane fade" id="bitacora" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="bitacora-form-card">
                                <h6 class="fw-bold bitacora-title mb-3"><i class="bi bi-pencil-square me-2 text-warning"></i>Nueva Nota Administrativa</h6>
                                <form id="formObservacion">
                                    <input type="hidden" name="id_expediente" value="{{ $expediente->id_expediente }}">
                                    <div class="mb-3">
                                        <label class="form-label small text-muted">Categoría</label>
                                        <select name="categoria" class="form-select form-select-sm exp-input">
                                            <option value="General">General</option>
                                            <option value="Académica">Académica / Convalidaciones</option>
                                            <option value="Financiera">Financiera / Arreglo de Pago</option>
                                            <option value="Conducta">Conducta / Disciplinaria</option>
                                            <option value="Trámite">Trámite de Graduación</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted">Observación</label>
                                        <textarea name="observacion" rows="4" class="form-control exp-input" placeholder="Escriba la nota de seguimiento interno..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm px-4 rounded-pill fw-semibold w-100">
                                        <i class="bi bi-save me-1"></i> Guardar Nota
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <h6 class="fw-bold bitacora-title mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Historial de Notas Internas</h6>
                            <div id="contenedorNotas">
                                @forelse($expediente->observaciones as $obs)
                                <div class="note-item-card mb-3" style="border-left: 4px solid #3b82f6;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-secondary bg-opacity-25 text-info">{{ $obs->categoria }}</span>
                                        <small class="note-meta">{{ $obs->fecha_registro ? $obs->fecha_registro->format('d/m/Y g:i a') : '' }}</small>
                                    </div>
                                    <p class="note-text small mb-1">{{ $obs->observacion }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="note-meta">Por: <strong>{{ $obs->autor_nombre ?: 'Administrador' }}</strong></small>
                                        <button onclick="eliminarObservacion({{ $obs->id }})" class="btn btn-link text-danger p-0 small">Eliminar</button>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                                    No hay notas internas registradas en la bitácora.
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- MODAL SUBIR DOCUMENTO -->
<div class="modal fade" id="modalSubirDoc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-doc-upload">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-cloud-arrow-up-fill text-success me-2"></i>Subir Documento a la Bóveda</h5>
                <button type="button" class="btn-close modal-close-btn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSubirArchivo" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id_expediente" value="{{ $expediente->id_expediente }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Tipo de Documento</label>
                        <select name="tipo_documento" class="form-select exp-input" required>
                            <option value="cedula">Cédula / Documento de Identidad</option>
                            <option value="titulo_secundaria">Título de Bachiller en Secundaria</option>
                            <option value="titulo_universitario">Título Universitario Previo</option>
                            <option value="certificacion_notas">Certificación de Notas / Convalidación</option>
                            <option value="fotografia">Fotografía Oficial para Carnet</option>
                            <option value="firma">Firma Digitalizada</option>
                            <option value="carta_recomendacion">Carta de Recomendación / Atestados</option>
                            <option value="comprobante_pago">Comprobante de Pago / Depósito</option>
                            <option value="otro">Otro Documento</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Categoría en Bóveda</label>
                        <select name="categoria" class="form-select exp-input" required>
                            @foreach($categoriasBoveda as $catK => $catV)
                                <option value="{{ $catK }}">{{ $catV }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Seleccionar Archivo (PDF, JPG, PNG, DOCX, ZIP - Max 25MB)</label>
                        <input type="file" name="archivo" class="form-control exp-input" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Descripción u Observación (Opcional)</label>
                        <input type="text" name="descripcion" class="form-control exp-input" placeholder="Ej: Copia legalizada de título">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4" id="btnGuardarDoc">Subir Archivo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Subir documento vía AJAX
    document.getElementById('formSubirArchivo').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnGuardarDoc');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Subiendo...';

        const formData = new FormData(this);

        fetch("{{ route('expedientes.subir_documento') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = 'Subir Archivo';
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Subida Exitosa!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', data.message || 'Error al subir documento', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = 'Subir Archivo';
            Swal.fire('Error', 'Fallo de comunicación con el servidor', 'error');
        });
    });

    // Eliminar documento vía AJAX
    function eliminarDocumento(idArchivo) {
        Swal.fire({
            title: '¿Eliminar documento?',
            text: 'Esta acción borrará el archivo de la bóveda de forma permanente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('/expedientes/documentos') }}/${idArchivo}/eliminar`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }
        });
    }

    // Guardar nota de bitácora
    document.getElementById('formObservacion').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch("{{ route('expedientes.observaciones.guardar') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Guardado',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    });

    // Eliminar nota de bitácora
    function eliminarObservacion(idObs) {
        Swal.fire({
            title: '¿Eliminar nota?',
            text: 'Se removerá la nota de la bitácora interna.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Eliminar'
        }).then((res) => {
            if (res.isConfirmed) {
                fetch(`{{ url('/expedientes/observaciones') }}/${idObs}/eliminar`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }
        });
    }
</script>
@endsection
