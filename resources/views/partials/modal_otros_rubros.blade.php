<!-- Modal Compartido: Generador y Gestor de Otros Rubros / Gastos (Nombre, Descripción y Costo) -->
<div class="modal fade" id="modalGestionarOtrosRubros" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content glass-card border-0 shadow-lg" style="background: var(--card-bg, #ffffff);">
            <div class="modal-header border-bottom px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="bi bi-tags-fill text-primary me-2"></i>Catálogo de Otros Rubros y Gastos
                    </h5>
                    <small class="text-muted">Alimente el sistema con rubros adicionales (nombre, descripción y costo respectivo).</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <form id="form-guardar-otro-rubro" class="row g-2 align-items-end mb-4 p-3 rounded-3 border bg-light">
                    <input type="hidden" id="otro-rubro-id" value="">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">Nombre del Rubro *</label>
                        <input type="text" id="otro-rubro-nombre" class="form-control" placeholder="Ej: UNIFORME CAMISA..." maxlength="150" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">Descripción (Opcional)</label>
                        <input type="text" id="otro-rubro-descripcion" class="form-control" placeholder="Ej: Camisa institucional bordada..." maxlength="255">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold mb-1">Costo (¢)</label>
                        <input type="number" id="otro-rubro-precio" class="form-control" min="0" step="100" value="0" placeholder="0.00">
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100 fw-bold" id="btn-guardar-otro-rubro">
                            <i class="bi bi-plus-circle me-1"></i> Guardar
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btn-cancelar-otro-rubro" style="display:none;" title="Cancelar edición" onclick="resetFormOtroRubro()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </form>

                <div class="table-responsive" style="max-height: 360px;">
                    <table class="table table-sm table-hover align-middle mb-0" id="tabla-otros-rubros-catalogo">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th class="text-end">Costo (¢)</th>
                                <th class="text-end" style="width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (($otrosRubros ?? []) as $or)
                                <tr data-id="{{ $or->id }}" data-nombre="{{ $or->nombre }}" data-descripcion="{{ $or->descripcion ?? '' }}" data-precio="{{ $or->precio }}">
                                    <td class="fw-semibold">{{ $or->nombre }}</td>
                                    <td class="text-muted small">{{ $or->descripcion ?: '—' }}</td>
                                    <td class="text-end font-monospace">¢{{ number_format((float)$or->precio, 2) }}</td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick='editarOtroRubroModal({{ $or->id }}, @js($or->nombre), @js($or->descripcion ?? ""), {{ (float)$or->precio }})' title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick='eliminarOtroRubroModal({{ $or->id }}, @js($or->nombre))' title="Eliminar">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.OTROS_RUBROS_CATALOGO = @json($otrosRubros ?? []);

    function resetFormOtroRubro() {
        document.getElementById('otro-rubro-id').value = '';
        document.getElementById('otro-rubro-nombre').value = '';
        document.getElementById('otro-rubro-descripcion').value = '';
        document.getElementById('otro-rubro-precio').value = '0';
        document.getElementById('btn-guardar-otro-rubro').innerHTML = '<i class="bi bi-plus-circle me-1"></i> Guardar';
        document.getElementById('btn-cancelar-otro-rubro').style.display = 'none';
    }

    function editarOtroRubroModal(id, nombre, descripcion, precio) {
        document.getElementById('otro-rubro-id').value = id;
        document.getElementById('otro-rubro-nombre').value = nombre;
        document.getElementById('otro-rubro-descripcion').value = descripcion || '';
        document.getElementById('otro-rubro-precio').value = precio;
        document.getElementById('btn-guardar-otro-rubro').innerHTML = '<i class="bi bi-check-lg me-1"></i> Actualizar';
        document.getElementById('btn-cancelar-otro-rubro').style.display = 'inline-block';
        document.getElementById('otro-rubro-nombre').focus();
    }

    async function eliminarOtroRubroModal(id, nombre) {
        if (!confirm(`¿Eliminar el rubro "${nombre}" del catálogo?`)) return;
        const res = await fetch(`/rubros-otros/${id}/eliminar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (res.ok && data.success) {
            window.OTROS_RUBROS_CATALOGO = window.OTROS_RUBROS_CATALOGO.filter(r => Number(r.id) !== Number(id));
            document.querySelector(`#tabla-otros-rubros-catalogo tr[data-id="${id}"]`)?.remove();
            if (typeof window.onOtrosRubrosUpdated === 'function') window.onOtrosRubrosUpdated();
        } else {
            alert(data.message || 'No se pudo eliminar el rubro.');
        }
    }

    document.getElementById('form-guardar-otro-rubro')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = document.getElementById('otro-rubro-id').value;
        const nombre = document.getElementById('otro-rubro-nombre').value.trim();
        const descripcion = document.getElementById('otro-rubro-descripcion').value.trim();
        const precio = parseFloat(document.getElementById('otro-rubro-precio').value || '0') || 0;
        if (!nombre) return;

        const btn = document.getElementById('btn-guardar-otro-rubro');
        btn.disabled = true;

        try {
            const res = await fetch("{{ route('rubros_otros.guardar') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id: id || null, nombre, descripcion, precio })
            });
            const data = await res.json();
            btn.disabled = false;

            if (res.ok && data.success && data.rubro) {
                const r = data.rubro;
                const idx = window.OTROS_RUBROS_CATALOGO.findIndex(x => Number(x.id) === Number(r.id));
                if (idx >= 0) {
                    window.OTROS_RUBROS_CATALOGO[idx] = r;
                } else {
                    window.OTROS_RUBROS_CATALOGO.push(r);
                    window.OTROS_RUBROS_CATALOGO.sort((a, b) => a.nombre.localeCompare(b.nombre));
                }

                const tbody = document.querySelector('#tabla-otros-rubros-catalogo tbody');
                if (tbody) {
                    tbody.innerHTML = window.OTROS_RUBROS_CATALOGO.map(item => `
                        <tr data-id="${item.id}" data-nombre="${item.nombre}" data-descripcion="${item.descripcion || ''}" data-precio="${item.precio}">
                            <td class="fw-semibold">${item.nombre}</td>
                            <td class="text-muted small">${item.descripcion || '—'}</td>
                            <td class="text-end font-monospace">¢${Number(item.precio).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick='editarOtroRubroModal(${item.id}, ${JSON.stringify(item.nombre)}, ${JSON.stringify(item.descripcion || "")}, ${Number(item.precio)})' title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick='eliminarOtroRubroModal(${item.id}, ${JSON.stringify(item.nombre)})' title="Eliminar">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');
                }

                resetFormOtroRubro();
                if (typeof window.onOtrosRubrosUpdated === 'function') window.onOtrosRubrosUpdated(r);
            } else {
                alert(data.message || 'No se pudo guardar el rubro.');
            }
        } catch (err) {
            btn.disabled = false;
            alert('Error de conexión al guardar el rubro.');
        }
    });
</script>
