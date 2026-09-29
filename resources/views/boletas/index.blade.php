@extends('layouts.app')

@section('title', 'Inbox BPM - Historial de Boletas')

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

        .filter-section {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.25rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .table-container {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .table-custom {
            margin-bottom: 0;
            background-color: transparent !important;
        }

        .table-custom thead th {
            background-color: rgba(255, 255, 255, 0.02) !important;
            color: var(--text-muted) !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid var(--border-dark);
        }

        .table-custom tbody tr {
            border-bottom: 1px solid var(--border-dark);
            transition: all 0.2s;
        }

        .table-custom tbody tr:last-child {
            border-bottom: none;
        }

        .table-custom tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }

        .table-custom td {
            background-color: transparent !important;
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            color: #e2e8f0 !important;
        }

        .form-control-custom {
            background-color: rgba(15, 23, 42, 0.5);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.6rem 1rem;
            transition: all 0.3s;
        }

        .form-control-custom:focus {
            background-color: rgba(15, 23, 42, 0.7);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15);
            color: #f8fafc;
            outline: none;
        }

        .btn-search {
            background-color: var(--primary);
            border: none;
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-search:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(95, 178, 48, 0.25);
        }

        .btn-clear {
            background-color: transparent;
            border: 2px solid var(--border-dark);
            color: var(--text-muted);
            padding: 0.6rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-clear:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #f8fafc;
            border-color: #f8fafc;
        }

        /* Pagination design override */
        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            background-color: rgba(30, 41, 59, 0.5);
            border-color: var(--border-dark);
            color: var(--text-muted);
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }

        .page-link:hover {
            background-color: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .status-badge {
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            white-space: nowrap;
            display: inline-block;
        }

        .status-pendiente {
            background-color: rgba(245, 158, 11, 0.15) !important;
            color: #f59e0b !important;
            border-color: rgba(245, 158, 11, 0.3);
        }

        .status-pagada {
            background-color: rgba(16, 185, 129, 0.15) !important;
            color: #10b981 !important;
            border-color: rgba(16, 185, 129, 0.3);
        }

        .status-parcial {
            background-color: rgba(59, 130, 246, 0.15) !important;
            color: #3b82f6 !important;
            border-color: rgba(59, 130, 246, 0.3);
        }

        .status-anulada {
            background-color: rgba(239, 68, 68, 0.15) !important;
            color: #ef4444 !important;
            border-color: rgba(239, 68, 68, 0.3);
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Historial de Boletas</h1>
                <p class="text-white-50 mb-0">Visualiza, consulta y descarga los comprobantes y facturas de matrícula de los estudiantes.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" id="btn-vaciar-pruebas" class="btn btn-outline-danger fw-semibold">
                    <i class="bi bi-trash3 me-1"></i> Vaciar Boletas de Prueba
                </button>
                <a href="{{ route('boletas.generar') }}" class="btn btn-search">
                    <i class="bi bi-plus-circle me-1"></i> Generar Nueva Boleta
                </a>
            </div>
        </div>

        <!-- Alerta destacada de boletas firmadas esperando oficialización -->
        @if (isset($count_firmadas) && $count_firmadas > 0)
            <div class="alert alert-success d-flex flex-wrap align-items-center justify-content-between p-3 rounded-4 shadow-sm mb-4 border-2 border-success">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-pen-fill fs-2 text-success"></i>
                    <div>
                        <h5 class="fw-bold mb-0 text-success">Tiene {{ $count_firmadas }} boleta(s) firmada(s) por estudiantes</h5>
                        <p class="mb-0 text-muted small">Los estudiantes completaron su firma digital y están a la espera de que la administración oficialice el trámite.</p>
                    </div>
                </div>
                <a href="{{ route('boletas.index', ['estado' => 'firmada']) }}" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                    <i class="bi bi-filter me-1"></i> Ver Firmadas ({{ $count_firmadas }})
                </a>
            </div>
        @endif

        <!-- Filter Form -->
        <div class="filter-section">
            <form action="{{ route('boletas.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label text-white-50 fw-semibold mb-2">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 border-2" style="border-color: var(--border-dark); border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem; color: var(--text-muted);">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" id="search" class="form-control form-control-custom border-start-0 ps-0" placeholder="Número de boleta, estudiante..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="estado" class="form-label text-white-50 fw-semibold mb-2">Estado de Boleta</label>
                    <select name="estado" id="estado" class="form-select form-control-custom">
                        <option value="">Todos los Estados</option>
                        <option value="firmada" {{ request('estado') == 'firmada' ? 'selected' : '' }}>Firmada (Lista para Oficializar)</option>
                        <option value="pendiente_firma" {{ request('estado') == 'pendiente_firma' ? 'selected' : '' }}>Pendiente de Firma</option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="pago_parcial" {{ request('estado') == 'pago_parcial' ? 'selected' : '' }}>Pago Parcial</option>
                        <option value="pagada" {{ request('estado') == 'pagada' ? 'selected' : '' }}>Pagada</option>
                        <option value="anulada" {{ request('estado') == 'anulada' ? 'selected' : '' }}>Anulada</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-search flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('boletas.index') }}" class="btn btn-clear flex-grow-1 text-center">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Número Boleta</th>
                            <th>Estudiante</th>
                            <th>Período</th>
                            <th>Total Cobrado</th>
                            <th>Saldo Pendiente</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($boletas as $boleta)
                            <tr>
                                <td>
                                    <strong class="text-white">{{ $boleta->numero_boleta }}</strong>
                                </td>
                                <td>
                                    <div class="fw-bold" style="color: #f8fafc;">
                                        {{ $boleta->estudiante->nombre ?? 'Desconocido' }} {{ $boleta->estudiante->apellidos ?? '' }}
                                    </div>
                                    <small class="text-white-50">{{ $boleta->estudiante->email ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="text-white-50">{{ $boleta->periodo }}</span>
                                </td>
                                <td>
                                    <strong class="text-white">₡{{ number_format($boleta->total, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="{{ $boleta->saldo_pendiente > 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                        ₡{{ number_format($boleta->saldo_pendiente, 2) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusClass = 'status-badge';
                                        if ($boleta->estado == 'firmada') {
                                            $statusClass .= ' bg-success text-white border border-success';
                                        } elseif ($boleta->estado == 'pendiente_firma') {
                                            $statusClass .= ' bg-warning text-dark border border-warning';
                                        } elseif ($boleta->estado == 'pendiente') {
                                            $statusClass .= ' status-pendiente';
                                        } elseif ($boleta->estado == 'pagada') {
                                            $statusClass .= ' status-pagada';
                                        } elseif ($boleta->estado == 'anulada') {
                                            $statusClass .= ' status-anulada';
                                        } else {
                                            $statusClass .= ' status-parcial';
                                        }
                                    @endphp
                                    <span class="{{ $statusClass }}">
                                        @if ($boleta->estado == 'firmada')
                                            <i class="bi bi-pen me-1"></i> Firmada
                                        @elseif ($boleta->estado == 'pendiente_firma')
                                            <i class="bi bi-hourglass-split me-1"></i> Pendiente Firma
                                        @else
                                            {{ ucfirst(str_replace('_', ' ', $boleta->estado)) }}
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="text-white-50">{{ $boleta->fecha_creacion ? $boleta->fecha_creacion->format('Y-m-d H:i') : 'N/D' }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1 flex-wrap">
                                        @if ($boleta->estado == 'firmada')
                                            <a href="{{ route('boletas.procesar', $boleta->id) }}" class="btn btn-sm btn-success fw-bold px-2" title="Procesar y Oficializar Boleta Firmada">
                                                <i class="bi bi-patch-check-fill me-1"></i> Oficializar
                                            </a>
                                        @elseif ($boleta->estado == 'pendiente_firma')
                                            <a href="{{ route('boletas.procesar', $boleta->id) }}" class="btn btn-sm btn-outline-warning" title="Ver Detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if (!empty($boleta->token_firma))
                                                <button type="button" class="btn btn-sm btn-outline-light btn-copy-firma" 
                                                    data-link="{{ route('boletas.firmar_publico', $boleta->token_firma) }}" 
                                                    title="Copiar Enlace de Firma Digital">
                                                    <i class="bi bi-link-45deg"></i>
                                                </button>
                                            @endif
                                        @endif

                                        <a href="{{ route('boletas.pdf', $boleta->id) }}" target="_blank" class="btn btn-sm btn-outline-success" title="Ver Boleta Oficial en PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>

                                        @if ($boleta->estado !== 'anulada')
                                            <button type="button" class="btn btn-sm btn-outline-info btn-enviar-wa-boleta" 
                                                data-id="{{ $boleta->id }}" 
                                                data-numero="{{ $boleta->numero_boleta }}" 
                                                data-estudiante="{{ $boleta->estudiante->nombre ?? '' }} {{ $boleta->estudiante->apellidos ?? '' }}" 
                                                title="Notificar Boleta por WhatsApp">
                                                <i class="bi bi-whatsapp"></i>
                                            </button>
                                        @endif

                                        @if ($boleta->estado !== 'anulada' && !empty($boleta->estudiante?->email))
                                            <button type="button" class="btn btn-sm btn-outline-warning btn-enviar-email-boleta" 
                                                data-id="{{ $boleta->id }}" 
                                                data-numero="{{ $boleta->numero_boleta }}" 
                                                data-email="{{ $boleta->estudiante->email }}" 
                                                data-estado="{{ $boleta->estado }}"
                                                title="Enviar Boleta por Correo Electrónico ({{ $boleta->estudiante->email }})">
                                                <i class="bi bi-envelope"></i>
                                            </button>
                                        @endif

                                        @if ($boleta->estado !== 'anulada' && $boleta->saldo_pendiente > 0 && $boleta->estado !== 'pendiente_firma')
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-pago" 
                                                data-id="{{ $boleta->id }}" 
                                                data-numero="{{ $boleta->numero_boleta }}" 
                                                data-saldo="{{ $boleta->saldo_pendiente }}" 
                                                title="Registrar Pago / Abono">
                                                <i class="bi bi-cash-stack"></i>
                                            </button>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-boleta" 
                                            data-id="{{ $boleta->id }}" 
                                            data-numero="{{ $boleta->numero_boleta }}" 
                                            title="Eliminar Boleta de Prueba">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-white-50">
                                    <i class="bi bi-receipt display-4 d-block mb-3 text-muted"></i>
                                    No se encontraron boletas en el historial.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            @if ($boletas->hasPages())
                <div class="d-flex justify-content-between align-items-center p-4 border-top" style="border-color: var(--border-dark) !important;">
                    <div class="text-white-50" style="font-size: 0.9rem;">
                        Mostrando registros del <strong>{{ $boletas->firstItem() }}</strong> al <strong>{{ $boletas->lastItem() }}</strong> de un total de <strong>{{ $boletas->total() }}</strong>
                    </div>
                    <div>
                        {{ $boletas->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>

    </div>

    <!-- Modal Registrar Pago -->
    <div class="modal fade" id="modalPago" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="background-color: var(--card-dark); border: 1px solid var(--border-dark); border-radius: 1.25rem;">
                <div class="modal-header border-bottom" style="border-color: var(--border-dark) !important;">
                    <h5 class="modal-title text-white fw-bold">
                        <i class="bi bi-cash-stack text-success me-2"></i>Registrar Pago / Abono
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPago">
                    <input type="hidden" id="pago_boleta_id">
                    <div class="modal-body p-4">
                        <div class="p-3 mb-3 rounded" style="background-color: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-dark);">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-white-50">Boleta:</span>
                                <span class="fw-bold text-white" id="pago_boleta_numero"></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-white-50">Saldo Pendiente:</span>
                                <span class="fw-bold text-danger" id="pago_saldo_display"></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 fw-semibold">Monto a Cancelar (¢)</label>
                            <input type="number" step="0.01" min="0.01" id="pago_monto" class="form-control form-control-custom" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 fw-semibold">Método de Pago</label>
                            <select id="pago_metodo" class="form-select form-control-custom">
                                <option value="Transferencia / SINPE">Transferencia / SINPE Móvil</option>
                                <option value="Tarjeta de Débito/Crédito">Tarjeta de Débito / Crédito</option>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Depósito Bancario">Depósito Bancario</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 fw-semibold">Comprobante / Referencia</label>
                            <input type="text" id="pago_referencia" class="form-control form-control-custom" placeholder="# Comprobante o Detalle">
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="pago_condonar">
                            <label class="form-check-label text-warning small" for="pago_condonar">
                                Condonar morosidad acumulada en cuotas pendientes
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-top" style="border-color: var(--border-dark) !important;">
                        <button type="button" class="btn btn-clear" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-search">
                            <i class="bi bi-check-circle me-1"></i> Confirmar Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalPagoEl = document.getElementById('modalPago');
            const modalPago = modalPagoEl ? new bootstrap.Modal(modalPagoEl) : null;

            document.querySelectorAll('.btn-pago').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const numero = this.dataset.numero;
                    const saldo = parseFloat(this.dataset.saldo);

                    document.getElementById('pago_boleta_id').value = id;
                    document.getElementById('pago_boleta_numero').textContent = numero;
                    document.getElementById('pago_saldo_display').textContent = '¢' + saldo.toLocaleString('es-CR', {minimumFractionDigits: 2});
                    document.getElementById('pago_monto').value = saldo.toFixed(2);
                    document.getElementById('pago_monto').max = saldo;
                    document.getElementById('pago_condonar').checked = false;
                    document.getElementById('pago_referencia').value = '';

                    modalPago.show();
                });
            });

            document.getElementById('formPago')?.addEventListener('submit', async function(e) {
                e.preventDefault();
                const id = document.getElementById('pago_boleta_id').value;
                const monto = document.getElementById('pago_monto').value;
                const metodo = document.getElementById('pago_metodo').value;
                const referencia = document.getElementById('pago_referencia').value;
                const condonar = document.getElementById('pago_condonar').checked;

                Swal.fire({
                    title: 'Procesando pago...',
                    text: 'Por favor espere un momento',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const res = await fetch(`/boletas/${id}/pago`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            monto: monto,
                            metodo: metodo,
                            referencia: referencia,
                            condonar_interes: condonar
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        modalPago.hide();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Pago Registrado!',
                            text: data.message,
                            confirmButtonColor: '#5fb230'
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'No se pudo procesar el pago'
                        });
                    }
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Red',
                        text: err.message
                    });
                }
            });

            // Eliminar Boleta Individual (Directo con confirmación, sin bloqueo por clave)
            document.querySelectorAll('.btn-eliminar-boleta, .btn-anular').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const numero = this.dataset.numero;
                    const isLight = (document.documentElement.getAttribute('data-theme') || localStorage.getItem('theme')) === 'light';

                    Swal.fire({
                        title: '¿Eliminar Boleta ' + numero + '?',
                        html: `
                            <div class="text-start">
                                <div class="alert alert-danger py-2 px-3 small fw-bold mb-3" style="border-radius: 0.5rem;">
                                    <i class="bi bi-exclamation-octagon-fill me-1"></i> Se eliminará permanentemente la boleta <u>${numero}</u>, sus cuotas de pago, comprobantes y firmas asociadas.
                                </div>
                                <p class="small mb-0" style="color: ${isLight ? '#475569' : '#94a3b8'};">
                                    ¿Está seguro de que desea eliminar definitivamente esta boleta de prueba?
                                </p>
                            </div>
                        `,
                        icon: 'warning',
                        iconColor: '#ef4444',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Sí, eliminar boleta',
                        cancelButtonText: 'Cancelar',
                        background: isLight ? '#ffffff' : '#1e293b',
                        color: isLight ? '#0f172a' : '#f8fafc'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Eliminando boleta...',
                                text: 'Limpiando registros contables y desvinculando datos...',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });

                            try {
                                const res = await fetch(`/boletas/${id}/eliminar`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                });

                                let data;
                                try {
                                    data = await res.json();
                                } catch (e) {
                                    data = { success: false, message: 'Respuesta del servidor (HTTP ' + res.status + ')' };
                                }

                                if (res.ok && data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Boleta Eliminada!',
                                        text: data.message,
                                        confirmButtonColor: '#5fb230'
                                    }).then(() => window.location.reload());
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error al Eliminar',
                                        text: data.message || ('Error ' + res.status + ': No se pudo eliminar la boleta')
                                    });
                                }
                            } catch (err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error de Red',
                                    text: err.message
                                });
                            }
                        }
                    });
                });
            });

            // Botón Vaciar Todas las Boletas de Prueba
            const btnVaciarPruebas = document.getElementById('btn-vaciar-pruebas');
            if (btnVaciarPruebas) {
                btnVaciarPruebas.addEventListener('click', function() {
                    const isLight = (document.documentElement.getAttribute('data-theme') || localStorage.getItem('theme')) === 'light';

                    Swal.fire({
                        title: '¿Vaciar TODAS las Boletas de Prueba?',
                        html: `
                            <div class="text-start">
                                <div class="alert alert-danger py-2 px-3 small fw-bold mb-3" style="border-radius: 0.5rem;">
                                    <i class="bi bi-trash3-fill me-1"></i> Se eliminarán todas las boletas de prueba generadas, sus cuotas de seguimiento y pagos registrados.
                                </div>
                                <p class="small mb-0" style="color: ${isLight ? '#475569' : '#94a3b8'};">
                                    Esta opción dejará el módulo de boletas completamente limpio para continuar sus pruebas.
                                </p>
                            </div>
                        `,
                        icon: 'warning',
                        iconColor: '#dc3545',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Sí, vaciar todas',
                        cancelButtonText: 'Cancelar',
                        background: isLight ? '#ffffff' : '#1e293b',
                        color: isLight ? '#0f172a' : '#f8fafc'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Vaciando boletas...',
                                text: 'Restableciendo historial a limpio...',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });

                            try {
                                const res = await fetch(`{{ route('boletas.vaciar_pruebas') }}`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                });

                                let data;
                                try {
                                    data = await res.json();
                                } catch (e) {
                                    data = { success: false, message: 'Respuesta del servidor (HTTP ' + res.status + ')' };
                                }

                                if (res.ok && data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Historial Vaciado!',
                                        text: data.message,
                                        confirmButtonColor: '#5fb230'
                                    }).then(() => window.location.reload());
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error al Vaciar',
                                        text: data.message || 'No se pudo vaciar el historial de boletas'
                                    });
                                }
                            } catch (err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error de Red',
                                    text: err.message
                                });
                            }
                        }
                    });
                });
            }
            // Enviar Boleta por WhatsApp
            document.querySelectorAll('.btn-enviar-wa-boleta').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const numero = this.dataset.numero;
                    const estudiante = this.dataset.estudiante;

                    Swal.fire({
                        title: 'Notificar por WhatsApp',
                        text: `¿Desea enviar la boleta ${numero} al estudiante ${estudiante} por WhatsApp?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#25d366',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bi bi-send-fill me-1"></i> Sí, enviar',
                        cancelButtonText: 'Cancelar'
                    }).then(async (result) => {
                            Swal.fire({
                                title: 'Enviando WhatsApp...',
                                text: 'Despachando notificación vía n8n...',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });

                            try {
                                const res = await fetch(`/boletas/${id}/enviar-whatsapp`, {
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
                                        title: '¡Notificación Enviada!',
                                        text: data.message,
                                        confirmButtonColor: '#25d366'
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'No se pudo enviar automáticamente',
                                        text: data.message,
                                        showCancelButton: true,
                                        confirmButtonColor: '#25d366',
                                        confirmButtonText: 'Abrir WhatsApp Web',
                                        cancelButtonText: 'Cerrar'
                                    }).then((subRes) => {
                                        if (subRes.isConfirmed && data.wa_link) {
                                            window.open(data.wa_link, '_blank');
                                        }
                                    });
                                }
                            } catch (err) {
                                Swal.fire('Error de Red', err.message, 'error');
                            }
                        }
                    });
                });
            });

            // Enviar Boleta por Correo Electrónico
            document.querySelectorAll('.btn-enviar-email-boleta').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const numero = this.dataset.numero;
                    const email = this.dataset.email;
                    const estado = this.dataset.estado;
                    const esFirma = (estado === 'pendiente_firma');

                    Swal.fire({
                        title: 'Enviar por Correo Electrónico',
                        html: `
                            <div class="text-start">
                                <p class="mb-2">¿Desea enviar la boleta <strong>${numero}</strong> por correo electrónico?</p>
                                <div class="p-3 rounded bg-light text-dark mb-2 small border">
                                    <div class="mb-1"><strong>Destinatario:</strong> <span class="text-primary">${email}</span></div>
                                    <div><strong>Modalidad:</strong> ${esFirma ? '<span class="badge bg-warning text-dark">Enlace de Firma Digital</span>' : '<span class="badge bg-success">Boleta Oficial (PDF Adjunto)</span>'}</div>
                                </div>
                            </div>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#1066ad',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bi bi-envelope-fill me-1"></i> Sí, Enviar Correo',
                        cancelButtonText: 'Cancelar'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Enviando correo...',
                                text: `Conectando con el servidor de correo institucional para enviar a ${email}...`,
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });

                            try {
                                const res = await fetch(`/boletas/${id}/enviar-email`, {
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
                                        title: '¡Correo Enviado!',
                                        text: data.message,
                                        confirmButtonColor: '#1066ad'
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error de Envío',
                                        text: data.message || 'No se pudo enviar el correo'
                                    });
                                }
                            } catch (err) {
                                Swal.fire('Error de Conexión', err.message, 'error');
                            }
                        }
                    });
                });
            });

            // Copiar enlace de firma digital
            document.querySelectorAll('.btn-copy-firma').forEach(btn => {
                btn.addEventListener('click', function() {
                    const link = this.dataset.link;
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(link).then(() => {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Enlace Copiado!',
                                text: 'El enlace de firma digital fue copiado al portapapeles.',
                                timer: 2000,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        });
                    } else {
                        prompt('Copie el siguiente enlace para que el estudiante firme la boleta:', link);
                    }
                });
            });
        });
    </script>
@endsection
