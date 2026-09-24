@extends('layouts.app')

@section('title', 'Inbox BPM - Editar Solicitud')

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
        }

        .legend-custom {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.1rem;
            border-bottom: 2px solid var(--border-dark);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            width: 100%;
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

        .btn-submit {
            background-color: var(--primary);
            border: none;
            color: white;
            padding: 0.9rem;
            border-radius: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 8px 15px rgba(95, 178, 48, 0.25);
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('solicitudes.index') }}" class="text-decoration-none text-white-50">Solicitudes</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Editar (#{{ $solicitud->id }})</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Editar Solicitud Curso Libre</h1>
                <p class="text-white-50 mb-0">Modifique los datos registrados de la postulación de admisión.</p>
            </div>
            <div>
                <a href="{{ route('solicitudes.index') }}" class="btn btn-outline-light rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Solicitudes
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 rounded-4 mb-4" style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2) !important;">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card glass-card">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('solicitudes.update', $solicitud->id) }}" method="POST">
                    @csrf

                    <!-- SECCIÓN 1: DATOS PERSONALES -->
                    <fieldset class="mb-5">
                        <div class="legend-custom">1. Datos Personales</div>
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label for="nombre" class="form-label-custom">Nombre</label>
                                <input type="text" name="nombre" id="nombre" class="form-control form-control-custom w-100" required value="{{ old('nombre', $solicitud->nombre) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="primer_apellido" class="form-label-custom">Primer Apellido</label>
                                <input type="text" name="primer_apellido" id="primer_apellido" class="form-control form-control-custom w-100" required value="{{ old('primer_apellido', $solicitud->primer_apellido) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="segundo_apellido" class="form-label-custom">Segundo Apellido</label>
                                <input type="text" name="segundo_apellido" id="segundo_apellido" class="form-control form-control-custom w-100" value="{{ old('segundo_apellido', $solicitud->segundo_apellido) }}">
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label for="identificacion" class="form-label-custom">Identificación / Cédula</label>
                                <input type="text" name="identificacion" id="identificacion" class="form-control form-control-custom w-100" required value="{{ old('identificacion', $solicitud->identificacion) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="nacionalidad" class="form-label-custom">Nacionalidad</label>
                                <input type="text" name="nacionalidad" id="nacionalidad" class="form-control form-control-custom w-100" value="{{ old('nacionalidad', $solicitud->nacionalidad) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="sexo" class="form-label-custom">Género</label>
                                <select name="sexo" id="sexo" class="form-select form-select-custom w-100">
                                    <option value="M" {{ old('sexo', $solicitud->sexo) === 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('sexo', $solicitud->sexo) === 'F' ? 'selected' : '' }}>Femenino</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="fecha_nacimiento" class="form-label-custom">Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control form-control-custom w-100" value="{{ old('fecha_nacimiento', $solicitud->fecha_nacimiento ? $solicitud->fecha_nacimiento->format('Y-m-d') : '') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="lugar_nacimiento" class="form-label-custom">Lugar de Nacimiento</label>
                                <input type="text" name="lugar_nacimiento" id="lugar_nacimiento" class="form-control form-control-custom w-100" value="{{ old('lugar_nacimiento', $solicitud->lugar_nacimiento) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="estado_civil" class="form-label-custom">Estado Civil</label>
                                <select name="estado_civil" id="estado_civil" class="form-select form-select-custom w-100">
                                    <option value="Soltero" {{ old('estado_civil', $solicitud->estado_civil) === 'Soltero' ? 'selected' : '' }}>Soltero(a)</option>
                                    <option value="Casado" {{ old('estado_civil', $solicitud->estado_civil) === 'Casado' ? 'selected' : '' }}>Casado(a)</option>
                                    <option value="Viudo" {{ old('estado_civil', $solicitud->estado_civil) === 'Viudo' ? 'selected' : '' }}>Viudo(a)</option>
                                    <option value="Divorciado" {{ old('estado_civil', $solicitud->estado_civil) === 'Divorciado' ? 'selected' : '' }}>Divorciado(a)</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <!-- SECCIÓN 2: INTERÉS Y CONTACTO -->
                    <fieldset class="mb-5">
                        <div class="legend-custom">2. Programa y Contacto</div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="programa_deseado" class="form-label-custom">Curso Libre Deseado</label>
                                <select name="programa_deseado" id="programa_deseado" class="form-select form-select-custom w-100" required>
                                    <option value="">Seleccione el Curso...</option>
                                    @foreach ($programas as $p)
                                        <option value="{{ $p->nombre_programa }}" {{ old('programa_deseado', $solicitud->programa_deseado) == $p->nombre_programa ? 'selected' : '' }}>{{ $p->nombre_programa }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="profesion" class="form-label-custom">Profesión / Ocupación</label>
                                <input type="text" name="profesion" id="profesion" class="form-control form-control-custom w-100" value="{{ old('profesion', $solicitud->profesion) }}">
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="telefono" class="form-label-custom">Número de Teléfono</label>
                                <input type="text" name="telefono" id="telefono" class="form-control form-control-custom w-100" value="{{ old('telefono', $solicitud->telefono) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label-custom">Correo Electrónico</label>
                                <input type="email" name="email" id="email" class="form-control form-control-custom w-100" value="{{ old('email', $solicitud->email) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label-custom">Dirección de Domicilio</label>
                            <textarea name="direccion" id="direccion" class="form-control form-control-custom w-100" rows="3">{{ old('direccion', $solicitud->direccion) }}</textarea>
                        </div>
                    </fieldset>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-submit py-3 shadow">
                            <i class="bi bi-save me-2"></i> Guardar Cambios
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
@endsection
