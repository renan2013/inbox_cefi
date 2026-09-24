@extends('layouts.app')

@section('title', 'Inbox BPM - Estado de Cuenta del Estudiante')

@section('styles')
    <style>
        .page-header {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);
            border: 1px solid var(--border-dark, #334155);
            border-radius: 1.5rem;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .search-section {
            background-color: var(--card-dark, #1e293b);
            border: 1px solid var(--border-dark, #334155);
            border-radius: 1.25rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-control-custom {
            background-color: rgba(15, 23, 42, 0.6);
            border: 1.5px solid var(--border-dark, #334155);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.75rem 1.2rem;
            transition: all 0.3s;
        }

        .form-control-custom:focus {
            background-color: rgba(15, 23, 42, 0.85);
            border-color: var(--primary, #5fb230);
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.2);
            color: #ffffff;
            outline: none;
        }

        .result-item {
            background-color: rgba(30, 41, 59, 0.95);
            border: 1px solid var(--border-dark, #334155);
            transition: all 0.2s;
            cursor: pointer;
            color: #e2e8f0;
        }

        .result-item:hover {
            background-color: var(--primary, #5fb230);
            color: white;
            border-color: var(--primary, #5fb230);
        }

        .student-summary-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
            border: 1px solid var(--border-dark, #334155);
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .nav-tabs-custom {
            border-bottom: 2px solid var(--border-dark, #334155);
        }

        .nav-tabs-custom .nav-link {
            color: var(--text-muted, #94a3b8);
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #f8fafc;
        }

        .nav-tabs-custom .nav-link.active {
            color: var(--primary, #5fb230);
            background-color: transparent;
            border-bottom: 3px solid var(--primary, #5fb230);
        }

        .status-badge {
            font-weight: 700;
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            white-space: nowrap;
            display: inline-block;
            text-transform: uppercase;
        }

        .status-pendiente {
            background-color: rgba(245, 158, 11, 0.15) !important;
            color: #f59e0b !important;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .status-pagada {
            background-color: rgba(16, 185, 129, 0.15) !important;
            color: #10b981 !important;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-parcial {
            background-color: rgba(59, 130, 246, 0.15) !important;
            color: #3b82f6 !important;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .status-anulada {
            background-color: rgba(239, 68, 68, 0.15) !important;
            color: #ef4444 !important;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .btn-action-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 50rem;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50 small fw-semibold"><i class="bi bi-house-door me-1"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('boletas.index') }}" class="text-decoration-none text-white-50 small fw-semibold">Boletas</a></li>
                        <li class="breadcrumb-item active text-success small fw-bold" aria-current="page">Estado de Cuenta</li>
                    </ol>
                </nav>
                <h1 class="display-6 fw-bold mb-1 text-white"><i class="bi bi-wallet2 text-success me-2"></i>Estado de Cuenta del Estudiante</h1>
                <p class="text-white-50 mb-0">Ficha financiera consolidada, historial de pagos, boletas de matrícula y cálculo de saldo al día.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('boletas.morosidad') }}" class="btn btn-outline-danger rounded-pill px-4 fw-bold">
                    <i class="bi bi-exclamation-triangle me-1"></i> Ver Morosidad
                </a>
                <a href="{{ route('boletas.generar') }}" class="btn btn-success rounded-pill px-4 fw-bold" style="background-color: var(--primary); border: none;">
                    <i class="bi bi-plus-circle me-1"></i> Generar Boleta
                </a>
            </div>
        </div>

        <!-- Search Box -->
        <div class="search-section shadow">
            <h4 class="fw-bold text-white mb-3"><i class="bi bi-search me-2 text-success"></i>Buscar Estudiante</h4>
            <div class="position-relative">
                <input type="text" id="buscador_estudiante" class="form-control form-control-custom w-100" 
                    placeholder="Escriba el nombre, apellidos, correo o cédula del estudiante..." autocomplete="off">
                <div id="resultados_busqueda" class="list-group mt-2 position-absolute w-100 shadow" 
                    style="z-index: 1000; max-height: 280px; overflow-y: auto; display: none;"></div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loading_estado_cuenta" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Cargando datos...</span>
            </div>
            <p class="text-white-50 mt-3">Calculando saldos y cuotas en tiempo real...</p>
        </div>

        <!-- Main Account Statement Container -->
        <div id="contenedor_estado_cuenta" style="display: none;">
            
            <!-- Student Header Banner -->
            <div class="student-summary-card d-flex flex-wrap justify-content-between align-items-center gap-3 shadow">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-20 text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.8rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <h2 class="fw-bold text-white mb-1" id="nombre_estudiante"></h2>
                        <div class="d-flex flex-wrap gap-3 text-white-50 small">
                            <span id="email_estudiante"><i class="bi bi-envelope me-1"></i></span>
                            <span id="cedula_estudiante"><i class="bi bi-card-heading me-1"></i></span>
                            <span id="telefono_estudiante_badge"></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center flex-wrap gap-3">
                    <div class="px-4 py-2 rounded-4 bg-danger bg-opacity-10 border border-danger border-opacity-30 text-center">
                        <small class="text-danger fw-bold d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Deuda Total Pendiente</small>
                        <strong class="text-danger fs-3" id="deuda_total">₡0.00</strong>
                    </div>
                    <button class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold" onclick="recargarEstadoCuentaActual()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
                    </button>
                    <button class="btn btn-success rounded-pill px-4 py-2 fw-bold" onclick="irAGenerarBoleta()" style="background-color: var(--primary); border: none;">
                        <i class="bi bi-receipt me-1"></i> Nueva Boleta
                    </button>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs nav-tabs-custom" id="finanzasTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="boletas-tab" data-bs-toggle="tab" data-bs-target="#boletas" type="button" role="tab">
                        <i class="bi bi-receipt me-1"></i> Boletas de Matrícula
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="cuotas-tab" data-bs-toggle="tab" data-bs-target="#cuotas" type="button" role="tab">
                        <i class="bi bi-calendar-check me-1"></i> Plan de Cuotas
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="pagos-tab" data-bs-toggle="tab" data-bs-target="#pagos" type="button" role="tab">
                        <i class="bi bi-cash-stack me-1"></i> Historial de Abonos
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="arreglos-tab" data-bs-toggle="tab" data-bs-target="#arreglos" type="button" role="tab">
                        <i class="bi bi-journal-text me-1"></i> Arreglos de Pago
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content bg-dark border border-secondary border-top-0 rounded-bottom-4 p-4 shadow mb-5" id="finanzasTabsContent">
                
                <!-- Boletas Tab -->
                <div class="tab-pane fade show active" id="boletas" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-white-50 small text-uppercase">
                                    <th>N° Boleta</th>
                                    <th>Fecha Emisión</th>
                                    <th>Período</th>
                                    <th>Total Original</th>
                                    <th>Monto Pagado</th>
                                    <th>Saldo Pendiente</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla_boletas"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Cuotas Tab -->
                <div class="tab-pane fade" id="cuotas" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-white-50 small text-uppercase">
                                    <th>Cuota</th>
                                    <th>Boleta Asociada</th>
                                    <th>Monto Capital</th>
                                    <th>Interés Mora</th>
                                    <th>Total Letra</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tabla_cuotas"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagos Tab -->
                <div class="tab-pane fade" id="pagos" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-white-50 small text-uppercase">
                                    <th>Fecha Abono</th>
                                    <th>Boleta</th>
                                    <th>Monto Pagado</th>
                                    <th>Método</th>
                                    <th>Referencia / Detalle</th>
                                    <th>Comprobante</th>
                                </tr>
                            </thead>
                            <tbody id="tabla_pagos"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Arreglos Tab -->
                <div class="tab-pane fade" id="arreglos" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-white-50 small text-uppercase">
                                    <th>ID Arreglo</th>
                                    <th>Fecha Creación</th>
                                    <th>Monto Acordado</th>
                                    <th>Estado</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla_arreglos"></tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let currentStudentId = null;
        let boletasCache = {};

        $(document).ready(function() {
            // 1. Buscador de Estudiante con Autocompletado
            $('#buscador_estudiante').on('input', function() {
                let term = $(this).val().trim();
                if (term.length < 2) {
                    $('#resultados_busqueda').empty().hide();
                    return;
                }

                $.getJSON("{{ route('boletas.buscar_estudiante_ajax') }}", { term: term }, function(data) {
                    let container = $('#resultados_busqueda').empty().show();
                    if (data.length === 0) {
                        container.append('<div class="list-group-item bg-dark text-white-50 border-secondary">No se encontraron resultados</div>');
                        return;
                    }
                    data.forEach(function(item) {
                        let btn = $(`<button type="button" class="list-group-item list-group-item-action result-item border-secondary p-3"></button>`);
                        btn.html(`<strong>${item.nombre} ${item.apellidos ?? ''}</strong><br><small class="text-white-50">${item.email}</small>`);
                        btn.on('click', function() {
                            cargarEstadoCuenta(item.id);
                            container.hide();
                            $('#buscador_estudiante').val('');
                        });
                        container.append(btn);
                    });
                });
            });

            // Cerrar buscador al hacer clic fuera
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#buscador_estudiante, #resultados_busqueda').length) {
                    $('#resultados_busqueda').hide();
                }
            });

            // 2. Autocarga desde URL (estudiante_id o id_estudiante)
            const urlParams = new URLSearchParams(window.location.search);
            const paramStudentId = urlParams.get('estudiante_id') || urlParams.get('id_estudiante');
            if (paramStudentId) {
                cargarEstadoCuenta(paramStudentId);
            }
        });

        function formatCurrency(val) {
            return '₡' + parseFloat(val || 0).toLocaleString('es-CR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function recargarEstadoCuentaActual() {
            if (currentStudentId) {
                cargarEstadoCuenta(currentStudentId);
            }
        }

        function cargarEstadoCuenta(id) {
            currentStudentId = id;
            $('#loading_estado_cuenta').show();
            $('#contenedor_estado_cuenta').hide();

            // Actualizar URL sin recargar para persistencia
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('estudiante_id', id);
            window.history.replaceState({}, '', newUrl);

            $.getJSON(`/boletas/estado-cuenta/${id}/ajax`, function(data) {
                $('#loading_estado_cuenta').hide();

                // Ficha del estudiante
                $('#nombre_estudiante').text(data.estudiante.nombre);
                $('#email_estudiante').html(`<i class="bi bi-envelope me-1"></i>${data.estudiante.email}`);
                
                if (data.estudiante.cedula) {
                    $('#cedula_estudiante').html(`<i class="bi bi-card-heading me-1"></i>${data.estudiante.cedula}`).show();
                } else {
                    $('#cedula_estudiante').hide();
                }

                if (data.estudiante.telefono) {
                    const waLink = `https://api.whatsapp.com/send?phone=${data.estudiante.telefono}`;
                    $('#telefono_estudiante_badge').html(
                        `<a href="${waLink}" target="_blank" class="text-success text-decoration-none fw-semibold">
                            <i class="bi bi-whatsapp me-1"></i>+${data.estudiante.telefono}
                         </a>`
                    ).show();
                } else {
                    $('#telefono_estudiante_badge').html('<span class="text-white-50"><i class="bi bi-telephone-x me-1"></i>Sin Teléfono</span>').show();
                }

                $('#deuda_total').text(formatCurrency(data.estudiante.deuda_total));

                // 1. Render Boletas
                boletasCache = {};
                let tBoletas = $('#tabla_boletas').empty();
                if (data.boletas.length === 0) {
                    tBoletas.append('<tr><td colspan="8" class="text-center py-4 text-white-50">No hay boletas generadas para este estudiante.</td></tr>');
                } else {
                    data.boletas.forEach(function(b) {
                        boletasCache[b.id] = b;
                        let badgeClass = 'status-pendiente';
                        if (b.estado === 'pagada') badgeClass = 'status-pagada';
                        else if (b.estado === 'pago_parcial') badgeClass = 'status-parcial';
                        else if (b.estado === 'anulada') badgeClass = 'status-anulada';

                        let viewPdfBtn = `<a href="${b.pdf_url}" target="_blank" class="btn btn-outline-light btn-action-sm me-1" title="Ver Boleta Oficial en PDF">
                                            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>PDF
                                          </a>`;

                        let payBtn = '';
                        if (b.saldo_pendiente > 0 && b.estado !== 'anulada') {
                            payBtn = `<button type="button" class="btn btn-success btn-action-sm me-1" onclick="abrirModalPago(${b.id})" title="Registrar abono a esta boleta">
                                        <i class="bi bi-cash-coin me-1"></i>Pagar
                                      </button>`;
                        }

                        let anularBtn = '';
                        if (b.estado !== 'anulada') {
                            anularBtn = `<button type="button" class="btn btn-outline-danger btn-action-sm" onclick="anularBoleta(${b.id}, '${b.numero_boleta}')" title="Anular boleta">
                                            <i class="bi bi-x-circle"></i>
                                         </button>`;
                        }

                        tBoletas.append(`
                            <tr>
                                <td><strong class="text-white">${b.numero_boleta}</strong></td>
                                <td>${b.fecha_creacion}</td>
                                <td><span class="badge bg-secondary bg-opacity-50">${b.periodo ?? 'N/D'}</span></td>
                                <td>${formatCurrency(b.total)}</td>
                                <td class="text-success">${formatCurrency(b.monto_pagado)}</td>
                                <td class="fw-bold ${b.saldo_pendiente > 0 ? 'text-danger' : 'text-white'}">
                                    ${formatCurrency(b.saldo_pendiente)}
                                    ${b.interes_acumulado > 0 ? `<br><small class="text-warning" style="font-size:0.75rem;">(mora: ${formatCurrency(b.interes_acumulado)})</small>` : ''}
                                </td>
                                <td><span class="status-badge ${badgeClass}">${b.estado}</span></td>
                                <td class="text-end text-nowrap">${viewPdfBtn}${payBtn}${anularBtn}</td>
                            </tr>
                        `);
                    });
                }

                // 2. Render Cuotas
                let tCuotas = $('#tabla_cuotas').empty();
                if (data.letras.length === 0) {
                    tCuotas.append('<tr><td colspan="7" class="text-center py-4 text-white-50">No hay cuotas programadas.</td></tr>');
                } else {
                    data.letras.forEach(function(l) {
                        let badge = l.estado === 'pendiente' ? 'status-pendiente' : 'status-pagada';
                        let capital = parseFloat(l.monto_cuota);
                        let mora = parseFloat(l.interes_acumulado);
                        let total = capital + mora;
                        tCuotas.append(`
                            <tr>
                                <td><strong>Cuota #${l.numero_cuota}</strong></td>
                                <td>${l.boleta_numero}</td>
                                <td>${formatCurrency(capital)}</td>
                                <td class="text-warning">${mora > 0 ? '+' + formatCurrency(mora) : '₡0.00'}</td>
                                <td class="fw-bold text-white">${formatCurrency(total)}</td>
                                <td>${l.fecha_vencimiento}</td>
                                <td><span class="status-badge ${badge}">${l.estado}</span></td>
                            </tr>
                        `);
                    });
                }

                // 3. Render Pagos
                let tPagos = $('#tabla_pagos').empty();
                if (data.pagos.length === 0) {
                    tPagos.append('<tr><td colspan="6" class="text-center py-4 text-white-50">No hay registros de pagos recibidos.</td></tr>');
                } else {
                    data.pagos.forEach(function(p) {
                        let viewReceiptBtn = p.ruta_comprobante ? `<a href="/${p.ruta_comprobante}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3"><i class="bi bi-file-image me-1"></i>Comprobante</a>` : '<span class="text-white-50 small">N/D</span>';
                        tPagos.append(`
                            <tr>
                                <td>${p.fecha_pago}</td>
                                <td>Boleta #${p.boleta_id}</td>
                                <td class="text-success fw-bold">${formatCurrency(p.monto)}</td>
                                <td><span class="badge bg-secondary bg-opacity-25">${p.metodo_pago ?? 'N/D'}</span></td>
                                <td class="small text-white-50">${p.referencia ?? 'Sin notas'}</td>
                                <td>${viewReceiptBtn}</td>
                            </tr>
                        `);
                    });
                }

                // 4. Render Arreglos
                let tArreglos = $('#tabla_arreglos').empty();
                if (data.arreglos.length === 0) {
                    tArreglos.append('<tr><td colspan="5" class="text-center py-4 text-white-50">No hay arreglos de pago registrados.</td></tr>');
                } else {
                    data.arreglos.forEach(function(a) {
                        tArreglos.append(`
                            <tr>
                                <td>#${a.id}</td>
                                <td>${a.fecha_creacion ? a.fecha_creacion.split('T')[0] : 'N/D'}</td>
                                <td class="fw-bold text-success">${formatCurrency(a.monto_total_acordado)}</td>
                                <td><span class="status-badge status-pagada">${a.estado}</span></td>
                                <td class="small text-white-50">${a.observaciones ?? 'Sin notas'}</td>
                            </tr>
                        `);
                    });
                }

                $('#contenedor_estado_cuenta').fadeIn();
            }).fail(function() {
                $('#loading_estado_cuenta').hide();
                Swal.fire('Error', 'No se pudo cargar la información financiera del estudiante.', 'error');
            });
        }

        // Modal para Registrar Pago
        function abrirModalPago(boletaId) {
            const b = boletasCache[boletaId];
            if (!b) return;

            const saldo = parseFloat(b.saldo_pendiente);
            const mora = parseFloat(b.interes_acumulado || 0);

            Swal.fire({
                title: `<span class="text-success"><i class="bi bi-cash-coin me-2"></i>Registrar Pago</span>`,
                html: `
                    <div class="text-start p-2" style="font-size: 0.9rem;">
                        <div class="p-3 bg-dark bg-opacity-50 border border-secondary rounded-3 mb-3">
                            <div><strong>Boleta:</strong> ${b.numero_boleta}</div>
                            <div><strong>Saldo Pendiente:</strong> <span class="text-danger fw-bold">${formatCurrency(saldo)}</span></div>
                            ${mora > 0 ? `<div><strong>Mora acumulada incluida:</strong> <span class="text-warning fw-semibold">${formatCurrency(mora)}</span></div>` : ''}
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-white-50 small">Monto a Abonar (₡):</label>
                            <input type="number" step="0.01" id="swal-monto" class="form-control form-control-custom" value="${saldo.toFixed(2)}" max="${saldo}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-white-50 small">Método de Pago:</label>
                            <select id="swal-metodo" class="form-select form-select-custom">
                                <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                                <option value="SINPE Móvil">SINPE Móvil</option>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta de Débito/Crédito">Tarjeta de Débito/Crédito</option>
                                <option value="Depósito Bancario">Depósito Bancario</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-white-50 small">N° Referencia o Comprobante:</label>
                            <input type="text" id="swal-referencia" class="form-control form-control-custom" placeholder="Ej: #123456 SINPE Móvil">
                        </div>

                        ${mora > 0 ? `
                        <div class="form-check form-switch mt-3 p-2 bg-warning bg-opacity-10 rounded border border-warning border-opacity-25">
                            <input class="form-check-input ms-0 me-2" type="checkbox" id="swal-condonar">
                            <label class="form-check-label fw-bold text-warning small" for="swal-condonar">
                                Condonar recargos por mora (${formatCurrency(mora)})
                            </label>
                        </div>` : ''}
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: '#5fb230',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Procesar Abono',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const monto = parseFloat(document.getElementById('swal-monto').value);
                    const metodo = document.getElementById('swal-metodo').value;
                    const referencia = document.getElementById('swal-referencia').value.trim();
                    const condonarEl = document.getElementById('swal-condonar');
                    const condonar = condonarEl && condonarEl.checked;

                    if (!monto || monto <= 0) {
                        Swal.showValidationMessage('Por favor ingrese un monto válido mayor a 0.');
                        return false;
                    }

                    if (monto > saldo) {
                        Swal.showValidationMessage(`El monto no puede superar el saldo pendiente (${formatCurrency(saldo)}).`);
                        return false;
                    }

                    return { boletaId, monto, metodo, referencia, condonar };
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando pago...',
                        text: 'Actualizando boleta y recalculando intereses',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    try {
                        const res = await fetch(`/boletas/${result.value.boletaId}/pago`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                monto: result.value.monto,
                                metodo: result.value.metodo,
                                referencia: result.value.referencia,
                                condonar_interes: result.value.condonar
                            })
                        });

                        const data = await res.json();
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Pago Registrado!',
                                text: data.message,
                                confirmButtonColor: '#5fb230'
                            }).then(() => {
                                recargarEstadoCuentaActual();
                            });
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo registrar el pago.', 'error');
                        }
                    } catch (err) {
                        Swal.fire('Error de Red', err.message, 'error');
                    }
                }
            });
        }

        // Anular Boleta
        function anularBoleta(boletaId, numeroBoleta) {
            Swal.fire({
                title: '¿Anular Boleta?',
                html: `¿Está seguro de que desea anular la boleta <strong>${numeroBoleta}</strong>?<br><br>
                       <span class="text-danger small"><i class="bi bi-exclamation-octagon me-1"></i> Esta acción eliminará sus cuotas y dejará la boleta en estado anulada.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, Anular Boleta',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Anulando...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    try {
                        const res = await fetch(`/boletas/${boletaId}/anular`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Boleta Anulada',
                                text: data.message,
                                confirmButtonColor: '#5fb230'
                            }).then(() => {
                                recargarEstadoCuentaActual();
                            });
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo anular la boleta.', 'error');
                        }
                    } catch (err) {
                        Swal.fire('Error de Red', err.message, 'error');
                    }
                }
            });
        }

        function irAGenerarBoleta() {
            if (currentStudentId) {
                window.location.href = `/boletas/generar?id_estudiante=${currentStudentId}`;
            } else {
                window.location.href = '/boletas/generar';
            }
        }
    </script>
@endsection
