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
            background-color: rgba(15, 23, 42, 0.5);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.7rem 1rem;
            transition: all 0.3s;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            background-color: rgba(15, 23, 42, 0.7);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15);
            color: #f8fafc;
            outline: none;
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

        <!-- FORMULARIO DE CREACIÓN -->
        <div class="card glass-card">
            <div class="card-body p-4 p-md-5">
                <h5 class="fw-bold mb-4 text-white"><i class="bi bi-plus-circle-fill text-success me-2"></i> Configurar Nuevo Grupo</h5>
                
                <form action="{{ route('grupos.store') }}" method="POST" id="grupo-form">
                    @csrf
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-custom">Carrera / Programa Académico</label>
                            <select id="id_programa_select" class="form-select form-select-custom w-100" required>
                                <option value="">Seleccione el Programa...</option>
                                @foreach ($programas as $p)
                                    <option value="{{ $p->id_programa }}">{{ $p->nombre_programa }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Materia / Curso Específico</label>
                            <select name="id_plan" id="id_plan_select" class="form-select form-select-custom w-100" required disabled>
                                <option value="">Seleccione un programa primero...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <label class="form-label-custom">Cuatrimestre</label>
                            <select id="cuatrimestre_select" class="form-select form-select-custom w-100">
                                <option value="I Cuatrimestre">I Cuatrimestre (Ene-Abr)</option>
                                <option value="II Cuatrimestre">II Cuatrimestre (May-Ago)</option>
                                <option value="III Cuatrimestre">III Cuatrimestre (Sep-Dic)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Año Lectivo</label>
                            <select id="ano_select" class="form-select form-select-custom w-100">
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                            </select>
                            <input type="hidden" name="periodo" id="periodo">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label-custom text-success fw-bold"><i class="bi bi-cloud-download me-1"></i> ID Moodle</label>
                            <input type="number" name="id_moodle" class="form-control form-control-custom w-100" placeholder="Ej: 125">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-custom text-success fw-bold">Enlace Zoom (Sincrónico)</label>
                            <input type="url" name="enlace_zoom" class="form-control form-control-custom w-100" placeholder="https://zoom.us/j/...">
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <label class="form-label-custom">Prefijo Título</label>
                            <input type="text" name="titulo_prefijo" class="form-control form-control-custom w-100" placeholder="Ej: Lic.">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Sufijo Título</label>
                            <input type="text" name="titulo_sufijo" class="form-control form-control-custom w-100" placeholder="Ej: PhD.">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Modalidad</label>
                            <select name="modalidad" class="form-select form-select-custom w-100">
                                <option value="Virtual">Virtual</option>
                                <option value="Presencial">Presencial</option>
                                <option value="Semi Presencial">Semi Presencial</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Tipo Curso</label>
                            <select name="tipo_curso" class="form-select form-select-custom w-100">
                                <option value="Regular">Regular</option>
                                <option value="Tutoría">Tutoría</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-custom">Profesor Asignado</label>
                            <select name="id_profesor" class="form-select form-select-custom w-100" required>
                                <option value="">Seleccione Profesor...</option>
                                @foreach ($profesores as $prof)
                                    <option value="{{ $prof->id }}">{{ $prof->apellidos }}, {{ $prof->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Horario Sincrónico</label>
                            <div class="input-group">
                                <select name="horario_dia" class="form-select form-select-custom">
                                    <option value="Lunes">Lunes</option>
                                    <option value="Martes">Martes</option>
                                    <option value="Miércoles">Miércoles</option>
                                    <option value="Jueves">Jueves</option>
                                    <option value="Viernes">Viernes</option>
                                    <option value="Sábado">Sábado</option>
                                </select>
                                <input type="time" name="horario_inicio" class="form-control form-control-custom" required>
                                <input type="time" name="horario_fin" class="form-control form-control-custom" required>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label-custom">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Fecha Final</label>
                            <input type="date" name="fecha_final" class="form-control form-control-custom w-100" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-submit w-100 py-3 shadow">
                        <i class="bi bi-check-circle-fill me-2"></i> Crear Nuevo Grupo de Estudio
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
                            <th class="text-center">Modalidad</th>
                            <th class="text-center">ID Moodle</th>
                            <th class="text-center">Zoom</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($grupos as $progName => $listaCursos)
                            <tr>
                                <td colspan="7" class="program-group-header ps-4 py-3">
                                    <i class="bi bi-layers-half me-2"></i> {{ $progName }}
                                </td>
                            </tr>
                            @foreach ($listaCursos as $g)
                                <tr>
                                    <td class="ps-4">
                                        <span class="d-block small text-white-50 fw-bold">{{ $g->planEstudio->codigo ?? 'N/A' }}</span>
                                        <span class="fw-bold text-white">{{ $g->planEstudio->materia ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="small fw-semibold text-white-50">
                                            @if ($g->profesor)
                                                {{ $g->profesor->apellidos }}, {{ $g->profesor->nombre }}
                                            @else
                                                <span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Sin Profesor</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td><span class="badge bg-light text-dark border border-light-subtle fw-bold px-3 py-2 rounded-pill">{{ $g->periodo }}</span></td>
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
                                            <button onclick="confirmarEliminarGrupo({{ $g->id_curso_activo }})" class="action-btn-custom action-btn-delete" title="Eliminar"><i class="bi bi-trash3-fill"></i></button>
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
        $(document).ready(function() {
            // Generar período
            function actPeriodo() { 
                $('#periodo').val($('#cuatrimestre_select').val() + ' ' + $('#ano_select').val()); 
            }
            $('#cuatrimestre_select, #ano_select').change(actPeriodo); 
            actPeriodo();

            // Cargar materias del programa vía AJAX
            $('#id_programa_select').change(function() {
                var id = $(this).val();
                if (id) {
                    var url = "{{ route('boletas.cursos_programa_ajax', ':id') }}".replace(':id', id);
                    $.getJSON(url, function(data) {
                        var select = $('#id_plan_select').empty().append('<option value="">Seleccione Materia...</option>');
                        if (data.length === 0) {
                            select.append('<option value="">No hay materias registradas para este programa</option>');
                        } else {
                            $.each(data, function(i, m) { 
                                select.append('<option value="'+m.id_plan+'">'+m.codigo+' - '+m.materia+'</option>'); 
                            });
                        }
                        select.prop('disabled', false);
                    });
                } else {
                    $('#id_plan_select').empty().append('<option value="">Seleccione un programa primero...</option>').prop('disabled', true);
                }
            });
        });

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
