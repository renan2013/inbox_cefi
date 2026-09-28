@extends('layouts.app')

@section('title', 'Inbox BPM - Procesar Boleta ' . ($boleta->numero_boleta ?? ''))

@section('styles')
<style>
    .boleta-process-card {
        max-width: 900px;
        background: #ffffff;
        color: #1e293b;
        border-radius: 1.5rem;
        box-shadow: 0 20px 50px rgba(0,0,0,0.1) !important;
        border: none;
        position: relative;
        overflow: hidden;
    }
    .boleta-process-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 8px;
        background: #5fb230;
    }
    .boleta-section-header {
        background-color: #1e293b !important;
        color: #ffffff !important;
        padding: 0.65rem 1.25rem !important;
        border-radius: 0.5rem;
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        margin-bottom: 1.25rem;
    }
    .table-boleta thead th {
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        padding: 0.85rem 1rem;
    }
    .table-boleta td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #1e293b;
    }
    .signature-verified-box {
        background-color: #f0fdf4;
        border: 2px solid #bbf7d0;
        border-radius: 1rem;
        padding: 1.25rem;
    }
</style>
@endsection

@section('content')
<div class="container mt-4 mb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-custom">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('boletas.index') }}"><i class="bi bi-receipt"></i> Bandeja de Boletas</a></li>
            <li class="breadcrumb-item active" aria-current="page">Procesar Boleta {{ $boleta->numero_boleta }}</li>
        </ol>
    </nav>

    <!-- Banner de Estado de la Boleta -->
    @if ($boleta->estado === 'firmada')
        <div class="alert alert-success d-flex flex-wrap align-items-center justify-content-between p-3 rounded-4 shadow-sm mb-4 border-2 border-success">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-patch-check-fill fs-2 text-success"></i>
                <div>
                    <h5 class="fw-bold mb-0 text-success">Boleta Firmada por el Estudiante</h5>
                    <p class="mb-0 text-muted small">
                        El estudiante completó la firma digital el {{ $boleta->fecha_firma ? $boleta->fecha_firma->format('d/m/Y h:i A') : date('d/m/Y') }}. Puede verificar los datos, registrar abonos y oficializar el trámite.
                    </p>
                </div>
            </div>
            <span class="badge bg-success px-3 py-2 fs-6 rounded-pill">Lista para Oficializar</span>
        </div>
    @elseif ($boleta->estado === 'pendiente' || $boleta->estado === 'pago_parcial' || $boleta->estado === 'pagada')
        <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between p-3 rounded-4 shadow-sm mb-4">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-check-all fs-2 text-primary"></i>
                <div>
                    <h5 class="fw-bold mb-0 text-primary">Boleta Ya Oficializada</h5>
                    <p class="mb-0 text-muted small">Esta boleta ya cuenta con PDF oficial generado y cuotas vigentes.</p>
                </div>
            </div>
            <a href="{{ route('boletas.pdf', $boleta->id) }}" target="_blank" class="btn btn-primary rounded-pill px-4 fw-bold">
                <i class="bi bi-file-earmark-pdf me-1"></i> Ver PDF Oficial
            </a>
        </div>
    @else
        <div class="alert alert-warning d-flex align-items-center gap-3 p-3 rounded-4 shadow-sm mb-4">
            <i class="bi bi-hourglass-split fs-2 text-warning"></i>
            <div>
                <h5 class="fw-bold mb-0 text-dark">Boleta en Espera de Firma Digital</h5>
                <p class="mb-0 text-muted small">El estudiante aún no ha firmado el documento a través del enlace seguro enviado a su correo.</p>
            </div>
        </div>
    @endif

    <div class="boleta-process-card p-4 p-md-5 mx-auto bg-white">
        <!-- Encabezado Institucional -->
        <div class="row border-bottom pb-4 mb-4 align-items-center">
            <div class="col-md-7 text-center text-md-start mb-4 mb-md-0">
                <img src="{{ asset('imgs/logo.png') }}" onerror="this.onerror=null; this.src='{{ asset('imgs/logo_unela_color.png') }}';" alt="Logo Institucional" style="max-height: 75px;" class="mb-2">
                <h5 class="fw-bold mb-1" style="font-size: 1.15rem; color: #1e293b;">{{ config('cliente.nombre_legal', config('cliente.nombre', 'CENTRO DE FORMACIÓN INTEGRAL CEFI')) }}</h5>
                <p class="mb-0 text-muted small">Cédula Jurídica: 3-002-066646 | Tel: 2211-1200</p>
            </div>
            <div class="col-md-5">
                <div class="p-3 text-center rounded-4 border" style="background-color: rgba(95, 178, 48, 0.05); border-color: rgba(95, 178, 48, 0.2) !important;">
                    <h5 class="fw-bold text-success mb-1">BOLETA DE MATRÍCULA</h5>
                    <div class="small text-muted">
                        <span><strong>No:</strong> <span class="text-danger fw-bold">{{ $boleta->numero_boleta }}</span></span> | 
                        <span><strong>Fecha:</strong> {{ $boleta->fecha_creacion ? $boleta->fecha_creacion->format('d/m/Y') : date('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Datos del Estudiante -->
        <div class="mb-4">
            <div class="boleta-section-header">Datos del Estudiante</div>
            <div class="row g-3 px-2">
                <div class="col-md-6">
                    <span class="text-muted small fw-bold d-block text-uppercase">Nombre Completo</span>
                    <div class="fw-bold border-bottom pb-1 text-dark">{{ $estudiante->nombre ?? '' }} {{ $estudiante->apellidos ?? '' }}</div>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small fw-bold d-block text-uppercase">Cédula / ID</span>
                    <div class="fw-bold border-bottom pb-1 text-dark">{{ $estudiante->cedula ?? 'N/A' }}</div>
                </div>
                <div class="col-12">
                    <span class="text-muted small fw-bold d-block text-uppercase">Carrera / Programa Académico</span>
                    <div class="fw-bold border-bottom pb-1 text-dark">{{ $cursos->pluck('nombre_programa')->unique()->implode(', ') ?: 'Programa Académico' }}</div>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small fw-bold d-block text-uppercase">Correo Electrónico</span>
                    <div class="fw-bold border-bottom pb-1 text-dark">{{ $estudiante->email ?? 'N/D' }}</div>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small fw-bold d-block text-uppercase">Período Lectivo</span>
                    <div class="fw-bold text-primary">{{ $boleta->periodo }}</div>
                </div>
            </div>
        </div>

        <!-- Detalle de Cargos -->
        <div class="mb-4">
            <div class="boleta-section-header">Detalle de Matrícula y Cursos</div>
            <div class="table-responsive">
                <table class="table table-boleta mb-0">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Código</th>
                            <th style="width: 55%;">Descripción de Cargo</th>
                            <th style="width: 30%;" class="text-end pe-4">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $max_ins = $cursos->max('costo_inscripcion_unica') ?: 8000;
                            $max_bib = $cursos->max('costo_biblioteca') ?: 5000;
                            $max_mat = $cursos->max('costo_matricula') ?: 35000;
                            $subtotal_calc = 0;
                        @endphp

                        @if ($boleta->cobrar_inscripcion)
                            @php $subtotal_calc += $max_ins; @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-primary">INS-01</td>
                                <td>INSCRIPCIÓN ÚNICA</td>
                                <td class="text-end pe-4 fw-bold">¢{{ number_format($max_ins, 2) }}</td>
                            </tr>
                        @endif

                        @if ($boleta->cobrar_biblioteca)
                            @php $subtotal_calc += $max_bib; @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-primary">BIB-01</td>
                                <td>USO DE BIBLIOTECA VIRTUAL</td>
                                <td class="text-end pe-4 fw-bold">¢{{ number_format($max_bib, 2) }}</td>
                            </tr>
                        @endif

                        @if ($boleta->cobrar_matricula)
                            @php $subtotal_calc += $max_mat; @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-primary">ADM-01</td>
                                <td>MATRÍCULA DEL PERÍODO</td>
                                <td class="text-end pe-4 fw-bold">¢{{ number_format($max_mat, 2) }}</td>
                            </tr>
                        @endif

                        @foreach ($cursos as $c)
                            @php 
                                $p = floatval($c->precio);
                                $subtotal_calc += $p;
                            @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-primary">{{ $c->codigo }}</td>
                                <td>{{ $c->materia }}</td>
                                <td class="text-end pe-4 fw-bold">¢{{ number_format($p, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light">
                        @if ($boleta->descuento > 0)
                            <tr>
                                <td colspan="2" class="text-end fw-bold py-2">SUBTOTAL:</td>
                                <td class="text-end fw-bold pe-4 py-2">¢{{ number_format($subtotal_calc, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-end text-success fw-bold py-2">DESCUENTO ESPECIAL:</td>
                                <td class="text-end text-success fw-bold pe-4 py-2">-¢{{ number_format($boleta->descuento, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="table-primary border-top border-primary border-2">
                            <td colspan="2" class="text-end fw-bold h5 py-3 mb-0">TOTAL MATRÍCULA Y CURSOS</td>
                            <td class="text-end fw-bold h5 text-primary pe-4 py-3 mb-0" id="display-total-final">¢{{ number_format($boleta->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Firma Digital del Estudiante -->
        <div class="mb-4">
            <div class="boleta-section-header">Firma Digital del Estudiante</div>
            @if (!empty($boleta->ruta_firma) && file_exists(public_path(ltrim($boleta->ruta_firma, '/'))))
                <div class="signature-verified-box text-center">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2 text-success fw-bold">
                        <i class="bi bi-shield-check fs-4"></i> Firma Digital Verificada
                    </div>
                    <img src="{{ asset(ltrim($boleta->ruta_firma, '/')) }}" alt="Firma del Estudiante" style="max-height: 90px; border-bottom: 1px solid #cbd5e0; padding-bottom: 5px;">
                    <div class="small text-muted mt-2">
                        <strong>{{ $estudiante->nombre ?? '' }} {{ $estudiante->apellidos ?? '' }}</strong><br>
                        Firmado el: {{ $boleta->fecha_firma ? $boleta->fecha_firma->format('d/m/Y h:i A') : date('d/m/Y') }}
                    </div>
                </div>
            @else
                <div class="alert alert-secondary text-center p-3 rounded-3">
                    <i class="bi bi-pen me-2"></i> Pendiente de firma manuscrita por parte del estudiante.
                </div>
            @endif
        </div>

        <!-- Opciones de Pago Inicial y Financiamiento -->
        @if ($boleta->estado === 'firmada')
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="p-3 rounded-4 border-2 border-dashed border-primary bg-primary bg-opacity-10 h-100">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-cash-stack me-2"></i>Pago Inicial Recibido Hoy</h6>
                        
                        <label class="small fw-bold text-muted mb-2 d-block">Monto de Pago Inicial (¢):</label>
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text bg-white border-2 border-end-0 fw-bold">¢</span>
                            <input type="number" id="oficializar-pago-inicial" class="form-control form-control-sm border-2 fw-bold" 
                                   value="{{ $boleta->pago_inicial > 0 ? $boleta->pago_inicial : 0 }}" min="0" max="{{ $boleta->total }}" step="500">
                        </div>

                        <div id="oficializar-detalles-pago-container" style="{{ $boleta->pago_inicial > 0 ? '' : 'display: none;' }}">
                            <label class="small fw-bold text-muted mb-2 d-block">Método de Pago:</label>
                            <select id="oficializar-metodo-pago" class="form-select form-select-sm fw-bold rounded-3 mb-3">
                                <option value="Sinpe Móvil" {{ $boleta->pago_inicial_metodo == 'Sinpe Móvil' ? 'selected' : '' }}>Sinpe Móvil</option>
                                <option value="Transferencia" {{ $boleta->pago_inicial_metodo == 'Transferencia' ? 'selected' : '' }}>Transferencia</option>
                                <option value="Efectivo" {{ ($boleta->pago_inicial_metodo == 'Efectivo' || empty($boleta->pago_inicial_metodo)) ? 'selected' : '' }}>Efectivo</option>
                                <option value="Tarjeta" {{ $boleta->pago_inicial_metodo == 'Tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                            </select>

                            <label class="small fw-bold text-muted mb-2 d-block">Número de Comprobante / Referencia:</label>
                            <input type="text" id="oficializar-referencia-pago" class="form-control form-control-sm border-2 rounded-3 mb-3" placeholder="Opcional" value="{{ $boleta->pago_inicial_referencia }}">
                        </div>

                        <div class="p-2 rounded-3 bg-white border">
                            <div class="d-flex justify-content-between small fw-bold">
                                <span class="text-muted">Saldo Restante a Financiar:</span>
                                <span class="text-danger" id="display-saldo-financiar">¢{{ number_format(max(0, $boleta->total - $boleta->pago_inicial), 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 rounded-4 border bg-light h-100">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-calendar3 me-2 text-primary"></i>Plan de Mensualidades ({{ $boleta->cuotas }} cuotas)</h6>
                        
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <span class="text-muted small">Monto por cuota:</span>
                            <span class="h6 fw-bold text-success mb-0" id="display-monto-cuota">¢{{ number_format(max(0, $boleta->total - $boleta->pago_inicial) / max(1, $boleta->cuotas), 2) }}</span>
                        </div>

                        <label class="small fw-bold text-muted mb-2 d-block">Fechas de Vencimiento de Cuotas:</label>
                        <div id="fechas-cuotas-inputs-container">
                            @for ($i = 1; $i <= max(1, $boleta->cuotas); $i++)
                                @php
                                    $default_date = $fechas_vencimiento_saved[$i - 1] ?? date('Y-m-d', strtotime('+' . ($i - 1) . ' month'));
                                @endphp
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-white border-2 border-end-0 fw-bold" style="font-size: 0.75rem;">Pago #{{ $i }}</span>
                                    <input type="date" class="form-control form-control-sm border-2 fw-bold fecha-cuota-val" data-index="{{ $i }}" value="{{ $default_date }}" required>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="row mt-4 g-3 justify-content-center">
                <div class="col-md-6">
                    <button type="button" id="btn-oficializar-boleta" class="btn btn-success btn-lg w-100 shadow fw-bold rounded-pill" style="background-color: #5fb230; border-color: #5fb230;">
                        <i class="bi bi-patch-check-fill me-2 fs-5"></i> Oficializar Boleta y Emitir PDF
                    </button>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('boletas.pdf', $boleta->id) }}" target="_blank" class="btn btn-outline-primary btn-lg w-100 rounded-pill fw-bold">
                        <i class="bi bi-eye me-1"></i> Previsualizar PDF
                    </a>
                </div>
            </div>
        @else
            <div class="text-center mt-4">
                <a href="{{ route('boletas.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Volver a la Bandeja
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const totalBoleta = {{ (float)$boleta->total }};
    const cuotas = {{ max(1, (int)$boleta->cuotas) }};
    const currencyFormatter = new Intl.NumberFormat('es-CR', { style: 'currency', currency: 'CRC' });

    $(document).ready(function() {
        $('#oficializar-pago-inicial').on('input change', function() {
            const pago = parseFloat($(this).val()) || 0;
            if (pago > 0) {
                $('#oficializar-detalles-pago-container').slideDown();
            } else {
                $('#oficializar-detalles-pago-container').slideUp();
            }

            const saldo = Math.max(0, totalBoleta - pago);
            $('#display-saldo-financiar').text(currencyFormatter.format(saldo));
            $('#display-monto-cuota').text(currencyFormatter.format(saldo / cuotas));
        });

        // Sincronización mensual de fechas
        $(document).on('change', '.fecha-cuota-val', function() {
            const idx = parseInt($(this).data('index'));
            const val = $(this).val();
            if (!val) return;

            const parts = val.split('-');
            if (parts.length === 3) {
                const baseDate = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                for (let i = idx + 1; i <= cuotas; i++) {
                    const d = new Date(baseDate);
                    d.setMonth(d.getMonth() + (i - idx));
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    $(`.fecha-cuota-val[data-index="${i}"]`).val(`${y}-${m}-${day}`);
                }
            }
        });

        // Acción de Oficializar
        $('#btn-oficializar-boleta').click(function() {
            const btn = $(this);
            const fechas = [];
            $('.fecha-cuota-val').each(function() {
                fechas.push($(this).val());
            });

            Swal.fire({
                title: '¿Oficializar Boleta?',
                text: 'Se confirmará la matrícula y se generará el documento PDF oficial.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#5fb230',
                confirmButtonText: 'Sí, Oficializar',
                cancelButtonText: 'Cancelar'
            }).then((res) => {
                if (res.isConfirmed) {
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Oficializando...');

                    $.ajax({
                        url: '{{ route('boletas.oficializar_firmada', $boleta->id) }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            pago_inicial: $('#oficializar-pago-inicial').val(),
                            pago_inicial_metodo: $('#oficializar-metodo-pago').val(),
                            pago_inicial_referencia: $('#oficializar-referencia-pago').val(),
                            fechas_vencimiento: fechas
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: '¡Boleta Oficializada!',
                                    text: 'La boleta ahora es un documento oficial emitido.',
                                    icon: 'success',
                                    confirmButtonColor: '#5fb230',
                                    confirmButtonText: '<i class="bi bi-file-earmark-pdf me-1"></i> Ver PDF'
                                }).then(() => {
                                    if (response.pdf_url) {
                                        window.open(response.pdf_url, '_blank');
                                    }
                                    window.location.href = '{{ route('boletas.index') }}';
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                                btn.prop('disabled', false).html('<i class="bi bi-patch-check-fill me-2 fs-5"></i> Oficializar Boleta y Emitir PDF');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Ocurrió un error al oficializar la boleta.', 'error');
                            btn.prop('disabled', false).html('<i class="bi bi-patch-check-fill me-2 fs-5"></i> Oficializar Boleta y Emitir PDF');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
