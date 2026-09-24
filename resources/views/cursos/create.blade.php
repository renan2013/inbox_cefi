@extends('layouts.app')

@section('title', 'Inbox BPM - Registrar Nuevo Curso / Materia')

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

        .pdf-upload-box {
            background-color: rgba(15, 23, 42, 0.3);
            border: 2px dashed var(--border-dark);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .pdf-upload-box:hover {
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
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cursos.index') }}" class="text-decoration-none text-white-50">Plan de Estudios</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Nuevo Curso</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Añadir Plan de Estudios</h1>
                <p class="text-white-50 mb-0">Registre un nuevo curso o materia y asócielo con una carrera académica.</p>
            </div>
            <div>
                <a href="{{ route('cursos.index') }}" class="btn btn-outline-light rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Cursos
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
                <form action="{{ route('cursos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- SECCIÓN 1: ASOCIACIÓN Y CÓDIGOS -->
                    <div class="section-divider">
                        <i class="bi bi-link-45deg"></i> Vinculación del Curso
                    </div>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label for="id_programa" class="form-label-custom">Carrera / Programa Académico</label>
                            <select name="id_programa" id="id_programa" class="form-select form-select-custom w-100" required>
                                <option value="">Seleccione el Programa...</option>
                                @foreach ($programas as $p)
                                    <option value="{{ $p->id_programa }}" {{ old('id_programa') == $p->id_programa ? 'selected' : '' }}>{{ $p->nombre_programa }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="cuatrimestre" class="form-label-custom">Nivel / Cuatrimestre</label>
                            <select name="cuatrimestre" id="cuatrimestre" class="form-select form-select-custom w-100" required>
                                <option value="I Cuatrimestre" {{ old('cuatrimestre') === 'I Cuatrimestre' ? 'selected' : '' }}>I Cuatrimestre</option>
                                <option value="II Cuatrimestre" {{ old('cuatrimestre') === 'II Cuatrimestre' ? 'selected' : '' }}>II Cuatrimestre</option>
                                <option value="III Cuatrimestre" {{ old('cuatrimestre') === 'III Cuatrimestre' ? 'selected' : '' }}>III Cuatrimestre</option>
                                <option value="IV Cuatrimestre" {{ old('cuatrimestre') === 'IV Cuatrimestre' ? 'selected' : '' }}>IV Cuatrimestre</option>
                                <option value="V Cuatrimestre" {{ old('cuatrimestre') === 'V Cuatrimestre' ? 'selected' : '' }}>V Cuatrimestre</option>
                                <option value="VI Cuatrimestre" {{ old('cuatrimestre') === 'VI Cuatrimestre' ? 'selected' : '' }}>VI Cuatrimestre</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <label for="codigo" class="form-label-custom">Código del Curso</label>
                            <input type="text" name="codigo" id="codigo" class="form-control form-control-custom w-100" placeholder="Ej: TEOL-101" required value="{{ old('codigo') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="materia" class="form-label-custom">Nombre de la Materia</label>
                            <input type="text" name="materia" id="materia" class="form-control form-control-custom w-100" placeholder="Ej: Introducción al Antiguo Testamento" required value="{{ old('materia') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="creditos" class="form-label-custom">Créditos</label>
                            <input type="number" name="creditos" id="creditos" class="form-control form-control-custom w-100" min="0" value="{{ old('creditos', '4') }}">
                        </div>
                    </div>

                    <!-- SECCIÓN 2: REQUISITOS Y METAS -->
                    <div class="section-divider">
                        <i class="bi bi-list-task"></i> Contenido y Requisitos del Curso
                    </div>

                    <div class="mb-4">
                        <label for="requisitos" class="form-label-custom">Requisitos del Curso (Co-requisitos / Pre-requisitos)</label>
                        <input type="text" name="requisitos" id="requisitos" class="form-control form-control-custom w-100" placeholder="Ej: Bachillerato completo o TEOL-100" value="{{ old('requisitos') }}">
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label for="objetivo_general" class="form-label-custom">Objetivo General</label>
                            <textarea name="objetivo_general" id="objetivo_general" class="form-control form-control-custom w-100" rows="4" placeholder="Describa el objetivo principal de la materia...">{{ old('objetivo_general') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="objetivos_especificos" class="form-label-custom">Objetivos Específicos</label>
                            <textarea name="objetivos_especificos" id="objetivos_especificos" class="form-control form-control-custom w-100" rows="4" placeholder="Escriba los objetivos específicos divididos por líneas o viñetas...">{{ old('objetivos_especificos') }}</textarea>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: SÍLABO / PDF ADJUNTO -->
                    <div class="section-divider">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Programa del Curso (Sílabo)
                    </div>

                    <div class="pdf-upload-box mb-5">
                        <label class="form-label-custom d-block">Archivo del Sílabo (PDF)</label>
                        <i class="bi bi-file-earmark-arrow-up text-white-50 display-5 d-block mb-3"></i>
                        <div class="d-flex justify-content-center">
                            <input type="file" name="adjunto_pdf" class="form-control form-control-custom" style="max-width: 400px;" accept="application/pdf">
                        </div>
                        <div class="form-text text-white-50 small mt-2">Formatos permitidos: PDF. Tamaño máximo sugerido: 5MB.</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-submit py-3 shadow">
                            <i class="bi bi-check-circle-fill me-2"></i> Añadir Materia al Plan de Estudios
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
@endsection
