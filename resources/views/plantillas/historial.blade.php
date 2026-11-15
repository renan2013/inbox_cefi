@extends('layouts.app')

@section('title', 'Inbox BPM - Historial de Emisiones')

@section('styles')
    <style>
        .page-header {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .table-custom {
            margin-bottom: 0;
            background-color: transparent !important;
        }
        .table-custom td, .table-custom th {
            background-color: transparent !important;
            color: #e2e8f0 !important;
            border-bottom: 1px solid var(--border-dark);
            padding: 1.1rem 1.5rem;
            vertical-align: middle;
        }

        .thumbnail-img {
            width: 85px;
            height: auto;
            border-radius: 8px;
            border: 2px solid var(--border-dark);
            cursor: pointer;
            transition: all 0.25s ease;
            background-color: rgba(15, 23, 42, 0.4);
            object-fit: contain;
            aspect-ratio: 4/3;
        }
        .thumbnail-img:hover {
            transform: scale(1.08);
            border-color: var(--primary);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
        }

        .img-full-preview {
            max-width: 100%;
            height: auto;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6);
        }
        .modal-content-img {
            background: transparent;
            border: none;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('plantillas.index') }}" class="text-decoration-none text-white-50">Automatizaciones</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Historial de Emisiones</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="display-6 fw-bold mb-1"><i class="bi bi-clock-history text-success me-2"></i>Historial de Documentos Emitidos</h1>
                <p class="text-white-50 mb-0">Registro y copias de los certificados, títulos y credenciales generados.</p>
            </div>
            <div>
                <a href="{{ route('plantillas.index') }}" class="btn btn-outline-light rounded-pill px-4 fw-semibold">
                    <i class="bi bi-arrow-left me-2"></i> Volver a Plantillas
                </a>
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
                <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <div class="card glass-card">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr class="bg-dark text-white-50">
                            <th class="ps-4">Vista Previa</th>
                            <th>Fecha y Destinatario</th>
                            <th>Plantilla</th>
                            <th>Datos Extra</th>
                            <th class="text-center">Descargas</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($emisiones->count() > 0)
                            @foreach ($emisiones as $row)
                                @php
                                    $extra = json_decode($row->datos_extra ?? '{}', true) ?: [];
                                    $hasPng = !empty($row->ruta_png) && file_exists(public_path($row->ruta_png));
                                    $hasPdf = !empty($row->ruta_archivo) && file_exists(public_path($row->ruta_archivo));
                                    $urlPng = $hasPng ? asset($row->ruta_png) : null;
                                    $urlPdf = $hasPdf ? asset($row->ruta_archivo) : null;
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        @if ($urlPng)
                                            <img src="{{ $urlPng }}" class="thumbnail-img" onclick="previewImage('{{ $urlPng }}', '{{ addslashes($row->nombre_estudiante) }}')" title="Ver vista previa">
                                        @else
                                            <div class="thumbnail-img d-flex align-items-center justify-content-center text-white-50 small">N/A</div>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-white-50 fw-bold d-block mb-1">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            {{ $row->fecha_emision ? \Carbon\Carbon::parse($row->fecha_emision)->format('d/m/Y H:i') : '-' }}
                                        </small>
                                        <strong class="text-white d-block">{{ $row->nombre_estudiante }}</strong>
                                        <small class="text-white-50"><i class="bi bi-person-badge me-1"></i> Por: {{ $row->emisor_nombre }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-25 text-white border border-secondary border-opacity-25 px-3 py-1.5 rounded-pill">
                                            {{ $row->plantilla_nombre }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small text-white-50">
                                            {{ ($extra['periodo'] ?? '') ?: 'General' }}
                                            @if(!empty($extra['estado']))
                                                | {{ $extra['estado'] }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-column gap-1 align-items-center">
                                            @if ($urlPdf)
                                                <a href="{{ $urlPdf }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 py-0.5 fw-bold" style="font-size: 0.75rem;">
                                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF
                                                </a>
                                            @endif
                                            @if ($urlPng)
                                                <a href="{{ $urlPng }}" download class="btn btn-sm btn-outline-info rounded-pill px-3 py-0.5 fw-bold" style="font-size: 0.75rem;">
                                                    <i class="bi bi-image-fill me-1"></i> PNG
                                                </a>
                                                <button type="button" onclick="copyImageToClipboard('{{ $urlPng }}')" class="btn btn-sm btn-link text-success text-decoration-none p-0" style="font-size: 0.7rem;" title="Copiar para WhatsApp">
                                                    <i class="bi bi-whatsapp me-1"></i> Copiar Imagen
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" title="Eliminar Registro" onclick="confirmarEliminacion({{ $row->id }}, '{{ addslashes($row->nombre_estudiante) }}')">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center py-5 text-white-50">
                                    <i class="bi bi-clock display-4 d-block mb-2 opacity-25"></i>
                                    Aún no se ha emitido ningún documento en el historial.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Vista Previa -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-content-img">
                <div class="modal-body p-0 text-center">
                    <img src="" id="full-preview-img" class="img-full-preview">
                    <div class="mt-3 text-white fw-bold" id="preview-student-name"></div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function previewImage(url, name) {
            document.getElementById('full-preview-img').src = url;
            document.getElementById('preview-student-name').innerText = name;
            new bootstrap.Modal(document.getElementById('imagePreviewModal')).show();
        }

        async function copyImageToClipboard(imageUrl) {
            try {
                const response = await fetch(imageUrl);
                const blob = await response.blob();
                await navigator.clipboard.write([
                    new ClipboardItem({ [blob.type]: blob })
                ]);
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: '¡Imagen copiada! Ya puedes pegarla en WhatsApp (Ctrl+V)',
                    showConfirmButton: false,
                    timer: 3000
                });
            } catch (err) {
                Swal.fire('Error', 'No se pudo copiar automáticamente. Usa el botón PNG para descargarla.', 'error');
            }
        }

        function confirmarEliminacion(id, nombre) {
            Swal.fire({
                title: '¿Eliminar registro?',
                text: `Se borrará el registro de "${nombre}" y los archivos físicos del servidor.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('plantillas.historial.delete', ':id') }}".replace(':id', id);
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
@endsection
