<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenida - {{ $curso->planEstudio->materia }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5fb230;
            --secondary: #004a99;
            --dark-bg: #0f172a;
        }
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Outfit', sans-serif;
            background-color: var(--dark-bg);
            color: #f8fafc;
            overflow: hidden;
        }
        .bg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top, rgba(95, 178, 48, 0.15) 0%, rgba(0, 74, 153, 0.15) 100%);
            z-index: 1;
        }
        .content-container {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 2rem;
        }
        .pulse-logo {
            max-width: 250px;
            margin-bottom: 2rem;
            animation: floatLogo 4s ease-in-out infinite;
        }
        @keyframes floatLogo {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .welcome-title {
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 4px;
            color: var(--primary);
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }
        .materia-name {
            font-size: 4rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -1px;
            background: linear-gradient(to right, #ffffff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }
        .profesor-name {
            font-size: 1.8rem;
            font-weight: 300;
            color: #cbd5e1;
            margin-bottom: 3.5rem;
        }
        .timer-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            padding: 2rem 4rem;
            border-radius: 2rem;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            margin-bottom: 3rem;
            animation: glowBorder 5s infinite alternate;
        }
        @keyframes glowBorder {
            0% { border-color: rgba(95, 178, 48, 0.2); }
            100% { border-color: rgba(0, 74, 153, 0.2); }
        }
        .countdown-digits {
            font-size: 5.5rem;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            letter-spacing: 2px;
            color: #fff;
        }
        .countdown-label {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--primary);
            font-weight: 600;
            margin-top: 0.5rem;
        }
        .social-bar {
            position: absolute;
            bottom: 30px;
            width: 100%;
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            z-index: 10;
        }
        .social-link {
            color: rgba(255,255,255,0.4);
            font-size: 1.5rem;
            transition: color 0.2s;
        }
        .social-link:hover {
            color: #fff;
        }
    </style>
</head>
<body>

    <div class="bg-overlay"></div>

    <div class="content-container">
        
        <!-- Logo -->
        <img src="https://unela.ac.cr/virtual/pluginfile.php/1/theme_adaptable/logo/1722880753/logo-unela.png" alt="UNELA" class="pulse-logo">

        <div class="welcome-title">Bienvenido a la clase virtual</div>
        
        <!-- Materia -->
        <h1 class="materia-name">{{ $curso->planEstudio->materia }}</h1>
        
        <!-- Profesor -->
        <div class="profesor-name">
            {{ $curso->profesor->nombre ?? 'Docente' }} {{ $curso->profesor->apellidos ?? '' }}
        </div>

        <!-- Temporizador -->
        <div class="timer-card">
            <div class="countdown-digits" id="timer">--:--</div>
            <div class="countdown-label" id="timerLabel">Iniciando en breve</div>
        </div>

        <!-- Instrucciones en pantalla -->
        <p class="text-white-50 small"><i class="bi bi-info-circle me-1"></i> Asegúrese de habilitar su audio y cámara al conectarse.</p>

    </div>

    <!-- Botones sociales / informativos inferiores -->
    <div class="social-bar">
        <a href="#" class="social-link"><i class="bi bi-globe"></i></a>
        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
    </div>

    <script>
        let totalSeconds = {{ $countdown_minutes }} * 60;
        const timerElement = document.getElementById('timer');
        const timerLabel = document.getElementById('timerLabel');

        function updateTimer() {
            if (totalSeconds <= 0) {
                timerElement.innerText = "¡CLASE INICIADA!";
                timerElement.style.fontSize = "3.5rem";
                timerElement.style.color = "#5fb230";
                timerLabel.innerText = "La sesión está en curso";
                clearInterval(interval);
                return;
            }

            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;
            
            const displayMin = minutes < 10 ? '0' + minutes : minutes;
            const displaySec = seconds < 10 ? '0' + seconds : seconds;

            timerElement.innerText = `${displayMin}:${displaySec}`;
            totalSeconds--;
        }

        updateTimer();
        const interval = setInterval(updateTimer, 1000);
    </script>
</body>
</html>
