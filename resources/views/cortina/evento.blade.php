<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }} — Cortina Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top, #1e3a8a 0%, #090d16 75%);
            color: #ffffff;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .stage {
            max-width: 960px;
            padding: 2.5rem;
        }
        .logo-pulse {
            max-height: 110px;
            margin-bottom: 1.5rem;
            animation: float 3.5s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        .timer-box {
            display: inline-block;
            background: rgba(255,255,255,0.07);
            border: 2px solid rgba(255,255,255,0.16);
            border-radius: 28px;
            padding: 1.25rem 3.5rem;
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 800;
            color: #4ade80;
            letter-spacing: 3px;
            margin-top: 1.5rem;
            backdrop-filter: blur(12px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
        }
    </style>
</head>
<body>
    <div class="stage">
        <img src="{{ asset(config('cliente.logo_Institucional', 'imgs/logo_cefi_oficial.png')) }}"
             onerror="this.src='{{ asset('imgs/SVG/logo_blanco.svg') }}'"
             alt="Logo" class="logo-pulse">
        <h1 class="fw-extrabold display-5 mb-2">{{ $titulo }}</h1>
        <h3 class="fw-normal text-info mb-3">{{ $subtitulo }}</h3>
        @if(!empty($expositor))
            <p class="fs-4 text-white-50 mb-3"><i class="bi bi-person-workspace me-2"></i>{{ $expositor }}</p>
        @endif
        <div class="timer-box" id="timerDisplay">00:00</div>
    </div>

    <script>
        let seconds = {{ (int)$minutos * 60 }};
        const el = document.getElementById('timerDisplay');
        function renderTimer() {
            const m = String(Math.floor(seconds / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            el.textContent = m + ':' + s;
            if (seconds > 0) seconds--;
            else el.textContent = '¡EN VIVO!';
        }
        renderTimer();
        setInterval(renderTimer, 1000);
    </script>
</body>
</html>
