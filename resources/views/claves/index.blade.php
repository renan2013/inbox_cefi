@extends('layouts.app')

@section('title', 'Inbox BPM - Gestión de Claves Institucionales')

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

        .key-badge {
            background-color: rgba(95, 178, 48, 0.1);
            color: var(--primary);
            border: 1px solid rgba(95, 178, 48, 0.2);
            font-weight: 700;
            border-radius: 0.5rem;
            padding: 0.2rem 0.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Bóveda de Claves</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1"><i class="bi bi-shield-lock-fill text-success me-2"></i> Gestión de Claves</h1>
                <p class="text-white-50 mb-0">Administración de contraseñas de portales institucionales de UNELA.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#crearClaveModal" style="background-color: var(--primary); border: none;">
                    <i class="bi bi-plus-lg me-1"></i> Nueva Credencial
                </button>
                <form action="{{ route('claves.salir') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                        <i class="bi bi-lock-fill me-1"></i> Cerrar Bóveda
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- CREDENTIALS LIST -->
        <div class="card glass-card">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr class="bg-dark text-white-50">
                            <th class="ps-4">Servicio / Plataforma</th>
                            <th>Descripción</th>
                            <th>Usuario</th>
                            <th>Contraseña</th>
                            <th>Enlace de Acceso</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($claves as $c)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-white d-block">{{ $c->nombre }}</span>
                                    <span class="text-white-50 small font-monospace" style="font-size: 0.7rem;">ID: #{{ $c->id }}</span>
                                </td>
                                <td class="text-white-50 small" style="max-width: 250px;">{{ $c->descripcion ?: '-' }}</td>
                                <td>
                                    <div class="input-group input-group-sm" style="max-width: 200px;">
                                        <input type="text" class="form-control bg-dark border-secondary text-white text-center rounded-start" value="{{ $c->usuario }}" readonly>
                                        <button class="btn btn-outline-success border-secondary" onclick="copiarTexto('{{ $c->usuario }}')" title="Copiar Usuario"><i class="bi bi-clipboard"></i></button>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm" style="max-width: 220px;">
                                        <input type="password" id="pass-{{ $c->id }}" class="form-control bg-dark border-secondary text-white text-center rounded-start" value="{{ $c->clave }}" readonly>
                                        <button class="btn btn-outline-light border-secondary" onclick="togglePassVisibility({{ $c->id }})" title="Revelar"><i class="bi bi-eye-fill" id="eye-{{ $c->id }}"></i></button>
                                        <button class="btn btn-outline-success border-secondary" onclick="copiarTexto('{{ $c->clave }}')" title="Copiar"><i class="bi bi-clipboard"></i></button>
                                    </div>
                                </td>
                                <td>
                                    @if ($c->link)
                                        <a href="{{ $c->link }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3"><i class="bi bi-box-arrow-up-right me-1"></i> Ir al Sitio</a>
                                    @else
                                        <span class="text-white-50 small">-</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="abrirEditarModal({{ json_encode($c) }})"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="confirmarEliminar({{ $c->id }})"><i class="bi bi-trash3"></i></button>
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
    <div class="modal fade" id="crearClaveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-lg text-success me-2"></i> Añadir Credencial</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('claves.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4 row g-3">
                        <div class="col-md-12">
                            <label class="form-label-custom">Nombre del Servicio / Plataforma</label>
                            <input type="text" name="nombre" class="form-control form-control-custom w-100" placeholder="Ej: Correo Office 365" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label-custom">Descripción / Notas</label>
                            <textarea name="descripcion" class="form-control form-control-custom w-100" rows="2" placeholder="Ej: Cuenta de TI administrativa..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Usuario / Email</label>
                            <input type="text" name="usuario" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Contraseña</label>
                            <input type="text" name="clave" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label-custom">Enlace del Sitio Web</label>
                            <input type="url" name="link" class="form-control form-control-custom w-100" placeholder="https://...">
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Guardar Credencial</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR -->
    <div class="modal fade" id="editarClaveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-success me-2"></i> Editar Credencial</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editClaveForm" method="POST">
                    @csrf
                    <div class="modal-body p-4 row g-3">
                        <div class="col-md-12">
                            <label class="form-label-custom">Nombre del Servicio / Plataforma</label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label-custom">Descripción / Notas</label>
                            <textarea name="descripcion" id="edit_descripcion" class="form-control form-control-custom w-100" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Usuario / Email</label>
                            <input type="text" name="usuario" id="edit_usuario" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Contraseña</label>
                            <input type="text" name="clave" id="edit_clave" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label-custom">Enlace del Sitio Web</label>
                            <input type="url" name="link" id="edit_link" class="form-control form-control-custom w-100">
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
        function togglePassVisibility(id) {
            const input = $('#pass-' + id);
            const eye = $('#eye-' + id);
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                eye.removeClass('bi-eye-fill').addClass('bi-eye-slash-fill');
            } else {
                input.attr('type', 'password');
                eye.removeClass('bi-eye-slash-fill').addClass('bi-eye-fill');
            }
        }

        function copiarTexto(texto) {
            navigator.clipboard.writeText(texto).then(() => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Copiado al portapapeles',
                    showConfirmButton: false,
                    timer: 2000
                });
            });
        }

        function abrirEditarModal(c) {
            $('#edit_nombre').val(c.nombre);
            $('#edit_descripcion').val(c.descripcion || '');
            $('#edit_usuario').val(c.usuario);
            $('#edit_clave').val(c.clave);
            $('#edit_link').val(c.link || '');
            
            const actionUrl = "{{ route('claves.update', ':id') }}".replace(':id', c.id);
            $('#editClaveForm').attr('action', actionUrl);
            
            $('#editarClaveModal').modal('show');
        }

        function confirmarEliminar(id) {
            Swal.fire({
                title: '¿Eliminar credencial?',
                text: 'Esta acción borrará definitivamente esta credencial de la base de datos.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('<form method="POST" action="' + "{{ route('claves.delete', ':id') }}".replace(':id', id) + '"></form>');
                    form.append('@csrf');
                    $('body').append(form);
                    form.submit();
                }
            });
        }
    </script>
@endsection
