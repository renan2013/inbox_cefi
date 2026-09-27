@extends('layouts.app')

@section('title', 'Expediente 360° - ' . ($usuario ? $usuario->nombre . ' ' . $usuario->apellidos : 'Estudiante'))

@section('styles')
<style>
    .hero-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%);
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
    .tabs-card {
        background: rgba(30, 41, 59, 0.8);
        border: 1px solid var(--border-dark) !important;
    }
    [data-theme="light"] .hero-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
    }
    [data-theme="light"] .tabs-card {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- ALERTAS DE SESIÓN -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- PERFIL HERO SUPERIOR -->
    <div class="card hero-card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-column flex-lg-row align-items-center align-items-lg-start gap-4">
                
                <!-- Avatar / Fotografía Oficial -->
                <div class="position-relative">
                    @if($fotoUrl)
                        <img src="{{ $fotoUrl }}" alt="Foto Oficial" class="rounded-4 object-fit-cover shadow-lg border border-2 border-success" style="width: 130px; height: 130px;">
                    @else
                        <div class="rounded-4 d-flex align-items-center justify-content-center shadow-lg border border-secondary border-opacity-25" style="width: 130px; height: 130px; background: rgba(95, 178, 48, 0.15); color: var(--primary, #5fb230); font-size: 3rem; font-weight: bold;">
                            {{ mb_substr($usuario->nombre ?? 'E', 0, 1) }}{{ mb_substr($usuario->apellidos ?? '', 0, 1) }}
                        </div>
                    @endif
                    <span class="position-absolute bottom-0 end-0 translate-middle-y badge rounded-pill bg-dark border border-secondary px-2 py-1 small" style="font-family: monospace;">
                        #{{ str_pad($expediente->id_expediente, 5, '0', STR_PAD_LEFT) }}
                    </span>
                </div>

                <!-- Datos del Estudiante -->
                <div class="flex-grow-1 text-center text-lg-start">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-2 mb-2">
                        <h2 class="fw-bold text-white mb-0">
                            {{ $usuario ? "{$usuario->nombre} {$usuario->apellidos}" : 'Estudiante' }}
                        </h2>
                        
                        <!-- Badge Estado Expediente -->
                        @if($expediente->estado === 'Aprobado')
                            <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 rounded-pill px-3 py-1 fw-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> Expediente Formalizado
                            </span>
                        @elseif($expediente->estado === 'Rechazado')
                            <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-30 rounded-pill px-3 py-1 fw-semibold">
                                <i class="bi bi-x-circle-fill me-1"></i> Expediente Rechazado
                            </span>
                        @else
                            <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-30 rounded-pill px-3 py-1 fw-semibold">
                                <i class="bi bi-clock-fill me-1"></i> En Revisión
                            </span>
                        @endif
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-3 text-muted mb-3" style="font-size: 0.95rem;">
                        <span><i class="bi bi-card-text text-primary me-1"></i> {{ $usuario->cedula ?? $expediente->cedula_residencia ?? $expediente->pasaporte ?? 'Sin ID' }}</span>
                        <span>•</span>
                        <span><i class="bi bi-mortarboard-fill text-warning me-1"></i> {{ $expediente->especialidad_deseada ?: 'Programa General' }}</span>
                        <span>•</span>
                        <span><i class="bi bi-envelope-fill text-info me-1"></i> {{ $usuario->email ?? 'N/D' }}</span>
                    </div>

                    <!-- Botones de Acción Rápida -->
                    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-start">
                        @if(!empty($linkWa) && $linkWa !== '#')
                            <a href="{{ $linkWa }}" target="_blank" class="btn btn-success btn-sm px-3 rounded-pill fw-semibold d-flex align-items-center gap-2">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                        @endif

                        <a href="{{ route('expedientes.record_pdf', $expediente->id_expediente) }}" target="_blank" class="btn btn-outline-info btn-sm px-3 rounded-pill fw-semibold d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-pdf-fill"></i> Certificación Récord PDF
                        </a>

                        <a href="{{ route('expedientes.descargar_zip', $expediente->id_expediente) }}" class="btn btn-outline-light btn-sm px-3 rounded-pill fw-semibold d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-zip-fill"></i> Descargar Bóveda ZIP
                        </a>

                        <!-- Dropdown Cambio de Estado -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm px-3 rounded-pill dropdown-toggle text-white border-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-shield-check me-1"></i> Cambiar Estado
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark">
                                <li>
                                    <form action="{{ route('expedientes.cambiar_estado', $expediente->id_expediente) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action_expediente" value="aprobar">
                                        <button type="submit" class="dropdown-item text-success"><i class="bi bi-check-lg me-2"></i>Aprobar / Formalizar</button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('expedientes.cambiar_estado', $expediente->id_expediente) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action_expediente" value="pendiente">
                                        <button type="submit" class="dropdown-item text-warning"><i class="bi bi-clock me-2"></i>Poner en Revisión</button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('expedientes.cambiar_estado', $expediente->id_expediente) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action_expediente" value="rechazar">
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-x-lg me-2"></i>Rechazar Expediente</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Métricas KPI Rápidas -->
                <div class="d-flex flex-row flex-lg-column gap-3 justify-content-center border-start-lg border-secondary border-opacity-25 ps-lg-4" style="min-width: 220px;">
                    <div class="p-2 text-center text-lg-start">
                        <span class="text-muted small fw-bold text-uppercase d-block">Promedio Ponderado</span>
                        <h3 class="fw-bold text-white mb-0">{{ number_format($promedioGpa, 2) }} <span class="fs-6 text-muted">/100</span></h3>
                    </div>
                    <div class="p-2 text-center text-lg-start">
                        <span class="text-muted small fw-bold text-uppercase d-block">Materias Aprobadas</span>
                        <h4 class="fw-bold text-success mb-0">{{ $cntAprobados }} <span class="fs-6 text-muted">({{ $totalCreditosAprobados }} créditos)</span></h4>
                    </div>
                    <div class="p-2 text-center text-lg-start">
                        <span class="text-muted small fw-bold text-uppercase d-block">Estado Financiero</span>
                        <div>
                            @if($cuotasMoraCount > 0)
                                <span class="badge bg-danger rounded-pill px-3 py-1">En Cobro / Mora ({{ $cuotasMoraCount }})</span>
                            @elseif($saldoPendiente > 0)
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Saldo Pendiente</span>
                            @else
                                <span class="badge bg-success rounded-pill px-3 py-1">Solvente (Al Día)</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- PESTAÑAS 360° -->
    <div class="card tabs-card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header border-bottom border-secondary border-opacity-25 p-0 bg-transparent">
            <ul class="nav nav-tabs nav-fill border-0" id="expedienteTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 fw-bold text-white border-0" id="ficha-tab" data-bs-toggle="tab" data-bs-target="#ficha" type="button" role="tab" style="border-radius: 0;">
                        <i class="bi bi-person-badge-fill me-2 text-primary"></i>1. Ficha Personal & Biométrica
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold text-white border-0" id="record-tab" data-bs-toggle="tab" data-bs-target="#record" type="button" role="tab" style="border-radius: 0;">
                        <i class="bi bi-mortarboard-fill me-2 text-warning"></i>2. Récord Académico (Kardex)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold text-white border-0" id="finanzas-tab" data-bs-toggle="tab" data-bs-target="#finanzas" type="button" role="tab" style="border-radius: 0;">
                        <i class="bi bi-wallet2 me-2 text-info"></i>3. Estado de Cuenta & Finanzas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold text-white border-0" id="boveda-tab" data-bs-toggle="tab" data-bs-target="#boveda" type="button" role="tab" style="border-radius: 0;">
                        <i class="bi bi-archive-fill me-2 text-success"></i>4. Bóveda Documental
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold text-white border-0" id="bitacora-tab" data-bs-toggle="tab" data-bs-target="#bitacora" type="button" role="tab" style="border-radius: 0;">
                        <i class="bi bi-journal-text me-2 text-danger"></i>5. Bitácora & Notas
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4 text-white">
            <div class="tab-content" id="expedienteTabsContent">

                <!-- 1. FICHA PERSONAL & BIOMÉTRICA -->
                <div class="tab-pane fade show active" id="ficha" role="tabpanel">
                    <div class="row g-4">
                        <!-- Identidad y Personales -->
                        <div class="col-lg-6">
                            <div class="p-3 rounded-3 mb-4" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.05);">
                                <h5 class="fw-bold text-primary mb-3"><i class="bi bi-person-circle me-2"></i>Datos de Identidad</h5>
                                <div class="row g-2 small">
                                    <div class="col-sm-4 text-muted">Nombre Completo:</div>
                                    <div class="col-sm-8 fw-semibold">{{ $usuario->nombre ?? '' }} {{ $usuario->apellidos ?? '' }}</div>
                                    <div class="col-sm-4 text-muted">Identificación:</div>
                                    <div class="col-sm-8 fw-semibold">{{ $usuario->cedula ?? $expediente->cedula_residencia ?? $expediente->pasaporte ?? 'N/D' }}</div>
                                    <div class="col-sm-4 text-muted">Género:</div>
                                    <div class="col-sm-8">{{ $expediente->genero ?: 'No especificado' }}</div>
                                    <div class="col-sm-4 text-muted">Nacionalidad:</div>
                                    <div class="col-sm-8">{{ $expediente->nacionalidad ?: 'Costarricense' }}</div>
                                    <div class="col-sm-4 text-muted">Fecha Nacimiento:</div>
                                    <div class="col-sm-8">{{ $expediente->fecha_nacimiento ? date('d/m/Y', strtotime($expediente->fecha_nacimiento)) : 'N/D' }}</div>
                                    <div class="col-sm-4 text-muted">Lugar Nacimiento:</div>
                                    <div class="col-sm-8">{{ $expediente->lugar_nacimiento ?: 'N/D' }}</div>
                                    <div class="col-sm-4 text-muted">Estado Civil:</div>
                                    <div class="col-sm-8">{{ $expediente->estado_civil ?: 'N/D' }}</div>
                                </div>
                            </div>

                            <div class="p-3 rounded-3" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.05);">
                                <h5 class="fw-bold text-warning mb-3"><i class="bi bi-geo-alt-fill me-2"></i>Contacto y Domicilio</h5>
                                <div class="row g-2 small">
                                    <div class="col-sm-4 text-muted">Dirección Exacta:</div>
                                    <div class="col-sm-8">{{ $expediente->domicilio_direccion ?: 'N/D' }}</div>
                                    <div class="col-sm-4 text-muted">Provincia/Cantón:</div>
                                    <div class="col-sm-8">{{ $expediente->domicilio_provincia ?: 'N/D' }} / {{ $expediente->domicilio_canton ?: 'N/D' }}</div>
                                    <div class="col-sm-4 text-muted">Celular:</div>
                                    <div class="col-sm-8 fw-semibold">{{ $expediente->contacto_tel_celular ?: ($usuario->telefono ?? 'N/D') }}</div>
                                    <div class="col-sm-4 text-muted">Tel. Habitación:</div>
                                    <div class="col-sm-8">{{ $expediente->contacto_tel_habitacion ?: 'N/D' }}</div>
                                    <div class="col-sm-4 text-muted">Contacto Emergencia:</div>
                                    <div class="col-sm-8 text-danger fw-semibold">{{ $expediente->contacto_otro_emergencias ?: 'No registrado' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Procedencia y Laboral -->
                        <div class="col-lg-6">
                            <div class="p-3 rounded-3 mb-4" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.05);">
                                <h5 class="fw-bold text-success mb-3"><i class="bi bi-building me-2"></i>Procedencia Académica</h5>
                                <div class="row g-2 small">
                                    <div class="col-sm-4 text-muted">Colegio / Secundaria:</div>
                                    <div class="col-sm-8">{{ $expediente->procedencia_secundaria_institucion ?: 'N/D' }} ({{ $expediente->procedencia_secundaria_ano_graduacion ?: 'N/D' }})</div>
                                    <div class="col-sm-4 text-muted">Título Secundaria:</div>
                                    <div class="col-sm-8">{{ $expediente->procedencia_secundaria_grado_obtenido ?: 'Bachiller en Educación Media' }}</div>
                                    <div class="col-sm-4 text-muted">Universidad Previa:</div>
                                    <div class="col-sm-8">{{ $expediente->procedencia_universidad ?: 'Ninguna / Primer Ingreso' }}</div>
                                    <div class="col-sm-4 text-muted">Título Universitario:</div>
                                    <div class="col-sm-8">{{ $expediente->procedencia_universidad_grado_obtenido ?: 'N/D' }}</div>
                                </div>
                            </div>

                            <div class="p-3 rounded-3 mb-4" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.05);">
                                <h5 class="fw-bold text-info mb-3"><i class="bi bi-briefcase-fill me-2"></i>Información Laboral</h5>
                                <div class="row g-2 small">
                                    <div class="col-sm-4 text-muted">Empresa / Iglesia:</div>
                                    <div class="col-sm-8 fw-semibold">{{ $expediente->laboral_institucion ?: 'N/D' }}</div>
                                    <div class="col-sm-4 text-muted">Puesto / Cargo:</div>
                                    <div class="col-sm-8">{{ $expediente->laboral_puesto ?: 'N/D' }}</div>
                                    <div class="col-sm-4 text-muted">Teléfono Laboral:</div>
                                    <div class="col-sm-8">{{ $expediente->laboral_telefono ?: 'N/D' }}</div>
                                </div>
                            </div>

                            <!-- Firma Digitalizada -->
                            @if($firmaUrl)
                            <div class="p-3 rounded-3 text-center" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.05);">
                                <h6 class="text-muted small fw-bold text-uppercase mb-2">Firma Manuscrita Digitalizada</h6>
                                <img src="{{ $firmaUrl }}" alt="Firma del Estudiante" class="img-fluid bg-white rounded p-2" style="max-height: 80px;">
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 2. RÉCORD ACADÉMICO (KARDEX) -->
                <div class="tab-pane fade" id="record" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold text-white mb-0">Récord de Calificaciones Oficiales</h5>
                            <small class="text-muted">Historial completo de cursos cursados, créditos y notas registradas en Actas.</small>
                        </div>
                        <a href="{{ route('expedientes.record_pdf', $expediente->id_expediente) }}" target="_blank" class="btn btn-primary btn-sm px-3 rounded-pill fw-semibold">
                            <i class="bi bi-printer-fill me-1"></i> Imprimir Certificación Oficial
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-white" style="border-color: rgba(255,255,255,0.08);">
                            <thead style="background: rgba(15, 23, 42, 0.6); font-size: 0.8rem; text-transform: uppercase;">
                                <tr>
                                    <th class="py-3 text-muted">Código</th>
                                    <th class="py-3 text-muted">Asignatura</th>
                                    <th class="py-3 text-muted">Período</th>
                                    <th class="py-3 text-muted">Docente</th>
                                    <th class="py-3 text-muted text-center">Créd.</th>
                                    <th class="py-3 text-muted text-center">Nota</th>
                                    <th class="py-3 text-muted text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cursosHistorial as $ch)
                                <tr>
                                    <td class="font-monospace fw-bold text-muted">{{ $ch['codigo'] }}</td>
                                    <td class="fw-semibold">{{ $ch['materia'] }}</td>
                                    <td><span class="badge bg-secondary bg-opacity-25 text-light">{{ $ch['periodo'] ?: 'N/D' }}</span></td>
                                    <td class="small text-muted">{{ $ch['prof_nombre'] }}</td>
                                    <td class="text-center">{{ $ch['creditos_calc'] }}</td>
                                    <td class="text-center font-monospace fw-bold fs-6">
                                        @if($ch['calificacion'] !== null && $ch['calificacion'] !== '')
                                            {{ number_format((float)$ch['calificacion'], 1) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($ch['estado_calc'] === 'Aprobado')
                                            <span class="badge bg-success rounded-pill px-3 py-1">Aprobado</span>
                                        @elseif($ch['estado_calc'] === 'Reprobado')
                                            <span class="badge bg-danger rounded-pill px-3 py-1">Reprobado</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-3 py-1">En Curso</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-book fs-1 d-block mb-2"></i>
                                        El estudiante no tiene asignaturas matriculadas o registradas en el sistema.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. ESTADO DE CUENTA & FINANZAS -->
                <div class="tab-pane fade" id="finanzas" role="tabpanel">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background: rgba(15, 23, 42, 0.6); border-left: 4px solid #3b82f6;">
                                <div class="text-muted small text-uppercase fw-bold">Total Facturado</div>
                                <h3 class="text-white fw-bold mb-0">₡{{ number_format($totalFacturado, 2) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background: rgba(15, 23, 42, 0.6); border-left: 4px solid #10b981;">
                                <div class="text-muted small text-uppercase fw-bold">Total Pagado</div>
                                <h3 class="text-success fw-bold mb-0">₡{{ number_format($totalPagado, 2) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3" style="background: rgba(15, 23, 42, 0.6); border-left: 4px solid {{ $saldoPendiente > 0 ? '#ef4444' : '#10b981' }};">
                                <div class="text-muted small text-uppercase fw-bold">Saldo Pendiente</div>
                                <h3 class="{{ $saldoPendiente > 0 ? 'text-danger' : 'text-success' }} fw-bold mb-0">₡{{ number_format($saldoPendiente, 2) }}</h3>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold text-white mb-3">Historial de Boletas Emitidas</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-white" style="border-color: rgba(255,255,255,0.08);">
                            <thead style="background: rgba(15, 23, 42, 0.6); font-size: 0.8rem; text-transform: uppercase;">
                                <tr>
                                    <th class="py-3 text-muted">Boleta #</th>
                                    <th class="py-3 text-muted">Período</th>
                                    <th class="py-3 text-muted">Fecha Emisión</th>
                                    <th class="py-3 text-muted text-end">Total</th>
                                    <th class="py-3 text-muted text-end">Pagado</th>
                                    <th class="py-3 text-muted text-end">Saldo</th>
                                    <th class="py-3 text-muted text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($boletasHistorial as $item)
                                @php $b = $item['boleta']; @endphp
                                <tr>
                                    <td class="font-monospace fw-bold text-primary">{{ $b->numero_boleta }}</td>
                                    <td>{{ $b->periodo ?: 'N/D' }}</td>
                                    <td>{{ $b->fecha_creacion ? date('d/m/Y', strtotime($b->fecha_creacion)) : 'N/D' }}</td>
                                    <td class="text-end fw-bold">₡{{ number_format((float)$b->total, 2) }}</td>
                                    <td class="text-end text-success">₡{{ number_format((float)$b->monto_pagado, 2) }}</td>
                                    <td class="text-end text-danger fw-bold">₡{{ number_format((float)$b->saldo_pendiente, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-pill px-3 py-1">{{ ucfirst($b->estado) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-wallet2 fs-1 d-block mb-2"></i>
                                        No hay boletas de pago registradas para este estudiante.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 4. BÓVEDA DOCUMENTAL DINÁMICA -->
                <div class="tab-pane fade" id="boveda" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold text-white mb-0">Bóveda Documental Jerárquica (EDMS)</h5>
                            <small class="text-muted">Almacenamiento clasificado por categorías para identificación, títulos, comprobantes y cartas.</small>
                        </div>
                        <button type="button" class="btn btn-success btn-sm px-4 rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#modalSubirDoc">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Subir Documento
                        </button>
                    </div>

                    <div class="row g-4">
                        @foreach($categoriasBoveda as $catKey => $catNombre)
                        @php
                            $docsCat = $expediente->archivos->filter(function($arch) use ($catKey) {
                                return \App\Services\ExpedienteStorageService::getCategoriaSubfolder($arch->tipo_documento, $arch->categoria) === $catKey;
                            });
                        @endphp
                        <div class="col-lg-6">
                            <div class="p-3 rounded-3 h-100" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.05);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold text-white mb-0">{{ $catNombre }}</h6>
                                    <span class="badge bg-secondary bg-opacity-25 text-light rounded-pill">{{ $docsCat->count() }}</span>
                                </div>
                                
                                @if($docsCat->isEmpty())
                                    <div class="text-muted small py-2 fst-italic">Sin documentos en esta categoría.</div>
                                @else
                                    <div class="list-group list-group-flush">
                                        @foreach($docsCat as $doc)
                                        @php $resDoc = \App\Services\ExpedienteStorageService::resolveFilePath($doc->nombre_servidor); @endphp
                                        <div class="list-group-item bg-transparent text-white px-0 py-2 border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 75%;">
                                                <i class="bi bi-file-earmark-text-fill text-primary"></i>
                                                <div class="text-truncate">
                                                    <a href="{{ $resDoc['web_url'] }}" target="_blank" class="text-white text-decoration-none fw-semibold small">
                                                        {{ $doc->nombre_original }}
                                                    </a>
                                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $doc->fecha_subida ? $doc->fecha_subida->format('d/m/Y g:i a') : '' }} • {{ $doc->subido_por }}</div>
                                                </div>
                                            </div>
                                            <button onclick="eliminarDocumento({{ $doc->id_archivo }})" class="btn btn-link text-danger p-0 ms-2" title="Eliminar archivo">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- 5. BITÁCORA & NOTAS INTERNAS -->
                <div class="tab-pane fade" id="bitacora" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="p-3 rounded-3" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.05);">
                                <h6 class="fw-bold text-white mb-3"><i class="bi bi-pencil-square me-2 text-warning"></i>Nueva Nota Administrativa</h6>
                                <form id="formObservacion">
                                    <input type="hidden" name="id_expediente" value="{{ $expediente->id_expediente }}">
                                    <div class="mb-3">
                                        <label class="form-label small text-muted">Categoría</label>
                                        <select name="categoria" class="form-select form-select-sm bg-dark text-white border-secondary">
                                            <option value="General">General</option>
                                            <option value="Académica">Académica / Convalidaciones</option>
                                            <option value="Financiera">Financiera / Arreglo de Pago</option>
                                            <option value="Conducta">Conducta / Pastoral</option>
                                            <option value="Trámite">Trámite de Graduación</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted">Observación</label>
                                        <textarea name="observacion" rows="4" class="form-control bg-dark text-white border-secondary" placeholder="Escriba la nota de seguimiento interno..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm px-4 rounded-pill fw-semibold w-100">
                                        <i class="bi bi-save me-1"></i> Guardar Nota
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <h6 class="fw-bold text-white mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Historial de Notas Internas</h6>
                            <div id="contenedorNotas">
                                @forelse($expediente->observaciones as $obs)
                                <div class="p-3 rounded-3 mb-3" style="background: rgba(15, 23, 42, 0.5); border-left: 3px solid #3b82f6;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-secondary bg-opacity-25 text-info">{{ $obs->categoria }}</span>
                                        <small class="text-muted">{{ $obs->fecha_registro ? $obs->fecha_registro->format('d/m/Y g:i a') : '' }}</small>
                                    </div>
                                    <p class="text-white small mb-1">{{ $obs->observacion }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Por: <strong>{{ $obs->autor_nombre ?: 'Administrador' }}</strong></small>
                                        <button onclick="eliminarObservacion({{ $obs->id }})" class="btn btn-link text-danger p-0 small">Eliminar</button>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                                    No hay notas internas registradas en la bitácora.
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- MODAL SUBIR DOCUMENTO -->
<div class="modal fade" id="modalSubirDoc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold"><i class="bi bi-cloud-arrow-up-fill text-success me-2"></i>Subir Documento a la Bóveda</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSubirArchivo" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id_expediente" value="{{ $expediente->id_expediente }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Tipo de Documento</label>
                        <select name="tipo_documento" class="form-select bg-dark text-white border-secondary" required>
                            <option value="cedula">Cédula / Documento de Identidad</option>
                            <option value="titulo_secundaria">Título de Bachiller en Secundaria</option>
                            <option value="titulo_universitario">Título Universitario Previo</option>
                            <option value="certificacion_notas">Certificación de Notas / Convalidación</option>
                            <option value="fotografia">Fotografía Oficial para Carnet</option>
                            <option value="firma">Firma Digitalizada</option>
                            <option value="carta_pastoral">Carta Pastoral / Recomendación</option>
                            <option value="comprobante_pago">Comprobante de Pago / Depósito</option>
                            <option value="otro">Otro Documento</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Categoría en Bóveda</label>
                        <select name="categoria" class="form-select bg-dark text-white border-secondary" required>
                            @foreach($categoriasBoveda as $catK => $catV)
                                <option value="{{ $catK }}">{{ $catV }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Seleccionar Archivo (PDF, JPG, PNG, DOCX, ZIP - Max 25MB)</label>
                        <input type="file" name="archivo" class="form-control bg-dark text-white border-secondary" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Descripción u Observación (Opcional)</label>
                        <input type="text" name="descripcion" class="form-control bg-dark text-white border-secondary" placeholder="Ej: Copia legalizada de título">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4" id="btnGuardarDoc">Subir Archivo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Subir documento vía AJAX
    document.getElementById('formSubirArchivo').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnGuardarDoc');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Subiendo...';

        const formData = new FormData(this);

        fetch("{{ route('expedientes.subir_documento') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = 'Subir Archivo';
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Subida Exitosa!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', data.message || 'Error al subir documento', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = 'Subir Archivo';
            Swal.fire('Error', 'Fallo de comunicación con el servidor', 'error');
        });
    });

    // Eliminar documento vía AJAX
    function eliminarDocumento(idArchivo) {
        Swal.fire({
            title: '¿Eliminar documento?',
            text: 'Esta acción borrará el archivo de la bóveda de forma permanente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('/expedientes/documentos') }}/${idArchivo}/eliminar`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }
        });
    }

    // Guardar nota de bitácora
    document.getElementById('formObservacion').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch("{{ route('expedientes.observaciones.guardar') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Guardado',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    });

    // Eliminar nota de bitácora
    function eliminarObservacion(idObs) {
        Swal.fire({
            title: '¿Eliminar nota?',
            text: 'Se removerá la nota de la bitácora interna.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Eliminar'
        }).then((res) => {
            if (res.isConfirmed) {
                fetch(`{{ url('/expedientes/observaciones') }}/${idObs}/eliminar`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }
        });
    }
</script>
@endsection
