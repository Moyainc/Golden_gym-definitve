<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reporte de Ventas - Golden Gym</title>
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
    <h2>Reporte de Ventas</h2>
    <p style="font-size: 10px; color: #666; margin-top: 10px;">Mes: {{ $mes }} / Año: {{ $anio }}</p>
  </div>

  <div class="info-section">
    <p><strong>Período:</strong> {{ $mes }} / {{ $anio }}</p>
    <p><strong>Fecha de Generación:</strong> {{ now()->format('d/m/Y H:i') }}</p>
  </div>

  <!-- TABLA DE VENTAS -->
  @if($ventas->count() > 0)
  <table>
    <thead>
      <tr>
        <th>ID Venta</th>
        <th>Producto</th>
        <th>Categoría</th>
        <th>Método de Pago</th>
        <th>Precio</th>
        <th>Fecha</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody>
      @foreach($ventas as $venta)
      @foreach($venta->detalles as $detalle)
      <tr>
        <td>{{ $venta->id }}</td>
        <td>{{ $detalle->producto->nombre ?? 'Producto Eliminado' }}</td>
        <td>{{ $detalle->producto->categoria->nombre ?? 'Sin Categoría' }}</td>
        <td>{{ ucfirst($venta->metodo_pago) }}</td>
        <td>${{ number_format($detalle->subtotal, 0) }}</td>
        <td>{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }}</td>
        <td>{{ $venta->estado }}</td>
      </tr>
      @endforeach
      @endforeach
      <tr class="total-row">
        <td colspan="4" style="text-align: right;">TOTAL VENTAS:</td>
        <td colspan="3">${{ number_format($totalVentas, 0) }}</td>
      </tr>
    </tbody>
  </table>
  @else
  <p style="text-align: center; color: #666; padding: 20px;">No hay registros de ventas en este período</p>
  @endif

  <div class="summary">
    <p><strong>Total Ventas:</strong> ${{ number_format($totalVentas, 0) }}</p>
    <p><strong>Total Productos Vendidos:</strong> {{ $numProductos }}</p>
    <p><strong>Total Ventas Pendientes:</strong> {{ $pendientes }}</p>
  </div>

  <div class="footer">
    <p><strong>Golden Gym - Sistema de Gestión de Ventas</strong></p>
    <p>Documento generado automáticamente el {{ now()->format('d/m/Y H:i') }}</p>
  </div>
</body>

</html>
