@extends('layouts.app')

@section('title', 'Inbox BPM - Diseñar Plantilla')

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

        .editor-wrapper { 
            background: #0f172a; 
            padding: 24px; 
            border-radius: 1.5rem;
            border: 1px solid var(--border-dark);
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.5);
            position: relative;
        }

        #preview-container {
            position: relative;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background-color: #000;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            overflow: hidden;
            border: 1px solid var(--border-dark);
            aspect-ratio: {{ $plantilla->ancho_mm ?: 279 }} / {{ $plantilla->alto_mm ?: 216 }};
            user-select: none;
        }

        #bg-preview { width: 100%; height: 100%; display: block; object-fit: fill; pointer-events: none; }

        .text-element {
            position: absolute;
            white-space: nowrap;
            font-family: Arial, sans-serif;
            font-weight: bold;
            line-height: 1;
            cursor: grab;
            transform-origin: top left;
            padding: 4px 8px;
            border-radius: 6px;
            transition: outline 0.15s, box-shadow 0.15s;
        }
        .text-element:active {
            cursor: grabbing;
        }
        .text-element:hover {
            outline: 2px dashed #38bdf8;
        }
        .text-element.selected-el {
            outline: 2px solid #38bdf8 !important;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.6);
        }

        .qr-preview {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="white"/><path d="M10,10h30v30h-30z M20,20h10v10h-10z M60,10h30v30h-30z M70,20h10v10h-10z M10,60h30v30h-30z M20,70h10v10h-10z" fill="black"/></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            border: 1px solid #333;
        }

        .foto-placeholder {
            border: 2px dashed #0284c7;
            background-color: rgba(14, 165, 233, 0.2);
            color: #38bdf8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 11px;
        }

        .card-editor { max-height: 75vh; overflow-y: auto; scrollbar-width: thin; padding-right: 6px; }

        .campo-item {
            background: rgba(30, 41, 59, 0.5) !important;
            border: 1px solid var(--border-dark) !important;
            border-radius: 1rem !important;
            margin-bottom: 0.85rem !important;
            transition: all 0.25s ease-in-out;
        }

        .campo-item:hover { border-color: var(--primary) !important; }
        .campo-item.active-card {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25);
            background: rgba(30, 41, 59, 0.85) !important;
        }

        .preset-badge {
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.75rem;
            user-select: none;
        }
        .preset-badge:hover {
            transform: translateY(-2px);
            filter: brightness(1.15);
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-4 py-4">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('plantillas.index') }}" class="text-decoration-none text-white-50">Automatizaciones</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Diseñar #{{ $plantilla->id }}</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 rounded-pill px-3 py-1 mb-2">Diseñador Gráfico Interactivo</span>
                <h1 class="display-6 fw-bold mb-1">{{ $plantilla->nombre }}</h1>
                <p class="text-white-50 mb-0">Arrastre los textos directamente sobre el lienzo para posicionarlos con precisión milimétrica.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('plantillas.preparar', $plantilla->id) }}" class="btn btn-outline-info rounded-pill px-4">
                    <i class="bi bi-file-earmark-arrow-down me-1"></i> Probar Emisión
                </a>
                <a href="{{ route('plantillas.index') }}" class="btn btn-outline-light rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 rounded-4 mb-4" style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2) !important;">
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('plantillas.update', $plantilla->id) }}" method="POST" enctype="multipart/form-data" id="plantillaForm">
            @csrf
            
            <div class="row g-4">
                <!-- PANEL IZQUIERDO: LIENZO INTERACTIVO CON DRAG & DROP -->
                <div class="col-lg-8">
                    <div class="card glass-card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-white"><i class="bi bi-arrows-move me-2 text-info"></i> Lienzo Interactivo</h5>
                            <span class="badge bg-secondary font-monospace" id="doc-dimensions-badge">{{ $plantilla->ancho_mm ?: 279 }} x {{ $plantilla->alto_mm ?: 216 }} mm</span>
                        </div>
                        <div class="alert alert-info py-2 px-3 small rounded-3 border-0 mb-3 d-flex align-items-center" style="background: rgba(14, 165, 233, 0.15); color: #7dd3fc;">
                            <i class="bi bi-hand-index-thumb me-2 fs-5"></i>
                            <div><strong>Arrastrar y Soltar:</strong> Haz clic sobre cualquier texto o código para moverlo por la plantilla. Se actualizará en mm automáticamente.</div>
                        </div>

                        <div class="editor-wrapper text-center">
                            <div id="preview-container">
                                @php
                                    $bgSrc = asset($plantilla->imagen_fondo);
                                    if (!file_exists(public_path($plantilla->imagen_fondo)) && file_exists(base_path('../' . ltrim($plantilla->imagen_fondo, '/')))) {
                                        $bgSrc = url('../' . ltrim($plantilla->imagen_fondo, '/'));
                                    }
                                @endphp
                                <img id="bg-preview" src="{{ $bgSrc }}" alt="Fondo Documento">
                                <div id="elements-layer" style="position: absolute; top:0; left:0; width:100%; height:100%;"></div>
                            </div>
                        </div>

                        <!-- Propiedades de Lienzo y Fondo -->
                        <div class="row g-3 mt-3 pt-3 border-top border-secondary border-opacity-25">
                            <div class="col-md-6">
                                <label class="form-label-custom">Nombre de la Plantilla</label>
                                <input type="text" name="nombre" class="form-control form-control-custom" value="{{ $plantilla->nombre }}" required>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label-custom">Ancho (mm)</label>
                                <input type="number" step="0.1" name="ancho_mm" id="doc_ancho" class="form-control form-control-custom" value="{{ $plantilla->ancho_mm ?: 279 }}" required>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label-custom">Alto (mm)</label>
                                <input type="number" step="0.1" name="alto_mm" id="doc_alto" class="form-control form-control-custom" value="{{ $plantilla->alto_mm ?: 216 }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Reemplazar Imagen de Fondo (Opcional)</label>
                                <input type="file" name="imagen_fondo" id="input_bg" class="form-control form-control-custom" accept="image/png, image/jpeg">
                                <small class="text-white-50">Sube una nueva imagen JPG o PNG si deseas renovar el diseño base.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PANEL DERECHO: CONFIGURACIÓN DE CAMPOS Y CHIPS RÁPIDOS -->
                <div class="col-lg-4">
                    <div class="card glass-card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-white"><i class="bi bi-sliders me-2 text-warning"></i> Campos</h5>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" id="btn-add-campo">
                                <i class="bi bi-plus-lg me-1"></i> Agregar
                            </button>
                        </div>

                        <!-- CHIPS DE INSERCIÓN RÁPIDA -->
                        <div class="mb-3">
                            <small class="text-white-50 d-block mb-1 text-uppercase fw-semibold" style="font-size: 0.7rem;">Insertar en 1 Clic:</small>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 preset-badge" onclick="agregarCampoPreset('Nombre del Alumno', 'dinamico', 24, '#000000', 'C')">+ 👤 Nombre Alumno</span>
                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 preset-badge" onclick="agregarCampoPreset('Nombre del Curso', 'dinamico', 16, '#1e293b', 'C')">+ 🎓 Nombre Curso</span>
                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25 preset-badge" onclick="agregarCampoPreset('Fecha de Emisión', 'dinamico', 11, '#475569', 'C')">+ 📅 Fecha</span>
                                <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25 preset-badge" onclick="agregarCampoPreset('Cédula / ID', 'dinamico', 12, '#334155', 'L')">+ 🆔 Cédula</span>
                                <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-25 preset-badge" onclick="agregarCampoPreset('Horas Lectivas', 'dinamico', 11, '#475569', 'C')">+ ⏱️ Horas</span>
                                <span class="badge bg-dark text-light border border-light border-opacity-25 preset-badge" onclick="agregarCampoPreset('Código QR', 'qr', 25, '#000000', 'L')">+ 📱 QR</span>
                                <span class="badge bg-light bg-opacity-10 text-white border border-light border-opacity-25 preset-badge" onclick="agregarCampoPreset('Texto Fijo Institucional', 'fijo', 12, '#000000', 'C')">+ 📌 Texto Fijo</span>
                            </div>
                        </div>

                        <!-- LISTA DE CAMPOS SCROLLABLE -->
                        <div class="card-editor" id="campos-container">
                            @foreach ($campos as $idx => $campo)
                                <div class="campo-item p-3 mb-3" data-index="{{ $idx }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-secondary font-monospace" style="font-size: 0.7rem;">#{{ $idx + 1 }}</span>
                                            <span class="badge {{ ($campo['tipo'] ?? 'dinamico') === 'dinamico' ? 'bg-warning text-dark' : 'bg-primary' }} text-uppercase fw-bold" style="font-size: 0.65rem;">
                                                {{ ($campo['tipo'] ?? 'dinamico') === 'dinamico' ? '⚡ Dinámico' : '📌 Fijo' }}
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-campo" title="Eliminar"><i class="bi bi-trash3"></i></button>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label-custom">Nombre / Etiqueta del Campo</label>
                                        <input type="text" name="campo_label[]" class="form-control form-control-custom form-control-sm" value="{{ $campo['label'] ?? '' }}" required>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label-custom">¿Cómo se comporta este campo?</label>
                                        <select name="campo_tipo[]" class="form-select form-select-custom form-select-sm campo-tipo-selector">
                                            <option value="dinamico" {{ ($campo['tipo'] ?? 'dinamico') === 'dinamico' ? 'selected' : '' }}>⚡ Texto Dinámico (Variable al emitir)</option>
                                            <option value="fijo" {{ ($campo['tipo'] ?? 'dinamico') === 'fijo' ? 'selected' : '' }}>📌 Texto Fijo (Permanente en certificado)</option>
                                            <option value="qr" {{ ($campo['tipo'] ?? '') === 'qr' ? 'selected' : '' }}>📱 Código QR (Enlace / Verificación)</option>
                                            <option value="qr_info" {{ ($campo['tipo'] ?? '') === 'qr_info' ? 'selected' : '' }}>📱 QR Informativo Multiparámetro</option>
                                            <option value="foto" {{ ($campo['tipo'] ?? '') === 'foto' ? 'selected' : '' }}>👤 Foto del Estudiante</option>
                                        </select>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label-custom">Posición X (mm)</label>
                                            <input type="number" step="0.5" name="campo_x[]" class="form-control form-control-custom form-control-sm campo-x" value="{{ $campo['x'] ?? 10 }}">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label-custom">Posición Y (mm)</label>
                                            <input type="number" step="0.5" name="campo_y[]" class="form-control form-control-custom form-control-sm campo-y" value="{{ $campo['y'] ?? 10 }}">
                                        </div>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-4">
                                            <label class="form-label-custom">Tamaño</label>
                                            <input type="number" name="campo_size[]" class="form-control form-control-custom form-control-sm" value="{{ $campo['size'] ?? 14 }}">
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label-custom">Color</label>
                                            <input type="color" name="campo_color[]" class="form-control form-control-custom form-control-sm p-1" value="{{ $campo['color'] ?? '#000000' }}" style="height: 33px;">
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label-custom">Alineación</label>
                                            <select name="campo_align[]" class="form-select form-select-custom form-select-sm">
                                                <option value="L" {{ ($campo['align'] ?? 'L') === 'L' ? 'selected' : '' }}>Izquierda</option>
                                                <option value="C" {{ ($campo['align'] ?? 'L') === 'C' ? 'selected' : '' }}>Centro</option>
                                                <option value="R" {{ ($campo['align'] ?? 'L') === 'R' ? 'selected' : '' }}>Derecha</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <label class="form-label-custom">Tipografía</label>
                                        <select name="campo_font[]" class="form-select form-select-custom form-select-sm">
                                            <option value="Arial" {{ ($campo['font'] ?? 'Arial') === 'Arial' ? 'selected' : '' }}>Arial</option>
                                            <option value="Helvetica" {{ ($campo['font'] ?? '') === 'Helvetica' ? 'selected' : '' }}>Helvetica</option>
                                            <option value="Times" {{ ($campo['font'] ?? '') === 'Times' ? 'selected' : '' }}>Times New Roman</option>
                                            <option value="Courier" {{ ($campo['font'] ?? '') === 'Courier' ? 'selected' : '' }}>Courier</option>
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                            <button type="submit" class="btn btn-success rounded-pill w-100 py-2 fw-bold" style="background-color: var(--primary); border: none;">
                                <i class="bi bi-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
@endsection

@section('scripts')
    <script>
        const container = document.getElementById('campos-container');
        const elementsLayer = document.getElementById('elements-layer');
        const previewContainer = document.getElementById('preview-container');
        const docAnchoInput = document.getElementById('doc_ancho');
        const docAltoInput = document.getElementById('doc_alto');
        const bgImg = document.getElementById('bg-preview');
        const inputBg = document.getElementById('input_bg');

        let draggedElement = null;
        let dragStartX = 0, dragStartY = 0;
        let elInitialLeft = 0, elInitialTop = 0;

        function updateAspectRatio() {
            const w = parseFloat(docAnchoInput.value) || 279;
            const h = parseFloat(docAltoInput.value) || 216;
            previewContainer.style.aspectRatio = `${w} / ${h}`;
            document.getElementById('doc-dimensions-badge').innerText = `${w} x ${h} mm`;
            updatePreview();
        }

        docAnchoInput.addEventListener('input', updateAspectRatio);
        docAltoInput.addEventListener('input', updateAspectRatio);

        // Previsualización de fondo al subir archivo local
        inputBg.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    bgImg.src = evt.target.result;
                    setTimeout(updatePreview, 100);
                };
                reader.readAsDataURL(file);
            }
        });

        function agregarCampoPreset(label, tipo, size, color, align) {
            const w = parseFloat(docAnchoInput.value) || 279;
            const h = parseFloat(docAltoInput.value) || 216;
            const x = align === 'C' ? Math.round(w / 2) : 30;
            const y = Math.round(h / 2);

            const card = document.createElement('div');
            card.className = 'campo-item p-3 mb-3';
            card.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary font-monospace" style="font-size: 0.7rem;">#</span>
                        <span class="badge ${tipo === 'dinamico' ? 'bg-warning text-dark' : 'bg-primary'} text-uppercase fw-bold" style="font-size: 0.65rem;">
                            ${tipo === 'dinamico' ? '⚡ Dinámico' : '📌 Fijo'}
                        </span>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-campo"><i class="bi bi-trash3"></i></button>
                </div>
                <div class="mb-2">
                    <label class="form-label-custom">Nombre / Etiqueta del Campo</label>
                    <input type="text" name="campo_label[]" class="form-control form-control-custom form-control-sm" value="${label}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label-custom">¿Cómo se comporta este campo?</label>
                    <select name="campo_tipo[]" class="form-select form-select-custom form-select-sm campo-tipo-selector">
                        <option value="dinamico" ${tipo === 'dinamico' ? 'selected' : ''}>⚡ Texto Dinámico (Variable al emitir)</option>
                        <option value="fijo" ${tipo === 'fijo' ? 'selected' : ''}>📌 Texto Fijo (Permanente en certificado)</option>
                        <option value="qr" ${tipo === 'qr' ? 'selected' : ''}>📱 Código QR (Enlace / Verificación)</option>
                        <option value="qr_info" ${tipo === 'qr_info' ? 'selected' : ''}>📱 QR Informativo Multiparámetro</option>
                        <option value="foto" ${tipo === 'foto' ? 'selected' : ''}>👤 Foto del Estudiante</option>
                    </select>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label-custom">Posición X (mm)</label>
                        <input type="number" step="0.5" name="campo_x[]" class="form-control form-control-custom form-control-sm campo-x" value="${x}">
                    </div>
                    <div class="col-6">
                        <label class="form-label-custom">Posición Y (mm)</label>
                        <input type="number" step="0.5" name="campo_y[]" class="form-control form-control-custom form-control-sm campo-y" value="${y}">
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-4">
                        <label class="form-label-custom">Tamaño</label>
                        <input type="number" name="campo_size[]" class="form-control form-control-custom form-control-sm" value="${size}">
                    </div>
                    <div class="col-4">
                        <label class="form-label-custom">Color</label>
                        <input type="color" name="campo_color[]" class="form-control form-control-custom form-control-sm p-1" value="${color}" style="height: 33px;">
                    </div>
                    <div class="col-4">
                        <label class="form-label-custom">Alineación</label>
                        <select name="campo_align[]" class="form-select form-select-custom form-select-sm">
                            <option value="L" ${align === 'L' ? 'selected' : ''}>Izquierda</option>
                            <option value="C" ${align === 'C' ? 'selected' : ''}>Centro</option>
                            <option value="R" ${align === 'R' ? 'selected' : ''}>Derecha</option>
                        </select>
                    </div>
                </div>
                <div class="mt-2">
                    <label class="form-label-custom">Tipografía</label>
                    <select name="campo_font[]" class="form-select form-select-custom form-select-sm">
                        <option value="Arial" selected>Arial</option>
                        <option value="Helvetica">Helvetica</option>
                        <option value="Times">Times New Roman</option>
                        <option value="Courier">Courier</option>
                    </select>
                </div>
            `;
            container.appendChild(card);
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            updatePreview();
        }

        document.getElementById('btn-add-campo').addEventListener('click', () => {
            agregarCampoPreset('Nuevo Campo', 'dinamico', 14, '#000000', 'L');
        });

        document.addEventListener('click', (e) => {
            if (e.target.closest('.remove-campo')) {
                e.target.closest('.campo-item').remove();
                updatePreview();
            }
        });

        // Actualizar badges al cambiar selector de tipo
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('campo-tipo-selector')) {
                const card = e.target.closest('.campo-item');
                const badge = card.querySelector('.badge.text-uppercase');
                if (badge) {
                    if (e.target.value === 'dinamico') {
                        badge.className = 'badge bg-warning text-dark text-uppercase fw-bold';
                        badge.innerText = '⚡ Dinámico';
                    } else if (e.target.value === 'fijo') {
                        badge.className = 'badge bg-primary text-uppercase fw-bold';
                        badge.innerText = '📌 Fijo';
                    } else {
                        badge.className = 'badge bg-info text-uppercase fw-bold';
                        badge.innerText = e.target.value.toUpperCase();
                    }
                }
                updatePreview();
            }
        });

        function updatePreview() {
            elementsLayer.innerHTML = '';
            const docW = parseFloat(docAnchoInput.value) || 279;
            const docH = parseFloat(docAltoInput.value) || 216;
            const rect = previewContainer.getBoundingClientRect();
            const items = document.querySelectorAll('.campo-item');

            items.forEach((item, index) => {
                item.dataset.index = index;
                const idxBadge = item.querySelector('.badge.font-monospace');
                if (idxBadge) idxBadge.innerText = '#' + (index + 1);

                const x = parseFloat(item.querySelector('[name="campo_x[]"]').value) || 0;
                const y = parseFloat(item.querySelector('[name="campo_y[]"]').value) || 0;
                const size = parseFloat(item.querySelector('[name="campo_size[]"]').value) || 14;
                const color = item.querySelector('[name="campo_color[]"]').value;
                const label = item.querySelector('[name="campo_label[]"]').value || 'Texto';
                const tipo = item.querySelector('[name="campo_tipo[]"]').value;
                const align = item.querySelector('[name="campo_align[]"]').value;
                const font = item.querySelector('[name="campo_font[]"]').value;

                const el = document.createElement('div');
                el.className = 'text-element';
                el.dataset.index = index;
                el.style.fontFamily = font + ', sans-serif';

                if (tipo === 'qr' || tipo === 'qr_info') {
                    el.className += ' qr-preview';
                    el.innerText = tipo === 'qr_info' ? 'QR INFO' : '';
                    const sizePx = (size * rect.width) / docW;
                    el.style.width = sizePx + 'px';
                    el.style.height = sizePx + 'px';
                } else if (tipo === 'foto') {
                    el.className += ' foto-placeholder';
                    el.innerText = '👤 FOTO';
                    const sizePx = (size * rect.width) / docW;
                    el.style.width = sizePx + 'px';
                    el.style.height = (sizePx * 1.25) + 'px';
                } else {
                    el.innerText = label;
                    const fontSizePx = (size * 0.3527 * rect.width) / docW;
                    el.style.fontSize = fontSizePx + 'px';
                    el.style.color = color;
                }

                el.style.top = (y * 100 / docH) + '%';
                el.style.left = (x * 100 / docW) + '%';

                if (align === 'C') el.style.transform = 'translateX(-50%)';
                else if (align === 'R') el.style.transform = 'translateX(-100%)';
                else el.style.transform = 'none';

                if (tipo === 'dinamico') {
                    el.style.background = 'rgba(234, 179, 8, 0.2)';
                    el.style.border = '1.5px dashed #ca8a04';
                    el.title = '⚡ Campo Dinámico: ' + label + ' (Haz clic o arrastra)';
                } else if (tipo === 'fijo') {
                    el.style.background = 'rgba(59, 130, 246, 0.15)';
                    el.style.border = '1.5px dashed #3b82f6';
                    el.title = '📌 Texto Fijo: ' + label;
                }

                // Eventos Drag & Drop con el mouse
                el.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    draggedElement = el;
                    dragStartX = e.clientX;
                    dragStartY = e.clientY;

                    document.querySelectorAll('.text-element').forEach(t => t.classList.remove('selected-el'));
                    document.querySelectorAll('.campo-item').forEach(c => c.classList.remove('active-card'));
                    el.classList.add('selected-el');

                    const targetCard = document.querySelector(`.campo-item[data-index="${index}"]`);
                    if (targetCard) {
                        targetCard.classList.add('active-card');
                        targetCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }

                    const currentX = parseFloat(item.querySelector('[name="campo_x[]"]').value) || 0;
                    const currentY = parseFloat(item.querySelector('[name="campo_y[]"]').value) || 0;
                    elInitialLeft = currentX;
                    elInitialTop = currentY;

                    function onMouseMove(moveEvent) {
                        if (!draggedElement) return;
                        const containerRect = previewContainer.getBoundingClientRect();
                        const deltaXPx = moveEvent.clientX - dragStartX;
                        const deltaYPx = moveEvent.clientY - dragStartY;

                        const deltaXMm = (deltaXPx / containerRect.width) * docW;
                        const deltaYMm = (deltaYPx / containerRect.height) * docH;

                        let newX = Math.round((elInitialLeft + deltaXMm) * 10) / 10;
                        let newY = Math.round((elInitialTop + deltaYMm) * 10) / 10;

                        if (newX < 0) newX = 0;
                        if (newX > docW) newX = docW;
                        if (newY < 0) newY = 0;
                        if (newY > docH) newY = docH;

                        item.querySelector('[name="campo_x[]"]').value = newX;
                        item.querySelector('[name="campo_y[]"]').value = newY;

                        el.style.left = (newX * 100 / docW) + '%';
                        el.style.top = (newY * 100 / docH) + '%';
                    }

                    function onMouseUp() {
                        draggedElement = null;
                        window.removeEventListener('mousemove', onMouseMove);
                        window.removeEventListener('mouseup', onMouseUp);
                    }

                    window.addEventListener('mousemove', onMouseMove);
                    window.addEventListener('mouseup', onMouseUp);
                });

                elementsLayer.appendChild(el);
            });
        }

        container.addEventListener('input', updatePreview);
        window.addEventListener('resize', updatePreview);
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(updatePreview, 200);
        });
    </script>
@endsection
