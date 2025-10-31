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
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #ffc107;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #ffc107;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header h2 {
            color: #666;
            font-size: 16px;
            font-weight: normal;
        }
        .info-section {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .info-section p {
            margin: 5px 0;
        }
        .info-section strong {
            color: #ffc107;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        thead {
            background-color: #ffc107;
            color: #000;
        }
        thead th {
            padding: 10px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }
        tbody tr {
            border-bottom: 1px solid #ddd;
        }
        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tbody td {
            padding: 8px 5px;
            font-size: 10px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }
        .badge-activa {
            background-color: #28a745;
            color: white;
        }
        .badge-proxima {
            background-color: #ffc107;
            color: #000;
        }
        .badge-vencida {
            background-color: #dc3545;
            color: white;
        }
        .badge-sin {
            background-color: #6c757d;
            color: white;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .total-section {
            margin-top: 20px;
            text-align: right;
            font-size: 12px;
        }
        .total-section strong {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>GOLDEN GYM</h1>
        <h2>Reporte de Clientes</h2>
    </div>

    <div class="info-section">
        <p><strong>Período:</strong> {{ $fecha_inicio }} - {{ $fecha_fin }}</p>
        <p><strong>Fecha de generación:</strong> {{ $fecha_generacion }}</p>
        <p><strong>Total de clientes:</strong> {{ $total }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Identificación</th>
                <th>Membresía</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientes as $index => $cliente)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $cliente->nombre_completo }}</td>
                <td>{{ $cliente->cedula ?? 'N/A' }}</td>
                <td>{{ $cliente->membresia_nombre }}</td>
                <td>{{ $cliente->fecha_inicio }}</td>
                <td>{{ $cliente->fecha_fin }}</td>
                <td>
                    @if($cliente->estado === 'activa')
                        <span class="badge badge-activa">Activa</span>
                    @elseif($cliente->estado === 'proxima')
                        <span class="badge badge-proxima">Próxima a vencer</span>
                    @elseif($cliente->estado === 'vencida')
                        <span class="badge badge-vencida">Vencida</span>
                    @else
                        <span class="badge badge-sin">Sin membresía</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; color: #999;">
                    No se encontraron clientes en el rango de fechas seleccionado
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-section">
        <p><strong>Total de registros:</strong> {{ $total }}</p>
    </div>

    <div class="footer">
        <p>Golden Gym - Sistema de Gestión de Clientes</p>
        <p>Documento generado automáticamente el {{ $fecha_generacion }}</p>
    </div>
</body>
</html>
