@extends('layouts.app')

@section('title', 'Resultados de Encuesta Docente')

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #1a2e1a 0%, #2d5a1b 60%, #5fb230 100%);
        border-radius: 1.25rem;
        padding: 2rem;
        color: #fff;
        margin-bottom: 1.75rem;
    }
    .kpi-card {
        background: var(--card-dark);
        border-radius: 16px;
        border: 1px solid var(--border-dark);
        padding: 1.5rem 1rem;
        text-align: center;
        transition: transform .2s;
    }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.2); }
    .kpi-val { font-size: 2.4rem; font-weight: 800; line-height: 1; }
    .kpi-lbl { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; font-weight: 600; margin-top: 4px; }

    .gauge-ring {
        width: 85px; height: 85px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto .5rem;
        font-size: 1.4rem; font-weight: 800;
        border: 8px solid;
    }

    .resultado-card {
        background: var(--card-dark);
        border-radius: 16px;
        border: 1px solid var(--border-dark);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .resultado-header { padding: 1rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }

    .item-row { padding: .75rem 1.5rem; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .item-row:last-child { border-bottom: none; }
    .item-num { min-width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; color: #fff; flex-shrink: 0; }
    .item-txt { flex: 1; font-size: .88rem; color: #e2e8f0; font-weight: 500; }
    .item-bar-wrap { width: 160px; }
    .item-bar { height: 10px; border-radius: 50px; background: rgba(255,255,255,0.1); overflow: hidden; }
    .item-bar-fill { height: 100%; border-radius: 50px; transition: width .8s; }
    .item-prom { min-width: 44px; text-align: right; font-weight: 800; font-size: .92rem; }

    .comment-card {
        background: rgba(15, 23, 42, 0.4);
        border: 1px solid var(--border-dark);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: .75rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1200px; margin: 0 auto;">

    <!-- Header -->
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-bar-chart-fill me-2" style="color: #84cc16;"></i>Resultados de Encuestas Docentes</h1>
            <p class="mb-0 text-white-50 small">Métricas y retroalimentación anónima de los cursos evaluados.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if (Auth::user()->id_rol == 1)
                <a href="{{ route('encuestas.preguntas') }}" class="btn btn-outline-light rounded-pill px-4">
                    <i class="bi bi-clipboard2-check me-1"></i> Banco de Preguntas
                </a>
            @endif
        </div>
    </div>

    <!-- Selector de Cursos Evaluados -->
    <div class="card bg-dark border border-secondary border-opacity-25 rounded-4 p-3 mb-4 text-white">
        <form method="GET" action="{{ route('encuestas.resultados') }}" class="row g-2 align-items-center">
            <div class="col-md-9">
                <label class="form-label x-small text-white-50 fw-bold mb-1">Seleccionar Curso con Evaluaciones:</label>
                <select name="id_curso" class="form-select bg-dark text-white border-secondary" onchange="this.form.submit()">
                    @forelse ($cursos_con_encuesta as $c)
                        <option value="{{ $c->id_curso_activo }}" {{ $c->id_curso_activo == $id_curso ? 'selected' : '' }}>
                            [{{ $c->periodo }}] {{ $c->materia }} — Prof: {{ trim(($c->nombre_prof ?? '') . ' ' . ($c->apellidos_prof ?? '')) }} ({{ $c->total_respuestas }} respuestas)
                        </option>
                    @empty
                        <option value="">No hay evaluaciones registradas aún</option>
                    @endforelse
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                @if ($id_curso > 0)
                    <a href="{{ route('encuesta.responder', $id_curso) }}" target="_blank" class="btn btn-outline-success w-100 rounded-pill py-2">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Abrir Encuesta
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if ($curso_detalle && $total_sesiones > 0)
        <!-- Dashboard del Curso Seleccionado -->
        
        <!-- Info del Curso -->
        <div class="card bg-dark border border-secondary border-opacity-25 rounded-4 mb-4 p-4 text-white">
            <div class="row g-2 align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-primary bg-opacity-25 text-primary rounded-pill px-3 py-1 mb-1">
                        {{ $curso_detalle->planEstudio->programa->nombre_programa ?? '' }}
                    </span>
                    <h2 class="h4 fw-bold mb-1">{{ $curso_detalle->planEstudio->materia ?? '' }}</h2>
                    <p class="text-white-50 small mb-0">
                        <i class="bi bi-person-circle me-1"></i>
                        {{ trim(($curso_detalle->profesor->titulo_academico ?? '') . ' ' . ($curso_detalle->profesor->nombre ?? '') . ' ' . ($curso_detalle->profesor->apellidos ?? '')) }}
                        &nbsp;·&nbsp;<i class="bi bi-calendar3 me-1"></i>{{ $curso_detalle->periodo }}
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    @if (Auth::user()->id_rol == 1)
                        <button class="btn btn-success btn-sm rounded-pill px-4" onclick="publicarEnMoodle({{ $curso_detalle->id_curso_activo }})">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Publicar en Moodle
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- KPIs Promedios -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="kpi-card text-white">
                    <div class="gauge-ring" style="border-color: {{ $promedio_global >= 80 ? '#22c55e' : ($promedio_global >= 60 ? '#84cc16' : '#f97316') }}; color: {{ $promedio_global >= 80 ? '#22c55e' : ($promedio_global >= 60 ? '#84cc16' : '#f97316') }};">
                        {{ $promedio_global }}%
                    </div>
                    <div class="kpi-lbl">Promedio Global</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="kpi-card text-white">
                    <div class="kpi-val text-success" style="margin-top: 15px;">{{ $promedio_doc }}%</div>
                    <div class="kpi-lbl mt-2">Desempeño Docente</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="kpi-card text-white">
                    <div class="kpi-val text-primary" style="margin-top: 15px;">{{ $promedio_inst }}%</div>
                    <div class="kpi-lbl mt-2">Institución / Plataforma</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="kpi-card text-white">
                    <div class="kpi-val text-info" style="margin-top: 15px;">{{ $total_sesiones }}</div>
                    <div class="kpi-lbl mt-2">Respuestas Totales</div>
                </div>
            </div>
        </div>

        <!-- Desglose por Sección -->
        <div class="row g-4">
            <!-- Sección Docente -->
            @if (!empty($resultados_doc))
                <div class="col-lg-6">
                    <div class="resultado-card text-white">
                        <div class="resultado-header border-bottom border-secondary border-opacity-25" style="background: rgba(22,163,74,0.1);">
                            <div>
                                <h6 class="fw-bold mb-0 text-success"><i class="bi bi-person-check-fill me-1"></i> Desempeño Docente</h6>
                                <small class="text-white-50">{{ count($resultados_doc) }} preguntas evaluadas</small>
                            </div>
                            <span class="badge bg-success rounded-pill fs-6">{{ $promedio_doc }}%</span>
                        </div>
                        <div>
                            @foreach ($resultados_doc as $idx => $r)
                                <div class="item-row">
                                    <div class="item-num bg-success">{{ $idx + 1 }}</div>
                                    <div class="item-txt">{{ $r['texto'] }}</div>
                                    <div class="item-bar-wrap">
                                        <div class="item-bar">
                                            <div class="item-bar-fill" style="width: {{ $r['pct'] }}%; background: {{ $r['pct'] >= 80 ? '#22c55e' : ($r['pct'] >= 60 ? '#84cc16' : '#f97316') }};"></div>
                                        </div>
                                    </div>
                                    <div class="item-prom" style="color: {{ $r['pct'] >= 80 ? '#22c55e' : ($r['pct'] >= 60 ? '#84cc16' : '#f97316') }};">
                                        {{ $r['promedio'] }}%
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Sección Institución -->
            @if (!empty($resultados_inst))
                <div class="col-lg-6">
                    <div class="resultado-card text-white">
                        <div class="resultado-header border-bottom border-secondary border-opacity-25" style="background: rgba(37,99,235,0.1);">
                            <div>
                                <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-building me-1"></i> Aspectos Institucionales</h6>
                                <small class="text-white-50">{{ count($resultados_inst) }} preguntas evaluadas</small>
                            </div>
                            <span class="badge bg-primary rounded-pill fs-6">{{ $promedio_inst }}%</span>
                        </div>
                        <div>
                            @foreach ($resultados_inst as $idx => $r)
                                <div class="item-row">
                                    <div class="item-num bg-primary">{{ $idx + 1 }}</div>
                                    <div class="item-txt">{{ $r['texto'] }}</div>
                                    <div class="item-bar-wrap">
                                        <div class="item-bar">
                                            <div class="item-bar-fill" style="width: {{ $r['pct'] }}%; background: {{ $r['pct'] >= 80 ? '#3b82f6' : ($r['pct'] >= 60 ? '#60a5fa' : '#f97316') }};"></div>
                                        </div>
                                    </div>
                                    <div class="item-prom" style="color: {{ $r['pct'] >= 80 ? '#3b82f6' : ($r['pct'] >= 60 ? '#60a5fa' : '#f97316') }};">
                                        {{ $r['promedio'] }}%
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Comentarios de Estudiantes -->
        @if ($comentarios->isNotEmpty())
            <div class="card bg-dark border border-secondary border-opacity-25 rounded-4 p-4 text-white mt-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-chat-left-heart text-warning me-2"></i> Comentarios y Sugerencias Anónimas</h5>
                <div class="row g-3">
                    @foreach ($comentarios as $c)
                        <div class="col-md-6">
                            <div class="comment-card">
                                <p class="mb-2 text-white" style="font-style: italic;">"{{ $c->comentarios }}"</p>
                                <small class="text-white-50"><i class="bi bi-clock me-1"></i> Enviado el {{ $c->fecha_envio ? $c->fecha_envio->format('d/m/Y H:i') : 'Reciente' }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @else
        <div class="card bg-dark border border-secondary border-opacity-25 rounded-4 text-center py-5 text-muted">
            <i class="bi bi-clipboard-data fs-1 d-block mb-3 text-warning"></i>
            <h4 class="text-white fw-bold mb-1">Sin respuestas registradas</h4>
            <p class="text-white-50">El curso seleccionado aún no cuenta con respuestas de encuestas de estudiantes.</p>
        </div>
    @endif

</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function publicarEnMoodle(id_curso) {
        Swal.fire({
            title: '¿Publicar Encuesta en Moodle?',
            text: 'Se creará el enlace de la encuesta como actividad en la sección general de Moodle.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5fb230',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, publicar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/encuestas/${id_curso}/publicar-moodle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: '¡Publicada!', text: data.message });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                    }
                });
            }
        });
    }
</script>
@endsection
