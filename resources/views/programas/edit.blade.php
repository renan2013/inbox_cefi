@extends('layouts.app')

@section('title', 'Inbox BPM - Editar Programa: ' . $programa->nombre_programa)

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
        }

        .section-divider {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 2px solid var(--border-dark);
            padding-bottom: 0.75rem;
        }

        .form-control-custom, .form-select-custom {
            background-color: rgba(15, 23, 42, 0.5);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.7rem 1rem;
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
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .cost-card {
            background-color: rgba(95, 178, 48, 0.02);
            border: 1px dashed var(--primary);
            border-radius: 1.25rem;
            padding: 2rem;
        }

        .img-upload-box {
            background-color: rgba(15, 23, 42, 0.3);
            border: 2px dashed var(--border-dark);
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.3s ease;
            text-align: center;
        }

        .img-upload-box:hover {
            border-color: var(--primary);
            background-color: rgba(15, 23, 42, 0.5);
        }

        .btn-submit {
            background-color: var(--primary);
            border: none;
            color: white;
            padding: 0.9rem;
            border-radius: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(95, 178, 48, 0.2);
        }

        .img-current-preview {
            max-height: 80px;
            max-width: 100%;
            border-radius: 0.5rem;
            object-fit: cover;
            margin-bottom: 0.75rem;
            border: 1px solid var(--border-dark);
        }

        .btn-outline-custom {
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #f8fafc;
            border-radius: 50rem;
            padding: 0.5rem 1.25rem;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-outline-custom:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        /* Soporte para Modo Día (Light Theme) */
        [data-theme="light"] .page-header {
            background: #ffffff !important;
            border-color: #cbd5e1 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05) !important;
        }

        [data-theme="light"] .page-header h1 {
            color: #0f172a !important;
        }

        [data-theme="light"] .page-header p {
            color: #64748b !important;
        }

        [data-theme="light"] .glass-card {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05) !important;
        }

        [data-theme="light"] .form-label-custom {
            color: #334155 !important;
        }

        [data-theme="light"] .form-control-custom,
        [data-theme="light"] .form-select-custom {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        [data-theme="light"] .form-control-custom:focus,
        [data-theme="light"] .form-select-custom:focus {
            background-color: #ffffff !important;
            border-color: var(--primary) !important;
            color: #0f172a !important;
        }

        [data-theme="light"] .form-control-custom::placeholder {
            color: #94a3b8 !important;
        }

        [data-theme="light"] .cost-card {
            background-color: rgba(95, 178, 48, 0.04) !important;
            border-color: #16a34a !important;
        }

        [data-theme="light"] .img-upload-box {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
        }

        [data-theme="light"] .img-upload-box:hover {
            border-color: var(--primary) !important;
            background-color: rgba(95, 178, 48, 0.05) !important;
        }

        [data-theme="light"] .section-divider {
            border-bottom-color: #e2e8f0 !important;
            color: #15803d !important;
        }

        [data-theme="light"] .btn-outline-custom {
            border-color: #cbd5e1;
            color: #334155;
        }

        [data-theme="light"] .btn-outline-custom:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('programas.index') }}" class="text-decoration-none text-white-50">Programas Académicos</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Editar Programa</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Editar Programa: {{ $programa->nombre_programa }}</h1>
                <p class="text-white-50 mb-0">Actualice la información general, aranceles de cobro y recursos multimedia del programa.</p>
            </div>
            <div>
                <a href="{{ route('programas.index') }}" class="btn btn-outline-custom">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Programas
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 rounded-4 mb-4" style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2) !important;">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card glass-card">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('programas.update', $programa->id_programa) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- SECCIÓN 1: INFORMACIÓN BÁSICA -->
                    <div class="section-divider">
                        <i class="bi bi-info-circle-fill"></i> Información Básica
                    </div>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-8">
                            <label for="nombre_programa" class="form-label-custom">Nombre del Programa / Carrera</label>
                            <input type="text" name="nombre_programa" id="nombre_programa" class="form-control form-control-custom w-100" placeholder="Ej: Bachillerato en Teología" required value="{{ old('nombre_programa', $programa->nombre_programa) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="categoria" class="form-label-custom">Categoría Académica</label>
                            <select name="categoria" id="categoria" class="form-select form-select-custom w-100" required>
                                <option value="">Seleccione...</option>
                                @php $catActual = old('categoria', $programa->categoria); @endphp
                                <option value="Bachillerato" {{ $catActual === 'Bachillerato' ? 'selected' : '' }}>Bachillerato</option>
                                <option value="Licenciatura" {{ $catActual === 'Licenciatura' ? 'selected' : '' }}>Licenciatura</option>
                                <option value="Maestría" {{ $catActual === 'Maestría' ? 'selected' : '' }}>Maestría</option>
                                <option value="Doctorado" {{ $catActual === 'Doctorado' ? 'selected' : '' }}>Doctorado</option>
                                <option value="Carrera Técnica" {{ $catActual === 'Carrera Técnica' ? 'selected' : '' }}>Carrera Técnica</option>
                                <option value="Curso Libre" {{ $catActual === 'Curso Libre' ? 'selected' : '' }}>Curso Libre</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="informacion" class="form-label-custom">Resumen Informativo (Portada)</label>
                        <textarea name="informacion" id="informacion" class="form-control form-control-custom w-100" rows="3" placeholder="Información descriptiva para la oferta académica...">{{ old('informacion', $programa->informacion) }}</textarea>
                    </div>

                    <div class="mb-5">
                        <label for="perfil" class="form-label-custom">Perfil Profesional</label>
                        <textarea name="perfil" id="perfil" class="form-control form-control-custom w-100" rows="3" placeholder="Habilidades y capacidades del egresado...">{{ old('perfil', $programa->perfil) }}</textarea>
                    </div>

                    <!-- SECCIÓN 2: ESTRUCTURA DE COSTOS -->
                    <div class="section-divider">
                        <i class="bi bi-wallet2"></i> Configuración Financiera (Costos)
                    </div>

                    <div class="cost-card mb-5">
                        <div class="row g-4">
                            <div class="col-md-3">
                                <label for="costo_materia" class="form-label-custom">Costo por Materia</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-white-50">₡</span>
                                    <input type="number" step="0.01" name="costo_materia" id="costo_materia" class="form-control form-control-custom" required value="{{ old('costo_materia', $programa->costo_materia) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="costo_matricula" class="form-label-custom">Costo de Matrícula</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-white-50">₡</span>
                                    <input type="number" step="0.01" name="costo_matricula" id="costo_matricula" class="form-control form-control-custom" required value="{{ old('costo_matricula', $programa->costo_matricula) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="costo_biblioteca" class="form-label-custom">Costo de Biblioteca</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-white-50">₡</span>
                                    <input type="number" step="0.01" name="costo_biblioteca" id="costo_biblioteca" class="form-control form-control-custom" required value="{{ old('costo_biblioteca', $programa->costo_biblioteca) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="costo_inscripcion_unica" class="form-label-custom">Inscripción Única</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-white-50">₡</span>
                                    <input type="number" step="0.01" name="costo_inscripcion_unica" id="costo_inscripcion_unica" class="form-control form-control-custom" required value="{{ old('costo_inscripcion_unica', $programa->costo_inscripcion_unica) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: ARCHIVOS PORTADAS E IMÁGENES -->
                    <div class="section-divider">
                        <i class="bi bi-images"></i> Recursos Visuales (Imágenes)
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-md-6 col-lg-3">
                            <div class="img-upload-box h-100">
                                <label class="form-label-custom d-block">Imagen Principal</label>
                                @if(!empty($programa->imagen_principal) && file_exists(public_path($programa->imagen_principal)))
                                    <img src="{{ asset($programa->imagen_principal) }}" class="img-current-preview d-block mx-auto" alt="Principal">
                                @else
                                    <i class="bi bi-image text-white-50 display-6 d-block mb-3"></i>
                                @endif
                                <input type="file" name="imagen_principal" class="form-control form-control-custom w-100">
                                <small class="text-white-50 d-block mt-1">Subir para reemplazar</small>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="img-upload-box h-100">
                                <label class="form-label-custom d-block">Imagen Secundaria</label>
                                @if(!empty($programa->imagen_secundaria) && file_exists(public_path($programa->imagen_secundaria)))
                                    <img src="{{ asset($programa->imagen_secundaria) }}" class="img-current-preview d-block mx-auto" alt="Secundaria">
                                @else
                                    <i class="bi bi-image text-white-50 display-6 d-block mb-3"></i>
                                @endif
                                <input type="file" name="imagen_secundaria" class="form-control form-control-custom w-100">
                                <small class="text-white-50 d-block mt-1">Subir para reemplazar</small>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="img-upload-box h-100">
                                <label class="form-label-custom d-block">Imagen Encabezado</label>
                                @if(!empty($programa->imagen_encabezado) && file_exists(public_path($programa->imagen_encabezado)))
                                    <img src="{{ asset($programa->imagen_encabezado) }}" class="img-current-preview d-block mx-auto" alt="Encabezado">
                                @else
                                    <i class="bi bi-image-fill text-white-50 display-6 d-block mb-3"></i>
                                @endif
                                <input type="file" name="imagen_encabezado" class="form-control form-control-custom w-100">
                                <small class="text-white-50 d-block mt-1">Subir para reemplazar</small>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="img-upload-box h-100">
                                <label class="form-label-custom d-block">Imagen Footer</label>
                                @if(!empty($programa->imagen_footer) && file_exists(public_path($programa->imagen_footer)))
                                    <img src="{{ asset($programa->imagen_footer) }}" class="img-current-preview d-block mx-auto" alt="Footer">
                                @else
                                    <i class="bi bi-image-fill text-white-50 display-6 d-block mb-3"></i>
                                @endif
                                <input type="file" name="imagen_footer" class="form-control form-control-custom w-100">
                                <small class="text-white-50 d-block mt-1">Subir para reemplazar</small>
                            </div>
                        </div>
                    </div>

                    <!-- DETALLES COMPLEMENTARIOS -->
                    <div class="section-divider">
                        <i class="bi bi-file-earmark-ruled"></i> Regulaciones y Detalles
                    </div>
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label for="detalles_programa" class="form-label-custom">Detalles del Plan / Requisitos</label>
                            <textarea name="detalles_programa" id="detalles_programa" class="form-control form-control-custom w-100" rows="4" placeholder="Regulaciones académicas específicas...">{{ old('detalles_programa', $programa->detalles_programa) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="normas_netiqueta" class="form-label-custom">Normas de Netiqueta</label>
                            <textarea name="normas_netiqueta" id="normas_netiqueta" class="form-control form-control-custom w-100" rows="4" placeholder="Normas de convivencia virtual sugeridas...">{{ old('normas_netiqueta', $programa->normas_netiqueta) }}</textarea>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-submit py-3 shadow">
                            <i class="bi bi-check-circle-fill me-2"></i> Guardar Cambios del Programa
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
@endsection
