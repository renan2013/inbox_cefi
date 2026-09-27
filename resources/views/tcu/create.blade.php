@extends('layouts.app')

@section('title', 'Inbox BPM - Nueva Bitácora de TCU')

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

        .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .form-control-custom, .form-select-custom {
            background-color: rgba(15, 23, 42, 0.5);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.7rem 1rem;
            transition: all 0.3s;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            background-color: rgba(15, 23, 42, 0.7);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15);
            color: #f8fafc;
            outline: none;
        }

        .form-label-custom {
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tcu.index') }}" class="text-decoration-none text-white-50">Bitácoras TCU</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Nueva Bitácora</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Nueva Bitácora TCU</h1>
                <p class="text-white-50 mb-0">Registre la cabecera de información del proyecto de Trabajo Comunal Universitario.</p>
            </div>
            <div>
                <a href="{{ route('tcu.index') }}" class="btn btn-outline-light rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Listado
                </a>
            </div>
        </div>

        <div class="card glass-card">
            <div class="card-body p-4 p-md-5 text-white">
                <form id="crearBitacoraForm">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label-custom">Nombre del Proyecto *</label>
                            <input type="text" name="nombre_proyecto" class="form-control form-control-custom w-100" placeholder="Ej: Apoyo en digitalización de expedientes..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Carrera *</label>
                            <input type="text" name="carrera" class="form-control form-control-custom w-100" placeholder="Ej: Bachillerato en Ciencias de la Educación" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Lugar de Realización *</label>
                            <input type="text" name="lugar_realizacion" class="form-control form-control-custom w-100" placeholder="Ej: Oficinas centrales de {{ config('cliente.nombre', 'CEFI') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Institución / Comunidad Beneficiada *</label>
                            <input type="text" name="institucion_beneficiada" class="form-control form-control-custom w-100" placeholder="Ej: Biblioteca Pública de San José" required>
                        </div>
                        
                        <hr class="my-4 border-secondary">
                        <h5 class="fw-bold text-success mb-2"><i class="bi bi-person-badge-fill me-2"></i>Datos del Supervisor de la Institución</h5>

                        <div class="col-md-6">
                            <label class="form-label-custom">Nombre Completo del Supervisor *</label>
                            <input type="text" name="nombre_supervisor" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Cédula del Supervisor *</label>
                            <input type="text" name="cedula_supervisor" class="form-control form-control-custom w-100" placeholder="Ej: 1-0234-0567" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Correo del Supervisor / Institución *</label>
                            <input type="email" name="email_institucion" class="form-control form-control-custom w-100" placeholder="supervisor@ejemplo.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Teléfono de la Institución *</label>
                            <input type="text" name="telefono_institucion" class="form-control form-control-custom w-100" placeholder="Ej: 2233-4455" required>
                        </div>

                        <div class="col-12 mt-5">
                            <button type="submit" class="btn btn-success py-3 px-5 rounded-3 fw-bold shadow w-100" style="background-color: var(--primary); border: none;">
                                <i class="bi bi-play-fill me-1"></i> Iniciar Registro de Actividades
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#crearBitacoraForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: "{{ route('tcu.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Bitácora Inicializada',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                window.location.href = "{{ route('tcu.edit', ':id') }}".replace(':id', response.bitacora_id);
                            });
                        } else {
                            Swal.fire('Error', response.error || 'No se pudo inicializar la bitácora.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Hubo un error de conectividad con el servidor.', 'error');
                    }
                });
            });
        });
    </script>
@endsection
