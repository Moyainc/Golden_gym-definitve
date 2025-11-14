<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="description" content="Responsive Admin &amp; Dashboard Template" />
  <meta name="author" content="AdminKit" />
  <meta name="keywords" content="adminkit, bootstrap, dashboard, template, responsive, css, html" />

  <link rel="preconnect" href="https://fonts.gstatic.com" />
  <link rel="shortcut icon" href="{{ asset('img/icons/golden_gym.png') }}" />
  <link rel="canonical" href="https://demo-basic.adminkit.io/" />

  <title>Golden Gym</title>

  <link href="{{ asset('css/app.css') }}" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet" />

</head>

<body>
  <!-- Preloader -->
  <div id="preloader">
    <div class="spinner"></div>
  </div>

  <!-- NAVBAR PRINCIPAL -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-black shadow-sm py-2 px-3 fixed-top" style="backdrop-filter: blur(6px); background: rgba(0,0,0,0.85);">
    <div class="container-fluid d-flex align-items-center">

      <!-- Logo + Nombre -->
      <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
        <img src="{{ asset('img/icons/golden_gym.png') }}" alt="Golden Gym" style="width:40px; height:40px;">
        <span class="fw-bold text-warning fs-5">Golden Gym</span>
      </a>

      <!-- Botón Hamburguesa -->
      <button class="navbar-toggler bg-dark border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarGG">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Enlaces -->
      <div class="collapse navbar-collapse" id="navbarGG">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-4">

          <li class="nav-item">
            <a class="nav-link text-light fw-semibold hover-yellow" href="#entrenadores">
              Entrenadores
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link text-light fw-semibold hover-yellow" href="#localizacion">
              Localización
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link text-light fw-semibold hover-yellow" href="#productos">
              Productos
            </a>
          </li>

          <!-- Botón Login -->
          <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
            <a href="{{ route('login') }}" class="btn btn-warning text-dark fw-bold px-3 py-2">
              Iniciar Sesión
            </a>
          </li>

        </ul>
      </div>
    </div>
  </nav>


  <!-- Video de fondo -->
  <div class="video-bg">
    <video autoplay muted loop id="bgVideo">
      <source src="{{ asset('img/photos/video-index.mp4') }}" type="video/mp4" />
      Tu navegador no soporta video HTML5.
    </video>
    <div class="overlay"></div>
  </div>

  <script src="{{ asset('js/inicio_public.js') }}"></script>
</body>

</html>
