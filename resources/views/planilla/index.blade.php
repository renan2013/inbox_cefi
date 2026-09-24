@extends('layouts.app')

@section('title', 'Inbox BPM - Panel de Planilla y Asistencia')

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

        .stat-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1.5rem;
        }

        .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-header-custom {
            background-color: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid var(--border-dark);
            padding: 1.25rem 1.5rem;
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
            padding: 1rem 1.25rem;
        }

        /* Modal styling */
        .modal-content-custom {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Panel de Planilla</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Panel de Planilla y Asistencia</h1>
                <p class="text-white-50 mb-0">Controles administrativos para el cálculo de salarios y registros de asistencia.</p>
            </div>
            <div>
                <a href="{{ route('asistencia.marcar') }}" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">
                    <i class="bi bi-clock-fill me-2"></i> Ir a Marcador de Asistencia
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- TARJETAS ESTADISTICAS -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#activeEmployeesModal">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-person-fill-check"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-white">{{ $total_activos }}</h3>
                        <span class="text-white-50 small text-uppercase fw-semibold">Trabajando hoy</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-white">{{ $total_marcas_hoy }}</h3>
                        <span class="text-white-50 small text-uppercase fw-semibold">Total Marcas Hoy</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-white">{{ $total_empleados_config }}</h3>
                        <span class="text-white-50 small text-uppercase fw-semibold">Configurados Planilla</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- TARIFAS DE CATEGORIAS -->
            <div class="col-lg-6">
                <div class="card glass-card">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-white mb-0"><i class="bi bi-wallet2 text-success me-2"></i> Tarifas de Categorías</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr class="text-white-50">
                                    <th>Categoría / Rol</th>
                                    <th>Modalidad Pago</th>
                                    <th>Tarifa</th>
                                    <th class="text-end">Configurar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categorias_tarifas as $cat)
                                    <tr>
                                        <td class="fw-bold text-white">{{ $cat->rol_nombre }}</td>
                                        <td>
                                            @if ($cat->tipo_pago === 'fijo')
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1.5 fw-bold border border-success border-opacity-25">Fijo</span>
                                            @elseif ($cat->tipo_pago === 'por_horas')
                                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1.5 fw-bold border border-primary border-opacity-25">Por Horas</span>
                                            @else
                                                <span class="text-white-50 small">Sin configurar</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold text-white">
                                            @if ($cat->monto_pago !== null)
                                                {{ $cat->moneda === 'USD' ? '$' : '₡' }}{{ number_format($cat->monto_pago, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="abrirConfigTarifaModal({{ json_encode($cat) }})">
                                                <i class="bi bi-gear-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ULTIMAS MARCAS -->
            <div class="col-lg-6">
                <div class="card glass-card">
                    <div class="card-header-custom">
                        <h5 class="fw-bold text-white mb-0"><i class="bi bi-clock-history text-success me-2"></i> Últimas 5 Marcas Registradas</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr class="text-white-50">
                                    <th>Empleado</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th class="text-end">Marca</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ultimas_marcas as $um)
                                    <tr>
                                        <td class="fw-bold text-white">{{ $um->usuario->nombre ?? '' }} {{ $um->usuario->apellidos ?? '' }}</td>
                                        <td>{{ $um->fecha->format('d/m/Y') }}</td>
                                        <td>{{ Carbon\Carbon::parse($um->hora_entrada)->format('g:i a') }}</td>
                                        <td class="text-end">
                                            @if ($um->estado === 'activo')
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold">Entrada</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1 fw-bold">Salida</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- MODAL CONFIGURAR TARIFA -->
    <div class="modal fade" id="tarifaConfigModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-wallet2 text-success me-2"></i> Configurar Tarifa Categoría</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('planilla.tarifa.categoria') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_rol" id="modal_id_rol">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label-custom">Categoría de Empleado</label>
                            <input type="text" id="modal_rol_nombre" class="form-control form-control-custom w-100" readonly style="opacity: 0.7;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Tipo de Pago</label>
                            <select name="tipo_pago" id="modal_tipo_pago" class="form-select form-select-custom w-100" required>
                                <option value="por_horas">Pago por Horas</option>
                                <option value="fijo">Salario Fijo</option>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-8">
                                <label class="form-label-custom">Monto de Pago / Salario</label>
                                <input type="number" step="0.01" name="monto_pago" id="modal_monto_pago" class="form-control form-control-custom w-100" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label-custom">Moneda</label>
                                <select name="moneda" id="modal_moneda" class="form-select form-select-custom w-100" required>
                                    <option value="CRC">Colones (₡)</option>
                                    <option value="USD">Dólares ($)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Guardar Tarifa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EMPLEADOS TRABAJANDO HOY -->
    <div class="modal fade" id="activeEmployeesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-fill-check text-success me-2"></i> Trabajando Hoy</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <ul class="list-group list-group-flush bg-transparent">
                        @if ($activos->count() > 0)
                            @foreach ($activos as $a)
                                <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong class="text-white">{{ $a->usuario->nombre }} {{ $a->usuario->apellidos }}</strong>
                                        <span class="d-block text-white-50 small">{{ $a->usuario->email }}</span>
                                    </div>
                                    <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-3 py-1.5 rounded-pill">
                                        Entrada: {{ Carbon\Carbon::parse($a->hora_entrada)->format('g:i a') }}
                                    </span>
                                </li>
                            @endforeach
                        @else
                            <div class="text-center text-white-50 py-3">No hay empleados con jornada activa actualmente.</div>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function abrirConfigTarifaModal(cat) {
            $('#modal_id_rol').val(cat.id_rol);
            $('#modal_rol_nombre').val(cat.rol_nombre);
            if (cat.tipo_pago) {
                $('#modal_tipo_pago').val(cat.tipo_pago);
            }
            if (cat.monto_pago) {
                $('#modal_monto_pago').val(cat.monto_pago);
            }
            if (cat.moneda) {
                $('#modal_moneda').val(cat.moneda);
            }
            $('#tarifaConfigModal').modal('show');
        }
    </script>
@endsection
