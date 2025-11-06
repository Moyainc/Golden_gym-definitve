@extends('layouts.app')
@section('title', 'Soporte y Desarrolladores - Golden Gym')

@section('content')
<div class="wrapper">
  <div class="main">
    <main class="content">
      <div class="container-fluid p-0">

        <div class="text-center mb-5 mt-3">
          <h1 class="fw-bold text-warning display-6 magic-title">
            <i data-feather="help-circle" class="me-2 text-warning"></i>
            Soporte & Desarrolladores
          </h1>
          <p class="text-muted lead">
            ¿Necesitas ayuda o quieres comunicarte con los desarrolladores del sistema? Aquí estamos para ti 💫
          </p>
        </div>

        <div class="row justify-content-center g-4">
          <div class="col-md-5">
            <div class="card bg-black border-0 shadow-lg text-center dev-card p-4">
              <div class="glow-circle mx-auto mb-3">
                <i data-feather="code" class="text-warning icon-giant"></i>
              </div>
              <h5 class="text-light fw-bold">Luis David Lemus Perea</h5>
              <p class="text-muted small mb-2">Desarrollador Principal</p>
              <p class="text-muted small">Desarrollo Frontend (Bootstrap, Js) y Backend (Laravel).</p>
              <hr class="border-secondary">
              <p class="text-white small mb-1">
                <i data-feather="mail" class="me-2 text-warning"></i> contacto.luislemus@example.com
              </p>
              <p class="text-white small">
                <i data-feather="github" class="me-2 text-warning"></i> github.com/luislemus
              </p>
            </div>
          </div>


          <div class="col-md-5">
            <div class="card bg-black border-0 shadow-lg text-center dev-card p-4">
              <div class="glow-circle mx-auto mb-3">
                <i data-feather="terminal" class="text-warning icon-giant"></i>
              </div>
              <h5 class="text-light fw-bold">Harbin Andres Moya Perea</h5>
              <p class="text-muted small mb-2">Desarrollador Principal</p>
              <p class="text-muted small">Desarrollo Backend (Laravel) y mantenimiento de base de datos.</p>
              <hr class="border-secondary">
              <p class="text-white small mb-1">
                <i data-feather="mail" class="me-2 text-warning"></i> contacto.desarrolladora@example.com
              </p>
              <p class="text-white small">
                <i data-feather="github" class="me-2 text-warning"></i> github.com/desarrollador
              </p>
            </div>
          </div>
        </div>

        <div class="text-center mt-5">
          <p class="text-muted small">© 2025 Golden Gym — Innovación, Rendimiento y Pasión.</p>
        </div>
      </div>
    </main>
  </div>
</div>


{{-- Efectos CSS personalizados --}}
<style>

</style>
@endsection
