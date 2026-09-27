@extends('layouts.app')

@section('title', 'Inbox - Gestionar Curso')

@section('styles')
    <style>
        .header-panel-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .btn-action-vertical {
            min-width: 80px;
            height: 90px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            border-radius: 1rem !important;
            border: 1px solid var(--border-dark) !important;
            background-color: var(--card-dark) !important;
            color: var(--text-light) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .btn-action-vertical:hover {
            transform: translateY(-3px);
            border-color: var(--primary) !important;
            box-shadow: 0 8px 18px rgba(95, 178, 48, 0.2);
        }
        .btn-action-vertical span {
            color: var(--text-light) !important;
        }
        [data-theme="light"] .btn-action-vertical {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }
        [data-theme="light"] .btn-action-vertical span {
            color: #0f172a !important;
        }

        .btn-silabo-destacado {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: white !important;
            font-weight: 700;
            border-radius: 0.75rem;
            letter-spacing: 0.5px;
            padding: 0.8rem 1.5rem;
        }

        /* Accordion custom styling */
        .accordion-custom .accordion-item {
            background-color: var(--card-dark) !important;
            border: 1px solid var(--border-dark) !important;
            border-radius: 1rem;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .accordion-custom .accordion-button {
            background-color: var(--card-dark) !important;
            color: #f8fafc !important;
            font-weight: 600;
            padding: 1.25rem 1.5rem;
            box-shadow: none !important;
        }

        .accordion-custom .accordion-button::after {
            filter: invert(1);
        }

        .accordion-custom .accordion-button:not(.collapsed) {
            border-bottom: 1px solid var(--border-dark);
            background-color: rgba(255, 255, 255, 0.02) !important;
        }

        .table-grades {
            background-color: transparent !important;
            --bs-table-bg: transparent !important;
            --bs-table-hover-bg: rgba(255, 255, 255, 0.05) !important;
            --bs-table-color: #f8fafc !important;
        }
        .table-grades th {
            background-color: rgba(255, 255, 255, 0.02) !important;
            color: #94a3b8 !important;
            border-bottom: 1px solid var(--border-dark) !important;
            padding: 1rem;
        }
        .table-grades tr, .table-grades td {
            background-color: transparent !important;
            color: #cbd5e1 !important;
            border-bottom: 1px solid var(--border-dark) !important;
            padding: 0.8rem 1rem;
        }

        .input-nota-modern {
            height: 38px;
            font-weight: 600;
            text-align: center;
            border-radius: 0.5rem;
            background-color: rgba(15, 23, 42, 0.5) !important;
            border: 1px solid var(--border-dark) !important;
            color: #fff !important;
            width: 80px;
            margin: 0 auto;
        }
        .input-nota-modern:focus {
            background-color: #fff !important;
            color: #000 !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 0.25rem rgba(95, 178, 48, 0.2) !important;
        }

        /* Floating yellow action button */
        .academic-float-btn-container {
            position: fixed;
            bottom: 95px;
            right: 25px;
            z-index: 1040;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .academic-float-btn-container:hover {
            transform: scale(1.05);
        }
        .academic-float-btn-container .btn {
            border: none;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.25) !important;
            background-color: #ffc107 !important;
            color: #212529 !important;
        }

        @keyframes alertPulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
        .btn-action-vertical.alert-pulse {
            animation: alertPulse 1.8s infinite;
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        .revision-item {
            transition: all 0.3s ease;
            border-left: 4px solid #ffc107;
            background-color: rgba(255,255,255,0.02);
        }
        .revision-item.resuelto {
            border-left: 4px solid #198754;
            background-color: rgba(255,255,255,0.01);
            opacity: 0.85;
        }

        .form-control-custom, .form-select-custom {
            background-color: rgba(15, 23, 42, 0.5);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.6rem 0.9rem;
            transition: all 0.3s;
        }
        .form-control-custom:focus, .form-select-custom:focus {
            background-color: rgba(15, 23, 42, 0.7);
            border-color: var(--primary);
            color: #f8fafc;
            outline: none;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-5 px-md-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('mis_cursos.index') }}" class="text-decoration-none text-white-50">Mis Cursos</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">{{ $curso->planEstudio->materia }}</li>
            </ol>
        </nav>

        <!-- HEADER PANEL CARD -->
        <div class="card border-0 header-panel-card text-white overflow-hidden rounded-4">
            <div class="card-body p-0">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary px-3 py-2 rounded-pill me-2">{{ $curso->planEstudio->codigo }}</span>
                            <span class="badge bg-white text-primary border border-primary px-3 py-2 rounded-pill">{{ $curso->periodo }}</span>
                        </div>
                        <h1 class="h2 fw-bold text-white mb-1">{{ $curso->planEstudio->materia }}</h1>
                        <p class="lead text-white-50 mb-3">
                            <i class="bi bi-person-circle me-1 text-primary"></i> 
                            Docente: {{ $curso->profesor->nombre ?? 'Sin docente' }} {{ $curso->profesor->apellidos ?? '' }}
                        </p>
                        
                        <div class="d-flex flex-wrap gap-3 mt-2 small text-white-50 align-items-center">
                            <span><i class="bi bi-people-fill me-1 text-primary"></i> <strong>{{ count($alumnos) }}</strong> Estudiantes</span>
                            @if ($es_admin)
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-xs btn-outline-success py-1 px-2 rounded-pill" onclick="sincronizarAlumnos()" title="Sincronizar Estudiantes desde Moodle">
                                        <i class="bi bi-arrow-repeat me-1"></i> Sincronizar
                                    </button>
                                </div>
                            @endif
                            
                            <!-- Calculate Approved / Failed metrics dynamically -->
                            @php
                                $aprobados = 0; $reprobados = 0;
                                foreach($alumnos as $a) {
                                    $n_f = 0; $completa = true;
                                    if(count($rubros) > 0) {
                                        foreach($rubros as $r) {
                                            if(!isset($notas[$a->id_matricula][$r->id_rubro]) || $notas[$a->id_matricula][$r->id_rubro] === '') {
                                                $completa = false;
                                            } else {
                                                $n_f += floatval($notas[$a->id_matricula][$r->id_rubro]);
                                            }
                                        }
                                    } else { $completa = false; }
                                    
                                    if($completa) {
                                        if($n_f >= 70) $aprobados++; else $reprobados++;
                                    }
                                }
                            @endphp

                            <span><i class="bi bi-check-circle-fill me-1 text-success ms-md-2"></i> <strong>{{ $aprobados }}</strong> Aprobados</span>
                            <span><i class="bi bi-x-circle-fill me-1 text-danger"></i> <strong>{{ $reprobados }}</strong> Reprobados</span>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                        <div class="d-flex flex-column gap-2 align-items-lg-end">
                            <a href="{{ route('mis_cursos.gestionar_silabo', $curso->id_curso_activo) }}" class="btn btn-silabo-destacado w-100 mb-2">
                                <i class="bi bi-journal-text me-2"></i> SÍLABO OFICIAL DEL CURSO
                            </a>
                            
                            <!-- Status action box list -->
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end w-100 mt-2">
                                <button type="button" class="btn btn-action-vertical" onclick="scrollToAndOpen('collapseNotas')" title="Gestionar y Enviar Calificaciones">
                                    <i class="bi bi-send-check text-primary fs-4 d-block mb-1"></i>
                                    <span class="small fw-bold">Notas</span>
                                </button>
                                
                                <a href="{{ route('mis_cursos.asistencia', $curso->id_curso_activo) }}" class="btn btn-action-vertical" title="Lista de Asistencia de Estudiantes">
                                    <i class="bi bi-calendar-check text-success fs-4 d-block mb-1"></i>
                                    <span class="small fw-bold">Asistencia</span>
                                </a>

                                <button type="button" class="btn btn-action-vertical" onclick="abrirPantallaBienvenida()" title="Pantalla de Bienvenida (Cortina de Clase)">
                                    <i class="bi bi-display text-info fs-4 d-block mb-1"></i>
                                    <span class="small fw-bold">Cortina</span>
                                </button>

                                @if (!empty($curso->enlace_zoom))
                                    <a href="{{ $curso->enlace_zoom }}" target="_blank" class="btn btn-action-vertical" title="Unirse a la Sesión de Zoom del Curso">
                                        <i class="bi bi-camera-video-fill text-primary fs-4 d-block mb-1"></i>
                                        <span class="small fw-bold">Zoom</span>
                                    </a>
                                @else
                                    <button type="button" class="btn btn-action-vertical opacity-50" disabled title="No hay enlace de Zoom configurado para este curso">
                                        <i class="bi bi-camera-video text-muted fs-4 d-block mb-1"></i>
                                        <span class="small fw-bold text-muted">Zoom</span>
                                    </button>
                                @endif

                                <a href="{{ route('mis_cursos.recursos_drive', $curso->id_curso_activo) }}" class="btn btn-action-vertical" title="Gestionar Recursos">
                                    <i class="bi bi-google text-primary fs-4 d-block mb-1"></i>
                                    <span class="small fw-bold">Recursos</span>
                                </a>

                                @if ($es_admin)
                                    <button type="button" class="btn btn-action-vertical" onclick="sincronizarMoodle()" title="Sincronizar Moodle">
                                        <i class="bi bi-cloud-arrow-up text-success fs-4 d-block mb-1"></i>
                                        <span class="small fw-bold">Moodle</span>
                                    </button>
                                @endif

                                <a href="{{ route('mis_cursos.crear_portada', $curso->id_curso_activo) }}" class="btn btn-action-vertical" title="Creador de Portada">
                                    <i class="bi bi-image text-warning fs-4 d-block mb-1"></i>
                                    <span class="small fw-bold">Portada</span>
                                </a>

                                <a href="{{ route('mis_cursos.notificaciones_clase', $curso->id_curso_activo) }}" class="btn btn-action-vertical" title="Notificaciones de Clase">
                                    <i class="bi bi-megaphone text-danger fs-4 d-block mb-1"></i>
                                    <span class="small fw-bold">Notif.</span>
                                </a>
                                
                                @if ($revisiones_pendientes > 0)
                                    <button type="button" class="btn btn-action-vertical alert-pulse text-white" onclick="abrirModalRevisiones()" title="Hay observaciones académicas pendientes">
                                        <i class="bi bi-exclamation-triangle-fill text-white fs-4 d-block mb-1"></i>
                                        <span class="small fw-bold">Observaciones ({{ $revisiones_pendientes }})</span>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-action-vertical" onclick="abrirModalRevisiones()" title="Revisiones y Sugerencias Académicas">
                                        <i class="bi bi-pencil-square text-warning fs-4 d-block mb-1"></i>
                                        <span class="small fw-bold">Revisión Acad.</span>
                                    </button>
                                @endif

                                <button type="button" id="btnActionEncuesta" class="btn btn-action-vertical" onclick="scrollToAndOpen('collapseEncuesta')" title="Encuesta de Evaluación Docente">
                                    @if (!empty($curso->check_encuesta))
                                        <i class="bi bi-clipboard2-check-fill fs-4 d-block mb-1" style="color: #5fb230;"></i>
                                    @else
                                        <i class="bi bi-clipboard2-check text-muted fs-4 d-block mb-1"></i>
                                    @endif
                                    <span class="small fw-bold">Encuesta</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2) !important;">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <!-- ACCORDIONS -->
        <div class="accordion accordion-custom mt-4" id="accordionGestionCurso">
            
            <!-- Accordion 1: Calificaciones -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotas">
                        <i class="bi bi-table text-primary me-2"></i> Gestión de Calificaciones y Envío a Estudiantes
                    </button>
                </h2>
                <div id="collapseNotas" class="accordion-collapse collapse" data-bs-parent="#accordionGestionCurso">
                    <div class="accordion-body p-0">
                        <div class="px-4 py-3 border-bottom border-secondary d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span class="text-white-50 small">
                                <i class="bi bi-info-circle me-1"></i> Ingrese las notas por rubro. El sistema calcula el total y habilita el envío automático.
                            </span>
                            <div class="d-flex gap-2">
                                @module('moodle_sync')
                                <button type="button" id="btnSincronizarNotasMoodle" class="btn btn-success btn-sm text-white rounded-pill px-3 shadow-sm fw-bold" onclick="sincronizarNotasMoodle({{ $curso->id_curso_activo }})" title="Importar calificaciones directamente desde Moodle Virtual">
                                    <i class="bi bi-mortarboard-fill me-1"></i> Sincronizar Moodle
                                </button>
                                @endmodule
                                <button type="submit" id="btnGuardarTodasNotas" form="formNotas" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" style="background-color: var(--primary); border: none;">
                                    <i class="bi bi-save2-fill me-1"></i> Guardar Cambios
                                </button>
                                <button type="button" id="btnNotificarTodos" class="btn btn-warning btn-sm text-dark rounded-pill px-3 shadow-sm fw-bold" onclick="notificarNotasTodos()">
                                    <i class="bi bi-send-check-fill me-1"></i> Notificar a Todos
                                </button>
                                <button type="button" id="btnActaCalificaciones" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm fw-bold">
                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Generar Acta
                                </button>
                            </div>
                        </div>
                        
                        <form action="{{ route('mis_cursos.guardar_notas', $curso->id_curso_activo) }}" method="POST" id="formNotas">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-grades table-hover align-middle mb-0 text-center">
                                    <thead>
                                        <tr class="text-white-50 small font-weight-bold">
                                            <th style="width: 40px;">#</th>
                                            <th class="text-start ps-4">Estudiante</th>
                                            @foreach ($rubros as $r)
                                                <th style="width: 100px;">
                                                    <div class="text-truncate" title="{{ $r->rubro }}">{{ $r->rubro }}</div>
                                                    <span style="color: #60a5fa !important;" class="fw-bold">{{ $r->porcentaje }}%</span>
                                                </th>
                                            @endforeach
                                            <th class="bg-primary bg-opacity-10 text-white" style="width: 90px;">Total</th>
                                            <th style="width: 120px;">Condición</th>
                                            <th style="width: 60px;">Envío</th>
                                            <th style="width: 110px;">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($alumnos as $idx => $alum)
                                            @php
                                                $nota_final_calc = 0;
                                                $todas_notas_completas = true;
                                                foreach ($rubros as $r) {
                                                    $val = $notas[$alum->id_matricula][$r->id_rubro] ?? '';
                                                    if ($val === '') {
                                                        $todas_notas_completas = false;
                                                    } else {
                                                        $nota_final_calc += floatval($val);
                                                    }
                                                }
                                                $disabled_attr = !$todas_notas_completas ? 'disabled' : '';
                                            @endphp
                                            <tr>
                                                <td class="text-white-50 small">{{ $idx + 1 }}</td>
                                                <td class="text-start ps-4">
                                                    <div class="fw-bold text-white">{{ $alum->nombre }} {{ $alum->apellidos }}</div>
                                                    <div class="small text-white-50"><i class="bi bi-envelope me-1"></i> {{ $alum->email }}</div>
                                                </td>
                                                @foreach ($rubros as $r)
                                                    @php $val = $notas[$alum->id_matricula][$r->id_rubro] ?? ''; @endphp
                                                    <td class="p-1">
                                                        <input type="number" step="0.5" min="0" max="{{ $r->porcentaje }}" 
                                                            name="notas_parciales[{{ $alum->id_matricula }}][{{ $r->id_rubro }}]" 
                                                            class="form-control form-control-sm input-nota-modern" 
                                                            data-porcentaje="{{ $r->porcentaje }}"
                                                            data-matricula="{{ $alum->id_matricula }}"
                                                            value="{{ $val }}">
                                                    </td>
                                                @endforeach
                                                <td class="bg-primary bg-opacity-10 fw-bold fs-5">
                                                    <span id="total_{{ $alum->id_matricula }}" class="{{ !$todas_notas_completas ? 'text-white-50' : (($nota_final_calc >= 70) ? 'text-success' : 'text-danger') }}">
                                                        {{ number_format($nota_final_calc, 2) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span id="condicion_{{ $alum->id_matricula }}" class="badge rounded-pill {{ !$todas_notas_completas ? 'bg-secondary bg-opacity-10 text-white-50 border border-secondary' : (($nota_final_calc >= 70) ? 'bg-success' : 'bg-danger') }} px-3">
                                                        {{ !$todas_notas_completas ? 'Sin calificar' : (($nota_final_calc >= 70) ? 'Aprobado' : 'Reprobado') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($alum->email_enviado)
                                                        <span class="text-success" title="Enviado: {{ $alum->fecha_envio_email }}"><i class="bi bi-check-all fs-4"></i></span>
                                                    @else
                                                        <span class="text-white-50" title="Pendiente"><i class="bi bi-clock fs-5"></i></span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-notificar-individual" 
                                                            data-id-matricula="{{ $alum->id_matricula }}"
                                                            data-nombre="{{ $alum->nombre }} {{ $alum->apellidos }}"
                                                            data-email="{{ $alum->email }}"
                                                            data-enviado="{{ $alum->email_enviado ? 1 : 0 }}"
                                                            onclick="enviarNotaIndividual({{ $alum->id_matricula }}, '{{ addslashes($alum->nombre . ' ' . $alum->apellidos) }}', '{{ $alum->email }}')" 
                                                            {{ $disabled_attr }}>
                                                        <i class="bi bi-send-fill small"></i> Notificar
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Accordion 2: Recursos y Materiales -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRecursos">
                        <i class="bi bi-folder2-open text-primary me-2"></i> Recursos y Materiales del Curso <span class="badge bg-light text-dark border ms-2">{{ count($recursos_drive) }}</span>
                    </button>
                </h2>
                <div id="collapseRecursos" class="accordion-collapse collapse" data-bs-parent="#accordionGestionCurso">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-grades table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-white-50 small">
                                        <th class="ps-4">Tipo</th>
                                        <th>Nombre del Recurso</th>
                                        <th>Fecha</th>
                                        <th class="text-end pe-4">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="text-white">
                                    @if (count($recursos_drive) > 0)
                                        @foreach ($recursos_drive as $rd)
                                            <tr>
                                                <td class="ps-4"><i class="bi bi-google text-primary fs-4"></i></td>
                                                <td>
                                                    <div class="fw-bold">{{ $rd->nombre_archivo }}</div>
                                                    <small class="text-white-50">Recurso externo (Google Drive)</small>
                                                </td>
                                                <td class="text-white-50 small">{{ \Carbon\Carbon::parse($rd->fecha_subida)->format('d/m/Y g:i a') }}</td>
                                                <td class="text-end pe-4">
                                                    <a href="{{ $rd->link_publico }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                        <i class="bi bi-download me-1"></i> Bajar
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-white-50">No se han cargado recursos para este curso todavía.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        @if ($es_admin || Auth::id() == $curso->id_profesor)
                            <div class="p-3 text-center bg-dark bg-opacity-25" style="border-top: 1px solid var(--border-dark);">
                                <a href="{{ route('mis_cursos.recursos_drive', $curso->id_curso_activo) }}" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                                    <i class="bi bi-plus-circle me-2"></i> GESTIONAR RECURSOS DRIVE
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Accordion 3: Registrar Factura -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRegistrarFactura">
                        <i class="bi bi-file-earmark-arrow-up text-success me-2"></i> Registrar Factura Electrónica
                    </button>
                </h2>
                <div id="collapseRegistrarFactura" class="accordion-collapse collapse" data-bs-parent="#accordionGestionCurso">
                    <div class="accordion-body">
                        <div class="row g-4">
                            <div class="col-lg-5">
                                <div class="bg-dark p-4 rounded-4 border border-secondary shadow-sm h-100 text-white">
                                    <h6 class="fw-bold text-success mb-3 border-bottom border-secondary pb-2"><i class="bi bi-info-circle-fill text-primary me-2"></i>Datos para Facturación</h6>
                                    <div class="mb-2"><small class="text-white-50 d-block">Razón Social:</small><span class="fw-bold small">{{ config('cliente.nombre_legal', 'CEFI') }}</span></div>
                                    <div class="mb-2"><small class="text-white-50 d-block">Cédula Jurídica:</small><span class="fw-bold small">{{ config('cliente.cedula_juridica', '3-002-000000') }}</span></div>
                                    <div class="mb-2"><small class="text-white-50 d-block">Correo:</small><span class="fw-bold small">{{ config('cliente.email_finanzas', 'finanzas@cefi.cr') }}</span></div>
                                    <div class="mb-2"><small class="text-white-50 d-block">Teléfono:</small><span class="fw-bold small">{{ config('cliente.telefono_display', '+506 8777-7849') }}</span></div>
                                    <div><small class="text-white-50 d-block">Dirección:</small><span class="fw-bold small">{{ config('cliente.direccion', 'San José, Costa Rica') }}</span></div>
                                </div>
                            </div>
                            <div class="col-lg-7 text-white">
                                <form id="formSubirFactura">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label-custom">Nro. Factura *</label>
                                        <input type="text" name="numero_factura" class="form-control form-control-custom w-100" placeholder="Ej: FE-0001" required>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-md-7">
                                            <label class="form-label-custom">Monto *</label>
                                            <input type="number" step="0.01" name="monto" class="form-control form-control-custom w-100" required>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label-custom">Moneda *</label>
                                            <select name="moneda" class="form-select form-select-custom w-100">
                                                <option value="CRC">Colones (₡)</option>
                                                <option value="USD">Dólares ($)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label-custom">Archivo (PDF o XML) *</label>
                                        <input type="file" name="factura_archivo" class="form-control form-control-custom w-100" accept=".pdf,.xml" required>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold py-2.5"><i class="bi bi-cloud-upload me-2"></i>SUBIR Y REGISTRAR FACTURA</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accordion 4: Historial Facturación -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHistorialFactura">
                        <i class="bi bi-clock-history text-primary me-2"></i> Historial de Facturación <span class="badge bg-light text-dark border ms-2">{{ count($facturas) }}</span>
                    </button>
                </h2>
                <div id="collapseHistorialFactura" class="accordion-collapse collapse" data-bs-parent="#accordionGestionCurso">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-grades table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-white-50 small">
                                        <th class="ps-4">Fecha</th>
                                        <th>Nro. Factura</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th class="text-end pe-4">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($facturas) > 0)
                                        @foreach ($facturas as $f)
                                            <tr>
                                                <td class="ps-4 text-white-50">{{ $f->fecha_subida->format('d/m/Y') }}</td>
                                                <td class="fw-bold">{{ $f->numero_factura }}</td>
                                                <td>{{ $f->moneda === 'CRC' ? '₡' : '$' }}{{ number_format($f->monto, 2) }}</td>
                                                <td>
                                                    <span class="badge rounded-pill bg-opacity-20 py-1.5 px-3 border {{ $f->estado_revision === 'aprobada' ? 'bg-success text-success border-success border-opacity-20' : ($f->estado_revision === 'pendiente' ? 'bg-warning text-warning border-warning border-opacity-20' : 'bg-danger text-danger border-danger border-opacity-20') }}">
                                                        {{ strtoupper($f->estado_revision) }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <a href="{{ asset($f->archivo_ruta) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3">
                                                            <i class="bi bi-file-earmark-pdf me-1"></i> Ver
                                                        </a>
                                                        <form action="{{ route('mis_cursos.eliminar_factura', $curso->id_curso_activo) }}" method="POST" onsubmit="return confirm('¿Seguro que desea eliminar esta factura?')">
                                                            @csrf
                                                            <input type="hidden" name="id_factura_eliminar" value="{{ $f->id }}">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                                <i class="bi bi-trash3"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-white-50">No hay facturas registradas.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accordion 5: Repositorio Sílabo -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSilabo">
                        <i class="bi bi-journal-check text-info me-2"></i> Repositorio de Sílabo Oficial
                    </button>
                </h2>
                <div id="collapseSilabo" class="accordion-collapse collapse" data-bs-parent="#accordionGestionCurso">
                    <div class="accordion-body">
                        <div class="text-center py-5 text-white">
                            <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 4rem;"></i>
                            <h6 class="fw-bold mt-3 text-white">Versión Actual del Sílabo</h6>
                            <p class="text-white-50 small">Documento oficial aprobado para este periodo.</p>
                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <a href="{{ route('mis_cursos.gestionar_silabo', $curso->id_curso_activo) }}" class="btn btn-info rounded-pill px-4 shadow-sm fw-bold">
                                    <i class="bi bi-eye me-2"></i> VISUALIZAR SÍLABO
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accordion 6: Encuesta de Evaluación Docente -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEncuesta">
                        <i class="bi bi-clipboard2-check-fill text-success me-2"></i> Encuesta de Evaluación Docente
                        @if (!empty($curso->check_encuesta))
                            <span class="badge rounded-pill ms-2 fw-semibold bg-success bg-opacity-20 text-success" style="font-size: .75rem;">Publicada</span>
                        @else
                            <span class="badge rounded-pill ms-2 fw-semibold bg-secondary bg-opacity-20 text-white-50" style="font-size: .75rem;">No publicada</span>
                        @endif
                    </button>
                </h2>
                <div id="collapseEncuesta" class="accordion-collapse collapse" data-bs-parent="#accordionGestionCurso">
                    <div class="accordion-body p-4 text-white">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 p-3 rounded-4 bg-dark border border-secondary border-opacity-25">
                            <div>
                                <h6 class="fw-bold mb-1"><i class="bi bi-link-45deg text-success me-1"></i> Enlace Público de la Encuesta</h6>
                                <small class="text-white-50">Los estudiantes pueden responder desde este enlace seguro y anónimo.</small>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="copiarEnlaceEncuesta('{{ route('encuesta.responder', $curso->id_curso_activo) }}')">
                                    <i class="bi bi-clipboard me-1"></i> Copiar Enlace
                                </button>
                                <a href="{{ route('encuesta.responder', $curso->id_curso_activo) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir Encuesta
                                </a>
                                <a href="{{ route('encuestas.resultados', ['id_curso' => $curso->id_curso_activo]) }}" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                                    <i class="bi bi-bar-chart-fill me-1"></i> Ver Resultados
                                </a>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-25 text-center">
                                    <i class="bi bi-shield-check text-success fs-3 d-block mb-1"></i>
                                    <h6 class="fw-bold mb-0">100% Anónima</h6>
                                    <small class="text-white-50">Garantiza total privacidad en las respuestas.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-25 text-center">
                                    <i class="bi bi-mortarboard text-primary fs-3 d-block mb-1"></i>
                                    <h6 class="fw-bold mb-0">Integrada a Moodle</h6>
                                    <small class="text-white-50">Se enlaza directamente en la sección general del curso.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-25 text-center">
                                    <i class="bi bi-sliders text-warning fs-3 d-block mb-1"></i>
                                    <h6 class="fw-bold mb-0">Gestión Global</h6>
                                    <small class="text-white-50">Reactivos actualizables desde el banco de preguntas.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- BOTÓN FLOTANTE (REVISIÓN ACADÉMICA / OBSERVACIONES) -->
    <div class="academic-float-btn-container">
        @if ($revisiones_pendientes > 0)
            <button type="button" class="btn alert-pulse btn-lg rounded-pill shadow-lg px-4 py-3 d-flex align-items-center gap-2 text-white" onclick="abrirModalRevisiones()">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <span class="fw-bold">Observaciones ({{ $revisiones_pendientes }})</span>
            </button>
        @else
            <button type="button" class="btn btn-warning btn-lg rounded-pill shadow-lg px-4 py-3 d-flex align-items-center gap-2" onclick="abrirModalRevisiones()">
                <i class="bi bi-pencil-square fs-5"></i>
                <span class="fw-bold">Revisión Acad.</span>
            </button>
        @endif
    </div>

    <!-- MODAL DE REVISIONES ACADÉMICAS -->
    <div class="modal fade" id="modalRevisiones" tabindex="-1" aria-labelledby="modalRevisionesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content bg-dark border border-secondary text-white rounded-4">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold" id="modalRevisionesLabel">
                        <i class="bi bi-shield-check text-warning me-2"></i> Supervisión Académica / Revisiones del Curso
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    
                    <div class="alert alert-info border-0 rounded-3 mb-4 d-flex align-items-center" style="background-color: rgba(13, 202, 240, 0.1); color: #0dcaf0; border: 1px solid rgba(13, 202, 240, 0.2) !important;">
                        <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                        <div>
                            <small class="fw-bold">Instrucciones:</small><br>
                            <span class="small">El departamento académico coloca observaciones y capturas para mejorar la calidad del curso. El profesor debe revisar cada punto y marcarlo como resuelto.</span>
                        </div>
                    </div>

                    <!-- FORMULARIO NUEVA OBSERVACIÓN (ADMIN ONLY) -->
                    @if ($es_admin)
                        <div class="card bg-black bg-opacity-20 border border-secondary p-4 rounded-3 mb-4 text-white">
                            <h6 class="fw-bold text-success mb-3"><i class="bi bi-plus-circle me-1"></i> Nueva Observación o Recomendación</h6>
                            <form id="formNuevaRevision" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Detalle de la Recomendación</label>
                                    <textarea name="sugerencia" class="form-control form-control-custom w-100" rows="3" placeholder="Describa el problema o sugerencia..." required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Adjuntar Captura de Pantalla (Opcional)</label>
                                    <input type="file" name="captura" class="form-control form-control-custom w-100" accept="image/*">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4" id="btnGuardarRevision" style="background-color: var(--primary); border: none;">
                                    <i class="bi bi-save me-1"></i> Guardar Observación
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- LISTADO DE OBSERVACIONES -->
                    <h6 class="fw-bold text-white mb-3"><i class="bi bi-list-task me-1"></i> Lista de Recomendaciones</h6>
                    <div id="listaRevisiones" class="d-flex flex-column gap-3">
                        <!-- Loading spinner -->
                    </div>

                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const esAdmin = {{ $es_admin ? 'true' : 'false' }};
        const idCurso = {{ $curso->id_curso_activo }};
        let modalRevisionesObj = null;

        function getLegacyUrl(path) {
            return '{{ url('/') }}/' + path;
        }

        function scrollToAndOpen(collapseId) {
            const collapseElement = document.getElementById(collapseId);
            const accordionBtn = collapseElement.parentElement.querySelector('.accordion-button');
            if (collapseElement.classList.contains('collapse') && !collapseElement.classList.contains('show')) {
                bootstrap.Collapse.getOrCreateInstance(collapseElement).show();
            }
            setTimeout(() => { collapseElement.parentElement.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 300);
        }

        function toggleHito(tipo) {
            $.ajax({
                url: "{{ route('supervision.toggle_check') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id_curso: idCurso,
                    tipo: tipo
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        }

        function abrirPantallaBienvenida() {
            Swal.fire({ 
                title: 'Minutos de Cuenta Regresiva:', 
                input: 'number', 
                inputValue: 5,
                showCancelButton: true,
                confirmButtonText: 'Abrir Cortina',
                cancelButtonText: 'Cancelar'
            }).then(r => { 
                if (r.isConfirmed && r.value) { 
                    window.open("{{ route('mis_cursos.cortina', $curso->id_curso_activo) }}?minutes=" + r.value, '_blank'); 
                } 
            });
        }

        function sincronizarMoodle() {
            Swal.fire({ title: 'Conectando con Moodle...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const check_s = {{ $curso->check_listo ? 1 : 0 }};
            const check_r = {{ $curso->check_recursos ? 1 : 0 }};
            const check_p = {{ $curso->check_portada ? 1 : 0 }};
            const check_e = {{ $curso->check_estudiantes ? 1 : 0 }};
            const all_ready = (check_s && check_r && check_p);

            $.ajax({
                url: getLegacyUrl('mis_cursos/get_moodle_categories.php'),
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    Swal.close();
                    if (data.error) {
                        Swal.fire('Error', data.error, 'error');
                        return;
                    }

                    Swal.fire({
                        title: 'Sincronizar con {{ config('cliente.nombre', 'CEFI') }} Virtual',
                        html: `
                            <div class="mb-4 text-center">
                                <div class="d-inline-flex p-3 rounded-circle bg-light bg-opacity-10 mb-3">
                                    <i class="bi bi-cloud-arrow-up-fill text-success fs-1"></i>
                                </div>
                                <h5 class="fw-bold">Bóveda de Sincronización</h5>
                                <p class="text-muted small">Configure el destino y valide con su clave maestra.</p>
                            </div>

                            <div class="d-flex justify-content-center gap-2 mb-4">
                                <div class="p-2 rounded border ${check_s ? 'bg-info border-info text-white' : 'bg-light text-muted'}" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer;" onclick="toggleHito('listo')" title="Sílabo Terminado">
                                    <span class="fw-bold">S</span>
                                </div>
                                <div class="p-2 rounded border ${check_r ? 'bg-primary border-primary text-white' : 'bg-light text-muted'}" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer;" onclick="toggleHito('recursos')" title="Recursos Drive Listos">
                                    <span class="fw-bold">R</span>
                                </div>
                                <div class="p-2 rounded border ${check_p ? 'bg-warning border-warning text-white' : 'bg-light text-muted'}" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer;" onclick="toggleHito('portada')" title="Portada Lista">
                                    <span class="fw-bold">P</span>
                                </div>
                            </div>

                            <div class="text-start mb-3">
                                <label class="form-label small fw-bold text-muted">Categoría en Moodle</label>
                                <select id="swal_cat_id" class="form-select bg-dark text-white border-secondary">
                                    ${data.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
                                </select>
                            </div>

                            <div class="text-start mb-0">
                                <label class="form-label small fw-bold text-muted">Validación de Seguridad</label>
                                <input type="password" id="swal_auth_key" class="form-control text-center fs-5 bg-dark text-white border-secondary" placeholder="Palabra Clave" style="letter-spacing: 0.3rem;">
                            </div>
                            
                            ${!all_ready ? '<div class="alert alert-warning py-2 px-3 mt-3 small mb-0 border-0 shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i>Debe completar los hitos del curso (S, R, P) para iniciar.</div>' : ''}
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Iniciar Sincronización',
                        confirmButtonColor: '#10b981',
                        showConfirmButton: all_ready,
                        width: '450px',
                        preConfirm: () => {
                            const catId = document.getElementById('swal_cat_id').value;
                            const key = document.getElementById('swal_auth_key').value;
                            if (key !== "{{ config('cliente.moodle_key', 'cefi2026') }}") {
                                Swal.showValidationMessage('Palabra clave incorrecta');
                                return false;
                            }
                            return { catId, key };
                        }
                    }).then(res => {
                        if (!res || !res.isConfirmed) return;

                        const catId = res.value.catId;
                        const key = res.value.key;

                        // Paso 1: Init
                        Swal.fire({ title: 'Paso 1: Inicializando Curso...', html: 'Preparando entorno en Moodle...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                        $.ajax({
                            url: getLegacyUrl(`mis_cursos/sincronizar_moodle.php?step=init&id_curso=${idCurso}&id_plan={{ $curso->id_plan }}&category_id=${catId}&key=${key}`),
                            type: 'GET',
                            dataType: 'json',
                            success: function(initData) {
                                if (!initData.success) {
                                    Swal.fire('Error', initData.message, 'error');
                                    return;
                                }

                                const totalWeeks = initData.total_weeks;
                                const moodleId = initData.moodle_id;
                                
                                // Paso 2: Sync weeks sequentially
                                syncWeeks(0, totalWeeks, moodleId, key);
                            },
                            error: function() {
                                Swal.fire('Error', 'Error al conectar con Moodle.', 'error');
                            }
                        });
                    });
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'No se pudieron recuperar las categorías de Moodle.', 'error');
                }
            });
        }

        async function syncWeeks(weekIdx, totalWeeks, moodleId, key) {
            if (weekIdx >= totalWeeks) {
                Swal.fire({
                    title: '¡Sincronización Exitosa!',
                    text: 'El curso y todas sus actividades han sido creados en Moodle.',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-box-arrow-up-right me-2"></i> IR AL CURSO',
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    cancelButtonText: 'Cerrar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(`{{ config('cliente.campus_virtual', 'https://virtual.cefi.cr') }}/course/view.php?id=${moodleId}`, '_blank');
                    }
                });
                return;
            }

            const percent = Math.round(((weekIdx + 1) / totalWeeks) * 100);
            Swal.update({ 
                title: `Sincronizando Semanas...`, 
                html: `Procesando semana ${weekIdx+1} de ${totalWeeks}<br><br>
                       <div class="progress" style="height: 20px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: ${percent}%">${percent}%</div>
                       </div>` 
            });

            $.ajax({
                url: getLegacyUrl(`mis_cursos/sincronizar_moodle.php?step=week&week_idx=${weekIdx}&id_curso=${idCurso}&id_plan={{ $curso->id_plan }}&key=${key}`),
                type: 'GET',
                dataType: 'json',
                success: function(weekRes) {
                    if (!weekRes.success) {
                        Swal.fire('Error de sincronización', `Error en la semana ${weekIdx+1}: ${weekRes.message}`, 'error');
                        return;
                    }
                    syncWeeks(weekIdx + 1, totalWeeks, moodleId, key);
                },
                error: function() {
                    Swal.fire('Error', 'Fallo de conexión al sincronizar semana.', 'error');
                }
            });
        }

        function sincronizarAlumnos() {
            const idMoodle = {{ $curso->id_moodle ?: 0 }};
            if (!idMoodle) {
                Swal.fire('ID Moodle Requerido', 'Este curso aún no tiene un ID de Moodle asociado. Debe sincronizar la estructura primero o asignar un ID manualmente.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Validación de Seguridad',
                html: `
                    <div class="mb-4 text-center">
                        <div class="d-inline-flex p-3 rounded-circle bg-light bg-opacity-10 mb-3">
                            <i class="bi bi-people-fill text-success fs-1"></i>
                        </div>
                        <h5 class="fw-bold">Bóveda de Estudiantes</h5>
                        <p class="text-muted small">Ingrese la clave maestra para sincronizar la matrícula desde Moodle.</p>
                    </div>
                    <div class="text-start mb-0">
                        <input type="password" id="swal_auth_key_est" class="form-control text-center fs-5 bg-dark text-white border-secondary" placeholder="Palabra Clave" style="letter-spacing: 0.3rem;">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Validar y Sincronizar',
                confirmButtonColor: '#10b981',
                width: '450px',
                preConfirm: () => {
                    const key = document.getElementById('swal_auth_key_est').value;
                    if (key !== "{{ config('cliente.moodle_key', 'cefi2026') }}") {
                        Swal.showValidationMessage('Palabra clave incorrecta');
                        return false;
                    }
                    return key;
                }
            }).then(res => {
                if (!res.isConfirmed) return;

                Swal.fire({
                    title: 'Sincronizando Alumnos...',
                    text: 'Obteniendo matrícula desde Moodle Virtual.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "{{ route('mis_cursos.sincronizar_estudiantes', $curso->id_curso_activo) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_moodle: idMoodle,
                        key: res.value
                    },
                    success: function(data) {
                        if (data.success) {
                            Swal.fire({
                                title: '¡Sincronización Exitosa!',
                                html: `Se han procesado <b>${data.details.moodle_total}</b> alumnos.<br>
                                       Nuevas matrículas: <b>${data.details.nuevas_matriculas}</b>`,
                                icon: 'success'
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                    }
                });
            });
        }

        function checkIfAllNotesAreComplete() {
            // El botón permanece habilitado para soporte de estudiantes oyentes
            const btnActa = document.getElementById('btnActaCalificaciones');
            if (btnActa) {
                btnActa.disabled = false;
            }
        }

        // Manejador del botón Generar Acta Oficial
        $('#btnActaCalificaciones').on('click', function(e) {
            e.preventDefault();
            let hayIncompletos = false;
            const inputs = document.querySelectorAll('.input-nota-modern');
            if (inputs.length === 0) {
                hayIncompletos = true;
            } else {
                inputs.forEach(i => {
                    if (i.value.trim() === '' || isNaN(parseFloat(i.value))) {
                        hayIncompletos = true;
                    }
                });
            }

            const actaUrl = "{{ route('mis_cursos.acta_pdf', $curso->id_curso_activo) }}";

            if (hayIncompletos) {
                Swal.fire({
                    title: '¿Generar Acta con notas pendientes?',
                    html: 'Se detectaron estudiantes sin calificar o en condición de <b>Oyente</b>.<br><br>¿Deseas emitir el Acta Oficial de Calificaciones con las notas registradas hasta el momento?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="bi bi-file-earmark-pdf-fill me-1"></i> Sí, generar Acta Oficial',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(actaUrl, '_blank');
                    }
                });
            } else {
                window.open(actaUrl, '_blank');
            }
        });

        function sincronizarNotasMoodle(idCurso) {
            Swal.fire({
                title: '🎓 Sincronizar Calificaciones',
                html: `
                    <p class="text-white-50 small mb-3">
                        Esta acción consultará en tiempo real el Libro de Calificaciones de <b>Moodle Virtual</b> y cargará las notas obtenidas por rubro para cada estudiante matriculado.
                    </p>
                    <div class="alert alert-info py-2 small text-start mb-0">
                        <i class="bi bi-info-circle-fill me-1"></i> Se recalculará automáticamente el promedio final de cada estudiante (Aprobado / Reprobado).
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-cloud-arrow-down-fill me-1"></i> Iniciar Sincronización',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                cancelButtonText: 'Cancelar'
            }).then(res => {
                if (!res.isConfirmed) return;

                Swal.fire({
                    title: 'Sincronizando con Moodle...',
                    html: `
                        <div class="d-flex justify-content-center my-3">
                            <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;"></div>
                        </div>
                        <span class="text-white-50 small">Descargando libro de calificaciones y emparejando rubros...</span>
                    `,
                    allowOutsideClick: false,
                    showConfirmButton: false
                });

                $.ajax({
                    url: "{{ route('mis_cursos.sincronizar_moodle_notas', $curso->id_curso_activo) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(data) {
                        if (data.success) {
                            Swal.fire({
                                title: '¡Calificaciones Sincronizadas!',
                                html: `
                                    <div class="text-start bg-dark p-3 rounded border border-secondary small mb-2 text-white">
                                        ✅ <b>Estudiantes procesados:</b> ${data.total_procesados || 0}<br>
                                        📊 <b>Rubros actualizados:</b> ${data.notas_insertadas || 0}<br>
                                        📄 <b>Acta Oficial:</b> Lista para generar.
                                    </div>
                                `,
                                icon: 'success',
                                confirmButtonText: 'Ver Tabla de Notas',
                                confirmButtonColor: '#10b981'
                            }).then(() => {
                                window.location.hash = '#collapseNotas';
                                location.reload();
                            });
                        } else {
                            Swal.fire('Atención', data.message, 'warning');
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error en la conexión con el servidor.';
                        Swal.fire('Error de Conexión', msg, 'error');
                    }
                });
            });
        }

        // Grades matrix input triggers
        document.querySelectorAll('.input-nota-modern').forEach(input => {
            input.addEventListener('input', function() {
                const idMat = this.dataset.matricula;
                const maxR = parseFloat(this.dataset.porcentaje) || 100;
                let val = parseFloat(this.value);
                if (val > maxR) { this.value = maxR; val = maxR; }
                
                let total = 0; let filaComp = true;
                document.querySelectorAll(`.input-nota-modern[data-matricula="${idMat}"]`).forEach(i => {
                    const v = parseFloat(i.value);
                    if (!isNaN(v)) total += v; else filaComp = false;
                });
                
                total = Math.round((total + Number.EPSILON) * 100) / 100;
                const dispT = document.getElementById(`total_${idMat}`);
                dispT.innerText = total.toFixed(2);
                const dispC = document.getElementById(`condicion_${idMat}`);
                
                if (!filaComp) {
                    dispT.className = 'text-white-50';
                    dispC.innerText = 'Sin calificar';
                    dispC.className = 'badge rounded-pill bg-secondary bg-opacity-10 text-white-50 border border-secondary px-3';
                } else if (total >= 70) { 
                    dispT.className = 'text-success'; 
                    dispC.innerText = 'Aprobado'; 
                    dispC.className = 'badge rounded-pill bg-success px-3'; 
                } else { 
                    dispT.className = 'text-danger'; 
                    dispC.innerText = 'Reprobado'; 
                    dispC.className = 'badge rounded-pill bg-danger px-3'; 
                }
                
                const btnInd = document.querySelector(`button[onclick*="enviarNotaIndividual(${idMat},"]`);
                if (btnInd) { btnInd.disabled = !filaComp; }
                checkIfAllNotesAreComplete();
            });
        });

        function enviarNotaIndividual(idMat, nombre, email) {
            Swal.fire({
                title: '¿Enviar calificación?',
                html: `Se enviará la nota por correo electrónico a <b>${nombre}</b>.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: "{{ route('mis_cursos.enviar_nota_individual', $curso->id_curso_activo) }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id_matricula: idMat
                        }
                    }).then(res => {
                        if (!res.success) throw new Error(res.message);
                        return res;
                    });
                }
            }).then(r => {
                if (r.isConfirmed) {
                    Swal.fire('Enviado', r.value.message, 'success').then(() => location.reload());
                }
            }).catch(err => {
                Swal.fire('Error', err.message, 'error');
            });
        }

        function notificarNotasTodos() {
            const buttons = Array.from(document.querySelectorAll('.btn-notificar-individual')).filter(btn => !btn.disabled);
            if (buttons.length === 0) {
                Swal.fire('Sin notas listas', 'No hay estudiantes con calificaciones completas listas para notificar.', 'info');
                return;
            }

            const noEnviados = buttons.filter(btn => btn.dataset.enviado == "0");
            const countText = noEnviados.length === buttons.length 
                ? `los <b>${buttons.length}</b> estudiantes con notas completas.`
                : `<b>${noEnviados.length}</b> estudiantes que tienen notas completas y aún no han sido notificados (de un total de ${buttons.length} listos).`;

            Swal.fire({
                title: '¿Enviar notificaciones?',
                html: `Se enviarán las calificaciones finales por correo electrónico a ${countText}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, enviar a todos',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (!result.isConfirmed) return;

                const targets = noEnviados.length > 0 ? noEnviados : buttons;
                
                Swal.fire({
                    title: 'Enviando Calificaciones...',
                    html: `Procesando correo <b>1</b> de ${targets.length}...<br><br>
                           <div class="progress" style="height: 20px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%">0%</div>
                           </div>`,
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                let enviados = 0;
                let errores = 0;

                for (let i = 0; i < targets.length; i++) {
                    const btn = targets[i];
                    const idMat = btn.dataset.idMatricula;
                    const nombre = btn.dataset.nombre;
                    const percent = Math.round(((i + 1) / targets.length) * 100);

                    Swal.update({
                        html: `Enviando correo a <b>${nombre}</b> (${i+1} de ${targets.length})...<br><br>
                               <div class="progress" style="height: 20px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: ${percent}%">${percent}%</div>
                               </div>`
                    });

                    try {
                        const res = await $.ajax({
                            url: "{{ route('mis_cursos.enviar_nota_individual', $curso->id_curso_activo) }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                id_matricula: idMat
                            }
                        });
                        if (res.success) enviados++; else errores++;
                    } catch (err) {
                        errores++;
                    }
                }

                Swal.fire({
                    title: 'Proceso Finalizado',
                    html: `Se enviaron con éxito <b>${enviados}</b> notificaciones.<br>
                           ${errores > 0 ? `<span class="text-danger">Fallaron <b>${errores}</b> envíos.</span>` : ''}`,
                    icon: errores === 0 ? 'success' : 'warning'
                }).then(() => location.reload());
            });
        }

        // Academic Reviews Modal
        function abrirModalRevisiones() {
            if (!modalRevisionesObj) {
                modalRevisionesObj = new bootstrap.Modal(document.getElementById('modalRevisiones'));
            }
            cargarRevisiones();
            modalRevisionesObj.show();
        }

        function cargarRevisiones() {
            const listContainer = document.getElementById('listaRevisiones');
            listContainer.innerHTML = `
                <div class="text-center py-4 text-white-50">
                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                    <span class="d-block small">Cargando observaciones...</span>
                </div>`;

            $.ajax({
                url: "{{ route('mis_cursos.revisiones.list', $curso->id_curso_activo) }}",
                type: "GET",
                success: function(response) {
                    if (!response.success) {
                        listContainer.innerHTML = `<div class="alert alert-danger small p-2">${response.message}</div>`;
                        return;
                    }

                    if (response.data.length === 0) {
                        listContainer.innerHTML = `
                            <div class="text-center py-4 p-3 rounded-4 shadow-sm" style="background-color: var(--card-dark, #f8fafc); border: 1px solid var(--border-dark, #e2e8f0);">
                                <div class="d-inline-flex p-3 rounded-circle bg-success bg-opacity-10 mb-2">
                                    <i class="bi bi-emoji-smile-fill text-success fs-2"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">¡Felicidades!</h6>
                                <p class="small text-muted mb-0">No hay observaciones ni sugerencias pendientes para este curso.</p>
                            </div>`;
                        return;
                    }

                    let html = '';
                    response.data.forEach(item => {
                        const esResuelto = (item.estado === 'resuelto');
                        const classResuelto = esResuelto ? 'resuelto' : '';
                        
                        html += `
                            <div class="card shadow-sm border-0 rounded-3 revision-item ${classResuelto} p-3 position-relative text-white" style="border-left-width: 5px !important;">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge rounded-pill bg-dark text-white border border-secondary px-2 py-1 x-small me-2" style="font-size: 0.7rem;">
                                            <i class="bi bi-person me-1"></i> ${item.creador}
                                        </span>
                                        <span class="badge rounded-pill bg-dark text-white border border-secondary px-2 py-1 x-small" style="font-size: 0.7rem;">
                                            <i class="bi bi-calendar-event me-1"></i> ${item.fecha_creacion}
                                        </span>
                                    </div>
                                    <div>
                                        ${esResuelto ? 
                                            `<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 border border-success" style="font-size: 0.75rem;"><i class="bi bi-check-circle-fill me-1"></i> Resuelto</span>` : 
                                            `<span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-1 border border-warning" style="font-size: 0.75rem;"><i class="bi bi-clock-history me-1"></i> Pendiente</span>`
                                        }
                                    </div>
                                </div>
                                
                                <p class="mb-3 text-white small" style="white-space: pre-line; font-size: 0.9rem;">${item.sugerencia}</p>
                                
                                <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                                    ${item.captura_pantalla ? `
                                        <div class="w-100 mb-2">
                                            <a href="${item.captura_pantalla}" target="_blank" class="d-inline-block border rounded p-1 bg-white" style="max-width: 120px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" title="Clic para ampliar captura">
                                                <img src="${item.captura_pantalla}" class="img-fluid rounded" alt="Miniatura de captura" style="max-height: 70px; object-fit: contain;">
                                            </a>
                                        </div>
                                    ` : ''}
                                    
                                    ${!esResuelto ? `
                                        <button type="button" class="btn btn-success btn-xs rounded-pill px-3 ms-auto" style="font-size: 0.75rem;" onclick="resolverRevision(${item.id})">
                                            <i class="bi bi-check-lg me-1"></i> Marcar como Solucionado
                                        </button>
                                    ` : `
                                        <div class="ms-auto x-small text-white-50" style="font-size: 0.75rem;">
                                            <i class="bi bi-check-all text-success"></i> Solucionado por <b>${item.resolutor}</b> el ${item.fecha_resolucion}
                                        </div>
                                    `}
                                    
                                    ${esAdmin ? `
                                        <button type="button" class="btn btn-outline-danger btn-xs rounded-circle p-1 ms-2" style="width: 28px; height: 28px; font-size: 0.75rem; display: inline-flex; align-items: center; justify-content: center;" onclick="eliminarRevision(${item.id})" title="Eliminar Observación">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        `;
                    });
                    listContainer.innerHTML = html;
                }
            });
        }

        function resolverRevision(id) {
            Swal.fire({
                title: '¿Confirmar Solución?',
                text: '¿Has completado o solucionado la recomendación realizada por el departamento académico?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, solucionado',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: "{{ route('mis_cursos.revisiones.resolver') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id
                        }
                    }).then(res => {
                        if (!res.success) throw new Error(res.message);
                        return res;
                    });
                }
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire('Solucionado', result.value.message, 'success');
                    cargarRevisiones();
                    if (result.value.todo_resuelto) {
                        setTimeout(() => location.reload(), 1500);
                    }
                }
            });
        }

        function eliminarRevision(id) {
            Swal.fire({
                title: '¿Eliminar Observación?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: "{{ route('mis_cursos.revisiones.delete') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id
                        }
                    }).then(res => {
                        if (!res.success) throw new Error(res.message);
                        return res;
                    });
                }
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire('Eliminado', result.value.message, 'success');
                    cargarRevisiones();
                    setTimeout(() => location.reload(), 1000);
                }
            });
        }

        // Form Nueva Revision (Submit)
        $(document).on('submit', '#formNuevaRevision', function(e) {
            e.preventDefault();
            const btn = $('#btnGuardarRevision');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Guardando...');

            const fd = new FormData(this);
            $.ajax({
                url: "{{ route('mis_cursos.revisiones.store', $curso->id_curso_activo) }}",
                type: "POST",
                data: fd,
                contentType: false,
                processData: false,
                success: function(data) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Guardar Observación');
                    if (data.success) {
                        Swal.fire('Guardado', data.message, 'success');
                        $('#formNuevaRevision')[0].reset();
                        cargarRevisiones();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                }
            });
        });

        // Billing form upload
        $(document).on('submit', '#formSubirFactura', function(e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Procesando...');

            const fd = new FormData(this);
            $.ajax({
                url: "{{ route('mis_cursos.subir_factura', $curso->id_curso_activo) }}",
                type: "POST",
                data: fd,
                contentType: false,
                processData: false,
                success: function(data) {
                    if (data.success) {
                        Swal.fire('¡Éxito!', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                        btn.prop('disabled', false).html('<i class="bi bi-cloud-upload me-2"></i>SUBIR Y REGISTRAR FACTURA');
                    }
                }
            });
        });

        function copiarEnlaceEncuesta(url) {
            navigator.clipboard.writeText(url);
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Enlace de encuesta copiado al portapapeles', showConfirmButton: false, timer: 2000 });
        }

        $(document).ready(function() {
            checkIfAllNotesAreComplete();
        });
    </script>
@endsection
