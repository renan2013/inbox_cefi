<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta Ya Firmada - {{ config('cliente.nombre', 'CEFI') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .success-card { max-width: 650px; background: white; border-radius: 1.5rem; box-shadow: 0 20px 50px rgba(0,0,0,0.06); }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="success-card p-5 text-center">
        <div class="mb-4">
            <i class="bi bi-patch-check-fill text-success" style="font-size: 4.5rem;"></i>
        </div>
        <h3 class="fw-bold mb-2">¡Boleta de Matrícula Ya Firmada!</h3>
        <p class="text-muted mb-4">
            La boleta <strong>{{ $boleta->numero_boleta }}</strong> ya cuenta con su firma digital registrada exitosamente el 
            {{ $boleta->fecha_firma ? $boleta->fecha_firma->format('d/m/Y h:i A') : 'recientemente' }}.
        </p>
        <div class="alert alert-light border rounded-3 p-3 text-start small mb-4">
            <div><strong>Estudiante:</strong> {{ $boleta->estudiante->nombre ?? '' }} {{ $boleta->estudiante->apellidos ?? '' }}</div>
            <div><strong>Período:</strong> {{ $boleta->periodo }}</div>
            <div><strong>Estado Actual:</strong> <span class="badge bg-success-subtle text-success border border-success-subtle">{{ strtoupper(str_replace('_', ' ', $boleta->estado)) }}</span></div>
        </div>
        <p class="small text-muted mb-0">El departamento administrativo ha recibido su documento para la oficialización de sus asignaturas.</p>
    </div>
</body>
</html>
