@extends('layouts.app')

@section('title', 'Inbox BPM - Gestionar Soporte')

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
            padding: 1.25rem 1.5rem;
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
                <li class="breadcrumb-item active text-white" aria-current="page">Gestionar Soporte</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Gestión de Soporte / Averías</h1>
                <p class="text-white-50 mb-0">Registre reportes de incidentes, averías técnicas y asigne soluciones.</p>
            </div>
            <div>
                <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#crearTicketModal" style="background-color: var(--primary); border: none;">
                    <i class="bi bi-plus-lg me-1"></i> Reportar Avería
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- FILTROS -->
        <div class="card glass-card">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('soporte.gestionar') }}" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label-custom">Categoría</label>
                        <select name="categoria_id" class="form-select form-select-custom w-100">
                            <option value="">Todas las categorías...</option>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">Estado del Reporte</label>
                        <select name="estado" class="form-select form-select-custom w-100">
                            <option value="">Todos los estados...</option>
                            <option value="reportado" {{ request('estado') === 'reportado' ? 'selected' : '' }}>Reportado / Activo</option>
                            <option value="solucionado" {{ request('estado') === 'solucionado' ? 'selected' : '' }}>Solucionado</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-success w-100 py-2.5 rounded-3 fw-bold" style="background-color: var(--primary); border: none;">Filtrar</button>
                        <a href="{{ route('soporte.gestionar') }}" class="btn btn-outline-secondary w-100 py-2.5 rounded-3 fw-bold text-white">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- TICKETS LIST -->
        <div class="card glass-card">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr class="bg-dark text-white-50">
                            <th class="ps-4">Ticket / Título</th>
                            <th>Categoría</th>
                            <th>Creado Por</th>
                            <th>Asignado A</th>
                            <th>Estado</th>
                            <th>Adjunto</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($soportes->count() > 0)
                            @foreach ($soportes as $ticket)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-white d-block">{{ $ticket->titulo }}</span>
                                        <span class="text-white-50 small" style="font-size: 0.75rem;">
                                            <i class="bi bi-calendar-event me-1"></i>{{ $ticket->fecha_creacion ? $ticket->fecha_creacion->format('d/m/Y g:i a') : '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold border border-success border-opacity-25">{{ $ticket->categoria->nombre ?? 'General' }}</span>
                                    </td>
                                    <td class="text-white-50 small">{{ $ticket->creador->nombre ?? '' }} {{ $ticket->creador->apellidos ?? '' }}</td>
                                    <td class="text-white fw-bold">{{ $ticket->reportadoA->nombre ?? 'Sin Asignar' }}</td>
                                    <td>
                                        @if ($ticket->estado === 'reportado')
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1 fw-bold border border-danger border-opacity-25 animate__animated animate__flash animate__infinite">Reportado</span>
                                        @else
                                            <span class="badge bg-secondary text-light rounded-pill px-3 py-1 fw-bold">Solucionado</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($ticket->adjunto)
                                            <a href="{{ asset('uploads/soporte/' . $ticket->adjunto) }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3"><i class="bi bi-file-earmark-arrow-down-fill"></i> Ver</a>
                                        @else
                                            <span class="text-white-50 small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <!-- Cambiar Estado Form -->
                                            <form action="{{ route('soporte.estado', $ticket->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="nuevo_estado" value="{{ $ticket->estado === 'reportado' ? 'solucionado' : 'reportado' }}">
                                                <button type="submit" class="btn btn-sm {{ $ticket->estado === 'reportado' ? 'btn-success' : 'btn-outline-danger' }} rounded-pill px-3">
                                                    {{ $ticket->estado === 'reportado' ? 'Resolver' : 'Reabrir' }}
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="abrirModalEdicion({{ json_encode($ticket) }})"><i class="bi bi-pencil-square"></i></button>
                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="confirmarEliminar({{ $ticket->id }})"><i class="bi bi-trash3"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center py-5 text-white-50">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    No hay reportes de soporte configurados actualmente.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @if ($soportes->hasPages())
                <div class="card-footer bg-transparent border-top border-secondary p-4">
                    {{ $soportes->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- MODAL CREAR TICKET -->
    <div class="modal fade" id="crearTicketModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-lg text-success me-2"></i> Reportar Nueva Avería</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('soporte.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4 row g-3">
                        <div class="col-md-12">
                            <label class="form-label-custom">Título Descriptivo</label>
                            <input type="text" name="titulo" class="form-control form-control-custom w-100" placeholder="Ej: Falla en proyector de aula 4" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Categoría de Soporte</label>
                            <select name="categoria_id" class="form-select form-select-custom w-100">
                                <option value="">Seleccione categoría...</option>
                                @foreach ($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Reportado / Asignado A</label>
                            <select name="reportado_a" class="form-select form-select-custom w-100">
                                <option value="">Sin Asignar...</option>
                                @foreach ($usuarios_soporte as $us)
                                    <option value="{{ $us->id }}">{{ $us->nombre }} {{ $us->apellidos }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label-custom">Descripción del Problema</label>
                            <textarea name="problema" class="form-control form-control-custom w-100" rows="3" placeholder="Detalle la falla aquí..."></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label-custom">Solución Propuesta o Aplicada</label>
                            <textarea name="solucion" class="form-control form-control-custom w-100" rows="3" placeholder="Si ya se solucionó, detalle qué se realizó..."></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label-custom">Archivo Adjunto (Imagen o Documento)</label>
                            <input type="file" name="adjunto" class="form-control form-control-custom w-100" accept="image/*,application/pdf">
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Crear Reporte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDICION TICKET -->
    <div class="modal fade" id="editarTicketModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-success me-2"></i> Editar Reporte de Avería</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTicketForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4 row g-3">
                        <div class="col-md-12">
                            <label class="form-label-custom">Título Descriptivo</label>
                            <input type="text" name="titulo" id="edit_titulo" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Categoría de Soporte</label>
                            <select name="categoria_id" id="edit_categoria_id" class="form-select form-select-custom w-100">
                                <option value="">Seleccione categoría...</option>
                                @foreach ($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Reportado / Asignado A</label>
                            <select name="reportado_a" id="edit_reportado_a" class="form-select form-select-custom w-100">
                                <option value="">Sin Asignar...</option>
                                @foreach ($usuarios_soporte as $us)
                                    <option value="{{ $us->id }}">{{ $us->nombre }} {{ $us->apellidos }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label-custom">Descripción del Problema</label>
                            <textarea name="problema" id="edit_problema" class="form-control form-control-custom w-100" rows="3"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label-custom">Solución Propuesta o Aplicada</label>
                            <textarea name="solucion" id="edit_solucion" class="form-control form-control-custom w-100" rows="3"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label-custom">Actualizar Archivo Adjunto</label>
                            <input type="file" name="adjunto" class="form-control form-control-custom w-100" accept="image/*,application/pdf">
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
        function abrirModalEdicion(ticket) {
            $('#edit_titulo').val(ticket.titulo);
            $('#edit_categoria_id').val(ticket.categoria_id || '');
            $('#edit_reportado_a').val(ticket.reportado_a || '');
            $('#edit_problema').val(ticket.problema || '');
            $('#edit_solucion').val(ticket.solucion || '');
            
            const actionUrl = "{{ route('soporte.update', ':id') }}".replace(':id', ticket.id);
            $('#editTicketForm').attr('action', actionUrl);
            
            $('#editarTicketModal').modal('show');
        }

        function confirmarEliminar(id) {
            Swal.fire({
                title: '¿Eliminar reporte?',
                text: 'Esta acción borrará permanentemente la avería registrada y su adjunto.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('<form method="POST" action="' + "{{ route('soporte.delete', ':id') }}".replace(':id', id) + '"></form>');
                    form.append('@csrf');
                    $('body').append(form);
                    form.submit();
                }
            });
        }
    </script>
@endsection
