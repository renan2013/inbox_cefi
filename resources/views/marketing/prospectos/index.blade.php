@extends('layouts.app')

@section('title', 'Inbox BPM - Base de Datos de Prospectos y Leads Externos')

@section('styles')
<style>
    .page-header-prospectos {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%);
        border: 1px solid var(--border-dark);
        border-radius: 1.5rem;
        padding: 2.2rem;
        margin-bottom: 2rem;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
    }

    [data-theme="light"] .page-header-prospectos {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.04) !important;
    }

    [data-theme="light"] .page-header-prospectos h2 {
        color: #0f172a !important;
    }

    [data-theme="light"] .page-header-prospectos p,
    [data-theme="light"] .page-header-prospectos .subtext {
        color: #64748b !important;
    }

    .kpi-card-lead {
        background: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        padding: 1.5rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        transition: transform 0.2s;
    }

    .kpi-card-lead:hover {
        transform: translateY(-2px);
    }

    [data-theme="light"] .kpi-card-lead {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
    }

    [data-theme="light"] .kpi-card-lead .kpi-num {
        color: #0f172a !important;
    }

    [data-theme="light"] .kpi-card-lead .kpi-label {
        color: #64748b !important;
    }

    .table-container-card {
        background: var(--card-dark);
        border: 1px solid var(--border-dark);
        border-radius: 1.25rem;
        padding: 1.5rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    [data-theme="light"] .table-container-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
    }

    .badge-lead-origin {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.3);
        border-radius: 2rem;
        padding: 0.3rem 0.75rem;
        font-size: 0.78rem;
        font-weight: 600;
    }

    [data-theme="light"] .badge-lead-origin {
        background: #eff6ff !important;
        color: #1d4ed8 !important;
        border-color: #bfdbfe !important;
    }

    .form-control-custom, .form-select-custom {
        background-color: rgba(15, 23, 42, 0.6);
        border: 2px solid var(--border-dark);
        border-radius: 0.75rem;
        color: #f8fafc;
        padding: 0.6rem 0.9rem;
    }

    [data-theme="light"] .form-control-custom,
    [data-theme="light"] .form-select-custom {
        background-color: #ffffff !important;
        border: 1.5px solid #cbd5e1 !important;
        color: #0f172a !important;
    }

    .form-control-custom:focus, .form-select-custom:focus {
        border-color: #25d366 !important;
        box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.2) !important;
        outline: none;
    }
</style>
@endsection

@section('content')
<div class="container py-4">

    <!-- Encabezado -->
    <div class="page-header-prospectos d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-primary px-3 py-1 rounded-pill"><i class="bi bi-person-lines-fill me-1"></i> Base Externa de Marketing</span>
                <span class="subtext small">Contactos independientes de usuarios y alumnos</span>
            </div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-database-fill-gear text-primary me-2"></i> Base de Datos de Prospectos y Leads
            </h2>
            <p class="mb-0">
                Registra o importa números telefónicos de listas externas, colegios o campañas de redes para difundir la oferta académica por WhatsApp.
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-success rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalPegadoRapido">
                <i class="bi bi-clipboard-plus me-1"></i> Pegado Rápido
            </button>
            <button class="btn btn-outline-primary rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalImportarCsv">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Importar CSV
            </button>
            <button class="btn btn-outline-secondary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalNuevoProspecto">
                <i class="bi bi-person-plus me-1"></i> Nuevo Manual
            </button>
            <a href="{{ route('whatsapp.campanas.index') }}" class="btn btn-warning rounded-pill px-3 fw-bold text-dark">
                <i class="bi bi-megaphone-fill me-1"></i> Lanzar Campaña WhatsApp
            </a>
        </div>
    </div>

    <!-- Alertas Flash -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tarjetas KPI -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="kpi-card-lead d-flex align-items-center justify-content-between">
                <div>
                    <div class="kpi-label small fw-semibold">Total Prospectos en Base</div>
                    <div class="kpi-num fs-2 fw-bold">{{ number_format($totalProspectos) }}</div>
                </div>
                <div class="rounded-circle p-3 bg-primary bg-opacity-10 text-primary fs-3">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card-lead d-flex align-items-center justify-content-between">
                <div>
                    <div class="kpi-label small fw-semibold">Con WhatsApp Normalizado</div>
                    <div class="kpi-num fs-2 fw-bold text-success">{{ number_format($totalActivosWhatsApp) }}</div>
                </div>
                <div class="rounded-circle p-3 bg-success bg-opacity-10 text-success fs-3">
                    <i class="bi bi-whatsapp"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card-lead d-flex align-items-center justify-content-between">
                <div>
                    <div class="kpi-label small fw-semibold">Listas / Orígenes Registrados</div>
                    <div class="kpi-num fs-2 fw-bold text-info">{{ count($origenes) }}</div>
                </div>
                <div class="rounded-circle p-3 bg-info bg-opacity-10 text-info fs-3">
                    <i class="bi bi-tags-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="table-container-card mb-4">
        <form action="{{ route('marketing.prospectos.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold text-muted">Buscar por Nombre, Teléfono o Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="buscar" class="form-control-custom border-start-0 w-100" 
                           placeholder="Ej: 87777849 o Carlos..." value="{{ request('buscar') }}">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Filtrar por Lista / Origen</label>
                <select name="origen" class="form-select-custom w-100" onchange="this.form.submit()">
                    <option value="todos">-- Todos los orígenes ({{ $totalProspectos }}) --</option>
                    @foreach($origenes as $o)
                        <option value="{{ $o }}" {{ request('origen') == $o ? 'selected' : '' }}>{{ $o }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill px-4 w-100 fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                @if(request()->filled('buscar') || (request()->filled('origen') && request('origen') != 'todos'))
                    <a href="{{ route('marketing.prospectos.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Limpiar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Prospectos -->
    <div class="table-container-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="fw-bold mb-0">Listado de Prospectos</h5>
            
            @if(request()->filled('origen') && request('origen') != 'todos')
                <form action="{{ route('marketing.prospectos.eliminar_origen') }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar TODOS los contactos de este origen? Esta acción no se puede deshacer.');">
                    @csrf
                    <input type="hidden" name="origen_eliminar" value="{{ request('origen') }}">
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">
                        <i class="bi bi-trash3 me-1"></i> Eliminar lote "{{ request('origen') }}"
                    </button>
                </form>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nombre y Apellidos</th>
                        <th>WhatsApp / Teléfono</th>
                        <th>Email</th>
                        <th>Lista / Origen</th>
                        <th>Interés</th>
                        <th>Registrado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prospectos as $p)
                        <tr>
                            <td class="text-muted small">{{ $p->id }}</td>
                            <td>
                                <div class="fw-bold">{{ $p->nombre_completo }}</div>
                            </td>
                            <td>
                                <a href="https://api.whatsapp.com/send?phone={{ $p->telefono }}" target="_blank" class="text-decoration-none fw-semibold text-success">
                                    <i class="bi bi-whatsapp me-1"></i> +{{ $p->telefono }}
                                </a>
                            </td>
                            <td class="small text-muted">{{ $p->email ?: 'N/D' }}</td>
                            <td>
                                <span class="badge-lead-origin">{{ $p->origen }}</span>
                            </td>
                            <td class="small">{{ $p->interes ?: '-' }}</td>
                            <td class="small text-muted">{{ $p->created_at ? $p->created_at->format('d/m/Y') : 'N/D' }}</td>
                            <td class="text-end">
                                <form action="{{ route('marketing.prospectos.destroy', $p->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este prospecto?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-50"></i>
                                No se encontraron prospectos con los filtros seleccionados.<br>
                                <button class="btn btn-success btn-sm rounded-pill mt-2" data-bs-toggle="modal" data-bs-target="#modalPegadoRapido">
                                    <i class="bi bi-clipboard-plus me-1"></i> Cargar los primeros contactos
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="mt-4 d-flex justify-content-end">
            {{ $prospectos->links() }}
        </div>
    </div>

</div>

<!-- Modal Pegado Rápido -->
<div class="modal fade" id="modalPegadoRapido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 1.25rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-clipboard-plus-fill text-success"></i> Carga Rápida: Copiar y Pegar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('marketing.prospectos.importar_pegado') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <p class="small text-muted mb-3">
                        Copia una lista desde Excel, WhatsApp o bloc de notas y pégala aquí. El sistema detecta nombres, teléfonos y correos automáticamente, eliminando guiones y duplicados.
                    </p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nombre de la Lista / Origen:</label>
                            <input type="text" name="origen_lote" class="form-control-custom w-100" 
                                   required placeholder="Ej: Feria Vocacional Mayo 2026" value="Lista Externa {{ date('d/m/Y') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Programa / Área de Interés (Opcional):</label>
                            <input type="text" name="interes_lote" class="form-control-custom w-100" 
                                   placeholder="Ej: Licenciaturas o Maestrías">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pega los contactos aquí (uno por línea):</label>
                        <textarea name="texto_pegado" rows="9" class="form-control-custom w-100 font-monospace small" 
                                  required placeholder="Carlos Mendoza 8777-7849 carlos@ejemplo.com&#10;María González 506 88991122&#10;83445566 Pedro Solano&#10;..."></textarea>
                    </div>

                    <div class="alert alert-info small mb-0 border-0">
                        💡 <strong>Formatos aceptados:</strong> Reconoce números con o sin 506, con guiones o espacios (ej. `8888-9999`, `+506 8888 9999`, `88889999`).
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Procesar e Importar Contactos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Importar CSV -->
<div class="modal fade" id="modalImportarCsv" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 1.25rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-spreadsheet text-primary"></i> Importar Archivo CSV
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('marketing.prospectos.importar_csv') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nombre de la Lista / Origen:</label>
                        <input type="text" name="origen_archivo" class="form-control-custom w-100" 
                               required placeholder="Ej: Base Contactos Colegios 2026">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Seleccionar archivo CSV (.csv):</label>
                        <input type="file" name="archivo_csv" accept=".csv,.txt" class="form-control-custom w-100" required>
                    </div>

                    <p class="small text-muted mb-0">
                        El archivo puede contener columnas para Nombre, Teléfono y Correo.
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Subir y Procesar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nuevo Prospecto Manual -->
<div class="modal fade" id="modalNuevoProspecto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 1.25rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-person-plus-fill text-success"></i> Nuevo Contacto Individual
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('marketing.prospectos.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Teléfono / WhatsApp (*):</label>
                        <input type="text" name="telefono" class="form-control-custom w-100" required placeholder="506 8777-7849">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Nombre:</label>
                            <input type="text" name="nombre" class="form-control-custom w-100" placeholder="Carlos">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Apellidos:</label>
                            <input type="text" name="apellidos" class="form-control-custom w-100" placeholder="Mendoza">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email (Opcional):</label>
                        <input type="email" name="email" class="form-control-custom w-100" placeholder="correo@ejemplo.com">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Origen / Lista:</label>
                            <input type="text" name="origen" class="form-control-custom w-100" placeholder="Ej: Referido Directo">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Interés:</label>
                            <input type="text" name="interes" class="form-control-custom w-100" placeholder="Ej: Teología">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Guardar Contacto</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
