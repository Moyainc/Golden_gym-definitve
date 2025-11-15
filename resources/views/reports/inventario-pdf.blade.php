<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inventario - Golden Gym</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            font-family: 'poppins', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            padding: 30px;
            line-height: 1.5;
            background: #fff
        }

        h1,
        h2 {
            font-family: 'poppins', sans-serif
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #fcd52b;
            padding-bottom: 20px;
            margin-bottom: 25px
        }

        .logo {
            width: 85px;
            height: 85px;
            object-fit: contain;
            margin-right: 20px
        }

        .header-info {
            flex: 1;
            text-align: center
        }

        .header h1 {
            color: #1a1a1a;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase
        }

        .header h2 {
            color: #555;
            font-size: 14px;
            margin-top: 4px
        }

        .header-date {
            color: #777;
            font-size: 10px;
            margin-top: 6px
        }

        .info-section {
            background: #1a1a1a;
            color: #fff;
            padding: 12px 16px;
            border-left: 6px solid #fcd52b;
            border-radius: 6px;
            margin-bottom: 18px
        }

        .info-section p {
            font-size: 11px;
            margin: 4px 0
        }

        .info-section strong {
            color: #fcd52b
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px
        }

        thead {
            background: #1a1a1a;
            color: #fff
        }

        thead th {
            padding: 8px;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #fcd52b
        }

        tbody tr:nth-child(even) {
            background: #f8f8f8
        }

        tbody td {
            padding: 6px 8px;
            font-size: 10px;
            border-bottom: 1px solid #ddd
        }

        .total-row {
            background: #1a1a1a;
            color: #fff;
            font-weight: 700
        }

        .total-row td {
            padding: 8px;
            border-bottom: 2px solid #fcd52b
        }

        .summary {
            background: #1a1a1a;
            color: #fff;
            padding: 12px;
            border-left: 6px solid #fcd52b;
            border-radius: 6px;
            margin-top: 12px
        }

        .summary p {
            margin: 4px 0;
            font-size: 11px
        }

        .summary strong {
            color: #fcd52b
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px
        }
    </style>
</head>

<body>
    <div class="header">
        @if(file_exists(public_path('img/icons/golden_gym.png')))
        <img src="{{ public_path('img/icons/golden_gym.png') }}" alt="Logo Golden Gym" class="logo">
        @elseif(file_exists(public_path('images/logo-gym.png')))
        <img src="{{ public_path('img/icons/golden_gym.png') }}" alt="Logo Golden Gym" class="logo">
        @else
        <div class="logo-placeholder">GYM</div>
        @endif

        <div class="header-info">
            <h1>GOLDEN GYM</h1>
            <h2>Reporte de Inventario</h2>
            <div class="header-date">Generado: {{ $fecha_generacion }}</div>
        </div>
    </div>

    <div class="info-section">
        @if(isset($inicio) && isset($fin))
        <p><strong>Período:</strong> {{ \Carbon\Carbon::parse($inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fin)->format('d/m/Y') }}</p>
        @else
        <p><strong>Período:</strong> Todos</p>
        @endif
        <p><strong>Total categorías:</strong> {{ $totalCategorias ?? $categorias->count() ?? 0 }}</p>
        <p><strong>Total productos:</strong> {{ $totalProductos ?? $productos->count() ?? 0 }}</p>
    </div>

    {{-- TABLA A: LISTADO DE PRODUCTOS --}}
    <h3 style="margin-bottom:6px;border-bottom:2px solid #fcd52b;padding-bottom:6px;">Listado de Productos</h3>
    @if(isset($productos) && $productos->count() > 0)
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Precio unitario</th>
                <th>Estado</th>
                <th>Creado</th>
                <th>Última actualización</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $i => $producto)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->categoria->nombre ?? 'Sin Categoría' }}</td>
                <td>{{ $producto->stock }}</td>
                <td>${{ number_format($producto->precio, 0, ',', '.') }}</td>
                <td>{{ $producto->estado }}</td>
                <td>{{ \Carbon\Carbon::parse($producto->created_at)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($producto->updated_at)->format('d/m/Y') }}</td>
            </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="3" style="text-align:right">TOTAL PRODUCTOS LISTADOS:</td>
                <td colspan="5">{{ $totalProductos ?? $productos->count() }}</td>
            </tr>
        </tbody>
    </table>
    @else
    <p style="text-align:center;color:#666;padding:10px;">No hay productos para el período seleccionado.</p>
    @endif

    {{-- TABLA B: RESUMEN POR CATEGORÍA --}}
    <h3 style="margin-top:18px;margin-bottom:6px;border-bottom:2px solid #fcd52b;padding-bottom:6px;">Resumen por Categoría</h3>

    @if(isset($porCategoria) && $porCategoria->count() > 0)
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Categoría</th>
                <th>Productos (cantidad)</th>
                <th>Stock total</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach($porCategoria as $nombreCategoria => $datos)
            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ $nombreCategoria }}</td>
                <td>{{ $datos['cantidad'] }}</td>
                <td>{{ $datos['stock_total'] }}</td>
            </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="2" style="text-align:right;font-weight:bold;">TOTAL CATEGORÍAS:</td>
                <td colspan="2" style="font-weight:bold;">{{ $totalCategorias ?? $porCategoria->count() }}</td>
            </tr>
        </tbody>
    </table>
    @else
    <p style="text-align:center;color:#666;padding:10px;">No hay categorías para el período seleccionado.</p>
    @endif


    <div class="summary">
        <p><strong>Total categorías:</strong> {{ $totalCategorias ?? $categorias->count() ?? 0 }}</p>
        <p><strong>Total productos (listados):</strong> {{ $totalProductos ?? $productos->count() ?? 0 }}</p>
    </div>

    <div class="footer">
        <p><strong>Golden Gym - Sistema de Gestión de Inventario</strong></p>
        <p>Documento generado automáticamente el {{ $fecha_generacion }}</p>
    </div>
</body>

</html>