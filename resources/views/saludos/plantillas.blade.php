@extends('layouts.app')

@section('title', 'Módulo de Saludos y Tarjetas Institucionales - Inbox 2.0')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-0"><i class="bi bi-chat-heart-fill text-success me-2"></i>Módulo de Saludos y Tarjetas Institucionales</h2>
            <p class="text-muted small mb-0">Cree plantillas de felicitación, bienvenida o cumpleaños y genere tarjetas personalizadas con enlace público.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCrearTarjeta">
                <i class="bi bi-envelope-paper-heart-fill me-1"></i>Preparar y Enviar Tarjeta
            </button>
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalCrearPlantillaSaludo">
                <i class="bi bi-plus-lg me-1"></i>Añadir Plantilla
            </button>
            <a href="{{ route('saludos.categorias') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-folder2 me-1"></i>Categorías
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4" style="background: var(--card-dark);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Plantillas Disponibles ({{ $plantillas->count() }})</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Vista</th>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($plantillas as $p)
                                    <tr>
                                        <td>
                                            <img src="{{ asset($p->ruta_imagen ?: 'imgs/logo_cefi_oficial.png') }}" alt="{{ $p->nombre }}" style="width: 72px; height: 48px; object-fit: cover; border-radius: 8px;">
                                        </td>
                                        <td class="fw-bold">{{ $p->nombre }}</td>
                                        <td><span class="badge bg-info bg-opacity-10 text-info">{{ $p->categoria_nombre ?: 'General' }}</span></td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('saludos.plantillas.destroy', $p->id) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta plantilla?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No hay plantillas de saludo creadas aún.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4" style="background: var(--card-dark);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-send-check text-info me-2"></i>Últimas Tarjetas Emitidas</h5>
                    <div class="list-group list-group-flush">
                        @forelse($tarjetas as $t)
                            <div class="list-group-item bg-transparent px-0 py-3 d-flex justify-content-between align-items-center border-secondary border-opacity-25">
                                <div>
                                    <div class="fw-bold">{{ $t->destinatario }}</div>
                                    <div class="small text-muted">{{ $t->plantilla_nombre ?: 'Tarjeta Institucional' }} • {{ date('d/m/Y H:i', strtotime($t->created_at)) }}</div>
                                </div>
                                <a href="{{ route('saludos.ver_tarjeta', $t->token) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>Ver Tarjeta
                                </a>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">Aún no se han emitido tarjetas personalizadas.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CREAR PLANTILLA -->
<div class="modal fade" id="modalCrearPlantillaSaludo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('saludos.plantillas.store') }}" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg" style="background: var(--card-dark);">
            @csrf
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Nueva Plantilla de Saludo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nombre de la Plantilla *</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej: Tarjeta Oficial de Cumpleaños" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Categoría *</label>
                    <select name="categoria_id" class="form-select" required>
                        @foreach($categorias as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Imagen o Arte de Fondo (Opcional)</label>
                    <input type="file" name="imagen" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Mensaje Sugerido</label>
                    <textarea name="mensaje_default" class="form-control" rows="3">¡Reciba una cordial felicitación y nuestros mejores deseos de éxito!</textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Guardar Plantilla</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PREPARAR TARJETA -->
<div class="modal fade" id="modalCrearTarjeta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('saludos.tarjetas.store') }}" class="modal-content border-0 rounded-4 shadow-lg" style="background: var(--card-dark);">
            @csrf
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-envelope-paper-heart text-success me-2"></i>Preparar Tarjeta Personalizada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Plantilla *</label>
                    <select name="plantilla_id" class="form-select" required>
                        @forelse($plantillas as $p)
                            <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                        @empty
                            <option value="0">Tarjeta Institucional General</option>
                        @endforelse
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nombre del Destinatario *</label>
                    <input type="text" name="destinatario" class="form-control" placeholder="Ej: Licda. María Rodríguez" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Dedicatoria / Mensaje *</label>
                    <textarea name="mensaje" class="form-control" rows="3" required>Le deseamos un día extraordinario lleno de alegría y bendiciones junto a sus seres queridos.</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Firma / Remitente</label>
                    <input type="text" name="remitente" class="form-control" value="{{ config('cliente.nombre_legal', 'Dirección Académica CEFI') }}">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Generar Tarjeta</button>
            </div>
        </form>
    </div>
</div>
@endsection
