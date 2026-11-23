@extends('layouts.app')

@section('title', 'Inbox BPM - Registro de Ingresos')

@section('styles')
    <style>
        .page-header {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 1.75rem 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            overflow: hidden;
        }

        .print-only-header {
            display: none;
        }

        @media print {
            body {
                background: #ffffff !important;
                color: #0f172a !important;
            }
            .no-print,
            nav.navbar,
            footer,
            .inbox-ai-fab,
            .inbox-ai-card,
            .breadcrumb {
                display: none !important;
            }
            .print-only-header {
                display: block !important;
                border-bottom: 2px solid #003b73;
                padding-bottom: 10px;
                margin-bottom: 15px;
            }
            .glass-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            .container-fluid {
                padding: 0 !important;
            }
            table {
                font-size: 11px !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4 px-md-5">

        <nav aria-label="breadcrumb" class="mb-3 no-print">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}" class="text-decoration-none">Reportes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Registro de Ingresos</li>
            </ol>
        </nav>

        <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 no-print">
            <div>
                <h1 class="h2 fw-bold mb-1">
                    <i class="bi bi-door-open-fill text-success me-2"></i> Registro de Ingresos al Sistema
                </h1>
                <p class="text-muted mb-0">Bitácora local en tiempo real de accesos a {{ config('app.url', 'inbox.ceficr.com') }} — lista para imprimir o exportar.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-bold">
                    <i class="bi bi-printer-fill me-1"></i> Imprimir / Guardar PDF
                </button>
                <a href="{{ route('reportes.ingresos_drive', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-success rounded-pill px-4 fw-bold">
                    <i class="bi bi-file-earmark-excel me-1"></i> Exportar Excel (CSV)
                </a>
                <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>

        <div class="glass-card mb-4 no-print">
            <form method="GET" action="{{ route('reportes.ingresos_drive') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Buscar usuario o correo</label>
                    <input type="text" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Nombre o email...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Desde</label>
                    <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Rol</label>
                    <select name="id_rol" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($rolesMap as $rId => $rNom)
                            <option value="{{ $rId }}" {{ (string)$idRol === (string)$rId ? 'selected' : '' }}>{{ $rNom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="bi bi-funnel-fill me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('reportes.ingresos_drive') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="glass-card">
            <div class="print-only-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-0">{{ config('cliente.nombre_legal', 'CEFI') }}</h4>
                        <div class="small text-muted">Reporte Oficial de Ingresos a {{ config('app.url', 'inbox.ceficr.com') }}</div>
                    </div>
                    <div class="text-end small">
                        <div><strong>Fecha de impresión:</strong> {{ now()->setTimezone('America/Costa_Rica')->format('d/m/Y H:i') }}</div>
                        <div><strong>Registros:</strong> {{ count($ingresos) }}</div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Usuario</th>
                            <th>Correo Electrónico</th>
                            <th>Rol</th>
                            <th>Fecha y Hora</th>
                            <th>IP</th>
                            <th>Origen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ingresos as $idx => $item)
                            <tr>
                                <td class="text-muted">{{ $idx + 1 }}</td>
                                <td class="fw-semibold">{{ $item->nombre }}</td>
                                <td>{{ $item->email }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2">
                                        {{ $rolesMap[(int)$item->id_rol] ?? ('Rol ' . $item->id_rol) }}
                                    </span>
                                </td>
                                <td class="font-monospace small">{{ \Carbon\Carbon::parse($item->fecha_ingreso)->format('d/m/Y H:i:s') }}</td>
                                <td class="font-monospace small text-muted">{{ $item->ip ?: '—' }}</td>
                                <td class="small text-muted">{{ $item->origen }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Aún no hay ingresos registrados con los filtros actuales. Los próximos inicios de sesión en <strong>inbox.ceficr.com</strong> aparecerán aquí automáticamente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
