@extends('layouts.app')
@section('title', 'Membresía - Golden Gym')
@section('content')
  <body>
    <div class="wrapper">
      <div class="main">
        <main class="content">
          <div class="container-fluid p-0">
            <!-- Título y controles -->
            <div class="d-flex align-items-center justify-content-between mb-4">
              <h1 class="h3 text-white mb-0 fw-bold">
                <i data-feather="credit-card" class="me-2 tittle-color"></i
                >Membresías
              </h1>
              <button
                id="btnAddMembership"
                class="btn btn-warning tittle-color btn-sm p-2"
                data-bs-toggle="modal"
                data-bs-target="#membershipModal"
              >
                <i data-feather="plus-circle" class="me-1"></i> Nueva membresía
              </button>
            </div>

            <!-- Resumen de métricas -->
            <div class="row g-3 mb-4">
              <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100">
                  <div
                    class="card-body d-flex flex-column justify-content-center"
                  >
                    <h6 class="text-muted text-uppercase small mb-2">
                      Total en membresías
                    </h6>
                    <h4 class="text-warning fw-bold mb-0">$80.000</h4>
                  </div>
                </div>
              </div>

              <!-- Total hecho en diarias -->
              <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100">
                  <div
                    class="card-body d-flex flex-column justify-content-center"
                  >
                    <h6 class="text-muted text-uppercase small mb-2">
                      Total en diarias
                    </h6>
                    <h4 class="text-warning fw-bold mb-0">$10.000</h4>
                  </div>
                </div>
              </div>

              <!-- Total general -->
              <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100">
                  <div
                    class="card-body d-flex flex-column justify-content-center"
                  >
                    <h6 class="text-muted text-uppercase small mb-2">
                      Total general
                    </h6>
                    <h4 class="text-success fw-bold mb-0">$90.000</h4>
                  </div>
                </div>
              </div>

              <!-- Membresías activas -->
              <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100">
                  <div
                    class="card-body d-flex flex-column justify-content-center"
                  >
                    <h6 class="text-muted text-uppercase small mb-2">
                      Membresías activas
                    </h6>
                    <h4 class="text-info fw-bold mb-0">2</h4>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tabla de membresías -->
            <div class="card border-0 shadow-sm">
              <div
                class="card-header border-secondary d-flex justify-content-between align-items-center"
              >
                <h6 class="mb-0 tittle-color">
                  <i data-feather="list" class="me-1"></i>Listado de Membresías
                </h6>
              </div>

              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover align-middle border-0 mb-0">
                    <thead>
                      <tr class="text-muted small ">
                        <th>Nombre</th>
                        <th>Duración</th>
                        <th>Valor</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="membershipsTableBody" >
                      <tr class="border-secondary">
                        <td>Mensual</td>
                        <td>30 días</td>
                        <td>$80.000</td>
                        <td>
                          <span class="badge bg-success bg-opacity-75"
                            >Activa</span
                          >
                        </td>
                        <td class="text-end">
                          <button
                            class="btn btn-sm btn-outline-warning me-1"
                            data-bs-toggle="modal"
                            data-bs-target="#editMembershipModal"
                          >
                            <i data-feather="edit"></i>
                          </button>

                          <button
                            class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteMembershipModal"
                          >
                            <i data-feather="trash-2"></i>
                          </button>
                        </td>
                      </tr>
                      <tr>
                        <td>Diaria</td>
                        <td>1 día</td>
                        <td>$10.000</td>
                        <td>
                          <span class="badge bg-success bg-opacity-75"
                            >Activa</span
                          >
                        </td>
                        <td class="text-end">
                          <button
                            class="btn btn-sm btn-outline-warning me-1"
                            data-bs-toggle="modal"
                            data-bs-target="#membershipModal"
                          >
                            <i data-feather="edit"></i>
                          </button>
                          <button class="btn btn-sm btn-outline-danger">
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
        </main>

        <div class="modal fade" id="membershipModal" tabindex="-1">
          <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content bg-dark border-0 shadow-lg">
              <div class="modal-header bg-dark border-secondary">
                <h5 class="modal-title text-warning">
                  <i data-feather="plus-circle" class="me-1"></i> Membresía
                </h5>
                <button
                  type="button"
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal"
                ></button>
              </div>

              <div class="modal-body">
                <form id="membershipForm" class="row g-3">
                  <div class="col-12">
                    <label class="form-label">Nombre *</label>
                    <input
                      type="text"
                      class="form-control text-white border-0"
                      placeholder="Ej: Mensual, Trimestral, Diaria..."
                      required
                    />
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Duración *</label>
                    <input
                      type="number"
                      class="form-control text-white border-0"
                      placeholder="Ej: 30"
                      required
                    />
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Unidad de tiempo *</label>
                    <select class="form-select text-white border-0" required>
                      <option value="dias">Días</option>
                      <option value="meses">Meses</option>
                    </select>
                  </div>

                  <div class="col-12">
                    <label class="form-label">Valor base *</label>
                    <input
                      type="number"
                      class="form-control text-white border-0"
                      placeholder="Ej: 80000"
                      required
                    />
                  </div>

                  <div class="col-12">
                    <label class="form-label">Estado *</label>
                    <select class="form-select text-white border-0" required>
                      <option value="activa">Activa</option>
                      <option value="inactiva">Inactiva</option>
                    </select>
                  </div>
                </form>
              </div>

              <div class="modal-footer bg-dark border-secondary">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                  Cancelar
                </button>
                <button class="btn btn-warning" id="saveMembership">
                  <i data-feather="save" class="me-1"></i>Guardar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- MODAL EDITAR MEMBRESÍA -->
        <div
          class="modal fade"
          id="editMembershipModal"
          tabindex="-1"
          aria-labelledby="editMembershipModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light border-0 shadow-lg">
              <div class="modal-header bg-dark border-secondary">
                <h5
                  class="modal-title text-warning fw-bold"
                  id="editMembershipModalLabel"
                >
                  <i data-feather="edit" class="me-2"></i>Editar Membresía
                </h5>
                <button
                  type="button"
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal"
                  aria-label="Cerrar"
                ></button>
              </div>

              <div class="modal-body">
                <form id="editMembershipForm">
                  <div class="mb-3">
                    <label class="form-label text-muted">Nombre</label>
                    <input
                      type="text"
                      id="editName"
                      class="form-control bg-black text-light border-secondary"
                      placeholder="Ej: Mensual, Diaria..."
                      required
                    />
                  </div>

                  <div class="mb-3">
                    <label class="form-label text-muted">Duración</label>
                    <input
                      type="text"
                      id="editDuration"
                      class="form-control bg-black text-light border-secondary"
                      placeholder="Ej: 30 días"
                      required
                    />
                  </div>

                  <div class="mb-3">
                    <label class="form-label text-muted">Valor</label>
                    <input
                      type="number"
                      id="editPrice"
                      class="form-control bg-black text-light border-secondary"
                      placeholder="Ej: 80000"
                      required
                    />
                  </div>

                  <div class="mb-3">
                    <label class="form-label text-muted">Estado</label>
                    <select
                      id="editStatus"
                      class="form-select bg-black text-light border-secondary"
                      required
                    >
                      <option value="Activa">Activa</option>
                      <option value="Inactiva">Inactiva</option>
                    </select>
                  </div>
                </form>
              </div>

              <div class="modal-footer bg-dark border-secondary">
                <button
                  type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  form="editMembershipForm"
                  class="btn btn-warning text-dark fw-bold"
                >
                  Guardar Cambios
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- MODAL CONFIRMAR ELIMINACIÓN -->
        <div
          class="modal fade"
          id="deleteMembershipModal"
          tabindex="-1"
          aria-labelledby="deleteMembershipModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light border-0 shadow-lg">
              <div class="modal-header bg-dark border-secondary">
                <h5
                  class="modal-title text-danger fw-bold"
                  id="deleteMembershipModalLabel"
                >
                  <i data-feather="alert-triangle" class="me-2"></i>Confirmar
                  Eliminación
                </h5>
                <button
                  type="button"
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal"
                  aria-label="Cerrar"
                ></button>
              </div>

              <div class="modal-body text-center">
                <p class="mb-3">
                  ¿Estás seguro de que deseas eliminar esta membresía?
                </p>
                <p class="text-muted small">
                  Esta acción no se puede deshacer.
                </p>
              </div>

              <div
                class="modal-footer border-secondary bg-dark"
              >
                <button
                  type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal"
                >
                  Cancelar
                </button>
                <button
                  type="button"
                  id="confirmDeleteBtn"
                  class="btn btn-danger fw-bold"
                >
                  Sí, eliminar
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