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

        /* Action Icon Buttons */
        .btn-action-icon {
            width: 34px;
            height: 34px;
            border-radius: 0.6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            text-decoration: none;
            flex-shrink: 0;
            cursor: pointer;
        }

        .btn-action-icon:hover {
            transform: translateY(-2px);
        }

        .btn-action-icon.btn-action-expediente {
            background-color: rgba(95, 178, 48, 0.12);
            border-color: rgba(95, 178, 48, 0.3);
            color: #5fb230;
        }

        .btn-action-icon.btn-action-expediente:hover {
            background-color: #5fb230;
            color: white;
            box-shadow: 0 4px 12px rgba(95, 178, 48, 0.35);
        }

        .btn-action-icon.btn-action-edit {
            background-color: rgba(59, 130, 246, 0.12);
            border-color: rgba(59, 130, 246, 0.3);
            color: #3b82f6;
        }

        .btn-action-icon.btn-action-edit:hover {
            background-color: #3b82f6;
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
        }

        .btn-action-icon.btn-action-delete {
            background-color: rgba(239, 68, 68, 0.12);
            border-color: rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .btn-action-icon.btn-action-delete:hover {
            background-color: #ef4444;
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
        }

        /* Expediente Digital Column Badges & Elements */
        .badge-sin-expediente {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(248, 113, 113, 0.12);
            color: #f87171;
            border: 1px solid rgba(248, 113, 113, 0.3);
            border-radius: 2rem;
            padding: 0.35rem 0.75rem;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .btn-crear-expediente-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: rgba(95, 178, 48, 0.14);
            color: #5fb230;
            border: 1px solid rgba(95, 178, 48, 0.35);
            border-radius: 2rem;
            padding: 0.28rem 0.7rem;
            font-size: 0.74rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 0.35rem;
        }

        .btn-crear-expediente-pill:hover {
            background: #5fb230;
            color: white;
            box-shadow: 0 4px 12px rgba(95, 178, 48, 0.35);
            transform: translateY(-1px);
        }

        .progress-expediente {
            height: 7px;
            background-color: rgba(255, 255, 255, 0.08);
            border-radius: 6px;
            overflow: hidden;
        }

        .badge-exp-estado {
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.15rem 0.5rem;
            border-radius: 1rem;
            border: 1px solid;
            font-family: monospace;
        }

        .badge-exp-aprobado {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.3);
        }

        .badge-exp-pendiente {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.3);
        }

        .badge-exp-rechazado {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .exp-carrera-sub {
            font-size: 0.72rem;
            color: var(--text-muted);
            max-width: 145px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
        }

        .exp-link-icon {
            color: var(--primary);
            font-size: 0.8rem;
            transition: all 0.2s;
            padding: 0.1rem 0.25rem;
            text-decoration: none;
        }

        .exp-link-icon:hover {
            color: #ffffff;
            transform: scale(1.15);
        }

        [data-theme="light"] .badge-sin-expediente {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        [data-theme="light"] .btn-crear-expediente-pill {
            background: rgba(95, 178, 48, 0.1);
            color: #3d8b18;
            border-color: rgba(95, 178, 48, 0.4);
        }

        [data-theme="light"] .btn-crear-expediente-pill:hover {
            background: #5fb230;
            color: #ffffff;
        }

        [data-theme="light"] .progress-expediente {
            background-color: #e2e8f0;
        }

        [data-theme="light"] .btn-action-icon.btn-action-expediente {
            background-color: rgba(95, 178, 48, 0.1);
            border-color: rgba(95, 178, 48, 0.35);
            color: #3d8b18;
        }

        [data-theme="light"] .btn-action-icon.btn-action-edit {
            background-color: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.3);
            color: #2563eb;
        }

        [data-theme="light"] .btn-action-icon.btn-action-delete {
            background-color: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
            color: #dc2626;
        }

        [data-theme="light"] .modal-content.glass-card {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #0f172a !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
        }

        [data-theme="light"] #modalEditarUsuario .modal-header,
        [data-theme="light"] #modalEditarUsuario .modal-footer {
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
        }

        [data-theme="light"] #modalEditarUsuario .btn-close {
            filter: none !important;
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

        <!-- KPI Interactive Cards: Roles -->
        <div class="row g-3 mb-3">
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

        <!-- KPI Interactive Cards: Expedientes Digitales 360° -->
        <div class="row g-3 mb-4">
            <!-- Expedientes Completos -->
            <div class="col-sm-6 col-lg-4">
                <div class="kpi-card d-flex justify-content-between align-items-center border-start border-4 border-success" id="kpi-exp-completo" onclick="filtrarPorExpedienteKpi('completo')" title="Click para filtrar usuarios con expediente digital completado">
                    <div>
                        <div class="kpi-lbl text-success d-flex align-items-center gap-2">
                            <i class="bi bi-patch-check-fill fs-6"></i> Expedientes Completos
                        </div>
                        <div class="kpi-val text-success">{{ $kpis['expedientes_completos'] ?? 0 }}</div>
                        <small class="text-white-50" style="font-size: 0.72rem;">Requisitos completos y aprobados</small>
                    </div>
                    <div class="kpi-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <i class="bi bi-folder-check"></i>
                    </div>
                </div>
            </div>

            <!-- Expedientes En Progreso / Incompletos -->
            <div class="col-sm-6 col-lg-4">
                <div class="kpi-card d-flex justify-content-between align-items-center border-start border-4 border-warning" id="kpi-exp-progreso" onclick="filtrarPorExpedienteKpi('en_progreso')" title="Click para filtrar usuarios con expediente en proceso">
                    <div>
                        <div class="kpi-lbl text-warning d-flex align-items-center gap-2">
                            <i class="bi bi-pie-chart-fill fs-6"></i> En Progreso (Incompletos)
                        </div>
                        <div class="kpi-val text-warning">{{ $kpis['expedientes_en_progreso'] ?? 0 }}</div>
                        <small class="text-white-50" style="font-size: 0.72rem;">Con requisitos pendientes de completar</small>
                    </div>
                    <div class="kpi-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>

            <!-- Sin Expediente Digital -->
            <div class="col-sm-6 col-lg-4">
                <div class="kpi-card d-flex justify-content-between align-items-center border-start border-4 border-danger" id="kpi-exp-sin" onclick="filtrarPorExpedienteKpi('sin_expediente')" title="Click para filtrar usuarios que aún no tienen expediente">
                    <div>
                        <div class="kpi-lbl d-flex align-items-center gap-2" style="color: #f87171;">
                            <i class="bi bi-folder-x fs-6"></i> Sin Expediente Digital
                        </div>
                        <div class="kpi-val" style="color: #f87171;">{{ $kpis['sin_expediente'] ?? 0 }}</div>
                        <small class="text-white-50" style="font-size: 0.72rem;">Usuarios pendientes de crear su expediente</small>
                    </div>
                    <div class="kpi-icon-box" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">
                        <i class="bi bi-file-earmark-x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Form & Instant Search -->
        <div class="filter-section">
            <form action="{{ route('usuarios.index') }}" method="GET" id="filterForm" class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
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

                <div class="col-lg-3 col-md-6">
                    <label for="expedienteFilter" class="form-label text-white-50 fw-semibold mb-2">Expediente Digital</label>
                    <select name="expediente_status" id="expedienteFilter" class="form-select form-control-custom">
                        <option value="">Todos los Estados</option>
                        <option value="completo" {{ request('expediente_status') == 'completo' ? 'selected' : '' }}>✅ Expedientes Completos</option>
                        <option value="en_progreso" {{ request('expediente_status') == 'en_progreso' ? 'selected' : '' }}>⏳ En Progreso / Incompletos</option>
                        <option value="sin_expediente" {{ request('expediente_status') == 'sin_expediente' ? 'selected' : '' }}>📁 Sin Expediente Digital</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-4">
                    <label for="rolFilter" class="form-label text-white-50 fw-semibold mb-2">Filtrar por Rol</label>
                    <select name="rol_id" id="rolFilter" class="form-select form-control-custom">
                        <option value="">Todos los Roles</option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id }}" {{ request('rol_id') == $rol->id ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-1 col-md-4">
                    <label for="origenFilter" class="form-label text-white-50 fw-semibold mb-2">Origen</label>
                    <select name="origen" id="origenFilter" class="form-select form-control-custom">
                        <option value="">Todos</option>
                        <option value="inbox" {{ request('origen') == 'inbox' ? 'selected' : '' }}>Inbox</option>
                        <option value="moodle" {{ request('origen') == 'moodle' ? 'selected' : '' }}>Moodle</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-4 d-flex gap-2">
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
                            <th>Expediente Digital</th>
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

                                $hasExpediente = !is_null($usuario->expediente);
                                $exp = $usuario->expediente;
                                $porcExp = $hasExpediente ? $exp->porcentaje_completitud : 0;
                                $statusExp = $hasExpediente ? ($exp->estado === 'Aprobado' ? 'completo' : 'en_progreso') : 'sin_expediente';
                            @endphp
                            <tr class="user-row" 
                                data-id="{{ $usuario->id }}"
                                data-nombre="{{ $nombreCompleto }}"
                                data-apellidos="{{ $usuario->apellidos }}"
                                data-email="{{ $usuario->email }}"
                                data-cedula="{{ $usuario->cedula }}"
                                data-telefono="{{ $usuario->telefono }}"
                                data-rol-id="{{ $usuario->id_rol }}"
                                data-origen="{{ strtolower($usuario->origen) }}"
                                data-expediente-status="{{ $statusExp }}"
                                data-expediente-porc="{{ $porcExp }}">
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
                                    @if ($hasExpediente)
                                        @php
                                            $colorBar = $porcExp >= 80 ? 'bg-success' : ($porcExp >= 50 ? 'bg-warning' : 'bg-danger');
                                            $colorText = $porcExp >= 80 ? 'text-success' : ($porcExp >= 50 ? 'text-warning' : 'text-danger');
                                            $badgeEstadoClass = $exp->estado === 'Aprobado' ? 'badge-exp-aprobado' : ($exp->estado === 'Rechazado' ? 'badge-exp-rechazado' : 'badge-exp-pendiente');
                                            $tooltipText = $exp->campos_pendientes_texto;
                                        @endphp
                                        <div style="min-width: 175px;" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $tooltipText }}">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="fw-bold small {{ $colorText }} d-inline-flex align-items-center gap-1">
                                                    @if ($exp->estado === 'Aprobado')
                                                        <i class="bi bi-patch-check-fill"></i> 100% Completo
                                                    @else
                                                        <i class="bi bi-pie-chart-fill"></i> {{ $porcExp }}% Avance
                                                    @endif
                                                </span>
                                                <span class="badge-exp-estado {{ $badgeEstadoClass }}">
                                                    {{ $exp->estado ?: 'Pendiente' }}
                                                </span>
                                            </div>
                                            <div class="progress progress-expediente">
                                                <div class="progress-bar {{ $colorBar }}" role="progressbar" style="width: {{ $porcExp }}%;" aria-valuenow="{{ $porcExp }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <span class="exp-carrera-sub" title="{{ $exp->especialidad_deseada ?: 'Expediente Institucional' }}">
                                                    <i class="bi bi-mortarboard me-1 opacity-75"></i>{{ $exp->especialidad_deseada ?: 'Expediente Digital' }}
                                                </span>
                                                <a href="{{ route('expedientes.ver', $exp->id_expediente) }}" class="exp-link-icon" title="Abrir expediente digital 360°">
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <div style="min-width: 165px;">
                                            <div>
                                                <span class="badge-sin-expediente" title="Este usuario aún no tiene expediente digital creado">
                                                    <i class="bi bi-folder-x"></i> Sin Expediente
                                                </span>
                                            </div>
                                            <div>
                                                <a href="{{ route('expedientes.create', ['id_usuario' => $usuario->id]) }}" 
                                                   class="btn-crear-expediente-pill" 
                                                   title="Crear expediente digital para {{ $nombreCompleto }}">
                                                    <i class="bi bi-plus-circle-fill"></i> Crear Expediente
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($isMoodle)
                                        <span class="badge-origen-moodle" title="Sincronizado vía Moodle Bridge"><i class="bi bi-mortarboard-fill me-1"></i> Moodle</span>
                                    @else
                                        <span class="badge-origen-inbox" title="Registrado en Inbox BPM"><i class="bi bi-shield-check me-1"></i> Inbox</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end align-items-center gap-1">
                                        @if ($hasExpediente)
                                            <a href="{{ route('expedientes.ver', $exp->id_expediente) }}" 
                                                class="btn-action-icon btn-action-expediente" 
                                                title="Ver Expediente Digital 360° ({{ $porcExp }}%)">
                                                <i class="bi bi-folder2-open"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('usuarios.edit', $usuario->id) }}" 
                                            class="btn-action-icon btn-action-edit btn-editar-usuario" 
                                            data-id="{{ $usuario->id }}"
                                            data-nombre="{{ $usuario->nombre }}"
                                            data-apellidos="{{ $usuario->apellidos }}"
                                            data-cedula="{{ $usuario->cedula }}"
                                            data-email="{{ $usuario->email }}"
                                            data-telefono="{{ $usuario->telefono }}"
                                            data-rol-id="{{ $usuario->id_rol }}"
                                            data-origen="{{ strtolower($usuario->origen) }}"
                                            title="Editar datos de {{ $nombreCompleto }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @if (!$isSelf && !$isMoodle)
                                            <button type="button" class="btn-action-icon btn-action-delete btn-eliminar-usuario" 
                                                data-id="{{ $usuario->id }}" 
                                                data-nombre="{{ $nombreCompleto }}"
                                                title="Eliminar usuario">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        @elseif ($isMoodle)
                                            <span class="badge bg-secondary bg-opacity-10 text-white-50 py-2 px-2" title="Usuario sincronizado vía Moodle">
                                                <i class="bi bi-mortarboard-fill"></i>
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-white-50 py-2 px-2" title="Tu propia cuenta en sesión">
                                                <i class="bi bi-person-check"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyServerRow">
                                <td colspan="7" class="text-center py-5 text-white-50">
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

        <!-- Modal de Edición Rápida de Usuario -->
        <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content glass-card shadow-lg border-0">
                    <div class="modal-header border-bottom px-4 py-3" style="border-color: var(--border-dark) !important;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill px-3 py-1 font-monospace" id="modal-user-badge">ID #0000</span>
                            <h5 class="modal-title fw-bold text-white mb-0" id="modalEditarUsuarioLabel">
                                <i class="bi bi-person-gear text-primary me-1"></i> Editar Usuario
                            </h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form id="form-editar-usuario" onsubmit="guardarEdicionUsuario(event)">
                        @csrf
                        <input type="hidden" id="edit-user-id" name="id">
                        <div class="modal-body px-4 py-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit-user-nombre" class="form-label form-label-custom small fw-bold">Nombres</label>
                                    <input type="text" class="form-control form-control-custom" id="edit-user-nombre" name="nombre" required placeholder="Ej: Juan">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit-user-apellidos" class="form-label form-label-custom small fw-bold">Apellidos</label>
                                    <input type="text" class="form-control form-control-custom" id="edit-user-apellidos" name="apellidos" required placeholder="Ej: Pérez García">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit-user-cedula" class="form-label form-label-custom small fw-bold">Cédula / Identificación</label>
                                    <input type="text" class="form-control form-control-custom" id="edit-user-cedula" name="cedula" placeholder="Formato nacional o pasaporte">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit-user-email" class="form-label form-label-custom small fw-bold">Correo Electrónico</label>
                                    <input type="email" class="form-control form-control-custom" id="edit-user-email" name="email" required placeholder="nombre@ejemplo.com">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit-user-telefono" class="form-label form-label-custom small fw-bold">Teléfono / WhatsApp</label>
                                    <input type="text" class="form-control form-control-custom" id="edit-user-telefono" name="telefono" placeholder="Ej: 50688889999">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit-user-rol" class="form-label form-label-custom small fw-bold">Rol Institucional</label>
                                    <select class="form-select form-control-custom" id="edit-user-rol" name="id_rol" required>
                                        @foreach ($roles as $rol)
                                            <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label for="edit-user-password" class="form-label form-label-custom small fw-bold">Nueva Contraseña (Opcional)</label>
                                <input type="password" class="form-control form-control-custom" id="edit-user-password" name="password" placeholder="Dejar en blanco para conservar la actual">
                                <small class="text-white-50 d-block mt-1">Escriba una contraseña solo si desea cambiarla (mínimo 6 caracteres).</small>
                            </div>
                        </div>
                        <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between" style="border-color: var(--border-dark) !important;">
                            <div>
                                <a href="#" id="modal-link-full-edit" class="small text-decoration-none text-primary fw-semibold">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir formulario completo
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btn-guardar-usuario">
                                    <i class="bi bi-check-lg me-1"></i> Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('liveSearch');
            const rolSelect = document.getElementById('rolFilter');
            const origenSelect = document.getElementById('origenFilter');
            const expSelect = document.getElementById('expedienteFilter');
            const btnReset = document.getElementById('btnResetFilter');
            const userRows = document.querySelectorAll('.user-row');
            const visibleCountEl = document.getElementById('visibleCount');
            const noLiveResults = document.getElementById('noLiveResults');

            // Inicializar tooltips de Bootstrap para ver requisitos pendientes
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

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
                const expVal = expSelect ? expSelect.value : '';

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
                    const rowExpStatus = row.dataset.expedienteStatus || 'sin_expediente';

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

                    // Coincidencia de estado de expediente digital
                    let matchExp = true;
                    if (expVal) {
                        if (expVal === 'completo') {
                            matchExp = (rowExpStatus === 'completo');
                        } else if (expVal === 'en_progreso') {
                            matchExp = (rowExpStatus === 'en_progreso');
                        } else if (expVal === 'sin_expediente') {
                            matchExp = (rowExpStatus === 'sin_expediente');
                        }
                    }

                    if (matchSearch && matchRol && matchOrigen && matchExp) {
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
            expSelect?.addEventListener('change', aplicarFiltrosEnVivo);

            btnReset?.addEventListener('click', function() {
                searchInput.value = '';
                rolSelect.value = '';
                origenSelect.value = '';
                if (expSelect) expSelect.value = '';
                document.querySelectorAll('.kpi-card').forEach(c => c.classList.remove('active-kpi'));
                aplicarFiltrosEnVivo();
                if (window.location.search) {
                    window.location.href = "{{ route('usuarios.index') }}";
                }
            });

            // Función global para hacer clic en las tarjetas KPI de roles
            window.filtrarPorKpi = function(rolId) {
                document.querySelectorAll('.kpi-card').forEach(c => c.classList.remove('active-kpi'));
                if (rolId === '1') document.getElementById('kpi-admins')?.classList.add('active-kpi');
                else if (rolId === '2') document.getElementById('kpi-docentes')?.classList.add('active-kpi');
                else if (rolId === '3') document.getElementById('kpi-estudiantes')?.classList.add('active-kpi');
                else document.getElementById('kpi-total')?.classList.add('active-kpi');

                if (expSelect) expSelect.value = '';
                rolSelect.value = rolId;
                aplicarFiltrosEnVivo();
            };

            // Función global para hacer clic en las tarjetas KPI de expedientes digitales
            window.filtrarPorExpedienteKpi = function(status) {
                document.querySelectorAll('.kpi-card').forEach(c => c.classList.remove('active-kpi'));
                if (status === 'completo') document.getElementById('kpi-exp-completo')?.classList.add('active-kpi');
                else if (status === 'en_progreso') document.getElementById('kpi-exp-progreso')?.classList.add('active-kpi');
                else if (status === 'sin_expediente') document.getElementById('kpi-exp-sin')?.classList.add('active-kpi');

                rolSelect.value = '';
                if (expSelect) {
                    expSelect.value = status;
                    aplicarFiltrosEnVivo();
                }
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
            // --- MODAL DE EDICIÓN DE USUARIO ---
            const modalUserEl = document.getElementById('modalEditarUsuario');
            const modalUser = modalUserEl ? new bootstrap.Modal(modalUserEl) : null;

            document.querySelectorAll('.btn-editar-usuario').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (e.ctrlKey || e.metaKey || e.button === 1) return;

                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    const nombre = this.getAttribute('data-nombre') || '';
                    const apellidos = this.getAttribute('data-apellidos') || '';
                    const cedula = this.getAttribute('data-cedula') || '';
                    const email = this.getAttribute('data-email') || '';
                    const telefono = this.getAttribute('data-telefono') || '';
                    const rolId = this.getAttribute('data-rol-id') || '';

                    document.getElementById('edit-user-id').value = id;
                    document.getElementById('edit-user-nombre').value = nombre;
                    document.getElementById('edit-user-apellidos').value = apellidos;
                    document.getElementById('edit-user-cedula').value = cedula;
                    document.getElementById('edit-user-email').value = email;
                    document.getElementById('edit-user-telefono').value = telefono;
                    document.getElementById('edit-user-rol').value = rolId;
                    document.getElementById('edit-user-password').value = '';
                    document.getElementById('modal-user-badge').textContent = 'ID #' + String(id).padStart(4, '0');
                    document.getElementById('modal-link-full-edit').href = `/usuarios/${id}/editar`;

                    if (modalUser) modalUser.show();
                });
            });

            window.guardarEdicionUsuario = async function(e) {
                e.preventDefault();
                const form = document.getElementById('form-editar-usuario');
                const id = document.getElementById('edit-user-id').value;
                const btn = document.getElementById('btn-guardar-usuario');
                const originalHtml = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

                const formData = new FormData(form);

                try {
                    const res = await fetch(`/usuarios/${id}/actualizar`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await res.json();
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;

                    if (res.ok && data.success) {
                        if (modalUser) modalUser.hide();

                        // Actualizar la fila en vivo
                        const u = data.usuario;
                        const row = document.querySelector(`.user-row[data-id="${id}"]`);
                        if (row) {
                            row.setAttribute('data-nombre', u.nombre + ' ' + (u.apellidos || ''));
                            row.setAttribute('data-apellidos', u.apellidos || '');
                            row.setAttribute('data-email', u.email);
                            row.setAttribute('data-cedula', u.cedula || '');
                            row.setAttribute('data-telefono', u.telefono || '');
                            row.setAttribute('data-rol-id', u.id_rol);

                            // Actualizar nombre
                            const nameEl = row.querySelector('.user-name-primary');
                            if (nameEl) {
                                nameEl.innerHTML = (u.apellidos ? `<strong class="text-white">${u.apellidos}</strong>, ${u.nombre}` : `<strong class="text-white">${u.nombre}</strong>`);
                            }
                            // Actualizar email y teléfono
                            const emailCell = row.cells[1];
                            if (emailCell) {
                                emailCell.innerHTML = `<div class="text-white">${u.email}</div>` + (u.telefono ? `<small class="text-white-50"><i class="bi bi-telephone me-1"></i>${u.telefono}</small>` : '');
                            }
                            // Actualizar cédula
                            const cedulaCell = row.cells[2];
                            if (cedulaCell) {
                                cedulaCell.innerHTML = u.cedula ? `<span class="badge bg-dark border border-secondary border-opacity-25 text-light font-monospace">${u.cedula}</span>` : `<span class="text-white-50 small">No registrada</span>`;
                            }
                            // Actualizar rol badge
                            const rolCell = row.cells[3];
                            if (rolCell && u.rol) {
                                let rClass = 'badge-role';
                                if (u.id_rol == 1) rClass += ' role-admin';
                                else if (u.id_rol == 2) rClass += ' role-teacher';
                                else rClass += ' role-student';
                                rolCell.innerHTML = `<span class="${rClass}">${u.rol.nombre}</span>`;
                            }

                            // Actualizar datos del botón de editar
                            const editBtn = row.querySelector('.btn-editar-usuario');
                            if (editBtn) {
                                editBtn.setAttribute('data-nombre', u.nombre);
                                editBtn.setAttribute('data-apellidos', u.apellidos || '');
                                editBtn.setAttribute('data-cedula', u.cedula || '');
                                editBtn.setAttribute('data-email', u.email);
                                editBtn.setAttribute('data-telefono', u.telefono || '');
                                editBtn.setAttribute('data-rol-id', u.id_rol);
                            }
                        }

                        Swal.fire({
                            icon: 'success',
                            title: '¡Usuario Actualizado!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    } else {
                        let errorMsg = data.message || 'Error al actualizar el usuario.';
                        if (data.errors) {
                            const errList = Object.values(data.errors).flat().join('<br>');
                            errorMsg = errList;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo guardar',
                            html: errorMsg
                        });
                    }
                } catch (err) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Red',
                        text: err.message
                    });
                }
            };
        });
    </script>
@endsection
