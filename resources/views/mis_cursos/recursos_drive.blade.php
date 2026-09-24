@extends('layouts.app')

@section('title', 'Gestionar Recursos - ' . $curso->planEstudio->materia)

@section('styles')
<style>
    :root {
        --primary-blue: #0d6efd;
        --primary-blue-hover: #0b5ed7;
        --card-dark-bg: var(--card-dark);
        --border-color: var(--border-dark);
    }

    .main-container {
        padding-top: 2rem;
        padding-bottom: 4rem;
        color: #f8fafc;
    }

    .resource-card {
        background-color: var(--card-dark-bg);
        border: 1px solid var(--border-color);
        border-radius: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .table-custom {
        margin-bottom: 0;
        background-color: transparent !important;
    }

    .table-custom td, .table-custom th {
        background-color: transparent !important;
        color: #cbd5e1 !important;
        border-bottom: 1px solid var(--border-color);
        padding: 1rem;
        vertical-align: middle;
    }

    .table-custom th {
        color: #94a3b8 !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }

    .form-control-custom, .form-select-custom {
        background-color: rgba(15, 23, 42, 0.5) !important;
        border: 1px solid var(--border-color) !important;
        color: #f8fafc !important;
        border-radius: 0.5rem !important;
        padding: 0.6rem 1rem !important;
    }

    .form-control-custom:focus, .form-select-custom:focus {
        border-color: var(--primary-blue) !important;
        outline: none !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .btn-action:hover {
        transform: translateY(-2px);
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-md-5 main-container">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('mis_cursos.index') }}" class="text-decoration-none text-white-50">Mis Cursos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('mis_cursos.ver', $curso->id_curso_activo) }}" class="text-decoration-none text-white-50">{{ $curso->planEstudio->materia }}</a></li>
            <li class="breadcrumb-item active text-white" aria-current="page">Recursos del Curso</li>
        </ol>
    </nav>

    <!-- Header Panel -->
    <div class="resource-card">
        <div class="row align-items-center">
            <div class="col-md-9">
                <h1 class="h2 fw-bold text-white mb-1"><i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i> Gestión de Recursos del Curso</h1>
                <p class="text-white-50 mb-0">Suba materiales de clase y recursos directamente a la base de datos de la plataforma.</p>
            </div>
            <div class="col-md-3 text-md-end mt-3 mt-md-0">
                <a href="{{ route('mis_cursos.ver', $curso->id_curso_activo) }}" class="btn btn-outline-light rounded-pill px-4" id="btn_volver_curso">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Curso
                </a>
            </div>
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

    <div class="row g-4">
        <!-- Formulario de Subida -->
        <div class="col-lg-4">
            <div class="resource-card h-100">
                <h4 class="h5 fw-bold text-white mb-4"><i class="bi bi-file-earmark-plus me-2 text-primary"></i> Subir Nuevo Archivo</h4>
                
                <form action="{{ route('mis_cursos.recursos_drive.guardar', $curso->id_curso_activo) }}" method="POST" enctype="multipart/form-data" id="form_subir_recurso">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label text-white-50 small fw-bold">Seleccionar Archivo</label>
                        <input type="file" name="archivo_drive" class="form-control form-control-custom w-100" id="archivo_drive" required>
                        <div class="form-text text-white-50 x-small mt-2">Tamaño máximo de archivo recomendado: 20MB.</div>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="es_compartido" id="es_compartido" value="1">
                        <label class="form-check-label text-white-50 small fw-bold" for="es_compartido">Compartir con otros profesores</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2.5 fw-bold" id="btn_subir_archivo">
                        <i class="bi bi-cloud-upload me-2"></i> Subir Recurso
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Archivos -->
        <div class="col-lg-8">
            <div class="resource-card h-100 p-0 overflow-hidden">
                <div class="p-4 border-bottom border-secondary d-flex justify-content-between align-items-center">
                    <h4 class="h5 fw-bold text-white mb-0"><i class="bi bi-folder2-open me-2 text-primary"></i> Archivos Vinculados</h4>
                    <span class="badge bg-primary rounded-pill px-3">{{ count($recursos) }} Recursos</span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 55%;">Nombre del Recurso</th>
                                <th style="width: 25%;">Fecha</th>
                                <th class="text-end pe-4" style="width: 20%;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($recursos) > 0)
                                @foreach ($recursos as $r)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-file-earmark-arrow-up text-primary fs-3 me-3"></i>
                                                <div>
                                                    <div class="fw-bold text-white">{{ $r->nombre_archivo }}</div>
                                                    <div class="d-flex gap-2 align-items-center mt-1">
                                                        <small class="text-white-50 x-small">ID Local: {{ substr($r->id_drive, 0, 8) }}</small>
                                                        @if ($r->es_compartido)
                                                            <span class="badge bg-success rounded-pill x-small" style="font-size: 0.6rem;"><i class="bi bi-share-fill"></i> Compartido</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-white-50 small">
                                            {{ \Carbon\Carbon::parse($r->fecha_subida)->format('d/m/Y g:i a') }}
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-inline-flex gap-1">
                                                <button class="btn btn-action btn-outline-primary" onclick="copyToClipboard('{{ $r->link_publico }}')" title="Copiar Enlace" id="btn_copiar_{{ $r->id }}">
                                                    <i class="bi bi-link-45deg"></i>
                                                </button>
                                                <a href="{{ $r->link_publico }}" target="_blank" class="btn btn-action btn-outline-success" title="Descargar" id="btn_bajar_{{ $r->id }}">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <a href="{{ route('mis_cursos.recursos_drive.eliminar', [$curso->id_curso_activo, $r->id]) }}" 
                                                   class="btn btn-action btn-outline-danger" 
                                                   onclick="return confirm('¿Está seguro de eliminar este recurso del curso?')"
                                                   title="Eliminar" id="btn_eliminar_{{ $r->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-white-50">
                                        <i class="bi bi-folder-x fs-1 d-block mb-3 opacity-50"></i>
                                        No hay recursos vinculados a este curso todavía.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Enlace copiado',
                text: 'El enlace de descarga se ha copiado al portapapeles.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        });
    }
</script>
@endsection
