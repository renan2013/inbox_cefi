<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorteo en Vivo: {{ $evento->nombre }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <style>
        body {
            background: radial-gradient(circle at center, #1e293b 0%, #090d16 100%);
            color: #fff;
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            overflow-x: hidden;
        }
        .roulette-box {
            background: rgba(255,255,255,0.05);
            border: 2px solid rgba(255,255,255,0.12);
            border-radius: 28px;
            padding: 3rem 2rem;
            max-width: 820px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
            backdrop-filter: blur(12px);
        }
        .slot-display {
            background: #0f172a;
            border: 3px solid #38bdf8;
            border-radius: 20px;
            padding: 2rem 1.5rem;
            margin: 2rem 0;
            font-size: clamp(1.8rem, 4vw, 3rem);
            font-weight: 800;
            color: #38bdf8;
            min-height: 130px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: inset 0 0 30px rgba(56, 189, 248, 0.2);
            transition: all 0.2s;
        }
        .slot-display.winner-glow {
            border-color: #4ade80;
            color: #4ade80;
            box-shadow: 0 0 40px rgba(74, 222, 128, 0.4);
            transform: scale(1.03);
        }
    </style>
</head>
<body>
    <div class="roulette-box">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold">
                <i class="bi bi-trophy-fill me-1"></i>{{ $evento->premio }}
            </span>
            <span class="badge bg-info text-dark rounded-pill px-3 py-2 fw-bold">
                <i class="bi bi-people-fill me-1"></i>{{ $participantes->count() }} Participantes en Tómbola
            </span>
        </div>

        <h1 class="fw-extrabold mb-1">{{ $evento->nombre }}</h1>
        <p class="text-white-50 mb-0">{{ $evento->lugar ?: 'Sorteo Oficial en Vivo' }}</p>

        <div class="slot-display" id="slotDisplay">
            <span id="slotName">¡Listos para girar la tómbola!</span>
            <small id="slotPhone" class="fs-5 text-white-50 mt-1"></small>
        </div>

        <div class="d-flex flex-wrap justify-content-center gap-3">
            <button type="button" id="btnSpin" class="btn btn-success btn-lg rounded-pill px-5 py-3 fw-bold fs-4 shadow-lg" onclick="iniciarSorteo()" {{ $participantes->count() === 0 ? 'disabled' : '' }}>
                <i class="bi bi-stars me-2"></i>¡GIRAR RULETA AHORA!
            </button>
            <button type="button" class="btn btn-outline-light btn-lg rounded-pill px-4" onclick="window.location.reload()">
                <i class="bi bi-arrow-clockwise me-1"></i>Actualizar Inscritos
            </button>
        </div>
    </div>

    <script>
        const participantes = @json($participantes);

        function maskPhone(phone) {
            if (!phone || phone.length < 4) return phone;
            return phone.slice(0, -4).replace(/[0-9]/g, '*') + phone.slice(-4);
        }

        function iniciarSorteo() {
            if (!participantes.length) return;
            const btn = document.getElementById('btnSpin');
            const box = document.getElementById('slotDisplay');
            const nameEl = document.getElementById('slotName');
            const phoneEl = document.getElementById('slotPhone');

            btn.disabled = true;
            box.classList.remove('winner-glow');

            let ticks = 0;
            const totalTicks = 38;
            const interval = setInterval(() => {
                const rand = participantes[Math.floor(Math.random() * participantes.length)];
                nameEl.textContent = rand.nombre;
                phoneEl.textContent = maskPhone(rand.telefono) + (rand.interes ? ' • ' + rand.interes : '');
                ticks++;

                if (ticks >= totalTicks) {
                    clearInterval(interval);
                    const winner = participantes[Math.floor(Math.random() * participantes.length)];
                    nameEl.textContent = '🎉 ' + winner.nombre + ' 🎉';
                    phoneEl.textContent = maskPhone(winner.telefono) + (winner.interes ? ' • ' + winner.interes : '');
                    box.classList.add('winner-glow');
                    btn.disabled = false;

                    if (typeof confetti === 'function') {
                        confetti({ particleCount: 160, spread: 90, origin: { y: 0.6 } });
                    }

                    // Guardar ganador en base de datos
                    fetch("{{ route('base_datos.sorteo.ganador', $evento->id) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ id_participante: winner.id })
                    });
                }
            }, 90);
        }
    </script>
</body>
</html>
