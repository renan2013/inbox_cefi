@extends('layouts.app')

@section('title', 'Categorías de Saludos - Inbox 2.0')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="bi bi-folder2-open text-info me-2"></i>Categorías del Módulo Saludos</h2>
        <a href="{{ route('saludos.plantillas') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="bi bi-arrow-left me-1"></i>Volver a Plantillas
        </a>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4" style="background: var(--card-dark);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Nueva Categoría</h5>
                    <form method="POST" action="{{ route('saludos.categorias.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre de Categoría *</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Aniversario, Graduación..." required>
                        </div>
                        <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold">Guardar Categoría</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4" style="background: var(--card-dark);">
                <div class="card-body p-4">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Categoría</th>
                                <th>Plantillas</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categorias as $c)
                                <tr>
                                    <td>#{{ $c->id }}</td>
                                    <td class="fw-bold">{{ $c->nombre }}</td>
                                    <td><span class="badge bg-info bg-opacity-10 text-info">{{ $c->total_plantillas }} plantillas</span></td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('saludos.categorias.destroy', $c->id) }}" onsubmit="return confirm('¿Eliminar categoría?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
