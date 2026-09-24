@extends('layouts.app')

@section('title', 'Inbox BPM - Ingresos en Tiempo Real (Drive)')

@section('styles')
    <style>
        .page-header {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
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

        .iframe-wrapper {
            position: relative;
            width: 100%;
            height: 750px;
            border-radius: 1rem;
            overflow: hidden;
            border: 1px solid var(--border-dark);
        }

        .iframe-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-5 px-md-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}" class="text-decoration-none text-white-50">Reportes</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Ingresos en Tiempo Real</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1 text-white">
                    <i class="bi bi-file-earmark-spreadsheet-fill text-success me-2"></i> Ingresos en Tiempo Real (Drive)
                </h1>
                <p class="text-white-50 mb-0">Hoja de cálculo de Google Drive alimentada por n8n con el registro de accesos en tiempo real.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Reportes
                </a>
            </div>
        </div>

        <div class="glass-card">
            <div class="iframe-wrapper">
                <iframe src="https://docs.google.com/spreadsheets/d/14qKM6ocC96i8PoxG30NdpPxMlmDmYaRLe6tMZhUWC-4/edit?rm=minimal" allowfullscreen></iframe>
            </div>
        </div>

    </div>
@endsection
