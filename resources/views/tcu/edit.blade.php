@extends('layouts.app')

@section('title', 'Inbox BPM - Bitácora de TCU')

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
            overflow: hidden;
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

        /* Dark themed transparent table list */
        .table-custom {
            margin-bottom: 0;
            background-color: transparent !important;
        }
        .table-custom td, .table-custom th {
            background-color: transparent !important;
            color: #e2e8f0 !important;
            border-bottom: 1px solid var(--border-dark);
            padding: 1rem 1.25rem;
        }

        .info-pill {
            background-color: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-dark);
            border-radius: 1rem;
            padding: 1.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tcu.index') }}" class="text-decoration-none text-white-50">Bitácoras TCU</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Editar Bitácora</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Registro de Actividades TCU</h1>
                <p class="text-white-50 mb-0">Complete la bitácora agregando las horas de servicio e informe final.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('tcu.imprimir', $bitacora->id) }}" target="_blank" class="btn btn-outline-danger rounded-pill px-4">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Imprimir PDF
                </a>
                @if (!$es_lectura)
                    <button type="button" id="btnFinalizar" class="btn btn-success rounded-pill px-4" onclick="finalizarBitacora()" style="background-color: var(--primary); border: none;">
                        <i class="bi bi-send-check me-1"></i> Entregar Bitácora
                    </button>
                @endif
                <a href="{{ route('tcu.index') }}" class="btn btn-outline-light rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Listado
                </a>
            </div>
        </div>

        @if ($es_admin)
            <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center" style="background-color: rgba(59, 130, 246, 0.1); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.2) !important; border-radius: 1rem;">
                <i class="bi bi-person-badge-fill fs-4 me-3"></i>
                <div>
                    <strong>Modo Administrador:</strong> Viendo reporte de <strong>{{ $bitacora->estudiante->nombre ?? 'Desconocido' }} {{ $bitacora->estudiante->apellidos ?? '' }}</strong>
                </div>
            </div>

            <!-- CONTROLES ADMIN -->
            <div class="card glass-card p-4 mb-4 bg-dark bg-opacity-20">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <span class="text-warning fw-bold"><i class="bi bi-shield-lock me-1"></i> Gestión y Aprobación de TCU</span>
                    <div class="d-flex gap-2">
                        @if ($bitacora->estado === 'finalizado')
                            <button class="btn btn-sm btn-success rounded-pill px-4" onclick="revisarBitacora()">
                                <i class="bi bi-check-lg me-1"></i> Marcar como Revisada
                            </button>
                        @endif
                        @if ($bitacora->estado !== 'borrador')
                            <button class="btn btn-sm btn-outline-warning rounded-pill px-4 text-white" onclick="reabrirBitacora()">
                                <i class="bi bi-unlock me-1"></i> Reabrir para Edición
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($bitacora->estado !== 'borrador' && !$es_admin)
            <div class="alert alert-success border-0 rounded-4 mb-4 d-flex align-items-center" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>Esta bitácora está en estado <strong>{{ strtoupper($bitacora->estado) }}</strong>. El registro está cerrado para edición.</div>
            </div>
        @endif

        <div class="row g-4 mb-4">
            <!-- CABECERA -->
            <div class="col-lg-5">
                <div class="card glass-card p-4 h-100 text-white">
                    <h5 class="fw-bold text-success mb-4 border-bottom border-secondary pb-2"><i class="bi bi-info-circle me-2"></i> Datos del Proyecto</h5>
                    
                    <form id="cabeceraForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label-custom">Nombre del Proyecto</label>
                            <input type="text" name="nombre_proyecto" class="form-control form-control-custom w-100" value="{{ $bitacora->nombre_proyecto }}" required {{ $es_lectura ? 'disabled' : '' }}>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Carrera del Estudiante</label>
                            <input type="text" name="carrera" class="form-control form-control-custom w-100" value="{{ $bitacora->carrera }}" required {{ $es_lectura ? 'disabled' : '' }}>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Lugar de Realización</label>
                            <input type="text" name="lugar_realizacion" class="form-control form-control-custom w-100" value="{{ $bitacora->lugar_realizacion }}" required {{ $es_lectura ? 'disabled' : '' }}>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Institución Beneficiada</label>
                            <input type="text" name="institucion_beneficiada" class="form-control form-control-custom w-100" value="{{ $bitacora->institucion_beneficiada }}" required {{ $es_lectura ? 'disabled' : '' }}>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label-custom">Supervisor</label>
                                <input type="text" name="nombre_supervisor" class="form-control form-control-custom w-100" value="{{ $bitacora->nombre_supervisor }}" required {{ $es_lectura ? 'disabled' : '' }}>
                            </div>
                            <div class="col-6">
                                <label class="form-label-custom">Cédula Supervisor</label>
                                <input type="text" name="cedula_supervisor" class="form-control form-control-custom w-100" value="{{ $bitacora->cedula_supervisor }}" required {{ $es_lectura ? 'disabled' : '' }}>
                            </div>
                        </div>
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="form-label-custom">Correo Supervisor</label>
                                <input type="email" name="email_institucion" class="form-control form-control-custom w-100" value="{{ $bitacora->email_institucion }}" required {{ $es_lectura ? 'disabled' : '' }}>
                            </div>
                            <div class="col-6">
                                <label class="form-label-custom">Teléfono Supervisor</label>
                                <input type="text" name="telefono_institucion" class="form-control form-control-custom w-100" value="{{ $bitacora->telefono_institucion }}" required {{ $es_lectura ? 'disabled' : '' }}>
                            </div>
                        </div>

                        @if (!$es_lectura)
                            <button type="submit" class="btn btn-outline-success w-100 rounded-pill py-2.5 fw-bold" style="border-color: var(--primary); color: var(--primary);">
                                <i class="bi bi-save me-1"></i> Guardar Cabecera
                            </button>
                        @endif
                    </form>
                </div>
            </div>

            <!-- REGISTRAR ACTIVIDADES (Si no es lectura) -->
            @if (!$es_lectura)
                <div class="col-lg-7">
                    <div class="card glass-card p-4 h-100 text-white">
                        <h5 class="fw-bold text-success mb-4 border-bottom border-secondary pb-2"><i class="bi bi-calendar-plus me-2"></i> Registrar Nueva Jornada</h5>
                        
                        <form id="actividadForm">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label-custom">Fecha de la Actividad</label>
                                    <input type="date" name="fecha" id="act_fecha" class="form-control form-control-custom w-100" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-custom">Hora de Entrada</label>
                                    <input type="time" name="hora_entrada" id="act_entrada" class="form-control form-control-custom w-100" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-custom">Hora de Salida</label>
                                    <input type="time" name="hora_salida" id="act_salida" class="form-control form-control-custom w-100" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label-custom">Actividades Realizadas</label>
                                    <textarea name="actividades" id="act_desc" class="form-control form-control-custom w-100" rows="4" placeholder="Describa el trabajo realizado detalladamente..." required></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">Horas Totales (Calculadas)</label>
                                    <input type="number" step="0.01" name="cantidad_horas" id="act_horas" class="form-control form-control-custom w-100 bg-dark" readonly value="0">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="submit" class="btn btn-success w-100 py-3 rounded-3 fw-bold shadow" style="background-color: var(--primary); border: none;">
                                        <i class="bi bi-plus-lg me-1"></i> Agregar a Bitácora
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <!-- INFO RESUMEN DE HORAS -->
                <div class="col-lg-7">
                    <div class="card glass-card p-5 h-100 text-center d-flex flex-column justify-content-center text-white">
                        <i class="bi bi-award text-success display-2 mb-3"></i>
                        <h4 class="fw-bold">Resumen de Trabajo Comunal</h4>
                        <div class="info-pill my-4 d-inline-block mx-auto" style="max-width: 300px;">
                            <span class="d-block text-white-50 text-uppercase small">Total de horas completadas</span>
                            <h2 class="display-5 fw-bold text-success mt-1 mb-0">{{ $total_horas }} hrs</h2>
                        </div>
                        <p class="text-white-50 mb-0">Esta bitácora ha sido cerrada de forma exitosa y está lista para su validación.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- LISTADO DE ACTIVIDADES -->
        <div class="card glass-card">
            <div class="card-header bg-transparent border-bottom border-secondary p-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-white mb-0"><i class="bi bi-journal-text text-success me-2"></i> Actividades Registradas</h5>
                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-bold" id="resumen_horas_badge">
                    Total: {{ $total_horas }} horas
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr class="bg-dark text-white-50">
                            <th class="ps-4">Fecha</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Cantidad de Horas</th>
                            <th>Actividades Detalladas</th>
                            @if (!$es_lectura)
                                <th class="text-end pe-4">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="actividadesTableBody">
                        @if (count($actividades) > 0)
                            @foreach ($actividades as $act)
                                <tr id="act-row-{{ $act->id }}">
                                    <td class="ps-4 fw-bold text-white">{{ $act->fecha->format('d/m/Y') }}</td>
                                    <td class="text-success">{{ Carbon\Carbon::parse($act->hora_entrada)->format('g:i a') }}</td>
                                    <td class="text-danger">{{ Carbon\Carbon::parse($act->hora_salida)->format('g:i a') }}</td>
                                    <td class="fw-bold text-white">{{ $act->cantidad_horas }} hrs</td>
                                    <td class="text-white-50 small" style="max-width: 400px; white-space: normal;">{{ $act->actividades }}</td>
                                    @if (!$es_lectura)
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="eliminarActividad({{ $act->id }})">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            <tr id="empty-row">
                                <td colspan="{{ !$es_lectura ? '6' : '5' }}" class="text-center py-5 text-white-50">
                                    No hay jornadas registradas para esta bitácora.
                                </td>
                            </tr>
                        @endif
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
            // Calcular horas automáticamente
            $('#act_entrada, #act_salida').on('change', function() {
                const entrada = $('#act_entrada').val();
                const salida = $('#act_salida').val();
                
                if (entrada && salida) {
                    const t1 = new Date("2000-01-01 " + entrada);
                    const t2 = new Date("2000-01-01 " + salida);
                    
                    let diffMs = t2 - t1;
                    if (diffMs < 0) { // Cruce de media noche
                        diffMs += 24 * 60 * 60 * 1000;
                    }
                    
                    const horas = diffMs / (1000 * 60 * 60);
                    $('#act_horas').val(horas.toFixed(2));
                }
            });

            // Guardar Cabecera
            $('#cabeceraForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('tcu.update', $bitacora->id) }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: response.message, showConfirmButton: false, timer: 3000 });
                        } else {
                            Swal.fire('Error', response.error, 'error');
                        }
                    }
                });
            });

            // Agregar Actividad
            $('#actividadForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('tcu.actividad.store', $bitacora->id) }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Actividad agregada', showConfirmButton: false, timer: 2000 });
                            location.reload(); // Recargar para actualizar los totales y la tabla
                        } else {
                            Swal.fire('Error', response.error, 'error');
                        }
                    }
                });
            });
        });

        // Eliminar Actividad
        function eliminarActividad(id) {
            Swal.fire({
                title: '¿Eliminar jornada?',
                text: 'Esta acción borrará las horas seleccionadas.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('tcu.actividad.delete', ':id') }}".replace(':id', id),
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if(response.success) {
                                $('#act-row-' + id).remove();
                                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Eliminado con éxito', showConfirmButton: false, timer: 2000 });
                                setTimeout(() => { location.reload(); }, 1000);
                            } else {
                                Swal.fire('Error', response.error, 'error');
                            }
                        }
                    });
                }
            });
        }

        // Finalizar Bitácora
        function finalizarBitacora() {
            Swal.fire({
                title: '¿Entregar y Cerrar Bitácora?',
                text: 'Una vez enviada, no podrá realizar más modificaciones en las jornadas.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, entregar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if(result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('tcu.finalizar', $bitacora->id) }}",
                        type: "POST",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(response) {
                            if(response.success) {
                                Swal.fire('Entregado', response.message, 'success').then(() => {
                                    window.location.href = "{{ route('tcu.index') }}";
                                });
                            } else {
                                Swal.fire('Error', response.error, 'error');
                            }
                        }
                    });
                }
            });
        }

        // Admin: Marcar como Revisada
        function revisarBitacora() {
            $.ajax({
                url: "{{ route('tcu.revisar', $bitacora->id) }}",
                type: "POST",
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if(response.success) {
                        Swal.fire('Revisado', response.message, 'success').then(() => { location.reload(); });
                    }
                }
            });
        }

        // Admin: Reabrir para Edición
        function reabrirBitacora() {
            $.ajax({
                url: "{{ route('tcu.reabrir', $bitacora->id) }}",
                type: "POST",
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if(response.success) {
                        Swal.fire('Reabierta', response.message, 'success').then(() => { location.reload(); });
                    }
                }
            });
        }
    </script>
@endsection
