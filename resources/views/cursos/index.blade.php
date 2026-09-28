@extends('layouts.app')

@section('title', 'Inbox BPM - Lista de Cursos')

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

        .badge-cuatri {
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            background-color: rgba(95, 178, 48, 0.15);
            color: var(--primary);
            border: 1px solid rgba(95, 178, 48, 0.3);
            white-space: nowrap;
            display: inline-block;
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
                <h1 class="display-6 fw-bold mb-1">Lista de Cursos</h1>
                <p class="text-white-50 mb-0">Gestiona y consulta las materias, códigos y créditos correspondientes a los planes de estudio.</p>
            </div>
            <div>
                <a href="{{ route('cursos.create') }}" class="btn btn-search">
                    <i class="bi bi-plus-circle me-1"></i> Registrar Nuevo Curso
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="filter-section">
            <form action="{{ route('cursos.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label text-white-50 fw-semibold mb-2">Buscar Materia o Código</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 border-2" style="border-color: var(--border-dark); border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem; color: var(--text-muted);">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" id="search" class="form-control form-control-custom border-start-0 ps-0" placeholder="Nombre de materia o código..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="programa_id" class="form-label text-white-50 fw-semibold mb-2">Programa Académico</label>
                    <select name="programa_id" id="programa_id" class="form-select form-control-custom">
                        <option value="">Todos los Programas</option>
                        @foreach ($programas as $prog)
                            <option value="{{ $prog->id_programa }}" {{ request('programa_id') == $prog->id_programa ? 'selected' : '' }}>{{ $prog->nombre_programa }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-search flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('cursos.index') }}" class="btn btn-clear flex-grow-1 text-center">
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
                            <th>Código</th>
                            <th>Nombre de la Materia</th>
                            <th>Programa</th>
                            <th>Cuatrimestre</th>
                            <th>Créditos</th>
                            <th>Precio</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cursos as $curso)
                            <tr>
                                <td>
                                    <strong class="text-white">{{ $curso->codigo }}</strong>
                                </td>
                                <td>
                                    <div class="fw-bold" style="color: #f8fafc;">{{ $curso->materia }}</div>
                                    <small class="text-white-50">ID Plan: {{ $curso->id_plan }}</small>
                                </td>
                                <td>
                                    <div>{{ $curso->programa->nombre_programa ?? 'Sin Programa' }}</div>
                                    <small class="text-white-50">{{ $curso->programa->categoria ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="badge-cuatri">{{ $curso->cuatrimestre }}</span>
                                </td>
                                <td>
                                    <strong class="text-white">{{ $curso->creditos ?? 'N/D' }}</strong>
                                </td>
                                <td>
                                    <span class="text-success fw-bold">₡{{ number_format($curso->precio, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('cursos.descriptor_pdf', $curso->id_plan) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Ver Descriptor PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                        <a href="{{ route('cursos.descriptor_doc', $curso->id_plan) }}" class="btn btn-sm btn-outline-primary" title="Descargar Descriptor Word (.doc)">
                                            <i class="bi bi-file-earmark-word"></i>
                                        </a>
                                        <a href="{{ route('cursos.edit', $curso->id_plan) }}" class="btn btn-sm btn-outline-success" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-curso" data-id="{{ $curso->id_plan }}" data-materia="{{ $curso->materia }}" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-white-50">
                                    <i class="bi bi-journal-x display-4 d-block mb-3 text-muted"></i>
                                    <p class="mb-3">No se encontraron cursos con los criterios de búsqueda especificados.</p>
                                    <a href="{{ route('cursos.create') }}" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-plus-circle me-1"></i> Registrar Nuevo Curso
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            @if ($cursos->hasPages())
                <div class="d-flex justify-content-between align-items-center p-4 border-top" style="border-color: var(--border-dark) !important;">
                    <div class="text-white-50" style="font-size: 0.9rem;">
                        Mostrando registros del <strong>{{ $cursos->firstItem() }}</strong> al <strong>{{ $cursos->lastItem() }}</strong> de un total de <strong>{{ $cursos->total() }}</strong>
                    </div>
                    <div>
                        {{ $cursos->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>

    </div>

    <!-- Hidden Form for deletion -->
    <form id="form-delete-curso" method="POST" style="display: none;">
        @csrf
    </form>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Operación Exitosa!',
                    text: "{{ session('success') }}",
                    timer: 2500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Atención',
                    text: "{{ session('error') }}"
                });
            @endif

            document.querySelectorAll('.btn-eliminar-curso').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    const materia = this.getAttribute('data-materia');

                    Swal.fire({
                        title: '¿Eliminar Materia?',
                        text: `¿Seguro que deseas eliminar "${materia}" del plan de estudios?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('form-delete-curso');
                            form.action = `/cursos/${id}/eliminar`;
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
