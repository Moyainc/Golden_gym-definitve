<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Detalle de tu compra en Golden Gym</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            -ms-interpolation-mode: bicubic;
        }

        @media screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                padding: 10px !important;
            }

            .header img {
                width: 120px !important;
                height: auto !important;
            }

            .content {
                padding: 15px !important;
                font-size: 16px !important;
            }

            .button {
                padding: 12px 20px !important;
                font-size: 16px !important;
            }

            .table-products {
                width: 100% !important;
                font-size: 14px !important;
            }
        }

        .table-products th, .table-products td {
            padding: 8px;
            text-align: left;
        }

        .table-products th {
            background-color: #fcd52b;
            color: #1a1a1a;
        }

        .table-products td {
            background-color: #333333;
            color: #ffffff;
        }
    </style>
</head>

<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#1a1a1a; color:#ffffff;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table class="container" width="600" cellpadding="0" cellspacing="0"
                    style="background-color:#272727; margin:20px auto; border-radius:10px; overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td class="header" style="background-color:#fcd52b; padding:20px; text-align:center;">
                            <img src="{{ asset('img/icons/golden_gym.png') }}" alt="Golden Gym" width="150">
                        </td>
                    </tr>

                    <!-- Contenido principal -->
                    <tr>
                        <td class="content" style="padding:30px; text-align:left; color:#ffffff;">
                            <h1 style="color:#fcd52b;">¡Hola, {{ $cliente->nombre_completo }}!</h1>
                            <p>Gracias por tu compra en Golden Gym. Aquí tienes el detalle de tu venta:</p>

                            <p><strong>Atendido por:</strong> {{ $usuario->nombre_completo }}</p>
                            <p><strong>Método de pago:</strong> {{ $metodoPago }}</p>
                            <p><strong>Total:</strong> {{ number_format((float) $total, 0, ',', '.') }} COP</p>

                            <h3 style="color:#fcd52b;">Productos adquiridos:</h3>
                            <table class="table-products" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                                @foreach($productos as $detalle)
                                <tr>
                                    <td>{{ $detalle->producto->nombre }}</td>
                                    <td>{{ $detalle->cantidad }}</td>
                                    <td>{{ number_format((float) $detalle->precio_unitario, 0, ',', '.') }} COP</td>
                                    <td>{{ number_format((float) $detalle->subtotal, 0, ',', '.') }} COP</td>
                                </tr>
                                @endforeach
                            </table>

                            <p>¡Gracias por confiar en nosotros! Si tienes alguna duda o necesitas asistencia, estamos aquí para ayudarte.</p>
                        </td>
                    </tr>

                    <!-- Botón -->
                    <tr>
                        <td align="center" style="padding:20px;">
                            <a href="{{ route('inicio') }}"
                                class="button"
                                style="background-color:#fcd52b; color:#1a1a1a; text-decoration:none; padding:15px 30px; border-radius:5px; display:inline-block; font-weight:bold;">
                                Visita nuestro sitio web
                            </a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px; text-align:center; font-size:12px; color:#cccccc; background-color:#272727;">
                            © 2025 Golden Gym. Todos los derechos reservados.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
