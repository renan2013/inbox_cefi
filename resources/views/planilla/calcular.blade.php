@extends('layouts.app')

@section('title', 'Inbox BPM - Cálculo de Planilla')

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

        .info-pill {
            background-color: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-dark);
            border-radius: 1rem;
            padding: 1.5rem;
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
            box-shadow: 0 8px 15px rgba(95, 178, 48, 0.25);
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('planilla.index') }}" class="text-decoration-none text-white-50">Panel de Planilla</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Cálculo de Planilla</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Cálculo de Planilla</h1>
                <p class="text-white-50 mb-0">Calcule y registre los salarios y pagos históricos del personal por fechas.</p>
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

        <!-- FILTER CALCULATOR -->
        <div class="card glass-card mb-4">
            <div class="card-body p-4 p-md-5">
                <form method="GET" action="{{ route('planilla.calcular') }}" class="row g-4 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label-custom">Seleccionar Empleado</label>
                        <select name="id_usuario" class="form-select form-select-custom w-100" required>
                            <option value="">Seleccione Colaborador...</option>
                            @foreach ($empleados as $e)
                                <option value="{{ $e->id }}" {{ $calc_user == $e->id ? 'selected' : '' }}>{{ $e->nombre }} {{ $e->apellidos }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Fecha Inicio Rango</label>
                        <input type="date" name="fecha_inicio" class="form-control form-control-custom w-100" required value="{{ $calc_start }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Fecha Fin Rango</label>
                        <input type="date" name="fecha_fin" class="form-control form-control-custom w-100" required value="{{ $calc_end }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100 py-3 rounded-3 fw-bold" style="background-color: var(--primary); border: none;">
                            <i class="bi bi-calculator-fill me-1"></i> Calcular
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if ($mostrar_calculo)
            <div class="row g-4 mb-5">
                <!-- DETALLES Y LIQUIDACIÓN -->
                <div class="col-lg-5">
                    <div class="card glass-card h-100 p-4">
                        <h5 class="fw-bold text-white mb-4 border-bottom border-secondary pb-2"><i class="bi bi-receipt text-success me-2"></i> Liquidación de Pago</h5>
                        
                        <div class="info-pill mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-white-50">Empleado:</span>
                                <strong class="text-white">{{ $empleado_info->nombre }} {{ $empleado_info->apellidos }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-white-50">Tipo Pago:</span>
                                <span class="badge bg-success bg-opacity-20 text-success rounded-pill px-3 py-1 fw-bold border border-success border-opacity-25">{{ $tarifas_info->tipo_pago === 'fijo' ? 'Salario Fijo' : 'Por Horas' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-white-50">Tarifa Aplicada:</span>
                                <strong class="text-white">{{ $tarifas_info->moneda === 'USD' ? '$' : '₡' }}{{ number_format($tarifas_info->monto_pago, 2) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-white-50">Total Horas Trabajadas:</span>
                                <strong class="text-white">{{ $horas_totales }} hrs</strong>
                            </div>
                        </div>

                        <div class="text-center py-4 rounded-3 bg-dark mb-4 border border-secondary">
                            <span class="d-block text-white-50 small text-uppercase">Monto Neto a Depositar</span>
                            <h2 class="display-6 fw-bold text-success mt-1 mb-0">{{ $tarifas_info->moneda === 'USD' ? '$' : '₡' }}{{ number_format($monto_calculado, 2) }}</h2>
                        </div>

                        <!-- REGISTRAR PAGO -->
                        <form action="{{ route('planilla.calcular.pago') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id_usuario" value="{{ $calc_user }}">
                            <input type="hidden" name="fecha_inicio" value="{{ $calc_start }}">
                            <input type="hidden" name="fecha_fin" value="{{ $calc_end }}">
                            <input type="hidden" name="tipo_pago" value="{{ $tarifas_info->tipo_pago }}">
                            <input type="hidden" name="horas_totales" value="{{ $horas_totales }}">
                            <input type="hidden" name="tarifa_aplicada" value="{{ $tarifas_info->monto_pago }}">
                            <input type="hidden" name="monto_total" value="{{ $monto_calculado }}">

                            <div class="mb-4">
                                <label class="form-label-custom">Notas / Observaciones del Pago</label>
                                <textarea name="notes" class="form-control form-control-custom" rows="3" placeholder="Ej: Depósito de quincena regular..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-submit w-100 py-3 shadow">
                                <i class="bi bi-wallet2 me-1"></i> Procesar y Registrar Pago
                            </button>
                        </form>
                    </div>
                </div>

                <!-- BITACORA DE MARCAS COMPILADAS -->
                <div class="col-lg-7">
                    <div class="card glass-card h-100">
                        <div class="card-header-custom">
                            <h5 class="fw-bold text-white mb-0"><i class="bi bi-clock-history text-success me-2"></i> Jornadas Registradas ({{ count($jornadas) }})</h5>
                        </div>
                        <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                            <table class="table table-custom mb-0">
                                <thead class="bg-dark text-white-50 sticky-top">
                                    <tr>
                                        <th class="ps-4">Fecha</th>
                                        <th>Entrada</th>
                                        <th>Salida</th>
                                        <th class="text-end pe-4">Horas Totales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($jornadas) > 0)
                                        @foreach ($jornadas as $jor)
                                            <tr>
                                                <td class="ps-4 fw-bold text-white">{{ Carbon\Carbon::parse($jor->fecha)->format('d/m/Y') }}</td>
                                                <td class="text-success"><i class="bi bi-box-arrow-in-right me-1"></i> {{ Carbon\Carbon::parse($jor->hora_entrada)->format('g:i a') }}</td>
                                                <td>
                                                    @if ($jor->hora_salida)
                                                        <span class="text-danger"><i class="bi bi-box-arrow-left me-1"></i> {{ Carbon\Carbon::parse($jor->hora_salida)->format('g:i a') }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="text-end pe-4 font-monospace fw-bold text-white">{{ $jor->horas_trabajadas }} hrs</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-white-50">
                                                No se encontraron marcas de asistencia completadas para este rango de fechas.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection
