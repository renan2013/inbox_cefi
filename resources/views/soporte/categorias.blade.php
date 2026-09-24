@extends('layouts.app')

@section('title', 'Inbox BPM - Categorías de Soporte')

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
                <li class="breadcrumb-item"><a href="{{ route('soporte.gestionar') }}" class="text-decoration-none text-white-50">Gestionar Soporte</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Categorías</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Categorías de Averías</h1>
                <p class="text-white-50 mb-0">Gestione los grupos de clasificación de incidencias y averías del sistema.</p>
            </div>
            <div>
                <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#crearCatModal" style="background-color: var(--primary); border: none;">
                    <i class="bi bi-plus-lg me-1"></i> Nueva Categoría
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

        <!-- CATEGORIES TABLE -->
        <div class="card glass-card">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr class="bg-dark text-white-50">
                            <th class="ps-4">No. Categoría</th>
                            <th>Nombre</th>
                            <th>Total Averías Registradas</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categorias as $c)
                            <tr>
                                <td class="ps-4 text-white font-monospace fw-bold">#{{ str_pad($c->id, 3, '0', STR_PAD_LEFT) }}</td>
                                <td class="fw-bold text-white">{{ $c->nombre }}</td>
                                <td>
                                    <span class="badge bg-light text-dark fw-bold px-3 py-1.5 rounded-pill">{{ $c->soportes_count }} averías</span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="abrirEditarModal({{ json_encode($c) }})"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="confirmarEliminarCat({{ $c->id }})"><i class="bi bi-trash3"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL CREAR -->
    <div class="modal fade" id="crearCatModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-lg text-success me-2"></i> Crear Categoría</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('soporte.categorias.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label-custom">Nombre de la Categoría</label>
                            <input type="text" name="nombre" class="form-control form-control-custom w-100" placeholder="Ej: Redes e Internet" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Añadir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR -->
    <div class="modal fade" id="editarCatModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-success me-2"></i> Editar Categoría</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editarCatForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label-custom">Nombre de la Categoría</label>
                            <input type="text" name="nombre" id="edit_cat_nombre" class="form-control form-control-custom w-100" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL ELIMINAR CON CLAVE MAESTRA -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteCatForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="text-white-50 small mb-4">Esta acción puede afectar a los reportes asignados a esta categoría. Introduzca la clave maestra del sistema para proceder.</p>
                        <div>
                            <label class="form-label-custom">Clave Maestra de Confirmación</label>
                            <input type="password" name="clave" class="form-control form-control-custom w-100" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4">Eliminar Categoría</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function abrirEditarModal(c) {
            $('#edit_cat_nombre').val(c.nombre);
            const actionUrl = "{{ route('soporte.categorias.update', ':id') }}".replace(':id', c.id);
            $('#editarCatForm').attr('action', actionUrl);
            $('#editarCatModal').modal('show');
        }

        function confirmarEliminarCat(id) {
            const actionUrl = "{{ route('soporte.categorias.delete', ':id') }}".replace(':id', id);
            $('#deleteCatForm').attr('action', actionUrl);
            $('#deleteConfirmModal').modal('show');
        }
    </script>
@endsection
