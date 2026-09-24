<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo No Disponible - UNELA Inbox BPM</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5fb230;
            --primary-dark: #4e9a26;
            --bg-dark: #0f172a;
            --card-dark: #1e293b;
            --border-dark: #334155;
            --text-muted: #94a3b8;
            --text-light: #f8fafc;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            color: var(--text-light);
        }

        .card-disabled {
            background: #1e293b;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.08);
            max-width: 540px;
            width: 100%;
            overflow: hidden;
            text-align: center;
            position: relative;
        }

        .card-disabled::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #f59e0b, #ef4444);
        }

        .icon-circle {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.8rem;
            margin-bottom: 1.5rem;
            border: 2px solid rgba(245, 158, 11, 0.3);
        }

        .btn-custom {
            background: #5fb230;
            color: #ffffff;
            font-weight: 600;
            padding: 12px 26px;
            border-radius: 12px;
            border: none;
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-custom:hover {
            background: #4e9a26;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(95, 178, 48, 0.4);
        }

        .badge-locked {
            font-size: 0.75rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 30px;
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
            display: inline-block;
            margin-bottom: 1.2rem;
        }

        .info-box {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 14px;
            padding: 16px;
            text-align: left;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="card-disabled p-4 p-md-5">
        <div class="icon-circle">
            <i class="bi bi-shield-lock-fill"></i>
        </div>

        <div>
            <span class="badge-locked">Módulo No Contratado</span>
        </div>

        <h2 class="fw-bold text-white mb-2">{{ $nombreModulo ?? 'Módulo no disponible' }}</h2>
        <p class="text-muted mb-4" style="line-height: 1.6; font-size: 0.95rem;">
            Esta funcionalidad no forma parte de los módulos habilitados para esta instalación o licencia de Inbox BPM.
        </p>

        <div class="info-box">
            <div class="d-flex align-items-center gap-2 mb-1 fw-bold text-white">
                <i class="bi bi-info-circle-fill text-warning"></i> ¿Desea incorporar este módulo?
            </div>
            <div class="text-muted" style="font-size: 0.85rem;">
                Comuníquese con el administrador del sistema o su proveedor de software para gestionar la activación de esta característica en sus servidores.
            </div>
        </div>

        <div class="d-flex justify-content-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn-custom">
                <i class="bi bi-speedometer2"></i> Ir al Dashboard
            </a>
            <button onclick="window.history.back();" class="btn btn-outline-secondary px-3" style="border-radius: 12px; border-color: rgba(255,255,255,0.2); color: #cbd5e1;">
                Regresar
            </button>
        </div>
    </div>
</body>
</html>
