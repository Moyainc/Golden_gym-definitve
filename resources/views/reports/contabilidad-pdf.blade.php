<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Membresías - Golden Gym</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            padding: 30px;
            line-height: 1.5;
            background: #fff;
        }

        h1,
        h2,
        h3 {
            font-family: 'Poppins', sans-serif;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #fcd52b;
            padding-bottom: 20px;
            margin-bottom: 35px;
        }

        .logo {
            width: 85px;
            height: 85px;
            object-fit: contain;
            margin-right: 20px;
        }

        .logo-placeholder {
            width: 85px;
            height: 85px;
            background: #1a1a1a;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #fcd52b;
            font-size: 24px;
        }

        .header-info {
            flex: 1;
            text-align: center;
        }

        .header h1 {
            color: #1a1a1a;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header h2 {
            color: #555;
            font-size: 14px;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        .header-date {
            color: #777;
            font-size: 10px;
            margin-top: 6px;
        }

        .info-section {
            background: #1a1a1a;
            color: #fff;
            padding: 18px 22px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 6px solid #fcd52b;
        }

        .info-section p {
            font-size: 11px;
            margin: 6px 0;
        }

        .info-section strong {
            color: #fcd52b;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead {
            background: #1a1a1a;
            color: #fff;
        }

        thead th {
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 3px solid #fcd52b;
        }

        tbody tr:nth-child(even) {
            background: #f8f8f8;
        }

        tbody tr:hover {
            background: #fcd52b22;
        }

        tbody td {
            padding: 8px;
            font-size: 10px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
            color: #1a1a1a;
        }

        /* Texto visible */
        tbody td strong {
            color: #1a1a1a;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-activa {
            background: #28a745;
            color: #fff;
        }

        .badge-proxima {
            background: #fcd52b;
            color: #1a1a1a;
        }

        .badge-vencida {
            background: #dc3545;
            color: #fff;
        }

        .badge-sin {
            background: #6c757d;
            color: #fff;
        }

        .total-section {
            margin-top: 25px;
            background: #1a1a1a;
            padding: 15px;
            border-left: 6px solid #fcd52b;
            border-radius: 4px;
            color: #fff;
            font-size: 12px;
            font-weight: bold;
        }

        .total-section-light {
            text-align: center;
            margin-top: 25px;
            padding: 15px;
            border-left: 6px solid #fcd52b;
            border-radius: 4px;
            color: #1a1a1a;
            background: #fff;
            font-size: 12px;
            font-weight: bold;
        }


        .total-section strong {
            color: #fcd52b;
        }

        .total-section p {
            margin: 4px 0;
        }

        .footer {
            margin-top: 45px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }

        .footer p:first-child {
            font-weight: 700;
            color: #1a1a1a;
        }
    </style>
</head>

<body>
    <div class="header">
        @if(file_exists(public_path('img/icons/golden_gym.png')))
        <img src="{{ asset('img/icons/golden_gym.png') }}" alt="Logo Golden Gym" class="logo">
        @elseif(file_exists(public_path('images/logo-gym.png')))
        <img src="{{ asset('img/icons/golden_gym.png') }}" alt="Logo Golden Gym" class="logo">
        @else
        <div class="logo-placeholder">GYM</div>
        @endif
        <div class="header-info">
            <h1>GOLDEN GYM</h1>
            <h2>Reporte de Membresías y Pagos</h2>
            <div class="header-date">Generado: {{ $fecha_generacion }}</div>
        </div>
    </div>

    <div class="info-section">
        <p><strong>Período de Reporte:</strong> {{ $fecha_inicio }} al {{ $fecha_fin }}</p>
        <p><strong>Fecha de Generación:</strong> {{ $fecha_generacion }}</p>
        <p><strong>Total Membresías:</strong> {{ $pagosMembresias->count() }}</p>
        <p><strong>Total Pagos Diarios:</strong> {{ $pagosDiarios->count() }}</p>
    </div>

    {{-- Membresías --}}
    @if($pagosMembresias->count() > 0)
    <h3 style="margin-top:30px; border-bottom:2px solid #fcd52b; padding-bottom:5px;">Membresías</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Membresía</th>
                <th>Monto</th>
                <th>Método Pago</th>
                <th>Registrado por</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagosMembresias as $index => $pago)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $pago->cliente->nombre_completo ?? 'Sin cliente' }}</strong></td>
                <td>{{ $pago->membresia->nombre ?? 'N/A' }}</td>
                <td>${{ number_format($pago->monto,2) }}</td>
                <td>{{ ucfirst($pago->metodo_pago ?? 'N/A') }}</td>
                <td>{{ $pago->usuario->nombre_completo ?? 'Desconocido' }}</td>
                <td>{{ \Carbon\Carbon::parse($pago->fecha_inicio)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($pago->fecha_fin)->format('d/m/Y') }}</td>
                <td>
                    @if($pago->estado === 'Activa')
                    <span class="badge badge-activa">Activa</span>
                    @elseif($pago->estado === 'Próxima a vencer')
                    <span class="badge badge-proxima">Próxima</span>
                    @elseif($pago->estado === 'Vencida')
                    <span class="badge badge-vencida">Vencida</span>
                    @else
                    <span class="badge badge-sin">Sin Membresía</span>
                    @endif
                </td>
            </tr>
            @endforeach
            <tr class="total-section-light">
                <td colspan="3">TOTAL MEMBRESÍAS:</td>
                <td colspan="5">${{ number_format($totalMembresias,2) }}</td>
            </tr>
        </tbody>
    </table>
    @else
    <p style="text-align:center; margin:20px; color:#666;">No hay registros de membresías en este período</p>
    @endif

    {{-- Pagos Diarios --}}
    @if($pagosDiarios->count() > 0)
    <h3 style="margin-top:30px; border-bottom:2px solid #fcd52b; padding-bottom:5px;">Pagos Diarios</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Monto</th>
                <th>Método Pago</th>
                <th>Registrado por</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagosDiarios as $index => $pago)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $pago->nombre_cliente ?? 'Sin nombre' }}</strong></td>
                <td>${{ number_format($pago->monto,2) }}</td>
                <td>{{ ucfirst($pago->metodo_pago ?? 'N/A') }}</td>
                <td>{{ $pago->usuario->nombre_completo ?? 'Desconocido' }}</td>
                <td>{{ \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') }}</td>
            </tr>
            @endforeach
            <tr class="total-section-light">
                <td colspan="3">TOTAL PAGOS DIARIOS:</td>
                <td colspan="5">${{ number_format($totalDiarios,2) }}</td>
            </tr>
        </tbody>
    </table>
    @else
    <p style="text-align:center; margin:20px; color:#666;">No hay registros de pagos diarios en este período</p>
    @endif

    {{-- Total General --}}
    <div class="total-section">
        <p><strong>TOTAL GENERAL:</strong> ${{ number_format($totalMembresias + $totalDiarios,2) }}</p>
    </div>

    <div class="footer">
        <p><strong>Golden Gym - Sistema de Gestión de Contabilidad</strong></p>
        <p>Documento generado automáticamente el {{ $fecha_generacion }}</p>
        <p style="font-size:8px; color:#aaa;">Sistema Confidencial - Uso Interno</p>
    </div>
</body>

</html>