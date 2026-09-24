@extends('layouts.app')

@section('title', 'Inbox BPM - Bitácoras de TCU')

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

        .avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            background-color: rgba(95, 178, 48, 0.15);
            color: var(--primary);
            border: 2px solid var(--border-dark);
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Bitácoras TCU</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">
                    {{ $es_admin ? 'Gestión de Bitácoras TCU' : 'Mis Bitácoras de TCU' }}
                </h1>
                <p class="text-white-50 mb-0">Seguimiento de horas y actividades de Trabajo Comunal Universitario.</p>
            </div>
            @if (!$es_admin)
                <div>
                    <a href="{{ route('tcu.create') }}" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">
                        <i class="bi bi-plus-lg me-1"></i> Nueva Bitácora
                    </a>
                </div>
            @endif
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

        <!-- BITACORAS TABLE -->
        <div class="card glass-card">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr class="bg-dark text-white-50">
                            @if ($es_admin)
                                <th class="ps-4">Estudiante</th>
                            @endif
                            <th class="{{ !$es_admin ? 'ps-4' : '' }}">Proyecto / Institución</th>
                            <th class="text-center">Creación</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($bitacoras->count() > 0)
                            @foreach ($bitacoras as $row)
                                <tr>
                                    @if ($es_admin)
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-2">
                                                    {{ strtoupper(substr($row->estudiante->nombre ?? 'E', 0, 1)) }}
                                                </div>
                                                <span class="fw-bold text-white">{{ $row->estudiante->nombre ?? 'Desconocido' }} {{ $row->estudiante->apellidos ?? '' }}</span>
                                            </div>
                                        </td>
                                    @endif
                                    <td class="{{ !$es_admin ? 'ps-4' : '' }}">
                                        <strong class="text-white d-block">{{ $row->nombre_proyecto ?? 'Sin nombre' }}</strong>
                                        <span class="text-white-50 small"><i class="bi bi-building me-1"></i>{{ $row->institucion_beneficiada ?: 'N/A' }}</span>
                                    </td>
                                    <td class="text-center text-white-50 small">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $row->fecha_creacion->format('d/m/Y') }}
                                    </td>
                                    <td class="text-center">
                                        @if ($row->estado === 'borrador')
                                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-1.5 border border-warning border-opacity-25 fw-bold">Borrador</span>
                                        @elseif ($row->estado === 'finalizado')
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1.5 border border-success border-opacity-25 fw-bold">Entregada</span>
                                        @else
                                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3 py-1.5 border border-info border-opacity-25 fw-bold">Revisada</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('tcu.edit', $row->id) }}" class="btn btn-sm btn-outline-success rounded-pill px-3" title="{{ ($row->estado === 'borrador' && !$es_admin) ? 'Editar' : 'Ver' }}">
                                                <i class="bi {{ ($row->estado === 'borrador' && !$es_admin) ? 'bi-pencil-square' : 'bi-eye-fill' }}"></i> {{ ($row->estado === 'borrador' && !$es_admin) ? 'Editar' : 'Ver' }}
                                            </a>
                                            <a href="{{ route('tcu.imprimir', $row->id) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="PDF / Imprimir">
                                                <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                                            </a>
                                            @if ($row->estado === 'borrador' || $es_admin)
                                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="confirmarEliminar({{ $row->id }})" title="Eliminar">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="{{ $es_admin ? '5' : '4' }}" class="text-center py-5 text-white-50">
                                    <i class="bi bi-journal-x display-4 d-block mb-3"></i>
                                    No hay bitácoras de TCU registradas.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @if ($bitacoras->hasPages())
                <div class="card-footer bg-transparent border-top border-secondary p-4">
                    {{ $bitacoras->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmarEliminar(id) {
            Swal.fire({
                title: '¿ELIMINAR BITÁCORA?',
                text: "¡ATENCIÓN! Se eliminarán todas las actividades y horas registradas de forma permanente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, ELIMINAR TODO',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('<form method="POST" action="' + "{{ route('tcu.delete', ':id') }}".replace(':id', id) + '"></form>');
                    form.append('@csrf');
                    $('body').append(form);
                    form.submit();
                }
            });
        }
    </script>
@endsection
