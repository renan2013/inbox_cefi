<div class="modal fade" id="modalRazonesDescuento" tabindex="-1" aria-labelledby="modalRazonesDescuentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold text-dark" id="modalRazonesDescuentoLabel">
                    <i class="bi bi-tags-fill text-success me-2"></i>Gestionar Razones de Descuento / Ajuste
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-2 align-items-end mb-4">
                    <input type="hidden" id="razon-descuento-id" value="">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold text-muted mb-1">Nombre de la Razón</label>
                        <input type="text" id="razon-descuento-nombre" class="form-control" placeholder="Ej: Promoción Matrícula Temprana, Beca 50%, Convenio...">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="button" class="btn btn-success fw-bold w-100" id="btn-guardar-razon-descuento" onclick="guardarRazonDescuentoModal()">
                            <i class="bi bi-plus-circle me-1"></i> Guardar
                        </button>
                        <button type="button" class="btn btn-outline-secondary d-none" id="btn-cancelar-razon-descuento" onclick="limpiarFormRazonDescuento()">
                            Cancelar
                        </button>
                    </div>
                </div>

                <div class="table-responsive border rounded-3" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3 py-2">Razón</th>
                                <th class="text-end pe-3 py-2" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-razones-descuento">
                            @foreach($razonesDescuento ?? [] as $rd)
                                <tr data-id="{{ $rd->id }}" data-nombre="{{ $rd->nombre }}">
                                    <td class="ps-3 fw-medium">{{ $rd->nombre }}</td>
                                    <td class="text-end pe-3">
                                        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2" onclick="editarRazonDescuentoModal({{ $rd->id }}, @js($rd->nombre))" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" onclick="eliminarRazonDescuentoModal({{ $rd->id }})" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function limpiarFormRazonDescuento() {
        $('#razon-descuento-id').val('');
        $('#razon-descuento-nombre').val('');
        $('#btn-guardar-razon-descuento').html('<i class="bi bi-plus-circle me-1"></i> Guardar');
        $('#btn-cancelar-razon-descuento').addClass('d-none');
    }

    function editarRazonDescuentoModal(id, nombre) {
        $('#razon-descuento-id').val(id);
        $('#razon-descuento-nombre').val(nombre).focus();
        $('#btn-guardar-razon-descuento').html('<i class="bi bi-check2 me-1"></i> Actualizar');
        $('#btn-cancelar-razon-descuento').removeClass('d-none');
    }

    function renderListaRazonesEnBoleta(lista) {
        const container = $('#razones-descuento-list');
        if (!container.length) return;
        const seleccionados = [];
        container.find('.razon-descuento-check:checked').each(function() {
            seleccionados.push($(this).val());
        });

        let html = '';
        let tbodyHtml = '';
        if (!lista || lista.length === 0) {
            html = '<span class="text-muted small">No hay razones registradas. Use "Gestionar Razones" para crear una.</span>';
        } else {
            lista.forEach(item => {
                const checked = seleccionados.includes(item.nombre) ? 'checked' : '';
                const safeNombre = $('<div>').text(item.nombre).html();
                html += `
                    <label class="btn btn-sm ${checked ? 'btn-success text-white' : 'btn-outline-secondary'} rounded-pill d-inline-flex align-items-center gap-1 px-3 py-1 cursor-pointer razon-pill-label">
                        <input type="checkbox" class="form-check-input m-0 razon-descuento-check" value="${safeNombre}" ${checked}>
                        <span>${safeNombre}</span>
                    </label>
                `;
                tbodyHtml += `
                    <tr data-id="${item.id}" data-nombre="${safeNombre}">
                        <td class="ps-3 fw-medium">${safeNombre}</td>
                        <td class="text-end pe-3">
                            <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2" onclick='editarRazonDescuentoModal(${item.id}, ${JSON.stringify(item.nombre)})' title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" onclick="eliminarRazonDescuentoModal(${item.id})" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        container.html(html);
        $('#tbody-razones-descuento').html(tbodyHtml);
    }

    function guardarRazonDescuentoModal() {
        const id = $('#razon-descuento-id').val();
        const nombre = ($('#razon-descuento-nombre').val() || '').trim();
        if (!nombre) {
            Swal.fire('Atención', 'Ingrese el nombre de la razón.', 'warning');
            return;
        }
        const btn = $('#btn-guardar-razon-descuento');
        btn.prop('disabled', true);

        fetch('{{ route('boletas.razones_descuento.guardar') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: id || null, nombre: nombre })
        })
        .then(r => r.json())
        .then(res => {
            btn.prop('disabled', false);
            if (res.success) {
                limpiarFormRazonDescuento();
                renderListaRazonesEnBoleta(res.razones || []);
            } else {
                Swal.fire('Error', res.message || 'No se pudo guardar la razón.', 'error');
            }
        })
        .catch(() => {
            btn.prop('disabled', false);
            Swal.fire('Error', 'Error de conexión al guardar la razón.', 'error');
        });
    }

    function eliminarRazonDescuentoModal(id) {
        fetch(`{{ url('boletas/razones-descuento') }}/${id}/eliminar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                renderListaRazonesEnBoleta(res.razones || []);
            } else {
                Swal.fire('Error', res.message || 'No se pudo eliminar.', 'error');
            }
        });
    }
</script>
