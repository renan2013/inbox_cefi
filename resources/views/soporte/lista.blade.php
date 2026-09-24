@extends('layouts.app')

@section('title', 'Inbox BPM - Lista de Soporte')

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

        .solution-box {
            background-color: rgba(255, 255, 255, 0.02);
            border-left: 4px solid var(--primary);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 0.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Lista de Soporte</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header">
            <h1 class="display-6 fw-bold mb-1">Bitácora de Soporte Técnico</h1>
            <p class="text-white-50 mb-0">Consulte reportes de averías, estados de resolución y soluciones aplicadas históricamente.</p>
        </div>

        <!-- SEARCH AND FILTER -->
        <div class="card glass-card">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('soporte.lista') }}" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label-custom">Buscar por palabra clave</label>
                        <input type="text" name="search" class="form-control form-control-custom w-100" placeholder="Ej: internet, proyector, licencias..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">Filtrar por Categoría</label>
                        <select name="categoria_id" class="form-select form-select-custom w-100">
                            <option value="">Todas las categorías...</option>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-success w-100 py-2.5 rounded-3 fw-bold" style="background-color: var(--primary); border: none;">Buscar</button>
                        <a href="{{ route('soporte.lista') }}" class="btn btn-outline-secondary w-100 py-2.5 rounded-3 fw-bold text-white">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- TICKET ACCORDION LIST -->
        <div class="card glass-card p-4">
            @if ($soportes->count() > 0)
                <div class="accordion accordion-dark" id="ticketsAccordion">
                    @foreach ($soportes as $ticket)
                        <div class="accordion-item bg-transparent border-secondary text-white mb-3" style="border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 1rem; overflow: hidden;">
                            <h2 class="accordion-header" id="heading-{{ $ticket->id }}">
                                <button class="accordion-button collapsed bg-transparent text-white fw-bold d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $ticket->id }}" aria-expanded="false" aria-controls="collapse-{{ $ticket->id }}" style="box-shadow: none;">
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($ticket->estado === 'reportado')
                                            <span class="badge bg-danger rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">Reportado</span>
                                        @else
                                            <span class="badge bg-success rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">Solucionado</span>
                                        @endif
                                        <span>{{ $ticket->titulo }}</span>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse-{{ $ticket->id }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $ticket->id }}" data-bs-parent="#ticketsAccordion">
                                <div class="accordion-body p-4 border-top border-secondary">
                                    <div class="row g-3 mb-3 small text-white-50">
                                        <div class="col-md-4">
                                            <strong>Categoría:</strong> <span class="text-white">{{ $ticket->categoria->nombre ?? 'General' }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Reportado el:</strong> <span class="text-white">{{ $ticket->fecha_creacion ? $ticket->fecha_creacion->format('d/m/Y g:i a') : '-' }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Encargado:</strong> <span class="text-white">{{ $ticket->reportadoA->nombre ?? 'Sin Asignar' }} {{ $ticket->reportadoA->apellidos ?? '' }}</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <h6 class="fw-bold text-success mb-1">Problema / Falla:</h6>
                                        <p class="text-white-50 mb-0">{{ $ticket->problema ?: 'Sin descripción detallada.' }}</p>
                                    </div>

                                    @if ($ticket->solucion)
                                        <div class="solution-box">
                                            <h6 class="fw-bold text-success mb-1"><i class="bi bi-patch-check-fill me-1"></i> Resolución técnica:</h6>
                                            <p class="text-white mb-0">{{ $ticket->solucion }}</p>
                                        </div>
                                    @endif

                                    @if ($ticket->adjunto)
                                        <div class="mt-3">
                                            <a href="{{ asset('uploads/soporte/' . $ticket->adjunto) }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3"><i class="bi bi-file-earmark-arrow-down-fill me-1"></i> Descargar Adjunto</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5 text-white-50">
                    <i class="bi bi-clock-history display-3 d-block mb-3"></i>
                    No se encontraron reportes que coincidan con la búsqueda.
                </div>
            @endif
            
            @if ($soportes->hasPages())
                <div class="border-top border-secondary pt-4 mt-4">
                    {{ $soportes->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
