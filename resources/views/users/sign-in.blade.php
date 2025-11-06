@extends('layouts.auth')
@section('title', 'Iniciar Sesion - Golden Gym')

@section('content')

<body>

  <main class="d-flex align-items-center py-3">
    <div class="container d-flex flex-column">
      <div class="row">
        <div
          class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table">
          <div class="d-table-cell align-middle">
            <a class="sidebar-brand">
              <img src="{{ asset('img/icons/golden_gym.png') }}" class="sidebar-logo" />
            </a>
            <div class="text-center mt-4">
              <h1 class="text-light fw-bolder h2">Bienvenido de nuevo!</h1>
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
                  </form>

                </div>
              </div>
            </div>
            <div class="text-center mb-3">
              Volver a
              <a class="tittle-color" href="">Inicio</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>


  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/inicio.js') }}"></script>
</body>

@endsection
