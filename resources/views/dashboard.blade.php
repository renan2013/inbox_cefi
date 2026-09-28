@extends('layouts.app')

@section('title', 'Inbox BPM - Dashboard Principal')

@section('styles')
<style>
    :root {
        --primary-color: #5fb230;
        --card-bg: var(--card-dark);
        --border-color: var(--border-dark);
    }

    .dashboard-container {
        padding-top: 2rem;
        padding-bottom: 4rem;
    }

    .dash-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 1.25rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }

    .dash-card:hover {
        border-color: rgba(95, 178, 48, 0.3);
    }

    .stat-card-item {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 1.25rem;
        padding: 1.25rem 1.5rem;
        transition: all 0.3s ease;
    }

    .stat-card-item:hover {
        transform: translateY(-3px);
    }

    /* Table custom styles to fix Dark & Light mode contrast */
    .table-custom {
        --bs-table-bg: transparent !important;
        --bs-table-color: var(--text-light) !important;
        --bs-table-border-color: var(--border-color) !important;
        --bs-table-hover-bg: rgba(255, 255, 255, 0.05) !important;
        --bs-table-hover-color: var(--text-light) !important;
        color: var(--text-light) !important;
        background-color: transparent !important;
    }

    [data-theme="light"] .table-custom {
        --bs-table-hover-bg: rgba(0, 0, 0, 0.03) !important;
    }

    .table-custom th, .table-custom td {
        background-color: transparent !important;
        color: inherit !important;
        border-color: var(--border-color) !important;
    }

    .task-title-text {
        color: var(--text-light) !important;
        font-weight: 700;
    }

    .task-subtitle-text {
        color: var(--text-muted) !important;
    }

    .table-header-row th {
        color: var(--text-muted) !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        background-color: transparent !important;
    }

    /* Kanban Styles */
    .kanban-board {
        display: none;
        gap: 1.25rem;
        overflow-x: auto;
        padding: 0.5rem 0;
    }

    .kanban-board.active {
        display: flex;
    }

    .kanban-column {
        flex: 1;
        min-width: 260px;
        background-color: rgba(15, 23, 42, 0.3);
        border: 1px solid var(--border-color);
        border-radius: 1rem;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        max-height: 75vh;
    }

    [data-theme="light"] .kanban-column {
        background-color: #f1f5f9 !important;
    }

    .kanban-cards-container {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        overflow-y: auto;
        flex-grow: 1;
        min-height: 150px;
        padding: 0.25rem;
    }

    .kanban-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        padding: 1rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        cursor: grab;
        transition: all 0.2s ease;
    }

    .kanban-card:hover {
        transform: translateY(-2px);
        border-color: var(--primary-color);
    }

    .btn-view-toggle {
        border: 1px solid var(--border-color);
        background: transparent;
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.4rem 1rem;
        transition: all 0.2s ease;
    }

    .btn-view-toggle.active {
        background-color: var(--primary-color) !important;
        color: #ffffff !important;
        border-color: var(--primary-color) !important;
    }

    .form-select-dash {
        background-color: rgba(15, 23, 42, 0.4) !important;
        border: 1px solid var(--border-color) !important;
        color: #f8fafc !important;
        border-radius: 0.5rem !important;
    }

    [data-theme="light"] .form-select-dash {
        background-color: #ffffff !important;
        color: #0f172a !important;
        border-color: #cbd5e1 !important;
    }

    /* --- MONDAY.COM STYLED ELEMENTS --- */
    .monday-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        min-width: 120px;
        padding: 0.35rem 0.85rem;
        border-radius: 0.35rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: capitalize;
        cursor: pointer;
        user-select: none;
        transition: transform 0.15s ease, filter 0.15s ease, box-shadow 0.15s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        border: none;
    }

    .monday-pill:hover {
        transform: translateY(-1px) scale(1.02);
        filter: brightness(1.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }

    .monday-pill:active {
        transform: scale(0.98);
    }

    /* Monday Official Color Palette */
    .status-completada { background-color: #00c875 !important; color: #ffffff !important; }
    .status-en_proceso { background-color: #fdab3d !important; color: #ffffff !important; }
    .status-pendiente { background-color: #579bfc !important; color: #ffffff !important; }
    .status-cancelada { background-color: #df2f4a !important; color: #ffffff !important; }

    .priority-alta { background-color: #e2445c !important; color: #ffffff !important; }
    .priority-media { background-color: #579bfc !important; color: #ffffff !important; }
    .priority-baja { background-color: #00c875 !important; color: #ffffff !important; }

    /* Monday Dropdown Menu */
    .monday-dropdown-menu {
        background-color: #1e293b !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        border-radius: 0.65rem !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4) !important;
        padding: 0.4rem !important;
        min-width: 160px;
        z-index: 1050;
    }

    .monday-dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.45rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #f1f5f9 !important;
        border-radius: 0.4rem;
        transition: background-color 0.15s ease;
        cursor: pointer;
    }

    .monday-dropdown-item:hover {
        background-color: rgba(255, 255, 255, 0.08) !important;
    }

    .monday-color-dot {
        width: 12px;
        height: 12px;
        border-radius: 3px;
        flex-shrink: 0;
    }

    /* Monday Avatars */
    .monday-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 800;
        color: #ffffff;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border: 2px solid rgba(255, 255, 255, 0.15);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        cursor: default;
    }

    .monday-avatar-empty {
        background: rgba(255, 255, 255, 0.08);
        color: var(--text-muted);
        border: 1px dashed var(--border-color);
    }

    /* Monday Quick-Add Row */
    .monday-quick-row {
        background: rgba(255, 255, 255, 0.015);
        border-top: 1px dashed var(--border-color) !important;
        transition: background-color 0.2s ease;
    }

    .monday-quick-row:hover {
        background: rgba(255, 255, 255, 0.04);
    }

    .monday-quick-input {
        background: transparent !important;
        border: 1px solid transparent !important;
        color: var(--text-light) !important;
        font-size: 0.85rem;
        padding: 0.45rem 0.75rem;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .monday-quick-input:focus {
        background: rgba(15, 23, 42, 0.4) !important;
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 3px rgba(95, 178, 48, 0.15) !important;
        outline: none;
    }

    .monday-progress-strip {
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.06);
        display: flex;
    }

    .monday-progress-seg {
        transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-md-5 dashboard-container">
    
    <!-- Top Header & Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 g-3">
        <div>
            <h1 class="h2 fw-bold text-white mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i> Dashboard Principal</h1>
            <p class="text-white-50 mb-0">Bienvenido de nuevo, <strong>{{ Auth::user()->nombre }}</strong>. Tienes <span class="badge bg-primary rounded-pill px-2 py-1">{{ $tareas_pendientes_count }}</span> tareas pendientes.</p>
        </div>
        
        @if ($es_admin)
            <div class="dropdown mt-2 mt-md-0">
                <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-plus-lg me-2"></i> Nueva Tarea
                </button>
                <ul class="dropdown-menu dropdown-menu-dark-custom shadow border-0">
                    <li>
                        <a class="dropdown-item dropdown-item-custom py-2" href="{{ route('tareas.seleccionar_plantilla') }}">
                            <i class="bi bi-file-earmark-text text-primary me-2"></i> Desde Plantilla
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item dropdown-item-custom py-2" href="{{ route('tareas.create') }}">
                            <i class="bi bi-plus-circle text-success me-2"></i> Tarea en Blanco
                        </a>
                    </li>
                </ul>
            </div>
        @endif
    </div>

    <!-- Widgets Superiores de Estadísticas y Filtros -->
    <div class="row g-3 mb-4">
        <!-- Estadísticas -->
        <div class="col-xl-9 col-lg-8">
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <div class="stat-card-item border-start border-4 border-warning">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-white-50 fw-bold text-uppercase d-block" style="font-size: 0.7rem;">PENDIENTES</small>
                                <h3 class="mb-0 fw-bold text-warning">{{ $stats['pendiente'] }}</h3>
                            </div>
                            <div class="p-2 rounded-circle style-icon-box" style="background: rgba(255, 193, 7, 0.15);">
                                <i class="bi bi-clock-history text-warning fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <div class="stat-card-item border-start border-4 border-info">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-white-50 fw-bold text-uppercase d-block" style="font-size: 0.7rem;">EN PROCESO</small>
                                <h3 class="mb-0 fw-bold text-info">{{ $stats['en_proceso'] }}</h3>
                            </div>
                            <div class="p-2 rounded-circle style-icon-box" style="background: rgba(13, 202, 240, 0.15);">
                                <i class="bi bi-person-workspace text-info fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <div class="stat-card-item border-start border-4 border-success">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-white-50 fw-bold text-uppercase d-block" style="font-size: 0.7rem;">COMPLETADAS</small>
                                <h3 class="mb-0 fw-bold text-success">{{ $stats['completada'] }}</h3>
                            </div>
                            <div class="p-2 rounded-circle style-icon-box" style="background: rgba(25, 135, 84, 0.15);">
                                <i class="bi bi-check2-circle text-success fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-6">
                    <div class="stat-card-item border-start border-4 border-primary">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-white-50 fw-bold text-uppercase d-block" style="font-size: 0.7rem;">TOTAL TAREAS</small>
                                <h3 class="mb-0 fw-bold text-primary">{{ $stats['total'] }}</h3>
                            </div>
                            <div class="p-2 rounded-circle style-icon-box" style="background: rgba(95, 178, 48, 0.15);">
                                <i class="bi bi-journal-check text-primary fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros Rápidos -->
        <div class="col-xl-3 col-lg-4">
            <div class="dash-card p-3 h-100 d-flex align-items-center">
                <form action="{{ route('dashboard') }}" method="GET" class="w-100">
                    <div class="d-flex gap-2">
                        <select name="anio" class="form-select form-select-dash form-select-sm" title="Año">
                            @php $year_current = date('Y'); @endphp
                            @for ($y = 2024; $y <= $year_current + 1; $y++)
                                <option value="{{ $y }}" {{ $anio_seleccionado == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <select name="etiqueta" class="form-select form-select-dash form-select-sm">
                            <option value="">Todas las Etiquetas...</option>
                            @foreach ($etiquetas_disponibles as $etiqueta_item)
                                <option value="{{ $etiqueta_item->id }}" {{ $etiqueta_seleccionada == $etiqueta_item->id ? 'selected' : '' }}>
                                    {{ $etiqueta_item->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary btn-sm px-3 rounded-3" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                    @if ($etiqueta_seleccionada || $anio_seleccionado != date('Y'))
                        <div class="text-center mt-2">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none small text-white-50"><i class="bi bi-x-circle me-1"></i>Limpiar filtros</a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content Area: Tasks Table & Kanban -->
        <div class="{{ (count($cumpleaneros) > 0 || count($mis_cursos_asignados) > 0) ? 'col-lg-9' : 'col-12' }}">
            
            <!-- Alerta de Inventario en Dashboard -->
            @if ($productos_bajo_stock > 0)
                <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center mb-4 p-3 shadow-sm" style="background-color: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3) !important;">
                    <div class="bg-danger bg-opacity-20 p-3 rounded-circle me-3">
                        <i class="bi bi-box-seam-fill text-danger fs-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading fw-bold mb-1">¡Alerta de Inventario!</h5>
                        <p class="mb-0">Hay <strong>{{ $productos_bajo_stock }}</strong> productos que han alcanzado su stock mínimo y requieren renovación.</p>
                    </div>
                    <a href="{{ route('inventario.index') }}" class="btn btn-danger btn-sm rounded-pill px-4 fw-bold">Gestionar Inventario</a>
                </div>
            @endif

            <div class="dash-card p-0 overflow-hidden">
                <!-- Header de la Tarjeta de Tareas -->
                <div class="px-4 py-3 border-bottom border-secondary d-flex justify-content-between align-items-center bg-dark bg-opacity-25">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="bi bi-list-task me-2 text-primary"></i>
                        {{ $es_admin ? 'Listado General de Tareas' : 'Mis Tareas Asignadas' }}
                    </h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="btn-group rounded-pill p-1 border border-secondary" role="group" aria-label="Vista de Tareas">
                            <button type="button" class="btn btn-view-toggle rounded-pill px-3 active" id="btn-view-table" onclick="switchView('table')">
                                <i class="bi bi-table me-1"></i> Tabla
                            </button>
                            <button type="button" class="btn btn-view-toggle rounded-pill px-3" id="btn-view-kanban" onclick="switchView('kanban')">
                                <i class="bi bi-kanban me-1"></i> Tablero
                            </button>
                        </div>
                        <span class="badge bg-primary rounded-pill px-3 py-2" style="font-size: 0.8rem;">{{ $tareas->total() }} registros</span>
                    </div>
                </div>

                <div class="p-4">
                    <!-- 1. Vista de Tabla -->
                    <div id="view-table-container">
                        <div class="table-responsive">
                            <table class="table table-custom table-hover align-middle mb-0" id="monday-tasks-table">
                                <thead>
                                    <tr class="table-header-row">
                                        <th class="ps-4" style="width: 50px;">#</th>
                                        <th>Tarea</th>
                                        <th style="width: 130px;">Responsable</th>
                                        <th style="width: 140px;">Prioridad</th>
                                        <th style="width: 160px;">Estado</th>
                                        <th style="width: 140px;">Vencimiento</th>
                                        <th class="text-end pe-4" style="width: 90px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="monday-tasks-tbody">
                                    @forelse ($tareas as $index => $tarea)
                                        <tr id="task-row-{{ $tarea->id }}" data-task-id="{{ $tarea->id }}">
                                            <td class="ps-4 task-subtitle-text small">{{ $tareas->firstItem() + $index }}</td>
                                            <td>
                                                <div class="task-title-text mb-1">
                                                    {{ $tarea->titulo }}
                                                    @if(!empty($tarea->id_curso_activo))
                                                        <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 ms-2">
                                                            <i class="bi bi-mortarboard-fill me-1"></i>{{ $tarea->curso_nombre }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if(!empty($tarea->descripcion))
                                                    <div class="small task-subtitle-text text-truncate" style="max-width: 320px;">
                                                        {{ $tarea->descripcion }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-1">
                                                    @if(isset($asignaciones[$tarea->id]) && count($asignaciones[$tarea->id]) > 0)
                                                        @foreach($asignaciones[$tarea->id] as $asig)
                                                            @php
                                                                $initials = strtoupper(substr($asig->nombre, 0, 1) . substr($asig->apellidos, 0, 1));
                                                            @endphp
                                                            <span class="monday-avatar" title="{{ $asig->nombre }} {{ $asig->apellidos }}" data-bs-toggle="tooltip">
                                                                {{ $initials }}
                                                            </span>
                                                        @endforeach
                                                    @else
                                                        <span class="monday-avatar monday-avatar-empty" title="Sin Asignar" data-bs-toggle="tooltip">
                                                            <i class="bi bi-person"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <!-- Píldora de Prioridad Estilo Monday -->
                                                <div class="dropdown">
                                                    <button class="monday-pill priority-{{ $tarea->prioridad ?? 'media' }}" id="priority-pill-{{ $tarea->id }}" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 95px;" title="Cambiar prioridad">
                                                        <span class="priority-label">{{ ucfirst($tarea->prioridad ?? 'media') }}</span>
                                                        <i class="bi bi-chevron-down" style="font-size: 0.6rem; opacity: 0.8;"></i>
                                                    </button>
                                                    <ul class="dropdown-menu monday-dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item monday-dropdown-item" href="javascript:void(0)" onclick="cambiarPrioridadAjax({{ $tarea->id }}, 'alta')">
                                                                <span class="monday-color-dot" style="background-color: #e2445c;"></span> Alta
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item monday-dropdown-item" href="javascript:void(0)" onclick="cambiarPrioridadAjax({{ $tarea->id }}, 'media')">
                                                                <span class="monday-color-dot" style="background-color: #579bfc;"></span> Media
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item monday-dropdown-item" href="javascript:void(0)" onclick="cambiarPrioridadAjax({{ $tarea->id }}, 'baja')">
                                                                <span class="monday-color-dot" style="background-color: #00c875;"></span> Baja
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                            <td>
                                                <!-- Píldora de Estado Estilo Monday -->
                                                <div class="dropdown">
                                                    <button class="monday-pill status-{{ $tarea->estado }}" id="status-pill-{{ $tarea->id }}" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Cambiar estado">
                                                        <span class="status-label">{{ ucfirst(str_replace('_', ' ', $tarea->estado)) }}</span>
                                                        <i class="bi bi-chevron-down" style="font-size: 0.65rem; opacity: 0.8;"></i>
                                                    </button>
                                                    <ul class="dropdown-menu monday-dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item monday-dropdown-item" href="javascript:void(0)" onclick="cambiarEstadoAjax({{ $tarea->id }}, 'completada')">
                                                                <span class="monday-color-dot" style="background-color: #00c875;"></span> Listo / Completada
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item monday-dropdown-item" href="javascript:void(0)" onclick="cambiarEstadoAjax({{ $tarea->id }}, 'en_proceso')">
                                                                <span class="monday-color-dot" style="background-color: #fdab3d;"></span> En Proceso
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item monday-dropdown-item" href="javascript:void(0)" onclick="cambiarEstadoAjax({{ $tarea->id }}, 'pendiente')">
                                                                <span class="monday-color-dot" style="background-color: #579bfc;"></span> Pendiente
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item monday-dropdown-item" href="javascript:void(0)" onclick="cambiarEstadoAjax({{ $tarea->id }}, 'cancelada')">
                                                                <span class="monday-color-dot" style="background-color: #df2f4a;"></span> Detenida / Cancelada
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                            <td>
                                                @if (!empty($tarea->fecha_vencimiento) && $tarea->fecha_vencimiento !== '0000-00-00')
                                                    @php
                                                        $hoy = \Carbon\Carbon::today();
                                                        $vencimiento = \Carbon\Carbon::parse($tarea->fecha_vencimiento);
                                                        $dias = $hoy->diffInDays($vencimiento, false);
                                                    @endphp

                                                    @if ($tarea->estado == 'completada')
                                                        <span class="task-subtitle-text small"><i class="bi bi-check-all me-1 text-success"></i>Finalizada</span>
                                                    @elseif ($dias < 0)
                                                        <span class="text-danger fw-bold small"><i class="bi bi-exclamation-octagon me-1"></i>Atrasada ({{ abs($dias) }} d)</span>
                                                    @elseif ($dias == 0)
                                                        <span class="text-warning fw-bold small"><i class="bi bi-hourglass-split me-1"></i>Hoy</span>
                                                    @elseif ($dias == 1)
                                                        <span class="text-warning small"><i class="bi bi-clock me-1"></i>Mañana</span>
                                                    @else
                                                        <span class="{{ $dias <= 3 ? 'text-warning' : 'task-subtitle-text' }} small"><i class="bi bi-calendar3 me-1"></i>{{ $dias }} días</span>
                                                    @endif
                                                @else
                                                    <span class="task-subtitle-text small">Sin fecha</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group rounded-pill overflow-hidden border border-secondary shadow-sm">
                                                    <button type="button" class="btn btn-sm btn-outline-info border-0" onclick="alert('Tarea: {{ addslashes($tarea->titulo) }}')" title="Ver Detalle">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="empty-row-placeholder">
                                            <td colspan="7" class="text-center py-5 task-subtitle-text">
                                                <i class="bi bi-emoji-smile fs-1 d-block mb-2 text-muted"></i>
                                                <p class="mb-0">No hay tareas que coincidan con los filtros seleccionados.</p>
                                            </td>
                                        </tr>
                                    @endforelse

                                    <!-- Fila de Creación Rápida Estilo Monday.com -->
                                    <tr class="monday-quick-row">
                                        <td class="ps-4 text-primary text-center"><i class="bi bi-plus-lg fw-bold"></i></td>
                                        <td colspan="6" class="py-2 pe-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="text" id="monday-inline-task-title" class="form-control form-control-sm monday-quick-input flex-grow-1" placeholder="+ Añadir una nueva tarea y presiona Enter..." autocomplete="off">
                                                <button class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold" type="button" id="btn-quick-add-task" onclick="ejecutarCreacionRapida()">
                                                    <i class="bi bi-arrow-return-left me-1"></i>Añadir
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Barra de Resumen de Progreso Estilo Monday -->
                        <div class="mt-3 p-3 rounded-4 bg-dark bg-opacity-25 border border-secondary">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                                <span class="small fw-bold text-white"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Distribución del Tablero</span>
                                <div class="d-flex align-items-center gap-3 small" id="monday-legend-stats">
                                    <span><span class="monday-color-dot d-inline-block me-1" style="background-color: #00c875;"></span> <strong id="count-completada">0</strong> Listo</span>
                                    <span><span class="monday-color-dot d-inline-block me-1" style="background-color: #fdab3d;"></span> <strong id="count-en_proceso">0</strong> En Proceso</span>
                                    <span><span class="monday-color-dot d-inline-block me-1" style="background-color: #579bfc;"></span> <strong id="count-pendiente">0</strong> Pendiente</span>
                                    <span><span class="monday-color-dot d-inline-block me-1" style="background-color: #df2f4a;"></span> <strong id="count-cancelada">0</strong> Cancelada</span>
                                </div>
                            </div>
                            <div class="monday-progress-strip">
                                <div id="m-prog-completada" class="monday-progress-seg status-completada" style="width: 0%;" title="Completadas"></div>
                                <div id="m-prog-en_proceso" class="monday-progress-seg status-en_proceso" style="width: 0%;" title="En Proceso"></div>
                                <div id="m-prog-pendiente" class="monday-progress-seg status-pendiente" style="width: 0%;" title="Pendientes"></div>
                                <div id="m-prog-cancelada" class="monday-progress-seg status-cancelada" style="width: 0%;" title="Canceladas"></div>
                            </div>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $tareas->links() }}
                        </div>
                        </div>
                    </div>

                    <!-- 2. Vista de Tablero Kanban -->
                    @php
                        $kanban_tareas = [
                            'pendiente' => [],
                            'en_proceso' => [],
                            'completada' => [],
                            'cancelada' => []
                        ];
                        foreach ($tareas as $t) {
                            $est = $t->estado;
                            if (array_key_exists($est, $kanban_tareas)) {
                                $kanban_tareas[$est][] = $t;
                            } else {
                                $kanban_tareas['pendiente'][] = $t;
                            }
                        }
                    @endphp
                    <div id="view-kanban-container" class="kanban-board">
                        <!-- Columna: Pendiente -->
                        <div class="kanban-column" id="col-pendiente">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold text-warning small text-uppercase"><i class="bi bi-clock-history me-1"></i> Pendiente</span>
                                <span class="badge bg-warning text-dark rounded-pill">{{ count($kanban_tareas['pendiente']) }}</span>
                            </div>
                            <div class="kanban-cards-container">
                                @foreach ($kanban_tareas['pendiente'] as $t)
                                    <div class="kanban-card">
                                        <div class="fw-bold task-title-text small mb-1">{{ $t->titulo }}</div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-secondary">
                                            <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-25 small" style="font-size: 0.65rem;">{{ ucfirst($t->prioridad) }}</span>
                                            <button class="btn btn-sm btn-outline-info py-0 px-2" style="font-size: 0.7rem;" onclick="cambiarEstadoAjax({{ $t->id }}, 'en_proceso')">Procesar &rarr;</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Columna: En Proceso -->
                        <div class="kanban-column" id="col-en_proceso">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold text-info small text-uppercase"><i class="bi bi-person-workspace me-1"></i> En Proceso</span>
                                <span class="badge bg-info text-dark rounded-pill">{{ count($kanban_tareas['en_proceso']) }}</span>
                            </div>
                            <div class="kanban-cards-container">
                                @foreach ($kanban_tareas['en_proceso'] as $t)
                                    <div class="kanban-card">
                                        <div class="fw-bold task-title-text small mb-1">{{ $t->titulo }}</div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-secondary">
                                            <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-25 small" style="font-size: 0.65rem;">{{ ucfirst($t->prioridad) }}</span>
                                            <button class="btn btn-sm btn-outline-success py-0 px-2" style="font-size: 0.7rem;" onclick="cambiarEstadoAjax({{ $t->id }}, 'completada')">Completar &check;</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Columna: Completada -->
                        <div class="kanban-column" id="col-completada">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold text-success small text-uppercase"><i class="bi bi-check2-circle me-1"></i> Completada</span>
                                <span class="badge bg-success text-white rounded-pill">{{ count($kanban_tareas['completada']) }}</span>
                            </div>
                            <div class="kanban-cards-container">
                                @foreach ($kanban_tareas['completada'] as $t)
                                    <div class="kanban-card">
                                        <div class="fw-bold task-title-text small mb-1">{{ $t->titulo }}</div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-secondary">
                                            <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 small" style="font-size: 0.65rem;">Completada</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Columna: Cancelada -->
                        <div class="kanban-column" id="col-cancelada">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold text-secondary small text-uppercase"><i class="bi bi-x-circle me-1"></i> Cancelada</span>
                                <span class="badge bg-secondary text-white rounded-pill">{{ count($kanban_tareas['cancelada']) }}</span>
                            </div>
                            <div class="kanban-cards-container">
                                @foreach ($kanban_tareas['cancelada'] as $t)
                                    <div class="kanban-card opacity-50">
                                        <div class="fw-bold task-title-text small mb-1">{{ $t->titulo }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Widgets Secundarios (Cumpleaños y Mis Cursos) -->
        @if (count($cumpleaneros) > 0 || count($mis_cursos_asignados) > 0)
            <div class="col-lg-3">
                <div class="row g-4">
                    
                    <!-- Widget Cumpleaños del Mes -->
                    @if (count($cumpleaneros) > 0)
                        <div class="col-12">
                            <div class="dash-card border-top border-4 border-primary p-3">
                                <div class="px-2 py-1 mb-3">
                                    <h6 class="mb-0 fw-bold text-white"><i class="bi bi-cake2-fill text-primary me-2"></i> Cumpleaños del Mes</h6>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    @foreach ($cumpleaneros as $c)
                                        <div class="d-flex align-items-center justify-content-between p-2 rounded bg-dark bg-opacity-25 border border-secondary">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary text-white rounded-circle shadow-sm p-1 me-2 text-center" style="min-width: 36px; height: 36px;">
                                                    <span class="d-block fw-bold lh-1 small">{{ \Carbon\Carbon::parse($c->fecha_nacimiento)->format('d') }}</span>
                                                    <small style="font-size: 0.6rem;">{{ \Carbon\Carbon::parse($c->fecha_nacimiento)->format('M') }}</small>
                                                </div>
                                                <span class="small fw-bold text-white">{{ $c->nombre }} {{ $c->apellidos }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Widget Mis Cursos -->
                    @if (count($mis_cursos_asignados) > 0)
                        <div class="col-12">
                            <div class="dash-card border-top border-4 border-info p-3">
                                <div class="px-2 py-1 mb-3 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold text-white"><i class="bi bi-book-half text-info me-2"></i> Mis Cursos</h6>
                                    <a href="{{ route('mis_cursos.index') }}" class="text-info small text-decoration-none">Ver todos</a>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    @foreach ($mis_cursos_asignados as $curso_item)
                                        <div class="p-3 border border-secondary rounded bg-dark bg-opacity-25">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-secondary text-white small" style="font-size: 0.65rem;">{{ $curso_item->codigo }}</span>
                                                <span class="badge bg-info text-dark small" style="font-size: 0.65rem;">{{ $curso_item->periodo }}</span>
                                            </div>
                                            <h6 class="small fw-bold mb-1 text-white text-truncate" title="{{ $curso_item->materia }}">{{ $curso_item->materia }}</h6>
                                            <p class="mb-2 text-white-50" style="font-size: 0.7rem;"><i class="bi bi-mortarboard me-1"></i>{{ $curso_item->nombre_programa }}</p>
                                            <a href="{{ route('mis_cursos.ver', $curso_item->id_curso_activo) }}" class="btn btn-sm btn-info text-white w-100 py-1 fw-bold" style="font-size: 0.75rem;">Gestionar Curso</a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const STATUS_MAP = {
        'completada': { label: 'Listo / Completada', class: 'status-completada' },
        'en_proceso': { label: 'En Proceso', class: 'status-en_proceso' },
        'pendiente': { label: 'Pendiente', class: 'status-pendiente' },
        'cancelada': { label: 'Detenida / Cancelada', class: 'status-cancelada' }
    };

    const PRIORITY_MAP = {
        'alta': { label: 'Alta', class: 'priority-alta' },
        'media': { label: 'Media', class: 'priority-media' },
        'baja': { label: 'Baja', class: 'priority-baja' }
    };

    function switchView(view) {
        if (view === 'table') {
            $('#view-table-container').show();
            $('#view-kanban-container').removeClass('active').hide();
            $('#btn-view-table').addClass('active');
            $('#btn-view-kanban').removeClass('active');
        } else {
            $('#view-table-container').hide();
            $('#view-kanban-container').addClass('active').css('display', 'flex');
            $('#btn-view-kanban').addClass('active');
            $('#btn-view-table').removeClass('active');
        }
    }

    // --- MONDAY RECEPTIVE AJAX: CAMBIO DE ESTADO SIN RECARGAR PÁGINA ---
    function cambiarEstadoAjax(idTarea, nuevoEstado) {
        const pill = $(`#status-pill-${idTarea}`);
        if (!pill.length) return;

        // 1. Actualización Optimista de la interfaz (Efecto Monday instantáneo)
        pill.removeClass('status-completada status-en_proceso status-pendiente status-cancelada');
        pill.addClass(STATUS_MAP[nuevoEstado].class);
        pill.find('.status-label').text(STATUS_MAP[nuevoEstado].label);

        // Micro-animación de rebote sutil
        pill.css('transform', 'scale(1.1)');
        setTimeout(() => pill.css('transform', ''), 200);

        // 2. Mover la tarjeta si la vista Kanban está activa
        const kanbanCard = $(`#task-row-${idTarea}`).length ? $(`#kanban-card-${idTarea}`) : null;
        if (kanbanCard && kanbanCard.length) {
            $(`#col-${nuevoEstado} .kanban-cards-container`).prepend(kanbanCard);
        }

        // 3. Petición en segundo plano al servidor
        $.post("{{ route('tareas.cambiar_estado') }}", {
            _token: "{{ csrf_token() }}",
            id: idTarea,
            estado: nuevoEstado
        }, function(res) {
            if (res.success) {
                recalcularProgresoMonday();
            }
        }).fail(function() {
            alert('No se pudo actualizar el estado. Revisa tu conexión.');
        });
    }

    // --- MONDAY RECEPTIVE AJAX: CAMBIO DE PRIORIDAD ---
    function cambiarPrioridadAjax(idTarea, nuevaPrioridad) {
        const pill = $(`#priority-pill-${idTarea}`);
        if (!pill.length) return;

        pill.removeClass('priority-alta priority-media priority-baja');
        pill.addClass(PRIORITY_MAP[nuevaPrioridad].class);
        pill.find('.priority-label').text(PRIORITY_MAP[nuevaPrioridad].label);

        pill.css('transform', 'scale(1.1)');
        setTimeout(() => pill.css('transform', ''), 200);

        $.post("{{ route('tareas.cambiar_prioridad') }}", {
            _token: "{{ csrf_token() }}",
            id: idTarea,
            prioridad: nuevaPrioridad
        });
    }

    // --- CREACIÓN RÁPIDA DE TAREA EN LÍNEA (+ AÑADIR TAREA) ---
    function ejecutarCreacionRapida() {
        const input = $('#monday-inline-task-title');
        const titulo = input.val().trim();

        if (!titulo) {
            input.focus();
            input.css('border-color', '#df2f4a');
            setTimeout(() => input.css('border-color', 'transparent'), 1500);
            return;
        }

        const btn = $('#btn-quick-add-task');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.post("{{ route('tareas.crear_rapida') }}", {
            _token: "{{ csrf_token() }}",
            titulo: titulo
        }, function(res) {
            btn.prop('disabled', false).html('<i class="bi bi-arrow-return-left me-1"></i>Añadir');
            if (res.success && res.tarea) {
                input.val('');
                $('#empty-row-placeholder').remove();

                const t = res.tarea;
                const newRowHtml = `
                    <tr id="task-row-${t.id}" data-task-id="${t.id}" style="animation: fadeIn 0.4s ease;">
                        <td class="ps-4 task-subtitle-text small"><i class="bi bi-stars text-primary"></i></td>
                        <td>
                            <div class="task-title-text mb-1">${escapeHtml(t.titulo)}</div>
                        </td>
                        <td>
                            <span class="monday-avatar monday-avatar-empty" title="Sin Asignar">
                                <i class="bi bi-person"></i>
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="monday-pill priority-media" id="priority-pill-${t.id}" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 95px;">
                                    <span class="priority-label">Media</span>
                                    <i class="bi bi-chevron-down" style="font-size: 0.6rem; opacity: 0.8;"></i>
                                </button>
                                <ul class="dropdown-menu monday-dropdown-menu">
                                    <li><a class="dropdown-item monday-dropdown-item" href="javascript:void(0)" onclick="cambiarPrioridadAjax(${t.id}, 'alta')"><span class="monday-color-dot" style="background-color: #e2445c;"></span> Alta</a></li>
                                    <li><a class="dropdown-item monday-dropdown-item" href="javascript:void(0)" onclick="cambiarPrioridadAjax(${t.id}, 'media')"><span class="monday-color-dot" style="background-color: #579bfc;"></span> Media</a></li>
                                    <li><a class="dropdown-item monday-dropdown-item" href="javascript:void(0)" onclick="cambiarPrioridadAjax(${t.id}, 'baja')"><span class="monday-color-dot" style="background-color: #00c875;"></span> Baja</a></li>
                                </ul>
                            </div>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="monday-pill status-pendiente" id="status-pill-${t.id}" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="status-label">Pendiente</span>
                                    <i class="bi bi-chevron-down" style="font-size: 0.65rem; opacity: 0.8;"></i>
                                </button>
                                <ul class="dropdown-menu monday-dropdown-menu">
                                    <li><a class="dropdown-item monday-dropdown-item" href="javascript:void(0)" onclick="cambiarEstadoAjax(${t.id}, 'completada')"><span class="monday-color-dot" style="background-color: #00c875;"></span> Listo / Completada</a></li>
                                    <li><a class="dropdown-item monday-dropdown-item" href="javascript:void(0)" onclick="cambiarEstadoAjax(${t.id}, 'en_proceso')"><span class="monday-color-dot" style="background-color: #fdab3d;"></span> En Proceso</a></li>
                                    <li><a class="dropdown-item monday-dropdown-item" href="javascript:void(0)" onclick="cambiarEstadoAjax(${t.id}, 'pendiente')"><span class="monday-color-dot" style="background-color: #579bfc;"></span> Pendiente</a></li>
                                    <li><a class="dropdown-item monday-dropdown-item" href="javascript:void(0)" onclick="cambiarEstadoAjax(${t.id}, 'cancelada')"><span class="monday-color-dot" style="background-color: #df2f4a;"></span> Detenida / Cancelada</a></li>
                                </ul>
                            </div>
                        </td>
                        <td><span class="task-subtitle-text small">Sin fecha</span></td>
                        <td class="text-end pe-4">
                            <div class="btn-group rounded-pill overflow-hidden border border-secondary shadow-sm">
                                <button type="button" class="btn btn-sm btn-outline-info border-0" onclick="alert('Tarea: ${escapeHtml(t.titulo)}')" title="Ver Detalle">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;

                // Insertar al inicio de la tabla (arriba de las existentes)
                $('#monday-tasks-tbody tr.monday-quick-row').before(newRowHtml);
                recalcularProgresoMonday();
            }
        }).fail(function() {
            btn.prop('disabled', false).html('<i class="bi bi-arrow-return-left me-1"></i>Añadir');
            alert('Error al crear la tarea.');
        });
    }

    // --- RECALCULAR BARRA DE PROGRESO SEGMENTADA ESTILO MONDAY ---
    function recalcularProgresoMonday() {
        const rows = $('#monday-tasks-tbody tr[data-task-id]');
        const total = rows.length;

        if (total === 0) {
            $('#m-prog-completada, #m-prog-en_proceso, #m-prog-pendiente, #m-prog-cancelada').css('width', '0%');
            return;
        }

        let counts = { completada: 0, en_proceso: 0, pendiente: 0, cancelada: 0 };

        rows.each(function() {
            const pill = $(this).find('[id^="status-pill-"]');
            if (pill.hasClass('status-completada')) counts.completada++;
            else if (pill.hasClass('status-en_proceso')) counts.en_proceso++;
            else if (pill.hasClass('status-cancelada')) counts.cancelada++;
            else counts.pendiente++;
        });

        // Actualizar números en leyenda
        $('#count-completada').text(counts.completada);
        $('#count-en_proceso').text(counts.en_proceso);
        $('#count-pendiente').text(counts.pendiente);
        $('#count-cancelada').text(counts.cancelada);

        // Actualizar porcentaje de las barras
        $('#m-prog-completada').css('width', `${(counts.completada / total) * 100}%`);
        $('#m-prog-en_proceso').css('width', `${(counts.en_proceso / total) * 100}%`);
        $('#m-prog-pendiente').css('width', `${(counts.pendiente / total) * 100}%`);
        $('#m-prog-cancelada').css('width', `${(counts.cancelada / total) * 100}%`);
    }

    function escapeHtml(text) {
        return $('<div>').text(text).html();
    }

    $(document).ready(function() {
        recalcularProgresoMonday();

        // Enviar con tecla Enter en el campo rápido
        $('#monday-inline-task-title').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                ejecutarCreacionRapida();
            }
        });
    });
</script>
@endsection
