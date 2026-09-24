@extends('layouts.app')

@section('title', 'Inbox BPM - Gestión de Usuarios')

@section('styles')
    <style>
        .page-header {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2.2rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        /* KPI Cards */
        .kpi-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.25rem;
            padding: 1.25rem 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.25);
            border-color: var(--primary);
        }

        .kpi-card.active-kpi {
            border-color: var(--primary) !important;
            background: rgba(95, 178, 48, 0.08) !important;
            box-shadow: 0 0 0 2px var(--primary);
        }

        .kpi-val {
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .kpi-lbl {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 0.35rem;
        }

        .kpi-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }

        .filter-section {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.25rem;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
        }

        .table-container {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .table-custom {
            margin-bottom: 0;
            background-color: transparent !important;
        }

        .table-custom thead th {
            background-color: rgba(255, 255, 255, 0.02) !important;
            color: var(--text-muted) !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 1.1rem 1.5rem;
            border-bottom: 2px solid var(--border-dark);
        }

        .table-custom tbody tr {
            border-bottom: 1px solid var(--border-dark);
            transition: all 0.2s;
        }

        .table-custom tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }

        .table-custom td {
            background-color: transparent !important;
            padding: 1.1rem 1.5rem;
            vertical-align: middle;
            color: #e2e8f0 !important;
        }

        .user-name-primary {
            color: #f8fafc;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .badge-role {
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .role-admin {
            background-color: rgba(239, 68, 68, 0.15) !important;
            color: #ef4444 !important;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .role-teacher {
            background-color: rgba(59, 130, 246, 0.15) !important;
            color: #3b82f6 !important;
            border-color: rgba(59, 130, 246, 0.3);
        }

        .role-student {
            background-color: rgba(16, 185, 129, 0.15) !important;
            color: #10b981 !important;
            border-color: rgba(16, 185, 129, 0.3);
        }

        .badge-origen-inbox {
            background-color: rgba(95, 178, 48, 0.12);
            color: #5fb230;
            border: 1px solid rgba(95, 178, 48, 0.25);
            border-radius: 2rem;
            padding: 0.25rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-origen-moodle {
            background-color: rgba(249, 115, 22, 0.12);
            color: #f97316;
            border: 1px solid rgba(249, 115, 22, 0.25);
            border-radius: 2rem;
            padding: 0.25rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .form-control-custom {
            background-color: rgba(15, 23, 42, 0.5);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.6rem 1rem;
            transition: all 0.3s;
        }

        .form-control-custom:focus {
            background-color: rgba(15, 23, 42, 0.7);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15);
            color: #f8fafc;
            outline: none;
        }

        .btn-search {
            background-color: var(--primary);
            border: none;
            color: white;
            padding: 0.6rem 1.4rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-search:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(95, 178, 48, 0.25);
            color: white;
        }

        .btn-clear {
            background-color: transparent;
            border: 2px solid var(--border-dark);
            color: var(--text-muted);
            padding: 0.6rem 1.2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-clear:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #f8fafc;
            border-color: #f8fafc;
        }

        .btn-action-delete {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #ef4444;
            border-radius: 0.5rem;
            padding: 0.35rem 0.65rem;
            transition: all 0.2s;
        }

        .btn-action-delete:hover {
            background-color: #ef4444;
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            transform: translateY(-1px);
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50 small fw-semibold"><i class="bi bi-house-door me-1"></i> Inicio</a></li>
                        <li class="breadcrumb-item active text-primary small fw-bold" aria-current="page">Usuarios</li>
                    </ol>
                </nav>
                <h1 class="display-6 fw-bold text-white mb-1">
                    <i class="bi bi-people-fill text-primary me-2"></i>Gestión de Usuarios
                </h1>
                <p class="text-white-50 mb-0">Directorio institucional ordenado por apellidos con búsqueda universal en vivo y borrado seguro.</p>
            </div>
            <div>
                <a href="{{ route('usuarios.create') }}" class="btn btn-search">
                    <i class="bi bi-person-plus-fill me-1"></i> Registrar Nuevo Usuario
                </a>
            </div>
        </div>

        <!-- KPI Interactive Cards -->
        <div class="row g-3 mb-4">
            <!-- Total Usuarios -->
            <div class="col-sm-6 col-lg-3">
                <div class="kpi-card d-flex justify-content-between align-items-center" id="kpi-total" onclick="filtrarPorKpi('')" title="Ver todos los usuarios">
                    <div>
                        <div class="kpi-lbl">Total Usuarios</div>
                        <div class="kpi-val text-white">{{ $kpis['total'] ?? 0 }}</div>
                    </div>
                    <div class="kpi-icon-box" style="background: rgba(255, 255, 255, 0.08); color: #f8fafc;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>

            <!-- Docentes -->
            <div class="col-sm-6 col-lg-3">
                <div class="kpi-card d-flex justify-content-between align-items-center" id="kpi-docentes" onclick="filtrarPorKpi('2')" title="Filtrar Docentes">
                    <div>
                        <div class="kpi-lbl text-primary">Docentes / Profesores</div>
                        <div class="kpi-val text-primary">{{ $kpis['docentes'] ?? 0 }}</div>
                    </div>
                    <div class="kpi-icon-box" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                </div>
            </div>

            <!-- Administradores -->
            <div class="col-sm-6 col-lg-3">
                <div class="kpi-card d-flex justify-content-between align-items-center" id="kpi-admins" onclick="filtrarPorKpi('1')" title="Filtrar Administradores">
                    <div>
                        <div class="kpi-lbl text-danger">Administradores</div>
                        <div class="kpi-val text-danger">{{ $kpis['admins'] ?? 0 }}</div>
                    </div>
                    <div class="kpi-icon-box" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                </div>
            </div>

            <!-- Estudiantes -->
            <div class="col-sm-6 col-lg-3">
                <div class="kpi-card d-flex justify-content-between align-items-center" id="kpi-estudiantes" onclick="filtrarPorKpi('3')" title="Filtrar Estudiantes">
                    <div>
                        <div class="kpi-lbl text-success">Estudiantes / Miembros</div>
                        <div class="kpi-val text-success">{{ $kpis['estudiantes'] ?? 0 }}</div>
                    </div>
                    <div class="kpi-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <i class="bi bi-person-badge"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Form & Instant Search -->
        <div class="filter-section">
            <form action="{{ route('usuarios.index') }}" method="GET" id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="liveSearch" class="form-label text-white-50 fw-semibold mb-2 d-flex justify-content-between">
                        <span>Búsqueda Universal en Tiempo Real</span>
                        <span class="badge bg-secondary bg-opacity-25 text-white-50 font-monospace">Atajo: /</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 border-2" style="border-color: var(--border-dark); border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem; color: var(--text-muted);">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" id="liveSearch" class="form-control form-control-custom border-start-0 ps-0" placeholder="Apellidos, nombre, cédula, email, teléfono, #ID..." value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="rolFilter" class="form-label text-white-50 fw-semibold mb-2">Filtrar por Rol</label>
                    <select name="rol_id" id="rolFilter" class="form-select form-control-custom">
                        <option value="">Todos los Roles</option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id }}" {{ request('rol_id') == $rol->id ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="origenFilter" class="form-label text-white-50 fw-semibold mb-2">Origen</label>
                    <select name="origen" id="origenFilter" class="form-select form-control-custom">
                        <option value="">Todos</option>
                        <option value="inbox" {{ request('origen') == 'inbox' ? 'selected' : '' }}>Inbox BPM</option>
                        <option value="moodle" {{ request('origen') == 'moodle' ? 'selected' : '' }}>Moodle</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-search flex-grow-1" title="Búsqueda profunda en servidor">
                        <i class="bi bi-funnel"></i>
                    </button>
                    <button type="button" class="btn btn-clear flex-grow-1" id="btnResetFilter" title="Restablecer filtros">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-secondary border-opacity-10 small text-white-50">
                <span id="counterStatus">Mostrando <strong class="text-white" id="visibleCount">{{ $usuarios->count() }}</strong> usuarios en esta vista</span>
                <span>Orden: <strong>Apellidos (A-Z), Nombres</strong></span>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-custom align-middle" id="tablaUsuarios">
                    <thead>
                        <tr>
                            <th>Usuario (Apellidos, Nombre)</th>
                            <th>Contacto</th>
                            <th>Identificación</th>
                            <th>Rol</th>
                            <th>Origen</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usuarios as $usuario)
                            @php
                                $nombreCompleto = trim($usuario->nombre . ' ' . $usuario->apellidos);
                                $apellidosFirst = trim($usuario->apellidos . ', ' . $usuario->nombre);
                                $initial = strtoupper(substr($usuario->apellidos ?: $usuario->nombre, 0, 1));
                                $isSelf = auth()->check() && auth()->id() === $usuario->id;
                                $isMoodle = strtolower((string)$usuario->origen) === 'moodle' || !empty($usuario->id_moodle);
                            @endphp
                            <tr class="user-row" 
                                data-id="{{ $usuario->id }}"
                                data-nombre="{{ $nombreCompleto }}"
                                data-apellidos="{{ $usuario->apellidos }}"
                                data-email="{{ $usuario->email }}"
                                data-cedula="{{ $usuario->cedula }}"
                                data-telefono="{{ $usuario->telefono }}"
                                data-rol-id="{{ $usuario->id_rol }}"
                                data-origen="{{ strtolower($usuario->origen) }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 42px; height: 42px; background: rgba(255, 255, 255, 0.06); border: 1.5px solid var(--border-dark);">
                                            <span class="text-white fw-bold">{{ $initial }}</span>
                                        </div>
                                        <div>
                                            <div class="user-name-primary">
                                                @if (!empty($usuario->apellidos))
                                                    <strong class="text-white">{{ $usuario->apellidos }}</strong>, {{ $usuario->nombre }}
                                                @else
                                                    <strong class="text-white">{{ $usuario->nombre }}</strong>
                                                @endif
                                                @if ($isSelf)
                                                    <span class="badge bg-primary bg-opacity-25 text-primary ms-1" style="font-size: 0.65rem;">Tú</span>
                                                @endif
                                            </div>
                                            <small class="text-white-50 font-monospace">#ID {{ str_pad($usuario->id, 4, '0', STR_PAD_LEFT) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-white">{{ $usuario->email }}</div>
                                    @if (!empty($usuario->telefono))
                                        <small class="text-white-50"><i class="bi bi-telephone me-1"></i>{{ $usuario->telefono }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if (!empty($usuario->cedula))
                                        <span class="badge bg-dark border border-secondary border-opacity-25 text-light font-monospace">{{ $usuario->cedula }}</span>
                                    @else
                                        <span class="text-white-50 small">No registrada</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $roleClass = 'badge-role';
                                        if ($usuario->id_rol == 1) {
                                            $roleClass .= ' role-admin';
                                        } elseif ($usuario->id_rol == 2) {
                                            $roleClass .= ' role-teacher';
                                        } else {
                                            $roleClass .= ' role-student';
                                        }
                                    @endphp
                                    <span class="{{ $roleClass }}">{{ $usuario->rol->nombre ?? 'Sin Rol' }}</span>
                                </td>
                                <td>
                                    @if ($isMoodle)
                                        <span class="badge-origen-moodle" title="Sincronizado vía Moodle Bridge"><i class="bi bi-mortarboard-fill me-1"></i> Moodle</span>
                                    @else
                                        <span class="badge-origen-inbox" title="Registrado en Inbox BPM"><i class="bi bi-shield-check me-1"></i> Inbox</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        @if (!$isSelf && !$isMoodle)
                                            <button type="button" class="btn btn-sm btn-action-delete btn-eliminar-usuario" 
                                                data-id="{{ $usuario->id }}" 
                                                data-nombre="{{ $nombreCompleto }}"
                                                title="Eliminar usuario con clave de confirmación">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @elseif ($isMoodle)
                                            <span class="badge bg-secondary bg-opacity-10 text-white-50 py-2 px-2" title="Los usuarios de Moodle se gestionan desde el aula virtual">
                                                <i class="bi bi-lock-fill"></i> Moodle
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-white-50 py-2 px-2" title="Tu propia cuenta en sesión">
                                                <i class="bi bi-person-check"></i> Activo
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyServerRow">
                                <td colspan="6" class="text-center py-5 text-white-50">
                                    <i class="bi bi-people display-4 d-block mb-3 text-muted"></i>
                                    No se encontraron usuarios en la base de datos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Mensaje cuando la búsqueda en vivo oculta todo -->
            <div id="noLiveResults" class="p-5 text-center text-white-50 d-none">
                <i class="bi bi-search display-5 text-muted d-block mb-3"></i>
                <h5 class="text-white mb-1">Sin resultados coincidentes</h5>
                <p class="mb-3">Ningún usuario coincide con los filtros aplicados en esta vista.</p>
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="document.getElementById('btnResetFilter').click()">
                    Restablecer Búsqueda
                </button>
            </div>

            <!-- Pagination Footer -->
            @if ($usuarios->hasPages())
                <div class="d-flex justify-content-between align-items-center p-4 border-top" style="border-color: var(--border-dark) !important;">
                    <div class="text-white-50" style="font-size: 0.9rem;">
                        Página <strong>{{ $usuarios->currentPage() }}</strong> de <strong>{{ $usuarios->lastPage() }}</strong> (Total: {{ $usuarios->total() }} registros)
                    </div>
                    <div>
                        {{ $usuarios->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('liveSearch');
            const rolSelect = document.getElementById('rolFilter');
            const origenSelect = document.getElementById('origenFilter');
            const btnReset = document.getElementById('btnResetFilter');
            const userRows = document.querySelectorAll('.user-row');
            const visibleCountEl = document.getElementById('visibleCount');
            const noLiveResults = document.getElementById('noLiveResults');

            // Normalizador de acentos y diacríticos
            function normalizar(texto) {
                return (texto || '')
                    .toString()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim();
            }

            // Atajo de teclado: presionar '/' para enfocar el buscador
            window.addEventListener('keydown', function(e) {
                if (e.key === '/' && document.activeElement !== searchInput && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                } else if (e.key === 'Escape' && document.activeElement === searchInput) {
                    searchInput.value = '';
                    aplicarFiltrosEnVivo();
                }
            });

            // Función de filtrado en vivo instantáneo
            function aplicarFiltrosEnVivo() {
                const queryRaw = searchInput.value;
                const terms = normalizar(queryRaw).split(/\s+/).filter(t => t.length > 0);
                const rolVal = rolSelect.value;
                const origenVal = origenSelect.value.toLowerCase();

                let visibles = 0;

                userRows.forEach(row => {
                    const rowId = row.dataset.id || '';
                    const rowNombre = normalizar(row.dataset.nombre);
                    const rowApellidos = normalizar(row.dataset.apellidos);
                    const rowEmail = normalizar(row.dataset.email);
                    const rowCedula = normalizar(row.dataset.cedula);
                    const rowTelefono = normalizar(row.dataset.telefono);
                    const rowRolId = row.dataset.rolId;
                    const rowOrigen = (row.dataset.origen || '').toLowerCase();

                    const searchBlob = `${rowNombre} ${rowApellidos} ${rowEmail} ${rowCedula} ${rowTelefono} #${rowId}`;

                    // Coincidencia con todos los términos de búsqueda
                    let matchSearch = true;
                    for (const term of terms) {
                        if (!searchBlob.includes(term)) {
                            matchSearch = false;
                            break;
                        }
                    }

                    // Coincidencia de rol
                    const matchRol = !rolVal || rowRolId === rolVal;

                    // Coincidencia de origen
                    const matchOrigen = !origenVal || rowOrigen === origenVal;

                    if (matchSearch && matchRol && matchOrigen) {
                        row.style.display = '';
                        visibles++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (visibleCountEl) visibleCountEl.textContent = visibles;

                if (noLiveResults) {
                    if (visibles === 0 && userRows.length > 0) {
                        noLiveResults.classList.remove('d-none');
                    } else {
                        noLiveResults.classList.add('d-none');
                    }
                }
            }

            searchInput?.addEventListener('input', aplicarFiltrosEnVivo);
            rolSelect?.addEventListener('change', aplicarFiltrosEnVivo);
            origenSelect?.addEventListener('change', aplicarFiltrosEnVivo);

            btnReset?.addEventListener('click', function() {
                searchInput.value = '';
                rolSelect.value = '';
                origenSelect.value = '';
                document.querySelectorAll('.kpi-card').forEach(c => c.classList.remove('active-kpi'));
                aplicarFiltrosEnVivo();
                if (window.location.search) {
                    window.location.href = "{{ route('usuarios.index') }}";
                }
            });

            // Función global para hacer clic en las tarjetas KPI superiores
            window.filtrarPorKpi = function(rolId) {
                document.querySelectorAll('.kpi-card').forEach(c => c.classList.remove('active-kpi'));
                if (rolId === '1') document.getElementById('kpi-admins')?.classList.add('active-kpi');
                else if (rolId === '2') document.getElementById('kpi-docentes')?.classList.add('active-kpi');
                else if (rolId === '3') document.getElementById('kpi-estudiantes')?.classList.add('active-kpi');
                else document.getElementById('kpi-total')?.classList.add('active-kpi');

                rolSelect.value = rolId;
                aplicarFiltrosEnVivo();
            };

            // Eliminación segura de usuario con clave de autorización
            document.querySelectorAll('.btn-eliminar-usuario').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const nombre = this.dataset.nombre;

                    Swal.fire({
                        title: `¿Eliminar a ${nombre}?`,
                        html: `
                            <p class="text-white-50 small mb-3">Esta acción es irreversible y eliminará el acceso del usuario. Para autorizar, ingrese la <strong>clave interna</strong>:</p>
                            <input type="password" id="swal_admin_pwd" class="form-control form-control-custom text-center mb-2" placeholder="Ingrese clave de autorización" autocomplete="new-password">
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Eliminar Definitivamente',
                        cancelButtonText: 'Cancelar',
                        preConfirm: () => {
                            const pwd = document.getElementById('swal_admin_pwd').value;
                            if (!pwd) {
                                Swal.showValidationMessage('Debe ingresar la clave para confirmar');
                            }
                            return pwd;
                        }
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            const adminPassword = result.value;

                            Swal.fire({
                                title: 'Procesando eliminación...',
                                text: 'Verificando autorización y dependencias',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });

                            try {
                                const res = await fetch(`/usuarios/${id}/eliminar`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        admin_password: adminPassword
                                    })
                                });

                                const data = await res.json();
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Usuario Eliminado!',
                                        text: data.message,
                                        confirmButtonColor: '#5fb230'
                                    }).then(() => {
                                        const row = document.querySelector(`.user-row[data-id="${id}"]`);
                                        if (row) {
                                            row.remove();
                                            aplicarFiltrosEnVivo();
                                        } else {
                                            window.location.reload();
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'No se pudo eliminar',
                                        text: data.message || 'Error al procesar la solicitud.'
                                    });
                                }
                            } catch (err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error de Red',
                                    text: err.message
                                });
                            }
                        }
                    });
                });
            });
        });
    </script>
@endsection
