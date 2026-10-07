<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bitacora_TCU_{{ str_replace(' ', '_', $bitacora->estudiante->nombre ?? 'Estudiante') }}.pdf</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            margin: 20mm 15mm;
            font-size: 11pt;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 16pt;
            margin: 0 0 5px 0;
            font-weight: bold;
        }

        .header h2 {
            font-size: 11pt;
            margin: 0;
            font-weight: normal;
        }

        .logo {
            display: block;
            margin: 20px auto;
            width: 200px;
        }

        .section-title {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            margin-top: 30px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .data-table th, .data-table td {
            border: 1px solid #999;
            padding: 8px 12px;
            text-align: left;
        }

        .data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }

        .info-block {
            text-align: center;
            margin-bottom: 20px;
        }

        .info-block p {
            margin: 4px 0;
        }

        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 9pt;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="background-color: #1e293b; padding: 15px; text-align: center; margin-bottom: 20px; border-radius: 8px;">
        <button onclick="window.print()" style="background-color: #5fb230; color: white; border: none; padding: 10px 20px; font-weight: bold; border-radius: 5px; cursor: pointer; margin-right: 10px;">
            <i class="bi bi-printer"></i> Imprimir / Guardar como PDF
        </button>
        <button onclick="window.close()" style="background-color: #475569; color: white; border: none; padding: 10px 20px; font-weight: bold; border-radius: 5px; cursor: pointer;">
            Cerrar Vista
        </button>
    </div>

    <!-- PÁGINA 1: PORTADA / DATOS GENERALES -->
    <div class="header">
        <h1>BITÁCORA</h1>
        <h2>DEL TRABAJO COMUNAL UNIVERSITARIO</h2>
        <img class="logo" src="{{ \App\Services\ClienteService::logoUrl() }}" alt="Logo {{ config('cliente.nombre', 'CEFI') }}">
    </div>

    <div class="section-title">PROYECTO</div>

    <div class="info-block">
        <p><strong>Datos del Proyecto:</strong></p>
        <p>{{ $bitacora->nombre_proyecto }}</p>
    </div>

    <div class="info-block" style="margin-top: 20px;">
        <p><strong>Lugar donde se realizó:</strong></p>
        <p>{{ $bitacora->lugar_realizacion }}</p>
    </div>

    <table class="data-table" style="margin-top: 30px;">
        <thead>
            <tr class="text-center">
                <th>Total de horas</th>
                <th>Fecha de inicio</th>
                <th>Fecha de finalización</th>
            </tr>
        </thead>
        <tbody>
            <tr class="text-center">
                <td><strong>{{ number_format($total_horas, 2) }}</strong></td>
                <td>{{ $fecha_inicio ? $fecha_inicio->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $fecha_final ? $fecha_final->format('d/m/Y') : 'N/A' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title" style="margin-top: 40px;">ESTUDIANTE</div>

    <table class="data-table">
        <thead>
            <tr class="text-center">
                <th style="width: 65%;">Nombre Completo</th>
                <th style="width: 35%;">Carrera</th>
            </tr>
        </thead>
        <tbody>
            <tr class="text-center">
                <td>{{ $bitacora->estudiante->nombre ?? 'N/A' }} {{ $bitacora->estudiante->apellidos ?? '' }}</td>
                <td>{{ $bitacora->carrera }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 30px; font-size: 11pt;">
        <p>Nombre del supervisor: <strong style="border-bottom: 1px solid #333; display: inline-block; padding-bottom: 2px; width: 70%;">{{ $bitacora->nombre_supervisor }} (Ced: {{ $bitacora->cedula_supervisor }})</strong></p>
    </div>

    <!-- PÁGINA 2: TABLA DE ACTIVIDADES -->
    <div class="page-break"></div>

    <div class="section-title" style="margin-top: 0;">DETALLE DE ACTIVIDADES</div>

    <table class="data-table">
        <thead>
            <tr style="background-color: #e5e7eb;">
                <th class="text-center" style="width: 15%;">Fecha</th>
                <th class="text-center" style="width: 12%;">Entrada</th>
                <th class="text-center" style="width: 12%;">Salida</th>
                <th class="text-center" style="width: 12%;">Horas</th>
                <th style="width: 49%;">Actividades</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($actividades as $act)
                <tr>
                    <td class="text-center">{{ $act->fecha->format('d/m/Y') }}</td>
                    <td class="text-center">{{ Carbon\Carbon::parse($act->hora_entrada)->format('g:i a') }}</td>
                    <td class="text-center">{{ Carbon\Carbon::parse($act->hora_salida)->format('g:i a') }}</td>
                    <td class="text-center">{{ $act->cantidad_horas }}</td>
                    <td>{{ $act->actividades }}</td>
                </tr>
            @endforeach
            <tr style="background-color: #f3f4f6; font-weight: bold;">
                <td colspan="3" class="text-right">Total Horas:</td>
                <td class="text-center">{{ number_format($total_horas, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- FIRMAS AL FINAL -->
    <div class="signature-section">
        <div class="signature-box">
            <p style="margin-bottom: 2px;"><strong>{{ $bitacora->nombre_supervisor }}</strong></p>
            <p class="small" style="margin: 0; color: #666;">Ced: {{ $bitacora->cedula_supervisor }}</p>
            <div class="signature-line">
                Nombre completo y N° cédula del supervisor
                <div style="font-size: 7.5pt; color: #888; margin-top: 3px; font-style: italic;">Firmar después de imprimir el documento</div>
            </div>
        </div>
        
        <div class="signature-box">
            <p style="margin-bottom: 2px;"><strong>{{ $bitacora->email_institucion }}</strong></p>
            <p class="small" style="margin: 0; color: #666;">Tel: {{ $bitacora->telefono_institucion }}</p>
            <div class="signature-line">
                Correo electrónico y número de teléfono de la institución beneficiada
            </div>
        </div>
    </div>

    <script>
        // Auto print window on load
        window.addEventListener('load', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
