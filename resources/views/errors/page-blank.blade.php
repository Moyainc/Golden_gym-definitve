<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Error {{ $code }} | Golden Gym</title>
  <link rel="shortcut icon" href="{{ asset('img/icons/golden_gym.png') }}" />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>

  <style>
    body {
      background: linear-gradient(135deg, #1a1a1a, #111111);
      font-family: 'Inter', sans-serif;
      color: #fcd52b;
    }

    .pulse {
      width: 12px;
      height: 12px;
      background-color: #fcd52b;
      border-radius: 50%;
      animation: pulse 1.5s infinite;
    }

    .pulse:nth-child(2) {
      animation-delay: 0.3s;
    }

    .pulse:nth-child(3) {
      animation-delay: 0.6s;
    }

    @keyframes pulse {

      0%,
      100% {
        transform: scale(1);
        opacity: 1;
      }

      50% {
        transform: scale(1.5);
        opacity: 0.5;
      }
    }

    .btn-glow:hover {
      box-shadow: 0 0 15px #fcd52b, 0 0 25px #fcd52b50;
      transform: translateY(-2px);
      transition: all 0.3s ease;
    }

    .neon-card {
      background: rgba(26, 26, 26, 0.95);
      border-radius: 1rem;
      padding: 3rem 2rem;
      box-shadow: 0 0 25px rgba(252, 213, 43, 0.3);
      backdrop-filter: blur(5px);
    }

    a {
      transition: all 0.3s ease;
    }
  </style>
</head>

<body class="flex items-center justify-center min-h-screen">
  <main class="text-center w-full max-w-lg px-4">
    <div class="neon-card mx-auto">
      <div class="mb-6">
        <i data-feather="alert-triangle" class="mx-auto" style="width: 80px; height: 80px; color: #fcd52b;"></i>
      </div>

      <h1 class="text-5xl font-bold mb-4">{{ $code }}</h1>

      <h2 class="text-2xl font-semibold mb-3">
        @if($code == 404)
        Página no encontrada
        @elseif($code == 403)
        Acceso no autorizado
        @else
        Ocurrió un error
        @endif
      </h2>

      <p class="text-gray-400 mb-6">
        Lo sentimos, esta sección no está disponible o fue movida. Estamos trabajando para mejorar tu experiencia.
      </p>

      <div class="flex flex-wrap justify-center gap-4 mb-6">
        <a href="{{ url('/') }}" class="btn-glow bg-yellow-400 text-gray-900 px-6 py-3 rounded-full font-semibold flex items-center gap-2">
          <i data-feather="home"></i> Ir al inicio
        </a>
        <a href="javascript:history.back()" class="btn-glow border border-gray-400 text-gray-200 px-6 py-3 rounded-full font-semibold flex items-center gap-2">
          <i data-feather="arrow-left"></i> Regresar
        </a>
      </div>

      <div class="flex justify-center items-center gap-2 mt-4">
        <div class="pulse"></div>
        <div class="pulse"></div>
        <div class="pulse"></div>
      </div>
    </div>
  </main>

  <script>
    feather.replace();
  </script>
</body>

</html>
