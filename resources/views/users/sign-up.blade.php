@extends('layouts.auth')
@section('title', 'Registrar - Golden Gym')

@section('content')

<body>

  <main class="d-flex vh-100">
    <div class="container d-flex align-items-center">
      <div class="row justify-content-center gap-5">

        <!-- Lado izquierdo: imagen y mensaje -->
        <div class="col-lg-5 d-none d-lg-flex flex-column justify-content-center text-center me-4">
          <img src="{{ asset('img/icons/golden_gym.png') }}" class="img-fluid mb-4" alt="Golden Gym">
        </div>


        <!-- Lado derecho: formulario -->
        <div class="col-sm-10 col-md-8 col-lg-5">
          <div class="d-flex flex-column justify-content-center h-100">


            <div class="text-center mb-4">
              <h1 class="tittle-color fw-bolder h2 mb-3">¡Bienvenido Administrador!</h1>
              <p class="medium mb-4">
                Con tu cuenta podrás gestionar usuarios, revisar registros y controlar todas las operaciones del gimnasio de manera segura y eficiente.
              </p>
            </div>

            <div class="card shadow-lg">
              <div class="card-body">
                <form id="registerForm" method="POST" action="{{ route('usuario.register') }}" enctype="multipart/form-data">
                  @csrf

                  <h3 class="text-center text-white mb-4">Crea tu cuenta</h3>

                  <!-- Nombre y correo en línea -->
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label for="nombre" class="form-label">Nombre completo *</label>
                      <input type="text" id="nombre" name="nombre_completo" class="form-control form-control-lg" placeholder="Ingrese su nombre" required>
                    </div>
                    <div class="col-md-6">
                      <label for="correo" class="form-label">Correo electrónico *</label>
                      <input type="email" id="correo" name="correo" class="form-control form-control-lg" placeholder="usuario@correo.com" required>
                    </div>
                  </div>

                  <!-- Teléfono y cédula en línea -->
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label for="telefono" class="form-label">Teléfono *</label>
                      <input type="tel" id="telefono" name="telefono" class="form-control form-control-lg" placeholder="3101234567" required>
                    </div>
                    <div class="col-md-6">
                      <label for="cedula" class="form-label">Cédula</label>
                      <input type="text" id="cedula" name="cedula" class="form-control form-control-lg" placeholder="Opcional">
                    </div>
                  </div>

                  <!-- Contraseña y confirmación -->
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label for="password" class="form-label">Contraseña *</label>
                      <input type="password" id="password" name="password" class="form-control form-control-lg" placeholder="********" required>
                    </div>
                    <div class="col-md-6">
                      <label for="password_confirmation" class="form-label">Confirmar contraseña *</label>
                      <input type="password" id="password_confirmation" name="password_confirmation" class="form-control form-control-lg" placeholder="********" required>
                    </div>
                  </div>

                  <!-- Botón de registro -->
                  <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-lg btn-primary">Registrar</button>
                  </div>

                </form>
              </div>
            </div>

            <!-- Link a login -->
            <div class="text-center pt-2">
              ¿Ya tienes una cuenta? <a href="{{ route('user.login') }}" class="tittle-color">Inicia sesión</a>
            </div>
            <div class="d-flex justify-content-center mt-3">
              <a href="{{ route('inicio') }}" class="btn btn-outline-warning btn-md shadow-sm">
                <i data-feather="home" class="me-1"></i> Ir al inicio
              </a>
            </div>

          </div>
        </div>

      </div>
    </div>
  </main>


  <script>
    const loginUrl = "{{ route('user.login') }}";
  </script>
  <script src="{{ asset('js/inicio.js') }}"></script>

</body>
@endsection
