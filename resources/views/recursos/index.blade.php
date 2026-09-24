@extends('layouts.app')

@section('title', 'Inbox BPM - Mis Recursos Didácticos')

@section('styles')
    <style>
        .page-header {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2rem 2.5rem;
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

        .form-control-custom, .form-select-custom {
            background-color: rgba(15, 23, 42, 0.5);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.6rem 0.9rem;
            transition: all 0.3s;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            background-color: rgba(15, 23, 42, 0.7);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15);
            color: #f8fafc;
            outline: none;
        }

        .form-label-custom {
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .folder-section {
            background: rgba(30, 41, 59, 0.2);
            border: 1px solid var(--border-dark);
            border-radius: 1.25rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .resource-card {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--border-dark);
            border-radius: 1rem;
            padding: 1.25rem;
            transition: all 0.3s;
            position: relative;
        }
        .resource-card:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Mis Recursos</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Mis Recursos Didácticos</h1>
                <p class="text-white-50 mb-0">Gestione sus herramientas personales y comparta materiales con otros docentes.</p>
            </div>
            
            <div class="btn-group gap-2">
                <a href="{{ route('recursos.compartidos') }}" class="btn btn-outline-success rounded-pill px-4">
                    <i class="bi bi-share-fill me-2"></i> Ver Compartidos
                </a>
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalCategoria">
                    <i class="bi bi-folder-plus me-2"></i> Nueva Categoría
                </button>
                @if (count($categorias) > 0)
                    <button type="button" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;" data-bs-toggle="modal" data-bs-target="#modalRecurso">
                        <i class="bi bi-plus-circle me-2"></i> Añadir Recurso
                    </button>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- FOLDERS & RESOURCES -->
        @if (count($categorias) > 0)
            @foreach ($categorias as $cat)
                <div class="folder-section">
                    <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-4">
                        <h4 class="fw-bold text-white m-0"><i class="bi bi-folder2-open text-warning me-2"></i>{{ $cat->nombre }}</h4>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-light border-0" onclick="editarCategoria({{ $cat->id }}, '{{ addslashes($cat->nombre) }}')" title="Editar Categoría">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('recursos.categoria.delete', $cat->id) }}" method="POST" onsubmit="return confirm('¿Seguro que desea eliminar esta categoría y todos sus recursos?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Eliminar Categoría">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="row g-3">
                        @if ($cat->recursos->count() > 0)
                            @foreach ($cat->recursos as $rec)
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="resource-card text-center text-white h-100 d-flex flex-column justify-content-between">
                                        <!-- Actions absolute positioned -->
                                        <div class="position-absolute top-0 end-0 p-2 d-flex gap-1.5" style="z-index: 10;">
                                            <button class="btn btn-xs btn-outline-light border-0 p-0 text-white-50" onclick='editarRecurso({{ json_encode($rec) }})'>
                                                <i class="bi bi-pencil-fill" style="font-size: 0.75rem;"></i>
                                            </button>
                                            <form action="{{ route('recursos.delete', $rec->id) }}" method="POST" onsubmit="return confirm('¿Eliminar recurso?')">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-danger border-0 p-0">
                                                    <i class="bi bi-trash3-fill" style="font-size: 0.75rem;"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <a href="{{ $rec->url }}" target="_blank" class="text-decoration-none text-white pt-2 d-block">
                                            <div class="mb-2">
                                                <i class="bi bi-link-45deg fs-2 text-success" style="color: var(--primary) !important;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1 small">{{ $rec->titulo }}</h6>
                                            <p class="text-white-50 x-small mb-2">{{ Str::limit($rec->descripcion, 50) }}</p>
                                        </a>

                                        <div>
                                            @if ($rec->es_compartido)
                                                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-20 rounded-pill px-2 py-0.5 x-small"><i class="bi bi-share-fill me-1"></i>Compartido</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-20 text-white-50 border border-secondary border-opacity-20 rounded-pill px-2 py-0.5 x-small"><i class="bi bi-lock-fill me-1"></i>Privado</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12">
                                <div class="text-center text-white-50 py-3 small fst-italic">Sin recursos didácticos registrados.</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="card glass-card p-5 text-center text-white-50">
                <i class="bi bi-folder-x display-4 d-block mb-3"></i>
                Aún no ha creado categorías ni recursos didácticos. ¡Empiece creando su primera categoría!
            </div>
        @endif

    </div>

    <!-- MODAL CATEGORÍA -->
    <div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark border border-secondary text-white rounded-4">
                <form action="{{ route('recursos.categoria.store') }}" method="POST" id="formCategoria">
                    @csrf
                    <input type="hidden" name="_method" id="categoriaMethod" value="POST">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title" id="modalCategoriaLabel">Gestionar Categoría</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-custom">Nombre de la Categoría</label>
                            <input type="text" name="nombre" id="catNombre" class="form-control form-control-custom w-100" placeholder="Ej: Lecturas recomendadas" required>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL RECURSO -->
    <div class="modal fade" id="modalRecurso" tabindex="-1" aria-labelledby="modalRecursoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark border border-secondary text-white rounded-4">
                <form action="{{ route('recursos.store') }}" method="POST" id="formRecurso">
                    @csrf
                    <input type="hidden" name="_method" id="recursoMethod" value="POST">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title" id="modalRecursoLabel">Gestionar Recurso</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-custom">Categoría *</label>
                            <select name="id_categoria" id="recCategoria" class="form-select form-select-custom w-100" required>
                                @foreach ($categorias as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Tipo de Recurso *</label>
                            <select name="tipo" id="recTipo" class="form-select form-select-custom w-100" required>
                                <option value="link">Enlace / URL</option>
                                <option value="snippet">Texto / Nota</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Título del Recurso *</label>
                            <input type="text" name="titulo" id="recTitulo" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Enlace (URL) *</label>
                            <input type="url" name="url" id="recUrl" class="form-control form-control-custom w-100" placeholder="https://ejemplo.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Descripción</label>
                            <textarea name="descripcion" id="recDesc" class="form-control form-control-custom w-100" rows="3"></textarea>
                        </div>
                        <div class="mb-3 form-check form-switch p-0 ps-5 mt-4">
                            <input class="form-check-input" type="checkbox" name="es_compartido" id="recCompartido" value="1">
                            <label class="form-check-label text-white-50 small" for="recCompartido">Compartir públicamente con otros profesores</label>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function editarCategoria(id, nombre) {
            $('#formCategoria').attr('action', "{{ route('recursos.categoria.update', ':id') }}".replace(':id', id));
            $('#categoriaMethod').val('POST'); // standard submit
            $('#catNombre').val(nombre);
            
            var modal = new bootstrap.Modal(document.getElementById('modalCategoria'));
            modal.show();
        }

        function editarRecurso(rec) {
            $('#formRecurso').attr('action', "{{ route('recursos.update', ':id') }}".replace(':id', rec.id));
            $('#recursoMethod').val('POST');
            $('#recCategoria').val(rec.id_categoria);
            $('#recTipo').val(rec.tipo);
            $('#recTitulo').val(rec.titulo);
            $('#recUrl').val(rec.url);
            $('#recDesc').val(rec.descripcion);
            $('#recCompartido').prop('checked', rec.es_compartido);

            var modal = new bootstrap.Modal(document.getElementById('modalRecurso'));
            modal.show();
        }
    </script>
@endsection
