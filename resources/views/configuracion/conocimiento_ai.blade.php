@extends('layouts.app')

@section('title', 'Inbox BPM - Base de Conocimiento IA')

@section('styles')
    <style>
        .page-header {
            background: var(--card-dark, #ffffff);
            padding: 2rem 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04);
            margin-bottom: 2rem;
            border: 1px solid var(--border-dark, #e2e8f0);
        }
        .section-title {
            font-weight: 800;
            color: var(--text-dark, #0f172a);
            font-size: 1.85rem;
        }
        .card-custom {
            background: var(--card-dark, #ffffff);
            border-radius: 1.25rem;
            border: 1px solid var(--border-dark, #e2e8f0);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.04);
            padding: 1.5rem;
        }
        .thumb-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 0.75rem;
            border: 1px solid var(--border-dark, #cbd5e1);
            cursor: pointer;
            transition: transform 0.2s;
        }
        .thumb-img:hover {
            transform: scale(1.1);
        }
        .item-title {
            color: #ffffff;
        }
        [data-theme="light"] .item-title,
        [data-theme="light"] .section-title {
            color: #0f172a !important;
        }
        [data-theme="light"] .text-adaptive-muted {
            color: #64748b !important;
        }
        .text-adaptive-muted {
            color: rgba(255, 255, 255, 0.65);
        }
        .pagination {
            margin-bottom: 0;
            gap: 4px;
        }
        .pagination .page-link {
            border-radius: 8px !important;
            border: 1px solid var(--border-dark, #334155);
            background: var(--card-dark, #1e293b);
            color: #94a3b8;
            padding: 0.45rem 0.85rem;
            font-weight: 600;
            font-size: 0.875rem;
        }
        .pagination .page-item.active .page-link {
            background: var(--primary, #5fb230) !important;
            border-color: var(--primary, #5fb230) !important;
            color: #ffffff !important;
        }
        .pagination .page-link:hover {
            background: rgba(95, 178, 48, 0.15);
            color: #ffffff;
        }
        [data-theme="light"] .pagination .page-link {
            background: #ffffff;
            border-color: #e2e8f0;
            color: #475569;
        }
        [data-theme="light"] .pagination .page-item.active .page-link {
            background: var(--primary, #5fb230) !important;
            border-color: var(--primary, #5fb230) !important;
            color: #ffffff !important;
        }
        .num-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            height: 32px;
            padding: 0 10px;
            font-size: 0.85rem;
            font-weight: 800;
            border-radius: 8px;
            background: rgba(95, 178, 48, 0.15);
            color: var(--primary, #5fb230);
            border: 1px solid rgba(95, 178, 48, 0.3);
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-3 px-md-4 py-4">
        
        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50 small fw-semibold"><i class="bi bi-house-door me-1"></i> Inicio</a></li>
                        <li class="breadcrumb-item active text-success small fw-bold" aria-current="page">Base de Conocimiento IA</li>
                    </ol>
                </nav>
                <h1 class="section-title mb-1 text-white"><i class="bi bi-cpu-fill text-success me-2"></i> Cerebro RAG - Inbox AI 2.0</h1>
                <p class="text-white-50 mb-0">Administra las instrucciones, respuestas, enlaces a módulos y capturas de pantalla que utiliza el asistente de IA (50 preguntas por página).</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 rounded-pill border border-success-subtle">
                    <i class="bi bi-collection me-1"></i> Total: {{ $conocimientos->total() }}
                </span>
                <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" style="background-color: var(--primary, #5fb230); border: none;" onclick="abrirModal(0)">
                    <i class="bi bi-plus-lg me-1"></i> Agregar Conocimiento
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Barra de Filtros y Búsqueda -->
        <div class="card-custom mb-4 p-3">
            <form method="GET" action="{{ route('configuracion.conocimiento_ai') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary-subtle text-adaptive-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="buscar" class="form-control" placeholder="Buscar por pregunta, respuesta o palabra clave..." value="{{ request('buscar') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary-subtle text-adaptive-muted"><i class="bi bi-tag"></i></span>
                        <select name="categoria" class="form-select" onchange="this.form.submit()">
                            <option value="todas">-- Todas las Categorías ({{ count($categorias) }}) --</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat }}" {{ request('categoria') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold flex-grow-1" style="background-color: var(--primary, #5fb230); border: none;">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
                    @if(request()->filled('buscar') || (request()->filled('categoria') && request('categoria') !== 'todas'))
                        <a href="{{ route('configuracion.conocimiento_ai') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Limpiar Filtros">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabla de Registros -->
        <div class="card-custom">
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-uppercase small fw-bold text-secondary">
                            <th class="text-center" style="width: 75px;"># N°</th>
                            <th style="width: 75px;">Captura</th>
                            <th>Pregunta / Título</th>
                            <th style="width: 200px;">Ruta / Módulo Vinculado</th>
                            <th style="width: 170px;">Categoría</th>
                            <th class="text-center" style="width: 90px;">Estado</th>
                            <th class="text-end" style="width: 110px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($conocimientos as $r)
                            <tr>
                                <td class="text-center">
                                    <span class="num-badge">
                                        #{{ ($conocimientos->currentPage() - 1) * $conocimientos->perPage() + $loop->iteration }}
                                    </span>
                                </td>
                                <td>
                                    @if (!empty($r->imagen) && file_exists(public_path('uploads/conocimiento_ai/' . $r->imagen)))
                                        <img src="{{ asset('uploads/conocimiento_ai/' . $r->imagen) }}" class="thumb-img" onclick="verImagen('{{ asset('uploads/conocimiento_ai/' . $r->imagen) }}')">
                                    @else
                                        <span class="badge bg-light text-muted border rounded-circle p-2"><i class="bi bi-image"></i></span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold fs-6 item-title">{{ $r->pregunta }}</div>
                                    <small class="text-adaptive-muted d-block text-truncate" style="max-width: 450px;">{!! strip_tags($r->respuesta) !!}</small>
                                </td>
                                <td>
                                    @if (!empty($r->ruta))
                                        <span class="badge bg-success-subtle text-success rounded-pill fw-bold"><i class="bi bi-link-45deg me-1"></i> {{ $r->ruta }}</span>
                                    @else
                                        <span class="text-adaptive-muted small">Sin enlace</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info rounded-pill fw-bold">{{ $r->categoria }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($r->estado == 1)
                                        <span class="badge bg-success-subtle text-success rounded-pill">Activo</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger rounded-pill">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill me-1" onclick='editarRegistro(@json($r))' title="Editar conocimiento">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('configuracion.conocimiento_ai.eliminar', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este conocimiento de la IA?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-adaptive-muted">
                                    <i class="bi bi-robot fs-1 d-block mb-2 text-success"></i>
                                    No se encontraron conocimientos registrados con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación de 50 en 50 -->
            @if($conocimientos->hasPages())
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 pt-3 mt-3 border-top border-secondary-subtle">
                    <div class="text-adaptive-muted small">
                        Mostrando <strong>{{ $conocimientos->firstItem() }}</strong> a <strong>{{ $conocimientos->lastItem() }}</strong> de <strong>{{ $conocimientos->total() }}</strong> preguntas (50 por página)
                    </div>
                    <div>
                        {{ $conocimientos->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @else
                <div class="d-flex justify-content-between align-items-center pt-3 mt-3 border-top border-secondary-subtle text-adaptive-muted small">
                    <span>Página 1 de 1</span>
                    <span>Total: <strong>{{ $conocimientos->total() }}</strong> preguntas</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Formulario -->
    <div class="modal fade" id="modalConocimiento" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form action="{{ route('configuracion.conocimiento_ai.guardar') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="modalId" value="0">
                    
                    <div class="modal-header bg-dark text-white rounded-top-4">
                        <h5 class="modal-title fw-bold" id="modalTitle"><i class="bi bi-robot text-success me-2"></i> Entrenar Inbox AI 2.0</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Pregunta / Título de la Consulta</label>
                            <input type="text" name="pregunta" id="modalPregunta" class="form-control rounded-3" placeholder="Ej: ¿Dónde veo el reporte de morosidad?" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Palabras Clave Trigger (Comas)</label>
                                <input type="text" name="palabras_clave" id="modalPalabras" class="form-control rounded-3" placeholder="Ej: mora, deudores, cobrar, lista de deuda">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark"><i class="bi bi-tag-fill text-success me-1"></i> Categoría (Escribe o Selecciona)</label>
                                <input type="text" name="categoria" id="modalCategoria" class="form-control rounded-3" list="categoriasList" placeholder="Ej: Sílabo de Curso, Finanzas..." required>
                                <datalist id="categoriasList">
                                    <option value="Sílabo de Curso">
                                    <option value="Finanzas y Morosidad">
                                    <option value="Cursos y Clases">
                                    <option value="Reportes y Documentos">
                                    <option value="Usuarios y Permisos">
                                    <option value="General / Sistema">
                                </datalist>
                            </div>
                        </div>

                        <!-- Selector Dinámico de Menú y Submódulo -->
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <label class="form-label fw-bold text-dark mb-2"><i class="bi bi-signpost-split-fill text-primary me-1"></i> Selección Dinámica de Menú y Módulo (Ruta Directa)</label>
                            <div class="row g-2 mb-2">
                                <div class="col-md-5">
                                    <select id="selectMenuPrincipal" class="form-select rounded-3" onchange="actualizarSubmodulos()">
                                        <option value="">1. Seleccionar Menú...</option>
                                        <option value="Registro">Registro</option>
                                        <option value="Gestionar Curso">Gestionar Curso</option>
                                        <option value="Estudiante">Estudiante</option>
                                        <option value="Tutor AI">Cerebro / Tutor AI</option>
                                        <option value="Configuración">Configuración</option>
                                        <option value="Soporte">Soporte</option>
                                        <option value="Planilla">Planilla</option>
                                        <option value="Logística">Logística</option>
                                        <option value="Custom">Escribir Manualmente...</option>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <select id="selectSubModulo" class="form-select rounded-3" onchange="fijarRutaCorta()" disabled>
                                        <option value="">2. Primero elige un menú arriba</option>
                                    </select>
                                </div>
                            </div>
                            <input type="text" name="ruta" id="modalRuta" class="form-control rounded-3" placeholder="Ruta del módulo (Ej: boletas.morosidad)">
                            <small class="text-muted d-block mt-1">Al elegir el Menú y Submódulo, el sistema fijará la ruta exacta para crear el botón de acceso directo en el chat.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Respuesta del Asistente (Soporta HTML)</label>
                            <textarea name="respuesta" id="modalRespuesta" class="form-control rounded-3" rows="3" placeholder="Escribe la explicación clara y concisa..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Captura de Pantalla / Imagen Guiada (Opcional)</label>
                            <input type="file" name="imagen" class="form-control rounded-3" accept="image/*">
                            <small class="text-muted">Si adjuntas una captura, Inbox AI la mostrará dentro del chat para guiar visualmente al usuario.</small>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="estado" id="modalEstado" value="1" checked>
                            <label class="form-check-label fw-semibold text-dark" for="modalEstado">Habilitar este conocimiento para Inbox AI</label>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold" style="background-color: var(--primary, #5fb230); border: none;">Guardar Conocimiento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Visor Imagen -->
    <div class="modal fade" id="modalImagen" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0 text-center">
                <img id="imgPreview" src="" class="img-fluid rounded-4 shadow-lg">
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const menuTree = {
            "Registro": [
                { nombre: "Generar Boleta de Pago", ruta: "boletas.generar" },
                { nombre: "Historial de Boletas", ruta: "boletas.index" },
                { nombre: "Estado de Cuenta", ruta: "boletas.estado_cuenta" },
                { nombre: "Control de Morosidad", ruta: "boletas.morosidad" },
                { nombre: "Expedientes Digitales 360°", ruta: "expedientes.index" },
                { nombre: "Crear Expediente Digital", ruta: "expedientes.create" },
                { nombre: "Registrar Usuario", ruta: "usuarios.create" },
                { nombre: "Lista de Usuarios", ruta: "usuarios.index" },
                { nombre: "Registrar Programa", ruta: "programas.create" },
                { nombre: "Registrar Curso", ruta: "cursos.create" },
                { nombre: "Gestionar Grupos", ruta: "grupos.index" },
                { nombre: "Convalidaciones & Homologaciones", ruta: "convalidaciones.index" },
                { nombre: "Retiros y Cambios de Curso", ruta: "retiros.index" },
                { nombre: "Auditoría y Graduaciones", ruta: "graduaciones.index" },
                { nombre: "Formularios de Registro", ruta: "solicitudes.index" },
                { nombre: "Lista de Programas", ruta: "programas.index" },
                { nombre: "Lista de Cursos", ruta: "cursos.index" },
                { nombre: "Programas Completos", ruta: "programas.completos" },
                { nombre: "Generador de Reportes", ruta: "reportes.index" },
                { nombre: "Utilitarios del Sistema", ruta: "utilitarios.index" }
            ],
            "Gestionar Curso": [
                { nombre: "Mis Cursos (Asignaciones)", ruta: "mis_cursos.index" },
                { nombre: "Mis Recursos Privados", ruta: "recursos.index" },
                { nombre: "Recursos Compartidos", ruta: "recursos.compartidos" },
                { nombre: "Videotutoriales", ruta: "videotutoriales.index" },
                { nombre: "Biblioteca de Medios", ruta: "biblioteca.index" },
                { nombre: "Supervisión de Cursos", ruta: "supervision.index" }
            ],
            "Estudiante": [
                { nombre: "Portal de Estudiantes", ruta: "estudiante.portal" },
                { nombre: "Bitácora de TCU", ruta: "tcu.index" }
            ],
            "Tutor AI": [
                { nombre: "Base de Conocimiento RAG", ruta: "configuracion.conocimiento_ai" }
            ],
            "Configuración": [
                { nombre: "Panel General de Configuración", ruta: "configuracion.index" },
                { nombre: "Pagos, Morosidad e Intereses", ruta: "configuracion.pagos" },
                { nombre: "Configuración Alertas", ruta: "configuracion.alertas" }
            ],
            "Soporte": [
                { nombre: "Gestionar Soporte", ruta: "soporte.gestionar" },
                { nombre: "Gestionar Categorías", ruta: "soporte.categorias" },
                { nombre: "Lista de Soporte", ruta: "soporte.lista" },
                { nombre: "Gestionar Claves", ruta: "claves.index" },
                { nombre: "Cerebro Inbox AI 2.0", ruta: "configuracion.conocimiento_ai" },
                { nombre: "Centro de Soporte - Credenciales", ruta: "centro_soporte.index" },
                { nombre: "Automatizaciones de Documentos", ruta: "plantillas.index" }
            ],
            "Planilla": [
                { nombre: "Registrar Asistencia", ruta: "asistencia.marcar" },
                { nombre: "Mi Credencial QR", ruta: "planilla.credencial" },
                { nombre: "Panel de Planilla", ruta: "planilla.index" },
                { nombre: "Estación de Escaneo", ruta: "planilla.escanear" },
                { nombre: "Configurar Empleados", ruta: "planilla.empleados" },
                { nombre: "Historial de Asistencias", ruta: "asistencia.historial" },
                { nombre: "Cálculo de Planilla", ruta: "planilla.calcular" },
                { nombre: "Historial de Pagos", ruta: "planilla.historial_pagos" }
            ],
            "Logística": [
                { nombre: "Control de Inventario", ruta: "inventario.index" },
                { nombre: "Operaciones de Bodega", ruta: "inventario.operaciones" }
            ]
        };

        function actualizarSubmodulos() {
            const menuVal = document.getElementById('selectMenuPrincipal').value;
            const subSelect = document.getElementById('selectSubModulo');
            const rutaInput = document.getElementById('modalRuta');

            subSelect.innerHTML = '<option value="">-- Seleccionar Módulo --</option>';

            if (!menuVal || menuVal === 'Custom') {
                subSelect.disabled = true;
                if (menuVal === 'Custom') {
                    rutaInput.value = '';
                    rutaInput.focus();
                }
                return;
            }

            const items = menuTree[menuVal] || [];
            subSelect.disabled = false;
            items.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.ruta;
                opt.textContent = `${item.nombre} (${item.ruta})`;
                subSelect.appendChild(opt);
            });
        }

        function fijarRutaCorta() {
            const subSelect = document.getElementById('selectSubModulo');
            const rutaInput = document.getElementById('modalRuta');
            if (subSelect.value) {
                rutaInput.value = subSelect.value;
            }
        }

        function abrirModal(id) {
            document.getElementById('modalId').value = '0';
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-robot text-success me-2"></i> Agregar Conocimiento a Inbox AI';
            document.getElementById('modalPregunta').value = '';
            document.getElementById('modalPalabras').value = '';
            document.getElementById('modalRespuesta').value = '';
            document.getElementById('modalCategoria').value = 'General';
            document.getElementById('selectMenuPrincipal').value = '';
            document.getElementById('selectSubModulo').innerHTML = '<option value="">2. Primero elige un menú arriba</option>';
            document.getElementById('selectSubModulo').disabled = true;
            document.getElementById('modalRuta').value = '';
            document.getElementById('modalEstado').checked = true;
            new bootstrap.Modal(document.getElementById('modalConocimiento')).show();
        }

        function editarRegistro(data) {
            document.getElementById('modalId').value = data.id;
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-square text-warning me-2"></i> Editar Conocimiento #' + data.id;
            document.getElementById('modalPregunta').value = data.pregunta;
            document.getElementById('modalPalabras').value = data.palabras_clave || '';
            document.getElementById('modalRespuesta').value = data.respuesta;
            document.getElementById('modalCategoria').value = data.categoria || 'General';
            document.getElementById('modalRuta').value = data.ruta || '';
            document.getElementById('modalEstado').checked = (data.estado == 1);
            new bootstrap.Modal(document.getElementById('modalConocimiento')).show();
        }

        function verImagen(src) {
            document.getElementById('imgPreview').src = src;
            new bootstrap.Modal(document.getElementById('modalImagen')).show();
        }
    </script>
@endsection
