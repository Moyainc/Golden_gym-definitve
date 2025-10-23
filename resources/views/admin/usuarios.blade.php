@extends('layouts.app')
@section('title', 'Usuarios - Golden Gym')

@section('content')

<body>
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
                <div
                  class="card-header border-bottom border-secondary d-flex justify-content-between align-items-center">
                  <h6 class="mb-0 tittle-color">
                    <i data-feather="shield" class="me-1"></i>Roles
                  </h6>
                  <small class="text-muted">Gestión rápida</small>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <label class="form-label text-white mb-1">Crear nuevo rol</label>
                    <div class="d-flex gap-2">
                      <input
                        id="newRoleName"
                        class="form-control form-control-sm text-white border-0"
                        placeholder="Nombre del rol, ej. Recepción" />
                      <input
                        id="newRoleDescription"
                        class="form-control form-control-sm text-white border-0"
                        placeholder="Descripción del rol" />
                      <button
                        id="btnCreateRole"
                        class="btn btn-sm btn-outline-warning tittle-color">
                        Crear
                      </button>
                    </div>
                  </div>

                  <hr class="border-secondary" />
                  <ul
                    id="rolesList"
                    class="list-group list-group-flush small">
                    <li
                      class="list-group-item text-white d-flex justify-content-between align-items-center animation-item">
                      Administrador
                      <div>
                        <button
                          class="btn btn-sm btn-outline-light me-1 editRole"
                          data-bs-toggle="modal"
                          data-bs-target="#addEditRoleModal">
                          <i data-feather="edit"></i>
                        </button>
                        <button
                          class="btn btn-sm btn-outline-danger deleteRole"
                          data-bs-toggle="modal"
                          data-bs-target="#deleteUserModal">
                          <i data-feather="trash-2"></i>
                        </button>
                      </div>
                    </li>
                    <li
                      class="list-group-item text-white d-flex justify-content-between align-items-center animation-item">
                      Entrenador
                      <div>
                        <button
                          class="btn btn-sm btn-outline-light me-1 editRole"
                          data-bs-toggle="modal"
                          data-bs-target="#addEditRoleModal">
                          <i data-feather="edit"></i>
                        </button>
                        <button
                          class="btn btn-sm btn-outline-danger deleteRole"
                          data-bs-toggle="modal"
                          data-bs-target="#deleteUserModal">
                          <i data-feather="trash-2"></i>
                        </button>
                      </div>
                    </li>
                  </ul>
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
                  <small id="usersCount" class="text-muted">3 usuarios</small>
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
                        <tr>
                          <td>
                            <i
                              data-feather="user"
                              class="me-1 tittle-color"></i>Laura Martínez
                          </td>
                          <td>laura.martinez@gym.com</td>
                          <td>
                            <span class="badge tittle-color">Administrador</span>
                          </td>
                          <td class="text-end">
                            <button
                              class="btn btn-sm btn-outline-light me-1 editRole"
                              data-bs-toggle="modal"
                              data-bs-target="#addEditUserModal">
                              <i data-feather="edit"></i>
                            </button>
                            <button
                              class="btn btn-sm btn-outline-danger deleteRole"
                              data-bs-toggle="modal"
                              data-bs-target="#deleteUserModal">
                              <i data-feather="trash-2"></i>
                            </button>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <i
                              data-feather="user"
                              class="me-1 text-warning"></i>Juan Pérez
                          </td>
                          <td>juan.perez@gym.com</td>
                          <td><span class="badge">Entrenador</span></td>
                          <td class="text-end">
                            <button
                              class="btn btn-sm btn-outline-light me-1 editRole"
                              data-bs-toggle="modal"
                              data-bs-target="#addEditUserModal">
                              <i data-feather="edit"></i>
                            </button>
                            <button
                              class="btn btn-sm btn-outline-danger deleteRole"
                              data-bs-toggle="modal"
                              data-bs-target="#deleteUserModal">
                              <i data-feather="trash-2"></i>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      <!-- Modal Agregar / Editar Rol -->
      <div
        class="modal fade"
        id="addEditRoleModal"
        tabindex="-1"
        aria-labelledby="addEditRoleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-dark border-0 shadow-lg">
            <!-- Encabezado -->
            <div class="modal-header border-secondary">
              <h5 class="modal-title tittle-color" id="addEditRoleModalLabel">
                <i data-feather="shield" class="me-1"></i> Nuevo rol
              </h5>
              <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"
                aria-label="Cerrar"></button>
            </div>

            <!-- Cuerpo -->
            <div class="modal-body">
              <form id="formAddEditRole">
                <input type="hidden" id="roleId" />

                <div class="mb-3">
                  <label for="roleName" class="form-label text-white">Nombre del rol</label>
                  <input
                    type="text"
                    class="form-control text-white border-0"
                    id="roleName"
                    placeholder="Ej. Recepción"
                    required />
                </div>

                <div class="mb-3">
                  <label for="roleDescription" class="form-label text-white">Descripción</label>
                  <textarea
                    class="form-control text-white border-0"
                    id="roleDescription"
                    rows="3"
                    placeholder="Descripción del rol"></textarea>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal">
                Cancelar
              </button>
              <button
                type="submit"
                form="formAddEditRole"
                class="btn btn-warning tittle-color">
                <i data-feather="save" class="me-1"></i> Guardar rol
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Agregar / Editar Usuario -->
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
                data-bs-dismiss="modal"></button>
            </div>

            <form
              id="formAddEditUser"
              method="POST"
              action="{{ route('admin.usuarios.store') }}"
              enctype="multipart/form-data">
              @csrf
              <div class="modal-body">
                <input type="hidden" id="userId" name="id" />

                <div class="mb-3">
                  <label for="userName" class="form-label text-white">Nombre completo</label>
                  <input
                    type="text"
                    class="form-control text-white border-0"
                    id="userName"
                    name="nombre_completo"
                    placeholder="Ej. Laura Martínez"
                    required />
                </div>

                <div class="mb-3">
                  <label for="userCedula" class="form-label text-white">Cédula</label>
                  <input
                    type="text"
                    class="form-control text-white border-0"
                    id="userCedula"
                    name="cedula"
                    placeholder="Ej. 123456789"
                    required />
                </div>

                <div class="mb-3">
                  <label for="userEmail" class="form-label text-white">Correo electrónico</label>
                  <input
                    type="email"
                    class="form-control text-white border-0"
                    id="userEmail"
                    name="correo"
                    placeholder="usuario@correo.com"
                    required />
                </div>

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

                <div class="mb-3 position-relative">
                  <label class="form-label text-white">Contraseña</label>
                  <div class="input-group">
                    <input
                      type="password"
                      name="password"
                      id="userPassword"
                      class="form-control text-white border-0"
                      placeholder="********"
                      required />
                    <button
                      class="btn btn-outline-secondary"
                      type="button"
                      id="togglePassword"
                      tabindex="-1">
                      <i data-feather="eye" id="eyeIcon"></i>
                    </button>
                  </div>
                  <small class="text-secondary d-block mt-1">
                    La contraseña debe tener al menos <strong>8 caracteres</strong>,
                    una <strong>mayúscula</strong>, un <strong>número</strong> y un
                    <strong>símbolo</strong>.
                  </small>
                </div>


                <div class="mb-3">
                  <label for="userPasswordConfirmation" class="form-label text-white">Confirmar contraseña</label>
                  <input
                    type="password"
                    class="form-control text-white border-0"
                    id="userPasswordConfirmation"
                    name="password_confirmation"
                    placeholder="********"
                    required />
                </div>

                <div class="mb-3">
                  <label for="userPhoto" class="form-label text-white">Foto</label>
                  <input
                    type="file"
                    class="form-control text-white border-0"
                    id="userPhoto"
                    name="foto"
                    accept="image/*" />
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
      <!-- Alerta usuario -->
      <script>
        document.getElementById('formAddEditUser').addEventListener('submit', async function(e) {
          e.preventDefault();

          const form = this;
          const formData = new FormData(form);

          try {
            const response = await fetch(form.action, {
              method: 'POST',
              body: formData,
              headers: {
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
              }
            });

            if (response.ok) {
              const data = await response.json();

              Swal.fire({
                icon: 'success',
                title: '¡Usuario registrado!',
                text: data.message || 'El usuario fue registrado correctamente.',
                timer: 2500,
                iconColor: '#fcb92c',
                showConfirmButton: false
              });

              // Cerrar el modal
              const modal = bootstrap.Modal.getInstance(document.getElementById('addEditUserModal'));
              modal.hide();

              // Limpiar el formulario
              form.reset();

              // Opcional: recargar tabla o página
              // location.reload();
            } else if (response.status === 422) {
              const errorData = await response.json();

              if (errorData.errors && Object.keys(errorData.errors).length > 0) {
                const primerCampo = Object.keys(errorData.errors)[0];
                const mensajes = errorData.errors[primerCampo];
                const primerMensaje = Array.isArray(mensajes) ? mensajes[0] : mensajes;

                Swal.fire({
                  icon: 'error',
                  title: 'Error de validación',
                  text: primerMensaje || 'Ocurrió un error de validación.',
                  confirmButtonColor: '#222E3C',
                  iconColor: '#fcb92c',
                  confirmButtonText: 'Entendido'
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Error de validación',
                  text: 'Por favor revisa los campos del formulario.',
                  confirmButtonColor: '#222E3C',
                  iconColor: '#fcb92c'
                });
              }
            }

          } catch (error) {
            console.error(error);
            Swal.fire({
              icon: 'error',
              title: 'Error del servidor',
              text: 'Ocurrió un problema al procesar la solicitud.',
              confirmButtonColor: '#222E3C',
              iconColor: '#fcb92c'
            });
          }
        });
        // Toggle visibilidad de la contraseña
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('userPassword');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', () => {
          const isPassword = passwordInput.type === 'password';
          passwordInput.type = isPassword ? 'text' : 'password';

          // Cambiar ícono (requiere feather icons ya cargados)
          eyeIcon.setAttribute('data-feather', isPassword ? 'eye-off' : 'eye');
          feather.replace();
        });
      </script>


      <!-- Modal Eliminar Usuario -->
      <div
        class="modal fade"
        id="deleteUserModal"
        tabindex="-1"
        aria-labelledby="deleteUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-dark border-0 shadow-lg">
            <div class="modal-header border-secondary">
              <h5 class="modal-title tittle-color" id="deleteUserModalLabel">
                <i data-feather="trash-2" class="me-1"></i> Eliminar usuario
              </h5>
              <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <p class="text-white">
                ¿Estás seguro que deseas eliminar al usuario
                <strong id="deleteUserName"></strong>?
              </p>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal">
                Cancelar
              </button>
              <button type="button" class="btn btn-danger">
                <i data-feather="trash-2" class="me-1"></i> Eliminar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="../js/app.js" defer></script>
</body>
@endsection