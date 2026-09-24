<!-- Widget Flotante de Chat Inteligente: Inbox AI 2.0 (Laravel View) -->
<style>
    .inbox-ai-fab {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary, #5fb230) 0%, #3b82f6 100%);
        color: white;
        border: none;
        box-shadow: 0 10px 25px rgba(95, 178, 48, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        cursor: pointer;
        z-index: 9999;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .inbox-ai-fab:hover {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 15px 30px rgba(95, 178, 48, 0.5);
    }
    .inbox-ai-fab .ai-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: #ef4444;
        color: white;
        font-size: 0.65rem;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 50rem;
        border: 2px solid #ffffff;
    }

    /* Modal de Chat */
    .inbox-ai-card {
        position: fixed;
        bottom: 96px;
        right: 24px;
        width: 380px;
        height: 520px;
        max-width: calc(100vw - 32px);
        max-height: calc(100vh - 120px);
        background: var(--card-dark, #ffffff);
        border-radius: 1.5rem;
        border: 1px solid var(--border-dark, #e2e8f0);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        display: none;
        flex-direction: column;
        z-index: 9999;
        overflow: hidden;
        animation: aiSlideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes aiSlideUp {
        from { opacity: 0; transform: translateY(20px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .inbox-ai-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
        color: #ffffff !important;
        padding: 1.15rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .inbox-ai-header-title {
        color: #ffffff !important;
        font-weight: 800 !important;
        font-size: 1rem !important;
    }

    .inbox-ai-body {
        flex: 1;
        padding: 1rem;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        background: var(--bg-dark, #f8fafc);
    }

    .ai-msg {
        max-width: 85%;
        padding: 0.85rem 1.1rem;
        border-radius: 1.15rem;
        font-size: 0.9rem;
        line-height: 1.45;
        word-wrap: break-word;
    }

    .ai-msg-bot {
        background: var(--card-dark, #ffffff);
        color: var(--text-light, #1e293b);
        border: 1px solid var(--border-dark, #e2e8f0);
        border-bottom-left-radius: 0.25rem;
        align-self: flex-start;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .ai-msg-user {
        background: linear-gradient(135deg, var(--primary, #5fb230) 0%, #4e9a26 100%);
        color: white;
        border-bottom-right-radius: 0.25rem;
        align-self: flex-end;
    }

    .ai-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-top: 0.5rem;
    }

    .ai-chip {
        background: var(--card-dark, #ffffff);
        border: 1.5px solid var(--border-dark, #cbd5e1);
        color: var(--text-light, #334155);
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 50rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .ai-chip:hover {
        background: rgba(95, 178, 48, 0.15);
        border-color: var(--primary, #5fb230);
        color: var(--primary, #5fb230);
    }

    .inbox-ai-footer {
        padding: 0.75rem 1rem;
        background: var(--card-dark, #ffffff);
        border-top: 1px solid var(--border-dark, #e2e8f0);
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .typing-indicator {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .typing-dot {
        width: 6px;
        height: 6px;
        background: #94a3b8;
        border-radius: 50%;
        animation: typingDot 1.4s infinite ease-in-out;
    }

    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }

    @keyframes typingDot {
        0%, 60%, 100% { transform: translateY(0); }
        30% { transform: translateY(-5px); }
    }
</style>

<!-- Botón Flotante (FAB) -->
<button type="button" class="inbox-ai-fab" id="inboxAiFab" title="Consultar a Inbox AI 2.0">
    <i class="bi bi-robot"></i>
    <span class="ai-badge">2.0</span>
</button>

<!-- Ventana de Chat -->
<div class="inbox-ai-card" id="inboxAiCard">
    <!-- Header -->
    <div class="inbox-ai-header">
        <div class="d-flex align-items-center gap-2">
            <div class="p-2 rounded-circle text-white d-flex align-items-center justify-content-center" style="width:36px; height:36px; background-color: var(--primary, #5fb230) !important;">
                <i class="bi bi-robot fs-5 text-white"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <img src="{{ asset('imgs/SVG/logo_blanco.svg') }}" alt="Inbox Logo" style="height: 20px; width: auto;">
                    <span class="inbox-ai-header-title">AI</span>
                    <span class="badge bg-success rounded-pill x-small fw-bold" style="background-color: var(--primary, #5fb230) !important; color: #ffffff !important;">2.0</span>
                </div>
                <small class="x-small" style="color: rgba(255,255,255,0.7) !important;"><i class="bi bi-circle-fill text-success me-1" style="font-size: 8px;"></i> Asistente VPS n8n</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-1">
            <button type="button" class="btn btn-sm text-white p-1 hover-white" id="inboxAiReset" title="Reiniciar chat">
                <i class="bi bi-arrow-clockwise fs-6 text-white"></i>
            </button>
            <button type="button" class="btn btn-sm text-white p-1 hover-white" id="inboxAiClose">
                <i class="bi bi-x-lg fs-6 text-white"></i>
            </button>
        </div>
    </div>

    <!-- Body / Chat Messages -->
    <div class="inbox-ai-body" id="inboxAiBody">
        <div class="ai-msg ai-msg-bot">
            ¡Hola! 👋 Soy <strong>Inbox AI 2.0</strong>, tu copiloto inteligente en Laravel conectado al servidor VPS n8n.
            <br><br>
            ¿En qué consulta del sistema te puedo asistir hoy?
            
            <div class="ai-chips">
                <div class="ai-chip" onclick="inboxAiAsk('¿Cuántos estudiantes están en mora?')">📊 Morosidad hoy</div>
                <div class="ai-chip" onclick="inboxAiAsk('¿Qué cursos están activos?')">🎓 Cursos Activos</div>
                <div class="ai-chip" onclick="inboxAiAsk('¿Cómo creo una boleta?')">❓ Ayuda de Boletas</div>
            </div>
        </div>
    </div>

    <!-- Footer / Input -->
    <div class="inbox-ai-footer">
        <input type="text" id="inboxAiInput" class="form-control rounded-pill border-1" placeholder="Pregunta algo a Inbox AI 2.0..." autocomplete="off">
        <button type="button" class="btn btn-success rounded-circle d-flex align-items-center justify-content-center p-0" id="inboxAiSend" style="width: 40px; height: 40px; background-color: var(--primary, #5fb230) !important; border: none;">
            <i class="bi bi-send-fill text-white fs-6"></i>
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fab = document.getElementById('inboxAiFab');
        const card = document.getElementById('inboxAiCard');
        const closeBtn = document.getElementById('inboxAiClose');
        const resetBtn = document.getElementById('inboxAiReset');
        const input = document.getElementById('inboxAiInput');
        const sendBtn = document.getElementById('inboxAiSend');
        const body = document.getElementById('inboxAiBody');

        const n8nAiWebhookUrl = '{{ route('inbox_ai.chat') }}';
        const csrfToken = '{{ csrf_token() }}';

        if (!fab || !card) return;

        fab.addEventListener('click', () => {
            const isVisible = card.style.display === 'flex';
            card.style.display = isVisible ? 'none' : 'flex';
            if (!isVisible) input.focus();
        });

        closeBtn.addEventListener('click', () => card.style.display = 'none');

        resetBtn.addEventListener('click', () => {
            body.innerHTML = `
                <div class="ai-msg ai-msg-bot">
                    ¡Hola! 👋 Soy <strong>Inbox AI 2.0</strong>, tu copiloto inteligente en Laravel conectado al servidor VPS n8n.
                    <br><br>
                    ¿En qué consulta del sistema te puedo asistir hoy?
                    <div class="ai-chips">
                        <div class="ai-chip" onclick="inboxAiAsk('¿Cuántos estudiantes están en mora?')">📊 Morosidad hoy</div>
                        <div class="ai-chip" onclick="inboxAiAsk('¿Qué cursos están activos?')">🎓 Cursos Activos</div>
                        <div class="ai-chip" onclick="inboxAiAsk('¿Cómo creo una boleta?')">❓ Ayuda de Boletas</div>
                    </div>
                </div>
            `;
        });

        window.inboxAiAsk = function(question) {
            input.value = question;
            sendMessage();
        };

        sendBtn.addEventListener('click', sendMessage);

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });

        function sendMessage() {
            const text = input.value.trim();
            if (!text) return;

            appendMessage(text, 'user');
            input.value = '';

            const typingId = appendTypingIndicator();
            scrollToBottom();

            fetch(n8nAiWebhookUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    query: text,
                    timestamp: new Date().toISOString(),
                    origen: 'inbox_bpm_laravel_2.0'
                })
            })
            .then(res => res.json())
            .then(data => {
                removeTypingIndicator(typingId);
                let replyText = typeof data === 'string' ? data : (data.reply || data.output || data.message || JSON.stringify(data));
                
                if (replyText === "Workflow was started") {
                    replyText = "💡 **Configuración en n8n:**<br>Recibí tu consulta. Por favor en tu n8n abre el primer nodo (<strong>Webhook Inbox AI</strong>), cambia el campo <strong>Respond</strong> a <strong>Using 'Respond to Webhook' Node</strong> y guarda cambios para ver la respuesta completa en tiempo real.";
                }
                appendMessage(replyText, 'bot');
            })
            .catch(err => {
                removeTypingIndicator(typingId);
                let fallbackMsg = "Te estoy escuchando. En este momento el webhook de n8n VPS (`/inbox-ai-chat`) está inicializando.";
                if (text.toLowerCase().includes('mora') || text.toLowerCase().includes('deudores')) {
                    fallbackMsg = "📊 **Resumen de Morosidad (Inbox 2.0):**<br>Actualmente el sistema registra cuotas pendientes. Puedes consultar el listado en el <a href='{{ route('boletas.morosidad') }}'>Control de Morosidad</a>.";
                } else if (text.toLowerCase().includes('curso') || text.toLowerCase().includes('activo')) {
                    fallbackMsg = "🎓 **Cursos Activos (Inbox 2.0):**<br>Puedes revisar la oferta académica y notificaciones en el panel de <a href='{{ route('mis_cursos.index') }}'>Supervisión de Cursos</a>.";
                } else if (text.toLowerCase().includes('boleta') || text.toLowerCase().includes('crear')) {
                    fallbackMsg = "💡 **Ayuda de Boletas (Inbox 2.0):**<br>Para emitir una boleta de pago, dirígete al menú **Registro -> Generar Boleta de Pago**.";
                }
                appendMessage(fallbackMsg, 'bot');
            });
        }

        function appendMessage(msg, sender) {
            const div = document.createElement('div');
            div.className = `ai-msg ai-msg-${sender}`;
            div.innerHTML = msg;
            body.appendChild(div);
            scrollToBottom();
        }

        function appendTypingIndicator() {
            const id = 'typing-' + Date.now();
            const div = document.createElement('div');
            div.id = id;
            div.className = 'ai-msg ai-msg-bot';
            div.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted">Inbox AI 2.0 pensando</span>
                    <div class="typing-indicator">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                </div>
            `;
            body.appendChild(div);
            return id;
        }

        function removeTypingIndicator(id) {
            const el = document.getElementById(id);
            if (el) el.remove();
        }

        function scrollToBottom() {
            body.scrollTop = body.scrollHeight;
        }
    });
</script>
