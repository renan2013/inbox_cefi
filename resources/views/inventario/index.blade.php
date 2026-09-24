@extends('layouts.app')

@section('title', 'Control de Inventario')

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

    .inventory-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .table-custom {
        margin-bottom: 0;
        background-color: transparent !important;
    }

    .table-custom td, .table-custom th {
        background-color: transparent !important;
        color: #cbd5e1 !important;
        border-bottom: 1px solid var(--border-color);
        padding: 1rem;
        vertical-align: middle;
    }

    .table-custom th {
        color: #94a3b8 !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }

    .table-warning-row {
        background-color: rgba(245, 158, 11, 0.08) !important;
    }

    .form-select-custom {
        background-color: rgba(15, 23, 42, 0.5) !important;
        border: 1px solid var(--border-color) !important;
        color: #f8fafc !important;
        border-radius: 0.5rem !important;
        padding: 0.5rem 1rem !important;
    }

    .form-select-custom:focus {
        border-color: var(--primary-blue) !important;
        outline: none !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .btn-action:hover {
        transform: translateY(-2px);
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-md-5 main-container">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item active text-white" aria-current="page">Control de Inventario</li>
        </ol>
    </nav>

    <!-- Header Panel -->
    <div class="inventory-card">
        <div class="row align-items-center">
            <div class="col-md-9">
                <h1 class="h2 fw-bold text-white mb-1"><i class="bi bi-clipboard-data text-primary me-2"></i> Control de Inventario</h1>
                <p class="text-white-50 mb-0">Control de stock de productos, alertas de abastecimiento y movimientos de bodega.</p>
            </div>
            <div class="col-md-3 text-md-end mt-3 mt-md-0">
                <a href="{{ route('inventario.operaciones') }}" class="btn btn-primary rounded-pill px-4 fw-bold">
                    <i class="bi bi-box-arrow-right me-1"></i> Operaciones Bodega
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2) !important;">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- Alert Stock Bajo -->
    @if ($bajo_stock->count() > 0)
        <div class="alert alert-warning border-0 rounded-4 d-flex align-items-center mb-4 p-3 shadow-sm" style="background-color: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2) !important;">
            <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
            <div>
                <strong class="fw-bold">Alerta de Stock Bajo:</strong> Existen {{ $bajo_stock->count() }} productos con nivel crítico de stock y necesitan renovación.
            </div>
        </div>
    @endif

    <!-- Filtros -->
    <div class="inventory-card p-3 mb-4">
        <form method="GET" class="row g-3 align-items-end" id="form_filtros">
            <div class="col-md-4">
                <label class="form-label text-white-50 small fw-bold text-uppercase">Filtrar por Categoría</label>
                <select name="categoria" class="form-select form-select-custom w-100" onchange="this.form.submit()" id="select_categoria">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $cl)
                        <option value="{{ $cl->id }}" {{ $id_cat_filter == $cl->id ? 'selected' : '' }}>
                            {{ $cl->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <a href="{{ route('inventario.index') }}" class="btn btn-outline-light w-100" id="btn_limpiar_filtros">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Tabla de Productos -->
    <div class="inventory-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Producto</th>
                        <th>Categoría / Marca</th>
                        <th>Proveedor</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Precio Unit.</th>
                        <th class="text-center">Subtotal</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total_inventario = 0; @endphp
                    @if (count($productos) > 0)
                        @foreach ($productos as $p)
                            @php
                                $es_critico = ($p->stock_actual <= $p->stock_minimo);
                                $row_class = ($es_critico && $p->estado == 'activo') ? 'table-warning-row' : '';
                                $subtotal = $p->stock_actual * $p->precio_estimado;
                                $total_inventario += $subtotal;
                            @endphp
                            <tr class="{{ $row_class }}">
                                <td class="ps-4">
                                    <div class="fw-bold text-white">{{ $p->nombre }}</div>
                                    <small class="text-white-50 d-block">{{ $p->descripcion }}</small>
                                    @if ($es_critico && $p->estado == 'activo')
                                        <span class="badge bg-danger mt-1" style="font-size: 0.65rem;">RENOVAR STOCK</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info text-white mb-1" style="font-size: 0.75rem;">
                                        {{ $p->categoria_nombre ?: 'Sin Categoría' }}
                                    </span>
                                    <small class="text-white-50 d-block">{{ $p->marca ?: 'Genérico' }}</small>
                                </td>
                                <td>
                                    <div class="small fw-bold text-white">{{ $p->proveedor ?: '-' }}</div>
                                    <div class="small text-white-50">{{ $p->contacto_proveedor }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="fs-5 fw-bold {{ $es_critico ? 'text-danger' : 'text-success' }}">
                                        {{ $p->stock_actual }}
                                    </span>
                                    <div class="text-white-50 small">{{ $p->unidad_medida }}</div>
                                </td>
                                <td class="text-center">
                                    ₡{{ number_format($p->precio_estimado, 2) }}
                                </td>
                                <td class="text-center fw-bold">
                                    ₡{{ number_format($subtotal, 2) }}
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-action btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalMovimiento" 
                                                onclick="setProductoMovimiento({{ $p->id }}, '{{ addslashes($p->nombre) }}')" title="Registrar Movimiento" id="btn_movimiento_{{ $p->id }}">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center py-5 text-white-50">
                                <i class="bi bi-box fs-1 d-block mb-3 opacity-50"></i>
                                No hay productos registrados en esta categoría.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if (count($productos) > 0)
            <div class="p-4 border-top border-secondary bg-dark bg-opacity-25 d-flex justify-content-end">
                <div class="text-end">
                    <span class="text-white-50 text-uppercase fw-bold small d-block">Valor Estimado de esta Vista</span>
                    <span class="fs-3 fw-bold text-success">₡{{ number_format($total_inventario, 2) }}</span>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal para Registrar Movimiento -->
<div class="modal fade" id="modalMovimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('inventario.movimiento.store') }}" method="POST" class="modal-content bg-dark border border-secondary text-white rounded-4 shadow-lg">
            @csrf
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-left-right text-primary me-2"></i> Registrar Movimiento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="id_producto" id="mov_id_producto">
                
                <div class="mb-4">
                    <label class="text-white-50 small text-uppercase fw-bold mb-1 d-block">Producto a Afectar</label>
                    <div id="mov_nombre_producto" class="fs-5 fw-bold text-white"></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-white-50 small fw-bold">Tipo de Movimiento *</label>
                    <select name="tipo" class="form-select form-select-custom w-100" required id="select_tipo_movimiento">
                        <option value="entrada">Entrada (Ingreso / Carga)</option>
                        <option value="salida">Salida (Retiro / Consumo)</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-white-50 small fw-bold">Cantidad *</label>
                    <input type="number" name="cantidad" class="form-control form-control-custom w-100" min="1" required id="input_cantidad_modal">
                </div>

                <div class="mb-3">
                    <label class="form-label text-white-50 small fw-bold">PIN de Autorización *</label>
                    <input type="password" name="pin" class="form-control form-control-custom w-100 text-center" placeholder="****" maxlength="10" required style="letter-spacing: 0.5rem; font-size: 1.25rem;" id="input_pin_modal">
                </div>
                
                <div class="mb-0">
                    <label class="form-label text-white-50 small fw-bold">Comentario (Opcional)</label>
                    <textarea name="comentario" class="form-control form-control-custom w-100" rows="2" placeholder="Ej: Compra mensual, uso para oficina..." id="textarea_comentario_modal"></textarea>
                </div>
            </div>
            <div class="modal-footer border-secondary p-4 pt-0">
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btn_confirmar_movimiento">Confirmar Movimiento</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function setProductoMovimiento(id, nombre) {
        document.getElementById('mov_id_producto').value = id;
        document.getElementById('mov_nombre_producto').innerText = nombre;
    }
</script>
@endsection
