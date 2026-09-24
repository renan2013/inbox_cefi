@extends('layouts.app')

@section('title', 'Inbox BPM - Historial de Asistencias')

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

        /* Modal styling */
        .modal-content-custom {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('planilla.index') }}" class="text-decoration-none text-white-50">Panel de Planilla</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Historial Asistencias</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Historial General de Asistencias</h1>
                <p class="text-white-50 mb-0">Verifique las marcas de entrada/salida y configure marcas manuales.</p>
            </div>
            <div>
                <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#manualMarcaModal" style="background-color: var(--primary); border: none;">
                    <i class="bi bi-plus-lg me-1"></i> Crear Marca Manual
                </button>
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

        <!-- FILTROS -->
        <div class="card glass-card">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('asistencia.historial') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label-custom">Empleado</label>
                        <select name="id_usuario" class="form-select form-select-custom w-100">
                            <option value="">Todos los empleados...</option>
                            @foreach ($empleados as $e)
                                <option value="{{ $e->id }}" {{ request('id_usuario') == $e->id ? 'selected' : '' }}>{{ $e->apellidos }}, {{ $e->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control form-control-custom w-100" value="{{ request('fecha_inicio') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control form-control-custom w-100" value="{{ request('fecha_fin') }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-success w-100 py-2.5 rounded-3 fw-bold" style="background-color: var(--primary); border: none;">Filtrar</button>
                        <a href="{{ route('asistencia.historial') }}" class="btn btn-outline-secondary w-100 py-2.5 rounded-3 fw-bold text-white">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- LISTADO -->
        <div class="card glass-card">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr class="bg-dark text-white-50">
                            <th class="ps-4">Empleado</th>
                            <th>Fecha</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th class="text-center">Horas</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($registros->count() > 0)
                            @foreach ($registros as $r)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-white d-block">
                                            @if ($r->usuario)
                                                {{ $r->usuario->apellidos }}, {{ $r->usuario->nombre }}
                                            @else
                                                <span class="text-danger">Usuario Eliminado</span>
                                            @endif
                                        </span>
                                        <span class="small text-white-50" style="font-size: 0.75rem;">{{ $r->usuario->email ?? '' }}</span>
                                    </td>
                                    <td>{{ $r->fecha->format('d/m/Y') }}</td>
                                    <td class="text-success fw-bold"><i class="bi bi-box-arrow-in-right me-1"></i> {{ Carbon\Carbon::parse($r->hora_entrada)->format('g:i a') }}</td>
                                    <td>
                                        @if ($r->hora_salida)
                                            <span class="text-danger fw-bold"><i class="bi bi-box-arrow-left me-1"></i> {{ Carbon\Carbon::parse($r->hora_salida)->format('g:i a') }}</span>
                                        @else
                                            <span class="text-white-50 small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($r->horas_trabajadas > 0)
                                            <span class="badge bg-light text-dark fw-bold px-3 py-1.5 rounded-pill">{{ $r->horas_trabajadas }} hrs</span>
                                        @else
                                            <span class="text-white-50 small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($r->estado === 'activo')
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1.5 fw-bold border border-success border-opacity-25">Activo</span>
                                        @else
                                            <span class="badge bg-secondary text-light rounded-pill px-3 py-1.5 fw-bold">Completado</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="action-btn-custom" onclick="abrirModalEdicion({{ json_encode($r) }})" title="Editar"><i class="bi bi-pencil-square"></i></button>
                                            <button onclick="confirmarEliminarMarca({{ $r->id }})" class="action-btn-custom action-btn-delete" title="Eliminar"><i class="bi bi-trash3-fill"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center py-5 text-white-50">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    No hay registros que coincidan con la búsqueda.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @if ($registros->hasPages())
                <div class="card-footer bg-transparent border-top border-secondary p-4">
                    {{ $registros->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- MODAL REGISTRO MANUAL -->
    <div class="modal fade" id="manualMarcaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-lg text-success me-2"></i> Crear Registro Manual</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('asistencia.manual.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label-custom">Empleado</label>
                            <select name="id_usuario" class="form-select form-select-custom w-100" required>
                                <option value="">Seleccione empleado...</option>
                                @foreach ($empleados as $e)
                                    <option value="{{ $e->id }}">{{ $e->apellidos }}, {{ $e->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Fecha de Jornada</label>
                            <input type="date" name="fecha" class="form-control form-control-custom w-100" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label-custom">Hora de Entrada</label>
                                <input type="time" name="hora_entrada" class="form-control form-control-custom w-100" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label-custom">Hora de Salida</label>
                                <input type="time" name="hora_salida" class="form-control form-control-custom w-100">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Registrar Marca</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDICION -->
    <div class="modal fade" id="editMarcaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-success me-2"></i> Editar Registro</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editMarcaForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label-custom">Empleado</label>
                            <input type="text" id="edit_empleado_name" class="form-control form-control-custom w-100" readonly style="opacity: 0.65;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Fecha de Jornada</label>
                            <input type="date" name="fecha" id="edit_fecha" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label-custom">Hora de Entrada</label>
                                <input type="time" name="hora_entrada" id="edit_hora_entrada" class="form-control form-control-custom w-100" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label-custom">Hora de Salida</label>
                                <input type="time" name="hora_salida" id="edit_hora_salida" class="form-control form-control-custom w-100">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Guardar Cambios</button>
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
        function abrirModalEdicion(record) {
            $('#edit_empleado_name').val(record.usuario.nombre + ' ' + record.usuario.apellidos);
            // Format fecha Y-m-d
            const fechaVal = record.fecha.split('T')[0];
            $('#edit_fecha').val(fechaVal);
            $('#edit_hora_entrada').val(record.hora_entrada);
            $('#edit_hora_salida').val(record.hora_salida);
            
            // Set form action
            const actionUrl = "{{ route('asistencia.manual.update', ':id') }}".replace(':id', record.id);
            $('#edit_marcaForm').attr('action', actionUrl);
            
            $('#editMarcaModal').modal('show');
        }

        function confirmarEliminarMarca(id) {
            Swal.fire({
                title: '¿Eliminar registro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('<form method="POST" action="' + "{{ route('asistencia.manual.delete', ':id') }}".replace(':id', id) + '"></form>');
                    form.append('@csrf');
                    $('body').append(form);
                    form.submit();
                }
            });
        }
    </script>
@endsection
