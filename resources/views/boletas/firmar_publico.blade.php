<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Digital de Boleta de Matrícula - {{ config('cliente.nombre', 'CEFI') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .boleta-card {
            max-width: 900px;
            background: #ffffff;
            border-radius: 1.5rem;
            box-shadow: 0 20px 50px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
            position: relative;
        }
        .boleta-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: #5fb230;
        }
        .boleta-section-header {
            background-color: #1e293b;
            color: #ffffff;
            padding: 0.6rem 1.25rem;
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
        }
        .signature-area {
            height: 150px;
            background-color: #ffffff;
            border: 2px dashed #cbd5e0;
            border-radius: 1rem;
            position: relative;
            touch-action: none;
        }
    </style>
</head>
<body class="py-4 py-md-5">

<div class="container">
    <div class="boleta-card p-4 p-md-5 mx-auto">
        <!-- Encabezado Institucional -->
        <div class="row border-bottom pb-4 mb-4 align-items-center">
            <div class="col-md-7 text-center text-md-start mb-4 mb-md-0">
                <img src="{{ \App\Services\ClienteService::logoUrl() }}" alt="Logo CEFI" style="max-height: 75px;" class="mb-2">
                <h5 class="fw-bold mb-1" style="font-size: 1.15rem; color: #1e293b;">{{ config('cliente.nombre_legal', config('cliente.nombre', 'CENTRO DE FORMACIÓN INTEGRAL CEFI')) }}</h5>
                <p class="mb-0 text-muted small">Cédula Jurídica: 3-002-066646 | Tel: 2211-1200</p>
                <p class="mb-0 text-muted small">Portal Oficial de Firma Digital de Estudiantes</p>
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

        <!-- Alerta Informativa -->
        <div class="alert alert-info rounded-3 mb-4 d-flex align-items-center gap-3">
            <i class="bi bi-shield-check fs-2 text-primary"></i>
            <div>
                <strong>Aceptación Digital de Términos de Matrícula</strong><br>
                <span class="small">Estimado/a estudiante, por favor verifique los datos de las materias y el calendario de pagos antes de estampar su firma digital al final de esta página.</span>
            </div>
        </div>

        <!-- Datos del Estudiante -->
        <div class="mb-4">
            <div class="boleta-section-header">Datos del Estudiante</div>
            <div class="row g-3 px-2">
                <div class="col-md-6">
                    <span class="text-muted small fw-bold d-block text-uppercase">Nombre Completo</span>
                    <div class="fw-bold border-bottom pb-1">{{ $estudiante->nombre ?? '' }} {{ $estudiante->apellidos ?? '' }}</div>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small fw-bold d-block text-uppercase">Cédula / ID</span>
                    <div class="fw-bold border-bottom pb-1">{{ $estudiante->cedula ?? 'N/A' }}</div>
                </div>
                <div class="col-12">
                    <span class="text-muted small fw-bold d-block text-uppercase">Carrera / Programa Académico</span>
                    <div class="fw-bold border-bottom pb-1">{{ $cursos->pluck('nombre_programa')->unique()->implode(', ') ?: 'Programa Académico' }}</div>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small fw-bold d-block text-uppercase">Correo Electrónico</span>
                    <div class="fw-bold border-bottom pb-1">{{ $estudiante->email ?? 'N/D' }}</div>
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
                                <td>INSCRIPCIÓN ÚNICA (NUEVO INGRESO)</td>
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
                            <td class="text-end fw-bold h5 text-primary pe-4 py-3 mb-0">¢{{ number_format($boleta->total, 2) }}</td>
                        </tr>
                        @php
                            $fechas_saved = json_decode($boleta->fechas_vencimiento_json ?? '[]', true) ?: [];
                        @endphp
                        @if (!empty($fechas_saved))
                            @php $monto_c = $boleta->total / max(1, count($fechas_saved)); @endphp
                            <tr class="table-light">
                                <td colspan="3" class="p-3 text-start">
                                    <div class="fw-bold text-dark mb-2"><i class="bi bi-calendar3 me-1 text-primary"></i> Calendario de Pagos / Letras de Vencimiento:</div>
                                    <div class="row g-2">
                                        @foreach ($fechas_saved as $idx => $f_venc)
                                            <div class="col-sm-6 col-md-3">
                                                <div class="p-2 border rounded bg-white text-center shadow-sm">
                                                    <span class="small text-muted d-block fw-bold" style="font-size: 0.75rem;">Pago #{{ $idx + 1 }}</span>
                                                    <span class="fw-bold text-primary d-block" style="font-size: 0.95rem;">¢{{ number_format($monto_c, 2) }}</span>
                                                    <span class="small text-muted d-block" style="font-size: 0.75rem;">Vence: {{ date('d/m/Y', strtotime($f_venc)) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Términos y Firma Manuscrita -->
        <div class="row align-items-end g-4 mt-2">
            <div class="col-md-6 text-start">
                <div class="p-3 bg-light rounded-4 small text-muted border h-100">
                    <p class="mb-1 fw-bold text-dark"><i class="bi bi-file-text me-1 text-primary"></i> TÉRMINOS Y COMPROMISO ACADÉMICO:</p>
                    <ul class="mb-0 ps-3">
                        <li>Este documento formaliza la solicitud y matrícula de asignaturas para el período lectivo indicado.</li>
                        <li>El estudiante asume el compromiso del pago de los aranceles en las fechas estipuladas.</li>
                        <li>Al estampar la firma manuscrita abajo, el estudiante declara su conformidad con el plan de estudios y reglamentos vigentes.</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 text-center">
                <label class="small fw-bold text-muted mb-2 d-block text-uppercase text-start">
                    <i class="bi bi-pen-fill me-1 text-success"></i> Estampe su Firma Manuscrita Aquí <span class="text-danger">*</span>
                </label>
                <div class="signature-area w-100 shadow-sm" style="overflow: hidden;">
                    <canvas id="signature-canvas" style="width: 100%; height: 150px; cursor: crosshair; touch-action: none; background-color: #ffffff;"></canvas>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted" style="font-size: 0.75rem;">Firme usando su dedo en pantalla táctil o el cursor.</small>
                    <button type="button" id="clear-signature-btn" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1">
                        <i class="bi bi-eraser me-1"></i> Borrar Firma
                    </button>
                </div>
            </div>
        </div>

        <!-- Botón de Confirmación -->
        <div class="mt-5 text-center">
            <button type="button" id="btn-firmar-boleta" class="btn btn-success btn-lg rounded-pill px-5 py-3 shadow border-0 fw-bold transition-all" style="background-color: #5fb230; font-size: 1.1rem;">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i> ACEPTAR Y FIRMAR BOLETA DE MATRÍCULA
            </button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const canvas = document.getElementById('signature-canvas');
    const ctx = canvas.getContext('2d');
    let drawing = false;

    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = 150;
        
        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }

    window.addEventListener('resize', resizeCanvas);
    setTimeout(resizeCanvas, 200);

    function getPointerPos(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }

    function startDrawing(e) {
        drawing = true;
        const pos = getPointerPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        e.preventDefault();
    }

    function draw(e) {
        if (!drawing) return;
        const pos = getPointerPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        e.preventDefault();
    }

    function stopDrawing() {
        drawing = false;
    }

    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    window.addEventListener('mouseup', stopDrawing);

    canvas.addEventListener('touchstart', startDrawing);
    canvas.addEventListener('touchmove', draw);
    window.addEventListener('touchend', stopDrawing);

    $('#clear-signature-btn').click(function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    });

    function isCanvasEmpty() {
        const blank = document.createElement('canvas');
        blank.width = canvas.width;
        blank.height = canvas.height;
        return canvas.toDataURL() === blank.toDataURL();
    }

    $('#btn-firmar-boleta').click(function() {
        if (isCanvasEmpty()) {
            Swal.fire('Atención', 'Por favor estampe su firma manuscrita en el recuadro antes de continuar.', 'warning');
            return;
        }

        const btn = $(this);
        Swal.fire({
            title: '¿Confirmar Firma Digital?',
            text: 'Su firma digital será registrada y el documento enviado a la administración para oficializar la matrícula.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5fb230',
            confirmButtonText: 'Sí, Confirmar Firma',
            cancelButtonText: 'Cancelar'
        }).then((res) => {
            if (res.isConfirmed) {
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Registrando firma digital...');

                $.ajax({
                    url: '{{ route('boletas.guardar_firma_publica', $boleta->token_firma) }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        signature: canvas.toDataURL()
                    },
                    dataType: 'json',
                    success: function(resp) {
                        if (resp.success) {
                            Swal.fire({
                                title: '¡Firma Registrada con Éxito!',
                                text: resp.message,
                                icon: 'success',
                                confirmButtonColor: '#5fb230'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', resp.message, 'error');
                            btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-2 fs-5"></i> ACEPTAR Y FIRMAR BOLETA DE MATRÍCULA');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Ocurrió un error al enviar la firma digital.', 'error');
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-2 fs-5"></i> ACEPTAR Y FIRMAR BOLETA DE MATRÍCULA');
                    }
                });
            }
        });
    });
</script>
</body>
</html>
