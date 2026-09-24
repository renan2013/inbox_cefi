@extends('layouts.app')

@section('title', 'Inbox BPM - Gestión de Plantillas')

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
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Automatizaciones</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Gestión de Plantillas de Documentos</h1>
                <p class="text-white-50 mb-0">Cree y configure las plantillas de impresión, títulos y certificaciones mediante arrastre gráfico.</p>
            </div>
            <div>
                <a href="{{ route('plantillas.create') }}" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">
                    <i class="bi bi-plus-lg me-1"></i> Nueva Plantilla
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- PLANTILLAS TABLE -->
        <div class="card glass-card">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr class="bg-dark text-white-50">
                            <th class="ps-4">Vista Previa</th>
                            <th>Identificador</th>
                            <th>Dimensiones (Ancho x Alto)</th>
                            <th>Campos de Datos Mapeados</th>
                            <th>Creada el</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($plantillas->count() > 0)
                            @foreach ($plantillas as $p)
                                <tr>
                                    <td class="ps-4">
                                        <div style="width: 80px; height: 50px; background-image: url('{{ asset($p->imagen_fondo) }}'); background-size: cover; background-position: center; border-radius: 0.5rem; border: 1px solid var(--border-dark);"></div>
                                    </td>
                                    <td>
                                        <strong class="text-white d-block">{{ $p->nombre }}</strong>
                                        <span class="text-white-50 small" style="font-size: 0.75rem;">ID: #{{ $p->id }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary text-light rounded px-2 py-1.5 fw-semibold font-monospace">{{ $p->ancho_mm }} mm x {{ $p->alto_mm }} mm</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1.5 border border-success border-opacity-25 fw-bold">
                                            {{ count(json_decode($p->configuracion_campos, true) ?: []) }} campos mapeados
                                        </span>
                                    </td>
                                    <td>{{ $p->fecha_creacion ? $p->fecha_creacion->format('d/m/Y') : '-' }}</td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('plantillas.preparar', $p->id) }}" class="btn btn-sm btn-success rounded-pill px-3 fw-bold" style="background-color: var(--primary); border: none;"><i class="bi bi-file-earmark-text me-1"></i> Emitir</a>
                                            <a href="{{ route('plantillas.edit', $p->id) }}" class="btn btn-sm btn-outline-light rounded-pill px-3"><i class="bi bi-pencil-square me-1"></i> Diseñar</a>
                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-2" onclick="confirmarEliminar({{ $p->id }})" title="Eliminar Plantilla"><i class="bi bi-trash3"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center py-5 text-white-50">
                                    No hay plantillas de documentos registradas.
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
        function confirmarEliminar(id) {
            Swal.fire({
                title: '¿Eliminar plantilla?',
                text: 'Esta acción borrará la plantilla y su imagen de fondo permanentemente.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('<form method="POST" action="' + "{{ route('plantillas.delete', ':id') }}".replace(':id', id) + '"></form>');
                    form.append('@csrf');
                    $('body').append(form);
                    form.submit();
                }
            });
        }
    </script>
@endsection
