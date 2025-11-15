<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pagos Diarios - Golden Gym</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            padding: 30px;
            line-height: 1.5;
            background-color: #fff;
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
        .header-info {
            flex: 1;
            text-align: center;
        }

        .header h1 {
            color: #1a1a1a;
            font-size: 32px;
            font-weight: 900;
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
            background-color: #1a1a1a;
            color: #fff;
            padding: 18px 22px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 6px solid #fcd52b;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
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
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }

        thead {
            background-color: #1a1a1a;
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
            background-color: #f8f8f8;
        }
        tbody tr:hover {
            background-color: #fcd52b22;
        }

        tbody td {
            padding: 8px;
            font-size: 10px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-efectivo { background-color: #28a745; color: #fff; }
        .badge-transferencia { background-color: #17a2b8; color: #fff; }
        .badge-tarjeta { background-color: #6f42c1; color: #fff; }

        .total-section {
            margin-top: 25px;
            background: #1a1a1a;
            padding: 15px;
            border-left: 6px solid #fcd52b;
            border-radius: 4px;
            color: #fff;
            font-size: 12px;
        }

        .total-section strong {
            color: #fcd52b;
        }
        .total-section p {
            margin: 4px 0;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #777;
            font-style: italic;
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
    </style>
</head>

<body>
    <div class="header">
        @if(file_exists(public_path('img/icons/golden_gym.png')))
            <img src="{{ public_path('img/icons/golden_gym.png') }}" alt="Logo Golden Gym" class="logo">
        @elseif(file_exists(public_path('images/logo-gym.png')))
            <img src="{{ public_path('images/logo-gym.png') }}" alt="Logo Golden Gym" class="logo">
        @else
            <div class="logo-placeholder">GYM</div>
        @endif
        <div class="header-info">
            <h1>GOLDEN GYM</h1>
            <h2>Reporte de Pagos Diarios</h2>
            <div class="header-date">Generado: {{ $fecha_generacion }}</div>
        </div>
    </div>

    <div class="info-section">
        <p><strong>Período de Reporte:</strong> {{ $fecha_inicio }} al {{ $fecha_fin }}</p>
        <p><strong>Fecha de Generación:</strong> {{ $fecha_generacion }}</p>
        <p><strong>Total de Pagos:</strong> <span style="font-size: 13px;">{{ $total }}</span></p>
        <p><strong>Total Ingreso:</strong> <span style="font-size: 13px;">$ {{ number_format($total_monto, 2) }}</span></p>
    </div>

    @if($pagos->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre Cliente</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                    <th>Método de Pago</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagos as $index => $pago)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $pago->nombre_cliente }}</strong></td>
                    <td>${{ number_format($pago->monto, 2) }}</td>
                    <td>{{ $pago->fecha->format('d/m/Y') }}</td>
                    <td>
                        @if($pago->metodo_pago === 'efectivo')
                            <span class="badge badge-efectivo">Efectivo</span>
                        @elseif($pago->metodo_pago === 'transferencia')
                            <span class="badge badge-transferencia">Transferencia</span>
                        @elseif($pago->metodo_pago === 'tarjeta')
                            <span class="badge badge-tarjeta">Tarjeta</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <p><strong>Total de Pagos:</strong> {{ $total }}</p>
            <p><strong>Total Ingreso:</strong> ${{ number_format($total_monto, 2) }}</p>
        </div>
    @else
        <div class="empty-state">
            <p>No se encontraron pagos en el rango de fechas seleccionado</p>
        </div>
    @endif

    <div class="footer">
        <p><strong>Golden Gym - Sistema de Gestión de Pagos</strong></p>
        <p>Documento generado automáticamente el {{ $fecha_generacion }}</p>
        <p style="font-size: 8px; color: #aaa;">Sistema Confidencial - Uso Interno</p>
    </div>
</body>

</html>
