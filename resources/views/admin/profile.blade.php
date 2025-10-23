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
              <!--Información del usuario -->
              <div class="col-md-4 col-xl-3">
                <div class="card border-0 shadow-sm mb-3">
                  <div class="card-body text-center">
                    <img
                      src="{{ asset('img/avatars/avatar-4.jpg') }}"
                      alt="Foto de perfil"
                      class="img-fluid rounded-circle mb-3"
                      width="128"
                      height="128"
                    />
                    <h5 class="text-white mb-0">Christina Mason</h5>
                    <p class="small mb-2 tittle-color">Administrador</p>
                    <p class="text-muted small mb-2">
                      Fecha de registro: 15/04/2023
                    </p>

                    <button
                      class="btn btn-outline-warning tittle-color btn-sm mb-2"
                    >
                      <i data-feather="upload" class="me-1"></i> Cambiar foto
                    </button>
                  </div>
                </div>
              </div>

              <!--Formulario de edición -->
              <div class="col-md-8 col-xl-9">
                <div class="card border-0 shadow-sm text-white">
                  <div class="card-header border-bottom border-secondary">
                    <h5 class="card-title mb-0 tittle-color">
                      Información personal
                    </h5>
                  </div>
                  <div class="card-body">
                    <form>
                      <div class="row mb-3">
                        <div class="col-md-6">
                          <label class="form-label text-white"
                            >Nombre completo *</label
                          >
                          <input
                            type="text"
                            class="form-control text-white border-0"
                            placeholder="Ingrese su nombre completo"
                            required
                          />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label text-white"
                            >Correo electrónico *</label
                          >
                          <input
                            type="email"
                            class="form-control text-white border-0"
                            placeholder="usuario@correo.com"
                            required
                          />
                        </div>
                      </div>

                      <div class="row mb-3">
                        <div class="col-md-6">
                          <label class="form-label text-white"
                            >Teléfono *</label
                          >
                          <input
                            type="tel"
                            class="form-control text-white border-0"
                            placeholder="Ej. 3101234567"
                            required
                          />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label text-white"
                            >Identificación</label
                          >
                          <input
                            type="text"
                            class="form-control text-white border-0"
                            placeholder="No obligatorio"
                          />
                        </div>
                      </div>

                      <div class="row mb-3">
                        <div class="col-md-6">
                          <label class="form-label text-white"
                            >Fecha de nacimiento</label
                          >
                          <input
                            type="date"
                            class="form-control text-white border-0"
                          />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label text-white">Turno</label>
                          <select class="form-select text-white border-0">
                            <option>Mañana</option>
                            <option>Tarde</option>
                            <option>Noche</option>
                            <option selected>No aplica</option>
                          </select>
                        </div>
                      </div>

                      <div class="row mb-4">
                        <div class="col-md-6">
                          <label class="form-label text-white">Cargo</label>
                          <input
                            type="text"
                            class="form-control text-white border-0"
                            value="Administrador"
                            readonly
                          />
                        </div>
                      </div>

                      <div class="border-top border-secondary pt-3">
                        <h6 class="tittle-color mb-3 fw-semibold">
                          <i data-feather="lock" class="me-2"></i> Seguridad y
                          acceso
                        </h6>

                        <button
                          type="button"
                          class="btn btn-outline-warning mb-3"
                          data-bs-toggle="modal"
                          data-bs-target="#changePasswordModal"
                        >
                          <i data-feather="key" class="me-1"></i> Cambiar
                          contraseña
                        </button>
                      </div>

                      <div class="mt-2">
                        <button
                          type="submit"
                          class="btn btn-warning text-black px-4"
                        >
                          <i data-feather="save" class="me-1"></i> Guardar
                          cambios
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>

        <!-- Modal para cambiar contraseña -->
        <div
          class="modal fade"
          id="changePasswordModal"
          tabindex="-1"
          aria-labelledby="changePasswordLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-centered">
            <div
              class="modal-content bg-dark text-white border-0 rounded-4 shadow-lg"
            >
              <div class="modal-header border-0">
                <h5 class="modal-title tittle-color" id="changePasswordLabel">
                  <i data-feather="key" class="me-2 text-warning"></i> Cambiar
                  contraseña
                </h5>
                <button
                  type="button"
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                ></button>
              </div>

              <div class="modal-body">
                <form id="passwordChangeForm">
                  <div class="mb-3">
                    <label for="currentPassword" class="form-label"
                      >Contraseña actual</label
                    >
                    <input
                      type="password"
                      id="currentPassword"
                      class="form-control text-white border-0"
                      placeholder="********"
                      required
                    />
                  </div>

                  <div class="mb-3">
                    <label for="newPassword" class="form-label"
                      >Nueva contraseña</label
                    >
                    <input
                      type="password"
                      id="newPassword"
                      class="form-control text-white border-0"
                      placeholder="********"
                      required
                    />
                    <div
                      id="passwordStrength"
                      class="mt-1 small fw-semibold text-warning"
                    >
                      Fuerza: Débil
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="confirmPassword" class="form-label"
                      >Confirmar nueva contraseña</label
                    >
                    <input
                      type="password"
                      id="confirmPassword"
                      class="form-control text-white border-0"
                      placeholder="********"
                      required
                    />
                    <div
                      id="passwordMatch"
                      class="mt-1 small fw-semibold text-danger"
                    ></div>
                  </div>
                </form>
              </div>

              <div class="modal-footer border-0">
                <button
                  type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  form="passwordChangeForm"
                  class="btn btn-warning text-black"
                >
                  Guardar cambios
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="../js/app.js"></script>
  </body>
@endsection
