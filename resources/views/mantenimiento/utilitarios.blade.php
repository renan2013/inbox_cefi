@extends('layouts.app')

@section('title', 'Inbox BPM - Diagnóstico y Utilitarios')

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

        .card-header-custom {
            background-color: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid var(--border-dark);
            padding: 1.25rem 1.5rem;
        }

        .table-custom {
            margin-bottom: 0;
            background-color: transparent !important;
        }

        .table-custom td, .table-custom th {
            background-color: transparent !important;
            color: #e2e8f0 !important;
            border-bottom: 1px solid var(--border-dark);
            padding: 1rem 1.25rem;
            vertical-align: middle;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Utilitarios de Diagnóstico</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1"><i class="bi bi-tools text-success me-2"></i> Utilitarios y Diagnóstico</h1>
                <p class="text-white-50 mb-0">Consola técnica para verificación de integridad de materias, planes y grupos.</p>
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

        <!-- GRUPOS ACTIVOS -->
        <div class="card glass-card">
            <div class="card-header-custom">
                <h5 class="fw-bold text-white mb-0"><i class="bi bi-list-check me-2 text-success"></i> Diagnóstico: Todos los Grupos Generales</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr class="bg-dark text-white-50">
                            <th class="ps-4">ID</th>
                            <th>Código</th>
                            <th>Materia / Asignatura</th>
                            <th>Período</th>
                            <th>Profesor</th>
                            <th class="text-end pe-4">Operación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($grupos as $g)
                            <tr>
                                <td class="ps-4 font-monospace text-white-50">#{{ $g->id_curso_activo }}</td>
                                <td class="font-monospace text-success fw-bold">{{ $g->planEstudio->codigo ?? 'N/A' }}</td>
                                <td class="fw-bold text-white">{{ $g->planEstudio->materia ?? 'PLAN HUÉRFANO' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $g->periodo }}</span></td>
                                <td>
                                    <small class="text-white-50">
                                        @if ($g->profesor)
                                            {{ $g->profesor->apellidos }}, {{ $g->profesor->nombre }}
                                        @else
                                            <span class="text-danger fw-bold">Sin Profesor</span>
                                        @endif
                                    </small>
                                </td>
                                <td class="text-end pe-4">
                                    <button onclick="confirmarEliminarGrupo({{ $g->id_curso_activo }})" class="btn btn-sm btn-outline-danger border-0">
                                        <i class="bi bi-trash3-fill"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DICCIONARIO DE MATERIAS BASE -->
        <div class="card glass-card">
            <div class="card-header-custom">
                <h5 class="fw-bold text-white mb-0"><i class="bi bi-book-half me-2 text-success"></i> Diccionario de Materias Base (Plan de Estudios)</h5>
            </div>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-custom mb-0">
                    <thead class="sticky-top bg-dark">
                        <tr class="text-white-50">
                            <th class="ps-4">ID Plan</th>
                            <th>Código</th>
                            <th>Materia</th>
                            <th>Programa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($materias as $m)
                            <tr>
                                <td class="ps-4 font-monospace text-white-50">#{{ $m->id_plan }}</td>
                                <td class="font-monospace text-success fw-bold">{{ $m->codigo }}</td>
                                <td class="fw-bold text-white">{{ $m->materia }}</td>
                                <td><small class="text-white-50">{{ $m->programa->nombre_programa ?? 'Sin programa' }}</small></td>
                            </tr>
                        @endforeach
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
        function confirmarEliminarGrupo(id) {
            Swal.fire({
                title: '¿Eliminar definitivamente?',
                text: 'Esto borrará el grupo técnico y todas sus matrículas y dependencias asociadas.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, borrar definitivamente',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('<form method="POST" action="' + "{{ route('utilitarios.destroy', ':id') }}".replace(':id', id) + '"></form>');
                    form.append('@csrf');
                    $('body').append(form);
                    form.submit();
                }
            });
        }
    </script>
@endsection
