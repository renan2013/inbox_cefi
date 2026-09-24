@extends('layouts.app')

@section('title', 'Inbox BPM - Generar Boleta de Pago')

@section('styles')
    <style>
        .page-header {
            background: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .form-section {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.25rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-control-custom {
            background-color: rgba(15, 23, 42, 0.5);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.7rem 1rem;
            transition: all 0.3s;
        }

        .form-control-custom:focus {
            background-color: rgba(15, 23, 42, 0.7);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15);
            color: #f8fafc;
            outline: none;
        }

        .result-item {
            background-color: rgba(255, 255, 255, 0.02);
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

        .course-card {
            background-color: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-dark);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
            transition: all 0.2s;
        }

        .course-card:hover {
            border-color: rgba(95, 178, 48, 0.3);
            background-color: rgba(95, 178, 48, 0.02);
        }

        .total-summary-panel {
            background-color: var(--card-dark);
            border: 2px solid var(--border-dark);
            border-radius: 1.25rem;
            padding: 2.5rem;
            position: sticky;
            top: 2rem;
        }

        .btn-submit {
            background-color: var(--primary);
            border: none;
            color: white;
            padding: 0.8rem;
            border-radius: 0.85rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(95, 178, 48, 0.3);
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Generar Boleta de Pago</h1>
                <p class="text-white-50 mb-0">Crea una nueva boleta de cobro por concepto de matrícula, aranceles y mensualidades de cursos.</p>
            </div>
            <div>
                <a href="{{ route('boletas.index') }}" class="btn btn-outline-light rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Historial
                </a>
            </div>
        </div>

        <form id="generarBoletaForm" method="POST" class="row g-4">
            @csrf
            
            <div class="col-lg-8">
                <!-- Step 1: Estudiante -->
                <div class="form-section shadow-sm">
                    <h4 class="fw-bold text-white mb-4"><i class="bi bi-person me-2 text-success"></i>1. Seleccionar Estudiante</h4>
                    
                    <div class="mb-3 position-relative">
                        <label for="buscador_estudiante" class="form-label text-white-50 fw-semibold mb-2">Buscar por Nombre, Email o Cédula</label>
                        <input type="text" id="buscador_estudiante" class="form-control form-control-custom" placeholder="Empiece a escribir para buscar..." value="{{ $student_name_auto }}">
                        <input type="hidden" name="id_estudiante" id="id_estudiante" value="{{ $id_estudiante_auto }}">
                        <div id="resultados_busqueda" class="list-group mt-2 position-absolute w-100 shadow" style="z-index: 1000; max-height: 200px; overflow-y: auto;"></div>
                    </div>

                    <div id="student_selected_info" class="p-3 bg-dark bg-opacity-30 rounded-3 border border-secondary" style="{{ $id_estudiante_auto ? '' : 'display: none;' }}">
                        <div class="text-white-50">Estudiante Seleccionado:</div>
                        <strong class="text-white fs-5" id="selected_student_name">{{ $student_name_auto }}</strong>
                        <div class="text-white-50 small" id="selected_student_email">{{ $student_email_auto }}</div>
                    </div>
                </div>

                <!-- Step 2: Programa y Período -->
                <div class="form-section shadow-sm">
                    <h4 class="fw-bold text-white mb-4"><i class="bi bi-mortarboard me-2 text-success"></i>2. Programa y Período</h4>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="id_programa" class="form-label text-white-50 fw-semibold mb-2">Programa Académico</label>
                            <select name="id_programa" id="id_programa" class="form-select form-control-custom">
                                <option value="">Seleccione un programa...</option>
                                @foreach ($programas_agrupados as $cat => $progs)
                                    <optgroup label="{{ $cat }}">
                                        @foreach ($progs as $prog)
                                            <option value="{{ $prog->id_programa }}" {{ $id_programa_auto == $prog->id_programa ? 'selected' : '' }}>
                                                {{ $prog->nombre_programa }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="periodo" class="form-label text-white-50 fw-semibold mb-2">Período Académico</label>
                            <select name="periodo" id="periodo" class="form-select form-control-custom">
                                <option value="I Cuatrimestre {{ date('Y') }}">I Cuatrimestre {{ date('Y') }}</option>
                                <option value="II Cuatrimestre {{ date('Y') }}">II Cuatrimestre {{ date('Y') }}</option>
                                <option value="III Cuatrimestre {{ date('Y') }}">III Cuatrimestre {{ date('Y') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Cursos a Matricular -->
                <div class="form-section shadow-sm">
                    <h4 class="fw-bold text-white mb-4"><i class="bi bi-book-half me-2 text-success"></i>3. Cursos del Plan de Estudios</h4>
                    <p class="text-white-50 small mb-4">Seleccione las materias que el estudiante llevará en este período.</p>
                    
                    <div id="contenedor_cursos_loader" class="text-center py-4 text-white-50" style="display: none;">
                        <div class="spinner-border text-success mb-2" role="status"></div>
                        <div>Cargando cursos del programa...</div>
                    </div>

                    <div id="contenedor_cursos">
                        <div class="text-center py-4 text-white-50 border border-dashed rounded-3">
                            Seleccione un programa para ver las materias disponibles.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Resumen y Opciones Financieras -->
            <div class="col-lg-4">
                <div class="total-summary-panel shadow-lg">
                    <h4 class="fw-bold text-white mb-4"><i class="bi bi-calculator me-2 text-success"></i>Resumen de Cobro</h4>
                    
                    <!-- Configuración Financiera -->
                    <div class="mb-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="cobrar_matricula" name="cobrar_matricula" checked>
                            <label class="form-check-label text-white-50" for="cobrar_matricula">Cobrar Matrícula del Período</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="aplicar_cargos_fijos" name="aplicar_cargos_fijos">
                            <label class="form-check-label text-white-50" for="aplicar_cargos_fijos">Aplicar Cargos Fijos (Biblioteca, etc)</label>
                        </div>
                    </div>

                    <hr class="border-secondary mb-4">

                    <!-- Totales -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50">Costo Materias:</span>
                        <strong class="text-white" id="resumen_materias">₡0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50">Costo Matrícula:</span>
                        <strong class="text-white" id="resumen_matricula">₡0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50">Cargos Fijos:</span>
                        <strong class="text-white" id="resumen_cargos">₡0.00</strong>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descuento" class="form-label text-white-50 small mb-1">Descuento Especial (₡)</label>
                        <input type="number" id="descuento" name="descuento" class="form-control form-control-custom" placeholder="Ej: 5000" value="0">
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 pt-3 border-top border-secondary">
                        <span class="fs-5 text-white">TOTAL A COBRAR:</span>
                        <strong class="fs-4 text-success" id="resumen_total">₡0.00</strong>
                    </div>

                    <!-- Cuotas -->
                    <div class="mb-4">
                        <label for="cuotas" class="form-label text-white-50 small mb-1">Dividir en Cuotas</label>
                        <select name="cuotas" id="cuotas" class="form-select form-control-custom">
                            <option value="1">Pago Único (De contado)</option>
                            <option value="2">2 Cuotas mensuales</option>
                            <option value="3">3 Cuotas mensuales</option>
                            <option value="4">4 Cuotas mensuales</option>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-submit">
                            <i class="bi bi-file-earmark-check-fill me-2"></i> Generar Boleta de Pago
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let costoMatriculaBase = 35000;
        let cargosFijosBase = 13000;

        $(document).ready(function() {
            // Buscador de Estudiante
            $('#buscador_estudiante').on('input', function() {
                let term = $(this).val();
                if (term.length < 2) {
                    $('#resultados_busqueda').empty().hide();
                    return;
                }

                $.getJSON("{{ route('boletas.buscar_estudiante_ajax') }}", { term: term }, function(data) {
                    let container = $('#resultados_busqueda').empty().show();
                    data.forEach(function(item) {
                        let btn = $(`<button type="button" class="list-group-item list-group-item-action result-item border-secondary p-3"></button>`);
                        btn.html(`<strong>${item.nombre} ${item.apellidos}</strong><br><small class="text-white-50">${item.email}</small>`);
                        btn.on('click', function() {
                            $('#id_estudiante').val(item.id);
                            $('#selected_student_name').text(item.nombre + ' ' + item.apellidos);
                            $('#selected_student_email').text(item.email);
                            $('#student_selected_info').show();
                            container.hide();
                            $('#buscador_estudiante').val(item.nombre + ' ' + item.apellidos);
                        });
                        container.append(btn);
                    });
                });
            });

            // Al elegir un Programa
            $('#id_programa').on('change', function() {
                let progId = $(this).val();
                if (!progId) {
                    $('#contenedor_cursos').html('<div class="text-center py-4 text-white-50 border border-dashed rounded-3">Seleccione un programa para ver las materias disponibles.</div>');
                    recalcularTotales();
                    return;
                }

                $('#contenedor_cursos_loader').show();
                $('#contenedor_cursos').hide();

                $.getJSON(`/boletas/cursos-programa/${progId}/ajax`, function(data) {
                    $('#contenedor_cursos_loader').hide();
                    let container = $('#contenedor_cursos').empty().show();
                    
                    if (Object.keys(data.cursos).length === 0) {
                        container.append('<div class="text-center py-4 text-white-50 border border-dashed rounded-3">No hay materias configuradas para este programa.</div>');
                        return;
                    }

                    // Renderizar agrupados por cuatrimestre
                    for (let cuatri in data.cursos) {
                        container.append(`<h5 class="text-white fw-bold mt-4 mb-3" style="color: var(--primary) !important;">${cuatri}</h5>`);
                        data.cursos[cuatri].forEach(function(c) {
                            container.append(`
                                <div class="course-card d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <input type="checkbox" name="cursos_ids[]" value="${c.id_plan}" data-precio="${c.precio}" class="form-check-input me-3 course-checkbox" style="width: 20px; height: 20px;">
                                        <div>
                                            <strong class="text-white">${c.codigo}</strong> - <span class="text-white-50">${c.materia}</span>
                                        </div>
                                    </div>
                                    <strong class="text-success">₡${parseFloat(c.precio).toLocaleString('es-CR', { minimumFractionDigits: 2 })}</strong>
                                </div>
                            `);
                        });
                    }

                    // Eventos checkboxes
                    $('.course-checkbox').on('change', function() {
                        recalcularTotales();
                    });
                });
            });

            // Cambios de interruptores financieros
            $('#cobrar_matricula, #aplicar_cargos_fijos, #descuento').on('change input', function() {
                recalcularTotales();
            });

            // Enviar formulario
            $('#generarBoletaForm').on('submit', function(e) {
                e.preventDefault();

                let idEstudiante = $('#id_estudiante').val();
                if (!idEstudiante) {
                    Swal.fire('Atención', 'Debe seleccionar un estudiante de la lista.', 'warning');
                    return;
                }

                let seleccionados = $('.course-checkbox:checked');
                if (seleccionados.length === 0) {
                    Swal.fire('Atención', 'Debe seleccionar al menos una materia para cobrar.', 'warning');
                    return;
                }

                // Armar payload
                let payload = {
                    _token: "{{ csrf_token() }}",
                    id_estudiante: idEstudiante,
                    periodo: $('#periodo').val(),
                    total: parseFloat($('#resumen_total').text().replace(/[^0-9.]/g, '')),
                    cuotas: parseInt($('#cuotas').val()),
                    descuento: parseFloat($('#descuento').val() || 0),
                    aplicar_cargos_fijos: $('#aplicar_cargos_fijos').is(':checked') ? 1 : 0,
                    cursos_ids: seleccionados.map(function() { return $(this).val(); }).get()
                };

                Swal.fire({
                    title: '¿Generar Boleta?',
                    text: 'Se procederá a registrar la boleta y programar las cuotas correspondientes.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#5fb230',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, Generar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Generando...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        $.post("{{ route('boletas.store') }}", payload, function(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Boleta Creada!',
                                    text: 'La boleta se generó correctamente en el sistema local.',
                                    confirmButtonText: 'Ver Historial'
                                }).then(() => {
                                    window.location.href = "{{ route('boletas.index') }}";
                                });
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        }).fail(function(xhr) {
                            Swal.fire('Error', 'Hubo un problema al procesar la solicitud.', 'error');
                        });
                    }
                });
            });
        });

        function recalcularTotales() {
            let totalMaterias = 0;
            $('.course-checkbox:checked').each(function() {
                totalMaterias += parseFloat($(this).data('precio'));
            });

            let cobrarMatricula = $('#cobrar_matricula').is(':checked');
            let cobrarCargos = $('#aplicar_cargos_fijos').is(':checked');

            let costoMatricula = cobrarMatricula ? costoMatriculaBase : 0;
            let costoCargos = cobrarCargos ? cargosFijosBase : 0;
            let descuento = parseFloat($('#descuento').val() || 0);

            let total = Math.max(0, (totalMaterias + costoMatricula + costoCargos) - descuento);

            $('#resumen_materias').text('₡' + totalMaterias.toLocaleString('es-CR', { minimumFractionDigits: 2 }));
            $('#resumen_matricula').text('₡' + costoMatricula.toLocaleString('es-CR', { minimumFractionDigits: 2 }));
            $('#resumen_cargos').text('₡' + costoCargos.toLocaleString('es-CR', { minimumFractionDigits: 2 }));
            $('#resumen_total').text('₡' + total.toLocaleString('es-CR', { minimumFractionDigits: 2 }));
        }
    </script>
@endsection
