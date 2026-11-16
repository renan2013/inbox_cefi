@php
    $siglasInst      = \App\Services\ClienteService::siglas();
    $nombreLegalInst = \App\Services\ClienteService::nombreLegal();
    $logoUrl         = \App\Services\ClienteService::logoUrl();
    $sitioWebHost    = parse_url(\App\Services\ClienteService::sitioWeb(), PHP_URL_HOST) ?: \App\Services\ClienteService::sitioWeb();
    $musicaActiva    = $musica ?? 1;
    $pistaActual     = $pista ?? 'ambient';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenida - {{ $titulo }} | {{ $siglasInst }}</title>
    <link rel="icon" type="image/png" href="{{ $logoUrl }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #5fb230;
            --primary-glow: rgba(95, 178, 48, 0.45);
            --secondary: #004a99;
            --secondary-glow: rgba(0, 74, 153, 0.45);
            --dark-bg: #0b131b;
            --card-bg: rgba(255, 255, 255, 0.04);
            --card-border: rgba(255, 255, 255, 0.1);
        }

        * {
            box-sizing: border-box;
            user-select: none;
        }

        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            background-color: var(--dark-bg);
            color: #f8fafc;
            overflow: hidden;
        }

        .ambient-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .bg-glow-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 15% 20%, rgba(0, 74, 153, 0.28) 0%, transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(95, 178, 48, 0.22) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(15, 23, 42, 0.75) 0%, #060b10 100%);
            z-index: 1;
        }

        .stage-container {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 1.5rem 2rem 5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .brand-logo-wrap {
            margin-bottom: 1.5rem;
            animation: floatLogo 5s ease-in-out infinite;
        }

        .brand-logo {
            max-height: 95px;
            max-width: 280px;
            height: auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 10px 22px;
            border-radius: 16px;
            filter: drop-shadow(0 12px 25px rgba(0, 0, 0, 0.6)) drop-shadow(0 0 35px rgba(95, 178, 48, 0.25));
        }

        @keyframes floatLogo {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .institution-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(95, 178, 48, 0.12);
            border: 1px solid rgba(95, 178, 48, 0.35);
            color: #8ce458;
            padding: 7px 22px;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            margin-bottom: 1.2rem;
            box-shadow: 0 0 25px rgba(95, 178, 48, 0.2);
        }

        .event-title {
            font-size: clamp(2.4rem, 5.2vw, 4.4rem);
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -1.2px;
            text-transform: uppercase;
            background: linear-gradient(135deg, #ffffff 30%, #cbd5e1 80%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.8rem;
            max-width: 1100px;
            text-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }

        .event-subtitle {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: clamp(1.1rem, 2vw, 1.55rem);
            font-weight: 400;
            color: #94a3b8;
            max-width: 850px;
            margin-bottom: 1.8rem;
            line-height: 1.5;
        }

        .timer-card {
            background: rgba(255, 255, 255, 0.035);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 1.8rem 4.5rem;
            border-radius: 2.2rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            animation: pulseGlow 4s infinite alternate;
            margin-bottom: 2rem;
            min-width: 380px;
        }

        @keyframes pulseGlow {
            0% {
                border-color: rgba(95, 178, 48, 0.25);
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4), 0 0 30px rgba(95, 178, 48, 0.12);
            }
            100% {
                border-color: rgba(0, 74, 153, 0.35);
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4), 0 0 35px rgba(0, 74, 153, 0.2);
            }
        }

        .countdown-digits {
            font-size: clamp(4.5rem, 9.5vw, 7.5rem);
            font-weight: 900;
            font-variant-numeric: tabular-nums;
            letter-spacing: 2px;
            color: #ffffff;
            line-height: 1;
            text-shadow: 0 0 30px rgba(255, 255, 255, 0.2);
        }

        .countdown-label {
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 3.5px;
            color: var(--primary);
            font-weight: 700;
            margin-top: 0.6rem;
        }

        .timer-actions {
            position: absolute;
            top: -16px;
            right: 25px;
            display: flex;
            gap: 6px;
            opacity: 0.2;
            transition: opacity 0.2s ease;
        }

        .timer-card:hover .timer-actions,
        .timer-actions:hover {
            opacity: 1;
        }

        .btn-timer-mini {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #e2e8f0;
            border-radius: 8px;
            padding: 2px 9px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-timer-mini:hover {
            background: var(--primary);
            color: #0b131b;
            border-color: var(--primary);
        }

        .live-status {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            color: #e2e8f0;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .live-dot {
            width: 12px;
            height: 12px;
            background-color: #ef4444;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 14px #ef4444;
            animation: liveBlink 1.4s infinite ease-in-out;
        }

        @keyframes liveBlink {
            0%, 100% { opacity: 0.3; transform: scale(0.85); }
            50% { opacity: 1; transform: scale(1.15); }
        }

        .footer-bar {
            position: absolute;
            bottom: 20px;
            width: 100%;
            left: 0;
            padding: 0 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: rgba(255, 255, 255, 0.45);
            font-size: 0.9rem;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .audio-dock {
            position: absolute;
            bottom: 22px;
            right: 35px;
            z-index: 20;
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 8px 18px;
            border-radius: 50px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
        }

        .audio-dock:hover {
            border-color: rgba(95, 178, 48, 0.4);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6), 0 0 15px rgba(95, 178, 48, 0.15);
        }

        .audio-btn {
            background: transparent;
            border: none;
            color: #ffffff;
            font-size: 1.25rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            transition: transform 0.2s, background 0.2s, color 0.2s;
        }

        .audio-btn:hover {
            transform: scale(1.1);
            background: rgba(255, 255, 255, 0.1);
            color: var(--primary);
        }

        .audio-btn.playing {
            color: var(--primary);
            animation: pulsePlay 2s infinite;
        }

        @keyframes pulsePlay {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); filter: drop-shadow(0 0 8px var(--primary)); }
        }

        .volume-slider {
            width: 85px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .track-select {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #cbd5e1;
            font-size: 0.82rem;
            padding: 4px 8px;
            border-radius: 20px;
            outline: none;
            cursor: pointer;
        }

        .track-select option {
            background: #0f172a;
            color: #fff;
        }

        .btn-fullscreen {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 4px 8px;
            transition: color 0.2s;
        }

        .btn-fullscreen:hover {
            color: #fff;
        }

        .audio-prompt-pill {
            position: absolute;
            top: 25px;
            right: 25px;
            z-index: 30;
            background: linear-gradient(135deg, rgba(95, 178, 48, 0.95), rgba(0, 74, 153, 0.95));
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.88rem;
            padding: 9px 20px;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4), 0 0 20px rgba(95, 178, 48, 0.3);
            display: flex;
            align-items: center;
            gap: 9px;
            transition: all 0.3s;
            animation: bounceIn 1s ease-out;
        }

        .audio-prompt-pill:hover {
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5), 0 0 30px rgba(95, 178, 48, 0.5);
        }

        @keyframes bounceIn {
            0% { transform: scale(0.8); opacity: 0; }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); opacity: 1; }
        }

        @media (max-width: 768px) {
            .timer-card {
                padding: 1.5rem 2rem;
                min-width: 90%;
            }
            .footer-bar {
                flex-direction: column;
                gap: 10px;
                text-align: center;
                bottom: 80px;
            }
            .audio-dock {
                bottom: 15px;
                right: 50%;
                transform: translateX(50%);
            }
        }
    </style>
</head>
<body>

    <div class="bg-glow-layer"></div>
    <canvas id="ambientCanvas" class="ambient-canvas"></canvas>

    @if($musicaActiva)
    <div class="audio-prompt-pill" id="audioPrompt" onclick="activarMusicaClick()">
        <i class="bi bi-music-note-beamed"></i>
        <span>Activar Música de Fondo</span>
    </div>
    @endif

    <div class="stage-container">
        <div class="brand-logo-wrap">
            <img src="{{ $logoUrl }}" alt="{{ $siglasInst }}" class="brand-logo" onerror="this.src='{{ asset('imgs/SVG/logo_blanco.svg') }}'">
        </div>

        <div class="institution-badge">
            <i class="bi bi-mortarboard-fill"></i> {{ $siglasInst }} Promoción & Extensión
        </div>

        <h1 class="event-title" id="eventTitleDisplay">{{ $titulo }}</h1>
        <div class="event-subtitle" id="eventSubtitleDisplay">{{ $subtitulo }}</div>

        @if(!empty($expositor))
            <p class="fs-5 text-white-50 mb-4"><i class="bi bi-person-workspace me-2"></i>{{ $expositor }}</p>
        @endif

        <div class="timer-card" id="timerBox">
            <div class="timer-actions">
                <button type="button" class="btn-timer-mini" onclick="ajustarMinutos(1)" title="Sumar 1 minuto">+1m</button>
                <button type="button" class="btn-timer-mini" onclick="ajustarMinutos(-1)" title="Restar 1 minuto">-1m</button>
                <button type="button" class="btn-timer-mini" id="btnPause" onclick="togglePausa()" title="Pausar o Continuar"><i class="bi bi-pause-fill"></i></button>
                <button type="button" class="btn-timer-mini" onclick="reiniciarTimer()" title="Reiniciar"><i class="bi bi-arrow-clockwise"></i></button>
            </div>

            <div class="countdown-digits" id="timerDigits">00:00</div>
            <div class="countdown-label" id="timerLabel">El evento iniciará en</div>
        </div>

        <div class="live-status">
            <span class="live-dot"></span>
            <span>Transmisión en Vivo &bull; {{ date('d/m/Y') }}</span>
        </div>
    </div>

    <div class="footer-bar">
        <div class="footer-brand">
            <i class="bi bi-globe2 text-success"></i> {{ $sitioWebHost }} &bull; {{ $nombreLegalInst }}
        </div>
        <div class="d-none d-md-block">
            <i class="bi bi-shield-check text-info me-1"></i> Transmisión Oficial &bull; Todos los derechos reservados
        </div>
    </div>

    <div class="audio-dock">
        <button type="button" class="audio-btn" id="btnAudioToggle" onclick="toggleAudio()" title="Reproducir / Pausar Música">
            <i class="bi bi-volume-mute" id="audioIcon"></i>
        </button>

        <select id="pistaSelect" class="track-select" onchange="cambiarPista(this.value)" title="Seleccionar Pista de Música">
            <option value="ambient" {{ $pistaActual === 'ambient' ? 'selected' : '' }}>🎵 Ambiente Relajante</option>
            <option value="piano" {{ $pistaActual === 'piano' ? 'selected' : '' }}>🎹 Piano Suave</option>
            <option value="corporate" {{ $pistaActual === 'corporate' ? 'selected' : '' }}>✨ Inspiración Corporativa</option>
            <option value="synth" {{ $pistaActual === 'synth' ? 'selected' : '' }}>⚡ Sintetizador Armónico (Offline)</option>
        </select>

        <input type="range" class="volume-slider" id="volumeControl" min="0" max="1" step="0.05" value="0.5" oninput="cambiarVolumen(this.value)" title="Volumen">

        <button type="button" class="btn-fullscreen" onclick="toggleFullscreen()" title="Pantalla Completa (F11)">
            <i class="bi bi-fullscreen" id="fsIcon"></i>
        </button>
    </div>

    <audio id="bgAudio" loop preload="auto"></audio>

    <script>
        let totalSeconds = {{ (int)$minutos * 60 }};
        const initialSeconds = totalSeconds;
        let isPaused = false;
        let isAudioPlaying = false;
        let audioMode = @json($pistaActual);
        let webAudioInterval = null;
        let webAudioCtx = null;
        let webAudioGain = null;

        const timerDigits = document.getElementById('timerDigits');
        const timerLabel = document.getElementById('timerLabel');
        const timerBox = document.getElementById('timerBox');
        const bgAudio = document.getElementById('bgAudio');
        const audioIcon = document.getElementById('audioIcon');
        const btnAudioToggle = document.getElementById('btnAudioToggle');
        const audioPrompt = document.getElementById('audioPrompt');
        const volumeControl = document.getElementById('volumeControl');

        const AUDIO_TRACKS = {
            ambient: "https://actions.google.com/sounds/v1/weather/rain_heavy.ogg",
            piano: "https://commondatastorage.googleapis.com/codeskulptor-demos/riceracer_assets/music/menu.ogg",
            corporate: "https://commondatastorage.googleapis.com/codeskulptor-assets/Epoq-Lepidoptera.ogg",
            synth: "synth"
        };

        function updateTimer() {
            if (isPaused) return;

            if (totalSeconds <= 0) {
                timerDigits.innerText = "¡EN VIVO!";
                timerDigits.style.color = "#5fb230";
                timerDigits.style.textShadow = "0 0 40px rgba(95, 178, 48, 0.7)";
                timerLabel.innerText = "BIENVENIDOS AL EVENTO";
                timerLabel.style.color = "#ffffff";
                timerBox.style.borderColor = "rgba(95, 178, 48, 0.8)";
                timerBox.style.boxShadow = "0 0 60px rgba(95, 178, 48, 0.4)";
                clearInterval(timerInterval);
                return;
            }

            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;
            timerDigits.innerText = `${minutes < 10 ? '0' + minutes : minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
            totalSeconds--;
        }

        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);

        function ajustarMinutos(delta) {
            totalSeconds = Math.max(0, totalSeconds + (delta * 60));
            updateTimer();
        }

        function togglePausa() {
            isPaused = !isPaused;
            const btnPause = document.getElementById('btnPause');
            if (isPaused) {
                btnPause.innerHTML = '<i class="bi bi-play-fill text-warning"></i>';
                timerLabel.innerText = "Temporizador en Pausa";
            } else {
                btnPause.innerHTML = '<i class="bi bi-pause-fill"></i>';
                timerLabel.innerText = "El evento iniciará en";
            }
        }

        function reiniciarTimer() {
            totalSeconds = initialSeconds;
            isPaused = false;
            document.getElementById('btnPause').innerHTML = '<i class="bi bi-pause-fill"></i>';
            timerLabel.innerText = "El evento iniciará en";
            timerDigits.style.color = "#ffffff";
            timerDigits.style.textShadow = "0 0 30px rgba(255, 255, 255, 0.2)";
            updateTimer();
        }

        function cambiarPista(pistaKey) {
            audioMode = pistaKey;
            detenerSintetizador();

            if (pistaKey === 'synth') {
                bgAudio.pause();
                if (isAudioPlaying) iniciarSintetizador();
            } else {
                bgAudio.src = AUDIO_TRACKS[pistaKey] || AUDIO_TRACKS.ambient;
                bgAudio.volume = parseFloat(volumeControl.value);
                if (isAudioPlaying) {
                    bgAudio.play().catch(() => iniciarSintetizador());
                }
            }
        }

        function toggleAudio() {
            if (isAudioPlaying) pausarMusica();
            else reproducirMusica();
        }

        function reproducirMusica() {
            isAudioPlaying = true;
            btnAudioToggle.classList.add('playing');
            audioIcon.className = "bi bi-volume-up-fill";
            if (audioPrompt) audioPrompt.style.display = 'none';

            if (audioMode === 'synth') {
                iniciarSintetizador();
            } else {
                if (!bgAudio.src) bgAudio.src = AUDIO_TRACKS[audioMode] || AUDIO_TRACKS.ambient;
                bgAudio.volume = parseFloat(volumeControl.value);
                const playPromise = bgAudio.play();
                if (playPromise !== undefined) {
                    playPromise.catch(() => iniciarSintetizador());
                }
            }
        }

        function pausarMusica() {
            isAudioPlaying = false;
            btnAudioToggle.classList.remove('playing');
            audioIcon.className = "bi bi-volume-mute";
            bgAudio.pause();
            detenerSintetizador();
        }

        function activarMusicaClick() {
            reproducirMusica();
            if (audioPrompt) {
                audioPrompt.style.opacity = '0';
                setTimeout(() => { audioPrompt.style.display = 'none'; }, 300);
            }
        }

        function cambiarVolumen(vol) {
            bgAudio.volume = parseFloat(vol);
            if (webAudioGain && webAudioCtx) {
                webAudioGain.gain.setValueAtTime(parseFloat(vol) * 0.15, webAudioCtx.currentTime);
            }
        }

        function iniciarSintetizador() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!webAudioCtx) webAudioCtx = new AudioCtx();
                if (webAudioCtx.state === 'suspended') webAudioCtx.resume();

                webAudioGain = webAudioCtx.createGain();
                webAudioGain.gain.setValueAtTime(parseFloat(volumeControl.value) * 0.15, webAudioCtx.currentTime);
                webAudioGain.connect(webAudioCtx.destination);

                const freqs = [130.81, 196.00, 293.66, 329.63];
                const oscs = [];
                freqs.forEach(freq => {
                    const osc = webAudioCtx.createOscillator();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, webAudioCtx.currentTime);

                    const lfo = webAudioCtx.createOscillator();
                    lfo.frequency.setValueAtTime(0.1 + Math.random() * 0.15, webAudioCtx.currentTime);
                    const lfoGain = webAudioCtx.createGain();
                    lfoGain.gain.setValueAtTime(1.5, webAudioCtx.currentTime);
                    lfo.connect(lfoGain);
                    lfoGain.connect(osc.frequency);
                    lfo.start();

                    osc.connect(webAudioGain);
                    osc.start();
                    oscs.push(osc);
                });
                webAudioInterval = oscs;
            } catch (e) {
                console.error("Web Audio API no soportado:", e);
            }
        }

        function detenerSintetizador() {
            if (webAudioInterval && Array.isArray(webAudioInterval)) {
                webAudioInterval.forEach(osc => {
                    try { osc.stop(); osc.disconnect(); } catch (e) {}
                });
                webAudioInterval = null;
            }
        }

        function toggleFullscreen() {
            const fsIcon = document.getElementById('fsIcon');
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => {
                    fsIcon.className = "bi bi-fullscreen-exit";
                }).catch(() => {});
            } else if (document.exitFullscreen) {
                document.exitFullscreen().then(() => {
                    fsIcon.className = "bi bi-fullscreen";
                });
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.code === 'Space') {
                e.preventDefault();
                togglePausa();
            } else if (e.key === 'm' || e.key === 'M') {
                toggleAudio();
            } else if (e.key === 'f' || e.key === 'F') {
                toggleFullscreen();
            }
        });

        const canvas = document.getElementById('ambientCanvas');
        const ctx = canvas.getContext('2d');
        let particles = [];

        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        class Particle {
            constructor() { this.reset(); }
            reset() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 2.5 + 0.5;
                this.speedX = (Math.random() - 0.5) * 0.4;
                this.speedY = (Math.random() - 0.5) * 0.4;
                this.alpha = Math.random() * 0.5 + 0.1;
                this.isGreen = Math.random() > 0.5;
            }
            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                if (this.x < 0 || this.x > canvas.width || this.y < 0 || this.y > canvas.height) this.reset();
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = this.isGreen
                    ? `rgba(95, 178, 48, ${this.alpha})`
                    : `rgba(0, 74, 153, ${this.alpha})`;
                ctx.fill();
            }
        }

        for (let i = 0; i < 45; i++) particles.push(new Particle());
        function animateCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach(p => { p.update(); p.draw(); });
            requestAnimationFrame(animateCanvas);
        }
        animateCanvas();

        cambiarPista(audioMode);
    </script>
</body>
</html>
