@extends('layouts.app')

@section('title', 'Operaciones de Bodega')

@section('styles')
<style>
    :root {
        --primary-blue: #0d6efd;
        --card-bg: var(--card-dark);
        --border-color: var(--border-dark);
    }

    .main-container {
        padding-top: 3rem;
        padding-bottom: 4rem;
        color: #f8fafc;
    }

    .operations-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 1.5rem;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.35);
        overflow: hidden;
    }

    .card-header-custom {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.15) 0%, rgba(13, 110, 253, 0.02) 100%);
        border-bottom: 1px solid var(--border-color);
        padding: 2.5rem 2rem;
        text-align: center;
    }

    .form-control-custom, .form-select-custom {
        background-color: rgba(15, 23, 42, 0.5) !important;
        border: 1px solid var(--border-color) !important;
        color: #f8fafc !important;
        border-radius: 0.5rem !important;
        padding: 0.8rem 1.2rem !important;
        font-size: 1rem !important;
    }

    .form-control-custom:focus, .form-select-custom:focus {
        border-color: var(--primary-blue) !important;
        outline: none !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }

    .btn-check:checked + .btn-outline-danger {
        background-color: #dc3545 !important;
        color: white !important;
        border-color: #dc3545 !important;
        box-shadow: 0 0 15px rgba(220, 53, 69, 0.4);
    }

    .btn-check:checked + .btn-outline-success {
        background-color: #198754 !important;
        color: white !important;
        border-color: #198754 !important;
        box-shadow: 0 0 15px rgba(25, 135, 84, 0.4);
    }

    .btn-outline-danger, .btn-outline-success {
        border-width: 2px;
        transition: all 0.3s ease;
    }
</style>
@endsection

@section('content')
<div class="container main-container">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4 col-md-8 mx-auto">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('inventario.index') }}" class="text-decoration-none text-white-50">Inventario</a></li>
            <li class="breadcrumb-item active text-white" aria-current="page">Operaciones de Bodega</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="operations-card">
                
                <div class="card-header-custom">
                    <i class="bi bi-box-seam text-primary mb-3" style="font-size: 3rem; display: block;"></i>
                    <h2 class="h3 fw-bold text-white mb-1">Operaciones de Bodega</h2>
                    <p class="text-white-50 mb-0">Retire o ingrese productos rápidamente del stock físico.</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    @if (session('error'))
                        <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2) !important;">
                            <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    <form action="{{ route('inventario.movimiento.store') }}" method="POST" id="form_operaciones_bodega">
                        @csrf
                        
                        <!-- 1. Producto -->
                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-bold text-uppercase mb-2">1. Seleccione el Producto</label>
                            <select name="id_producto" class="form-select form-select-custom w-100" required id="select_producto_operacion">
                                <option value="" disabled selected>Elegir producto de la lista...</option>
                                @foreach($productos as $p)
                                    <option value="{{ $p->id }}" {{ old('id_producto') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nombre }} 
                                        @if($p->marca) ({{ $p->marca }}) @endif
                                        [Stock Actual: {{ $p->stock_actual }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 2. Tipo de Movimiento -->
                        <div class="mb-4 text-center">
                            <label class="form-label text-white-50 small fw-bold text-uppercase d-block mb-3">2. Tipo de Movimiento</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="tipo" id="tipo_salida" value="salida" checked required>
                                <label class="btn btn-outline-danger py-3 fw-bold w-50" for="tipo_salida">
                                    <i class="bi bi-dash-circle fs-4 d-block mb-1"></i> RETIRO (Gasto)
                                </label>

                                <input type="radio" class="btn-check" name="tipo" id="tipo_entrada" value="entrada" required>
                                <label class="btn btn-outline-success py-3 fw-bold w-50" for="tipo_entrada">
                                    <i class="bi bi-plus-circle fs-4 d-block mb-1"></i> INGRESO (Carga)
                                </label>
                            </div>
                        </div>

                        <!-- 3. Cantidad -->
                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-bold text-uppercase mb-2">3. Cantidad a Mover</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <button class="btn btn-outline-secondary border-secondary px-4" type="button" onclick="changeQty(-1)"><i class="bi bi-dash fs-4"></i></button>
                                <input type="number" name="cantidad" id="input_cantidad_operacion" class="form-control form-control-custom text-center fw-bold fs-4 border-secondary border-start-0 border-end-0" value="1" min="1" required>
                                <button class="btn btn-outline-secondary border-secondary px-4" type="button" onclick="changeQty(1)"><i class="bi bi-plus fs-4"></i></button>
                            </div>
                        </div>

                        <!-- 4. PIN -->
                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-bold text-uppercase mb-2">4. PIN de Autorización</label>
                            <input type="password" name="pin" class="form-control form-control-custom w-100 text-center fw-bold" 
                                   placeholder="****" maxlength="10" required style="letter-spacing: 0.5rem; font-size: 1.5rem;" id="input_pin_operacion">
                            <div class="form-text text-center text-white-50 small mt-2">Ingrese su PIN personal asignado de bodega.</div>
                        </div>

                        <!-- 5. Comentario -->
                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-bold text-uppercase mb-2">5. Comentario (Opcional)</label>
                            <input type="text" name="comentario" class="form-control form-control-custom w-100" placeholder="Ej: Uso para recepción, recarga de insumos..." value="{{ old('comentario') }}" id="input_comentario_operacion">
                        </div>

                        <!-- Confirmación -->
                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow" id="btn_confirmar_operacion">
                                Confirmar Operación <i class="bi bi-check2-circle ms-2"></i>
                            </button>
                        </div>

                    </form>
                </div>

                <div class="card-footer bg-dark bg-opacity-25 border-top border-secondary p-3 text-center">
                    <a href="{{ route('inventario.index') }}" class="text-decoration-none text-white-50 small">
                        <i class="bi bi-arrow-left"></i> Volver al Panel de Inventario
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function changeQty(delta) {
        const input = document.getElementById('input_cantidad_operacion');
        let val = parseInt(input.value) || 0;
        val = Math.max(1, val + delta);
        input.value = val;
    }
</script>
@endsection
