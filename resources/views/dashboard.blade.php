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
                            <table class="table table-custom table-hover align-middle">
                                <thead>
                                    <tr class="table-header-row">
                                        <th class="ps-4" style="width: 50px;">#</th>
                                        <th>Título / Asignado</th>
                                        <th>Prioridad</th>
                                        <th>Estado</th>
                                        <th>Días Restantes</th>
                                        <th class="text-end pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tareas as $index => $tarea)
                                        <tr>
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
                                                <div class="small task-subtitle-text">
                                                    @if(isset($asignaciones[$tarea->id]))
                                                        <i class="bi bi-person me-1"></i>
                                                        @foreach($asignaciones[$tarea->id] as $asig)
                                                            <span class="fw-bold {{ $asig->user_id == Auth::id() ? 'text-primary' : 'task-title-text' }}">
                                                                {{ $asig->nombre }} {{ $asig->apellidos }}
                                                            </span>{{ !$loop->last ? ', ' : '' }}
                                                        @endforeach
                                                    @else
                                                        <span class="task-subtitle-text">Sin asignar</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $p = $tarea->prioridad;
                                                    $p_class = $p == 'alta' ? 'danger' : ($p == 'media' ? 'info' : 'success');
                                                    $p_icon = $p == 'alta' ? 'exclamation-triangle' : ($p == 'media' ? 'dash-circle' : 'arrow-down-circle');
                                                @endphp
                                                <span class="badge bg-{{ $p_class }} bg-opacity-20 text-{{ $p_class }} border border-{{ $p_class }} border-opacity-25 px-2 py-1">
                                                    <i class="bi bi-{{ $p_icon }} me-1"></i>{{ ucfirst($p) }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $e = $tarea->estado;
                                                    $e_class = $e == 'completada' ? 'success' : ($e == 'en_proceso' ? 'info' : ($e == 'pendiente' ? 'warning' : 'secondary'));
                                                @endphp
                                                <span class="badge bg-{{ $e_class }} rounded-pill px-3">{{ ucfirst(str_replace('_', ' ', $e)) }}</span>
                                            </td>
                                            <td>
                                                @if (!empty($tarea->fecha_vencimiento) && $tarea->fecha_vencimiento !== '0000-00-00')
                                                    @php
                                                        $hoy = \Carbon\Carbon::today();
                                                        $vencimiento = \Carbon\Carbon::parse($tarea->fecha_vencimiento);
                                                        $dias = $hoy->diffInDays($vencimiento, false);
                                                    @endphp

                                                    @if ($tarea->estado == 'completada')
                                                        <span class="task-subtitle-text small"><i class="bi bi-check-all me-1"></i>Finalizada</span>
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
                                                    <span class="task-subtitle-text small">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group rounded-pill overflow-hidden border border-secondary shadow-sm">
                                                    <button type="button" class="btn btn-sm btn-outline-info border-0" onclick="alert('Ver tarea ID: {{ $tarea->id }}')" title="Ver Detalle">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    @if($es_admin)
                                                        <button type="button" class="btn btn-sm btn-outline-primary border-0" onclick="alert('Editar tarea ID: {{ $tarea->id }}')" title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 task-subtitle-text">
                                                <i class="bi bi-emoji-smile fs-1 d-block mb-2 text-muted"></i>
                                                <p class="mb-0">No hay tareas que coincidan con los filtros seleccionados.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $tareas->links() }}
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

    function cambiarEstadoAjax(idTarea, nuevoEstado) {
        $.post("{{ route('tareas.cambiar_estado') }}", {
            _token: "{{ csrf_token() }}",
            id: idTarea,
            estado: nuevoEstado
        }, function(res) {
            if (res.success) {
                location.reload();
            }
        });
    }
</script>
@endsection
