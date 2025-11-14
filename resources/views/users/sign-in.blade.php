<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1, shrink-to-fi
    <meta
        name=" description
    content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5" />
  <meta name="author" content="Golden Gym" />
  <meta
    name="keywords"
    content="Golden Gym, gimnasio, administración, panel, membresías, clientes, ventas, dashboard, Bootstrap 5, AdminKit" />
  <meta name="description" content="Panel de administración de Golden Gym. Gestiona clientes, membresías, ventas y métricas del gimnasio de manera rápida y segura." />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="preconnect" href="https://fonts.gstatic.com" />
  <link rel="shortcut icon" href="{{ asset('img/icons/golden_gym.png') }}" />

  <title>@yield('title', 'Golden Gym')</title>

  <link href="{{ asset('css/app.css') }}" rel="stylesheet" />
  <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet" />

</head>

</head>

<body>

  <main class="d-flex align-items-start justify-content-center min-vh-100 pt-6">


    <div class="container d-flex flex-column">
      <div class="row">
        <div
          class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table">
          <div class="d-table-cell align-middle">
            <a class="sidebar-brand">
              <img src="{{ asset('img/icons/golden_gym.png') }}" class="sidebar-logo" />
            </a>
            <div class="text-center mt-4">
              <h1 class="text-light fw-bolder h2">
                ¡Bienvenido de nuevo!</h1>
              <p class="medium">Inicie sesion con su cuenta para continuar</p>
            </div>

            <div class="card">
              <div class="card-body">
                <div class="m-sm-3">
                  <form id="loginForm" method="POST" action="{{ route('usuario.login') }}">
                    @csrf

                    <div class="mb-3">
                      <label class="form-label">Correo</label>
                      <input
                        class="form-control form-control-lg"
                        type="email"
                        name="correo"
                        placeholder="Ingrese su correo"
                        required />
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Contraseña</label>
                      <input
                        class="form-control form-control-lg"
                        type="password"
                        name="password"
                        placeholder="Ingrese su contraseña"
                        required />
                    </div>

                    <div class="d-grid gap-2 mt-4">
                      <button type="submit" class="btn btn-md btn-primary text-black">Iniciar sesión</button>
                    </div>
                    <div id="loginAttempts" class="text-warning small mt-2"></div>
                  </form>
                </div>
              </div>
            </div>
            <div class="text-center mb-3">
              Volver a
              <a class="tittle-color" href="{{ route('inicio') }}">Inicio</a>
            </div>
            <div class="text-center mt-4 text-secondary small">
              Golden Gym © 2025. Todos los derechos reservados.
            </div>

          </div>
        </div>
      </div>
    </div>
  </main>


  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/inicio.js') }}"></script>
</body>

</html>
