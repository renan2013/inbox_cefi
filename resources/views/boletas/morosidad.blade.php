@extends('layouts.app')

@section('title', 'Inbox BPM - Control de Morosidad')

@section('styles')
    <style>
        :root {
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50:  #f8fafc;
        }

        /* Page Header */
        .page-header {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 1.25rem;
            padding: 1.75rem 2.25rem;
            margin-bottom: 1.75rem;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.04), 0 8px 10px -6px rgba(15, 23, 42, 0.02);
        }

        .section-title {
            font-weight: 800;
            color: var(--slate-900) !important;
            letter-spacing: -0.03em;
            font-size: 1.85rem;
            line-height: 1.2;
        }

        .header-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .header-subtitle {
            color: var(--slate-500);
            font-size: 0.95rem;
        }

        /* KPI Cards */
        .kpi-card {
            border-radius: 1.25rem;
            padding: 1.5rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            background: #ffffff;
            border: 1px solid var(--slate-200);
            box-shadow: 0 4px 15px -3px rgba(15, 23, 42, 0.03);
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -4px rgba(15, 23, 42, 0.08);
            border-color: var(--slate-300, #cbd5e1);
        }

        .kpi-title {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.35rem;
        }

        .kpi-value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }

        .kpi-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .kpi-card-rose .kpi-icon-box {
            background: #fef2f2;
            color: #ef4444;
        }

        .kpi-card-amber .kpi-icon-box {
            background: #fffbeb;
            color: #d97706;
        }

        .kpi-card-orange .kpi-icon-box {
            background: #fff7ed;
            color: #ea580c;
        }

        /* Gran Total Vencido - Hero Card */
        .kpi-card-total {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 50%, #ef4444 100%);
            border: 1px solid #b91c1c;
            color: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(220, 38, 38, 0.35);
        }

        .kpi-card-total:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 30px -6px rgba(220, 38, 38, 0.45);
        }

        .kpi-card-total .kpi-title {
            color: rgba(255, 255, 255, 0.85);
        }

        .kpi-card-total .kpi-value {
            color: #ffffff;
        }

        .kpi-card-total .kpi-icon-box {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            color: #ffffff;
        }

        /* Filter Section */
        .filter-card {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 1.25rem;
            padding: 1.15rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px -3px rgba(15, 23, 42, 0.03);
        }

        .form-control-custom, .form-select-custom {
            background-color: var(--slate-50);
            border: 1.5px solid var(--slate-200);
            border-radius: 0.75rem;
            color: var(--slate-900);
            padding: 0.65rem 1rem;
            font-size: 0.92rem;
            transition: all 0.2s;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            background-color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
            color: var(--slate-900);
            outline: none;
        }

        .badge-counter {
            background: var(--slate-100);
            color: var(--slate-600);
            font-weight: 700;
            font-size: 0.82rem;
            padding: 0.5rem 1rem;
            border-radius: 50rem;
            border: 1px solid var(--slate-200);
        }

        /* Deudores Container & Cards */
        .deudor-card {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 1.25rem;
            margin-bottom: 1.25rem;
            overflow: hidden;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.03);
        }

        .deudor-card:hover {
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -4px rgba(15, 23, 42, 0.08);
        }

        .deudor-header {
            padding: 1.35rem 1.65rem;
        }

        /* Student Avatar Badge */
        .student-avatar {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #4338ca;
            font-weight: 800;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 8px rgba(99, 102, 241, 0.15);
            flex-shrink: 0;
        }

        .student-name {
            font-weight: 800;
            color: var(--slate-900);
            font-size: 1.15rem;
            letter-spacing: -0.015em;
        }

        .badge-phone {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            font-weight: 600;
            padding: 0.2rem 0.65rem;
            border-radius: 50rem;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
        }

        .badge-atraso {
            background: #fef2f2;
            color: #dc2626;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 0.4rem 0.9rem;
            border-radius: 50rem;
            border: 1px solid #fecaca;
            display: inline-block;
        }

        /* Buttons Toolbar */
        .btn-toolbar-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-action-outline {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            color: var(--slate-700);
            font-weight: 600;
            font-size: 0.82rem;
            border-radius: 50rem;
            padding: 0.45rem 0.95rem;
            transition: all 0.2s;
        }

        .btn-action-outline:hover {
            background: var(--slate-100);
            border-color: var(--slate-300);
            color: var(--slate-900);
        }

        .btn-action-edit {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #b45309;
            font-weight: 600;
            font-size: 0.82rem;
            border-radius: 50rem;
            padding: 0.45rem 0.95rem;
            transition: all 0.2s;
        }

        .btn-action-edit:hover {
            background: #fef3c7;
            color: #92400e;
        }

        .btn-action-copy {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #ffffff;
            border: 1px solid var(--slate-200);
            color: var(--slate-500);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-action-copy:hover {
            background: var(--slate-100);
            color: #16a34a;
            border-color: #86efac;
        }

        .btn-action-whatsapp {
            background: #25d366;
            color: #ffffff;
            border: none;
            font-weight: 700;
            font-size: 0.82rem;
            border-radius: 50rem;
            padding: 0.45rem 1.15rem;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 3px 8px rgba(37, 211, 102, 0.25);
            transition: all 0.2s;
        }

        .btn-action-whatsapp:hover {
            background: #128c7e;
            color: #ffffff;
            box-shadow: 0 5px 12px rgba(37, 211, 102, 0.35);
            transform: translateY(-1px);
        }

        .btn-action-cuenta {
            background: var(--slate-900);
            color: #ffffff;
            border: none;
            font-weight: 700;
            font-size: 0.82rem;
            border-radius: 50rem;
            padding: 0.45rem 1.15rem;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s;
        }

        .btn-action-cuenta:hover {
            background: var(--slate-800);
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* Expandable Cuotas Section */
        .collapse-table-wrap {
            background: var(--slate-50);
            border-top: 1px solid var(--slate-200);
            padding: 1.35rem 1.75rem;
        }

        .table-cuotas {
            margin-bottom: 0;
        }

        .table-cuotas thead th {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--slate-500);
            background: #ffffff;
            border-bottom: 1.5px solid var(--slate-200);
            padding: 0.75rem 1rem;
            font-weight: 700;
        }

        .table-cuotas tbody td {
            padding: 0.85rem 1rem;
            font-size: 0.88rem;
            border-bottom: 1px solid var(--slate-200);
            background: #ffffff;
            color: var(--slate-700);
            vertical-align: middle;
        }

        .table-cuotas tbody tr:last-child td {
            border-bottom: none;
        }

        /* Modal Styles */
        .modal-custom .modal-content {
            border-radius: 1.25rem;
            border: 1px solid var(--slate-200);
            box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.15);
        }
    </style>
@endsection

@section('content')
    <div class="container py-4">
        
        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="header-icon-box">
                    <i class="bi bi-shield-exclamation"></i>
                </div>
                <div>
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb mb-0" style="font-size: 0.82rem;">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted fw-semibold"><i class="bi bi-house-door me-1"></i> Inicio</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}" class="text-decoration-none text-muted fw-semibold">Reportes</a></li>
                            <li class="breadcrumb-item active text-danger fw-bold" aria-current="page">Control de Morosidad</li>
                        </ol>
                    </nav>
                    <h1 class="section-title mb-1">Control de Morosidad</h1>
                    <p class="header-subtitle mb-0">Gestión de estudiantes con cuotas vencidas, cálculo de mora y herramientas de cobro rápido.</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('boletas.estado_cuenta') }}" class="btn btn-action-outline px-4 py-2 fw-bold">
                    <i class="bi bi-wallet2 me-1"></i> Ir a Estados de Cuenta
                </a>
                <a href="{{ route('boletas.index') }}" class="btn btn-action-outline px-4 py-2 fw-semibold">
                    <i class="bi bi-receipt me-1"></i> Ver Boletas
                </a>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="row g-3 mb-4">
            <!-- Estudiantes Deudores -->
            <div class="col-lg-3 col-md-6">
                <div class="kpi-card kpi-card-rose">
                    <div>
                        <div class="kpi-title text-danger">Estudiantes Deudores</div>
                        <div class="kpi-value text-dark">{{ $global_deudores_count }}</div>
                    </div>
                    <div class="kpi-icon-box">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>

            <!-- Capital Vencido -->
            <div class="col-lg-3 col-md-6">
                <div class="kpi-card kpi-card-amber">
                    <div>
                        <div class="kpi-title" style="color: #d97706;">Capital Vencido</div>
                        <div class="kpi-value text-dark">₡{{ number_format($global_capital_mora, 2) }}</div>
                    </div>
                    <div class="kpi-icon-box">
                        <i class="bi bi-bank"></i>
                    </div>
                </div>
            </div>

            <!-- Mora Acumulada -->
            <div class="col-lg-3 col-md-6">
                <div class="kpi-card kpi-card-orange">
                    <div>
                        <div class="kpi-title" style="color: #ea580c;">Mora ({{ $tasa_interes_mora ?? '2.0' }}%)</div>
                        <div class="kpi-value" style="color: #ea580c;">₡{{ number_format($global_interes_mora, 2) }}</div>
                    </div>
                    <div class="kpi-icon-box">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
            </div>

            <!-- Gran Total Vencido - Hero Card -->
            <div class="col-lg-3 col-md-6">
                <div class="kpi-card kpi-card-total">
                    <div>
                        <div class="kpi-title">Total Vencido</div>
                        <div class="kpi-value">₡{{ number_format($global_total_vencido, 2) }}</div>
                    </div>
                    <div class="kpi-icon-box">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra de Búsqueda y Filtros -->
        <div class="filter-card">
            <div class="row g-3 align-items-center">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 0.75rem 0 0 0.75rem; border-color: var(--slate-200);">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="buscador_morosos" class="form-control form-control-custom border-start-0" 
                            style="border-radius: 0 0.75rem 0.75rem 0;"
                            placeholder="Buscar deudor por nombre, apellidos, correo o teléfono...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="filtro_dias" class="form-select form-select-custom">
                        <option value="0">Todos los rangos de mora</option>
                        <option value="1">Mínimo 1 día de atraso</option>
                        <option value="5">Mínimo 5 días de atraso</option>
                        <option value="15">Mínimo 15 días de atraso</option>
                        <option value="25">Mínimo 25 días de atraso</option>
                    </select>
                </div>
                <div class="col-md-2 text-md-end text-start">
                    <span class="badge-counter d-inline-block" id="contador_visibles">
                        {{ $global_deudores_count }} deudores
                    </span>
                </div>
            </div>
        </div>

        <!-- Sección de Estudiantes en Mora -->
        <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-people-fill text-danger"></i> Estudiantes en Mora
            </h4>
        </div>

        <div id="contenedor_deudores">
            @forelse ($deudores as $deudor)
                @php
                    $banner_url = 'https://unela.org/bpm_unela/imgs/logo_unela_banner.jpg';

                    // Pre-construir desglose de cuotas vencidas
                    $detalles_texto = "";
                    foreach ($deudor['cuotas'] as $c) {
                        $detalles_texto .= "• *Boleta " . $c['numero_boleta'] . "* (Cuota #" . $c['numero_cuota'] . "): ₡" . number_format($c['total_cuota'], 2) . " (Venció: " . date('d/m/Y', strtotime($c['fecha_vencimiento'])) . " | " . $c['dias_atraso'] . " días de atraso)\n";
                    }

                    $primer_nombre = explode(' ', trim($deudor['nombre']))[0] ?? $deudor['nombre'];

                    // Plantilla oficial con logotipo garantizado para WhatsApp Web
                    $wa_message = "https://unela.org/bpm_unela/\n\n";
                    $wa_message .= "Estimado/a *" . $primer_nombre . "*,\n\n";
                    $wa_message .= "Le saludamos cordialmente de parte de la Universidad UNELA.\n\n";
                    $wa_message .= "Le informamos que a la fecha presenta cuota(s) pendiente(s) de colegiatura con recargo por mora acumulada al " . ($tasa_interes_mora ?? '2.0') . "% diario:\n\n";
                    $wa_message .= "*Detalle de Cuotas Pendientes:*\n";
                    $wa_message .= $detalles_texto . "\n";
                    $wa_message .= "💰 *Monto Total a Cancelar:* ₡" . number_format($deudor['total_vencido'], 2) . "\n";
                    $wa_message .= "   • Capital pendiente: ₡" . number_format($deudor['total_capital'], 2) . "\n";
                    $wa_message .= "   • Recargos por mora: ₡" . number_format($deudor['total_mora'], 2) . "\n\n";
                    $wa_message .= "📌 *Medios de Pago Autorizados:*\n";
                    $wa_message .= "• SINPE Móvil: 8777-7849\n";
                    $wa_message .= "• Transferencia bancaria (solicitar cuentas oficiales respondiendo a este mensaje)\n\n";
                    $wa_message .= "Agradecemos realizar su cancelación a la brevedad y remitir su comprobante por este medio.\n";
                    $wa_message .= "Si ya realizó su pago recientemente, por favor omita este recordatorio.\n\n";
                    $wa_message .= "------------------------------------------\n";
                    $wa_message .= "🔔 *Acceso Inbox BPM - Universidad UNELA:*\n";
                    $wa_message .= "https://unela.org/bpm_unela/\n\n";
                    $wa_message .= "🔔 *Atendido por:* " . (auth()->user()->nombre ?? 'Dpto. Cobro') . " " . (auth()->user()->apellidos ?? '') . "\n";
                    $wa_message .= "🕒 *Hora de emisión:* " . date('g:i a');

                    // Iniciales para avatar
                    $partes_nombre = explode(' ', trim($deudor['nombre']));
                    $iniciales = mb_strtoupper(mb_substr($partes_nombre[0] ?? 'E', 0, 1) . mb_substr($partes_nombre[1] ?? '', 0, 1));
                @endphp

                <div class="deudor-card deudor-row" 
                    data-nombre="{{ strtolower($deudor['nombre'] . ' ' . $deudor['email'] . ' ' . $deudor['telefono']) }}" 
                    data-atraso="{{ $deudor['max_dias_atraso'] }}">
                    
                    <div class="deudor-header">
                        <div class="row align-items-center g-3">
                            
                            <!-- 1. Perfil del Estudiante -->
                            <div class="col-lg-4 col-md-5">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="student-avatar">
                                        {{ $iniciales }}
                                    </div>
                                    <div>
                                        <div class="student-name mb-1">{{ $loop->iteration }}. {{ $deudor['nombre'] }}</div>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <span class="text-muted small"><i class="bi bi-envelope me-1"></i>{{ $deudor['email'] }}</span>
                                            @if ($deudor['telefono'])
                                                <span class="badge-phone"><i class="bi bi-whatsapp me-1"></i>+{{ $deudor['telefono'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 2. Monto Total Vencido -->
                            <div class="col-lg-3 col-md-3 text-md-center">
                                <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Vencido</div>
                                <strong class="text-danger d-block" style="font-size: 1.45rem; font-weight: 800; line-height: 1.1;">
                                    ₡{{ number_format($deudor['total_vencido'], 2) }}
                                </strong>
                                <small class="text-muted" style="font-size: 0.78rem;">
                                    ₡{{ number_format($deudor['total_capital'], 2) }} cap + ₡{{ number_format($deudor['total_mora'], 2) }} mora
                                </small>
                            </div>

                            <!-- 3. Badge Días de Atraso -->
                            <div class="col-lg-2 col-md-2 text-md-center">
                                <span class="badge-atraso">{{ $deudor['max_dias_atraso'] }} días de atraso</span>
                            </div>

                            <!-- 4. Barra de Acciones Unificada -->
                            <div class="col-lg-3 col-md-12 text-lg-end text-start">
                                <div class="btn-toolbar-group justify-content-lg-end justify-content-start">
                                    <!-- Botón Cuotas -->
                                    <button type="button" class="btn btn-action-outline" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#detalle-{{ $deudor['id_estudiante'] }}" 
                                        aria-expanded="false"
                                        title="Ver desglose de cuotas vencidas">
                                        <i class="bi bi-list-check me-1"></i> Cuotas
                                    </button>

                                    <!-- Botón Editar Mensaje -->
                                    <button type="button" class="btn btn-action-edit btn-editar-mensaje"
                                        data-id="{{ $deudor['id_estudiante'] }}"
                                        data-nombre="{{ $deudor['nombre'] }}"
                                        data-telefono="{{ $deudor['telefono'] }}"
                                        data-mensaje="{{ $wa_message }}"
                                        title="Personalizar texto de cobro">
                                        <i class="bi bi-pencil-square me-1"></i> Editar
                                    </button>

                                    <!-- Botón Copiar -->
                                    <button type="button" class="btn btn-action-copy btn-copiar-mensaje"
                                        data-id="{{ $deudor['id_estudiante'] }}"
                                        data-nombre="{{ $deudor['nombre'] }}"
                                        data-mensaje="{{ $wa_message }}"
                                        title="Copiar mensaje con logo al portapapeles">
                                        <i class="bi bi-clipboard-check"></i>
                                    </button>

                                    <!-- Botón WhatsApp: Abre directo en la app de WhatsApp de la computadora -->
                                    @if ($deudor['telefono'])
                                        <button type="button" class="btn-action-whatsapp btn-enviar-wa-mora" 
                                            data-id="{{ $deudor['id_estudiante'] }}" 
                                            data-nombre="{{ $deudor['nombre'] }}" 
                                            data-telefono="{{ $deudor['telefono'] }}"
                                            data-mensaje="{{ $wa_message }}"
                                            title="Abrir chat directo en la app de WhatsApp de su computadora">
                                            <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill text-muted px-3" 
                                            title="Sin teléfono registrado" 
                                            onclick="Swal.fire('Sin Teléfono', 'El estudiante {{ $deudor['nombre'] }} no tiene un número de teléfono registrado en el sistema.', 'info')">
                                            <i class="bi bi-telephone-x me-1"></i> Sin WA
                                        </button>
                                    @endif

                                    <!-- Botón Estado de Cuenta -->
                                    <a href="{{ route('boletas.estado_cuenta', ['estudiante_id' => $deudor['id_estudiante']]) }}" 
                                        class="btn-action-cuenta text-decoration-none"
                                        title="Ver Estado de Cuenta Completo">
                                        <i class="bi bi-eye me-1"></i> Cuenta
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desglose de Cuotas Expandible -->
                    <div class="collapse collapse-table-wrap" id="detalle-{{ $deudor['id_estudiante'] }}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-receipt-cutoff text-primary"></i> Desglose de Cuotas Pendientes
                            </h6>
                            <span class="badge bg-light text-dark border px-3 py-1 rounded-pill small">
                                {{ count($deudor['cuotas']) }} cuota(s) vencida(s)
                            </span>
                        </div>
                        <div class="table-responsive rounded-3 border">
                            <table class="table table-cuotas align-middle">
                                <thead>
                                    <tr>
                                        <th>N° Boleta</th>
                                        <th>Cuota</th>
                                        <th>Vencimiento</th>
                                        <th>Días Atraso</th>
                                        <th>Monto Capital</th>
                                        <th>Mora Acumulada</th>
                                        <th>Total a Pagar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($deudor['cuotas'] as $c)
                                        <tr>
                                            <td class="fw-bold text-dark">{{ $c['numero_boleta'] }}</td>
                                            <td>Cuota #{{ $c['numero_cuota'] }}</td>
                                            <td class="text-muted">{{ date('d/m/Y', strtotime($c['fecha_vencimiento'])) }}</td>
                                            <td>
                                                <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill fw-bold">
                                                    {{ $c['dias_atraso'] }} días
                                                </span>
                                            </td>
                                            <td class="fw-semibold text-dark">₡{{ number_format($c['monto_cuota'], 2) }}</td>
                                            <td class="text-warning fw-bold" style="color: #ea580c !important;">
                                                +₡{{ number_format($c['interes_acumulado'], 2) }}
                                            </td>
                                            <td class="text-danger fw-extrabold" style="font-weight: 800;">
                                                ₡{{ number_format($c['total_cuota'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            @empty
                <div class="deudor-card p-5 text-center text-muted">
                    <i class="bi bi-check-circle fs-1 text-success d-block mb-3"></i>
                    <h4 class="fw-bold text-dark mb-1">¡Sin Estudiantes en Mora!</h4>
                    <p class="mb-0">Todas las cuotas de la institución se encuentran al día sin saldos vencidos.</p>
                </div>
            @endforelse
        </div>

    </div>

    <!-- Modal Editar Mensaje de Cobro WhatsApp -->
    <div class="modal fade modal-custom" id="modalEditarMensaje" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-white text-dark shadow-lg">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square text-warning"></i>Personalizar Mensaje de Cobro
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Estudiante Destinatario</label>
                        <input type="text" id="modal_destinatario" class="form-control form-control-custom bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Teléfono WhatsApp</label>
                        <input type="text" id="modal_telefono" class="form-control form-control-custom">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Contenido del Mensaje (Incluye membrete y logo oficial)</label>
                        <textarea id="modal_texto_mensaje" class="form-control form-control-custom" rows="10" style="font-family: monospace; font-size: 0.88rem;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-action-outline px-3" id="modal_btn_copiar">
                        <i class="bi bi-clipboard-check me-1"></i> Copiar Mensaje con Logo
                    </button>
                    <button type="button" class="btn btn-action-whatsapp px-4 fw-bold" id="modal_btn_wa_web">
                        <i class="bi bi-whatsapp me-1"></i> Abrir en WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const BANNER_UNELA_URL = 'https://unela.org/bpm_unela/imgs/logo_unela_banner.jpg';

        /**
         * Normaliza el número telefónico asegurando el prefijo de país.
         * Si tiene 8 dígitos (formato estándar de Costa Rica), añade 506.
         */
        function normalizarTelefono(tel) {
            if (!tel) return '';
            let clean = tel.toString().replace(/[^0-9]/g, '');
            if (clean.length === 8) {
                clean = '506' + clean;
            }
            return clean;
        }


        /**
         * Copia el mensaje al portapapeles en formato enriquecido (con el banner/logo institucional en HTML)
         * y en formato de texto plano con compatibilidad universal.
         */
        async function copiarMensajeConLogo(texto, imgUrl = BANNER_UNELA_URL) {
            const htmlContent = `<p><img src="${imgUrl}" alt="Universidad UNELA" style="max-width: 500px; height: auto; border-radius: 8px;"></p><div style="font-family: Arial, sans-serif; white-space: pre-wrap; font-size: 14px;">${texto.replace(/\n/g, '<br>')}</div>`;

            if (navigator.clipboard && window.ClipboardItem) {
                try {
                    const blobHtml = new Blob([htmlContent], { type: 'text/html' });
                    const blobText = new Blob([texto], { type: 'text/plain' });
                    await navigator.clipboard.write([
                        new ClipboardItem({
                            'text/html': blobHtml,
                            'text/plain': blobText
                        })
                    ]);
                    return true;
                } catch (e) {
                    console.warn('Fallo ClipboardItem, reintentando con writeText', e);
                }
            }

            if (navigator.clipboard) {
                try {
                    await navigator.clipboard.writeText(texto);
                    return true;
                } catch (e) {}
            }

            // Fallback con elemento temporal
            const ta = document.createElement('textarea');
            ta.value = texto;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            return true;
        }

        function copiarTextoPortapapeles(txt) {
            try {
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(txt).catch(() => {});
                } else {
                    const ta = document.createElement('textarea');
                    ta.value = txt;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                }
            } catch (err) {
                console.warn('Error al copiar texto:', err);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // 1. Buscador y Filtro por Días en Tiempo Real
            const searchInput = document.getElementById('buscador_morosos');
            const filterSelect = document.getElementById('filtro_dias');
            const rows = document.querySelectorAll('.deudor-row');
            const counter = document.getElementById('contador_visibles');

            function filtrarDeudores() {
                const query = searchInput.value.toLowerCase().trim();
                const minAtraso = parseInt(filterSelect.value) || 0;
                let visibles = 0;

                rows.forEach(row => {
                    const rowText = row.dataset.nombre || '';
                    const rowAtraso = parseInt(row.dataset.atraso) || 0;

                    const matchesQuery = query === '' || rowText.includes(query);
                    const matchesAtraso = rowAtraso >= minAtraso;

                    if (matchesQuery && matchesAtraso) {
                        row.style.display = '';
                        visibles++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (counter) {
                    counter.textContent = `${visibles} deudor(es) visible(s)`;
                }
            }

            if (searchInput) searchInput.addEventListener('input', filtrarDeudores);
            if (filterSelect) filterSelect.addEventListener('change', filtrarDeudores);

            // 2. Botón Copiar Rápido Directo
            document.querySelectorAll('.btn-copiar-mensaje').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const msg = this.dataset.mensaje;
                    const nombre = this.dataset.nombre || 'el estudiante';

                    await copiarMensajeConLogo(msg);

                    Swal.fire({
                        icon: 'success',
                        title: '¡Mensaje Copiado con Logo!',
                        html: `El aviso oficial con el membrete y banner institucional para <strong>${nombre}</strong> se encuentra en su portapapeles.<br><br><small class="text-muted">Listo para pegar con <strong>Ctrl + V</strong> en WhatsApp.</small>`,
                        timer: 2500,
                        showConfirmButton: false
                    });
                });
            });

            // 3. Botón WhatsApp Directo (Optimizado para WhatsApp Web: no fuerza pestañas duplicadas)
            document.querySelectorAll('.btn-enviar-wa-mora').forEach(btn => {
                btn.addEventListener('click', async function() {
                    try {
                        const nombre = this.dataset.nombre;
                        const telefono = this.dataset.telefono;
                        const msg = this.dataset.mensaje;
                        const cleanTel = normalizarTelefono(telefono);

                        if (!cleanTel) {
                            Swal.fire('Sin Teléfono', `El estudiante ${nombre} no tiene un número de teléfono válido registrado.`, 'warning');
                            return;
                        }

                        // 1. Copiar automáticamente al portapapeles con el logo y membrete institucional
                        await copiarMensajeConLogo(msg);

                        const waUrl = `https://web.whatsapp.com/send?phone=${cleanTel}&text=${encodeURIComponent(msg)}`;

                        // 2. Diálogo elegante para usuarios de WhatsApp Web
                        Swal.fire({
                            icon: 'success',
                            title: `¡Aviso Copiado para WhatsApp!`,
                            html: `
                                <p class="mb-2 text-dark">El mensaje con membrete y logotipo de UNELA está en su portapapeles para <strong>${nombre}</strong>.</p>
                                <div class="p-3 bg-light rounded text-start small mb-3 border">
                                    <div><strong>Estudiante:</strong> ${nombre}</div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span><strong>WhatsApp:</strong> +${cleanTel}</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="copiarTextoPortapapeles('+${cleanTel}'); this.innerHTML='¡Copiado!';">
                                            <i class="bi bi-copy me-1"></i> Copiar Teléfono
                                        </button>
                                    </div>
                                </div>
                                <div class="alert alert-success py-2 px-3 small mb-0 text-start">
                                    <i class="bi bi-check-circle-fill me-1"></i> <strong>Listo para enviar:</strong> Vaya a su pestaña de WhatsApp Web ya abierta y pegue con <kbd>Ctrl</kbd> + <kbd>V</kbd>.
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonColor: '#25d366',
                            cancelButtonColor: '#0f172a',
                            confirmButtonText: '<i class="bi bi-check2 me-1"></i> Entendido (Pegar con Ctrl+V)',
                            cancelButtonText: '<i class="bi bi-box-arrow-up-right me-1"></i> Abrir Chat en Pestaña Nueva'
                        }).then((result) => {
                            // Solo si el usuario hace clic expresamente en "Abrir Chat en Pestaña Nueva", se abre la URL
                            if (result.dismiss === Swal.DismissReason.cancel) {
                                window.open(waUrl, '_blank');
                            }
                        });
                    } catch (e) {
                        console.error('Error en botón WhatsApp:', e);
                    }
                });
            });

            // 4. Modal Editar Mensaje
            const modalEl = document.getElementById('modalEditarMensaje');
            const bsModal = modalEl ? new bootstrap.Modal(modalEl) : null;
            const destInput = document.getElementById('modal_destinatario');
            const telInput = document.getElementById('modal_telefono');
            const txtArea = document.getElementById('modal_texto_mensaje');

            document.querySelectorAll('.btn-editar-mensaje').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (destInput) destInput.value = this.dataset.nombre;
                    if (telInput) telInput.value = this.dataset.telefono;
                    if (txtArea) txtArea.value = this.dataset.mensaje;
                    if (bsModal) bsModal.show();
                });
            });

            // Botón Copiar desde Modal
            const modalBtnCopiar = document.getElementById('modal_btn_copiar');
            if (modalBtnCopiar) {
                modalBtnCopiar.addEventListener('click', async function() {
                    if (!txtArea) return;
                    await copiarMensajeConLogo(txtArea.value);
                    if (bsModal) bsModal.hide();
                    
                    const nombre = destInput ? destInput.value : 'el estudiante';
                    Swal.fire({
                        icon: 'success',
                        title: '¡Mensaje Copiado con Logo!',
                        html: `El mensaje personalizado para <strong>${nombre}</strong> se encuentra en su portapapeles.<br><br>Vaya a su WhatsApp y presione <kbd>Ctrl</kbd> + <kbd>V</kbd>.`,
                        timer: 2500,
                        showConfirmButton: false
                    });
                });
            }

            // Botón Abrir en WhatsApp desde Modal
            const modalBtnWaWeb = document.getElementById('modal_btn_wa_web');
            if (modalBtnWaWeb) {
                modalBtnWaWeb.addEventListener('click', async function() {
                    const tel = normalizarTelefono(telInput ? telInput.value : '');
                    const msg = txtArea ? txtArea.value : '';

                    if (!tel) {
                        Swal.fire('Atención', 'Por favor ingrese un número de teléfono válido.', 'warning');
                        return;
                    }

                    await copiarMensajeConLogo(msg);
                    if (bsModal) bsModal.hide();

                    const waUrl = `https://web.whatsapp.com/send?phone=${tel}&text=${encodeURIComponent(msg)}`;
                    window.open(waUrl, 'whatsapp_web_unela');
                });
            }
        });
    </script>
@endsection
