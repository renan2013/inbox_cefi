@extends('layouts.app')

@section('title', 'Etiquetas de Tareas - Inbox 2.0')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-0"><i class="bi bi-bookmarks-fill text-warning me-2"></i>Gestión de Etiquetas de Tareas</h2>
            <p class="text-muted small mb-0">Clasifique las tareas operativas y académicas del sistema mediante etiquetas personalizadas.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="bi bi-arrow-left me-1"></i>Volver al Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4" style="background: var(--card-dark);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle text-success me-2"></i>Nueva Etiqueta</h5>
                    <form method="POST" action="{{ route('tareas.etiquetas.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre de la Etiqueta *</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Urgente, Académico, Contabilidad..." required>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold rounded-pill">
                            <i class="bi bi-save me-1"></i>Guardar Etiqueta
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4" style="background: var(--card-dark);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Etiquetas Registradas ({{ $etiquetas->count() }})</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre de Etiqueta</th>
                                    <th>Tareas Vinculadas</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($etiquetas as $et)
                                    <tr>
                                        <td>#{{ $et->id }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('tareas.etiquetas.update', $et->id) }}" class="d-flex gap-2">
                                                @csrf
                                                <input type="text" name="nombre" value="{{ $et->nombre }}" class="form-control form-control-sm" style="max-width: 240px;" required>
                                                <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    <i class="bi bi-check2"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">{{ $et->total_tareas }} tareas</span>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('tareas.etiquetas.destroy', $et->id) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta etiqueta?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                    <i class="bi bi-trash"></i> Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No hay etiquetas creadas aún.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
