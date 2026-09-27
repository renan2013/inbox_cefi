@extends('layouts.app')

@section('title', 'Inbox BPM - Supervisión de Cursos')

@section('styles')
    <style>
        .page-header {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .form-select-custom {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 0.75rem;
            color: var(--text-light);
            padding: 0.5rem 2.5rem 0.5rem 1rem !important;
            transition: all 0.3s;
        }

        .form-select-custom:focus {
            background-color: var(--card-dark);
            border-color: var(--primary);
            color: var(--text-light);
            outline: none;
        }

        /* Table design */
        .table-custom {
            --bs-table-bg: transparent !important;
            --bs-table-color: var(--text-light) !important;
            --bs-table-border-color: var(--border-dark) !important;
            --bs-table-hover-bg: rgba(255, 255, 255, 0.03) !important;
            --bs-table-hover-color: var(--text-light) !important;
            color: var(--text-light) !important;
            background-color: transparent !important;
            margin-bottom: 0;
        }

        [data-theme="light"] .table-custom {
            --bs-table-hover-bg: rgba(0, 0, 0, 0.03) !important;
        }

        .table-custom td, .table-custom th {
            background-color: transparent !important;
            color: inherit !important;
            border-bottom: 1px solid var(--border-dark) !important;
            padding: 1rem 1.25rem;
            vertical-align: middle;
        }

        .table-header-row th {
            color: var(--text-muted) !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .task-title-text {
            color: var(--text-light) !important;
            font-weight: 700;
        }

        .task-subtitle-text {
            color: var(--text-muted) !important;
        }

        /* Check toggles styles */
        .check-toggle {
            cursor: pointer;
            font-size: 1.25rem;
            transition: transform 0.2s, color 0.2s;
        }
        .check-toggle:hover {
            transform: scale(1.2);
        }
        .check-toggle.checked {
            color: var(--primary);
        }
        .check-toggle.unchecked {
            color: rgba(100, 116, 139, 0.25);
        }

        /* Weekly columns */
        .day-column {
            min-width: 260px;
            flex: 1;
            background-color: rgba(30, 41, 59, 0.3);
            border: 1px solid var(--border-dark);
            border-radius: 1.25rem;
            padding: 1.25rem;
        }

        [data-theme="light"] .day-column {
            background-color: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
        }

        .day-header {
            font-weight: 800;
            color: var(--text-light);
            border-bottom: 3px solid var(--primary);
            padding-bottom: 8px;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
        }

        .course-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .course-card:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(95, 178, 48, 0.15);
        }

        .btn-view-toggle {
            border: 1px solid var(--border-dark);
            background: transparent;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.4rem 1rem;
            transition: all 0.2s ease;
        }

        .btn-view-toggle.active {
            background-color: var(--primary) !important;
            color: white !important;
            border-color: var(--primary) !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-5 px-md-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Supervisión de Cursos</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-end flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1 task-title-text">Supervisión de Cursos</h1>
                <p class="task-subtitle-text mb-0">Seguimiento de calidad académica y cumplimiento de hitos de Inbox BPM.</p>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <div class="btn-group rounded-pill p-1 border border-secondary" role="group" aria-label="Vista de Supervisión">
                    <button type="button" class="btn btn-view-toggle rounded-pill px-3 active" id="btn-view-tabla" onclick="switchView('tabla')">
                        <i class="bi bi-table me-1"></i> Tabla
                    </button>
                    <button type="button" class="btn btn-view-toggle rounded-pill px-3" id="btn-view-semana" onclick="switchView('semana')">
                        <i class="bi bi-calendar-week me-1"></i> Semana
                    </button>
                </div>

                <form action="{{ route('supervision.index') }}" method="GET">
                    <select name="periodo" class="form-select form-select-custom" onchange="this.form.submit()">
                        @foreach ($periodos as $p)
                            <option value="{{ $p }}" {{ $p === $periodo_actual ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <!-- ALERTAS FLASH DE ACCIONES -->
        <div id="alertContainer"></div>

        <!-- VIEW TABLA -->
        <div id="view-tabla" class="card glass-card border-0">
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle">
                    <thead>
                        <tr class="table-header-row">
                            <th class="ps-4" style="width: 25%;">Curso / Profesor</th>
                            <th class="text-center" title="Recursos Didácticos">Rec.</th>
                            <th class="text-center" title="Portada Sílabo">Port.</th>
                            <th class="text-center" title="Mapeo Moodle">Mood.</th>
                            <th class="text-center" title="Sincronización Estudiantes">Est.</th>
                            <th class="text-center" title="Actividades Habilitadas">Act.</th>
                            <th class="text-center" title="Calificaciones Parciales">Cal.</th>
                            <th class="text-center" title="Acta Final Firmada">Acta</th>
                            <th class="text-center" title="Honorarios y Hitos Listos">Pago</th>
                            <th class="text-center">Notificado</th>
                            <th class="text-end pe-4" style="width: 15%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($cursos_supervisados) > 0)
                            @foreach ($cursos_supervisados as $c)
                                <tr>
                                    <td class="ps-4">
                                        <strong class="task-title-text d-block" style="font-size: 0.9rem;">{{ $c->planEstudio->materia }}</strong>
                                        <span class="task-subtitle-text small d-block">{{ $c->profesor->nombre ?? 'N/A' }} {{ $c->profesor->apellidos ?? '' }}</span>
                                        <span class="badge bg-secondary rounded-pill x-small mt-1" style="font-size: 0.65rem;">{{ $c->horario }}</span>
                                    </td>
                                    
                                    <!-- Checks column toggles -->
                                    <td class="text-center">
                                        <i class="bi bi-check-circle-fill check-toggle {{ $c->check_recursos ? 'checked' : 'unchecked' }}" onclick="toggleCheck({{ $c->id_curso_activo }}, 'recursos')"></i>
                                    </td>
                                    <td class="text-center">
                                        <i class="bi bi-check-circle-fill check-toggle {{ $c->check_portada ? 'checked' : 'unchecked' }}" onclick="toggleCheck({{ $c->id_curso_activo }}, 'portada')"></i>
                                    </td>
                                    <td class="text-center">
                                        <i class="bi bi-check-circle-fill check-toggle {{ $c->check_moodle ? 'checked' : 'unchecked' }}" onclick="toggleCheck({{ $c->id_curso_activo }}, 'moodle')"></i>
                                    </td>
                                    <td class="text-center">
                                        <i class="bi bi-check-circle-fill check-toggle {{ $c->check_estudiantes ? 'checked' : 'unchecked' }}" onclick="toggleCheck({{ $c->id_curso_activo }}, 'estudiantes')"></i>
                                    </td>
                                    <td class="text-center">
                                        <i class="bi bi-check-circle-fill check-toggle {{ $c->check_actividades ? 'checked' : 'unchecked' }}" onclick="toggleCheck({{ $c->id_curso_activo }}, 'actividades')"></i>
                                    </td>
                                    <td class="text-center">
                                        <i class="bi bi-check-circle-fill check-toggle {{ $c->check_calificaciones ? 'checked' : 'unchecked' }}" onclick="toggleCheck({{ $c->id_curso_activo }}, 'calificaciones')"></i>
                                    </td>
                                    <td class="text-center">
                                        <i class="bi bi-check-circle-fill check-toggle {{ $c->check_acta ? 'checked' : 'unchecked' }}" onclick="toggleCheck({{ $c->id_curso_activo }}, 'acta')"></i>
                                    </td>
                                    <td class="text-center">
                                        <i class="bi bi-check-circle-fill check-toggle {{ $c->check_pago ? 'checked' : 'unchecked' }}" onclick="confirmarPago({{ $c->id_curso_activo }}, '{{ addslashes($c->planEstudio->materia) }}', '{{ addslashes(($c->profesor->nombre ?? '') . ' ' . ($c->profesor->apellidos ?? '')) }}')"></i>
                                    </td>

                                    <!-- Last Notification logs -->
                                    <td class="text-center task-subtitle-text small">
                                        @if ($c->fecha_notificacion)
                                            <span class="d-block">{{ $c->fecha_notificacion->format('d/m') }}</span>
                                            <span class="x-small task-subtitle-text">{{ $c->fecha_notificacion->format('g:i a') }}</span>
                                        @else
                                            <span class="task-subtitle-text">-</span>
                                        @endif
                                    </td>

                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1">
                                            <button onclick="openNotifModal({{ $c->id_curso_activo }}, '{{ addslashes($c->planEstudio->materia) }}', '{{ addslashes($c->planEstudio->codigo) }}', '{{ addslashes($c->planEstudio->programa->nombre_programa ?? '') }}', '{{ addslashes($c->horario) }}', '{{ $c->fecha_inicio ? $c->fecha_inicio->format('Y-m-d') : '' }}', '{{ addslashes(($c->profesor->nombre ?? '') . ' ' . ($c->profesor->apellidos ?? '')) }}')" class="btn btn-sm btn-outline-success rounded-pill" title="Notificar">
                                                <i class="bi bi-bell-fill"></i>
                                            </button>
                                            <button onclick="verHistorialNotif({{ $c->id_curso_activo }})" class="btn btn-sm btn-outline-info rounded-pill" title="Historial Notif.">
                                                <i class="bi bi-clock-history"></i>
                                            </button>
                                            <button onclick="toggleSupervision({{ $c->id_curso_activo }})" class="btn btn-sm btn-outline-danger rounded-pill" title="Quitar de Supervisión">
                                                <i class="bi bi-eye-slash-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="11" class="text-center py-5 task-subtitle-text">
                                    No hay cursos en supervisión activa para el periodo seleccionado.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- VIEW SEMANA -->
        <div id="view-semana" class="d-none">
            <div class="d-flex flex-nowrap overflow-auto gap-3 pb-4">
                @foreach ($cursos_por_dia as $dia => $cursos)
                    <div class="day-column">
                        <div class="day-header">{{ $dia }} ({{ count($cursos) }})</div>
                        <div class="day-content">
                            @if (count($cursos) > 0)
                                @foreach ($cursos as $c)
                                    <div class="course-card">
                                        <h6 class="fw-bold task-title-text mb-1 small">{{ $c->planEstudio->materia }}</h6>
                                        <span class="task-subtitle-text d-block x-small mb-2">{{ $c->profesor->nombre ?? 'N/A' }} {{ $c->profesor->apellidos ?? '' }}</span>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="x-small task-subtitle-text"><i class="bi bi-clock me-1"></i>{{ Str::limit($c->horario, 20) }}</span>
                                            <div>
                                                <button onclick="openNotifModal({{ $c->id_curso_activo }}, '{{ addslashes($c->planEstudio->materia) }}', '{{ addslashes($c->planEstudio->codigo) }}', '{{ addslashes($c->planEstudio->programa->nombre_programa ?? '') }}', '{{ addslashes($c->horario) }}', '{{ $c->fecha_inicio ? $c->fecha_inicio->format('Y-m-d') : '' }}', '{{ addslashes(($c->profesor->nombre ?? '') . ' ' . ($c->profesor->apellidos ?? '')) }}')" class="btn btn-xs btn-outline-success p-1 px-2 rounded" title="Notificar">
                                                    <i class="bi bi-bell-fill"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center task-subtitle-text py-4 x-small fst-italic">Sin lecciones</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- MODAL DE NOTIFICACIÓN -->
    <div class="modal fade" id="notifModal" tabindex="-1" aria-labelledby="notifModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content dropdown-menu-dark-custom border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title task-title-text fw-bold" id="notifModalLabel"><i class="bi bi-whatsapp text-success me-2"></i> Generador de Mensaje de WhatsApp</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label task-subtitle-text fw-bold small text-uppercase">Plantilla de Notificación</label>
                        <select id="selectPlantilla" class="form-select form-select-custom" onchange="actualizarMensajePrevio()">
                            <option value="apertura">1. Apertura de Curso y Sílabo</option>
                            <option value="moodle_act">2. Habilitar Moodle y Actividades</option>
                            <option value="estudiantes">3. Estudiantes Matriculados y Sincronizados</option>
                            <option value="calificaciones">4. Recordatorio de Calificaciones al Día</option>
                            <option value="acta">5. Envío de Acta Final Firmada</option>
                            <option value="pago">6. Hitos Completados y Pago Procesado</option>
                            <option value="felicitacion">7. Felicitación por Cierre Exitoso</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label task-subtitle-text fw-bold small text-uppercase">Mensaje Generado (Editable)</label>
                        <textarea id="notifMensajeArea" class="form-control form-control-custom" rows="10" style="font-family: monospace; font-size: 0.85rem;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <div>
                        <button type="button" class="btn btn-outline-info rounded-pill px-4 me-2" onclick="copiarMensaje()">
                            <i class="bi bi-clipboard me-1"></i> Copiar Mensaje
                        </button>
                        <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" onclick="enviarWhatsApp()">
                            <i class="bi bi-whatsapp me-1"></i> Abrir en WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL HISTORIAL DE NOTIFICACIONES -->
    <div class="modal fade" id="historialModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content dropdown-menu-dark-custom border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title task-title-text fw-bold"><i class="bi bi-clock-history text-info me-2"></i> Historial de Notificaciones</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="historialModalBody">
                    <div class="text-center py-4 task-subtitle-text">Cargando historial...</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let currentCursoData = {};

    function switchView(view) {
        if (view === 'tabla') {
            $('#view-tabla').removeClass('d-none');
            $('#view-semana').addClass('d-none');
            $('#btn-view-tabla').addClass('active');
            $('#btn-view-semana').removeClass('active');
        } else {
            $('#view-tabla').addClass('d-none');
            $('#view-semana').removeClass('d-none');
            $('#btn-view-semana').addClass('active');
            $('#btn-view-tabla').removeClass('active');
        }
    }

    function toggleCheck(idCurso, campo) {
        $.post("{{ route('supervision.toggle_check') }}", {
            _token: "{{ csrf_token() }}",
            id_curso_activo: idCurso,
            campo: campo
        }, function(res) {
            if(res.success) {
                location.reload();
            }
        });
    }

    function toggleSupervision(idCurso) {
        if(confirm('¿Desea quitar este curso de la supervisión activa?')) {
            $.post("{{ route('supervision.toggle') }}", {
                _token: "{{ csrf_token() }}",
                id_curso_activo: idCurso
            }, function(res) {
                if(res.success) {
                    location.reload();
                }
            });
        }
    }

    function openNotifModal(id, curso, codigo, programa, horario, fechaInicio, profesor) {
        currentCursoData = { id, curso, codigo, programa, horario, fechaInicio, profesor };
        $('#selectPlantilla').val('apertura');
        actualizarMensajePrevio();
        $('#notifModal').modal('show');
    }

    function actualizarMensajePrevio() {
        const pType = $('#selectPlantilla').val();
        const { id, curso, codigo, programa, horario, fechaInicio, profesor } = currentCursoData;
        const senderName = "{{ Auth::user()->nombre }} {{ Auth::user()->apellidos }}";
        const dateNow = new Date();
        const timeStr = dateNow.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        const header = `Estimado(a) profesor(a) *${profesor}*:\n\n`;
        const footer = `\n------------------------------------------\n🔔 Acceso Inbox:\n{{ url('/') }}\n\n🔔 Atendido por: ${senderName}\n🕒 Hora de envío: ${timeStr}`;

        const campusName = "{{ config('cliente.nombre', 'CEFI') }} Virtual";

        const templates = {
            'apertura': `${header}Le informamos formalmente sobre la apertura de su curso:\n\n🔔 *Curso:* ${codigo} - ${curso}\n🔔 *Programa:* ${programa}\n📅 *Inicio de Lecciones:* ${fechaInicio || 'Por definir'}\n🕒 *Horario:* ${horario}\n\nFavor proceder con la revisión de su sílabo para iniciar con el montaje en ${campusName}. ¡Muchos éxitos!${footer}`,
            'moodle_act': `${header}Le informamos que el curso *${curso}* ya se encuentra habilitado en ${campusName}. Favor verificar el montaje de materiales y la debida configuración de actividades bien programadas (tareas, foros, etc.).${footer}`,
            'estudiantes': `${header}Le informamos que los estudiantes del curso *${curso}* han sido matriculados en Inbox y ya han sido sincronizados con ${campusName}, para que pueda realizar al término del curso todo el proceso final de calificaciones.${footer}`,
            'calificaciones': `${header}Se le recuerda mantener las calificaciones de los estudiantes al día en la plataforma para el curso *${curso}*, asegurando el envío oportuno de las notas parciales.${footer}`,
            'acta': `${header}Se le solicita cordialmente el envío del acta final de calificaciones para el curso *${curso}*, una vez que todas las notas hayan sido notificadas a los estudiantes.${footer}`,
            'pago': `${header}Estimado(a) profesor(a), le informamos que el pago por impartir el curso *${curso}* ha sido procesado exitosamente, tras cumplir con todos los hitos de calidad académica. ¡Muchas gracias!${footer}`,
            'felicitacion': `${header}Le extendemos una felicitación por el excelente desarrollo y cumplimiento de hitos en el curso *${curso}*. ¡Buen trabajo!${footer}`
        };

        $('#notifMensajeArea').val(templates[pType] || '');
    }

    function copiarMensaje() {
        const area = document.getElementById('notifMensajeArea');
        area.select();
        document.execCommand('copy');
        alert('¡Mensaje copiado al portapapeles!');
    }

    function enviarWhatsApp() {
        const text = $('#notifMensajeArea').val();
        const encoded = encodeURIComponent(text);
        
        // Registrar la notificación en BD
        $.post("{{ route('supervision.notificar') }}", {
            _token: "{{ csrf_token() }}",
            id_curso_activo: currentCursoData.id,
            tipo: $('#selectPlantilla').val(),
            mensaje: text
        }, function(res) {
            window.open(`https://api.whatsapp.com/send?text=${encoded}`, '_blank');
            $('#notifModal').modal('hide');
        });
    }

    function verHistorialNotif(idCurso) {
        $('#historialModalBody').html('<div class="text-center py-4 task-subtitle-text">Cargando historial...</div>');
        $('#historialModal').modal('show');

        const url = "{{ route('supervision.historial', ['id' => ':id']) }}".replace(':id', idCurso);
        $.get(url, function(res) {
            if (res.success && res.data.length > 0) {
                let html = '<div class="list-group list-group-flush gap-2">';
                res.data.forEach(item => {
                    html += `
                        <div class="list-group-item bg-dark bg-opacity-25 border border-secondary rounded p-3 text-white">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-success rounded-pill">${item.tipo}</span>
                                <small class="task-subtitle-text">${item.fecha}</small>
                            </div>
                            <p class="mb-1 small task-title-text" style="white-space: pre-wrap;">${item.mensaje}</p>
                            <small class="task-subtitle-text d-block text-end">Enviado por: ${item.usuario}</small>
                        </div>
                    `;
                });
                html += '</div>';
                $('#historialModalBody').html(html);
            } else {
                $('#historialModalBody').html('<div class="text-center py-4 task-subtitle-text">No hay notificaciones enviadas registradas para este curso.</div>');
            }
        });
    }

    function confirmarPago(idCurso, materia, profesor) {
        if(confirm(`¿Desea marcar el pago como PROCESADO para el curso ${materia} del docente ${profesor}?`)) {
            toggleCheck(idCurso, 'pago');
        }
    }
</script>
@endsection
