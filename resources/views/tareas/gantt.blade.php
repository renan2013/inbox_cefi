@extends('layouts.app')

@section('title', 'Diagrama de Gantt Colaborativo - Inbox 2.0')

@section('styles')
<style>
    .gantt-wrapper {
        background: var(--card-dark, #1e293b);
        border: 1px solid var(--border-dark, rgba(255,255,255,0.08));
        border-radius: 16px;
        overflow: hidden;
    }
    .gantt-row {
        transition: background 0.15s ease;
    }
    .gantt-row:hover {
        background: rgba(59, 130, 246, 0.05);
    }
    .gantt-bar-track {
        position: relative;
        height: 28px;
        background: rgba(148, 163, 184, 0.12);
        border-radius: 999px;
        overflow: hidden;
    }
    .gantt-bar-fill {
        height: 100%;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 12px;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(0,0,0,0.18);
    }
    .bar-pendiente { background: linear-gradient(90deg, #f59e0b, #d97706); }
    .bar-en_proceso { background: linear-gradient(90deg, #0ea5e9, #2563eb); }
    .bar-completada { background: linear-gradient(90deg, #10b981, #059669); }
    .bar-vencida { background: linear-gradient(90deg, #ef4444, #b91c1c); }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-info bg-opacity-10 text-info px-3 py-1 rounded-pill fw-bold">
                    <i class="bi bi-bar-chart-steps me-1"></i>Cronograma BPM 2.0
                </span>
                <span class="text-muted small">{{ $tareas->count() }} tareas en cronograma</span>
            </div>
            <h2 class="fw-bold mb-0"><i class="bi bi-bar-chart-steps text-info me-2"></i>Diagrama de Gantt & Cronograma de Tareas</h2>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="btn-group rounded-pill p-1 border border-secondary bg-dark bg-opacity-25 me-2" role="group" aria-label="Vista de Tareas">
                <a href="{{ route('dashboard', ['view' => 'table']) }}" class="btn btn-sm text-muted rounded-pill px-3">
                    <i class="bi bi-table me-1"></i> Tabla
                </a>
                <a href="{{ route('dashboard', ['view' => 'kanban']) }}" class="btn btn-sm text-muted rounded-pill px-3">
                    <i class="bi bi-kanban me-1"></i> Tablero
                </a>
                <a href="{{ route('tareas.gantt') }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                    <i class="bi bi-bar-chart-steps me-1"></i> Gantt
                </a>
            </div>
            <a href="{{ route('tareas.plantillas.gestionar') }}" class="btn btn-outline-info rounded-pill px-3">
                <i class="bi bi-card-checklist me-1"></i>Plantillas
            </a>
            <a href="{{ route('tareas.etiquetas.index') }}" class="btn btn-outline-warning rounded-pill px-3">
                <i class="bi bi-bookmarks me-1"></i>Etiquetas
            </a>
            <a href="{{ route('tareas.create') }}" class="btn btn-success fw-bold rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-lg me-1"></i>Nueva Tarea
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--card-dark);">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('tareas.gantt') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Año</label>
                    <select name="anio" class="form-select form-select-sm">
                        <option value="todos" {{ $filtroAnio === 'todos' ? 'selected' : '' }}>Todos los años</option>
                        @for($y = date('Y') + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ (string)$filtroAnio === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Estado</label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" {{ $filtroEstado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="en_proceso" {{ $filtroEstado === 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                        <option value="completada" {{ $filtroEstado === 'completada' ? 'selected' : '' }}>Completada</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Prioridad</label>
                    <select name="prioridad" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="alta" {{ $filtroPrioridad === 'alta' ? 'selected' : '' }}>Alta</option>
                        <option value="media" {{ $filtroPrioridad === 'media' ? 'selected' : '' }}>Media</option>
                        <option value="baja" {{ $filtroPrioridad === 'baja' ? 'selected' : '' }}>Baja</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Responsable Asignado</label>
                    <select name="usuario" class="form-select form-select-sm">
                        <option value="">Todos los usuarios</option>
                        @foreach($usuarios as $u)
                            <option value="{{ $u->id }}" {{ (string)$filtroUsuario === (string)$u->id ? 'selected' : '' }}>
                                {{ $u->nombre }} {{ $u->apellidos }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Etiqueta</label>
                    <select name="etiqueta" class="form-select form-select-sm">
                        <option value="">Todas las etiquetas</option>
                        @foreach($etiquetas as $et)
                            <option value="{{ $et->id }}" {{ (string)$filtroEtiqueta === (string)$et->id ? 'selected' : '' }}>
                                {{ $et->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">
                        <i class="bi bi-funnel-fill"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cronograma Visual Gantt -->
    <div class="gantt-wrapper shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th style="width: 28%;">Tarea / Responsables</th>
                        <th style="width: 110px;">Prioridad</th>
                        <th style="width: 130px;">Estado</th>
                        <th style="width: 170px;">Fechas (Inicio → Fin)</th>
                        <th>Línea de Tiempo & Avance (Gantt)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Calcular rango global de fechas para posicionar barras proporcionalmente
                        $minTs = time();
                        $maxTs = strtotime('+30 days');
                        if ($tareas->count() > 0) {
                            $starts = $tareas->pluck('fecha_inicio_gantt')->map(fn($d) => strtotime($d))->toArray();
                            $ends   = $tareas->pluck('fecha_fin_gantt')->map(fn($d) => strtotime($d))->toArray();
                            $minTs  = min($starts);
                            $maxTs  = max($ends);
                            if ($maxTs <= $minTs) {
                                $maxTs = $minTs + (86400 * 14);
                            }
                        }
                        $totalSpan = max(86400, $maxTs - $minTs);
                    @endphp

                    @forelse($tareas as $t)
                        @php
                            $tStart = strtotime($t->fecha_inicio_gantt);
                            $tEnd   = strtotime($t->fecha_fin_gantt);
                            $leftPct = max(0, min(85, round((($tStart - $minTs) / $totalSpan) * 100, 1)));
                            $widthPct = max(14, min(100 - $leftPct, round((max(86400, $tEnd - $tStart) / $totalSpan) * 100, 1)));
                            $vencida = ($t->estado !== 'completada' && !empty($t->fecha_vencimiento) && strtotime($t->fecha_vencimiento) < strtotime(date('Y-m-d')));
                            $barClass = $vencida ? 'bar-vencida' : ('bar-' . ($t->estado ?: 'pendiente'));
                            $diasDiff = !empty($t->fecha_vencimiento)
                                ? (int)floor((strtotime($t->fecha_vencimiento) - strtotime(date('Y-m-d'))) / 86400)
                                : null;
                        @endphp
                        <tr class="gantt-row">
                            <td class="fw-bold text-muted">#{{ $t->id }}</td>
                            <td>
                                <div class="fw-bold">{{ $t->titulo }}</div>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @forelse($t->asignados as $asig)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                            <i class="bi bi-person-fill me-1"></i>{{ $asig->nombre }} {{ $asig->apellidos }}
                                        </span>
                                    @empty
                                        <span class="text-muted small"><i class="bi bi-person-dash me-1"></i>Sin asignar</span>
                                    @endforelse
                                    @foreach($t->etiquetas_lista as $tag)
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <i class="bi bi-tag-fill me-1"></i>{{ $tag->nombre }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @php
                                    $pBadge = $t->prioridad === 'alta' ? 'danger' : ($t->prioridad === 'media' ? 'info' : 'success');
                                @endphp
                                <span class="badge bg-{{ $pBadge }} bg-opacity-10 text-{{ $pBadge }} border border-{{ $pBadge }} border-opacity-25 rounded-pill px-3">
                                    {{ ucfirst($t->prioridad ?? 'media') }}
                                </span>
                            </td>
                            <td>
                                <select class="form-select form-select-sm fw-bold" onchange="cambiarEstadoGantt({{ $t->id }}, this.value)">
                                    <option value="pendiente" {{ $t->estado === 'pendiente' ? 'selected' : '' }}>⏳ Pendiente</option>
                                    <option value="en_proceso" {{ $t->estado === 'en_proceso' ? 'selected' : '' }}>🚀 En Proceso</option>
                                    <option value="completada" {{ $t->estado === 'completada' ? 'selected' : '' }}>✅ Completada</option>
                                </select>
                            </td>
                            <td class="small">
                                <div><i class="bi bi-play-circle text-success me-1"></i>{{ date('d/m/Y', strtotime($t->fecha_inicio_gantt)) }}</div>
                                <div><i class="bi bi-flag-fill {{ $vencida ? 'text-danger' : 'text-primary' }} me-1"></i>{{ date('d/m/Y', strtotime($t->fecha_fin_gantt)) }}</div>
                            </td>
                            <td style="min-width: 320px;">
                                <div class="gantt-bar-track">
                                    <div class="gantt-bar-fill {{ $barClass }}" style="margin-left: {{ $leftPct }}%; width: {{ $widthPct }}%;">
                                        <span>{{ $t->progreso }}%</span>
                                        <span class="ms-2">
                                            @if($t->estado === 'completada')
                                                Completada
                                            @elseif($diasDiff !== null)
                                                {{ $diasDiff < 0 ? 'Atrasada (' . abs($diasDiff) . 'd)' : ($diasDiff === 0 ? 'Vence hoy' : $diasDiff . 'd restantes') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar2-x fs-1 d-block mb-2"></i>
                                No hay tareas registradas para los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function cambiarEstadoGantt(idTarea, nuevoEstado) {
    fetch("{{ route('tareas.cambiar_estado') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ id_tarea: idTarea, estado: nuevoEstado })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Estado actualizado en el cronograma',
                showConfirmButton: false,
                timer: 1400
            }).then(() => window.location.reload());
        }
    });
}
</script>
@endsection
