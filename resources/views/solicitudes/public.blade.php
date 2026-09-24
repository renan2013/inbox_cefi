<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNELA - Solicitud de Inscripción Curso Libre</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #5fb230;
            --primary-dark: #4e9a26;
            --bg-dark: #0f172a;
            --card-dark: #1e293b;
            --border-dark: #334155;
            --text-light: #f8fafc;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .public-container {
            max-width: 800px;
            width: 100%;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .brand-header h2 {
            font-weight: 700;
            color: var(--primary);
        }

        .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            padding: 3rem;
        }

        .legend-custom {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.15rem;
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
            padding: 1rem;
            border-radius: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(95, 178, 48, 0.3);
        }
    </style>
</head>
<body>

    <div class="public-container">
        
        <div class="brand-header animate__animated animate__fadeIn">
            <h2 class="mb-1"><i class="bi bi-mortarboard-fill me-2"></i>UNELA</h2>
            <p class="text-white-50">Formulario Oficial de Matrícula para Cursos Libres</p>
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

        <div class="card glass-card animate__animated animate__fadeInUp">
            <form action="{{ route('solicitudes.public.store') }}" method="POST">
                @csrf

                <!-- SECCIÓN 1: DATOS PERSONALES -->
                <fieldset class="mb-5">
                    <div class="legend-custom">1. Datos Personales</div>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <label for="nombre" class="form-label-custom">Nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control form-control-custom w-100" placeholder="Su nombre" required value="{{ old('nombre') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="primer_apellido" class="form-label-custom">Primer Apellido</label>
                            <input type="text" name="primer_apellido" id="primer_apellido" class="form-control form-control-custom w-100" placeholder="Primer apellido" required value="{{ old('primer_apellido') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="segundo_apellido" class="form-label-custom">Segundo Apellido</label>
                            <input type="text" name="segundo_apellido" id="segundo_apellido" class="form-control form-control-custom w-100" placeholder="Segundo apellido" value="{{ old('segundo_apellido') }}">
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <label for="identificacion" class="form-label-custom">Identificación / Cédula / Residencia</label>
                            <input type="text" name="identificacion" id="identificacion" class="form-control form-control-custom w-100" placeholder="Número de Cédula" required value="{{ old('identificacion') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="nacionalidad" class="form-label-custom">Nacionalidad</label>
                            <input type="text" name="nacionalidad" id="nacionalidad" class="form-control form-control-custom w-100" placeholder="Ej: Costarricense" value="{{ old('nacionalidad') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="sexo" class="form-label-custom">Género</label>
                            <select name="sexo" id="sexo" class="form-select form-select-custom w-100">
                                <option value="M" {{ old('sexo') === 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('sexo') === 'F' ? 'selected' : '' }}>Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label for="fecha_nacimiento" class="form-label-custom">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control form-control-custom w-100" value="{{ old('fecha_nacimiento') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="lugar_nacimiento" class="form-label-custom">Lugar de Nacimiento</label>
                            <input type="text" name="lugar_nacimiento" id="lugar_nacimiento" class="form-control form-control-custom w-100" placeholder="Provincia, País" value="{{ old('lugar_nacimiento') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="estado_civil" class="form-label-custom">Estado Civil</label>
                            <select name="estado_civil" id="estado_civil" class="form-select form-select-custom w-100">
                                <option value="Soltero" {{ old('estado_civil') === 'Soltero' ? 'selected' : '' }}>Soltero(a)</option>
                                <option value="Casado" {{ old('estado_civil') === 'Casado' ? 'selected' : '' }}>Casado(a)</option>
                                <option value="Viudo" {{ old('estado_civil') === 'Viudo' ? 'selected' : '' }}>Viudo(a)</option>
                                <option value="Divorciado" {{ old('estado_civil') === 'Divorciado' ? 'selected' : '' }}>Divorciado(a)</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <!-- SECCIÓN 2: INTERÉS Y CONTACTO -->
                <fieldset class="mb-5">
                    <div class="legend-custom">2. Programa de Interés y Contacto</div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label for="programa_deseado" class="form-label-custom">Curso Libre a Matricular</label>
                            <select name="programa_deseado" id="programa_deseado" class="form-select form-select-custom w-100" required>
                                <option value="">Seleccione el Curso...</option>
                                @foreach ($programas as $p)
                                    <option value="{{ $p->nombre_programa }}" {{ old('programa_deseado') == $p->nombre_programa ? 'selected' : '' }}>{{ $p->nombre_programa }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="profesion" class="form-label-custom">Profesión / Ocupación</label>
                            <input type="text" name="profesion" id="profesion" class="form-control form-control-custom w-100" placeholder="Su ocupación" value="{{ old('profesion') }}">
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label for="telefono" class="form-label-custom">Número de Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control form-control-custom w-100" placeholder="Ej: 8888-8888" value="{{ old('telefono') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label-custom">Correo Electrónico</label>
                            <input type="email" name="email" id="email" class="form-control form-control-custom w-100" placeholder="correo@ejemplo.com" value="{{ old('email') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="direccion" class="form-label-custom">Dirección de Domicilio Exacta</label>
                        <textarea name="direccion" id="direccion" class="form-control form-control-custom w-100" rows="3" placeholder="Dirección detallada para entrega de material...">{{ old('direccion') }}</textarea>
                    </div>
                </fieldset>

                <div class="d-grid">
                    <button type="submit" class="btn btn-submit py-3 shadow">
                        <i class="bi bi-send-check-fill me-2"></i> Enviar Formulario de Inscripción
                    </button>
                </div>

            </form>
        </div>

    </div>

</body>
</html>
