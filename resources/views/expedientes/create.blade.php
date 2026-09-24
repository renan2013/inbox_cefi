@extends('layouts.app')

@section('title', 'Inbox BPM - Crear Expediente Digital')

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
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 2rem;
        }

        #user-search-card {
            overflow: visible !important;
        }

        .step-header {
            background-color: rgba(255, 255, 255, 0.02);
            border-bottom: 2px solid rgba(95, 178, 48, 0.15);
            padding: 1.25rem 1.5rem;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .step-badge {
            background-color: var(--primary);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            box-shadow: 0 4px 8px rgba(95, 178, 48, 0.2);
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

        .legend-custom {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.05rem;
            border-bottom: 2px solid var(--border-dark);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            width: 100%;
        }

        .btn-submit {
            background-color: var(--primary);
            border: none;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(95, 178, 48, 0.25);
        }

        #user-search-results {
            background-color: var(--card-dark) !important;
            border: 1px solid var(--border-dark);
        }

        .result-item {
            background-color: var(--card-dark) !important;
            border: 1px solid var(--border-dark);
            transition: all 0.2s;
            cursor: pointer;
            color: #e2e8f0;
        }

        .result-item:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
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
                <li class="breadcrumb-item active text-white" aria-current="page">Nuevo Expediente</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Crear Expediente Digital</h1>
                <p class="text-white-50 mb-0">Proceso de matriculación y registro de información académica del estudiante.</p>
            </div>
            <div>
                <a href="#" class="btn btn-outline-light rounded-pill px-4">
                    <i class="bi bi-folder2-open me-1"></i> Gestionar Expedientes
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- Sincronización Moodle Warning -->
        <div class="alert alert-warning border-0 rounded-4 d-flex align-items-start mb-4" style="background-color: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2) !important;">
            <i class="bi bi-info-circle-fill fs-4 me-3 mt-1"></i>
            <div>
                <strong>Nota Importante:</strong> Antes de crear el expediente digital, es necesario crear el usuario en <strong><a href="https://unela.ac.cr/virtual" target="_blank" style="color: inherit; text-decoration: underline;">Unela Virtual</a></strong>, ya que los usuarios de CEFI Virtual se sincronizan con Inbox.
            </div>
        </div>

        <!-- PASO 1: BUSCADOR -->
        <div class="glass-card" id="user-search-card">
            <div class="step-header">
                <div class="step-badge">1</div> Identificación del Estudiante
            </div>
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-end g-4">
                    <div class="col-md-7 position-relative">
                        <label for="user-search" class="form-label-custom"><i class="bi bi-search"></i> Buscar en el sistema:</label>
                        <div class="input-group">
                            <input type="text" id="user-search" class="form-control form-control-custom w-100" placeholder="Escriba nombre, email o cédula...">
                            <button id="reset-user-search" class="btn btn-outline-danger border-2 ms-2 rounded-3" type="button" style="display: none;">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                        <div id="user-search-results" class="list-group position-absolute w-100 shadow" style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;"></div>
                    </div>
                    <div class="col-md-5">
                        <div class="p-4 rounded-4" style="background-color: rgba(95, 178, 48, 0.03); border: 1px dashed rgba(95, 178, 48, 0.2);">
                            <label for="fecha_registro" class="form-label-custom"><i class="bi bi-calendar-check"></i> Fecha de Registro:</label>
                            <input type="date" name="fecha_registro" id="fecha_registro" form="expediente-form" class="form-control form-control-custom bg-transparent w-100">
                            <div class="form-text text-white-50 small mt-2"><i class="bi bi-info-circle me-1"></i> Por defecto se usará la fecha de hoy.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORMULARIO DETALLADO (OCULTO POR DEFECTO) -->
        <div id="expediente-data-section" class="glass-card" style="display: none;">
            <div class="step-header">
                <div class="step-badge">2</div> Datos del Expediente
            </div>
            <div class="card-body p-4 p-md-5">
                <h4 class="text-white fw-bold mb-4" id="data-form-header"></h4>
                
                <form id="expediente-form" method="post" action="{{ route('expedientes.store') }}">
                    @csrf
                    <input type="hidden" id="student_id" name="id_usuario" value="">

                    <!-- GRADO Y ESPECIALIDAD -->
                    <fieldset class="mb-5">
                        <div class="legend-custom">Grado e Interés Académico</div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="grado_a_matricular" class="form-label-custom">Grado a Matricular</label>
                                <select name="grado_a_matricular" id="grado_a_matricular" class="form-select form-select-custom w-100">
                                    <option value="Bachillerato">Bachillerato</option>
                                    <option value="Licenciatura">Licenciatura</option>
                                    <option value="Maestria">Maestría</option>
                                    <option value="Doctorado">Doctorado</option>
                                    <option value="Tecnico">Técnico</option>
                                    <option value="Curso Libre">Curso Libre</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="especialidad_deseada" class="form-label-custom">Especialidad o Carrera Deseada</label>
                                <select name="especialidad_deseada" id="especialidad_deseada" class="form-select form-select-custom w-100">
                                    @foreach ($programas as $prog)
                                        <option value="{{ $prog->nombre_programa }}">{{ $prog->nombre_programa }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <!-- DATOS PERSONALES -->
                    <fieldset class="mb-5">
                        <div class="legend-custom">Datos Personales</div>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label-custom">Género</label>
                                <select name="genero" class="form-select form-select-custom w-100">
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="No especificado">No especificado</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" class="form-control form-control-custom w-100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Estado Civil</label>
                                <select name="estado_civil" class="form-select form-select-custom w-100">
                                    <option value="Soltero(a)">Soltero(a)</option>
                                    <option value="Casado(a)">Casado(a)</option>
                                    <option value="Divorciado(a)">Divorciado(a)</option>
                                    <option value="Unión Libre">Unión Libre</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <!-- DOMICILIO -->
                    <fieldset class="mb-5">
                        <div class="legend-custom">Lugar de Domicilio y Dirección</div>
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label class="form-label-custom">Provincia</label>
                                <input type="text" name="domicilio_provincia" class="form-control form-control-custom w-100" placeholder="Ej: San José">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Cantón</label>
                                <input type="text" name="domicilio_canton" class="form-control form-control-custom w-100" placeholder="Ej: Escazú">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Distrito</label>
                                <input type="text" name="domicilio_distrito" class="form-control form-control-custom w-100" placeholder="Ej: San Rafael">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Dirección Exacta</label>
                            <textarea name="domicilio_direccion" class="form-control form-control-custom w-100" rows="3" placeholder="Dirección detallada..."></textarea>
                        </div>
                    </fieldset>

                    <!-- CONTACTO -->
                    <fieldset class="mb-5">
                        <div class="legend-custom">Información de Contacto</div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label-custom">Celular / WhatsApp</label>
                                <input type="text" name="contacto_tel_celular" class="form-control form-control-custom w-100" placeholder="Ej: 50688887777">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Contacto Emergencia</label>
                                <input type="text" name="contacto_otro_emergencias" class="form-control form-control-custom w-100" placeholder="Nombre y teléfono de familiar...">
                            </div>
                        </div>
                    </fieldset>

                    <!-- DOCUMENTACIÓN FLAG -->
                    <fieldset class="mb-5">
                        <div class="legend-custom">Documentación Entregada</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="registro_doc_cedula" name="registro_doc_cedula">
                                    <label class="form-check-label text-white-50" for="registro_doc_cedula">Copia Cédula</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="registro_doc_titulo_sec" name="registro_doc_titulo_sec">
                                    <label class="form-check-label text-white-50" for="registro_doc_titulo_sec">Título Secundaria</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="registro_doc_titulo_univ" name="registro_doc_titulo_univ">
                                    <label class="form-check-label text-white-50" for="registro_doc_titulo_univ">Título Universitario</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-submit py-3">
                            <i class="bi bi-folder-check me-2"></i> Registrar y Crear Expediente
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            let searchTimeout = null;

            // Búsqueda interactiva de estudiante
            $('#user-search').on('input', function() {
                let term = $(this).val();
                if (term.length < 3) {
                    $('#user-search-results').empty().hide();
                    return;
                }

                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    $.getJSON("{{ route('expedientes.buscar_usuario_ajax') }}", { query: term }, function(data) {
                        let container = $('#user-search-results').empty().show();
                        if (data.length === 0) {
                            container.append('<div class="list-group-item bg-dark text-white-50 border-secondary">No se encontraron estudiantes</div>');
                            return;
                        }
                        
                        data.forEach(function(user) {
                            let btn = $(`<button type="button" class="list-group-item list-group-item-action result-item border-secondary p-3"></button>`);
                            
                            let badge = '';
                            if (user.has_expediente) {
                                if (user.estado_expediente === 'Aprobado') {
                                    badge = `<span class="badge bg-success float-end mt-1"><i class="bi bi-check-circle-fill"></i> EXPEDIENTE EXISTENTE</span>`;
                                } else {
                                    badge = `<span class="badge bg-warning text-dark float-end mt-1 fw-bold"><i class="bi bi-hourglass-split"></i> SOLICITUD PENDIENTE</span>`;
                                }
                            }

                            btn.html(`
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${user.nombre} ${user.apellidos || ''}</strong><br>
                                        <small class="text-white-50">${user.email}</small>
                                    </div>
                                    ${badge}
                                </div>
                            `);

                            btn.on('click', function() {
                                if (user.has_expediente && user.estado_expediente === 'Aprobado') {
                                    Swal.fire('Información', 'Este estudiante ya cuenta con un expediente aprobado.', 'info');
                                    container.hide();
                                    return;
                                }
                                selectUser(user.id, user.nombre + ' ' + user.apellidos, user.email);
                                container.hide();
                            });

                            container.append(btn);
                        });
                    });
                }, 300);
            });

            function selectUser(userId, userName, userEmail) {
                $('#student_id').val(userId);
                $('#data-form-header').text(`Información para: ${userName}`);
                $('#expediente-data-section').fadeIn();
                $('#user-search').val(userName).prop('disabled', true);
                $('#reset-user-search').show();
            }

            $('#reset-user-search').on('click', function() {
                window.location.reload();
            });

            // Alerta de confirmación de envío
            $('#expediente-form').on('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Crear Expediente?',
                    text: 'Se procederá a guardar la información provista en el expediente digital.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#5fb230',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, registrar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
    </script>
@endsection
