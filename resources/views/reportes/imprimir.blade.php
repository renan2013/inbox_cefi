@extends('layouts.app')

@section('title', 'Reporte - UNELA BPM')

@section('styles')
<style>
    :root {
        --primary-blue: #0d6efd;
        --card-bg: var(--card-dark);
        --border-color: var(--border-dark);
    }

    .main-container {
        padding-top: 2rem;
        padding-bottom: 4rem;
        color: #f8fafc;
    }

    .report-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        padding: 2.5rem;
        margin-bottom: 2rem;
    }

    .report-header-panel {
        border-bottom: 2px solid var(--border-dark);
        padding-bottom: 1.5rem;
        margin-bottom: 2rem;
    }

    .table-custom {
        margin-bottom: 0;
        background-color: transparent !important;
    }

    .table-custom td, .table-custom th {
        background-color: transparent !important;
        border-bottom: 1px solid var(--border-color);
        padding: 1rem;
        vertical-align: middle;
    }

    .table-custom th {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }

    /* Print styling rules */
    @media print {
        /* Hide navbar, menus, sidebar and footer */
        nav, .navbar, footer, .footer-custom, .no-print, #theme-toggle, .breadcrumb {
            display: none !important;
        }

        body {
            background-color: #ffffff !important;
            color: #000000 !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Adjust card to be a standard paper output */
        .main-container {
            padding: 0 !important;
            margin: 0 !important;
        }

        .report-card {
            background-color: #ffffff !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            color: #000000 !important;
        }

        .report-header-panel {
            border-bottom: 2px solid #000000 !important;
        }

        .table-custom td, .table-custom th {
            color: #000000 !important;
            border-bottom: 1px solid #cbd5e1 !important;
            background-color: transparent !important;
        }

        .table-custom th {
            background-color: #f1f5f9 !important;
        }

        /* Ensure bootstrap text overrides to black */
        .text-white, .text-light, .fw-bold, .report-title, strong {
            color: #000000 !important;
        }

        .text-white-50, .text-muted, p {
            color: #475569 !important;
        }

        .badge {
            border: 1px solid #000000 !important;
            color: #000000 !important;
            background: transparent !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container py-5 main-container">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4 no-print">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}" class="text-decoration-none text-white-50">Reportes</a></li>
            <li class="breadcrumb-item active text-white" aria-current="page">Visualizar Reporte</li>
        </ol>
    </nav>

    <!-- Main Report Card -->
    <div class="report-card">
        
        <!-- Header -->
        <div class="report-header-panel">
            <div class="row align-items-center">
                <div class="col-md-8 text-center text-md-start">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                        <span class="fs-2 fw-bold text-success" style="letter-spacing: -1px;">UNELA</span>
                        <span class="fs-5 text-white-50 border-start border-secondary ps-3 ms-2">BPM Intelligence System</span>
                    </div>
                </div>
                <div class="col-md-4 text-center text-md-end mt-3 mt-md-0 no-print">
                    <button onclick="window.print()" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-printer"></i> Imprimir Reporte
                    </button>
                    <a href="{{ route('reportes.index') }}" class="btn btn-outline-light rounded-pill px-4 ms-2">
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Metadata -->
        <div class="mb-4">
            <h2 class="report-title fw-bold text-white mb-1">{{ $titulo }}</h2>
            <p class="text-white-50 mb-0">Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} | Emitido por: {{ Auth::user()->nombre }}</p>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle">
                <thead>
                    <tr class="text-white-50">
                        @foreach($columnas as $col)
                            <th>{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="text-white">
                    @forelse($filas as $fila)
                        <tr>
                            @foreach($fila as $val)
                                <td>{!! $val !!}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columnas) }}" class="text-center py-5 text-white-50">
                                <i class="bi bi-folder-x fs-1 d-block mb-3 opacity-50"></i>
                                No se encontraron datos para este reporte.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="text-center text-white-50 mt-5 pt-3 border-top border-secondary">
            <p class="mb-0">&copy; {{ date('Y') }} Universidad Evangélica de las Américas - BPM Intelligence. Todos los derechos reservados.</p>
            <p class="mb-0 mt-1" style="font-size: 0.75rem;">Design and developed by renangalvan.net</p>
        </div>

    </div>

</div>
@endsection
