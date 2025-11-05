<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Clientes - Golden Gym</title>
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

        /* ===== ENCABEZADO ===== */
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

        /* ===== SECCIÓN INFORMATIVA ===== */
        .info-section {
            background-color: #1a1a1a;
            color: #fff;
            padding: 18px 22px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 6px solid #fcd52b;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .info-section p {
            font-size: 11px;
            margin: 6px 0;
        }

        .info-section strong {
            color: #fcd52b;
            font-weight: 700;
        }

        /* ===== TABLA ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
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

        tbody td strong {
            color: #1a1a1a;
        }

        /* ===== BADGES ===== */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-activa {
            background-color: #28a745;
            color: #fff;
        }

        .badge-proxima {
            background-color: #fcd52b;
            color: #1a1a1a;
        }

        .badge-vencida {
            background-color: #dc3545;
            color: #fff;
        }

        .badge-sin {
            background-color: #6c757d;
            color: #fff;
        }

        /* ===== SECCIÓN TOTAL ===== */
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

        /* ===== ESTADO VACÍO ===== */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #777;
            font-style: italic;
        }

        /* ===== PIE DE PÁGINA ===== */
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

        /* ===== LOGO ALTERNATIVO ===== */
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
            <h2>Reporte de Clientes y Membresías</h2>
            <div class="header-date">Generado: {{ $fecha_generacion }}</div>
        </div>
    </div>

    <div class="info-section">
        <p><strong>Período de Reporte:</strong> {{ $fecha_inicio }} al {{ $fecha_fin }}</p>
        <p><strong>Fecha de Generación:</strong> {{ $fecha_generacion }}</p>
        <p><strong>Total de Clientes:</strong> <span style="font-size: 13px;">{{ $total }}</span></p>
    </div>

    @if($clientes->count() > 0)
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre Completo</th>
                <th>Cédula</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Membresía</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Monto</th>
                <th>Método Pago</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $index => $cliente)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $cliente->nombre_completo }}</strong></td>
                <td>{{ $cliente->cedula ?? 'N/A' }}</td>
                <td>{{ $cliente->correo ?? 'N/A' }}</td>
                <td>{{ $cliente->telefono ?? 'N/A' }}</td>
                <td>{{ $cliente->membresia_nombre }}</td>
                <td>{{ $cliente->fecha_inicio }}</td>
                <td>{{ $cliente->fecha_fin }}</td>
                <td>${{ number_format($cliente->monto, 2) }}</td>
                <td>{{ ucfirst($cliente->metodo_pago ?? 'N/A') }}</td>
                <td>
                    @if($cliente->estado === 'activa')
                    <span class="badge badge-activa">Activa</span>
                    @elseif($cliente->estado === 'proxima')
                    <span class="badge badge-proxima">Próxima</span>
                    @elseif($cliente->estado === 'vencida')
                    <span class="badge badge-vencida">Vencida</span>
                    @else
                    <span class="badge badge-sin">Sin Membresía</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <p><strong>Total de Registros:</strong> {{ $total }}</p>
        <p style="font-size: 10px;">
            Activas: <strong>{{ $clientes->where('estado', 'activa')->count() }}</strong> |
            Próximas: <strong>{{ $clientes->where('estado', 'proxima')->count() }}</strong> |
            Vencidas: <strong>{{ $clientes->where('estado', 'vencida')->count() }}</strong>
        </p>
    </div>
    @else
    <div class="empty-state">
        <p>No se encontraron clientes en el rango de fechas seleccionado</p>
    </div>
    @endif

    <div class="footer">
        <p><strong>Golden Gym - Sistema de Gestión de Clientes</strong></p>
        <p>Documento generado automáticamente el {{ $fecha_generacion }}</p>
        <p style="font-size: 8px; color: #aaa;">Sistema Confidencial - Uso Interno</p>
    </div>
</body>

</html>
