@extends('layouts.app')

@section('title', 'Inbox BPM - Editar Plantilla de Tarea')

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
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tareas.plantillas.gestionar') }}" class="text-decoration-none text-white-50">Plantillas</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Editar Plantilla</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1 text-white">
                    <i class="bi bi-pencil-square text-warning me-2"></i> Editar Plantilla de Tarea
                </h1>
                <p class="text-white-50 mb-0">Modifique los campos por defecto de la plantilla seleccionada.</p>
            </div>
            <div>
                <a href="{{ route('tareas.plantillas.gestionar') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Plantillas
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="glass-card p-4 p-md-5">
                    <form action="{{ route('tareas.plantillas.update', $plantilla->id) }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="titulo" class="form-label-custom">Título de la Plantilla</label>
                            <input type="text" name="titulo" id="titulo" class="form-control form-control-custom form-control-lg fw-bold" value="{{ old('titulo', $plantilla->titulo) }}" required>
                        </div>

                        <div class="mb-4">
                            <label for="descripcion" class="form-label-custom">Descripción / Instrucciones por Defecto</label>
                            <textarea name="descripcion" id="descripcion" class="form-control form-control-custom" rows="8">{{ old('descripcion', $plantilla->descripcion) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="prioridad_default" class="form-label-custom">Prioridad por Defecto</label>
                            <select name="prioridad_default" id="prioridad_default" class="form-select form-select-custom" required>
                                <option value="baja" {{ old('prioridad_default', $plantilla->prioridad_default) == 'baja' ? 'selected' : '' }}>Baja (Informativa)</option>
                                <option value="media" {{ old('prioridad_default', $plantilla->prioridad_default) == 'media' ? 'selected' : '' }}>Media (Normal)</option>
                                <option value="alta" {{ old('prioridad_default', $plantilla->prioridad_default) == 'alta' ? 'selected' : '' }}>Alta (Urgente)</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-5">
                            <a href="{{ route('tareas.plantillas.gestionar') }}" class="btn btn-outline-secondary px-4 py-2.5 rounded-3">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-3 fw-bold shadow">
                                <i class="bi bi-check-circle-fill me-1"></i> Actualizar Plantilla
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection
