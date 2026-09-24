@extends('layouts.app')

@section('title', 'Inbox BPM - Recursos Compartidos')

@section('styles')
    <style>
        .page-header {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2rem 2.5rem;
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

        /* Table design */
        .table-custom {
            margin-bottom: 0;
            background-color: transparent !important;
        }
        .table-custom td, .table-custom th {
            background-color: transparent !important;
            color: #e2e8f0 !important;
            border-bottom: 1px solid var(--border-dark);
            padding: 1rem 1.25rem;
            vertical-align: middle;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-5 px-md-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('recursos.index') }}" class="text-decoration-none text-white-50">Mis Recursos</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Recursos Compartidos</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Recursos Compartidos</h1>
                <p class="text-white-50 mb-0">Materiales y herramientas didácticas disponibles para todo el cuerpo docente de UNELA.</p>
            </div>
            
            <div>
                <a href="{{ route('recursos.index') }}" class="btn btn-outline-light rounded-pill px-4">
                    <i class="bi bi-collection-fill me-2"></i> Mis Recursos Privados
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- GOOGLE DRIVE CLOUD FILES -->
            <div class="col-lg-6">
                <div class="card glass-card h-100">
                    <div class="card-header bg-transparent border-bottom border-secondary p-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-white mb-0"><i class="bi bi-google text-primary me-2"></i> Documentos en Drive (Nube)</h5>
                        <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-25 px-3 py-1.5 rounded-pill small">{{ count($drive_compartidos) }} Archivos</span>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr class="bg-dark text-white-50">
                                    <th class="ps-4">Archivo</th>
                                    <th>Origen / Curso</th>
                                    <th class="text-end pe-4">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($drive_compartidos) > 0)
                                    @foreach ($drive_compartidos as $rd)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-3 me-3"></i>
                                                    <div>
                                                        <strong class="text-white d-block small">{{ $rd->nombre_archivo }}</strong>
                                                        <span class="text-white-50 x-small">{{ $rd->fecha_subida->format('d/m/Y') }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-white-50 small">{{ $rd->cursoActivo->planEstudio->materia ?? 'N/A' }}</span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="{{ $rd->link_publico }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3">
                                                    <i class="bi bi-cloud-arrow-down-fill me-1"></i> Abrir
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-white-50">No hay documentos de Google Drive compartidos.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TEACHER RESOURCES / DIDACTICOS -->
            <div class="col-lg-6">
                <div class="card glass-card h-100">
                    <div class="card-header bg-transparent border-bottom border-secondary p-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-white mb-0"><i class="bi bi-collection-fill text-success me-2" style="color: var(--primary) !important;"></i> Recursos Didácticos Docentes</h5>
                        <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-3 py-1.5 rounded-pill small">{{ count($didacticos_compartidos) }} Enlaces</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr class="bg-dark text-white-50">
                                    <th class="ps-4">Recurso / Categoría</th>
                                    <th>Compartido por</th>
                                    <th class="text-end pe-4">Enlace</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($didacticos_compartidos) > 0)
                                    @foreach ($didacticos_compartidos as $rc)
                                        <tr>
                                            <td class="ps-4">
                                                <strong class="text-white d-block small">{{ $rc->titulo }}</strong>
                                                <span class="badge bg-secondary rounded-pill x-small mt-1" style="font-size: 0.6rem;">{{ $rc->categoria->nombre ?? 'General' }}</span>
                                            </td>
                                            <td>
                                                <span class="text-white-50 small">{{ $rc->profesor->nombre ?? 'Sistema' }} {{ $rc->profesor->apellidos ?? '' }}</span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="{{ $rc->url }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3" style="border-color: var(--primary); color: var(--primary);">
                                                    <i class="bi bi-link-45deg me-1"></i> Ir al enlace
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-white-50">No hay recursos didácticos compartidos todavía.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection
