@extends('layouts.app')

@section('title', 'Inbox BPM - Registro de Asistencia')

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

        .clock-container {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid var(--border-dark);
            border-radius: 2rem;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
        }

        .clock-time {
            font-size: 4rem;
            font-weight: 800;
            letter-spacing: -0.05em;
            font-family: monospace;
            text-shadow: 0 0 15px rgba(95, 178, 48, 0.4);
            color: #f8fafc;
        }

        .clock-date {
            font-size: 1.1rem;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 0.5rem;
        }

        .btn-marcar {
            padding: 2rem;
            border-radius: 1.5rem;
            font-weight: 800;
            font-size: 1.6rem;
            width: 100%;
            transition: all 0.3s;
            border: none;
        }

        .btn-entrada {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 10px 25px rgba(95, 178, 48, 0.3);
        }

        .btn-entrada:hover {
            background-color: var(--primary-dark);
            transform: translateY(-4px);
            box-shadow: 0 15px 30px rgba(95, 178, 48, 0.4);
        }

        .btn-salida {
            background-color: #ef4444;
            color: white;
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
        }

        .btn-salida:hover {
            background-color: #dc2626;
            transform: translateY(-4px);
            box-shadow: 0 15px 30px rgba(239, 68, 68, 0.4);
        }

        .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            margin-bottom: 2rem;
        }

        .table-custom {
            margin-bottom: 0;
            background-color: transparent !important;
        }
        .table-custom td, .table-custom th {
            background-color: transparent !important;
            color: #e2e8f0 !important;
            border-bottom: 1px solid var(--border-dark);
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Registro de Asistencia</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header">
            <h1 class="display-6 fw-bold mb-1">Registro de Asistencia</h1>
            <p class="text-white-50 mb-0">Registre sus horas de entrada y salida diarias de la jornada laboral.</p>
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

        <div class="row g-4 align-items-stretch">
            <!-- RELOJ DIGITAL -->
            <div class="col-lg-6">
                <div class="clock-container h-100 d-flex flex-column justify-content-center">
                    <i class="bi bi-clock text-success fs-1 mb-3 animate__animated animate__pulse animate__infinite"></i>
                    <div class="clock-time" id="digitalClock">00:00:00</div>
                    <div class="clock-date" id="digitalDate">Cargando fecha...</div>
                </div>
            </div>

            <!-- REGISTRAR ACCION -->
            <div class="col-lg-6">
                <div class="card glass-card p-5 h-100 d-flex flex-column justify-content-center text-center">
                    <form action="{{ route('asistencia.registrar') }}" method="POST" id="marcaForm">
                        @csrf
                        @if ($jornada_activa === null)
                            <h4 class="fw-bold mb-3 text-white">Jornada Cerrada</h4>
                            <p class="text-white-50 mb-4">No has registrado tu entrada para el día de hoy.</p>
                            <input type="hidden" name="action" value="entrada">
                            <button type="submit" class="btn btn-marcar btn-entrada shadow">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Registrar Entrada
                            </button>
                        @else
                            <h4 class="fw-bold mb-2 text-success"><i class="bi bi-person-check-fill"></i> Jornada Activa</h4>
                            <p class="text-white-50 mb-4">
                                Entrada registrada a las: 
                                <span class="badge bg-success bg-opacity-20 text-success fs-6 fw-bold px-3 py-1.5 rounded-pill border border-success border-opacity-25 mt-1 d-inline-block">
                                    {{ Carbon\Carbon::parse($jornada_activa->hora_entrada)->format('g:i a') }}
                                </span>
                            </p>
                            <input type="hidden" name="action" value="salida">
                            <button type="submit" class="btn btn-marcar btn-salida shadow">
                                <i class="bi bi-box-arrow-left me-2"></i> Registrar Salida
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- BITACORA DE HOY -->
        <div class="card glass-card mt-5">
            <div class="card-header bg-transparent border-bottom border-secondary p-4">
                <h5 class="fw-bold text-white mb-0"><i class="bi bi-calendar3 me-2 text-success"></i> Marcas de Asistencia de Hoy</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr class="bg-dark text-white-50">
                            <th class="ps-4">Fecha</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Horas Trabajadas</th>
                            <th class="pe-4 text-end">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($historial_hoy->count() > 0)
                            @foreach ($historial_hoy as $h)
                                <tr>
                                    <td class="ps-4 fw-bold text-white">{{ $h->fecha->format('d/m/Y') }}</td>
                                    <td class="text-success fw-bold"><i class="bi bi-box-arrow-in-right me-1"></i> {{ Carbon\Carbon::parse($h->hora_entrada)->format('g:i a') }}</td>
                                    <td>
                                        @if ($h->hora_salida)
                                            <span class="text-danger fw-bold"><i class="bi bi-box-arrow-left me-1"></i> {{ Carbon\Carbon::parse($h->hora_salida)->format('g:i a') }}</span>
                                        @else
                                            <span class="text-white-50 small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($h->horas_trabajadas > 0)
                                            <span class="badge bg-light text-dark fw-bold px-3 py-1.5 rounded-pill">{{ $h->horas_trabajadas }} hrs</span>
                                        @else
                                            <span class="text-white-50 small">-</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        @if ($h->estado === 'activo')
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1.5 fw-bold border border-success border-opacity-25 animate__animated animate__flash animate__infinite">Jornada Activa</span>
                                        @else
                                            <span class="badge bg-secondary text-light rounded-pill px-3 py-1.5 fw-bold">Completado</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center py-5 text-white-50">
                                    <i class="bi bi-clock-history display-4 d-block mb-3"></i>
                                    No has registrado ninguna marca para el día de hoy.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            
            // Format 24h
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            
            document.getElementById('digitalClock').textContent = `${hours}:${minutes}:${seconds}`;
            
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('digitalDate').textContent = now.toLocaleDateString('es-ES', options);
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
@endsection
