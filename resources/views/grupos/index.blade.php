@extends('layouts.app')

@section('title', 'Inbox BPM - Gestión de Grupos de Estudio')

@section('styles')
    <style>
        .page-header {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            margin-bottom: 2rem;
            overflow: visible !important;
        }

        .form-control-custom, .form-select-custom {
            background-color: var(--card-dark);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: var(--text-light);
            padding: 0.7rem 1rem;
            transition: all 0.3s;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            background-color: var(--card-dark);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15);
            color: var(--text-light);
            outline: none;
        }

        [data-theme="light"] .form-control-custom, 
        [data-theme="light"] .form-select-custom {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        [data-theme="light"] .form-control-custom:focus, 
        [data-theme="light"] .form-select-custom:focus {
            background-color: #ffffff !important;
            border-color: var(--primary) !important;
            color: #0f172a !important;
        }

        /* Modal adaptación tema claro y oscuro */
        .modal-custom-content {
            background-color: var(--card-dark) !important;
            border: 1px solid var(--border-dark) !important;
            border-radius: 1.25rem !important;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.3);
        }

        [data-theme="light"] .modal-custom-content {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.15) !important;
        }

        [data-theme="light"] .modal-custom-content .modal-header,
        [data-theme="light"] .modal-custom-content .modal-footer {
            border-color: #e2e8f0 !important;
        }

        [data-theme="light"] .modal-custom-content .modal-body {
            color: #0f172a !important;
        }

        [data-theme="light"] .modal-custom-content .form-label-custom {
            color: #475569 !important;
        }

        [data-theme="light"] .modal-custom-content .btn-close-custom {
            filter: none !important;
        }

        [data-theme="dark"] .modal-custom-content .btn-close-custom {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        .form-label-custom {
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .period-pill {
            background-color: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-dark);
            border-radius: 1rem;
            padding: 0.5rem 1rem;
        }

        .program-group-header {
            background-color: rgba(255, 255, 255, 0.02) !important;
            border-left: 5px solid var(--primary) !important;
            color: var(--primary) !important;
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        .action-btn-custom {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-dark);
            color: var(--text-light);
            width: 36px;
            height: 36px;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .action-btn-custom:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-1px);
        }

        .action-btn-delete:hover {
            background-color: #ef4444;
            border-color: #ef4444;
            color: white;
        }

        .btn-submit {
            background-color: var(--primary);
            border: none;
            color: white;
            padding: 0.9rem;
            border-radius: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(95, 178, 48, 0.25);
        }

        /* Dark themed transparent table list */
        .table-custom {
            margin-bottom: 0;
            background-color: transparent !important;
        }
        .table-custom td, .table-custom th {
            background-color: transparent !important;
            color: #e2e8f0 !important;
            border-bottom: 1px solid var(--border-dark);
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Grupos de Estudio</li>
            </ol>
        </nav>

        <!-- Header & Stats Summary -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-4">
            <div>
                <h1 class="display-6 fw-bold mb-1">Gestión de Grupos de Estudio</h1>
                <p class="text-white-50 mb-0">Configure los periodos, materias y profesores asignados a cada grupo académico.</p>
            </div>
            <div class="d-flex gap-3 flex-wrap">
                @foreach ($resumen_periodos as $res)
                    <div class="period-pill d-flex align-items-center shadow-sm">
                        <div class="me-3">
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 mb-1 d-block">{{ $res->periodo }}</span>
                        </div>
                        <div class="d-flex gap-3">
                            <div>
                                <h6 class="mb-0 fw-bold text-white">{{ $res->total_estudiantes }}</h6>
                                <span class="text-white-50 d-block" style="font-size: 0.6rem; text-transform: uppercase;">Estudiantes</span>
                            </div>
                            <div class="border-start ps-3">
                                <h6 class="mb-0 fw-bold text-success">{{ $res->total_cursos }}</h6>
                                <span class="text-white-50 d-block" style="font-size: 0.6rem; text-transform: uppercase;">Cursos</span>
                            </div>
                        </div>
                    </div>
                @endforeach
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

        <!-- FORMULARIO DE CONFIGURACIÓN / EDICIÓN -->
        <div class="card glass-card" id="card-formulario-grupo">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0 text-white" id="form-titulo">
                        <i class="bi bi-plus-circle-fill text-success me-2" id="form-icono"></i> <span id="form-titulo-texto">Configurar Nuevo Grupo</span>
                    </h5>
                    <button type="button" id="btn-cancelar-edicion" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="display: none;" onclick="cancelarEdicionGrupo()">
                        <i class="bi bi-x-circle me-1"></i> Cancelar Edición
                    </button>
                </div>
                
                <form action="{{ route('grupos.store') }}" method="POST" id="grupo-form">
                    @csrf
                    <input type="hidden" name="_method_custom" id="form_method_custom" value="POST">
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-custom">Carrera / Programa Académico</label>
                            <select id="id_programa_select" class="form-select form-select-custom w-100" required>
                                <option value="">Seleccione el Programa...</option>
                                @foreach ($programas as $p)
                                    <option value="{{ $p->id_programa }}">{{ $p->nombre_programa }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-custom">Materia / Curso Específico</label>
                            <select name="id_plan" id="id_plan_select" class="form-select form-select-custom w-100" required disabled>
                                <option value="">Seleccione un programa primero...</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label-custom">Identificador / Grupo</label>
                            <input type="text" name="nombre_grupo" id="nombre_grupo" class="form-control form-control-custom w-100" placeholder="Ej: Grupo 01, Grupo 02...">
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <label class="form-label-custom">Cuatrimestre / Periodo</label>
                            <select id="cuatrimestre_select" class="form-select form-select-custom w-100">
                                <option value="I Cuatrimestre">I Cuatrimestre (Ene-Abr)</option>
                                <option value="II Cuatrimestre">II Cuatrimestre (May-Ago)</option>
                                <option value="III Cuatrimestre">III Cuatrimestre (Sep-Dic)</option>
                                <option value="I Bimestre">I Bimestre</option>
                                <option value="II Bimestre">II Bimestre</option>
                                <option value="III Bimestre">III Bimestre</option>
                                <option value="IV Bimestre">IV Bimestre</option>
                                <option value="V Bimestre">V Bimestre</option>
                                <option value="VI Bimestre">VI Bimestre</option>
                                <option value="Mes">Mes</option>
                                <option value="Semana">Semana (1 Semana)</option>
                                <option value="Seminario">Seminario</option>
                                <option value="Taller">Taller / Intensivo</option>
                                <option value="Fecha Específica">Fecha Determinada / Específica</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Año Lectivo</label>
                            <select id="ano_select" class="form-select form-select-custom w-100">
                                @for ($anio = 2026; $anio <= 2030; $anio++)
                                    <option value="{{ $anio }}">{{ $anio }}</option>
                                @endfor
                            </select>
                            <input type="hidden" name="periodo" id="periodo">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label-custom text-success fw-bold"><i class="bi bi-cloud-download me-1"></i> ID Moodle</label>
                            <input type="number" name="id_moodle" id="id_moodle_input" class="form-control form-control-custom w-100" placeholder="Ej: 125">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-custom text-success fw-bold">Enlace Zoom (Sincrónico)</label>
                            <input type="url" name="enlace_zoom" id="enlace_zoom_input" class="form-control form-control-custom w-100" placeholder="https://zoom.us/j/...">
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <label class="form-label-custom">Prefijo Título</label>
                            <input type="text" name="titulo_prefijo" id="titulo_prefijo_input" class="form-control form-control-custom w-100" placeholder="Ej: Lic.">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Sufijo Título</label>
                            <input type="text" name="titulo_sufijo" id="titulo_sufijo_input" class="form-control form-control-custom w-100" placeholder="Ej: PhD.">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Modalidad</label>
                            <select name="modalidad" id="modalidad_select" class="form-select form-select-custom w-100">
                                <option value="Virtual">Virtual</option>
                                <option value="Presencial">Presencial</option>
                                <option value="Semi Presencial">Semi Presencial</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Tipo Curso</label>
                            <select name="tipo_curso" id="tipo_curso_select" class="form-select form-select-custom w-100">
                                <option value="Regular">Regular</option>
                                <option value="Tutoría">Tutoría</option>
                                <option value="Seminario">Seminario</option>
                                <option value="Taller">Taller</option>
                                <option value="Curso Libre">Curso Libre / Intensivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-custom">Profesor Asignado <span class="text-white-50 fw-normal">(Opcional)</span></label>
                            <select name="id_profesor" id="id_profesor_select" class="form-select form-select-custom w-100">
                                <option value="">Sin Profesor asignado (Asignar después)...</option>
                                @foreach ($profesores as $prof)
                                    <option value="{{ $prof->id }}">{{ $prof->apellidos }}, {{ $prof->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Horario Sincrónico / Día</label>
                            <div class="input-group">
                                <select name="horario_dia" id="horario_dia_select" class="form-select form-select-custom">
                                    <optgroup label="Día Individual">
                                        <option value="Lunes">Lunes</option>
                                        <option value="Martes">Martes</option>
                                        <option value="Miércoles">Miércoles</option>
                                        <option value="Jueves">Jueves</option>
                                        <option value="Viernes">Viernes</option>
                                        <option value="Sábado">Sábado</option>
                                        <option value="Domingo">Domingo</option>
                                    </optgroup>
                                    <optgroup label="Combinaciones Frecuentes">
                                        <option value="Lunes y Miércoles">Lunes y Miércoles</option>
                                        <option value="Martes y Jueves">Martes y Jueves</option>
                                        <option value="Lunes a Viernes">Lunes a Viernes (Diario)</option>
                                        <option value="Sábado y Domingo">Sábado y Domingo</option>
                                    </optgroup>
                                    <optgroup label="Modalidad Flexible">
                                        <option value="Asincrónico">100% Asincrónico (Sin Horario Fijo)</option>
                                        <option value="Por definir">Por definir / Horario Flexible</option>
                                    </optgroup>
                                </select>
                                <input type="time" name="horario_inicio" id="horario_inicio_input" class="form-control form-control-custom">
                                <input type="time" name="horario_fin" id="horario_fin_input" class="form-control form-control-custom">
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-md-3">
                            <label class="form-label-custom">N° de Lecciones / Semanas</label>
                            <div class="input-group">
                                <input type="number" id="num_lecciones" class="form-control form-control-custom" min="1" max="100" placeholder="Ej: 8" value="8">
                                <span class="input-group-text bg-transparent border-start-0 text-white-50" style="border-color: var(--border-dark);">lecc.</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-custom">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio_input" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-custom">Fecha Final <span class="text-success fw-normal">(Calculada)</span></label>
                            <input type="date" name="fecha_final" id="fecha_final_input" class="form-control form-control-custom w-100" required>
                        </div>
                    </div>

                    <button type="submit" id="btn-submit-form" class="btn btn-submit w-100 py-3 shadow">
                        <i class="bi bi-check-circle-fill me-2" id="btn-submit-icono"></i> <span id="btn-submit-texto">Crear Nuevo Grupo de Estudio</span>
                    </button>

                </form>
            </div>
        </div>

        <!-- TABLA DE GRUPOS -->
        <div class="card glass-card">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Materia / Curso</th>
                            <th>Profesor</th>
                            <th>Periodo</th>
                            <th class="text-center">Horario</th>
                            <th class="text-center">Estudiantes</th>
                            <th class="text-center">Modalidad</th>
                            <th class="text-center">ID Moodle</th>
                            <th class="text-center">Zoom</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($grupos as $progName => $listaCursos)
                            <tr>
                                <td colspan="9" class="program-group-header ps-4 py-3">
                                    <i class="bi bi-layers-half me-2"></i> {{ $progName }}
                                </td>
                            </tr>
                            @foreach ($listaCursos as $g)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="d-block small text-white-50 fw-bold">{{ $g->planEstudio->codigo ?? 'N/A' }}</span>
                                            @if (!empty($g->nombre_grupo))
                                                <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 rounded-pill px-2" style="font-size: 0.65rem;">{{ $g->nombre_grupo }}</span>
                                            @endif
                                        </div>
                                        <span class="fw-bold text-white">{{ $g->planEstudio->materia ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="small fw-semibold text-white-50">
                                            @if ($g->profesor)
                                                {{ $g->profesor->apellidos }}, {{ $g->profesor->nombre }}
                                            @else
                                                <button type="button" onclick="asignarProfesorGrupo({{ $g->id_curso_activo }}, '')" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-0 fw-bold" style="font-size: 0.75rem;">
                                                    <i class="bi bi-person-plus-fill me-1"></i> Sin Profesor (Asignar)
                                                </button>
                                            @endif
                                        </span>
                                    </td>
                                    <td><span class="badge bg-light text-dark border border-light-subtle fw-bold px-3 py-2 rounded-pill">{{ $g->periodo }}</span></td>
                                    <td class="text-center"><span class="small text-white-50"><i class="bi bi-clock me-1 text-success"></i>{{ $g->horario ?: 'Por definir' }}</span></td>
                                    <td class="text-center">
                                        <span class="badge {{ ($g->total_estudiantes ?? 0) > 0 ? 'bg-success text-white' : 'bg-secondary bg-opacity-25 text-white-50' }} rounded-pill px-3 py-1.5 fw-bold">
                                            <i class="bi bi-people-fill me-1"></i>{{ $g->total_estudiantes ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold">{{ $g->modalidad }}</span></td>
                                    <td class="text-center">
                                        @if ($g->id_moodle)
                                            <i class="bi bi-cloud-check-fill text-success fs-5" title="ID: {{ $g->id_moodle }}"></i>
                                        @else
                                            <i class="bi bi-cloud-slash text-white-50 opacity-25"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($g->enlace_zoom)
                                            <a href="{{ $g->enlace_zoom }}" target="_blank" class="text-info"><i class="bi bi-camera-video-fill fs-5"></i></a>
                                        @else
                                            <i class="bi bi-dash-circle text-white-50 opacity-25"></i>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" 
                                                    onclick='editarGrupoEnFormulario(@json($g))' 
                                                    class="action-btn-custom text-info" 
                                                    title="Editar Grupo en el Formulario">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" onclick="asignarProfesorGrupo({{ $g->id_curso_activo }}, '{{ $g->id_profesor ?? '' }}')" class="action-btn-custom" title="Asignar / Cambiar Profesor"><i class="bi bi-person-gear"></i></button>
                                            <button type="button" onclick="confirmarEliminarGrupo({{ $g->id_curso_activo }})" class="action-btn-custom action-btn-delete" title="Eliminar"><i class="bi bi-trash3-fill"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var materiasCargadas = {};

        function formatearFechaCorta(iso) {
            if (!iso) return '';
            var p = iso.split('-');
            return p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : iso;
        }

        function actPeriodo() { 
            var tipoPer = $('#cuatrimestre_select').val();
            var anio = $('#ano_select').val();
            var fIni = $('input[name="fecha_inicio"]').val();
            var fFin = $('input[name="fecha_final"]').val();

            if (tipoPer === 'Fecha Específica' && fIni) {
                if (fFin && fFin !== fIni) {
                    $('#periodo').val('Del ' + formatearFechaCorta(fIni) + ' al ' + formatearFechaCorta(fFin));
                } else {
                    $('#periodo').val('Fecha: ' + formatearFechaCorta(fIni));
                }
            } else {
                $('#periodo').val(tipoPer + ' ' + anio); 
            }
        }

        function parseSemanas(txt) {
            if (!txt) return null;
            var m = txt.match(/(\d+)\s*(?:semana|lecci|clase)/i);
            if (m) return parseInt(m[1], 10);
            if (/cuatrimestre/i.test(txt)) return 15;
            if (/bimestre/i.test(txt)) return 8;
            if (/mes/i.test(txt)) return 4;
            if (/semana/i.test(txt)) return 1;
            return null;
        }

        function calcularFechaFinal(fechaIni, semanas) {
            if (!fechaIni || !semanas || semanas <= 0) return null;
            var d = new Date(fechaIni + 'T00:00:00');
            if (isNaN(d.getTime())) return null;
            d.setDate(d.getDate() + (semanas * 7) - 1);
            var mes = ('0' + (d.getMonth() + 1)).slice(-2);
            var dia = ('0' + d.getDate()).slice(-2);
            return d.getFullYear() + '-' + mes + '-' + dia;
        }

        function autoAjustarFechaFinal() {
            var fIni = $('input[name="fecha_inicio"]').val();
            var numLecc = parseInt($('#num_lecciones').val(), 10);
            if (!fIni) return;

            if (!numLecc || isNaN(numLecc) || numLecc <= 0) {
                var planId = $('#id_plan_select').val();
                if (planId && materiasCargadas[planId]) {
                    var mat = materiasCargadas[planId];
                    numLecc = parseSemanas(mat.duracion) || parseSemanas(mat.cuatrimestre);
                }
                if (!numLecc) {
                    var tipoPer = $('#cuatrimestre_select').val();
                    numLecc = parseSemanas(tipoPer) || 8;
                }
                $('#num_lecciones').val(numLecc);
            }

            var fFinCalculada = calcularFechaFinal(fIni, numLecc);
            if (fFinCalculada) {
                $('input[name="fecha_final"]').val(fFinCalculada);
                actPeriodo();
            }
        }

        function cargarMateriasPrograma(idPrograma, idPlanSeleccionar, callback) {
            var select = $('#id_plan_select');
            materiasCargadas = {};
            if (idPrograma) {
                select.empty().append('<option value="">Cargando materias...</option>').prop('disabled', true);
                var url = "{{ route('grupos.cursos_programa_ajax', ':id') }}".replace(':id', idPrograma);
                $.getJSON(url, function(data) {
                    select.empty().append('<option value="">Seleccione Materia...</option>');

                    var lista = [];
                    if (Array.isArray(data)) {
                        lista = data;
                    } else if (data && data.cursos && typeof data.cursos === 'object') {
                        $.each(data.cursos, function(periodo, items) {
                            if (Array.isArray(items)) {
                                lista = lista.concat(items);
                            }
                        });
                    }

                    if (lista.length === 0) {
                        select.append('<option value="">No hay materias registradas para este programa</option>');
                    } else {
                        $.each(lista, function(i, m) {
                            materiasCargadas[m.id_plan] = m;
                            var cod = m.codigo ? (m.codigo + ' - ') : '';
                            var nom = m.materia || 'Materia sin nombre';
                            var per = m.cuatrimestre ? (' (' + m.cuatrimestre + ')') : '';
                            var dur = m.duracion ? (' [' + m.duracion + ']') : '';
                            select.append('<option value="' + m.id_plan + '">' + cod + nom + per + dur + '</option>');
                        });
                    }
                    select.prop('disabled', false);

                    if (idPlanSeleccionar) {
                        select.val(idPlanSeleccionar);
                    }
                    if (typeof callback === 'function') {
                        callback();
                    }
                }).fail(function() {
                    select.empty().append('<option value="">Error al cargar materias del programa</option>').prop('disabled', true);
                });
            } else {
                select.empty().append('<option value="">Seleccione un programa primero...</option>').prop('disabled', true);
            }
        }

        function desglosarPeriodo(periodoStr) {
            if (!periodoStr) return { tipo: 'I Cuatrimestre', anio: '2026' };
            periodoStr = periodoStr.trim();
            if (periodoStr.indexOf('Del ') === 0 || periodoStr.indexOf('Fecha: ') === 0) {
                return { tipo: 'Fecha Específica', anio: '2026' };
            }
            var tipos = [
                'I Cuatrimestre', 'II Cuatrimestre', 'III Cuatrimestre',
                'I Bimestre', 'II Bimestre', 'III Bimestre', 'IV Bimestre', 'V Bimestre', 'VI Bimestre',
                'Semana', 'Seminario', 'Taller', 'Mes', 'Fecha Específica'
            ];
            for (var i = 0; i < tipos.length; i++) {
                if (periodoStr.indexOf(tipos[i]) === 0) {
                    var anioMatch = periodoStr.match(/\b(20\d{2})\b/);
                    return {
                        tipo: tipos[i],
                        anio: anioMatch ? anioMatch[1] : '2026'
                    };
                }
            }
            return { tipo: 'I Cuatrimestre', anio: '2026' };
        }

        $(document).ready(function() {
            $('#cuatrimestre_select, #ano_select, input[name="fecha_inicio"], input[name="fecha_final"]').on('change input', actPeriodo); 
            actPeriodo();

            $('input[name="fecha_inicio"], #num_lecciones').on('change input', autoAjustarFechaFinal);

            $('#id_programa_select').change(function() {
                var id = $(this).val();
                cargarMateriasPrograma(id);
            });

            $('#id_plan_select').change(function() {
                var planId = $(this).val();
                if (planId && materiasCargadas[planId]) {
                    var mat = materiasCargadas[planId];
                    if (mat.modalidad) {
                        $('select[name="modalidad"]').val(mat.modalidad);
                    }
                    var lecc = parseSemanas(mat.duracion) || parseSemanas(mat.cuatrimestre);
                    if (lecc) {
                        $('#num_lecciones').val(lecc);
                    }
                    autoAjustarFechaFinal();
                }
            });

            $('#cuatrimestre_select').change(function() {
                var tipoPer = $(this).val();
                var lecc = parseSemanas(tipoPer);
                if (lecc) {
                    $('#num_lecciones').val(lecc);
                }
                autoAjustarFechaFinal();
            });
        });

        var opcionesProfesores = {
            '': '— Sin Profesor Asignado —',
            @foreach ($profesores as $prof)
                '{{ $prof->id }}': @json($prof->apellidos . ', ' . $prof->nombre),
            @endforeach
        };

        function parseHorarioCadena(cadena) {
            if (!cadena) return { dia: 'Lunes', inicio: '', fin: '' };
            cadena = cadena.trim();
            var partes = cadena.split(' ');
            var diasValidos = [
                'Lunes', 'Martes', 'Miércoles', 'Miercoles', 'Jueves', 'Viernes', 'Sábado', 'Sabado', 'Domingo',
                'Lunes y Miércoles', 'Martes y Jueves', 'Lunes a Viernes', 'Sábado y Domingo',
                'Asincrónico', 'Por definir'
            ];
            var diaEncontrado = 'Lunes';
            for (var i = 0; i < diasValidos.length; i++) {
                if (cadena.indexOf(diasValidos[i]) === 0) {
                    diaEncontrado = diasValidos[i];
                    break;
                }
            }
            var resto = cadena.substring(diaEncontrado.length).trim();
            var m = resto.match(/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/);
            return {
                dia: diaEncontrado,
                inicio: m ? m[1] : '',
                fin: m ? m[2] : ''
            };
        }

        function editarGrupoEnFormulario(g) {
            var urlUpdate = "{{ route('grupos.update', ':id') }}".replace(':id', g.id_curso_activo);
            $('#grupo-form').attr('action', urlUpdate);

            // Ajustar interfaz para modo edición
            $('#form-icono').removeClass('bi-plus-circle-fill text-success').addClass('bi-pencil-square text-info');
            var nomCurso = (g.plan_estudio && g.plan_estudio.materia) ? (' - ' + g.plan_estudio.materia) : '';
            $('#form-titulo-texto').text('Editar Grupo de Estudio: #' + g.id_curso_activo + nomCurso);
            $('#btn-submit-icono').removeClass('bi-check-circle-fill').addClass('bi-save');
            $('#btn-submit-texto').text('Guardar Cambios del Grupo');
            $('#btn-cancelar-edicion').show();

            $('#card-formulario-grupo').css({
                'border-color': 'var(--primary)',
                'box-shadow': '0 0 25px rgba(95, 178, 48, 0.25)'
            });

            // Asignar campos del formulario
            var idProg = (g.plan_estudio && g.plan_estudio.id_programa) ? g.plan_estudio.id_programa : '';
            $('#id_programa_select').val(idProg);
            cargarMateriasPrograma(idProg, g.id_plan);

            $('#nombre_grupo').val(g.nombre_grupo || '');

            var pInfo = desglosarPeriodo(g.periodo);
            $('#cuatrimestre_select').val(pInfo.tipo);
            if (pInfo.anio) {
                $('#ano_select').val(pInfo.anio);
            }
            $('#periodo').val(g.periodo || '');

            $('#id_moodle_input').val(g.id_moodle || '');
            $('#enlace_zoom_input').val(g.enlace_zoom || '');
            $('#titulo_prefijo_input').val(g.titulo_prefijo || '');
            $('#titulo_sufijo_input').val(g.titulo_sufijo || '');
            $('#modalidad_select').val(g.modalidad || 'Virtual');
            $('#tipo_curso_select').val(g.tipo_curso || 'Regular');
            $('#id_profesor_select').val(g.id_profesor || '');

            var hInfo = parseHorarioCadena(g.horario || '');
            $('#horario_dia_select').val(hInfo.dia);
            $('#horario_inicio_input').val(hInfo.inicio);
            $('#horario_fin_input').val(hInfo.fin);

            var fIni = g.fecha_inicio ? g.fecha_inicio.substring(0, 10) : '';
            var fFin = g.fecha_final ? g.fecha_final.substring(0, 10) : '';
            $('#fecha_inicio_input').val(fIni);
            $('#fecha_final_input').val(fFin);

            if (fIni && fFin) {
                var d1 = new Date(fIni + 'T00:00:00');
                var d2 = new Date(fFin + 'T00:00:00');
                var diffDias = Math.round((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
                var leccEst = Math.max(1, Math.round(diffDias / 7));
                $('#num_lecciones').val(leccEst);
            } else {
                $('#num_lecciones').val(8);
            }

            // Desplazar suavemente hacia el formulario
            $('html, body').animate({
                scrollTop: $('#card-formulario-grupo').offset().top - 80
            }, 350);
        }

        function cancelarEdicionGrupo() {
            $('#grupo-form').attr('action', "{{ route('grupos.store') }}");
            $('#form-icono').removeClass('bi-pencil-square text-info').addClass('bi-plus-circle-fill text-success');
            $('#form-titulo-texto').text('Configurar Nuevo Grupo');
            $('#btn-submit-icono').removeClass('bi-save').addClass('bi-check-circle-fill');
            $('#btn-submit-texto').text('Crear Nuevo Grupo de Estudio');
            $('#btn-cancelar-edicion').hide();
            $('#card-formulario-grupo').css({
                'border-color': '',
                'box-shadow': ''
            });

            $('#grupo-form')[0].reset();
            $('#id_plan_select').empty().append('<option value="">Seleccione un programa primero...</option>').prop('disabled', true);
            $('#cuatrimestre_select').val('I Cuatrimestre');
            $('#ano_select').val('2026');
            $('#num_lecciones').val('8');
            $('#modalidad_select').val('Virtual');
            $('#tipo_curso_select').val('Regular');
            $('#horario_dia_select').val('Lunes');
            actPeriodo();
        }

        function asignarProfesorGrupo(idCursoActivo, idProfesorActual) {
            Swal.fire({
                title: 'Asignar / Cambiar Profesor',
                text: 'Seleccione el docente encargado para este grupo de estudio:',
                input: 'select',
                inputOptions: opcionesProfesores,
                inputValue: idProfesorActual || '',
                showCancelButton: true,
                confirmButtonColor: '#5fb230',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Guardar Profesor',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('<form method="POST" action="{{ route('grupos.asignar_profesor') }}"></form>');
                    form.append('@csrf');
                    form.append('<input type="hidden" name="id_curso_activo" value="' + idCursoActivo + '">');
                    form.append('<input type="hidden" name="id_profesor" value="' + (result.value || '') + '">');
                    $('body').append(form);
                    form.submit();
                }
            });
        }

        function confirmarEliminarGrupo(id) {
            Swal.fire({
                title: '¿Eliminar grupo?',
                text: "Esta acción eliminará el grupo académico. Ingrese la clave maestra para confirmar:",
                icon: 'warning',
                input: 'password',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value) {
                        var form = $('<form method="POST" action="{{ route('grupos.destroy') }}"></form>');
                        form.append('@csrf');
                        form.append('<input type="hidden" name="id" value="' + id + '">');
                        form.append('<input type="hidden" name="clave" value="' + result.value + '">');
                        $('body').append(form);
                        form.submit();
                    } else {
                        Swal.fire('Error', 'Debe ingresar la clave maestra de confirmación.', 'error');
                    }
                }
            });
        }
    </script>
@endsection
