@extends('layouts.app')

@section('title', 'Inbox BPM - Centro de Soporte')

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
            transition: all 0.3s;
        }

        .glass-card:hover {
            transform: translateY(-4px);
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

        /* Dark themed transparent table list */
        .table-custom {
            margin-bottom: 0;
            background-color: transparent !important;
        }
        .table-custom td, .table-custom th {
            background-color: transparent !important;
            color: #e2e8f0 !important;
            border-bottom: 1px solid var(--border-dark);
            padding: 1.25rem 1.5rem;
        }

        /* Modal styling */
        .modal-content-custom {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
        }

        /* Custom dark pagination */
        .pagination {
            margin-bottom: 0;
            gap: 4px;
        }
        .page-item .page-link {
            background-color: rgba(15, 23, 42, 0.6);
            border-color: var(--border-dark);
            color: #e2e8f0;
            border-radius: 0.5rem !important;
            padding: 0.35rem 0.75rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        .page-item .page-link:hover {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #fff;
            font-weight: bold;
        }
        .page-item.disabled .page-link {
            background-color: rgba(15, 23, 42, 0.3);
            border-color: var(--border-dark);
            color: #64748b;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Centro de Soporte</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Centro de Soporte - Credenciales</h1>
                <p class="text-white-50 mb-0">Listado general de plataformas corporativas y accesos del personal.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#crearPlataformaModal">
                    <i class="bi bi-plus-lg me-1"></i> Nueva Plataforma
                </button>
                <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#crearCredencialModal" style="background-color: var(--primary); border: none;">
                    <i class="bi bi-key-fill me-1"></i> Nueva Credencial
                </button>
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
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <!-- CAJA DE BÚSQUEDA SUPERIOR -->
        <div class="card glass-card p-3 mb-4">
            <form method="GET" action="{{ route('centro_soporte.index') }}" class="row g-2 align-items-center">
                <div class="col-md-9 col-12">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary text-white-50"><i class="bi bi-search"></i></span>
                        <input type="text" name="buscar" id="buscarCredencialInput" class="form-control form-control-custom" placeholder="Buscar por plataforma, usuario, tipo o notas..." value="{{ request('buscar') }}">
                    </div>
                </div>
                <div class="col-md-3 col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-success rounded-pill px-4 flex-grow-1 fw-semibold" style="background-color: var(--primary); border: none;">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                    @if(request('buscar'))
                        <a href="{{ route('centro_soporte.index') }}" class="btn btn-outline-light rounded-pill px-3" title="Limpiar búsqueda">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="row g-4">
            <!-- PLATFORMS GRID -->
            <div class="col-md-4">
                <h5 class="fw-bold text-white mb-3"><i class="bi bi-globe2 text-success me-2"></i> Plataformas Integradas</h5>
                
                @if ($plataformas->count() > 0)
                    <div class="row g-3">
                        @foreach ($plataformas as $plat)
                            <div class="col-12">
                                <div class="card glass-card p-3 mb-0 d-flex flex-row justify-content-between align-items-center">
                                    <div class="overflow-hidden me-2" style="max-width: calc(100% - 75px);">
                                        <h6 class="fw-bold text-white mb-1 text-truncate" title="{{ $plat->nombre }}">{{ $plat->nombre }}</h6>
                                        <a href="{{ $plat->link_acceso }}" target="_blank" class="small text-success text-decoration-none font-monospace text-truncate d-block mb-0" title="{{ $plat->link_acceso }}">{{ $plat->link_acceso }}</a>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                        <button type="button" class="btn btn-sm btn-outline-light rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="editarPlataforma({{ $plat->id_plataforma }}, '{{ addslashes($plat->nombre) }}', '{{ addslashes($plat->link_acceso) }}')" title="Editar Plataforma">
                                            <i class="bi bi-pencil" style="font-size: 0.8rem;"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="eliminarPlataforma({{ $plat->id_plataforma }}, '{{ addslashes($plat->nombre) }}')" title="Eliminar Plataforma">
                                            <i class="bi bi-trash" style="font-size: 0.8rem;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="card glass-card p-4 text-center text-white-50">
                        No hay plataformas configuradas.
                    </div>
                @endif
            </div>

            <!-- CREDENTIALS LIST -->
            <div class="col-md-8">
                <h5 class="fw-bold text-white mb-3"><i class="bi bi-key-fill text-success me-2"></i> Credenciales de Acceso</h5>

                <div class="card glass-card">
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr class="bg-dark text-white-50">
                                    <th class="ps-4">Plataforma / Tipo</th>
                                    <th>Usuario</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($credenciales->count() > 0)
                                    @foreach ($credenciales as $c)
                                        <tr>
                                            <td class="ps-4">
                                                <strong class="text-white d-block">{{ $c->nombre_plataforma ?: 'Otra Plataforma' }}</strong>
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 fw-bold border border-success border-opacity-25" style="font-size: 0.65rem;">{{ $c->tipo ?: 'General' }}</span>
                                            </td>
                                            <td>
                                                <span class="text-white fw-semibold">{{ $c->usuario }}</span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-inline-flex gap-2">
                                                    <button type="button" 
                                                        class="btn btn-sm btn-outline-light rounded-pill px-3 btn-editar-cred" 
                                                        data-id="{{ $c->id_credencial }}"
                                                        data-usuario="{{ $c->usuario }}"
                                                        data-clave="{{ $c->clave }}"
                                                        data-link="{{ $c->link_acceso }}"
                                                        data-tipo="{{ $c->tipo }}"
                                                        data-datos="{{ $c->datos_link }}"
                                                        title="Editar Credencial">
                                                        <i class="bi bi-pencil-square me-1"></i> Editar
                                                    </button>
                                                    <button type="button" 
                                                        class="btn btn-sm btn-outline-success rounded-pill px-3 btn-compartir-wa" 
                                                        style="border-color: #25D366; color: #25D366;"
                                                        data-plataforma="{{ $c->nombre_plataforma ?: (config('cliente.nombre', 'CEFI') . ' VIRTUAL') }}"
                                                        data-usuario="{{ $c->usuario }}"
                                                        data-clave="{{ $c->clave }}"
                                                        data-link="{{ $c->link_acceso ?: '' }}"
                                                        data-tipo="{{ $c->tipo ?: 'Cuenta Nueva' }}"
                                                        data-attendee="{{ Auth::user()->nombre ?? 'Administrador' }}"
                                                        data-notas="{{ trim(preg_replace('/\s+/', ' ', (string)($c->datos_link ?? ''))) }}"
                                                        title="Compartir Acceso por WhatsApp">
                                                        <i class="bi bi-whatsapp me-1"></i> Compartir
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="confirmarEliminarCred({{ $c->id_credencial }})" title="Eliminar credencial">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-white-50">
                                            No hay credenciales registradas en el Centro de Soporte.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if ($credenciales->hasPages())
                        <div class="card-footer bg-transparent border-top border-secondary p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span class="text-white-50 small">
                                Mostrando {{ $credenciales->firstItem() }} a {{ $credenciales->lastItem() }} de {{ $credenciales->total() }} credenciales
                            </span>
                            <div>
                                {{ $credenciales->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- MODAL CREAR PLATAFORMA -->
    <div class="modal fade" id="crearPlataformaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-globe2 text-success me-2"></i> Registrar Plataforma</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('centro_soporte.plataforma.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4 row g-3">
                        <div class="col-md-12">
                            <label class="form-label-custom">Nombre de la Plataforma</label>
                            <input type="text" name="nombre" class="form-control form-control-custom w-100" placeholder="Ej: Portal Banner Académico" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label-custom">URL de Acceso Directo</label>
                            <input type="url" name="link_acceso" class="form-control form-control-custom w-100" placeholder="https://..." required>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR PLATAFORMA -->
    <div class="modal fade" id="editarPlataformaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-success me-2"></i> Editar Plataforma</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editPlatForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4 row g-3">
                        <div class="col-12">
                            <label class="form-label-custom">Nombre de la Plataforma</label>
                            <input type="text" name="nombre" id="editPlatNombre" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">URL de Acceso Directo</label>
                            <input type="url" name="link_acceso" id="editPlatLink" class="form-control form-control-custom w-100" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL CREAR CREDENCIAL -->
    <div class="modal fade" id="crearCredencialModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-key-fill text-success me-2"></i> Guardar Credencial</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('centro_soporte.credencial.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4 row g-3">
                        <div class="col-md-12">
                            <label class="form-label-custom">Plataforma Relacionada</label>
                            <select name="link_acceso" class="form-select form-select-custom w-100" required>
                                <option value="">Seleccione Plataforma...</option>
                                @foreach ($plataformas as $plat)
                                    <option value="{{ $plat->link_acceso }}">{{ $plat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Usuario</label>
                            <input type="text" name="usuario" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Clave / Contraseña</label>
                            <input type="text" name="clave" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Tipo de Credencial</label>
                            <input type="text" name="tipo" class="form-control form-control-custom w-100" placeholder="Ej: Docente, Admin, API...">
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Observaciones / Detalles</label>
                            <textarea name="datos_link" class="form-control form-control-custom w-100" rows="3" placeholder="Ej: Cuenta TI, notas, permisos o instrucciones adicionales..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR CREDENCIAL -->
    <div class="modal fade" id="editarCredencialModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-success me-2"></i> Editar Credencial</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editCredForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4 row g-3">
                        <div class="col-md-12">
                            <label class="form-label-custom">Plataforma Relacionada</label>
                            <select name="link_acceso" id="editCredLink" class="form-select form-select-custom w-100" required>
                                <option value="">Seleccione Plataforma...</option>
                                @foreach ($plataformas as $plat)
                                    <option value="{{ $plat->link_acceso }}">{{ $plat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Usuario</label>
                            <input type="text" name="usuario" id="editCredUsuario" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Clave / Contraseña</label>
                            <input type="text" name="clave" id="editCredClave" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Tipo de Credencial</label>
                            <input type="text" name="tipo" id="editCredTipo" class="form-control form-control-custom w-100" placeholder="Ej: Docente, Admin, API...">
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Observaciones / Detalles</label>
                            <textarea name="datos_link" id="editCredDatos" class="form-control form-control-custom w-100" rows="3" placeholder="Ej: Cuenta TI, notas, permisos o instrucciones adicionales..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL ELIMINAR CREDENCIAL CON CLAVE MAESTRA -->
    <div class="modal fade" id="deleteCredConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteCredForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="text-white-50 small mb-4">Introduzca la clave maestra del sistema para eliminar este registro permanentemente.</p>
                        <div>
                            <label class="form-label-custom">Clave Maestra</label>
                            <input type="password" name="clave" class="form-control form-control-custom w-100" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4">Eliminar</button>
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
        function togglePassVisibility(id) {
            const input = $('#pass-' + id);
            const eye = $('#eye-' + id);
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                eye.removeClass('bi-eye-fill').addClass('bi-eye-slash-fill');
            } else {
                input.attr('type', 'password');
                eye.removeClass('bi-eye-slash-fill').addClass('bi-eye-fill');
            }
        }

        function copiarAlPortapapeles(texto) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(texto);
            }
            return new Promise((resolve, reject) => {
                try {
                    const textArea = document.createElement("textarea");
                    textArea.value = texto;
                    textArea.style.position = "fixed";
                    textArea.style.left = "-999999px";
                    textArea.style.top = "-999999px";
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    const ok = document.execCommand('copy');
                    textArea.remove();
                    ok ? resolve() : reject(new Error('execCommand falló'));
                } catch (err) {
                    reject(err);
                }
            });
        }

        function copiarTexto(texto) {
            copiarAlPortapapeles(texto || '').then(() => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Copiado al portapapeles',
                    showConfirmButton: false,
                    timer: 2000
                });
            }).catch(() => {
                Swal.fire('Atención', 'No se pudo copiar automáticamente. Por favor selecciónelo manualmente.', 'info');
            });
        }

        function compartirWhatsApp(plataforma, usuario, clave, link, tipo_accion, attendee, notas) {
            let titulo = (plataforma || ("{{ config('cliente.nombre', 'CEFI') }} VIRTUAL")).toUpperCase();
            let ahora = new Date();
            let fechaHora = ahora.toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });

            let mensaje = `🏛️ *${titulo}*\n\nHola! Aquí están tus datos de acceso para *${tipo_accion || 'Cuenta Nueva'}*:\n\n` +
                          `────────────────\n` +
                          `👤 USUARIO: ${usuario || ''}\n` +
                          `🔑 CLAVE: *${clave || ''}*\n` +
                          `────────────────`;
            
            mensaje += (link && link.trim() !== '') ? `\n\n🔗 *Link de acceso:* ${link}` : `\n\n🔗 *Link de acceso:* {{ config('cliente.campus_virtual', 'https://virtual.cefi.cr') }}`;
            if (notas && notas.trim() !== '') mensaje += `\n\n📝 *Notas:* ${notas}`;
            
            mensaje += `\n\n_Atendido por: ${attendee || 'Soporte'} el ${fechaHora}_`;
            
            copiarAlPortapapeles(mensaje).then(() => {
                Swal.fire({
                    title: '¡Mensaje Copiado para WhatsApp!',
                    html: '<p class="mb-2">El formato profesional ya está en tu portapapeles.</p>' +
                          '<p class="text-muted small">Ve a la conversación de WhatsApp y presiona <b>Pegar (Ctrl+V)</b>.</p>',
                    icon: 'success',
                    confirmButtonText: '<i class="bi bi-whatsapp me-1"></i> Abrir WhatsApp Web',
                    showCancelButton: true,
                    cancelButtonText: 'Listo',
                    confirmButtonColor: '#25D366'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open('https://web.whatsapp.com', '_blank');
                    }
                });
            }).catch(() => {
                Swal.fire({
                    title: 'Mensaje para WhatsApp',
                    html: '<p class="small text-muted mb-2">Seleccione y copie el texto para enviarlo por WhatsApp:</p>' +
                          `<textarea class="form-control" rows="8" readonly id="swalTextoWa">${mensaje}</textarea>`,
                    confirmButtonText: 'Seleccionar Texto',
                    confirmButtonColor: '#25D366'
                }).then(() => {
                    const ta = document.getElementById('swalTextoWa');
                    if (ta) {
                        ta.focus();
                        ta.select();
                        document.execCommand('copy');
                    }
                });
            });
        }

        function editarPlataforma(id, nombre, link) {
            const actionUrl = "{{ route('centro_soporte.plataforma.update', ':id') }}".replace(':id', id);
            $('#editPlatForm').attr('action', actionUrl);
            $('#editPlatNombre').val(nombre);
            $('#editPlatLink').val(link);
            $('#editarPlataformaModal').modal('show');
        }

        function eliminarPlataforma(id, nombre) {
            Swal.fire({
                title: '¿Eliminar plataforma?',
                html: `¿Desea eliminar la plataforma <strong>${nombre}</strong>?<br><small class="text-white-50">Las credenciales guardadas no se borrarán.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                background: '#1e293b',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('centro_soporte.plataforma.delete', ':id') }}".replace(':id', id);
                    form.innerHTML = `@csrf @method('DELETE')`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function confirmarEliminarCred(id) {
            const actionUrl = "{{ route('centro_soporte.credencial.delete', ':id') }}".replace(':id', id);
            $('#deleteCredForm').attr('action', actionUrl);
            $('#deleteCredConfirmModal').modal('show');
        }

        $(document).on('click', '.btn-editar-cred', function() {
            const id = $(this).data('id');
            const actionUrl = "{{ route('centro_soporte.credencial.update', ':id') }}".replace(':id', id);
            $('#editCredForm').attr('action', actionUrl);
            $('#editCredUsuario').val($(this).data('usuario'));
            $('#editCredClave').val($(this).data('clave'));
            $('#editCredLink').val($(this).data('link'));
            $('#editCredTipo').val($(this).data('tipo'));
            $('#editCredDatos').val($(this).data('datos'));
            $('#editarCredencialModal').modal('show');
        });

        $(document).on('click', '.btn-compartir-wa', function() {
            compartirWhatsApp(
                $(this).data('plataforma'),
                $(this).data('usuario'),
                $(this).data('clave'),
                $(this).data('link'),
                $(this).data('tipo'),
                $(this).data('attendee'),
                $(this).data('notas')
            );
        });

        // Filtrado en vivo sobre las filas cargadas
        $('#buscarCredencialInput').on('keyup', function() {
            const val = $(this).val().toLowerCase();
            $('table tbody tr').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(val) > -1);
            });
        });
    </script>
@endsection
