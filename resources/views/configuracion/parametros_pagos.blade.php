@extends('layouts.app')

@section('title', 'Configuración de Pagos y Firmas Oficiales - Inbox CEFI')

@section('content')
<div class="container py-4" style="max-width: 1050px;">
    <!-- Encabezado Estilo UNELA con Seguridad Superior -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-1 font-monospace small">
                    <i class="bi bi-shield-check me-1"></i> CONTROL ADMINISTRADOR GENERAL
                </span>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 font-monospace small">
                    <i class="bi bi-lock-fill me-1"></i> SESIÓN SEGURA ACTIVA
                </span>
            </div>
            <h3 class="fw-bold mb-1 d-flex align-items-center gap-2" style="letter-spacing: -0.5px;">
                <i class="bi bi-gear-fill text-primary fs-3"></i> 
                <span>Configuración de Pagos y Firmas Oficiales</span>
            </h3>
            <p class="text-muted small mb-0">Gestione la firma institucional en boletas, cálculo de morosidad y valores fijos predeterminados.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-danger rounded-pill px-3 shadow-sm fw-bold d-flex align-items-center gap-1" onclick="resetearPruebasBoletas()">
                <i class="bi bi-radioactive"></i> Resetear Pruebas a 0
            </button>
            <a href="{{ route('boletas.index') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm fw-bold d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i> Ir a Boletas
            </a>
            <form action="{{ route('configuracion.salir') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger rounded-pill px-3 shadow-sm fw-bold d-flex align-items-center gap-1" title="Bloquear inmediatamente la sesión segura">
                    <i class="bi bi-lock-fill"></i> Bloquear Acceso
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form id="form-configuracion" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="eliminar_firma_imagen" id="eliminar_firma_imagen" value="0">

        <div class="row g-4">
            <!-- 1. Firma Oficial Institucional (Tarjeta Azul) -->
            <div class="col-lg-6 col-12">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-primary text-white py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-pen-fill fs-5"></i>
                            <h5 class="fw-bold mb-0">Firma Oficial en Documentos</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Nombre de la Autoridad Firmante</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-2"><i class="bi bi-person-badge"></i></span>
                                <input type="text" class="form-control border-2 fw-bold" name="firma_oficial_nombre" value="{{ $config['firma_oficial_nombre'] ?? 'Merlin Silva' }}" required placeholder="Ej: Merlin Silva">
                            </div>
                            <div class="form-text small">Nombre que se estampará en el bloque oficial de la administración.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Puesto / Cargo Oficial</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-2"><i class="bi bi-briefcase"></i></span>
                                <input type="text" class="form-control border-2" name="firma_oficial_cargo" value="{{ $config['firma_oficial_cargo'] ?? 'Administradora General - UNELA' }}" required placeholder="Ej: Administradora General">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Imagen de Firma Manuscrita / Sello</label>
                            <input type="file" class="form-control border-2 mb-2" name="firma_oficial_imagen_file" id="firma_file" accept="image/png, image/jpeg, image/webp, image/svg+xml">
                            <div class="form-text small mb-3">Recomendado: Imagen PNG con fondo transparente (máx. 2MB).</div>

                            <!-- Preview de Firma -->
                            <div class="p-3 bg-light rounded-3 text-center border border-2 border-dashed position-relative" id="firma-preview-box" style="min-height: 100px; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                                @php
                                    $firmaPath = $config['firma_oficial_imagen'] ?? '';
                                    $firmaExiste = !empty($firmaPath) && file_exists(public_path($firmaPath));
                                @endphp

                                @if($firmaExiste)
                                    <img src="{{ asset($firmaPath) }}?t={{ time() }}" id="img-preview" alt="Firma Oficial" style="max-height: 75px; max-width: 100%; object-fit: contain;">
                                    <div class="mt-2" id="btn-remover-wrapper">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold" onclick="removerFirma()">
                                            <i class="bi bi-trash me-1"></i> Quitar Firma
                                        </button>
                                    </div>
                                @else
                                    <div class="text-muted small py-2" id="no-firma-text">
                                        <i class="bi bi-image fs-3 d-block mb-1 opacity-50"></i>
                                        No hay imagen de firma subida.<br>(Se mostrará línea de firma estándar).
                                    </div>
                                    <img src="" id="img-preview" alt="Preview" style="max-height: 75px; max-width: 100%; object-fit: contain; display: none;">
                                    <div class="mt-2" id="btn-remover-wrapper" style="display: none;">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold" onclick="removerFirma()">
                                            <i class="bi bi-trash me-1"></i> Quitar Firma
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Políticas de Morosidad e Intereses (Tarjeta Roja) -->
            <div class="col-lg-6 col-12">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-danger text-white py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-percent fs-5"></i>
                            <h5 class="fw-bold mb-0">% Cálculo de Intereses y Morosidad</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Tasa de Interés / Recargo por Mora (%)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-2 fw-bold text-danger fs-5">%</span>
                                <input type="number" step="0.01" min="0" max="100" class="form-control border-2 fw-bold text-danger fs-4" name="tasa_interes_mora" value="{{ $config['tasa_interes_mora'] ?? '2.0' }}" required>
                            </div>
                            <div class="form-text small">Porcentaje aplicado sobre cuotas vencidas no pagadas.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Método de Cálculo</label>
                            <select class="form-select border-2 fw-bold" name="tipo_interes_mora" style="height: 48px;">
                                <option value="diario_compuesto" {{ ($config['tipo_interes_mora'] ?? '') === 'diario_compuesto' ? 'selected' : '' }}>
                                    Interés Diario Compuesto (Monto × (1 + r)^días - Monto)
                                </option>
                                <option value="diario_simple" {{ ($config['tipo_interes_mora'] ?? '') === 'diario_simple' ? 'selected' : '' }}>
                                    Interés Diario Simple (Monto × r × días)
                                </option>
                                <option value="mensual_simple" {{ ($config['tipo_interes_mora'] ?? '') === 'mensual_simple' ? 'selected' : '' }}>
                                    Interés Mensual Simple (Monto × r por cada mes de atraso)
                                </option>
                            </select>
                            <div class="form-text small">Seleccione la fórmula matemática con la que se liquidará la mora al vencer la fecha de pago.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Días de Gracia de Tolerancia</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-2"><i class="bi bi-calendar-event"></i></span>
                                <input type="number" min="0" max="30" class="form-control border-2 fw-bold" name="dias_gracia_mora" value="{{ $config['dias_gracia_mora'] ?? '0' }}" required>
                                <span class="input-group-text bg-light border-2 fw-bold">días</span>
                            </div>
                            <div class="form-text small">Días permitidos tras el vencimiento antes de empezar a cobrar mora.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Valores Predeterminados de Cargos Fijos (Tarjeta Oscura) -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-dark text-white py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-tags-fill fs-5 text-warning"></i>
                            <h5 class="fw-bold mb-0">Montos Predeterminados de Cargos Fijos</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">INS-01: Inscripción Única (₡)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-2 fw-bold">₡</span>
                                    <input type="number" step="100" min="0" class="form-control border-2 fw-bold fs-5" name="monto_inscripcion_unica" value="{{ $config['monto_inscripcion_unica'] ?? '8000.00' }}" required>
                                </div>
                                <div class="form-text small">Cobro opcional de primera vez por ingreso.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">BIB-01: Uso de Biblioteca (₡)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-2 fw-bold">₡</span>
                                    <input type="number" step="100" min="0" class="form-control border-2 fw-bold fs-5" name="monto_biblioteca" value="{{ $config['monto_biblioteca'] ?? '5000.00' }}" required>
                                </div>
                                <div class="form-text small">Acceso a recursos bibliográficos por período.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">ADM-01: Matrícula Base (₡)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-2 fw-bold">₡</span>
                                    <input type="number" step="100" min="0" class="form-control border-2 fw-bold fs-5" name="monto_matricula_base" value="{{ $config['monto_matricula_base'] ?? '30000.00' }}" required>
                                </div>
                                <div class="form-text small">Arancel administrativo ordinario de matrícula.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón Guardar -->
            <div class="col-12 text-end pt-2">
                <button type="submit" id="btn-guardar" class="btn btn-success btn-lg rounded-pill px-5 shadow-lg fw-bold" style="background-color: var(--primary, #5fb230); border-color: var(--primary, #5fb230); font-size: 1.05rem;">
                    <i class="bi bi-check-circle-fill me-2"></i> Guardar Todos los Parámetros
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Previsualización de imagen al seleccionarla
    const firmaFileInput = document.getElementById('firma_file');
    const imgPreview = document.getElementById('img-preview');
    const noFirmaText = document.getElementById('no-firma-text');
    const btnRemoverWrapper = document.getElementById('btn-remover-wrapper');
    const eliminarInput = document.getElementById('eliminar_firma_imagen');

    if (firmaFileInput) {
        firmaFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPreview.src = e.target.result;
                    imgPreview.style.display = 'block';
                    if (noFirmaText) noFirmaText.style.display = 'none';
                    if (btnRemoverWrapper) btnRemoverWrapper.style.display = 'block';
                    eliminarInput.value = '0';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    window.removerFirma = function() {
        imgPreview.src = '';
        imgPreview.style.display = 'none';
        if (noFirmaText) {
            noFirmaText.style.display = 'block';
            noFirmaText.innerHTML = '<i class="bi bi-image fs-3 d-block mb-1 opacity-50"></i> Firma removida.<br>(Se guardará al enviar el formulario).';
        }
        if (btnRemoverWrapper) btnRemoverWrapper.style.display = 'none';
        firmaFileInput.value = '';
        eliminarInput.value = '1';
    };

    // Envío AJAX del formulario
    const formConfig = document.getElementById('form-configuracion');
    if (formConfig) {
        formConfig.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            Swal.fire({
                title: 'Guardando parámetros...',
                text: 'Por favor espere un momento',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch("{{ route('configuracion.pagos.guardar') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Guardado Exitoso!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#5fb230',
                        confirmButtonText: 'Aceptar'
                    });
                } else {
                    Swal.fire('Error', data.message || 'Error al guardar parámetros.', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'No se pudo conectar con el servidor para guardar los cambios.', 'error');
            });
        });
    }

    // Resetear Pruebas a 0
    window.resetearPruebasBoletas = function() {
        Swal.fire({
            title: '¿Reset Total de Boletas y Pagos?',
            html: `
                <div class="text-start">
                    <div class="alert alert-danger py-2 px-3 small fw-bold mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> ¡ADVERTENCIA CRÍTICA!
                        <br>Esta acción vaciará completamente:
                        <ul class="mb-0 ps-3 mt-1">
                            <li>Todas las boletas generadas.</li>
                            <li>Todas las cuotas y letras de vencimiento.</li>
                            <li>Todo el historial de pagos y abonos.</li>
                            <li>Todos los archivos PDF de boletas guardados.</li>
                        </ul>
                    </div>
                    <label class="form-label fw-bold small text-muted text-uppercase mb-1">Ingrese su contraseña de administrador:</label>
                </div>
            `,
            icon: 'warning',
            input: 'password',
            inputAttributes: {
                autocapitalize: 'off',
                placeholder: 'Contraseña de administrador...'
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Sí, Resetear Todo a 0',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: async (password) => {
                if (!password || password.trim() === '') {
                    Swal.showValidationMessage('Debe ingresar su contraseña.');
                    return false;
                }
                try {
                    const fd = new FormData();
                    fd.append('password', password);
                    const resp = await fetch("{{ route('configuracion.pagos.reset_pruebas') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: fd
                    });
                    const res = await resp.json();
                    if (!res.success) {
                        throw new Error(res.message || 'Error al ejecutar el reset');
                    }
                    return res;
                } catch (err) {
                    Swal.showValidationMessage(err.message || 'Error de conexión con el servidor');
                    return false;
                }
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value && result.value.success) {
                Swal.fire({
                    title: '¡Reset Completado!',
                    text: result.value.message,
                    icon: 'success',
                    confirmButtonColor: '#5fb230'
                }).then(() => {
                    window.location.href = "{{ route('boletas.index') }}";
                });
            }
        });
    };
});
</script>
@endsection
