@extends('layouts.app')

@section('title', 'Inbox BPM - Lista de Programas')

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

        .filter-section {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.25rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .table-container {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .table-custom {
            margin-bottom: 0;
            background-color: transparent !important;
        }

        .table-custom thead th {
            background-color: rgba(255, 255, 255, 0.02) !important;
            color: var(--text-muted) !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid var(--border-dark);
        }

        .table-custom tbody tr {
            border-bottom: 1px solid var(--border-dark);
            transition: all 0.2s;
        }

        .table-custom tbody tr:last-child {
            border-bottom: none;
        }

        .table-custom tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }

        .table-custom td {
            background-color: transparent !important;
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            color: #e2e8f0 !important;
        }

        .form-control-custom {
            background-color: rgba(15, 23, 42, 0.5);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.6rem 1rem;
            transition: all 0.3s;
        }

        .form-control-custom:focus {
            background-color: rgba(15, 23, 42, 0.7);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15);
            color: #f8fafc;
            outline: none;
        }

        .btn-search {
            background-color: var(--primary);
            border: none;
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-search:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(95, 178, 48, 0.25);
        }

        .btn-clear {
            background-color: transparent;
            border: 2px solid var(--border-dark);
            color: var(--text-muted);
            padding: 0.6rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-clear:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #f8fafc;
            border-color: #f8fafc;
        }

        .badge-count {
            background-color: rgba(95, 178, 48, 0.15);
            color: var(--primary);
            font-weight: 700;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            border: 1px solid rgba(95, 178, 48, 0.3);
            font-size: 0.8rem;
        }

        /* Pagination design override */
        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            background-color: rgba(30, 41, 59, 0.5);
            border-color: var(--border-dark);
            color: var(--text-muted);
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }

        .page-link:hover {
            background-color: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .prog-title {
            color: #f8fafc;
            font-size: 1.05rem;
        }

        /* Soporte y optimizaciones para Modo Día (Light Theme) */
        [data-theme="light"] .page-header {
            background: #ffffff !important;
            border-color: #cbd5e1 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05) !important;
        }

        [data-theme="light"] .page-header h1 {
            color: #0f172a !important;
        }

        [data-theme="light"] .page-header p {
            color: #64748b !important;
        }

        [data-theme="light"] .filter-section,
        [data-theme="light"] .table-container {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05) !important;
        }

        [data-theme="light"] .form-label {
            color: #334155 !important;
        }

        [data-theme="light"] .input-group-text-custom {
            border-color: #cbd5e1 !important;
            color: #64748b !important;
            background-color: #f8fafc !important;
        }

        [data-theme="light"] .form-control-custom {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        [data-theme="light"] .form-control-custom:focus {
            background-color: #ffffff !important;
            border-color: var(--primary) !important;
            color: #0f172a !important;
        }

        [data-theme="light"] .form-control-custom::placeholder {
            color: #94a3b8 !important;
        }

        [data-theme="light"] .btn-clear {
            border-color: #cbd5e1;
            color: #64748b;
        }

        [data-theme="light"] .btn-clear:hover {
            background-color: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }

        [data-theme="light"] .table-custom thead th {
            background-color: #f8fafc !important;
            color: #475569 !important;
            border-bottom: 2px solid #e2e8f0 !important;
        }

        [data-theme="light"] .table-custom tbody tr {
            border-bottom: 1px solid #f1f5f9 !important;
        }

        [data-theme="light"] .table-custom tbody tr:hover {
            background-color: rgba(95, 178, 48, 0.04) !important;
        }

        [data-theme="light"] .table-custom td {
            color: #1e293b !important;
        }

        [data-theme="light"] .prog-title {
            color: #0f172a !important;
        }

        [data-theme="light"] .badge-count {
            background-color: rgba(95, 178, 48, 0.12) !important;
            color: #15803d !important;
            border-color: rgba(95, 178, 48, 0.3) !important;
        }

        [data-theme="light"] .page-link {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #475569 !important;
        }

        [data-theme="light"] .page-link:hover {
            background-color: var(--primary) !important;
            color: #ffffff !important;
            border-color: var(--primary) !important;
        }

        [data-theme="light"] .page-item.active .page-link {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
            color: #ffffff !important;
        }

        [data-theme="light"] .pagination-footer {
            border-color: #e2e8f0 !important;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Lista de Programas</h1>
                <p class="text-white-50 mb-0">Gestiona y consulta los programas académicos, bachilleratos, licenciaturas y maestrías de la universidad.</p>
            </div>
            <div>
                <a href="{{ route('programas.create') }}" class="btn btn-search">
                    <i class="bi bi-plus-circle me-1"></i> Registrar Nuevo Programa
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3) !important; color: #4ade80;">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3) !important; color: #f87171;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filter Form -->
        <div class="filter-section">
            <form action="{{ route('programas.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label for="search" class="form-label fw-semibold mb-2">Buscar Programa o Categoría</label>
                    <div class="input-group">
                        <span class="input-group-text input-group-text-custom bg-transparent border-end-0 border-2" style="border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" id="search" class="form-control form-control-custom border-start-0 ps-0" placeholder="Nombre de programa o categoría..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-search flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('programas.index') }}" class="btn btn-clear flex-grow-1 text-center">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Nombre del Programa</th>
                            <th>Categoría</th>
                            <th>Total Materias</th>
                            <th>Costos base</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($programas as $prog)
                            <tr>
                                <td>
                                    <div class="fw-bold prog-title">{{ $prog->nombre_programa }}</div>
                                    <small class="text-white-50">ID Programa: {{ $prog->id_programa }}</small>
                                </td>
                                <td>
                                    <span class="text-white-50">{{ $prog->categoria ?? 'N/D' }}</span>
                                </td>
                                <td>
                                    <span class="badge-count">{{ $prog->planes_estudio_count }} materias</span>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem;">
                                        Materia: <strong class="text-success">₡{{ number_format($prog->costo_materia, 2) }}</strong><br>
                                        Matrícula: <span class="text-white-50">₡{{ number_format($prog->costo_matricula ?? 0, 2) }}</span>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('programas.edit', $prog->id_programa) }}" class="btn btn-sm btn-outline-success" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('programas.destroy', $prog->id_programa) }}" method="POST" class="d-inline form-eliminar-programa">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-programa" title="Eliminar" data-nombre="{{ $prog->nombre_programa }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-white-50">
                                    <i class="bi bi-journal-x display-4 d-block mb-3 text-muted"></i>
                                    No se encontraron programas con los criterios de búsqueda especificados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            @if ($programas->hasPages())
                <div class="d-flex justify-content-between align-items-center p-4 border-top pagination-footer" style="border-color: var(--border-dark) !important;">
                    <div class="text-white-50" style="font-size: 0.9rem;">
                        Mostrando registros del <strong>{{ $programas->firstItem() }}</strong> al <strong>{{ $programas->lastItem() }}</strong> de un total de <strong>{{ $programas->total() }}</strong>
                    </div>
                    <div>
                        {{ $programas->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.btn-delete-programa');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const form = this.closest('form');
            const nombre = this.getAttribute('data-nombre') || 'este programa';
            const isLight = (document.documentElement.getAttribute('data-theme') || localStorage.getItem('theme')) === 'light';

            Swal.fire({
                title: '¿Eliminar Programa?',
                html: `
                    <div class="text-start">
                        <div class="alert alert-warning py-2 px-3 mb-3 small" style="background: ${isLight ? '#fef3c7' : 'rgba(245, 158, 11, 0.15)'}; border: 1px solid ${isLight ? '#fde68a' : 'rgba(245, 158, 11, 0.3)'}; color: ${isLight ? '#92400e' : '#fbbf24'}; border-radius: 0.5rem;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Esta acción es <strong>irreversible</strong> y eliminará el programa <strong>"${nombre}"</strong>.
                        </div>
                        <label class="form-label small fw-bold mb-1" style="color: ${isLight ? '#0f172a' : '#f8fafc'};">
                            Ingrese clave de administrador para autorizar:
                        </label>
                        <input type="password" id="swal_admin_pwd" class="form-control text-center" placeholder="Clave de administrador" autocomplete="new-password" style="background-color: ${isLight ? '#ffffff' : '#0f172a'}; color: ${isLight ? '#0f172a' : '#f8fafc'}; border: 2px solid ${isLight ? '#cbd5e1' : '#334155'}; border-radius: 0.75rem; padding: 0.65rem 1rem; font-size: 1rem;">
                    </div>
                `,
                icon: 'warning',
                iconColor: '#f59e0b',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Sí, autorizar y eliminar',
                cancelButtonText: 'Cancelar',
                background: isLight ? '#ffffff' : '#1e293b',
                color: isLight ? '#0f172a' : '#f8fafc',
                didOpen: () => {
                    const input = document.getElementById('swal_admin_pwd');
                    if (input) {
                        input.focus();
                        input.addEventListener('keyup', (ev) => {
                            if (ev.key === 'Enter') {
                                Swal.clickConfirm();
                            }
                        });
                    }
                },
                preConfirm: () => {
                    const pwd = document.getElementById('swal_admin_pwd').value.trim();
                    if (!pwd) {
                        Swal.showValidationMessage('Debe ingresar la clave de administrador para confirmar');
                        return false;
                    }
                    return pwd;
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    let inputPwd = form.querySelector('input[name="admin_password"]');
                    if (!inputPwd) {
                        inputPwd = document.createElement('input');
                        inputPwd.type = 'hidden';
                        inputPwd.name = 'admin_password';
                        form.appendChild(inputPwd);
                    }
                    inputPwd.value = result.value;
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection
