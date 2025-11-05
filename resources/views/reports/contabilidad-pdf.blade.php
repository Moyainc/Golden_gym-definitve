<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Contabilidad - Golden Gym</title>
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
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #fcd52b;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #1a1a1a;
            font-size: 28px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .header h2 {
            color: #555;
            font-size: 14px;
            margin-top: 4px;
        }

        .info-section {
            background-color: #1a1a1a;
            color: #fff;
            padding: 15px;
            border-left: 6px solid #fcd52b;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .info-section p {
            font-size: 11px;
            margin: 4px 0;
        }

        .info-section strong {
            color: #fcd52b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead {
            background-color: #1a1a1a;
            color: #fff;
        }

        thead th {
            padding: 8px;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #fcd52b;
        }

        tbody tr:nth-child(even) {
            background-color: #f8f8f8;
        }

        tbody td {
            padding: 6px 8px;
            font-size: 10px;
            border-bottom: 1px solid #ddd;
        }

        .total-row {
            background-color: #1a1a1a;
            color: #fff;
            font-weight: bold;
        }

        .total-row td {
            padding: 8px;
            border-bottom: 2px solid #fcd52b;
        }

        .summary {
            background: #1a1a1a;
            color: #fff;
            padding: 15px;
            border-left: 6px solid #fcd52b;
            margin-top: 20px;
            border-radius: 4px;
        }

        .summary p {
            margin: 4px 0;
            font-size: 11px;
        }

        .summary strong {
            color: #fcd52b;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>GOLDEN GYM</h1>
        <h2>Reporte de Contabilidad de Membresías y Pagos</h2>
        <p style="font-size: 10px; color: #666; margin-top: 10px;">{{ $fecha_inicio }} al {{ $fecha_fin }}</p>
    </div>

    <div class="info-section">
        <p><strong>Período:</strong> {{ $fecha_inicio }} al {{ $fecha_fin }}</p>
        <p><strong>Fecha de Generación:</strong> {{ $fecha_generacion }}</p>
    </div>

    <!-- SECCIÓN DE MEMBRESÍAS -->
    <h3 style="color: #1a1a1a; font-size: 14px; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #fcd52b; padding-bottom: 5px;">Ingresos por Membresías</h3>

    @if($pagosMembresias->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Tipo de Membresía</th>
                    <th>Monto</th>
                    <th>Método Pago</th>
                    <th>Fecha Inicio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagosMembresias as $pago)
                <tr>
                    <td>{{ $pago->id }}</td>
                    <td>{{ $pago->cliente->nombre_completo }}</td>
                    <td>{{ $pago->membresia->nombre }}</td>
                    <td>${{ number_format($pago->monto, 2) }}</td>
                    <td>{{ ucfirst($pago->metodo_pago) }}</td>
                    <td>{{ $pago->fecha_inicio->format('d/m/Y') }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">TOTAL MEMBRESÍAS:</td>
                    <td colspan="3">${{ number_format($totalMembresias, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #666; padding: 20px;">No hay registros de membresías en este período</p>
    @endif

    <!-- SECCIÓN DE PAGOS DIARIOS -->
    <h3 style="color: #1a1a1a; font-size: 14px; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #fcd52b; padding-bottom: 5px;">Ingresos por Pagos Diarios</h3>

    @if($pagosDiarios->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Monto</th>
                    <th>Método Pago</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagosDiarios as $pago)
                <tr>
                    <td>{{ $pago->id }}</td>
                    <td>{{ $pago->nombre_cliente }}</td>
                    <td>${{ number_format($pago->monto, 2) }}</td>
                    <td>{{ ucfirst($pago->metodo_pago) }}</td>
                    <td>{{ $pago->fecha->format('d/m/Y') }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2" style="text-align: right;">TOTAL PAGOS DIARIOS:</td>
                    <td colspan="3">${{ number_format($totalDiarios, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #666; padding: 20px;">No hay registros de pagos diarios en este período</p>
    @endif

    <!-- RESUMEN GENERAL -->
    <div class="summary">
        <p><strong>Total Membresías:</strong> ${{ number_format($totalMembresias, 2) }}</p>
        <p><strong>Total Pagos Diarios:</strong> ${{ number_format($totalDiarios, 2) }}</p>
        <p style="margin-top: 10px; font-size: 12px; border-top: 1px solid #fcd52b; padding-top: 8px;"><strong>TOTAL INGRESOS: ${{ number_format($totalIngresos, 2) }}</strong></p>
    </div>

    <div class="footer">
        <p><strong>Golden Gym - Sistema de Gestión de Contabilidad</strong></p>
        <p>Documento generado automáticamente el {{ $fecha_generacion }}</p>
    </div>
</body>
</html>
