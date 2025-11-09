<nav class="navbar navbar-expand navbar-light navbar-bg p-3">
  <a class="sidebar-toggle js-sidebar-toggle align-items-start">
    <i class="hamburger align-self-center"></i>
  </a>

  <div class="navbar-collapse collapse">
    <ul class="navbar-nav navbar-align">

      <li class="nav-item">
        <a
          class="nav-icon"
          href=""
          title="Redactar correo">
          <i class="align-middle" data-feather="mail"></i>
        </a>
      </li>

      <li class="nav-item dropdown">
        <a
          class="nav-icon dropdown-toggle d-inline-block d-sm-none"
          href="#"
          data-bs-toggle="dropdown">
          <i class="align-middle" data-feather="settings"></i>
        </a>

        <a
          class="nav-link dropdown-toggle bg-icono d-none d-sm-inline-block"
          href="#"
          data-bs-toggle="dropdown">
          @if(Auth::check())
          <span class="text-white">{{ Auth::user()->nombre_completo }}</span>
          <img
            src="{{ Auth::user()->foto ? asset('storage/' . Auth::user()->foto) : asset('img/avatars/default.png') }}"
            class="avatar img-fluid rounded p-1 me-1"
            alt="{{ Auth::user()->nombre_completo }}" />
          @else
          <span class="text-white">Invitado</span>
          <img
            src="{{ asset('img/avatars/default.png') }}"
            class="avatar img-fluid rounded p-1 me-1"
            alt="Invitado" />
          @endif
        </a>

        <div class="dropdown-menu dropdown-menu-end">
          <a class="dropdown-item dropdown-bg-item text-white" href="{{ route('admin.profile') }}">
            <i class="align-middle me-1" data-feather="user"></i> Perfil
          </a>

          <a class="dropdown-item dropdown-bg-item text-white" href="{{ route('admin.resumen.general') }}">
            <i class="align-middle me-1" data-feather="pie-chart"></i> Métricas
          </a>

          <div class="dropdown-divider"></div>

          <a class="dropdown-item dropdown-bg-item text-white" href="{{ route('admin.soporte') }}">
            <i class="align-middle me-1" data-feather="help-circle"></i> Soporte
          </a>
          <div class="dropdown-divider"></div>
          <form id="logoutForm" action="{{ route('usuario.logout') }}" method="POST" class="d-none">
            @csrf
          </form>

          <a class="dropdown-item dropdown-log-out text-white" href="#"
            onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
            <i class="align-middle me-1" data-feather="log-out"></i> Cerrar Sesión
          </a>
        </div>
      </li>
    </ul>
  </div>
</nav>
