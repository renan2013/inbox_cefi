<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Ejecutivo del Curso — {{ $curso->planEstudio->codigo ?? '' }} {{ $curso->planEstudio->materia ?? '' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f1f5f9; font-family: 'Segoe UI', Arial, sans-serif; color: #0f172a; }
        .sheet { max-width: 900px; margin: 30px auto; background: #fff; padding: 45px 50px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .header-bar { height: 5px; background: linear-gradient(90deg, #003b73 50%, #5fb230 50%); margin: 15px 0 25px 0; }
        .sec-title { background: #003b73; color: #fff; font-weight: 700; padding: 8px 14px; font-size: 0.9rem; text-transform: uppercase; border-left: 6px solid #5fb230; margin-bottom: 0; }
        @media print {
            body { background: #fff; }
            .sheet { margin: 0; padding: 0; box-shadow: none; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="container max-w-900 mt-3 no-print d-flex justify-content-end gap-2" style="max-width: 900px;">
        <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-printer-fill me-1"></i>Imprimir / Guardar PDF
        </button>
    </div>

    <div class="sheet">
        <div class="text-center">
            <img src="{{ asset(ltrim(config('cliente.logo_url', 'imgs/logo_cefi.png'), '/')) }}" alt="{{ config('cliente.nombre', 'CEFI') }}" style="max-height: 95px;">
            <h5 class="fw-bold mt-2 mb-0" style="color:#003b73;">{{ config('cliente.nombre_legal', 'CENTRO DE ENSEÑANZA DE FORMACIÓN INTEGRAL') }}</h5>
            <div class="fw-bold text-success">{{ $curso->planEstudio->programa->nombre_programa ?? 'Programa Académico' }}</div>
            <h4 class="fw-extrabold mt-2 mb-0">INFORME EJECUTIVO Y NÓMINA OFICIAL DE CURSO</h4>
        </div>
        <div class="header-bar"></div>

        <div class="sec-title">1. Datos Generales del Grupo Activo</div>
        <table class="table table-bordered table-sm mb-4">
            <tbody>
                <tr>
                    <th class="bg-light" style="width:25%;">Código y Materia</th>
                    <td><strong>{{ $curso->planEstudio->codigo ?? 'S/C' }}</strong> — {{ $curso->planEstudio->materia ?? '' }}</td>
                    <th class="bg-light" style="width:20%;">Período</th>
                    <td>{{ $curso->periodo ?? 'Activo' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Docente Titular</th>
                    <td>{{ trim(($curso->profesor->nombre ?? '') . ' ' . ($curso->profesor->apellidos ?? '')) ?: 'Por asignar' }}</td>
                    <th class="bg-light">Horario</th>
                    <td>{{ $curso->horario ?: 'Según programación' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Vigencia</th>
                    <td colspan="3">
                        Inicio: {{ $curso->fecha_inicio ? date('d/m/Y', strtotime($curso->fecha_inicio)) : '—' }} |
                        Finalización: {{ $curso->fecha_final ? date('d/m/Y', strtotime($curso->fecha_final)) : '—' }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="sec-title">2. Nómina de Estudiantes Matriculados ({{ $estudiantes->count() }})</div>
        <table class="table table-bordered table-striped table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:45px;" class="text-center">#</th>
                    <th>Identificación</th>
                    <th>Estudiante</th>
                    <th>Correo Electrónico</th>
                    <th class="text-center">Nota Actual</th>
                </tr>
            </thead>
            <tbody>
                @forelse($estudiantes as $idx => $est)
                    <tr>
                        <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                        <td>{{ $est->cedula ?: '—' }}</td>
                        <td class="fw-semibold">{{ $est->nombre }} {{ $est->apellidos }}</td>
                        <td class="small">{{ $est->email }}</td>
                        <td class="text-center fw-bold">{{ $est->calificacion !== null ? number_format((float)$est->calificacion, 1) : '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-3 text-muted">No hay estudiantes matriculados en este grupo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4 pt-3 border-top d-flex justify-content-between small text-muted">
            <span>Generado por Inbox BPM 2.0 — {{ date('d/m/Y H:i') }}</span>
            <span>{{ config('cliente.sitio_web', 'ceficr.com') }}</span>
        </div>
    </div>
</body>
</html>
