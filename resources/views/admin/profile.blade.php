@extends('layouts.app')
@section('title', 'Perfil - Golden Gym')
@section('content')

<body>
  <div class="wrapper">
    <div class="main">
      <div id="navbar"></div>

      <main class="content">
        <div class="container-fluid p-0">
          <div class="mb-3">
            <h1 class="text-white h2 d-inline align-middle fw-bold">
              Configuracion de la cuenta
            </h1>
          </div>

          <div class="row">
            <!-- FOTO DE PERFIL -->
            <div class="col-md-4 col-xl-3">
              <div class="card border-0 shadow-sm mb-3 text-center">
                <div class="card-body">
                  {{-- Foto de perfil --}}
                  <img
                    src="{{ auth()->user()->foto ? asset('storage/' . auth()->user()->foto) : asset('img/avatars/avatar-4.jpg') }}"
                    alt="Foto de perfil"
                    id="profileImagePreview"
                    class="img-fluid rounded-circle mb-3"
                    width="128"
                    height="128" />

                  {{-- Nombre --}}
                  <h5 class="text-white mb-0" id="profileUserName">{{ auth()->user()->nombre_completo }}</h5>

                  {{-- Rol --}}
                  <p class="small mb-2 tittle-color" id="profileUserRole">{{ ucfirst(auth()->user()->rol) }}</p>

                  {{-- Último acceso --}}
                  <p class="text-muted small mb-2" id="profileCreatedAt">
                    Último acceso: {{ auth()->user()->ultimo_acceso_formateado }}
                  </p>



                  {{-- Botones --}}
                  <div class="d-flex justify-content-center gap-2">
                    <!-- Cambiar foto: más pequeño -->
                    <button
                      class="btn btn-outline-warning btn-sm px-2 py-1 text-sm mb-2"
                      id="btnChangePhoto">
                      <i data-feather="upload" class="me-1"></i> Cambiar foto
                    </button>

                    <!-- Eliminar foto: todo rojo, más pequeño -->
                    <button
                      class="btn btn-danger btn-sm px-2 py-1 text-sm mb-2 d-none"
                      id="btnDeletePhoto">
                      <i data-feather="trash-2" class="me-1"></i> Eliminar
                    </button>
                  </div>


                  <input type="file" id="inputPhoto" class="d-none" accept="image/*">
                </div>
              </div>

            </div>

            <!-- FORMULARIO -->
            <div class="col-md-8 col-xl-9">
              <div class="card border-0 shadow-sm text-white">
                <div class="card-header border-bottom border-secondary">
                  <h5 class="card-title mb-0 tittle-color">Información personal</h5>
                </div>
                <div class="card-body">
                  <form id="profileForm">
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label for="userName" class="form-label text-white">Nombre completo *</label>
                        <input type="text" id="userName" name="nombre_completo" class="form-control text-white border-0" placeholder="Ingrese su nombre completo" required>
                      </div>

                      <div class="col-md-6">
                        <label for="userCedula" class="form-label text-white">Identificación</label>
                        <input type="text" id="userCedula" name="cedula" class="form-control text-white border-0" placeholder="No obligatorio">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label for="userEmail" class="form-label text-white">Correo electrónico *</label>
                        <input type="email" id="userEmail" name="correo" class="form-control text-white border-0" placeholder="usuario@correo.com" required>
                      </div>

                      <div class="col-md-6">
                        <label for="userPhone" class="form-label text-white">Teléfono *</label>
                        <input type="tel" id="userPhone" name="telefono" class="form-control text-white border-0" placeholder="Ej. 3101234567" required>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label for="userRoleDisplay" class="form-label text-white">Rol</label>
                        <input type="text" id="userRoleDisplay" class="form-control text-white border-0" value="{{ ucfirst(auth()->user()->rol) }}" readonly>
                      </div>



                      <div class="col-md-4 d-flex flex-column justify-content-end">
                        <button type="button" class="btn btn-outline-warning mb-1" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                          <i data-feather="key" class="me-1"></i> Cambiar contraseña
                        </button>
                      </div>
                    </div>

                    <div class="mt-2">
                      <button type="submit" class="btn btn-warning text-black px-4">
                        <i data-feather="save" class="me-1"></i> Guardar cambios
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>

        </div>
      </main>
    </div>
  </div>

  <!-- Modal para cambiar contraseña -->
  <div
    class="modal fade"
    id="changePasswordModal"
    tabindex="-1"
    aria-labelledby="changePasswordLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content bg-dark text-white border-0 rounded-4 shadow-lg">
        <div class="modal-header border-0">
          <h5 class="modal-title tittle-color" id="changePasswordLabel">
            <i data-feather="key" class="me-2 text-warning"></i> Cambiar contraseña
          </h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <form id="passwordChangeForm">
            {{-- Contraseña actual --}}
            <div class="mb-3 position-relative">
              <label for="currentPassword" class="form-label">Contraseña actual</label>
              <input
                type="password"
                id="currentPassword"
                class="form-control text-white border-0"
                placeholder="********"
                required />
              <i class="feather-eye toggle-password" data-target="currentPassword" style="position:absolute; top:50%; right:1rem; transform:translateY(-50%); cursor:pointer;"></i>
            </div>

            {{-- Nueva y Confirmar en línea --}}
            <div class="row g-2">
              <div class="col">
                <div class="mb-3 position-relative">
                  <label for="newPassword" class="form-label">Nueva contraseña</label>
                  <input
                    type="password"
                    id="newPassword"
                    class="form-control text-white border-0"
                    placeholder="********"
                    required />
                  <i class="feather-eye toggle-password" data-target="newPassword" style="position:absolute; top:50%; right:1rem; transform:translateY(-50%); cursor:pointer;"></i>
                  <div id="passwordStrength" class="mt-1 small fw-semibold text-warning">
                    Fuerza: Débil
                  </div>
                </div>
              </div>
              <div class="col">
                <div class="mb-3 position-relative">
                  <label for="confirmPassword" class="form-label">Confirmar contraseña</label>
                  <input
                    type="password"
                    id="confirmPassword"
                    class="form-control text-white border-0"
                    placeholder="********"
                    required />
                  <i class="feather-eye toggle-password" data-target="confirmPassword" style="position:absolute; top:50%; right:1rem; transform:translateY(-50%); cursor:pointer;"></i>
                  <div id="passwordMatch" class="mt-1 small fw-semibold text-danger"></div>
                </div>
              </div>
            </div>
          </form>
        </div>


        <div class="modal-footer border-0">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal">
            Cancelar
          </button>
          <button
            type="submit"
            form="passwordChangeForm"
            class="btn btn-warning text-black">
            Guardar cambios
          </button>
        </div>
      </div>
    </div>
  </div>
  <script src="../js/app.js"></script>
  <script src="{{ asset('js/perfil.js') }}"></script>

  @endsection
