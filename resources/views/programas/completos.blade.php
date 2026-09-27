@extends('layouts.app')

@section('title', 'Inbox BPM - Programas Completos')

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

        .accordion-custom .accordion-item {
            background-color: rgba(30, 41, 59, 0.4);
            border: 1px solid var(--border-dark);
            margin-bottom: 1rem;
            border-radius: 1rem;
            overflow: hidden;
        }

        .accordion-custom .accordion-button {
            background-color: rgba(30, 41, 59, 0.7);
            color: #f8fafc;
            font-weight: 700;
            padding: 1.25rem 1.5rem;
            box-shadow: none;
        }

        .accordion-custom .accordion-button:not(.collapsed) {
            background-color: rgba(95, 178, 48, 0.1);
            color: var(--primary);
            border-bottom: 1px solid var(--border-dark);
        }

        .accordion-custom .accordion-button::after {
            filter: invert(1);
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
        }
        
        .info-pill {
            background-color: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-dark);
            border-radius: 0.75rem;
            padding: 0.75rem 1.25rem;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Programas Completos</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Estructura de Programas Académicos</h1>
                <p class="text-white-50 mb-0">Vista detallada de la oferta académica actual de {{ config('cliente.nombre', 'CEFI') }} y sus mallas curriculares correspondientes.</p>
            </div>
        </div>

        <!-- ACCORDEON DE PROGRAMAS -->
        <div class="accordion accordion-custom" id="programasAccordion">
            @foreach ($programas as $p)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-{{ $p->id_programa }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $p->id_programa }}" aria-expanded="false" aria-controls="collapse-{{ $p->id_programa }}">
                            <div class="d-flex align-items-center justify-content-between w-100 pe-3">
                                <div>
                                    <i class="bi bi-layers-half me-2"></i> {{ $p->nombre_programa }}
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 small border border-success border-opacity-25">{{ $p->categoria }}</span>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse-{{ $p->id_programa }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $p->id_programa }}" data-bs-parent="#programasAccordion">
                        <div class="accordion-body p-4 p-md-5">
                            
                            <!-- DETALLES DEL PROGRAMA -->
                            <div class="row g-4 mb-5">
                                <div class="col-lg-8">
                                    <h6 class="text-white fw-bold mb-3"><i class="bi bi-info-circle me-1 text-success"></i> Resumen del Programa</h6>
                                    <p class="text-white-50 mb-4">{{ $p->informacion ?? 'Sin resumen descriptivo cargado.' }}</p>
                                    
                                    @if ($p->perfil)
                                        <h6 class="text-white fw-bold mb-3"><i class="bi bi-award me-1 text-success"></i> Perfil del Egresado</h6>
                                        <p class="text-white-50">{{ $p->perfil }}</p>
                                    @endif
                                </div>
                                <div class="col-lg-4">
                                    <div class="info-pill h-100">
                                        <h6 class="text-white fw-bold mb-3 border-bottom border-secondary pb-2"><i class="bi bi-currency-dollar me-1 text-success"></i> Estructura de Costos</h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-white-50 small">Costo Materia:</span>
                                            <span class="fw-bold text-white">₡{{ number_format($p->costo_materia, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-white-50 small">Costo Matrícula:</span>
                                            <span class="fw-bold text-white">₡{{ number_format($p->costo_matricula, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-white-50 small">Costo Biblioteca:</span>
                                            <span class="fw-bold text-white">₡{{ number_format($p->costo_biblioteca, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between border-top border-secondary pt-2 mt-2">
                                            <span class="text-white-50 small">Inscripción Única:</span>
                                            <span class="fw-bold text-success">₡{{ number_format($p->costo_inscripcion_unica, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MALLA CURRICULAR -->
                            <h6 class="text-white fw-bold mb-3"><i class="bi bi-book-half me-1 text-success"></i> Malla Curricular / Plan de Estudios</h6>
                            <div class="table-responsive rounded-3 border border-secondary">
                                <table class="table table-custom mb-0">
                                    <thead>
                                        <tr class="bg-dark">
                                            <th class="ps-4">Código</th>
                                            <th>Curso / Materia</th>
                                            <th>Nivel / Cuatrimestre</th>
                                            <th class="text-center">Créditos</th>
                                            <th>Requisitos</th>
                                            <th class="text-end pe-4">Descriptor Oficial</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($p->planesEstudio->count() > 0)
                                            @foreach ($p->planesEstudio as $pe)
                                                <tr>
                                                    <td class="ps-4 font-monospace text-success fw-bold">{{ $pe->codigo }}</td>
                                                    <td class="fw-bold text-white">{{ $pe->materia }}</td>
                                                    <td>{{ $pe->cuatrimestre }}</td>
                                                    <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold">{{ $pe->creditos }}</span></td>
                                                    <td class="text-white-50 small">{{ $pe->requisitos ?? 'Sin requisitos' }}</td>
                                                    <td class="text-end pe-4">
                                                        <a href="{{ route('cursos.descriptor_pdf', $pe->id_plan) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" title="Generar Descriptor PDF">
                                                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF
                                                        </a>
                                                        <a href="{{ route('cursos.descriptor_doc', $pe->id_plan) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 ms-1" title="Descargar Descriptor Word (.doc)">
                                                            <i class="bi bi-file-earmark-word-fill me-1"></i> Word
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-white-50">
                                                    No se han registrado materias para esta carrera académica.
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
@endsection
