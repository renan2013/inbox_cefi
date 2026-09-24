@extends('layouts.app')

@section('title', 'Inbox BPM - Gestionar Plantillas de Tareas')

@section('styles')
    <style>
        .page-header {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .form-control-custom, .form-select-custom {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 0.75rem;
            color: var(--text-light);
            padding: 0.65rem 1rem;
            transition: all 0.3s;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            background-color: var(--card-dark);
            border-color: var(--primary);
            color: var(--text-light);
            box-shadow: 0 0 0 0.25rem rgba(95, 178, 48, 0.15);
            outline: none;
        }

        .form-label-custom {
            color: var(--text-light);
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .table-custom {
            --bs-table-bg: transparent !important;
            --bs-table-color: var(--text-light) !important;
            --bs-table-border-color: var(--border-dark) !important;
            --bs-table-hover-bg: rgba(255, 255, 255, 0.03) !important;
            --bs-table-hover-color: var(--text-light) !important;
            color: var(--text-light) !important;
            background-color: transparent !important;
            margin-bottom: 0;
        }

        [data-theme="light"] .table-custom {
            --bs-table-hover-bg: rgba(0, 0, 0, 0.03) !important;
        }

        .table-custom td, .table-custom th {
            background-color: transparent !important;
            color: inherit !important;
            border-bottom: 1px solid var(--border-dark) !important;
            padding: 1rem 1.25rem;
            vertical-align: middle;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            border: 1px solid var(--border-dark);
            background: rgba(100, 116, 139, 0.1);
            color: var(--text-light);
            text-decoration: none;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .btn-edit:hover {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border-color: #ffc107;
        }

        .btn-delete:hover {
            background-color: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border-color: #ef4444;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-5 px-md-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tareas.seleccionar_plantilla') }}" class="text-decoration-none text-white-50">Plantillas</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Gestionar Plantillas</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1 text-white">
                    <i class="bi bi-file-earmark-medical-fill text-success me-2"></i> Plantillas de Tareas
                </h1>
                <p class="text-white-50 mb-0">Administra modelos predefinidos para la creación de tareas recurrentes.</p>
            </div>
            <div>
                <a href="{{ route('tareas.create') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Nueva Tarea
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 mb-4 shadow-sm">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger border-0 rounded-4 mb-4 shadow-sm">
                <i class="bi bi-exclamation-octagon-fill me-2"></i> {{ session('error') }}
            </div>
        @endif

        <div class="row g-4">
            <!-- Formulario de Creación -->
            <div class="col-lg-7">
                <div class="glass-card p-4 p-md-5">
                    <h5 class="fw-bold mb-4 text-white"><i class="bi bi-plus-circle text-primary me-2"></i> Crear Nueva Plantilla</h5>
                    
                    <form action="{{ route('tareas.plantillas.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="titulo" class="form-label-custom">Título de la Plantilla</label>
                            <input type="text" name="titulo" id="titulo" class="form-control form-control-custom" placeholder="Ej: Revisión de Inscripción Única" required>
                        </div>

                        <div class="mb-4">
                            <label for="descripcion" class="form-label-custom">Descripción / Instrucciones</label>
                            <textarea name="descripcion" id="descripcion" class="form-control form-control-custom" rows="6" placeholder="Escriba la descripción por defecto de la tarea..."></textarea>
                        </div>

                        <div class="row align-items-end g-3">
                            <div class="col-md-6">
                                <label for="prioridad_default" class="form-label-custom">Prioridad por Defecto</label>
                                <select name="prioridad_default" id="prioridad_default" class="form-select form-select-custom" required>
                                    <option value="baja">Baja (Informativa)</option>
                                    <option value="media" selected>Media (Normal)</option>
                                    <option value="alta">Alta (Urgente)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success w-100 py-2.5 rounded-3 fw-bold shadow" style="background-color: var(--primary); border: none;">
                                    <i class="bi bi-save me-1"></i> Guardar Plantilla
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Listado de Existentes -->
            <div class="col-lg-5">
                <div class="glass-card p-0">
                    <div class="px-4 py-3 border-bottom border-secondary bg-dark bg-opacity-25">
                        <h5 class="fw-bold mb-0 text-white"><i class="bi bi-list-stars text-primary me-2"></i> Plantillas Existentes</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom table-hover align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4">Título</th>
                                    <th class="text-center">Prioridad</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($plantillas as $plantilla)
                                    <tr>
                                        <td class="ps-4">
                                            <a href="{{ route('tareas.plantillas.edit', $plantilla->id) }}" class="text-white fw-bold text-decoration-none">
                                                {{ $plantilla->titulo }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $p = $plantilla->prioridad_default ?? 'media';
                                                $p_class = $p == 'alta' ? 'danger' : ($p == 'media' ? 'warning' : 'success');
                                            @endphp
                                            <span class="badge bg-{{ $p_class }} bg-opacity-20 text-{{ $p_class }} border border-{{ $p_class }} border-opacity-25 px-2 py-1 small text-uppercase">
                                                {{ ucfirst($p) }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('tareas.plantillas.edit', $plantilla->id) }}" class="action-btn btn-edit" title="Editar"><i class="bi bi-pencil-square"></i></a>
                                                <form action="{{ route('tareas.plantillas.destroy', $plantilla->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar la plantilla \'{{ addslashes($plantilla->titulo) }}\'?');">
                                                    @csrf
                                                    <button type="submit" class="action-btn btn-delete" title="Eliminar"><i class="bi bi-trash3-fill"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-white-50">
                                            <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-muted"></i>
                                            <p class="mb-0">No hay plantillas de tareas registradas.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
