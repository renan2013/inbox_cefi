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
            justify-content: space-between;
            gap: 0.75rem;
            border-bottom: 2px solid var(--border-dark);
            padding-bottom: 0.75rem;
        }

        .badge-page-ref {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            background: rgba(56, 189, 248, 0.12);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 0.3rem 0.75rem;
            border-radius: 50rem;
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

        .plan-card {
            background-color: rgba(15, 23, 42, 0.35);
            border: 1px solid var(--border-dark);
            border-radius: 1.25rem;
            padding: 1.5rem;
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

        .table-plan th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            border-bottom: 1px solid var(--border-dark);
            padding-bottom: 0.75rem;
        }

        .table-plan td {
            padding: 0.5rem 0.4rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
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

        [data-theme="light"] .plan-card {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
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
    <div class="container-fluid px-4 py-4">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('programas.index') }}" class="text-decoration-none text-white-50">Programas Académicos</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Editar Programa</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Editar Programa: {{ $programa->nombre_programa }}</h1>
                <p class="text-white-50 mb-0">Actualice la ficha institucional CEFI, el plan de estudios, aranceles y recursos gráficos.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('programas.pdf', $programa->id_programa) }}" target="_blank" class="btn btn-outline-danger rounded-pill px-3 shadow-sm">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Ver PDF CEFI
                </a>
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
                    <div id="contenedorEliminadosPlan"></div>
                    
                    <!-- SECCIÓN 1: ENCABEZADO DEL PROGRAMA -->
                    <div class="section-divider">
                        <span><i class="bi bi-award-fill me-1"></i> 1. Datos de Encabezado del Programa</span>
                        <span class="badge-page-ref">Encabezado PDF (Págs. 1 - 3)</span>
                    </div>
                    
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label for="nombre_programa" class="form-label-custom">Nombre del Programa / Carrera *</label>
                            <input type="text" name="nombre_programa" id="nombre_programa" class="form-control form-control-custom w-100" placeholder="Ej: TÉCNICO EN DISEÑO GRÁFICO CON IA" required value="{{ old('nombre_programa', $programa->nombre_programa) }}">
                        </div>
                        <div class="col-md-2">
                            <label for="categoria" class="form-label-custom">Categoría Académica *</label>
                            @php $catActual = old('categoria', $programa->categoria); @endphp
                            <select name="categoria" id="categoria" class="form-select form-select-custom w-100" required>
                                <option value="">Seleccione...</option>
                                <option value="Técnico Parauniversitario" {{ $catActual === 'Técnico Parauniversitario' ? 'selected' : '' }}>Técnico Parauniversitario</option>
                                <option value="Carrera Técnica" {{ $catActual === 'Carrera Técnica' ? 'selected' : '' }}>Carrera Técnica</option>
                                <option value="Diplomado" {{ $catActual === 'Diplomado' ? 'selected' : '' }}>Diplomado</option>
                                <option value="Curso Libre" {{ $catActual === 'Curso Libre' ? 'selected' : '' }}>Curso Libre</option>
                                <option value="Certificación" {{ $catActual === 'Certificación' ? 'selected' : '' }}>Certificación</option>
                                <option value="Bachillerato" {{ $catActual === 'Bachillerato' ? 'selected' : '' }}>Bachillerato</option>
                                <option value="Licenciatura" {{ $catActual === 'Licenciatura' ? 'selected' : '' }}>Licenciatura</option>
                                <option value="Maestría" {{ $catActual === 'Maestría' ? 'selected' : '' }}>Maestría</option>
                                <option value="Doctorado" {{ $catActual === 'Doctorado' ? 'selected' : '' }}>Doctorado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="duracion" class="form-label-custom">Duración *</label>
                            <input type="text" name="duracion" id="duracion" class="form-control form-control-custom w-100" placeholder="Ej: 12 meses." value="{{ old('duracion', $programa->duracion ?? '12 meses.') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="modalidad" class="form-label-custom">Modalidad *</label>
                            @php $modActual = old('modalidad', $programa->modalidad ?? 'Presencial.'); @endphp
                            <select name="modalidad" id="modalidad" class="form-select form-select-custom w-100">
                                <option value="Presencial." {{ $modActual === 'Presencial.' || $modActual === 'Presencial' ? 'selected' : '' }}>Presencial.</option>
                                <option value="Virtual." {{ $modActual === 'Virtual.' || $modActual === 'Virtual' ? 'selected' : '' }}>Virtual.</option>
                                <option value="Bimodal / Híbrida." {{ $modActual === 'Bimodal / Híbrida.' ? 'selected' : '' }}>Bimodal / Híbrida.</option>
                                <option value="Sincrónica." {{ $modActual === 'Sincrónica.' ? 'selected' : '' }}>Sincrónica.</option>
                            </select>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: PÁGINA 1 DEL MODELO CEFI (DESCRIPCIÓN Y OBJETIVO) -->
                    <div class="section-divider">
                        <span><i class="bi bi-file-earmark-text-fill me-1"></i> 2. Descripción y Objetivo del Curso</span>
                        <span class="badge-page-ref">Página 1 del PDF</span>
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-md-12">
                            <label for="informacion" class="form-label-custom">Descripción del Curso (DESCRIPCIÓN DEL CURSO:)</label>
                            <textarea name="informacion" id="informacion" class="form-control form-control-custom w-100" rows="5" placeholder="Descripción general del curso o programa...">{{ old('informacion', $programa->informacion) }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label for="objetivo_curso" class="form-label-custom">Objetivo del Curso (OBJETIVO CURSO:)</label>
                            <textarea name="objetivo_curso" id="objetivo_curso" class="form-control form-control-custom w-100" rows="4" placeholder="Objetivo formativo del programa...">{{ old('objetivo_curso', $programa->objetivo_curso) }}</textarea>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: PÁGINA 2 DEL MODELO CEFI (OPCIONES LABORALES Y LO QUE OBTENDRÁS) -->
                    <div class="section-divider">
                        <span><i class="bi bi-briefcase-fill me-1"></i> 3. Opciones Laborales y Lo Que Obtendrás</span>
                        <span class="badge-page-ref">Página 2 del PDF</span>
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label for="opciones_laborales" class="form-label-custom">Opciones Laborales (OPCIONES LABORALES:)</label>
                            <textarea name="opciones_laborales" id="opciones_laborales" class="form-control form-control-custom w-100" rows="12" placeholder="1. Agencias de publicidad y marketing digital...">{{ old('opciones_laborales', $programa->opciones_laborales ?? $programa->oferta) }}</textarea>
                            <small class="text-white-50 d-block mt-1">Columna izquierda de la página 2 del folleto/programa PDF.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="lo_que_obtendras" class="form-label-custom">Lo Que Obtendrás / Perfil de Salida (LO QUE OBTENDRÁS)</label>
                            <textarea name="lo_que_obtendras" id="lo_que_obtendras" class="form-control form-control-custom w-100" rows="12" placeholder="Al finalizar este programa, el estudiante contará con...">{{ old('lo_que_obtendras', $programa->lo_que_obtendras ?? $programa->perfil) }}</textarea>
                            <small class="text-white-50 d-block mt-1">Columna derecha de la página 2 del folleto/programa PDF.</small>
                        </div>
                    </div>

                    <!-- SECCIÓN 4: PÁGINA 3 DEL MODELO CEFI (PLAN DE ESTUDIOS, DOCENTES Y REQUISITOS) -->
                    <div class="section-divider">
                        <span><i class="bi bi-journal-bookmark-fill me-1"></i> 4. Plan de Estudios, Perfil Docente y Requisitos de Ingreso</span>
                        <span class="badge-page-ref">Página 3 del PDF</span>
                    </div>

                    <div class="mb-4">
                        <label for="duracion_plan_estudios" class="form-label-custom">Duración del Plan de Estudios (DURACIÓN DEL PLAN DE ESTUDIOS:)</label>
                        <textarea name="duracion_plan_estudios" id="duracion_plan_estudios" class="form-control form-control-custom w-100" rows="2" placeholder="Ej: El programa tiene una duración de 12 meses, con dos horas y media de clase y dos horas y media de trabajo/estudio en casa por semana">{{ old('duracion_plan_estudios', $programa->duracion_plan_estudios ?? $programa->detalles_programa) }}</textarea>
                    </div>

                    <!-- Constructor dinámico de cursos del Plan de Estudios -->
                    <div class="plan-card mb-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div>
                                <h6 class="fw-bold text-white mb-0"><i class="bi bi-table me-2 text-success"></i>Cursos del Plan de Estudios ({{ $programa->planesEstudio->count() }})</h6>
                                <small class="text-white-50">Gestione las materias de la tabla del Plan de Estudios (Código, Nombre del Curso, Duración y Requisito).</small>
                            </div>
                            <button type="button" id="btnAgregarCursoPlan" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                <i class="bi bi-plus-circle me-1"></i> Agregar Curso al Plan
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-borderless table-plan mb-0" id="tablaPlanEstudios">
                                <thead>
                                    <tr>
                                        <th style="width: 18%;">Código</th>
                                        <th style="width: 42%;">Nombre del Curso</th>
                                        <th style="width: 18%;">Duración</th>
                                        <th style="width: 16%;">Requisito</th>
                                        <th style="width: 6%;" class="text-end">Quitar</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyPlanEstudios">
                                    @forelse ($programa->planesEstudio as $pe)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="plan_id[]" value="{{ $pe->id_plan }}">
                                                <input type="text" name="plan_codigo[]" class="form-control form-control-custom form-control-sm" value="{{ $pe->codigo }}" placeholder="Ej: TDG-IA01">
                                            </td>
                                            <td>
                                                <input type="text" name="plan_materia[]" class="form-control form-control-custom form-control-sm" value="{{ $pe->materia }}" placeholder="Nombre del Curso">
                                            </td>
                                            <td>
                                                <input type="text" name="plan_duracion[]" class="form-control form-control-custom form-control-sm" value="{{ $pe->duracion ?? '8 semanas' }}" placeholder="Ej: 8 semanas">
                                            </td>
                                            <td>
                                                <input type="text" name="plan_requisito[]" class="form-control form-control-custom form-control-sm" value="{{ $pe->requisitos ?? 'N/A' }}" placeholder="Ej: N/A">
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-fila-plan" data-id-plan="{{ $pe->id_plan }}" title="Quitar curso">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td>
                                                <input type="hidden" name="plan_id[]" value="">
                                                <input type="text" name="plan_codigo[]" class="form-control form-control-custom form-control-sm" placeholder="Ej: TDG-IA01">
                                            </td>
                                            <td>
                                                <input type="text" name="plan_materia[]" class="form-control form-control-custom form-control-sm" placeholder="Ej: Diseño Fotográfico Básico">
                                            </td>
                                            <td>
                                                <input type="text" name="plan_duracion[]" class="form-control form-control-custom form-control-sm" placeholder="Ej: 8 semanas" value="8 semanas">
                                            </td>
                                            <td>
                                                <input type="text" name="plan_requisito[]" class="form-control form-control-custom form-control-sm" placeholder="Ej: N/A" value="N/A">
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-fila-plan" title="Quitar fila">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label for="perfil_docentes" class="form-label-custom">Perfil de los docentes a cargo del programa:</label>
                            <textarea name="perfil_docentes" id="perfil_docentes" class="form-control form-control-custom w-100" rows="4" placeholder="Ej: Los docentes que participan de este programa poseen un grado académico de licenciatura e ingenierías...">{{ old('perfil_docentes', $programa->perfil_docentes) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="requisitos_ingreso" class="form-label-custom">Requisitos de ingreso:</label>
                            <textarea name="requisitos_ingreso" id="requisitos_ingreso" class="form-control form-control-custom w-100" rows="4" placeholder="1- Leer y aceptar las políticas institucionales...">{{ old('requisitos_ingreso', $programa->requisitos_ingreso) }}</textarea>
                        </div>
                    </div>

                    <!-- SECCIÓN 5: ESTRUCTURA DE COSTOS -->
                    <div class="section-divider">
                        <span><i class="bi bi-wallet2 me-1"></i> 5. Configuración Financiera (Costos de Matrícula y Boletas)</span>
                        <span class="badge-page-ref">Uso Interno / Facturación</span>
                    </div>

                    <div class="cost-card mb-5">
                        <div class="row g-4">
                            <div class="col-md-3">
                                <label for="costo_materia" class="form-label-custom">Costo por Materia / Módulo</label>
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
                                <label for="costo_biblioteca" class="form-label-custom">Costo de Biblioteca / Plataforma</label>
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

                    <!-- SECCIÓN 6: ARCHIVOS PORTADAS E IMÁGENES -->
                    <div class="section-divider">
                        <span><i class="bi bi-images me-1"></i> 6. Recursos Visuales (Banner PDF e Imágenes)</span>
                        <span class="badge-page-ref">Diseño Visual</span>
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-md-6 col-lg-3">
                            <div class="img-upload-box h-100">
                                <label class="form-label-custom d-block">Banner Encabezado PDF</label>
                                @if(!empty($programa->imagen_encabezado) && file_exists(public_path($programa->imagen_encabezado)))
                                    <img src="{{ asset($programa->imagen_encabezado) }}" class="img-current-preview d-block mx-auto" alt="Encabezado">
                                @else
                                    <i class="bi bi-image-fill text-white-50 display-6 d-block mb-3"></i>
                                @endif
                                <input type="file" name="imagen_encabezado" class="form-control form-control-custom w-100" accept="image/*">
                                <small class="text-white-50 d-block mt-1">Subir para reemplazar</small>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="img-upload-box h-100">
                                <label class="form-label-custom d-block">Imagen Principal</label>
                                @if(!empty($programa->imagen_principal) && file_exists(public_path($programa->imagen_principal)))
                                    <img src="{{ asset($programa->imagen_principal) }}" class="img-current-preview d-block mx-auto" alt="Principal">
                                @else
                                    <i class="bi bi-image text-white-50 display-6 d-block mb-3"></i>
                                @endif
                                <input type="file" name="imagen_principal" class="form-control form-control-custom w-100" accept="image/*">
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
                                <input type="file" name="imagen_secundaria" class="form-control form-control-custom w-100" accept="image/*">
                                <small class="text-white-50 d-block mt-1">Subir para reemplazar</small>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="img-upload-box h-100">
                                <label class="form-label-custom d-block">Imagen Footer PDF</label>
                                @if(!empty($programa->imagen_footer) && file_exists(public_path($programa->imagen_footer)))
                                    <img src="{{ asset($programa->imagen_footer) }}" class="img-current-preview d-block mx-auto" alt="Footer">
                                @else
                                    <i class="bi bi-image-fill text-white-50 display-6 d-block mb-3"></i>
                                @endif
                                <input type="file" name="imagen_footer" class="form-control form-control-custom w-100" accept="image/*">
                                <small class="text-white-50 d-block mt-1">Subir para reemplazar</small>
                            </div>
                        </div>
                    </div>

                    <!-- NORMATIVA VIRTUAL OPCIONAL -->
                    <div class="mb-5">
                        <label for="normas_netiqueta" class="form-label-custom">Normas de Netiqueta (Para Sílabos Virtuales - Opcional)</label>
                        <textarea name="normas_netiqueta" id="normas_netiqueta" class="form-control form-control-custom w-100" rows="3" placeholder="Normas de convivencia virtual sugeridas...">{{ old('normas_netiqueta', $programa->normas_netiqueta) }}</textarea>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('tbodyPlanEstudios');
    const btnAdd = document.getElementById('btnAgregarCursoPlan');
    const contenedorEliminados = document.getElementById('contenedorEliminadosPlan');

    function crearFilaCurso() {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <input type="hidden" name="plan_id[]" value="">
                <input type="text" name="plan_codigo[]" class="form-control form-control-custom form-control-sm" placeholder="Ej: TDG-IA01">
            </td>
            <td>
                <input type="text" name="plan_materia[]" class="form-control form-control-custom form-control-sm" placeholder="Nombre del Curso">
            </td>
            <td>
                <input type="text" name="plan_duracion[]" class="form-control form-control-custom form-control-sm" placeholder="Ej: 8 semanas" value="8 semanas">
            </td>
            <td>
                <input type="text" name="plan_requisito[]" class="form-control form-control-custom form-control-sm" placeholder="Ej: N/A" value="N/A">
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-fila-plan" title="Quitar fila">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        return tr;
    }

    if (btnAdd && tbody) {
        btnAdd.addEventListener('click', function () {
            tbody.appendChild(crearFilaCurso());
        });

        tbody.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-quitar-fila-plan');
            if (!btn) return;
            const idPlan = btn.getAttribute('data-id-plan');
            if (idPlan && contenedorEliminados) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'plan_eliminar_ids[]';
                hidden.value = idPlan;
                contenedorEliminados.appendChild(hidden);
            }

            const rows = tbody.querySelectorAll('tr');
            if (rows.length > 1) {
                btn.closest('tr').remove();
            } else {
                const tr = btn.closest('tr');
                tr.querySelectorAll('input').forEach(i => i.value = '');
                btn.removeAttribute('data-id-plan');
            }
        });
    }
});
</script>
@endsection
