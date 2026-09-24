@extends('layouts.app')

@section('title', 'Inbox BPM - Crear Nueva Tarea')

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

        .form-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
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

        .file-upload-box {
            background-color: rgba(95, 178, 48, 0.05);
            border: 2px dashed var(--border-dark);
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .file-upload-box:hover {
            border-color: var(--primary);
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Dashboard</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Nueva Tarea</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header">
            <h1 class="display-6 fw-bold mb-1 text-white">
                <i class="bi bi-plus-circle text-primary me-2"></i> Crear Nueva Tarea
            </h1>
            <p class="text-white-50 mb-0">Organice el trabajo colaborativo asignando actividades a su equipo.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 rounded-4 mb-4 shadow-sm">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                
                <!-- Selector de Plantilla -->
                <div class="form-card mb-4 p-4">
                    <label for="plantilla_selector" class="form-label-custom d-block mb-2">
                        <i class="bi bi-magic text-primary me-2"></i> ¿Cargar desde una plantilla de tarea?
                    </label>
                    <select id="plantilla_selector" class="form-select form-select-custom" onchange="cargarPlantilla(this.value)">
                        <option value="" {{ empty($plantilla_id) ? 'selected' : '' }}>Seleccione para auto-completar...</option>
                        <option value="0">--- Tarea en Blanco ---</option>
                        @foreach ($plantillas as $p)
                            <option value="{{ $p->id }}" {{ $plantilla_id == $p->id ? 'selected' : '' }}>
                                {{ $p->titulo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Formulario Principal -->
                <div class="form-card p-4 p-md-5">
                    <form action="{{ route('tareas.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Título -->
                        <div class="mb-4">
                            <label for="titulo" class="form-label-custom"><i class="bi bi-fonts me-1"></i> Título de la Tarea</label>
                            <input type="text" name="titulo" id="titulo" class="form-control form-control-custom form-control-lg fw-bold" value="{{ old('titulo', $plantilla_titulo) }}" placeholder="Ej: Revisión de Acta Final" required>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-4">
                            <label for="descripcion" class="form-label-custom"><i class="bi bi-text-paragraph me-1"></i> Descripción Detallada</label>
                            <textarea name="descripcion" id="descripcion" class="form-control form-control-custom" rows="5" placeholder="Escriba aquí los detalles y requerimientos de la tarea...">{{ old('descripcion', $plantilla_descripcion) }}</textarea>
                        </div>

                        <!-- Fila de Propiedades (Prioridad, Fecha Inicio, Vencimiento) -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="prioridad" class="form-label-custom"><i class="bi bi-flag-fill me-1"></i> Prioridad</label>
                                <select name="prioridad" id="prioridad" class="form-select form-select-custom" required>
                                    <option value="baja" {{ old('prioridad', $plantilla_prioridad) == 'baja' ? 'selected' : '' }}>Baja</option>
                                    <option value="media" {{ old('prioridad', $plantilla_prioridad) == 'media' ? 'selected' : '' }}>Media (Normal)</option>
                                    <option value="alta" {{ old('prioridad', $plantilla_prioridad) == 'alta' ? 'selected' : '' }}>Alta (Urgente)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="fecha_creacion" class="form-label-custom"><i class="bi bi-calendar-event me-1"></i> Fecha de Inicio</label>
                                <input type="date" name="fecha_creacion" id="fecha_creacion" class="form-control form-control-custom" value="{{ old('fecha_creacion', date('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="fecha_vencimiento" class="form-label-custom text-danger"><i class="bi bi-calendar-check me-1"></i> Vencimiento</label>
                                <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control form-control-custom border-danger border-opacity-50" value="{{ old('fecha_vencimiento') }}">
                            </div>
                        </div>

                        <!-- Fila de Asignación y Etiquetas -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="id_asignado" class="form-label-custom"><i class="bi bi-people me-1"></i> Asignar Responsables</label>
                                <select name="id_asignado[]" id="id_asignado" class="form-select form-select-custom" multiple style="min-height: 140px;">
                                    @foreach ($usuarios as $u)
                                        <option value="{{ $u->id }}">{{ $u->nombre }} {{ $u->apellidos ?? '' }}</option>
                                    @endforeach
                                </select>
                                <small class="text-white-50 mt-1 d-block"><i class="bi bi-info-circle me-1"></i> Mantenga presionada la tecla Ctrl (Cmd en Mac) para seleccionar varios.</small>
                            </div>

                            <div class="col-md-6">
                                <label for="etiquetas" class="form-label-custom"><i class="bi bi-tags me-1"></i> Categorías / Etiquetas</label>
                                <select name="etiquetas[]" id="etiquetas" class="form-select form-select-custom" multiple style="min-height: 140px;">
                                    @foreach ($etiquetas as $et)
                                        <option value="{{ $et->id }}">{{ $et->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Curso Vinculado (Opcional) -->
                        @if (count($cursos_activos) > 0)
                            <div class="mb-4">
                                <label for="id_curso_activo" class="form-label-custom"><i class="bi bi-mortarboard me-1"></i> Vinculado a Curso Activo (Opcional)</label>
                                <select name="id_curso_activo" id="id_curso_activo" class="form-select form-select-custom">
                                    <option value="">Ninguno (Tarea General / Administrativa)</option>
                                    @foreach ($cursos_activos as $c)
                                        <option value="{{ $c->id_curso_activo }}">{{ $c->codigo }} - {{ $c->materia }} ({{ $c->periodo }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Archivos Adjuntos -->
                        <div class="mb-5">
                            <label class="form-label-custom"><i class="bi bi-paperclip me-1"></i> Archivos Adjuntos</label>
                            <div class="file-upload-box">
                                <input type="file" name="adjuntos[]" id="fileInput" class="form-control form-control-custom" multiple>
                                <small class="text-white-50 mt-2 d-block"><i class="bi bi-info-circle me-1"></i> Puede adjuntar imágenes, PDFs o documentos comprimidos (Máx. 5MB cada uno).</small>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="row g-3">
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-success w-100 py-3 rounded-3 fw-bold shadow" style="background-color: var(--primary); border: none;">
                                    <i class="bi bi-check-circle-fill me-2"></i> Crear y Notificar Tarea
                                </button>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary w-100 py-3 rounded-3">
                                    Cancelar
                                </a>
                            </div>
                        </div>

                    </form>
                </div>

            </div>
        </div>

    </div>
@endsection

@section('scripts')
<script>
    function cargarPlantilla(pId) {
        if (pId !== undefined && pId !== null && pId !== '') {
            window.location.href = pId === '0' ? "{{ route('tareas.create') }}" : "{{ route('tareas.create') }}?plantilla_id=" + pId;
        }
    }
</script>
@endsection
