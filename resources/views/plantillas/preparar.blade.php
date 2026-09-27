@extends('layouts.app')

@section('title', 'Inbox BPM - Preparar y Emitir Documento')

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
            padding: 0.65rem 0.95rem;
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
            margin-bottom: 0.4rem;
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
            transform-origin: top left;
            cursor: pointer;
            transition: all 0.15s;
        }

        .text-element:hover {
            outline: 2px dashed #38bdf8;
            border-radius: 4px;
        }

        .text-element.active-focus {
            outline: 2px solid #38bdf8 !important;
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.6);
            border-radius: 4px;
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

        .nav-pills-custom .nav-link {
            border-radius: 0.75rem;
            color: #94a3b8;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border: 1px solid transparent;
            transition: all 0.2s;
        }
        .nav-pills-custom .nav-link.active {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 15px rgba(95, 178, 48, 0.35);
        }
        .nav-pills-custom .nav-link:hover:not(.active) {
            background-color: rgba(255,255,255,0.05);
            color: #f8fafc;
        }

        .badge-dynamic {
            background: rgba(234, 179, 8, 0.2);
            color: #facc15;
            border: 1px solid rgba(234, 179, 8, 0.3);
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
                <li class="breadcrumb-item active text-white" aria-current="page">Emitir Documento</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 rounded-pill px-3 py-1 mb-2">Centro de Emisión y Automatización</span>
                <h1 class="display-6 fw-bold mb-1">{{ $plantilla->nombre }}</h1>
                <p class="text-white-50 mb-0">Genere certificados individuales al instante o emita de forma masiva en lote comprimido (ZIP).</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('plantillas.edit', $plantilla->id) }}" class="btn btn-outline-info rounded-pill px-4">
                    <i class="bi bi-pencil-square me-1"></i> Diseñador Gráfico
                </a>
                <a href="{{ route('plantillas.index') }}" class="btn btn-outline-light rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Plantillas
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2) !important;">
                <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @php
            $campos_dinamicos = [];
            foreach ($campos as $idx => $c) {
                if (($c['tipo'] ?? 'fijo') === 'dinamico') {
                    $campos_dinamicos[] = array_merge($c, ['_orig_idx' => $idx]);
                }
            }
        @endphp

        <!-- ALERTA SI LA PLANTILLA NO TIENE CAMPOS DINÁMICOS -->
        @if (count($campos_dinamicos) === 0)
            <div class="alert alert-warning border-0 rounded-4 p-4 mb-4 d-flex align-items-start gap-3" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3) !important;">
                <i class="bi bi-exclamation-triangle-fill fs-3 text-warning mt-1"></i>
                <div class="flex-grow-1">
                    <h5 class="fw-bold text-warning mb-1">¡Esta plantilla no tiene campos dinámicos (variables)!</h5>
                    <p class="text-white-50 mb-3">Todos los campos configurados actualmente están guardados como "Texto Fijo (Permanente)". Para que el formulario te solicite el nombre del alumno, curso o fecha al emitir, debes configurar al menos un campo como "⚡ Texto Dinámico".</p>
                    <a href="{{ route('plantillas.edit', $plantilla->id) }}" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">
                        <i class="bi bi-pencil-square me-1"></i> Abrir Diseñador y Convertir a Dinámico
                    </a>
                </div>
            </div>
        @endif

        <div class="row g-4">
            <!-- PANEL IZQUIERDO: LIENZO REACTIVO EN VIVO -->
            <div class="col-lg-7">
                <div class="card glass-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-white"><i class="bi bi-eye me-2 text-info"></i> Previsualización en Tiempo Real</h5>
                        <span class="badge bg-secondary font-monospace">{{ $plantilla->ancho_mm ?: 279 }} x {{ $plantilla->alto_mm ?: 216 }} mm</span>
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

                    <div class="d-flex justify-content-between align-items-center mt-3 text-white-50 small">
                        <span><i class="bi bi-info-circle me-1"></i> Al escribir en el formulario, el certificado se actualiza automáticamente.</span>
                        <span><i class="bi bi-hand-index-thumb me-1"></i> Haz clic en un texto para enfocar su campo.</span>
                    </div>
                </div>
            </div>

            <!-- PANEL DERECHO: PESTAÑAS (INDIVIDUAL VS LOTES) -->
            <div class="col-lg-5">
                <div class="card glass-card p-4">
                    <ul class="nav nav-pills nav-pills-custom mb-4" id="modoEmisionTabs" role="tablist">
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link active w-100 text-center" id="tab-individual" data-bs-toggle="pill" data-bs-target="#panel-individual" type="button" role="tab">
                                <i class="bi bi-person-fill me-1"></i> Emisión Individual
                            </button>
                        </li>
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link w-100 text-center" id="tab-lote" data-bs-toggle="pill" data-bs-target="#panel-lote" type="button" role="tab">
                                <i class="bi bi-file-earmark-zip-fill me-1"></i> Generar por Lotes
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- TAB 1: EMISIÓN INDIVIDUAL -->
                        <div class="tab-pane fade show active" id="panel-individual" role="tabpanel">
                            <form action="{{ route('plantillas.generar') }}" method="POST" enctype="multipart/form-data" target="_blank" id="formIndividual">
                                @csrf
                                <input type="hidden" name="plantilla_id" value="{{ $plantilla->id }}">

                                <h6 class="text-white fw-bold mb-3 d-flex align-items-center">
                                    <i class="bi bi-card-text me-2 text-warning"></i> Datos del Documento
                                    <span class="badge badge-dynamic ms-auto fw-bold" style="font-size: 0.7rem;">
                                        {{ count($campos_dinamicos) }} {{ count($campos_dinamicos) === 1 ? 'campo dinámico' : 'campos dinámicos' }}
                                    </span>
                                </h6>

                                @if (count($campos_dinamicos) > 0)
                                    @foreach ($campos_dinamicos as $k => $c)
                                        <div class="mb-3">
                                            <label class="form-label-custom d-flex justify-content-between align-items-center">
                                                <span>{{ $c['label'] }}</span>
                                                <span class="text-white-50 font-monospace" style="font-size: 0.65rem;">Pos: X={{ $c['x'] }}mm Y={{ $c['y'] }}mm</span>
                                            </label>
                                            <input type="text" name="campo_valor[]" class="form-control form-control-custom input-dinamico" data-orig-idx="{{ $c['_orig_idx'] }}" placeholder="Escribe {{ strtolower($c['label']) }}..." value="{{ $c['label'] }}" required>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="p-3 mb-3 rounded-3 text-center text-white-50" style="background: rgba(15, 23, 42, 0.4); border: 1px dashed var(--border-dark);">
                                        No hay campos variables asignados a esta plantilla.
                                    </div>
                                @endif

                                <!-- Campos adicionales de Foto y QR si existen -->
                                @php
                                    $hasFoto = false; $hasQrInfo = false;
                                    foreach ($campos as $c) {
                                        if (($c['tipo'] ?? '') === 'foto') $hasFoto = true;
                                        if (($c['tipo'] ?? '') === 'qr_info') $hasQrInfo = true;
                                    }
                                @endphp

                                @if ($hasFoto)
                                    <div class="mb-3">
                                        <label class="form-label-custom">Foto del Estudiante (Opcional)</label>
                                        <input type="file" name="foto_estudiante" class="form-control form-control-custom" accept="image/*">
                                    </div>
                                @endif

                                @if ($hasQrInfo)
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label-custom">Institución / Emisor</label>
                                            <input type="text" name="qr_universidad" class="form-control form-control-custom form-control-sm" value="{{ config('cliente.nombre', 'CEFI') }}">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label-custom">Periodo Académico</label>
                                            <input type="text" name="qr_periodo" class="form-control form-control-custom form-control-sm" placeholder="Ej: 2026-I">
                                        </div>
                                    </div>
                                @endif

                                <div class="row g-2 mb-4">
                                    <div class="col-6">
                                        <label class="form-label-custom">Formato</label>
                                        <select name="formato" class="form-select form-select-custom">
                                            <option value="pdf" selected>Documento PDF (.pdf)</option>
                                            <option value="png">Imagen HD (.png)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Acción</label>
                                        <select name="modo" class="form-select form-select-custom">
                                            <option value="download" selected>Descargar Archivo</option>
                                            <option value="preview">Abrir en Pestaña</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success rounded-pill py-2.5 fw-bold" style="background-color: var(--primary); border: none;">
                                        <i class="bi bi-printer-fill me-1"></i> Generar Documento
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- TAB 2: GENERACIÓN MASIVA POR LOTES (ZIP) -->
                        <div class="tab-pane fade" id="panel-lote" role="tabpanel">
                            <form action="{{ route('plantillas.generar_lote_zip') }}" method="POST" id="formLote">
                                @csrf
                                <input type="hidden" name="plantilla_id" value="{{ $plantilla->id }}">

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-white fw-bold mb-0"><i class="bi bi-table me-2 text-success"></i> Lista de Participantes</h6>
                                    <span class="badge bg-secondary font-monospace" id="batchCountBadge">0 filas detectadas</span>
                                </div>

                                <p class="text-white-50 small mb-2">
                                    Copia y pega desde <strong>Excel</strong> o <strong>Google Sheets</strong>, o separa por punto y coma (<code>;</code>) o coma (<code>,</code>).
                                </p>

                                @if (count($campos_dinamicos) > 0)
                                    <div class="mb-2 p-2 rounded-3 small" style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-dark);">
                                        <span class="text-white-50 d-block mb-1">Orden de columnas requerido:</span>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($campos_dinamicos as $idx => $c)
                                                <span class="badge bg-dark border border-secondary text-light font-monospace">Col {{ $idx + 1 }}: {{ $c['label'] }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <textarea name="datos_lote" id="textareaLote" class="form-control form-control-custom font-monospace" rows="7" placeholder="Juan Perez	juan@{{ config('cliente.id', 'cefi') }}.cr	Aprobado
Maria Gonzalez	maria@{{ config('cliente.id', 'cefi') }}.cr	Sobresaliente
Carlos Morales	carlos@{{ config('cliente.id', 'cefi') }}.cr	Aprobado" required></textarea>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <input type="file" id="fileCsvInput" accept=".csv, .txt" class="d-none">
                                        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="document.getElementById('fileCsvInput').click();">
                                            <i class="bi bi-file-earmark-arrow-up me-1"></i> Cargar CSV / TXT
                                        </button>
                                    </div>
                                    <span class="text-white-50 small" id="detectedDelimiterBadge">Delimitador: Auto</span>
                                </div>

                                <!-- PREVISUALIZACIÓN DE TABLA DE FILAS DETECTADAS -->
                                <div id="tablePreviewContainer" class="table-responsive mb-3 d-none" style="max-height: 180px; border-radius: 0.75rem; border: 1px solid var(--border-dark);">
                                    <table class="table table-dark table-sm table-striped mb-0 small" id="previewLoteTable">
                                        <thead>
                                            <tr class="text-white-50">
                                                <th>#</th>
                                                @if (count($campos_dinamicos) > 0)
                                                    @foreach ($campos_dinamicos as $c)
                                                        <th>{{ $c['label'] }}</th>
                                                    @endforeach
                                                @else
                                                    <th>Columna 1</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody id="previewLoteTbody"></tbody>
                                    </table>
                                </div>

                                <button type="submit" class="btn btn-primary rounded-pill w-100 py-2.5 fw-bold" id="btnGenerarLote">
                                    <i class="bi bi-file-earmark-zip-fill me-1"></i> GENERAR LOTE (ZIP)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const previewContainer = document.getElementById('preview-container');
        const elementsLayer = document.getElementById('elements-layer');
        const camposConfig = @json($campos);
        const docW = {{ $plantilla->ancho_mm ?: 279 }};
        const docH = {{ $plantilla->alto_mm ?: 216 }};

        function renderCanvasElements() {
            elementsLayer.innerHTML = '';
            const rect = previewContainer.getBoundingClientRect();
            const inputsDinamicos = document.querySelectorAll('.input-dinamico');

            camposConfig.forEach((campo, index) => {
                const el = document.createElement('div');
                el.className = 'text-element';
                el.dataset.origIdx = index;
                el.style.fontFamily = (campo.font || 'Arial') + ', sans-serif';

                const x = parseFloat(campo.x) || 0;
                const y = parseFloat(campo.y) || 0;
                const size = parseFloat(campo.size) || 14;
                const color = campo.color || '#000000';
                const align = campo.align || 'L';
                const tipo = campo.tipo || 'fijo';

                let texto = campo.label || '';

                if (tipo === 'dinamico') {
                    // Buscar input correspondiente
                    inputsDinamicos.forEach(inp => {
                        if (parseInt(inp.dataset.origIdx) === index && inp.value.trim() !== '') {
                            texto = inp.value;
                        }
                    });
                }

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
                    el.innerText = texto;
                    const fontSizePx = (size * 0.3527 * rect.width) / docW;
                    el.style.fontSize = fontSizePx + 'px';
                    el.style.color = color;
                }

                el.style.top = (y * 100 / docH) + '%';
                el.style.left = (x * 100 / docW) + '%';

                if (align === 'C') el.style.transform = 'translateX(-50%)';
                else if (align === 'R') el.style.transform = 'translateX(-100%)';
                else el.style.transform = 'none';

                // Al hacer clic sobre el texto en el certificado, enfocar su input si es dinámico
                el.addEventListener('click', () => {
                    document.querySelectorAll('.text-element').forEach(t => t.classList.remove('active-focus'));
                    el.classList.add('active-focus');
                    const targetInp = document.querySelector(`.input-dinamico[data-orig-idx="${index}"]`);
                    if (targetInp) {
                        targetInp.focus();
                        targetInp.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });

                elementsLayer.appendChild(el);
            });
        }

        // Reactividad en vivo al escribir en inputs
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('input-dinamico')) {
                renderCanvasElements();
            }
        });

        window.addEventListener('resize', renderCanvasElements);
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(renderCanvasElements, 200);
        });

        // ==========================
        // PROCESAMIENTO EN LOTE (ZIP)
        // ==========================
        const textareaLote = document.getElementById('textareaLote');
        const batchCountBadge = document.getElementById('batchCountBadge');
        const detectedDelimiterBadge = document.getElementById('detectedDelimiterBadge');
        const tablePreviewContainer = document.getElementById('tablePreviewContainer');
        const previewLoteTbody = document.getElementById('previewLoteTbody');
        const fileCsvInput = document.getElementById('fileCsvInput');

        function parseLoteText() {
            const raw = textareaLote.value.trim();
            if (!raw) {
                batchCountBadge.innerText = '0 filas detectadas';
                tablePreviewContainer.classList.add('d-none');
                return;
            }

            const lines = raw.split('\n').map(l => l.trim()).filter(l => l.length > 0);
            batchCountBadge.innerText = `${lines.length} fila${lines.length === 1 ? '' : 's'} detectada${lines.length === 1 ? '' : 's'}`;

            // Auto-detectar delimitador
            const sample = lines.slice(0, 5).join('\n');
            const tabs = (sample.match(/\t/g) || []).length;
            const semicolons = (sample.match(/;/g) || []).length;
            const commas = (sample.match(/,/g) || []).length;

            let delim = ';';
            let delimName = 'Punto y coma (;)';
            if (tabs >= semicolons && tabs >= commas && tabs > 0) {
                delim = '\t';
                delimName = 'Tabulación (Excel)';
            } else if (semicolons >= commas && semicolons > 0) {
                delim = ';';
                delimName = 'Punto y coma (;)';
            } else if (commas > 0) {
                delim = ',';
                delimName = 'Coma (,)';
            }
            detectedDelimiterBadge.innerText = `Delimitador: ${delimName}`;

            // Renderizar tabla de previsualización (primeras 5 filas)
            previewLoteTbody.innerHTML = '';
            lines.slice(0, 5).forEach((line, i) => {
                const cols = line.split(delim).map(c => c.trim());
                const tr = document.createElement('tr');
                let html = `<td class="text-white-50">${i + 1}</td>`;
                cols.forEach(c => {
                    html += `<td>${c}</td>`;
                });
                tr.innerHTML = html;
                previewLoteTbody.appendChild(tr);
            });
            tablePreviewContainer.classList.remove('d-none');
        }

        textareaLote.addEventListener('input', parseLoteText);

        // Cargar archivo CSV / TXT
        fileCsvInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (evt) => {
                    textareaLote.value = evt.target.result;
                    parseLoteText();
                };
                reader.readAsText(file);
            }
        });

        // Alerta animada al generar Lote ZIP
        document.getElementById('formLote').addEventListener('submit', function() {
            Swal.fire({
                title: 'Compilando Lote...',
                text: 'Generando todos los certificados y empaquetando en archivo ZIP. Por favor espere...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            // Cerrar el modal después de 4 segundos ya que el navegador iniciará la descarga directa
            setTimeout(() => {
                Swal.close();
            }, 4500);
        });
    </script>
@endsection
