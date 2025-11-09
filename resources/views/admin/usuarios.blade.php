@extends('layouts.app')
@section('title', 'Usuarios - Golden Gym')

@section('content')


<div class="wrapper">
  <div class="main">
    <main class="content">
      <div class="container-fluid p-0">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <h1 class="h3 text-white mb-0 fw-bold">
            <i data-feather="users" class="me-2 tittle-color"></i>Usuarios &
            Roles
          </h1>
          <div class="d-flex gap-2">
            <div class="input-group input-group-sm">
              <span
                class="input-group-text bg-secondary border-0 text-muted"><i data-feather="search" class="feather-sm"></i></span>
              <input
                id="searchUsers"
                class="form-control form-control-sm bg-dark text-white border-0"
                placeholder="Buscar usuario..." />
            </div>
            <button
              class="btn btn-warning tittle-color btn-sm"
              data-bs-toggle="modal"
              data-bs-target="#addEditUserModal">
              <i data-feather="user-plus" class="me-1"></i> Nuevo usuario
            </button>
          </div>
        </div>

        <div class="row g-3">
          <!-- Roles -->

          <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
              <!-- Encabezado -->
              <div
                class="card-header border-bottom border-secondary d-flex justify-content-between align-items-center">
                <h6 class="mb-0 tittle-color">
                  <i data-feather="shield" class="me-1"></i> Roles del sistema
                </h6>
                <small class="text-muted">Información general</small>
              </div>

              <!-- Cuerpo -->
              <div class="card-body text-white">
                <p class="text-muted small mb-4">
                  A continuación se describen los roles disponibles en el sistema y las funciones asociadas a cada uno.
                </p>

                <!-- Rol: Administrador -->
                <div class="mb-4 p-3 border rounded border-secondary">
                  <h6 class="tittle-color mb-2">
                    <i data-feather="user-check" class="me-1"></i> Administrador
                  </h6>
                  <p class="mb-0 text-light small">
                    Tiene acceso total al sistema: puede crear, editar y eliminar usuarios, gestionar roles y acceder a
                    todos los módulos sin restricciones.
                  </p>
                </div>

                <!-- Rol: Recepcionista -->
                <div class="mb-4 p-3 border rounded border-secondary">
                  <h6 class="tittle-color mb-2">
                    <i data-feather="clipboard" class="me-1"></i> Recepcionista
                  </h6>
                  <p class="mb-0 text-light small">
                    Registra visitantes y maneja la atención al cliente. Tiene permisos limitados para visualizar
                    información básica y registrar datos en el sistema.
                  </p>
                </div>

                <!-- Rol: Contador -->
                <div class="p-3 border rounded border-secondary">
                  <h6 class="tittle-color mb-2">
                    <i data-feather="file-text" class="me-1"></i> Contador
                  </h6>
                  <p class="mb-0 text-light small">
                    Gestiona la parte contable y financiera. Puede acceder a reportes económicos, registrar transacciones
                    y consultar estadísticas financieras.
                  </p>
                </div>
              </div>
            </div>
          </div>


          <!-- Usuarios -->
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
              <div
                class="card-header border-bottom border-secondary d-flex justify-content-between align-items-center">
                <h6 class="mb-0 tittle-color">
                  <i data-feather="user-check" class="me-1"></i>Usuarios
                </h6>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead>
                      <tr class="text-muted small">
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th class="text-end">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="usersTableBody">
                      @forelse ($usuarios as $usuario)
                      <tr>
                        <td>
                          <i data-feather="user"
                            class="me-1
                            @if($usuario->rol === 'Administrador') tittle-color
                            @elseif($usuario->rol === 'Contador') text-success
                            @else text-warning @endif">
                          </i>
                          {{ $usuario->nombre_completo }}
                        </td>
                        <td>{{ $usuario->correo }}</td>
                        <td>
                          @if($usuario->rol === 'Administrador')
                          <span class="badge tittle-color">{{ $usuario->rol }}</span>
                          @elseif($usuario->rol === 'Contador')
                          <span class="badge bg-success">{{ $usuario->rol }}</span>
                          @else
                          <span class="badge bg-warning text-dark">{{ $usuario->rol }}</span>
                          @endif
                        </td>
                        <td class="text-end">
                          <button
                            class="btn btn-sm btn-outline-light me-1 editRole"
                            data-bs-toggle="modal"
                            data-bs-target="#addEditUserModal"
                            data-id="{{ $usuario->id }}">
                            <i data-feather="edit"></i>
                          </button>
                          <button
                            class="btn btn-sm btn-outline-danger deleteRole"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteUserModal"
                            data-id="{{ $usuario->id }}"
                            data-name="{{ $usuario->nombre_completo }}">
                            <i data-feather="trash-2"></i>
                          </button>

                        </td>
                      </tr>
                      @empty
                      <tr>
                        <td colspan="4" class="text-muted text-center">No hay usuarios registrados</td>
                      </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal Agregar usuario -->
    <div
      class="modal fade"
      id="addEditUserModal"
      tabindex="-1"
      aria-labelledby="addEditUserModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-0 shadow-lg">
          <div class="modal-header border-secondary">
            <h5 class="modal-title tittle-color" id="addEditUserModalLabel">
              <i data-feather="user-plus" class="me-1"></i> Nuevo usuario
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
              aria-label="Cerrar"></button>
          </div>

          <form id="formAddEditUser" method="POST"
            data-url="{{ route('admin.usuarios.store') }}"
            enctype="multipart/form-data">

            @csrf

            <div class="modal-body">
              <input type="hidden" id="userId" name="id" />

              <!-- Nombre y Cédula -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="userName" class="form-label text-white">Nombre completo</label>
                  <input
                    type="text"
                    class="form-control text-white border-0"
                    id="userName"
                    name="nombre_completo"
                    placeholder="Ej. Laura Martínez"
                    required />
                </div>

                <div class="col-md-6">
                  <label for="userCedula" class="form-label text-white">Cédula</label>
                  <input
                    type="text"
                    class="form-control text-white border-0"
                    id="userCedula"
                    name="cedula"
                    placeholder="Ej. 123456789"
                    required />
                </div>
              </div>

              @php
              $userFoto = Auth::user()->foto;
              @endphp
              <!-- Foto de perfil -->
              <div class="text-center mb-3">
                <img
                  id="previewUserImage"
                  src="{{ $userFoto && file_exists(public_path('storage/' . $userFoto)) ? asset('storage/' . $userFoto) : asset('img/avatars/user.png') }}" alt="Foto de usuario"
                  class="rounded-circle border border-secondary mb-2"
                  width="100"
                  height="100" />
                <div>
                  <label for="userPhoto" class="form-label text-white">Foto de perfil</label>
                  <input
                    type="file"
                    class="form-control text-white border-0"
                    id="userPhoto"
                    name="foto"
                    accept="image/*" />
                </div>
              </div>

              <!-- Correo y Teléfono -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="userEmail" class="form-label text-white">Correo electrónico</label>
                  <input
                    type="email"
                    class="form-control text-white border-0"
                    id="userEmail"
                    name="correo"
                    placeholder="usuario@correo.com"
                    required />
                </div>

                <div class="col-md-6">
                  <label for="userPhone" class="form-label text-white">Teléfono</label>
                  <input
                    type="text"
                    class="form-control text-white border-0"
                    id="userPhone"
                    name="telefono"
                    placeholder="Ej. 3101234567" />
                </div>
              </div>

              <!-- Rol -->
              <div class="mb-3">
                <label for="userRole" class="form-label text-white">Rol</label>
                <select
                  id="userRole"
                  name="rol"
                  class="form-select text-white border-0"
                  required>
                  <option value="" selected disabled>Seleccione un rol</option>
                  <option value="Administrador">Administrador</option>
                  <option value="Contador">Contador</option>
                  <option value="Recepcionista">Recepcionista</option>
                </select>
              </div>

              <!-- Contraseña y Confirmar Contraseña -->
              <div class="row mb-3" id="passwordFields">
                <div class="col-md-6 position-relative">
                  <label class="form-label text-white">Contraseña</label>
                  <div class="input-group">
                    <input
                      type="password"
                      name="password"
                      id="userPassword"
                      class="form-control text-white border-0"
                      placeholder="********" />
                    <button
                      class="btn btn-outline-secondary"
                      type="button"
                      id="togglePassword"
                      tabindex="-1">
                      <i data-feather="eye" id="eyeIcon"></i>
                    </button>
                  </div>
                  <small class="text-secondary d-block mt-1">
                    Mínimo <strong>8 caracteres</strong>, una <strong>mayúscula</strong>,
                    un <strong>número</strong> y un <strong>símbolo</strong>.
                  </small>
                </div>

                <div class="col-md-6">
                  <label for="userPasswordConfirmation" class="form-label text-white">Confirmar contraseña</label>
                  <input
                    type="password"
                    class="form-control text-white border-0"
                    id="userPasswordConfirmation"
                    name="password_confirmation"
                    placeholder="********" />
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal">
                Cancelar
              </button>
              <button type="submit" class="btn btn-warning tittle-color">
                <i data-feather="save" class="me-1"></i> Guardar usuario
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>
<script>
  const DEFAULT_IMG = "{{ asset('img/avatars/user.png') }}";
</script>
<script src="{{ asset('js/modaluser.js') }}"></script>

@endsection
