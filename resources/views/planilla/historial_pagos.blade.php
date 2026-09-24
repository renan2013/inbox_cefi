@extends('layouts.app')

@section('title', 'Inbox BPM - Historial de Pagos de Planilla')

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
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('planilla.index') }}" class="text-decoration-none text-white-50">Panel de Planilla</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Historial de Pagos</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Historial de Pagos de Planilla</h1>
                <p class="text-white-50 mb-0">Bitácora completa y consulta de salarios emitidos y procesados históricamente.</p>
            </div>
        </div>

        <!-- LISTADO DE PAGOS -->
        <div class="card glass-card">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr class="bg-dark text-white-50">
                            <th class="ps-4">No. Pago</th>
                            <th>Empleado</th>
                            <th>Periodo de Pago</th>
                            <th>Modalidad</th>
                            <th class="text-center">Horas</th>
                            <th>Monto Pagado</th>
                            <th class="pe-4 text-end">Fecha Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($pagos->count() > 0)
                            @foreach ($pagos as $p)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-success bg-opacity-20 text-success rounded px-2.5 py-1.5 fw-bold border border-success border-opacity-25">
                                            #{{ str_pad($p->id, 5, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-white">{{ $p->usuario->nombre ?? '' }} {{ $p->usuario->apellidos ?? '' }}</div>
                                        <div class="small text-white-50" style="font-size: 0.8rem;">{{ $p->usuario->email ?? '' }}</div>
                                    </td>
                                    <td>
                                        <span class="small fw-semibold text-white">
                                            <i class="bi bi-calendar-range opacity-50 me-1"></i>
                                            {{ $p->fecha_inicio->format('d/m/Y') }} - {{ $p->fecha_fin->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($p->tipo_pago === 'fijo')
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1.5 fw-bold border border-success border-opacity-25">Fijo</span>
                                        @else
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1.5 fw-bold border border-primary border-opacity-25">Por Horas</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($p->tipo_pago === 'por_horas')
                                            <span class="fw-bold text-white">{{ $p->horas_totales }} hrs</span>
                                        @else
                                            <span class="text-white-50">-</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-success">
                                        {{ ($p->usuario && $p->usuario->empleadoTarifa && $p->usuario->empleadoTarifa->moneda === 'USD') ? '$' : '₡' }}{{ number_format($p->monto_total, 2) }}
                                    </td>
                                    <td class="pe-4 text-end text-white-50 small">{{ $p->fecha_pago->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center py-5 text-white-50">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    No se han registrado pagos de planilla.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @if ($pagos->hasPages())
                <div class="card-footer bg-transparent border-top border-secondary p-4">
                    {{ $pagos->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
