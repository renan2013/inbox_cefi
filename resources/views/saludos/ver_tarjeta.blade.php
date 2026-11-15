<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarjeta Especial para {{ $tarjeta->destinatario }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <style>
        body {
            background: radial-gradient(circle at top, #1e3a8a 0%, #090d16 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .greeting-card {
            background: #fff;
            color: #0f172a;
            border-radius: 28px;
            max-width: 560px;
            width: 100%;
            overflow: hidden;
            text-align: center;
            box-shadow: 0 30px 60px rgba(0,0,0,0.45);
        }
    </style>
</head>
<body onload="if(typeof confetti==='function') confetti({particleCount:120,spread:80,origin:{y:0.6}});">
    <div class="greeting-card">
        <div class="p-4 bg-light border-bottom">
            <img src="{{ asset($tarjeta->ruta_imagen ?: 'imgs/logo_cefi_oficial.png') }}"
                 alt="Imagen" style="max-height: 180px; max-width: 100%; border-radius: 16px;">
        </div>
        <div class="p-4 p-md-5">
            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold mb-2">
                MENSAJE ESPECIAL
            </span>
            <h2 class="fw-extrabold mb-3">{{ $tarjeta->destinatario }}</h2>
            <p class="fs-5 text-secondary mb-4" style="line-height: 1.6;">"{{ $tarjeta->mensaje }}"</p>
            <hr class="w-25 mx-auto my-3">
            <div class="fw-bold text-primary">{{ $tarjeta->remitente }}</div>
        </div>
    </div>
</body>
</html>
