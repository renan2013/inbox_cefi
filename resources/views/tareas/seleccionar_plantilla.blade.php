@extends('layouts.app')

@section('title', 'Inbox BPM - Seleccionar Plantilla de Tarea')

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

        .option-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .option-card:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
        }

        .form-select-custom {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 0.75rem;
            color: var(--text-light);
            padding: 0.65rem 2.5rem 0.65rem 1rem !important;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Dashboard</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Seleccionar Plantilla</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header text-center py-4">
            <h1 class="display-6 fw-bold mb-1 text-white">
                <i class="bi bi-magic text-primary me-2"></i> Crear Tarea desde Plantilla
            </h1>
            <p class="text-white-50 mb-0">Seleccione una plantilla predefinida para acelerar la creación de tareas recurrentes.</p>
        </div>

        <div class="row justify-content-center g-4">
            
            <!-- Opción 1: Seleccionar Plantilla Existente -->
            <div class="col-md-7 col-lg-6">
                <div class="option-card p-4 p-md-5">
                    <h4 class="fw-bold text-white mb-3"><i class="bi bi-file-earmark-text text-primary me-2"></i> Plantillas Disponibles</h4>
                    <p class="text-white-50 small mb-4">Elija una plantilla para autocompletar el formulario de nueva tarea.</p>

                    <form action="{{ route('tareas.create') }}" method="GET">
                        <div class="mb-4">
                            <label for="plantilla_id" class="form-label text-white fw-bold small text-uppercase">Seleccione Plantilla</label>
                            <select name="plantilla_id" id="plantilla_id" class="form-select form-select-custom" required>
                                <option value="">Elige una plantilla...</option>
                                @foreach ($plantillas as $plantilla)
                                    <option value="{{ $plantilla->id }}">{{ $plantilla->titulo }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2.5 rounded-3 fw-bold shadow">
                                <i class="bi bi-arrow-right-circle me-1"></i> Usar esta Plantilla
                            </button>
                            <a href="{{ route('tareas.create') }}" class="btn btn-outline-secondary py-2.5 rounded-3 fw-bold">
                                <i class="bi bi-plus-circle me-1"></i> Continuar con Tarea en Blanco
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Opción 2: Administrar Plantillas -->
            <div class="col-md-5 col-lg-5">
                <div class="option-card p-4 p-md-5 text-center h-100 d-flex flex-column justify-content-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle mx-auto mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-gear-wide-connected text-primary fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-2">¿Desea gestionar las plantillas?</h5>
                    <p class="text-white-50 small mb-4">Puede crear, editar o eliminar plantillas predeterminadas de tareas.</p>
                    <div>
                        <a href="{{ route('tareas.plantillas.gestionar') }}" class="btn btn-outline-primary px-4 py-2 rounded-3 fw-bold">
                            <i class="bi bi-sliders me-1"></i> Gestionar Plantillas
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
