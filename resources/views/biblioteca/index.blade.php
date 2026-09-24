@extends('layouts.app')

@section('title', 'Inbox BPM - Biblioteca de Medios')

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

        /* Asset Grid Item */
        .asset-card {
            background: rgba(30, 41, 59, 0.3);
            border: 1px solid var(--border-dark);
            border-radius: 1.25rem;
            overflow: hidden;
            transition: all 0.3s;
            position: relative;
        }
        .asset-card:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }

        .asset-preview-wrapper {
            position: relative;
            aspect-ratio: 16 / 10;
            background-color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-bottom: 1px solid var(--border-dark);
        }
        .asset-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        .asset-card:hover .asset-img {
            transform: scale(1.05);
        }

        .btn-clipboard {
            position: absolute;
            bottom: 10px;
            right: 10px;
            z-index: 10;
            background-color: rgba(15, 23, 42, 0.85);
            border: 1px solid var(--border-dark);
            color: var(--primary);
            font-size: 0.8rem;
            border-radius: 2rem;
            padding: 0.3rem 0.8rem;
            transition: all 0.2s;
        }
        .btn-clipboard:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-5 px-md-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Biblioteca de Medios</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Biblioteca de Medios</h1>
                <p class="text-white-50 mb-0">Gestione y organice imágenes de portadas y firmas académicas de los programas.</p>
            </div>
            
            <div>
                <button type="button" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;" data-bs-toggle="modal" data-bs-target="#modalSubirImagen">
                    <i class="bi bi-plus-circle me-2"></i> Subir Imagen
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- FILTROS -->
        <div class="card glass-card p-4 text-white mb-4">
            <form action="{{ route('biblioteca.index') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label-custom">Programa Asociado</label>
                    <select name="id_programa" class="form-select form-select-custom w-100" onchange="this.form.submit()">
                        <option value="">--- Todos los Programas ---</option>
                        @foreach ($programas as $prog)
                            <option value="{{ $prog->id_programa }}" {{ $prog->id_programa == $id_programa ? 'selected' : '' }}>[{{ $prog->categoria }}] {{ $prog->nombre_programa }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label-custom">Categoría Académica</label>
                    <select name="categoria_academica" class="form-select form-select-custom w-100" onchange="this.form.submit()">
                        <option value="">--- Todas las Categorías ---</option>
                        <option value="portada" {{ $categoria_academica === 'portada' ? 'selected' : '' }}>Portada Principal (Banner 1200x400)</option>
                        <option value="miniatura" {{ $categoria_academica === 'miniatura' ? 'selected' : '' }}>Miniatura Moodle (Tarjeta Curso 600x220)</option>
                        <option value="firma" {{ $categoria_academica === 'firma' ? 'selected' : '' }}>Firma Autorizada</option>
                        <option value="fondo" {{ $categoria_academica === 'fondo' ? 'selected' : '' }}>Fondo de Certificados</option>
                        <option value="general" {{ $categoria_academica === 'general' ? 'selected' : '' }}>General / Medios</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('biblioteca.index') }}" class="btn btn-outline-light w-100 rounded-pill py-2">
                        <i class="bi bi-eraser-fill me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- ASSETS GRID -->
        <div class="row g-4">
            @if (count($recursos) > 0)
                @foreach ($recursos as $r)
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <div class="asset-card text-white h-100 d-flex flex-column justify-content-between">
                            
                            <div class="asset-preview-wrapper">
                                <img src="{{ asset($r->ruta_archivo) }}" alt="{{ $r->nombre }}" class="asset-img">
                                <button onclick="copyToClipboard('{{ $r->ruta_archivo }}')" class="btn btn-clipboard shadow-sm">
                                    <i class="bi bi-link-45deg me-1"></i> Copiar URL
                                </button>
                            </div>

                            <div class="p-3">
                                <span class="badge bg-secondary bg-opacity-25 text-white-50 x-small mb-1 text-uppercase" style="font-size: 0.6rem;">{{ $r->categoria_academica ?: 'General' }}</span>
                                <h6 class="fw-bold text-white small mb-1" style="word-break: break-all;">{{ $r->nombre }}</h6>
                                <p class="text-white-50 x-small mb-3" style="font-size: 0.65rem;">{{ $r->programa->nombre_programa ?? 'Sin programa específico' }}</p>
                                
                                <form action="{{ route('biblioteca.delete', $r->id) }}" method="POST" onsubmit="return confirm('¿Seguro que desea eliminar esta imagen de la biblioteca corporativa?')">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-outline-danger w-100 rounded-pill py-1">
                                        <i class="bi bi-trash3-fill me-1"></i> Eliminar
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12">
                    <div class="card glass-card p-5 text-center text-white-50">
                        <i class="bi bi-images display-4 d-block mb-3"></i>
                        No se encontraron imágenes en la biblioteca corporativa con los filtros seleccionados.
                    </div>
                </div>
            @endif
        </div>

        @if ($recursos->hasPages())
            <div class="mt-4">
                {{ $recursos->links() }}
            </div>
        @endif

    </div>

    <!-- SUBIR MULTIMEDIA MODAL -->
    <div class="modal fade" id="modalSubirImagen" tabindex="-1" aria-labelledby="modalSubirImagenLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark border border-secondary text-white rounded-4">
                <form action="{{ route('biblioteca.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title" id="modalSubirImagenLabel"><i class="bi bi-upload text-success me-2"></i> Subir Imagen a Biblioteca</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-custom">Nombre / Descripción Corta *</label>
                            <input type="text" name="nombre_imagen" class="form-control form-control-custom w-100" placeholder="Ej: Portada Maestría Educación" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Programa Académico Asociado (Opcional)</label>
                            <select name="id_programa" class="form-select form-select-custom w-100">
                                <option value="">--- Ningún Programa ---</option>
                                @foreach ($programas as $prog)
                                    <option value="{{ $prog->id_programa }}">[{{ $prog->categoria }}] {{ $prog->nombre_programa }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Categoría de Imagen *</label>
                            <select name="categoria_academica" class="form-select form-select-custom w-100" required>
                                <option value="portada" selected>Portada Principal (Banner 1200x400)</option>
                                <option value="miniatura">Miniatura Moodle (Tarjeta Curso 600x220)</option>
                                <option value="firma">Firma Autorizada</option>
                                <option value="fondo">Fondo de Certificados</option>
                                <option value="general">General / Medios</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label-custom">Seleccionar Archivo de Imagen *</label>
                            <input type="file" name="imagen_biblioteca" class="form-control form-control-custom w-100" accept="image/*" required>
                            <span class="text-white-50 x-small mt-1 d-block">Extensiones permitidas: JPG, JPEG, PNG, WEBP (Max: 5MB).</span>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Subir Imagen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function copyToClipboard(url) {
            navigator.clipboard.writeText(url);
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'URL del activo copiado', showConfirmButton: false, timer: 2000 });
        }
    </script>
@endsection
