@extends('layouts.app')

@section('title', 'Inbox BPM - Solicitudes Cursos Libres')

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

        .avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            background-color: rgba(95, 178, 48, 0.15);
            color: var(--primary);
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
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Solicitudes Cursos Libres</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Solicitudes Cursos Libres</h1>
                <p class="text-white-50 mb-0">Gestione los registros e inscripciones para la oferta de cursos libres.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-success rounded-pill px-4" onclick="copiarEnlacePublico()">
                    <i class="bi bi-share me-1"></i> Compartir Formulario
                </button>
                <a href="{{ route('solicitudes.create') }}" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">
                    <i class="bi bi-plus-lg me-1"></i> Nueva Solicitud
                </a>
            </div>
        </div>

        <!-- Alerta de Copiado -->
        <div id="copyAlert" class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4 d-none" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div>¡Enlace de inscripción pública copiado al portapapeles! Ya puedes compartirlo.</div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <div class="card glass-card">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Nombre Completo</th>
                            <th>Identificación</th>
                            <th>Programa de Interés</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Fecha</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($solicitudes->count() > 0)
                            @foreach ($solicitudes as $s)
                                <tr>
                                    <td class="ps-4 fw-bold text-white-50">#{{ $s->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2">
                                                {{ strtoupper(substr($s->nombre, 0, 1)) }}
                                            </div>
                                            <span class="fw-bold text-white">{{ $s->nombre }} {{ $s->primer_apellido }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $s->identificacion }}</td>
                                    <td><span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25">{{ $s->programa_deseado ?? 'N/A' }}</span></td>
                                    <td>{{ $s->email }}</td>
                                    <td>{{ $s->telefono }}</td>
                                    <td>{{ $s->fecha_creacion->format('d/m/Y') }}</td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('solicitudes.edit', $s->id) }}" class="action-btn-custom" title="Editar"><i class="bi bi-pencil-square"></i></a>
                                            <button onclick="confirmarEliminar({{ $s->id }})" class="action-btn-custom action-btn-delete" title="Eliminar"><i class="bi bi-trash3-fill"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center py-5 text-white-50">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    No hay solicitudes registradas.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @if ($solicitudes->hasPages())
                <div class="card-footer bg-transparent border-top border-secondary p-4">
                    {{ $solicitudes->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function copiarEnlacePublico() {
            const urlPublica = window.location.origin + "/solicitud-curso-libre/publica";
            navigator.clipboard.writeText(urlPublica).then(() => {
                $('#copyAlert').removeClass('d-none');
                setTimeout(() => { $('#copyAlert').addClass('d-none'); }, 4000);
            }).catch(err => {
                alert("No se pudo copiar. URL: " + urlPublica);
            });
        }

        function confirmarEliminar(id) {
            Swal.fire({
                title: '¿Eliminar solicitud?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('<form method="POST" action="' + "{{ route('solicitudes.destroy', ':id') }}".replace(':id', id) + '"></form>');
                    form.append('@csrf');
                    $('body').append(form);
                    form.submit();
                }
            });
        }
    </script>
@endsection
