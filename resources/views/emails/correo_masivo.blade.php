<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Golden Gym - Notificación</title>
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
                font-size: 15px !important;
            }
        }
    </style>
</head>

<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#1a1a1a; color:#ffffff;">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">

                <table class="container" width="600" cellpadding="0" cellspacing="0"
                    style="background-color:#272727; margin:20px auto; border-radius:10px; overflow:hidden;">

                    <!-- HEADER -->
                    <tr>
                        <td class="header" style="background-color:#fcd52b; padding:20px; text-align:center;">
                            <img src="{{ asset('img/icons/golden_gym.png') }}" alt="Golden Gym" width="150">
                        </td>
                    </tr>

                    <!-- CONTENIDO -->
                    <tr>
                        <td class="content" style="padding:30px; text-align:left; color:#ffffff;">
                            <h2 style="color:#fcd52b; margin-bottom:15px;">
                                Notificación de Golden Gym
                            </h2>

                            <div style="font-size:16px; line-height:1.6;">
                                {!! $mensaje !!}
                            </div>

                            @if(!empty($imagenes))
                            <h3 style="color:#fcd52b; margin-top:25px;">Imágenes adjuntas</h3>

                            @foreach($imagenes as $img)
                            <div style="margin-top:10px;">
                                <img src="{{ $img }}" alt="Imagen" style="max-width:100%; border-radius:8px;">
                            </div>
                            @endforeach
                            @endif
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="padding:20px; text-align:center; font-size:12px; color:#cccccc; background-color:#272727;">
                            © {{ date('Y') }} Golden Gym. Todos los derechos reservados.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>