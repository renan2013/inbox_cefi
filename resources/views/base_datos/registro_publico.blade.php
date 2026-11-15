<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Oficial — {{ $evento->nombre }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .reg-card {
            background: #ffffff;
            color: #0f172a;
            border-radius: 24px;
            max-width: 480px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }
    </style>
</head>
<body>
    <div class="reg-card">
        <div class="p-4 text-center text-white" style="background: linear-gradient(135deg, #090d16 0%, #1e3a8a 100%);">
            <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold mb-2">
                <i class="bi bi-trophy-fill me-1"></i>Sorteo: {{ $evento->premio }}
            </span>
            <h4 class="fw-bold mb-1">{{ $evento->nombre }}</h4>
            <p class="small text-white-50 mb-0">{{ $evento->lugar ?: 'Complete sus datos para participar en vivo' }}</p>
        </div>

        <div class="p-4">
            @if(session('registro_exitoso'))
                <div class="text-center py-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success mb-3" style="width:72px;height:72px;font-size:2.2rem;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h4 class="fw-bold text-success">¡Registro Confirmado!</h4>
                    <p class="text-muted">{{ session('registro_exitoso') }}</p>
                </div>
            @else
                <form method="POST" action="{{ route('evento.registro_publico.store', $evento->codigo) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre Completo *</label>
                        <input type="text" name="nombre" class="form-control form-control-lg" placeholder="Su nombre y apellidos" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Número de WhatsApp / Celular *</label>
                        <input type="tel" name="telefono" class="form-control form-control-lg" placeholder="Ej: 88887777" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Carrera o Programa de Interés</label>
                        <select name="interes" class="form-select">
                            <option value="Información General">Información General</option>
                            @foreach($programas as $prog)
                                <option value="{{ $prog->nombre_programa }}">{{ $prog->nombre_programa }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow">
                        <i class="bi bi-Send-check-fill me-1"></i>Participar en el Sorteo Ahora
                    </button>
                </form>
            @endif
        </div>
    </div>
</body>
</html>
