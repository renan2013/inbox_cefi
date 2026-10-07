@extends('layouts.app')

@section('title', 'Inbox BPM - Crear Expediente Digital')

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
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 2rem;
        }

        #user-search-card {
            overflow: visible !important;
        }

        .step-header {
            background-color: rgba(255, 255, 255, 0.02);
            border-bottom: 2px solid rgba(95, 178, 48, 0.15);
            padding: 1.25rem 1.5rem;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .step-badge {
            background-color: var(--primary);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            box-shadow: 0 4px 8px rgba(95, 178, 48, 0.2);
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

        .student-selected-banner {
            border-radius: 1.5rem;
            border: 2px solid rgba(95, 178, 48, 0.4);
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .student-avatar {
            width: 54px;
            height: 54px;
            background: rgba(95, 178, 48, 0.25);
            border: 2px solid #5fb230;
            color: #ffffff;
            font-size: 1.4rem;
        }

        .student-title {
            color: #f8fafc;
        }

        .student-meta {
            color: #94a3b8;
        }

        .student-meta strong {
            color: #f8fafc;
        }

        .badge-student-id {
            background-color: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
            font-size: 0.75rem;
        }

        .badge-student-status {
            background-color: rgba(95, 178, 48, 0.15);
            color: #5fb230;
            border: 1px solid rgba(95, 178, 48, 0.3);
            font-size: 0.75rem;
        }

        .btn-change-user {
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #f8fafc;
            background: rgba(255, 255, 255, 0.05);
            transition: all 0.2s;
        }

        .btn-change-user:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border-color: #ffffff;
        }

        .data-form-header {
            color: #f8fafc;
        }

        .page-title {
            color: #ffffff;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.65);
        }

        .breadcrumb-link {
            color: rgba(255, 255, 255, 0.6);
        }

        .breadcrumb-active {
            color: #ffffff;
        }

        .btn-header-action {
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #f8fafc;
            background: transparent;
            transition: all 0.2s;
        }

        .btn-header-action:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border-color: #ffffff;
        }

        .form-check-label {
            color: #cbd5e1;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Tarjetas de subida de documentación */
        .doc-upload-box {
            background: rgba(15, 23, 42, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            padding: 1.25rem;
            transition: all 0.25s ease;
            height: 100%;
        }

        .doc-upload-box:hover {
            border-color: rgba(95, 178, 48, 0.4);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        .doc-upload-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #f1f5f9;
        }

        .doc-upload-desc {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .doc-existing-badge {
            background: rgba(34, 197, 94, 0.12);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.25);
            font-size: 0.78rem;
            border-radius: 0.5rem;
            padding: 0.35rem 0.65rem;
        }

        .doc-existing-link {
            color: #38bdf8;
            text-decoration: underline;
            font-weight: 500;
        }

        .doc-existing-link:hover {
            color: #7dd3fc;
        }

        /* ================= LIGHT MODE OVERRIDES ================= */
        [data-theme="light"] .page-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
        }

        [data-theme="light"] .page-title {
            color: #0f172a !important;
        }

        [data-theme="light"] .page-subtitle {
            color: #64748b !important;
        }

        [data-theme="light"] .breadcrumb-link {
            color: #64748b !important;
        }

        [data-theme="light"] .breadcrumb-active {
            color: #0f172a !important;
        }

        [data-theme="light"] .btn-header-action {
            border-color: #cbd5e1 !important;
            color: #334155 !important;
            background: #ffffff !important;
        }

        [data-theme="light"] .btn-header-action:hover {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-color: #94a3b8 !important;
        }

        [data-theme="light"] .glass-card {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
        }

        [data-theme="light"] .student-selected-banner {
            background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%) !important;
            border: 2px solid rgba(95, 178, 48, 0.35) !important;
            box-shadow: 0 6px 20px rgba(95, 178, 48, 0.08) !important;
        }

        [data-theme="light"] .student-avatar {
            background: #dcfce7 !important;
            border-color: #22c55e !important;
            color: #15803d !important;
        }

        [data-theme="light"] .student-title {
            color: #0f172a !important;
        }

        [data-theme="light"] .student-meta {
            color: #475569 !important;
        }

        [data-theme="light"] .student-meta strong {
            color: #0f172a !important;
        }

        [data-theme="light"] .badge-student-id {
            background-color: #eff6ff !important;
            color: #1d4ed8 !important;
            border-color: #bfdbfe !important;
        }

        [data-theme="light"] .badge-student-status {
            background-color: #ecfdf5 !important;
            color: #047857 !important;
            border-color: #a7f3d0 !important;
        }

        [data-theme="light"] .btn-change-user {
            border-color: #cbd5e1 !important;
            color: #334155 !important;
            background: #ffffff !important;
        }

        [data-theme="light"] .btn-change-user:hover {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-color: #94a3b8 !important;
        }

        [data-theme="light"] .data-form-header {
            color: #0f172a !important;
        }

        [data-theme="light"] .step-header {
            background-color: #f8fafc !important;
            border-bottom: 2px solid rgba(95, 178, 48, 0.2) !important;
            color: #15803d !important;
        }

        [data-theme="light"] .legend-custom {
            color: #15803d !important;
            border-bottom-color: #e2e8f0 !important;
        }

        [data-theme="light"] .form-label-custom {
            color: #475569 !important;
        }

        [data-theme="light"] .form-control-custom,
        [data-theme="light"] .form-select-custom {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        [data-theme="light"] .form-control-custom:focus,
        [data-theme="light"] .form-select-custom:focus {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15) !important;
        }

        [data-theme="light"] .form-control-custom::placeholder {
            color: #94a3b8 !important;
        }

        [data-theme="light"] .form-check-label {
            color: #334155 !important;
        }

        [data-theme="light"] .form-check-input:not(:checked) {
            background-color: #e2e8f0 !important;
            border-color: #cbd5e1 !important;
        }

        [data-theme="light"] .doc-upload-box {
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03) !important;
        }

        [data-theme="light"] .doc-upload-box:hover {
            border-color: #22c55e !important;
            box-shadow: 0 6px 16px rgba(34, 197, 94, 0.12) !important;
        }

        [data-theme="light"] .doc-upload-title {
            color: #0f172a !important;
        }

        [data-theme="light"] .doc-upload-desc {
            color: #64748b !important;
        }

        [data-theme="light"] .doc-existing-badge {
            background: #dcfce7 !important;
            color: #15803d !important;
            border-color: #86efac !important;
        }

        [data-theme="light"] .doc-existing-link {
            color: #0284c7 !important;
        }

        .form-label-custom {
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .legend-custom {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.05rem;
            border-bottom: 2px solid var(--border-dark);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            width: 100%;
        }

        .btn-submit {
            background-color: var(--primary);
            border: none;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(95, 178, 48, 0.25);
        }

        #user-search-results {
            background-color: var(--card-dark) !important;
            border: 1px solid var(--border-dark);
        }

        .result-item {
            background-color: var(--card-dark) !important;
            border: 1px solid var(--border-dark);
            transition: all 0.2s;
            cursor: pointer;
            color: #e2e8f0;
        }

        .result-item:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Dark themed transparent table list */
        .table-custom {
            margin-bottom: 0;
            background-color: transparent !important;
        }
        .table-custom td, .table-custom th {
            background-color: transparent !important;
            color: #e2e8f0 !important;
            border-bottom: 1px solid var(--border-dark);
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none breadcrumb-link"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active breadcrumb-active" aria-current="page">Nuevo Expediente</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1 page-title">Crear Expediente Digital</h1>
                <p class="mb-0 page-subtitle">Proceso de matriculación y registro de información académica del estudiante.</p>
            </div>
            <div>
                <a href="{{ route('expedientes.index') }}" class="btn btn-header-action rounded-pill px-4">
                    <i class="bi bi-folder2-open me-1"></i> Gestionar Expedientes
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- Sincronización Moodle Warning (Oculto para CEFI; disponible para clientes con módulo de sincronización activo) -->
        @if(config('services.moodle.sync_enabled', false))
        <div class="alert alert-warning border-0 rounded-4 d-flex align-items-start mb-4" style="background-color: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2) !important;">
            <i class="bi bi-info-circle-fill fs-4 me-3 mt-1"></i>
            <div>
                <strong>Nota Importante:</strong> Antes de crear el expediente digital, es necesario crear el usuario en <strong><a href="{{ config('cliente.campus_virtual', '#') }}" target="_blank" style="color: inherit; text-decoration: underline;">{{ config('cliente.nombre', 'CEFI') }} Virtual</a></strong>, ya que los usuarios del campus virtual se sincronizan con Inbox.
            </div>
        </div>
        @endif

        @if(isset($usuarioPreseleccionado) && $usuarioPreseleccionado)
            <!-- TARJETA DEL ESTUDIANTE PRESELECCIONADO (Sin buscador) -->
            <div class="student-selected-banner mb-4">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="student-avatar rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm flex-shrink-0">
                            {{ strtoupper(substr($usuarioPreseleccionado->apellidos ?: $usuarioPreseleccionado->nombre, 0, 1)) }}
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <h3 class="h4 fw-bold student-title mb-0">
                                    {{ $usuarioPreseleccionado->nombre }} {{ $usuarioPreseleccionado->apellidos }}
                                </h3>
                                <span class="badge badge-student-id">
                                    ID #{{ str_pad($usuarioPreseleccionado->id, 4, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="badge badge-student-status">
                                    <i class="bi bi-file-earmark-plus me-1"></i> Creando Expediente
                                </span>
                            </div>
                            <div class="student-meta small d-flex align-items-center gap-3 flex-wrap">
                                <span><i class="bi bi-envelope me-1 text-primary"></i>{{ $usuarioPreseleccionado->email }}</span>
                                @if(!empty($usuarioPreseleccionado->cedula))
                                    <span><i class="bi bi-card-text me-1 text-info"></i>Cédula: <strong>{{ $usuarioPreseleccionado->cedula }}</strong></span>
                                @endif
                                @if(!empty($usuarioPreseleccionado->telefono))
                                    <span><i class="bi bi-telephone me-1 text-success"></i>Tel: <strong>{{ $usuarioPreseleccionado->telefono }}</strong></span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-change-user rounded-pill px-3 py-2 btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Cambiar Usuario
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- PASO 1: BUSCADOR (Solo si NO se seleccionó usuario previamente) -->
            <div class="glass-card" id="user-search-card">
                <div class="step-header">
                    <div class="step-badge">1</div> Identificación del Estudiante
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-end g-4">
                        <div class="col-md-7 position-relative">
                            <label for="user-search" class="form-label-custom"><i class="bi bi-search"></i> Buscar en el sistema:</label>
                            <div class="input-group">
                                <input type="text" id="user-search" class="form-control form-control-custom w-100" placeholder="Escriba nombre, email o cédula...">
                                <button id="reset-user-search" class="btn btn-outline-danger border-2 ms-2 rounded-3" type="button" style="display: none;">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                            <div id="user-search-results" class="list-group position-absolute w-100 shadow" style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;"></div>
                        </div>
                        <div class="col-md-5">
                            <div class="p-3 rounded-4" style="background-color: rgba(95, 178, 48, 0.03); border: 1px dashed rgba(95, 178, 48, 0.2);">
                                <small class="text-white-50"><i class="bi bi-info-circle me-1"></i> Ingrese al menos 3 letras o busque por cédula para seleccionar al estudiante.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- FORMULARIO DETALLADO DEL EXPEDIENTE COMPLETO -->
        <div id="expediente-data-section" class="glass-card" style="{{ (isset($usuarioPreseleccionado) && $usuarioPreseleccionado) ? '' : 'display: none;' }}">
            <div class="step-header">
                <div class="step-badge"><i class="bi bi-folder-check"></i></div> 
                {{ (isset($exp) && $exp && $exp->id_expediente) ? 'Edición Oficial del Expediente Digital' : 'Formulario Oficial de Expediente Digital' }}
            </div>
            <div class="card-body p-4 p-md-5">
                @php
                    $exp = $usuarioPreseleccionado ? $usuarioPreseleccionado->expediente : null;
                    $archCedula = $exp?->archivos?->firstWhere('tipo_documento', 'cedula');
                    $archTituloSec = $exp?->archivos?->firstWhere('tipo_documento', 'titulo_secundaria');
                    $archTituloUniv = $exp?->archivos?->firstWhere('tipo_documento', 'titulo_universitario');
                    $archNotas = $exp?->archivos?->firstWhere('tipo_documento', 'certificacion_notas');
                    $archFoto = $exp?->archivos?->firstWhere('tipo_documento', 'fotografia');
                @endphp

                <h4 class="data-form-header fw-bold mb-4" id="data-form-header">
                    @if(isset($usuarioPreseleccionado) && $usuarioPreseleccionado)
                        {{ (isset($exp) && $exp && $exp->id_expediente) ? 'Editar Expediente Digital de:' : 'Expediente Digital para:' }} {{ $usuarioPreseleccionado->nombre }} {{ $usuarioPreseleccionado->apellidos }}
                    @endif
                </h4>
                
                <form id="expediente-form" method="post" action="{{ route('expedientes.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="student_id" name="id_usuario" value="{{ $usuarioPreseleccionado->id ?? '' }}">

                    <!-- 1. GRADO E INTERÉS ACADÉMICO -->
                    <fieldset class="mb-5">
                        <div class="legend-custom"><i class="bi bi-mortarboard-fill me-2"></i>1. Grado e Interés Académico</div>
                        <div class="row g-4">
                            <div class="col-xl-4 col-md-6">
                                <label for="grado_a_matricular" class="form-label-custom">Grado a Matricular</label>
                                <select name="grado_a_matricular" id="grado_a_matricular" class="form-select form-select-custom w-100">
                                    @php $gradoActual = old('grado_a_matricular', $exp->grado_a_matricular ?? 'Bachillerato'); @endphp
                                    <option value="Bachillerato" {{ $gradoActual == 'Bachillerato' ? 'selected' : '' }}>Bachillerato</option>
                                    <option value="Licenciatura" {{ $gradoActual == 'Licenciatura' ? 'selected' : '' }}>Licenciatura</option>
                                    <option value="Maestria" {{ $gradoActual == 'Maestria' ? 'selected' : '' }}>Maestría</option>
                                    <option value="Doctorado" {{ $gradoActual == 'Doctorado' ? 'selected' : '' }}>Doctorado</option>
                                    <option value="Tecnico" {{ $gradoActual == 'Tecnico' ? 'selected' : '' }}>Técnico</option>
                                    <option value="Curso Libre" {{ $gradoActual == 'Curso Libre' ? 'selected' : '' }}>Curso Libre</option>
                                </select>
                            </div>
                            <div class="col-xl-4 col-md-6">
                                <label for="especialidad_deseada" class="form-label-custom">Especialidad o Carrera Deseada</label>
                                <select name="especialidad_deseada" id="especialidad_deseada" class="form-select form-select-custom w-100">
                                    @php $espActual = old('especialidad_deseada', $exp->especialidad_deseada ?? ''); @endphp
                                    @foreach ($programas as $prog)
                                        <option value="{{ $prog->nombre_programa }}" {{ $espActual == $prog->nombre_programa ? 'selected' : '' }}>{{ $prog->nombre_programa }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-6">
                                <label for="fecha_registro" class="form-label-custom">Fecha de Registro</label>
                                <input type="date" name="fecha_registro" id="fecha_registro" class="form-control form-control-custom w-100" value="{{ old('fecha_registro', ($exp && $exp->fecha_registro) ? $exp->fecha_registro->format('Y-m-d') : date('Y-m-d')) }}">
                            </div>
                            <div class="col-xl-2 col-md-6">
                                <label for="estado" class="form-label-custom">Estado del Expediente</label>
                                <select name="estado" id="estado" class="form-select form-select-custom w-100">
                                    @php $estActual = old('estado', $exp->estado ?? 'Aprobado'); @endphp
                                    <option value="Aprobado" {{ $estActual == 'Aprobado' ? 'selected' : '' }}>✅ Aprobado</option>
                                    <option value="Pendiente" {{ $estActual == 'Pendiente' ? 'selected' : '' }}>⏳ Pendiente</option>
                                    <option value="Rechazado" {{ $estActual == 'Rechazado' ? 'selected' : '' }}>❌ Rechazado</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <!-- 2. DATOS DE IDENTIDAD Y PERSONALES -->
                    <fieldset class="mb-5">
                        <div class="legend-custom"><i class="bi bi-person-badge-fill me-2"></i>2. Datos de Identidad y Personales</div>
                        <div class="row g-4">
                            <div class="col-xl-3 col-md-6">
                                <label class="form-label-custom">Cédula o Identificación</label>
                                <input type="text" name="cedula_residencia" class="form-control form-control-custom w-100" value="{{ old('cedula_residencia', $exp->cedula_residencia ?? ($usuarioPreseleccionado->cedula ?? '')) }}" placeholder="Ej: 1-1234-5678">
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <label class="form-label-custom">Pasaporte (Extranjeros)</label>
                                <input type="text" name="pasaporte" class="form-control form-control-custom w-100" value="{{ old('pasaporte', $exp->pasaporte ?? '') }}" placeholder="Ej: A12345678">
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <label class="form-label-custom">Género</label>
                                <select name="genero" class="form-select form-select-custom w-100">
                                    @php $genActual = old('genero', $exp->genero ?? 'Masculino'); @endphp
                                    <option value="Masculino" {{ $genActual == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                    <option value="Femenino" {{ $genActual == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                    <option value="No especificado" {{ $genActual == 'No especificado' ? 'selected' : '' }}>No especificado</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <label class="form-label-custom">Nacionalidad</label>
                                <input type="text" name="nacionalidad" class="form-control form-control-custom w-100" value="{{ old('nacionalidad', $exp->nacionalidad ?? 'Costarricense') }}" placeholder="Ej: Costarricense">
                            </div>
                            <div class="col-xl-4 col-md-4">
                                <label class="form-label-custom">Fecha de Nacimiento</label>
                                @php
                                    $fechaNac = old('fecha_nacimiento');
                                    if (!$fechaNac) {
                                        if ($exp && $exp->fecha_nacimiento) $fechaNac = $exp->fecha_nacimiento->format('Y-m-d');
                                        elseif ($usuarioPreseleccionado && $usuarioPreseleccionado->fecha_nacimiento) $fechaNac = $usuarioPreseleccionado->fecha_nacimiento->format('Y-m-d');
                                    }
                                @endphp
                                <input type="date" name="fecha_nacimiento" class="form-control form-control-custom w-100" value="{{ $fechaNac }}">
                            </div>
                            <div class="col-xl-4 col-md-4">
                                <label class="form-label-custom">Lugar de Nacimiento</label>
                                <input type="text" name="lugar_nacimiento" class="form-control form-control-custom w-100" value="{{ old('lugar_nacimiento', $exp->lugar_nacimiento ?? '') }}" placeholder="Ej: San José, Costa Rica">
                            </div>
                            <div class="col-xl-4 col-md-4">
                                <label class="form-label-custom">Estado Civil</label>
                                <select name="estado_civil" class="form-select form-select-custom w-100">
                                    @php $estCivil = old('estado_civil', $exp->estado_civil ?? 'Soltero(a)'); @endphp
                                    <option value="Soltero(a)" {{ $estCivil == 'Soltero(a)' ? 'selected' : '' }}>Soltero(a)</option>
                                    <option value="Casado(a)" {{ $estCivil == 'Casado(a)' ? 'selected' : '' }}>Casado(a)</option>
                                    <option value="Divorciado(a)" {{ $estCivil == 'Divorciado(a)' ? 'selected' : '' }}>Divorciado(a)</option>
                                    <option value="Unión Libre" {{ $estCivil == 'Unión Libre' ? 'selected' : '' }}>Unión Libre</option>
                                    <option value="Viudo(a)" {{ $estCivil == 'Viudo(a)' ? 'selected' : '' }}>Viudo(a)</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <!-- 3. LUGAR DE DOMICILIO Y DIRECCIÓN -->
                    <fieldset class="mb-5">
                        <div class="legend-custom"><i class="bi bi-geo-alt-fill me-2"></i>3. Lugar de Domicilio y Residencia</div>
                        <div class="row g-4 mb-3">
                            <div class="col-xl-4 col-md-4">
                                <label class="form-label-custom">Provincia</label>
                                <input type="text" name="domicilio_provincia" class="form-control form-control-custom w-100" value="{{ old('domicilio_provincia', $exp->domicilio_provincia ?? '') }}" placeholder="Ej: San José">
                            </div>
                            <div class="col-xl-4 col-md-4">
                                <label class="form-label-custom">Cantón</label>
                                <input type="text" name="domicilio_canton" class="form-control form-control-custom w-100" value="{{ old('domicilio_canton', $exp->domicilio_canton ?? '') }}" placeholder="Ej: Escazú">
                            </div>
                            <div class="col-xl-4 col-md-4">
                                <label class="form-label-custom">Distrito</label>
                                <input type="text" name="domicilio_distrito" class="form-control form-control-custom w-100" value="{{ old('domicilio_distrito', $exp->domicilio_distrito ?? '') }}" placeholder="Ej: San Rafael">
                            </div>
                        </div>
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label-custom">Dirección Exacta de Residencia</label>
                                <textarea name="domicilio_direccion" class="form-control form-control-custom w-100" rows="2" placeholder="Señas exactas, número de casa, calle...">{{ old('domicilio_direccion', $exp->domicilio_direccion ?? '') }}</textarea>
                            </div>
                        </div>
                    </fieldset>

                    <!-- 4. INFORMACIÓN DE CONTACTO -->
                    <fieldset class="mb-5">
                        <div class="legend-custom"><i class="bi bi-telephone-fill me-2"></i>4. Información de Contacto</div>
                        <div class="row g-4">
                            <div class="col-xl-4 col-md-6">
                                <label class="form-label-custom">Teléfono Celular / WhatsApp</label>
                                <input type="text" name="contacto_tel_celular" class="form-control form-control-custom w-100" value="{{ old('contacto_tel_celular', $exp->contacto_tel_celular ?? ($usuarioPreseleccionado->telefono ?? '')) }}" placeholder="Ej: 50688887777">
                            </div>
                            <div class="col-xl-4 col-md-6">
                                <label class="form-label-custom">Teléfono Habitación / Fijo</label>
                                <input type="text" name="contacto_tel_habitacion" class="form-control form-control-custom w-100" value="{{ old('contacto_tel_habitacion', $exp->contacto_tel_habitacion ?? '') }}" placeholder="Ej: 22223333">
                            </div>
                            <div class="col-xl-4 col-md-12">
                                <label class="form-label-custom">Contacto y Teléfono de Emergencia</label>
                                <input type="text" name="contacto_otro_emergencias" class="form-control form-control-custom w-100" value="{{ old('contacto_otro_emergencias', $exp->contacto_otro_emergencias ?? '') }}" placeholder="Nombre, parentesco y teléfono...">
                            </div>
                        </div>
                    </fieldset>

                    <!-- 5. PROCEDENCIA ACADÉMICA (ESTUDIOS PREVIOS) -->
                    <fieldset class="mb-5">
                        <div class="legend-custom"><i class="bi bi-building-fill me-2"></i>5. Procedencia Académica (Estudios Previos)</div>
                        <div class="row g-4 mb-3">
                            <div class="col-xl-5 col-md-6">
                                <label class="form-label-custom">Institución de Secundaria (Colegio)</label>
                                <input type="text" name="procedencia_secundaria_institucion" class="form-control form-control-custom w-100" value="{{ old('procedencia_secundaria_institucion', $exp->procedencia_secundaria_institucion ?? '') }}" placeholder="Ej: Liceo de Costa Rica">
                            </div>
                            <div class="col-xl-3 col-md-3">
                                <label class="form-label-custom">Año Graduación Secundaria</label>
                                <input type="text" name="procedencia_secundaria_ano_graduacion" class="form-control form-control-custom w-100" value="{{ old('procedencia_secundaria_ano_graduacion', $exp->procedencia_secundaria_ano_graduacion ?? '') }}" placeholder="Ej: 2018">
                            </div>
                            <div class="col-xl-4 col-md-3">
                                <label class="form-label-custom">Título Secundaria Obtenido</label>
                                <input type="text" name="procedencia_secundaria_grado_obtenido" class="form-control form-control-custom w-100" value="{{ old('procedencia_secundaria_grado_obtenido', $exp->procedencia_secundaria_grado_obtenido ?? 'Bachiller en Educación Media') }}" placeholder="Ej: Bachiller en Educación Media">
                            </div>
                        </div>
                        <div class="row g-4">
                            <div class="col-xl-4 col-md-6">
                                <label class="form-label-custom">Universidad de Procedencia (Opcional)</label>
                                <input type="text" name="procedencia_universidad" class="form-control form-control-custom w-100" value="{{ old('procedencia_universidad', $exp->procedencia_universidad ?? '') }}" placeholder="Ej: Universidad Nacional">
                            </div>
                            <div class="col-xl-2 col-md-3">
                                <label class="form-label-custom">Año Graduación Univ.</label>
                                <input type="text" name="procedencia_universidad_ano_graduacion" class="form-control form-control-custom w-100" value="{{ old('procedencia_universidad_ano_graduacion', $exp->procedencia_universidad_ano_graduacion ?? '') }}" placeholder="Ej: 2022">
                            </div>
                            <div class="col-xl-3 col-md-3">
                                <label class="form-label-custom">Grado / Título Univ. Obtenido</label>
                                <input type="text" name="procedencia_universidad_grado_obtenido" class="form-control form-control-custom w-100" value="{{ old('procedencia_universidad_grado_obtenido', $exp->procedencia_universidad_grado_obtenido ?? '') }}" placeholder="Ej: Diplomado / Bachillerato">
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <label class="form-label-custom">Especialidad Univ. Previa</label>
                                <input type="text" name="procedencia_universidad_especialidad" class="form-control form-control-custom w-100" value="{{ old('procedencia_universidad_especialidad', $exp->procedencia_universidad_especialidad ?? '') }}" placeholder="Ej: Teología / Educación">
                            </div>
                        </div>
                    </fieldset>

                    <!-- 6. INFORMACIÓN LABORAL -->
                    <fieldset class="mb-5">
                        <div class="legend-custom"><i class="bi bi-briefcase-fill me-2"></i>6. Información Laboral</div>
                        <div class="row g-4 mb-3">
                            <div class="col-xl-4 col-md-6">
                                <label class="form-label-custom">Empresa / Institución / Iglesia</label>
                                <input type="text" name="laboral_institucion" class="form-control form-control-custom w-100" value="{{ old('laboral_institucion', $exp->laboral_institucion ?? '') }}" placeholder="Ej: Ministerio Cristiano / Empresa Privada">
                            </div>
                            <div class="col-xl-4 col-md-6">
                                <label class="form-label-custom">Puesto o Cargo que Desempeña</label>
                                <input type="text" name="laboral_puesto" class="form-control form-control-custom w-100" value="{{ old('laboral_puesto', $exp->laboral_puesto ?? '') }}" placeholder="Ej: Pastor Asociado / Administrador">
                            </div>
                            <div class="col-xl-4 col-md-6">
                                <label class="form-label-custom">Fecha de Ingreso Laboral</label>
                                @php
                                    $fechaLab = old('laboral_fecha_ingreso', ($exp && $exp->laboral_fecha_ingreso) ? $exp->laboral_fecha_ingreso->format('Y-m-d') : '');
                                @endphp
                                <input type="date" name="laboral_fecha_ingreso" class="form-control form-control-custom w-100" value="{{ $fechaLab }}">
                            </div>
                        </div>
                        <div class="row g-4">
                            <div class="col-xl-4 col-md-4">
                                <label class="form-label-custom">Teléfono Laboral</label>
                                <input type="text" name="laboral_telefono" class="form-control form-control-custom w-100" value="{{ old('laboral_telefono', $exp->laboral_telefono ?? '') }}" placeholder="Ej: 22220000">
                            </div>
                            <div class="col-xl-4 col-md-4">
                                <label class="form-label-custom">Extensión Telefónica</label>
                                <input type="text" name="laboral_extension" class="form-control form-control-custom w-100" value="{{ old('laboral_extension', $exp->laboral_extension ?? '') }}" placeholder="Ej: 104">
                            </div>
                            <div class="col-xl-4 col-md-4">
                                <label class="form-label-custom">Correo Electrónico Laboral</label>
                                <input type="email" name="laboral_correo_electronico" class="form-control form-control-custom w-100" value="{{ old('laboral_correo_electronico', $exp->laboral_correo_electronico ?? '') }}" placeholder="Ej: contacto@empresa.com">
                            </div>
                        </div>
                    </fieldset>

                    <!-- 7. DOCUMENTACIÓN DIGITAL Y OBSERVACIONES (ESTILO OFICIAL UNELA) -->
                    <fieldset class="mb-5">
                        <div class="legend-custom d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span><i class="bi bi-file-earmark-check-fill me-2"></i>7. Documentación Adjunta y Requisitos de Admisión</span>
                            <span class="badge bg-secondary bg-opacity-25 text-light fw-normal fs-6">
                                <i class="bi bi-shield-lock me-1"></i> Bóveda Digital Certificada
                            </span>
                        </div>
                        <p class="text-white-50 small mb-4">
                            Adjunte los documentos oficiales del estudiante en formato digital (PDF, JPG, PNG). Al seleccionar o subir un archivo, el sistema guardará el respaldo en la bóveda documental y verificará automáticamente el requisito correspondiente.
                        </p>

                        <div class="row g-4 mb-4">
                            <!-- Documento 1: Cédula / Documento de Identidad -->
                            <div class="col-xl-6">
                                <div class="doc-upload-box">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-person-vcard text-primary fs-4"></i>
                                            <div>
                                                <div class="doc-upload-title">Cédula / Documento de Identidad</div>
                                                <div class="doc-upload-desc">Cédula física, DIMEX de residencia o pasaporte vigente</div>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="registro_doc_cedula" name="registro_doc_cedula" {{ old('registro_doc_cedula', $exp->registro_doc_cedula ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="registro_doc_cedula">Entregada</label>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="form-label-custom mb-1">Adjuntar Archivo (PDF, JPG, PNG)</label>
                                        <input type="file" name="archivo_cedula" class="form-control form-control-custom w-100 doc-file-input" data-target-switch="registro_doc_cedula" accept=".pdf,image/*">
                                    </div>
                                    @if(isset($archCedula) && $archCedula)
                                        @php $resDoc = \App\Services\ExpedienteStorageService::resolveFilePath($archCedula->nombre_servidor); @endphp
                                        <div class="mt-2 doc-existing-badge d-flex align-items-center gap-2">
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            <span class="text-truncate">En bóveda: <a href="{{ $resDoc['web_url'] }}" target="_blank" class="doc-existing-link">{{ $archCedula->nombre_original }}</a></span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Documento 2: Título Bachiller en Secundaria -->
                            <div class="col-xl-6">
                                <div class="doc-upload-box">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-mortarboard text-success fs-4"></i>
                                            <div>
                                                <div class="doc-upload-title">Título de Bachiller en Secundaria</div>
                                                <div class="doc-upload-desc">Título oficial de conclusión de estudios de secundaria</div>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="registro_doc_titulo_sec" name="registro_doc_titulo_sec" {{ old('registro_doc_titulo_sec', $exp->registro_doc_titulo_sec ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="registro_doc_titulo_sec">Entregado</label>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="form-label-custom mb-1">Adjuntar Archivo (PDF, JPG, PNG)</label>
                                        <input type="file" name="archivo_titulo_sec" class="form-control form-control-custom w-100 doc-file-input" data-target-switch="registro_doc_titulo_sec" accept=".pdf,image/*">
                                    </div>
                                    @if(isset($archTituloSec) && $archTituloSec)
                                        @php $resDoc = \App\Services\ExpedienteStorageService::resolveFilePath($archTituloSec->nombre_servidor); @endphp
                                        <div class="mt-2 doc-existing-badge d-flex align-items-center gap-2">
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            <span class="text-truncate">En bóveda: <a href="{{ $resDoc['web_url'] }}" target="_blank" class="doc-existing-link">{{ $archTituloSec->nombre_original }}</a></span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Documento 3: Título Universitario Previo -->
                            <div class="col-xl-6">
                                <div class="doc-upload-box">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-award text-warning fs-4"></i>
                                            <div>
                                                <div class="doc-upload-title">Título Universitario Previo</div>
                                                <div class="doc-upload-desc">Diplomado, Bachillerato o Licenciatura universitaria previa (si aplica)</div>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="registro_doc_titulo_univ" name="registro_doc_titulo_univ" {{ old('registro_doc_titulo_univ', $exp->registro_doc_titulo_univ ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="registro_doc_titulo_univ">Entregado</label>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="form-label-custom mb-1">Adjuntar Archivo (PDF, JPG, PNG)</label>
                                        <input type="file" name="archivo_titulo_univ" class="form-control form-control-custom w-100 doc-file-input" data-target-switch="registro_doc_titulo_univ" accept=".pdf,image/*">
                                    </div>
                                    @if(isset($archTituloUniv) && $archTituloUniv)
                                        @php $resDoc = \App\Services\ExpedienteStorageService::resolveFilePath($archTituloUniv->nombre_servidor); @endphp
                                        <div class="mt-2 doc-existing-badge d-flex align-items-center gap-2">
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            <span class="text-truncate">En bóveda: <a href="{{ $resDoc['web_url'] }}" target="_blank" class="doc-existing-link">{{ $archTituloUniv->nombre_original }}</a></span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Documento 4: Certificaciones de Notas / Convalidación -->
                            <div class="col-xl-6">
                                <div class="doc-upload-box">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-file-earmark-spreadsheet text-info fs-4"></i>
                                            <div>
                                                <div class="doc-upload-title">Certificaciones de Calificaciones / Notas</div>
                                                <div class="doc-upload-desc">Historial académico oficial emitido por el centro de origen</div>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="registro_doc_certificaciones" name="registro_doc_certificaciones" {{ old('registro_doc_certificaciones', $exp->registro_doc_certificaciones ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="registro_doc_certificaciones">Entregadas</label>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="form-label-custom mb-1">Adjuntar Archivo (PDF, JPG, PNG)</label>
                                        <input type="file" name="archivo_certificaciones" class="form-control form-control-custom w-100 doc-file-input" data-target-switch="registro_doc_certificaciones" accept=".pdf,image/*">
                                    </div>
                                    @if(isset($archNotas) && $archNotas)
                                        @php $resDoc = \App\Services\ExpedienteStorageService::resolveFilePath($archNotas->nombre_servidor); @endphp
                                        <div class="mt-2 doc-existing-badge d-flex align-items-center gap-2">
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            <span class="text-truncate">En bóveda: <a href="{{ $resDoc['web_url'] }}" target="_blank" class="doc-existing-link">{{ $archNotas->nombre_original }}</a></span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Documento 5: Fotografía Oficial de Identificación -->
                            <div class="col-xl-6">
                                <div class="doc-upload-box">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-camera-fill text-danger fs-4"></i>
                                            <div>
                                                <div class="doc-upload-title">Fotografía Oficial para Carnet / Ficha</div>
                                                <div class="doc-upload-desc">Fotografía nítida tipo pasaporte o carnet estudiantil</div>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="registro_doc_fotografia" name="registro_doc_fotografia" {{ old('registro_doc_fotografia', $exp->registro_doc_fotografia ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="registro_doc_fotografia">Entregada</label>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="form-label-custom mb-1">Adjuntar Fotografía (JPG, PNG, WebP)</label>
                                        <input type="file" name="archivo_fotografia" class="form-control form-control-custom w-100 doc-file-input" data-target-switch="registro_doc_fotografia" accept="image/*">
                                    </div>
                                    @if(isset($archFoto) && $archFoto)
                                        @php $resDoc = \App\Services\ExpedienteStorageService::resolveFilePath($archFoto->nombre_servidor); @endphp
                                        <div class="mt-2 doc-existing-badge d-flex align-items-center gap-2">
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            <span class="text-truncate">En bóveda: <a href="{{ $resDoc['web_url'] }}" target="_blank" class="doc-existing-link">{{ $archFoto->nombre_original }}</a></span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Documento 6: Archivos y Documentación Adicional -->
                            <div class="col-xl-6">
                                <div class="doc-upload-box">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-folder-plus text-info fs-4"></i>
                                            <div>
                                                <div class="doc-upload-title">Documentos Complementarios / Otros Anexos</div>
                                                <div class="doc-upload-desc">Cartas de recomendación, cartas pastorales, comprobantes de pago, etc.</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="form-label-custom mb-1">Adjuntar Archivos Múltiples (PDF, Docs, Comprimidos)</label>
                                        <input type="file" name="archivos_adicionales[]" multiple class="form-control form-control-custom w-100" accept=".pdf,image/*,.doc,.docx,.zip,.rar">
                                    </div>
                                    <div class="mt-2">
                                        <input type="text" name="descripcion_adicional" class="form-control form-control-custom w-100 small" placeholder="Descripción breve de los anexos (opcional)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label-custom">Observaciones y Notas de Admisión / Expediente</label>
                                <textarea name="registro_observaciones" class="form-control form-control-custom w-100" rows="3" placeholder="Anotaciones administrativas, convalidaciones pendientes, requisitos especiales...">{{ old('registro_observaciones', $exp->registro_observaciones ?? '') }}</textarea>
                            </div>
                        </div>
                    </fieldset>

                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-submit py-3 fs-6">
                            <i class="bi bi-folder-check me-2"></i> {{ (isset($exp) && $exp && $exp->id_expediente) ? 'Actualizar y Guardar Expediente Digital' : 'Registrar y Guardar Expediente Digital' }}
                        </button>
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
        $(document).ready(function() {
            let searchTimeout = null;

            // Auto-activar el switch correspondiente cuando el usuario selecciona un archivo
            $('.doc-file-input').on('change', function() {
                let targetSwitch = $(this).data('target-switch');
                if (targetSwitch && this.files && this.files.length > 0) {
                    $('#' + targetSwitch).prop('checked', true);
                }
            });

            // Búsqueda interactiva de estudiante (solo si está disponible el buscador)
            $('#user-search').on('input', function() {
                let term = $(this).val();
                if (term.length < 3) {
                    $('#user-search-results').empty().hide();
                    return;
                }

                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    $.getJSON("{{ route('expedientes.buscar_usuario_ajax') }}", { query: term }, function(data) {
                        let container = $('#user-search-results').empty().show();
                        if (data.length === 0) {
                            container.append('<div class="list-group-item bg-dark text-white-50 border-secondary">No se encontraron estudiantes</div>');
                            return;
                        }
                        
                        data.forEach(function(user) {
                            let btn = $(`<button type="button" class="list-group-item list-group-item-action result-item border-secondary p-3"></button>`);
                            
                            let badge = '';
                            if (user.has_expediente) {
                                if (user.estado_expediente === 'Aprobado') {
                                    badge = `<span class="badge bg-success float-end mt-1"><i class="bi bi-check-circle-fill"></i> EXPEDIENTE EXISTENTE</span>`;
                                } else {
                                    badge = `<span class="badge bg-warning text-dark float-end mt-1 fw-bold"><i class="bi bi-hourglass-split"></i> SOLICITUD PENDIENTE</span>`;
                                }
                            }

                            btn.html(`
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${user.nombre} ${user.apellidos || ''}</strong><br>
                                        <small class="text-white-50">${user.email}</small>
                                    </div>
                                    ${badge}
                                </div>
                            `);

                            btn.on('click', function() {
                                if (user.has_expediente && user.estado_expediente === 'Aprobado') {
                                    Swal.fire('Información', 'Este estudiante ya cuenta con un expediente aprobado.', 'info');
                                    container.hide();
                                    return;
                                }
                                selectUser(user.id, user.nombre + ' ' + (user.apellidos || ''), user.email);
                                container.hide();
                            });

                            container.append(btn);
                        });
                    });
                }, 300);
            });

            function selectUser(userId, userName, userEmail) {
                $('#student_id').val(userId);
                $('#data-form-header').text(`Información para: ${userName}`);
                $('#expediente-data-section').fadeIn();
                $('#user-search').val(userName).prop('disabled', true);
                $('#reset-user-search').show();
            }

            $('#reset-user-search').on('click', function() {
                window.location.reload();
            });

            // Alerta de confirmación de envío
            $('#expediente-form').on('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Guardar Expediente Digital?',
                    text: 'Se registrará la información y se respaldarán los documentos adjuntos en la bóveda.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#5fb230',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, guardar expediente',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
    </script>
@endsection
