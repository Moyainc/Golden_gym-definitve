<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Motivación para tu entrenamiento</title>
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
                            <h1 style="color:#fcd52b;">¡Hola, {{ $nombre }}!</h1>

                            @php
                                $mensajes = [
                                    "¡Estás haciendo un gran trabajo! Sigue así y alcanzarás tus metas.",
                                    "Cada día es una nueva oportunidad para mejorar. ¡No te rindas!",
                                    "¡Golden Gym te acompaña en cada entrenamiento! Mantente constante.",
                                    "El esfuerzo de hoy será la recompensa de mañana. ¡Ánimo!",
                                    "Recuerda que la constancia supera a la perfección. ¡Sigue avanzando!",
                                    "¡Tu progreso es impresionante! Mantén la motivación alta.",
                                    "La disciplina es la clave del éxito. ¡Sigue con fuerza!",
                                    "¡Golden Gym cree en ti! Cada paso cuenta hacia tus objetivos.",
                                    "No te compares con nadie, solo con tu versión de ayer.",
                                    "Tu dedicación te llevará lejos. ¡No pierdas la motivación!",
                                    "Cada repetición cuenta. ¡Sigue entrenando con energía!",
                                    "¡Golden Gym te felicita por tu esfuerzo! Cada día es progreso.",
                                    "Mantente enfocado, los resultados llegarán pronto.",
                                    "El camino puede ser duro, pero el éxito lo vale.",
                                    "¡No hay límites para quien persevera! Sigue adelante.",
                                    "Cada pequeño logro suma. ¡Celebra tu constancia!",
                                    "¡Motívate! Lo que haces hoy es tu inversión para mañana.",
                                    "Siente orgullo de tu esfuerzo y mantente activo.",
                                    "¡Golden Gym está contigo! No abandones tu entrenamiento.",
                                    "Recuerda: la perseverancia vence a todo."
                                ];
                                $mensaje = $mensajes[array_rand($mensajes)];
                            @endphp

                            <p>{{ $mensaje }}</p>

                            <p>¡Sigue entrenando y mantente activo con Golden Gym!</p>
                        </td>
                    </tr>

                    <!-- Botón -->
                    <tr>
                        <td align="center" style="padding:20px;">
                            <a href="{{ route('inicio') }}"
                                class="button"
                                style="background-color:#fcd52b; color:#1a1a1a; text-decoration:none; padding:15px 30px; border-radius:5px; display:inline-block; font-weight:bold;">
                                Visita nuestra página
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
