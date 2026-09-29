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

        .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #fff;
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
                    <label for="search" class="form-label text-white-50 fw-semibold mb-2">Buscar Programa o Categoría</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 border-2" style="border-color: var(--border-dark); border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem; color: var(--text-muted);">
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
                                    <div class="fw-bold" style="color: #f8fafc; font-size: 1.05rem;">{{ $prog->nombre_programa }}</div>
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
                <div class="d-flex justify-content-between align-items-center p-4 border-top" style="border-color: var(--border-dark) !important;">
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

            Swal.fire({
                title: '¿Eliminar Programa?',
                text: `¿Estás seguro de que deseas eliminar "${nombre}"? Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                background: '#1e293b',
                color: '#f8fafc'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection
