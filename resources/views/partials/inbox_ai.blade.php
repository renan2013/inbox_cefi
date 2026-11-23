@if(Auth::check() && Auth::user()->id_rol != 3 && !(method_exists(Auth::user(), 'esAlumno') && Auth::user()->esAlumno()))
<!-- Widget Flotante de Asistente IA: Inbox AI -->
<style>
    .inbox-ai-fab {
        position: fixed;
        bottom: 26px;
        right: 24px;
        width: 66px;
        height: 66px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #0ea5e9 100%);
        color: white;
        border: 2px solid rgba(56, 189, 248, 0.55);
        box-shadow: 0 12px 30px rgba(14, 165, 233, 0.42), 0 0 0 0 rgba(56, 189, 248, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 9999;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        animation: aiFabPulse 2.8s infinite;
    }
    @keyframes aiFabPulse {
        0% { box-shadow: 0 12px 30px rgba(14, 165, 233, 0.42), 0 0 0 0 rgba(56, 189, 248, 0.45); }
        70% { box-shadow: 0 12px 30px rgba(14, 165, 233, 0.42), 0 0 0 14px rgba(56, 189, 248, 0); }
        100% { box-shadow: 0 12px 30px rgba(14, 165, 233, 0.42), 0 0 0 0 rgba(56, 189, 248, 0); }
    }
    .inbox-ai-fab:hover {
        transform: translateY(-4px) scale(1.08);
        box-shadow: 0 18px 36px rgba(14, 165, 233, 0.58);
    }
    .inbox-ai-fab .ai-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: linear-gradient(135deg, #10b981, #0ea5e9);
        color: #ffffff;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        padding: 2px 7px;
        border-radius: 50rem;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
    }

    /* Ventana de Chat IA */
    .inbox-ai-card {
        position: fixed;
        bottom: 104px;
        right: 24px;
        width: 410px;
        height: 560px;
        max-width: calc(100vw - 32px);
        max-height: calc(100vh - 130px);
        background: var(--card-dark, #ffffff);
        border-radius: 1.35rem;
        border: 1px solid rgba(56, 189, 248, 0.28);
        box-shadow: 0 24px 55px rgba(15, 23, 42, 0.34);
        display: none;
        flex-direction: column;
        z-index: 9999;
        overflow: hidden;
        animation: aiSlideUp 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @media (max-width: 576px) {
        .inbox-ai-card {
            right: 16px;
            bottom: 98px;
            max-width: calc(100vw - 32px);
        }
    }

    @keyframes aiSlideUp {
        from { opacity: 0; transform: translateY(14px) scale(0.97); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .inbox-ai-header {
        background: linear-gradient(135deg, #0b1120 0%, #1e293b 65%, #0f3a60 100%) !important;
        color: #ffffff !important;
        padding: 1rem 1.2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(56, 189, 248, 0.22);
    }

    .inbox-ai-avatar-wrap {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.22), rgba(16, 185, 129, 0.22));
        border: 1px solid rgba(56, 189, 248, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .inbox-ai-header-title {
        color: #ffffff !important;
        font-weight: 800 !important;
        font-size: 1rem !important;
        letter-spacing: -0.01em;
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
        max-width: 88%;
        padding: 0.85rem 1.05rem;
        border-radius: 1.1rem;
        font-size: 0.9rem;
        line-height: 1.5;
        word-wrap: break-word;
    }

    .ai-msg-bot {
        background: var(--card-dark, #ffffff);
        color: var(--text-light, #1e293b);
        border: 1px solid var(--border-dark, #e2e8f0);
        border-bottom-left-radius: 0.25rem;
        align-self: flex-start;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
    }

    .ai-msg-user {
        background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
        color: #ffffff;
        border-bottom-right-radius: 0.25rem;
        align-self: flex-end;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.22);
    }

    .ai-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-top: 0.6rem;
    }

    .ai-chip {
        background: rgba(14, 165, 233, 0.08);
        border: 1px solid rgba(14, 165, 233, 0.28);
        color: #0284c7;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 50rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .ai-chip:hover {
        background: #0ea5e9;
        border-color: #0ea5e9;
        color: #ffffff;
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
        background: #38bdf8;
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

<!-- Botón Flotante (FAB) con Robot IA SVG -->
<button type="button" class="inbox-ai-fab" id="inboxAiFab" title="Consultar a Inbox AI">
    <svg width="34" height="34" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <line x1="24" y1="6" x2="24" y2="13" stroke="#38bdf8" stroke-width="2.6" stroke-linecap="round"/>
        <circle cx="24" cy="5" r="3" fill="#34d399"/>
        <rect x="9" y="13" width="30" height="23" rx="8" fill="#0f172a" stroke="#38bdf8" stroke-width="2.4"/>
        <rect x="13" y="18" width="22" height="11" rx="5" fill="#1e293b"/>
        <circle cx="19" cy="23.5" r="2.7" fill="#38bdf8"/>
        <circle cx="29" cy="23.5" r="2.7" fill="#34d399"/>
        <path d="M18 32.5C20 34.2 28 34.2 30 32.5" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
        <rect x="5" y="20" width="4" height="8" rx="2" fill="#38bdf8"/>
        <rect x="39" y="20" width="4" height="8" rx="2" fill="#38bdf8"/>
    </svg>
    <span class="ai-badge">IA</span>
</button>

<!-- Ventana de Chat -->
<div class="inbox-ai-card" id="inboxAiCard">
    <!-- Header -->
    <div class="inbox-ai-header">
        <div class="d-flex align-items-center gap-2">
            <div class="inbox-ai-avatar-wrap">
                <svg width="26" height="26" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <line x1="24" y1="6" x2="24" y2="13" stroke="#38bdf8" stroke-width="3" stroke-linecap="round"/>
                    <circle cx="24" cy="5" r="3" fill="#34d399"/>
                    <rect x="9" y="13" width="30" height="23" rx="8" fill="#0f172a" stroke="#38bdf8" stroke-width="2.5"/>
                    <circle cx="19" cy="23.5" r="2.8" fill="#38bdf8"/>
                    <circle cx="29" cy="23.5" r="2.8" fill="#34d399"/>
                    <path d="M18 32.5C20 34.2 28 34.2 30 32.5" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="inbox-ai-header-title">Inbox AI</span>
                    <span class="badge rounded-pill fw-bold" style="background: linear-gradient(135deg, #10b981, #0ea5e9); color: #ffffff; font-size: 0.65rem;">IA Generativa</span>
                </div>
                <small class="d-block" style="color: rgba(255,255,255,0.75) !important; font-size: 0.74rem;">
                    <i class="bi bi-circle-fill me-1" style="font-size: 7px; color: #34d399;"></i> En línea · Asistente de Inbox y consultas generales
                </small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-1">
            <button type="button" class="btn btn-sm p-1" id="inboxAiReset" title="Nueva conversación" style="color: #ffffff !important; background: transparent; border: none;">
                <i class="bi bi-arrow-clockwise fs-6" style="color: #ffffff !important;"></i>
            </button>
            <button type="button" class="btn btn-sm p-1" id="inboxAiClose" title="Cerrar" style="color: #ffffff !important; background: transparent; border: none;">
                <i class="bi bi-x-lg fs-6" style="color: #ffffff !important;"></i>
            </button>
        </div>
    </div>

    <!-- Body / Chat Messages -->
    <div class="inbox-ai-body" id="inboxAiBody"></div>

    <!-- Footer / Input -->
    <div class="inbox-ai-footer">
        <input type="text" id="inboxAiInput" class="form-control rounded-pill border-1" placeholder="Pregúntame sobre Inbox o cualquier tema..." autocomplete="off">
        <button type="button" class="btn rounded-circle d-flex align-items-center justify-content-center p-0" id="inboxAiSend" style="width: 40px; height: 40px; background: linear-gradient(135deg, #0ea5e9, #10b981); border: none;">
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
        let chatHistory = [];

        if (!fab || !card) return;

        fab.addEventListener('click', () => {
            const isVisible = card.style.display === 'flex';
            card.style.display = isVisible ? 'none' : 'flex';
            if (!isVisible) input.focus();
        });

        closeBtn.addEventListener('click', () => card.style.display = 'none');

        resetBtn.addEventListener('click', () => {
            chatHistory = [];
            body.innerHTML = '';
            input.focus();
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

        function escapeHtml(str) {
            return str.replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        }

        function sendMessage() {
            const text = input.value.trim();
            if (!text) return;

            appendMessage(escapeHtml(text), 'user');
            input.value = '';

            const typingId = appendTypingIndicator();
            scrollToBottom();

            const historyToSend = chatHistory.slice(-8);

            fetch(n8nAiWebhookUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    query: text,
                    history: historyToSend,
                    timestamp: new Date().toISOString(),
                    origen: 'inbox_bpm_laravel_2.0'
                })
            })
            .then(res => res.json())
            .then(data => {
                removeTypingIndicator(typingId);
                let replyText = typeof data === 'string' ? data : (data.reply || data.output || data.message || JSON.stringify(data));

                if (replyText === "Workflow was started") {
                    replyText = "💡 <strong>Configuración en n8n:</strong><br>Recibí tu consulta. En tu n8n abre el nodo Webhook y selecciona <strong>Using 'Respond to Webhook' Node</strong>.";
                }
                appendMessage(replyText, 'bot');

                chatHistory.push({ role: 'user', text: text });
                const plainReply = replyText.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                if (plainReply) {
                    chatHistory.push({ role: 'model', text: plainReply.slice(0, 1200) });
                }
            })
            .catch(() => {
                removeTypingIndicator(typingId);
                appendMessage("No se pudo conectar en este momento con el asistente IA. Intenta de nuevo en unos segundos.", 'bot');
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
                    <span class="small text-muted">Inbox AI pensando</span>
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
@endif
