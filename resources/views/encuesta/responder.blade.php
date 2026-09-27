<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuesta de Evaluación Docente — {{ config('cliente.nombre', 'CEFI') }}</title>
    <meta name="description" content="Formulario de evaluación anónima del desempeño docente y calidad del curso. {{ config('cliente.nombre_legal', 'CEFI') }}.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #5fb230;
            --primary-dark: #3d8a1a;
            --inst-color: #2563eb;
            --doc-color: #5fb230;
            --bg: #f1f5f9;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); min-height: 100vh; }

        /* Header */
        .enc-header {
            background: linear-gradient(135deg, #1a2e1a 0%, #2d5a1b 60%, #5fb230 100%);
            padding: 2rem 0 3.5rem;
            position: relative;
            overflow: hidden;
        }
        .enc-header::after {
            content: '';
            position: absolute;
            bottom: -1px; left: 0; right: 0; height: 40px;
            background: var(--bg);
            border-radius: 40px 40px 0 0;
        }
        .enc-header .badge-unela {
            background: rgba(255,255,255,0.15); color: #fff;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 50px; padding: 4px 14px;
            font-size: 12px; letter-spacing: .5px; font-weight: 600;
        }
        .enc-header h1 { color: #fff; font-weight: 800; font-size: 1.8rem; }
        .info-card {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 14px; padding: 1rem 1.25rem;
            color: rgba(255,255,255,.9); font-size: .88rem;
        }
        .info-card .label { color: rgba(255,255,255,.6); font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; font-weight: 600; }
        .info-card .value { font-weight: 700; font-size: .95rem; color: #fff; }

        /* Progreso */
        .progress-track { background: #e2e8f0; border-radius: 50px; height: 8px; overflow: hidden; }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), #84cc16);
            border-radius: 50px;
            transition: width .5s cubic-bezier(.4,0,.2,1);
        }
        .step-indicators { display: flex; gap: 8px; justify-content: center; }
        .step-dot {
            width: 38px; height: 38px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 12px;
            transition: all .3s; border: 2px solid #e2e8f0;
            background: #fff; color: #94a3b8;
        }
        .step-dot.active { background: var(--primary); border-color: var(--primary); color: #fff; box-shadow: 0 0 0 4px rgba(95,178,48,.2); }
        .step-dot.done   { background: #dcfce7; border-color: #22c55e; color: #16a34a; }

        /* Secciones wizard */
        .section-card {
            background: #fff; border-radius: 20px; border: 1px solid #e2e8f0;
            overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.06);
            display: none;
        }
        .section-card.active { display: block; animation: fadeSlide .35s ease; }
        @keyframes fadeSlide { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }

        .section-header { padding: 1.25rem 1.75rem; border-bottom: 1px solid #f0f4f8; }
        .section-header h2 { font-size: 1.15rem; font-weight: 800; margin: 0; }

        /* Pregunta */
        .question-item {
            padding: 1rem 1.75rem; border-bottom: 1px solid #f8fafc;
        }
        .question-item:last-child { border-bottom: none; }
        .question-text { font-size: .92rem; font-weight: 600; color: #334155; display: flex; align-items: flex-start; gap: 8px; margin-bottom: .75rem; }
        .q-num {
            min-width: 26px; height: 26px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 800; color: #fff; flex-shrink: 0; margin-top: 1px;
        }

        /* Opciones de respuesta */
        .opciones-row { display: flex; gap: 8px; flex-wrap: wrap; }
        .opcion-btn {
            display: flex; flex-direction: column; align-items: center;
            padding: 10px 12px; border-radius: 12px; cursor: pointer;
            border: 2px solid #e2e8f0; background: #f8fafc;
            transition: all .2s; min-width: 80px; flex: 1;
            user-select: none;
        }
        .opcion-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
        .opcion-btn .emoji-val { font-size: 1.4rem; line-height: 1; }
        .opcion-btn .txt-val   { font-size: .78rem; font-weight: 700; margin-top: 4px; color: #475569; }
        .opcion-btn .pts-val   { font-size: .68rem; color: #94a3b8; margin-top: 2px; }
        .opcion-btn input[type="radio"] { display: none; }

        /* Estados seleccionados por puntaje */
        .opcion-btn.selected-25  { background: #fef2f2; border-color: #ef4444; }
        .opcion-btn.selected-25  .txt-val { color: #ef4444; }
        .opcion-btn.selected-50  { background: #fff7ed; border-color: #f97316; }
        .opcion-btn.selected-50  .txt-val { color: #f97316; }
        .opcion-btn.selected-75  { background: #fefce8; border-color: #eab308; }
        .opcion-btn.selected-75  .txt-val { color: #b45309; }
        .opcion-btn.selected-100 { background: #f0fdf4; border-color: #22c55e; }
        .opcion-btn.selected-100 .txt-val { color: #16a34a; }
        .opcion-btn.selected     { border-color: var(--primary); background: #f0fdf4; }
        .opcion-btn.selected .txt-val { color: var(--primary-dark); }

        /* Navegación */
        .nav-btns { display: flex; gap: 12px; justify-content: flex-end; padding: 1.5rem 1.75rem; }
        .btn-prev  { background: #f1f5f9; color: #475569; border: none; border-radius: 12px; padding: 12px 24px; font-weight: 600; }
        .btn-prev:hover { background: #e2e8f0; }
        .btn-next, .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff; border: none; border-radius: 12px;
            padding: 12px 28px; font-weight: 700; font-size: 15px;
            box-shadow: 0 4px 14px rgba(95,178,48,.35); transition: all .2s;
        }
        .btn-next:hover, .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(95,178,48,.4); }

        /* Comentarios */
        .comments-section { padding: 1.25rem 1.75rem; }
        .comments-section textarea {
            border: 2px solid #e2e8f0; border-radius: 12px; padding: 14px;
            font-size: .92rem; resize: vertical; transition: border-color .2s; width: 100%;
        }
        .comments-section textarea:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(95,178,48,.12); }

        /* Pantalla éxito */
        #success-screen { display:none; text-align:center; padding:4rem 2rem; background:#fff; border-radius:20px; border:1px solid #e2e8f0; }
        .success-anim {
            width: 90px; height: 90px; border-radius: 50%;
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem; font-size: 2.5rem;
            box-shadow: 0 0 0 12px rgba(34,197,94,.1);
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="enc-header">
        <div class="container" style="max-width: 780px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge-unela"><i class="bi bi-mortarboard-fill me-1"></i> {{ config('cliente.nombre', 'CEFI') }} Virtual</span>
                <span class="badge" style="background: rgba(0,0,0,0.25); color: #fff; font-size: 11px; border-radius: 50px;">
                    <i class="bi bi-shield-lock-fill me-1"></i> 100% Anónima
                </span>
            </div>
            <h1 class="mb-3">Evaluación del Curso y Desempeño Docente</h1>
            
            @if ($curso)
                <div class="row g-2">
                    <div class="col-sm-6">
                        <div class="info-card">
                            <div class="label"><i class="bi bi-book me-1"></i> Curso / Materia</div>
                            <div class="value">{{ $curso->planEstudio->materia ?? 'Materia' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="info-card">
                            <div class="label"><i class="bi bi-person-badge me-1"></i> Profesor Asignado</div>
                            <div class="value">{{ trim(($curso->profesor->titulo_academico ?? '') . ' ' . ($curso->profesor->nombre ?? '') . ' ' . ($curso->profesor->apellidos ?? '')) }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="container pb-5" style="max-width: 780px; margin-top: -1.5rem;">

        @if ($ya_respondio)
            <!-- Ya respondió -->
            <div class="card shadow-sm border-0 rounded-4 text-center p-5 bg-white">
                <div class="success-anim text-success"><i class="bi bi-check-lg"></i></div>
                <h3 class="fw-bold text-dark mb-2">¡Ya has completado esta evaluación!</h3>
                <p class="text-muted mb-4">Agradecemos profundamente tu tiempo y tus aportes para seguir mejorando.</p>
                <div>
                    <a href="{{ $moodle_return_url }}" class="btn btn-success rounded-pill px-4 py-2 fw-bold">
                        <i class="bi bi-arrow-left me-1"></i> Volver a Moodle
                    </a>
                </div>
            </div>
        @elseif (!$tiene_preguntas)
            <!-- Sin preguntas -->
            <div class="card shadow-sm border-0 rounded-4 text-center p-5 bg-white">
                <i class="bi bi-cone-striped fs-1 text-warning mb-3"></i>
                <h4 class="fw-bold text-dark mb-2">Encuesta no configurada</h4>
                <p class="text-muted mb-4">El banco de preguntas no contiene elementos activos en este momento.</p>
                <a href="{{ $moodle_return_url }}" class="btn btn-outline-secondary rounded-pill px-4">Regresar a Moodle</a>
            </div>
        @else
            <!-- Formulario Wizard -->
            <div class="card shadow-sm border-0 rounded-4 p-3 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="x-small fw-bold text-muted" id="step-label">Paso 1 de 3</span>
                    <span class="x-small fw-bold text-success" id="progress-pct">33%</span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" id="progress-bar" style="width: 33%;"></div>
                </div>
            </div>

            <form id="encuestaForm">
                @csrf
                <input type="hidden" name="id_curso" value="{{ $id_curso }}">

                <!-- SECCIÓN 1: INSTITUCIÓN -->
                @if ($preguntas_inst->isNotEmpty())
                    <div class="section-card active" id="sec-1">
                        <div class="section-header d-flex align-items-center gap-2" style="background: #eff6ff;">
                            <span class="badge bg-primary rounded-pill px-3 py-1">Sección 1</span>
                            <h2 class="text-primary mb-0"><i class="bi bi-building me-1"></i> Aspectos Institucionales y Plataforma</h2>
                        </div>
                        
                        @foreach ($preguntas_inst as $idx => $preg)
                            <div class="question-item">
                                <div class="question-text">
                                    <span class="q-num" style="background: var(--inst-color);">{{ $idx + 1 }}</span>
                                    <span>{{ $preg->texto }}</span>
                                </div>
                                <div class="opciones-row">
                                    @foreach ($preg->opciones as $op)
                                        <label class="opcion-btn" data-pts="{{ $op->puntaje }}">
                                            <input type="radio" name="resp[{{ $preg->id }}]" value="{{ $op->id }}" required>
                                            <span class="emoji-val">{{ $op->emoji }}</span>
                                            <span class="txt-val">{{ $op->texto }}</span>
                                            <span class="pts-val">{{ $op->puntaje }} pts</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="nav-btns">
                            <button type="button" class="btn-next" onclick="goToStep(2)">
                                Siguiente <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- SECCIÓN 2: DOCENTE -->
                @if ($preguntas_doc->isNotEmpty())
                    <div class="section-card" id="sec-2">
                        <div class="section-header d-flex align-items-center gap-2" style="background: #f0fdf4;">
                            <span class="badge bg-success rounded-pill px-3 py-1">Sección 2</span>
                            <h2 class="text-success mb-0"><i class="bi bi-person-check-fill me-1"></i> Desempeño del Docente y Curso</h2>
                        </div>

                        @foreach ($preguntas_doc as $idx => $preg)
                            <div class="question-item">
                                <div class="question-text">
                                    <span class="q-num" style="background: var(--doc-color);">{{ $idx + 1 }}</span>
                                    <span>{{ $preg->texto }}</span>
                                </div>
                                <div class="opciones-row">
                                    @foreach ($preg->opciones as $op)
                                        <label class="opcion-btn" data-pts="{{ $op->puntaje }}">
                                            <input type="radio" name="resp[{{ $preg->id }}]" value="{{ $op->id }}" required>
                                            <span class="emoji-val">{{ $op->emoji }}</span>
                                            <span class="txt-val">{{ $op->texto }}</span>
                                            <span class="pts-val">{{ $op->puntaje }} pts</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="nav-btns">
                            <button type="button" class="btn-prev" onclick="goToStep(1)">
                                <i class="bi bi-arrow-left me-1"></i> Anterior
                            </button>
                            <button type="button" class="btn-next" onclick="goToStep(3)">
                                Siguiente <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- SECCIÓN 3: COMENTARIOS Y ENVÍO -->
                <div class="section-card" id="sec-3">
                    <div class="section-header d-flex align-items-center gap-2" style="background: #faf5ff;">
                        <span class="badge bg-purple rounded-pill px-3 py-1" style="background: #9333ea; color: #fff;">Sección 3</span>
                        <h2 style="color: #9333ea;" class="mb-0"><i class="bi bi-chat-heart-fill me-1"></i> Comentarios y Sugerencias</h2>
                    </div>

                    <div class="comments-section">
                        <label class="form-label fw-bold text-dark small mb-2">
                            ¿Qué sugerencias o comentarios te gustaría compartir con respecto al docente o al curso? (Opcional)
                        </label>
                        <textarea name="comentarios" rows="4" placeholder="Escribe aquí tus observaciones constructivas..."></textarea>
                    </div>

                    <div class="nav-btns">
                        <button type="button" class="btn-prev" onclick="goToStep(2)">
                            <i class="bi bi-arrow-left me-1"></i> Anterior
                        </button>
                        <button type="button" class="btn-submit" id="btnSubmitForm" onclick="enviarEncuesta()">
                            <i class="bi bi-send-fill me-1"></i> ENVIAR EVALUACIÓN
                        </button>
                    </div>
                </div>

            </form>

            <!-- Pantalla de Éxito -->
            <div id="success-screen">
                <div class="success-anim text-success"><i class="bi bi-check-lg"></i></div>
                <h3 class="fw-bold text-dark mb-2">¡Evaluación Enviada con Éxito!</h3>
                <p class="text-muted mb-4">Tus respuestas han sido procesadas de forma 100% confidencial y anónima.</p>
                <div>
                    <a href="{{ $moodle_return_url }}" id="btnVolverMoodle" class="btn btn-success rounded-pill px-5 py-3 fw-bold shadow">
                        <i class="bi bi-mortarboard-fill me-2"></i> Continuar en Moodle
                    </a>
                </div>
            </div>

        @endif

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let currentStep = 1;
        const totalSteps = 3;

        // Selección de radio cards
        document.querySelectorAll('.opcion-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                const name = radio.name;
                
                document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
                    const p = r.closest('.opcion-btn');
                    p.className = 'opcion-btn';
                });

                radio.checked = true;
                const pts = parseInt(this.dataset.pts || '0');
                if ([25, 50, 75, 100].includes(pts)) {
                    this.classList.add(`selected-${pts}`);
                } else {
                    this.classList.add('selected');
                }
            });
        });

        function goToStep(step) {
            // Validar paso actual antes de avanzar
            if (step > currentStep) {
                const currentSec = document.getElementById(`sec-${currentStep}`);
                const requiredRadios = currentSec.querySelectorAll('input[type="radio"][required]');
                const groups = new Set();
                requiredRadios.forEach(r => groups.add(r.name));

                for (let group of groups) {
                    const checked = currentSec.querySelector(`input[name="${group}"]:checked`);
                    if (!checked) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Preguntas pendientes',
                            text: 'Por favor responde todas las preguntas de esta sección antes de continuar.',
                            confirmButtonColor: '#5fb230'
                        });
                        return;
                    }
                }
            }

            document.querySelectorAll('.section-card').forEach(s => s.classList.remove('active'));
            document.getElementById(`sec-${step}`).classList.add('active');

            currentStep = step;
            const pct = Math.round((step / totalSteps) * 100);
            document.getElementById('progress-bar').style.width = pct + '%';
            document.getElementById('progress-pct').innerText = pct + '%';
            document.getElementById('step-label').innerText = `Paso ${step} de ${totalSteps}`;

            window.scrollTo({ top: 120, behavior: 'smooth' });
        }

        function enviarEncuesta() {
            const form = document.getElementById('encuestaForm');
            const formData = new FormData(form);

            const btn = document.getElementById('btnSubmitForm');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';

            fetch("{{ route('encuesta.procesar') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> ENVIAR EVALUACIÓN';

                if (data.success) {
                    document.getElementById('encuestaForm').style.display = 'none';
                    document.getElementById('success-screen').style.display = 'block';
                    if (data.return_url) {
                        document.getElementById('btnVolverMoodle').href = data.return_url;
                    }
                    window.scrollTo({ top: 100, behavior: 'smooth' });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Atención',
                        text: data.message || 'Ocurrió un error al procesar la encuesta.',
                        confirmButtonColor: '#5fb230'
                    });
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> ENVIAR EVALUACIÓN';
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor. Intente nuevamente.',
                    confirmButtonColor: '#5fb230'
                });
            });
        }
    </script>
</body>
</html>
