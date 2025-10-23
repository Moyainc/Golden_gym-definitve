@extends('layouts.app')
@section('title', 'Clientes - Golden Gym')

@section('content')
<body>
  <div class="wrapper">
    <div class="main">

      <main class="content">
        <div class="container-fluid p-0">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 text-white mb-0 fw-bold">
              <i data-feather="users" class="me-2 tittle-color"></i>Clientes
            </h1>

            <div class="d-flex gap-2">
              <div class="input-group input-group-sm">
                <span
                  class="input-group-text bg-secondary border-0 text-muted">
                  <i data-feather="search" class="feather-sm"></i>
                </span>
                <input
                  id="searchClients"
                  class="form-control form-control-sm bg-dark text-white border-0"
                  placeholder="Buscar cliente por nombre, correo o identificación..." />
              </div>

              <button
                type="button"
                class="btn btn-info btn-sm text-white shadow-sm"
                data-bs-toggle="modal"
                data-bs-target="#dailyPaymentModal">
                <i data-feather="dollar-sign" class="me-1"></i> Pago diario
              </button>

              <button
                type="button"
                class="btn btn-warning tittle-color btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#clientFormModal">
                <i data-feather="user-plus" class="me-1"></i> Nuevo cliente
              </button>
            </div>
          </div>

          <div class="row g-3">
            <!-- Filtros -->
            <div class="col-lg-4">
              <div class="card border-0 shadow-lg">
                <div
                  class="card-header border-0 d-flex justify-content-between align-items-center">
                  <h6 class="mb-0 text-warning fw-bold">
                    <i data-feather="filter" class="me-1"></i>Filtros Rápidos
                    - Membresías
                  </h6>
                  <small class="text-muted">Selecciona uno</small>
                </div>

                <div class="card-body">
                  <div class="row g-2">
                    <!-- Filtro Activas -->
                    <div class="col-6">
                      <button
                        class="filter-btn btn btn-dark w-100 d-flex flex-column align-items-center justify-content-center">
                        <i
                          data-feather="check-circle"
                          class="text-success mb-1"></i>
                        <span class="small text-white">Activas</span>
                      </button>
                    </div>

                    <div class="col-6">
                      <button
                        class="filter-btn btn btn-dark w-100 d-flex flex-column align-items-center justify-content-center">
                        <i data-feather="clock" class="text-warning mb-1"></i>
                        <span class="small text-white">Próximas a vencer</span>
                      </button>
                    </div>

                    <div class="col-6">
                      <button
                        class="filter-btn btn btn-dark w-100 d-flex flex-column align-items-center justify-content-center">
                        <i
                          data-feather="x-circle"
                          class="text-danger mb-1"></i>
                        <span class="small text-white">Vencidas</span>
                      </button>
                    </div>

                    <div class="col-6">
                      <button
                        class="filter-btn btn btn-dark w-100 d-flex flex-column align-items-center justify-content-center">
                        <i
                          data-feather="calendar"
                          class="text-primary mb-1"></i>
                        <span class="small text-white">Pagos diarios</span>
                      </button>
                    </div>

                    <div class="col-6">
                      <button
                        class="filter-btn btn btn-dark w-100 d-flex flex-column align-items-center justify-content-center">
                        <i
                          data-feather="refresh-ccw"
                          class="text-warning mb-1"></i>
                        <span class="small text-white">En revisión</span>
                      </button>
                    </div>

                    <div class="col-6">
                      <button
                        class="filter-btn btn btn-dark w-100 d-flex flex-column align-items-center justify-content-center">
                        <i data-feather="grid" class="text-light mb-1"></i>
                        <span class="small text-white">Todas</span>
                      </button>
                    </div>
                  </div>

                  <hr class="border-secondary mt-3" />
                  <div
                    class="d-flex justify-content-between mt-2 small text-white">
                    <div>
                      <strong>Total clientes:</strong>
                      <span id="clientsCount">0</span>
                    </div>
                    <div class="text-muted">
                      <strong>Filtrados:</strong>
                      <span id="clientsFilteredLabel">Todos</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tabla de clientes -->
            <div class="col-lg-8">
              <div class="card border-0 shadow-sm">
                <div
                  class="card-header border-bottom border-secondary d-flex justify-content-between align-items-center">
                  <h6 class="mb-0 tittle-color">
                    <i data-feather="user-check" class="me-1"></i>Clientes
                  </h6>
                  <small id="clientsActiveCount" class="text-muted">Activos: 0</small>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                      <thead>
                        <tr class="text-muted small">
                          <th>Foto</th>
                          <th>Nombre</th>
                          <th>Identificación</th>
                          <th>Membresía</th>
                          <th>Estado</th>
                          <th>Último pago</th>
                          <th class="text-end">Acciones</th>
                        </tr>
                      </thead>
                      <tbody id="clientsTableBody">
                        <tr>
                          <td>
                            <img
                              src="https://randomuser.me/api/portraits/men/45.jpg"
                              alt="Foto"
                              class="rounded-circle"
                              style="
                                  width: 45px;
                                  height: 45px;
                                  object-fit: cover;
                                " />
                          </td>
                          <td class="fw-semibold text-white">Carlos Pérez</td>
                          <td class="text-muted">CC 1032456789</td>
                          <td>
                            <span class="badge bg-success bg-opacity-75">Mensual</span>
                          </td>
                          <td>
                            <span class="badge bg-success bg-opacity-75">Activa</span>
                          </td>
                          <td>10/10/2025</td>
                          <td class="text-end">
                            <button
                              class="btn btn-sm btn-outline-info me-1"
                              data-bs-toggle="modal"
                              data-bs-target="#clientViewModal">
                              <i data-feather="eye"></i>
                            </button>
                            <button
                              class="btn btn-sm btn-outline-warning me-1"
                              data-bs-toggle="modal"
                              data-bs-target="#clientFormModal">
                              <i data-feather="edit"></i>
                            </button>
                            <button
                              class="btn btn-sm btn-outline-danger"
                              data-bs-toggle="modal"
                              data-bs-target="#confirmDeleteModal">
                              <i data-feather="trash-2"></i>
                            </button>
                          </td>
                        </tr>

                        <tr>
                          <td>
                            <img
                              src="https://randomuser.me/api/portraits/women/68.jpg"
                              alt="Foto"
                              class="rounded-circle"
                              style="
                                  width: 45px;
                                  height: 45px;
                                  object-fit: cover;
                                " />
                          </td>
                          <td class="fw-semibold text-white">María López</td>
                          <td class="text-muted">CC 1098765432</td>
                          <td>
                            <span class="badge bg-primary bg-opacity-75">Pago diario</span>
                          </td>
                          <td>
                            <span
                              class="badge bg-warning bg-opacity-75 text-dark">Pendiente</span>
                          </td>
                          <td>14/10/2025</td>
                          <td class="text-end">
                            <button
                              class="btn btn-sm btn-outline-info me-1"
                              data-bs-toggle="modal"
                              data-bs-target="#clientViewModal">
                              <i data-feather="eye"></i>
                            </button>
                            <button
                              class="btn btn-sm btn-outline-warning me-1"
                              data-bs-toggle="modal"
                              data-bs-target="#clientFormModal">
                              <i data-feather="edit"></i>
                            </button>
                            <button
                              class="btn btn-sm btn-outline-danger"
                              data-bs-toggle="modal"
                              data-bs-target="#confirmDeleteModal">
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

      <!-- Modal Registrar Pago Diario -->
      <div class="modal fade" id="dailyPaymentModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
          <div class="modal-content bg-dark border-0 text-white shadow-lg">
            <div class="modal-header border-secondary">
              <h5 class="modal-title text-info">
                <i data-feather="dollar-sign" class="me-1"></i> Registrar Pago
                Diario
              </h5>
              <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"></button>
            </div>

            <form id="dailyPaymentForm" class="modal-body row g-3">
              <div class="col-md-6">
                <label class="form-label">Nombre del cliente</label>
                <input
                  id="dailyClientName"
                  type="text"
                  class="form-control text-white border-0"
                  placeholder="Ej: Juan Pérez"
                  required />
              </div>

              <div class="col-md-6">
                <label class="form-label">Identificación</label>
                <input
                  id="dailyClientId"
                  type="text"
                  class="form-control text-white border-0"
                  placeholder="Ej: 1234567890"
                  required />
              </div>

              <div class="col-md-6">
                <label class="form-label">Método de pago</label>
                <select
                  id="dailyPaymentMethod"
                  class="form-select text-white border-0"
                  required>
                  <option value="">Seleccione</option>
                  <option value="efectivo">Efectivo</option>
                  <option value="transferencia">Transferencia</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Estado</label>
                <input
                  id="dailyPaymentStatusDisplay"
                  type="text"
                  class="form-control text-white border-0"
                  value="Completado"
                  readonly />
              </div>

              <div
                class="col-12"
                id="dailyComprobanteContainer"
                style="display: none">
                <label class="form-label">Subir Comprobante</label>
                <input
                  type="file"
                  id="dailyComprobantePago"
                  class="form-control text-white border-0"
                  accept="image/*,application/pdf" />
              </div>

              <div class="col-12">
                <label class="form-label">Valor del día</label>
                <input
                  id="dailyPaymentValue"
                  type="text"
                  class="form-control text-warning fw-bold border-0"
                  value="$10.000"
                  readonly />
              </div>

              <input
                type="hidden"
                id="dailyPaymentStatus"
                value="Completado" />
            </form>

            <div class="modal-footer border-secondary">
              <button class="btn btn-secondary" data-bs-dismiss="modal">
                Cancelar
              </button>
              <button
                id="saveDailyPaymentBtn"
                class="btn btn-info text-white">
                <i data-feather="save" class="me-1"></i> Registrar pago
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Agregar / Editar Cliente -->
      <div class="modal fade" id="clientFormModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
          <div class="modal-content bg-dark border-0 text-white shadow-lg">
            <div class="modal-header border-secondary">
              <h5 class="modal-title text-warning">
                <i data-feather="user-plus" class="me-1"></i> Cliente
              </h5>
              <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"></button>
            </div>

            <form id="clientForm" class="modal-body row g-3">
              <input type="hidden" id="clientId" />

              <!-- Foto y nombre -->
              <div class="col-md-4 text-center">
                <label class="form-label">Foto</label>
                <input
                  id="clientPhoto"
                  type="file"
                  accept="image/*"
                  class="form-control text-white border-0" />
                <img
                  id="clientPhotoPreview"
                  src=""
                  alt=""
                  class="rounded-circle mt-2"
                  style="
                      width: 70px;
                      height: 70px;
                      display: none;
                      object-fit: cover;
                    " />
              </div>

              <div class="col-md-8">
                <label class="form-label">Nombre completo *</label>
                <input
                  id="clientName"
                  type="text"
                  class="form-control text-white border-0"
                  required />
              </div>

              <!-- Contacto -->
              <div class="col-md-6">
                <label class="form-label">Correo</label>
                <input
                  id="clientEmail"
                  type="email"
                  class="form-control text-white border-0" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Teléfono</label>
                <input
                  id="clientPhone"
                  type="tel"
                  class="form-control text-white border-0" />
              </div>

              <!-- Identificación y membresía -->
              <div class="col-md-6">
                <label class="form-label">Identificación</label>
                <input
                  id="clientIdNumber"
                  type="text"
                  class="form-control text-white border-0" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Tipo de membresía</label>
                <select
                  id="clientMembershipType"
                  class="form-select text-white border-0">
                  <option value="">-- Ninguna / Pago diario --</option>
                  <option value="monthly">Mensual</option>
                  <option value="quarterly">Trimestral</option>
                  <option value="annual">Anual</option>
                </select>
              </div>

              <!-- Fechas -->
              <div class="col-md-6">
                <label class="form-label">Fecha inicio</label>
                <input
                  id="clientMembershipStart"
                  type="date"
                  class="form-control text-white border-0" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Fecha fin</label>
                <input
                  id="clientMembershipEnd"
                  type="date"
                  class="form-control text-white border-0" />
              </div>

              <!-- Método de pago -->
              <div class="col-md-6">
                <label class="form-label">Método de pago</label>
                <select
                  id="paymentMethod"
                  class="form-select text-white border-0">
                  <option value="">Seleccione</option>
                  <option value="efectivo">Efectivo</option>
                  <option value="transferencia">Transferencia</option>
                  <option value="tarjeta">Tarjeta</option>
                  <option value="pendiente">Pendiente</option>
                </select>
              </div>

              <!-- Estado y comprobante -->
              <div class="col-md-6">
                <label class="form-label">Estado del pago</label>
                <input
                  id="paymentStatusDisplay"
                  type="text"
                  class="form-control text-white border-0"
                  readonly
                  value="Completado" />
              </div>

              <div
                class="col-12"
                id="comprobanteContainer"
                style="display: none">
                <label class="form-label">Subir Comprobante</label>
                <input
                  type="file"
                  id="comprobantePago"
                  class="form-control text-white border-0"
                  accept="image/*,application/pdf" />
              </div>

              <!-- Precio -->
              <div class="col-md-6">
                <label class="form-label">Precio</label>
                <input
                  id="membershipPrice"
                  type="text"
                  class="form-control text-white border-0"
                  readonly />
              </div>

              <!-- Estado oculto -->
              <input type="hidden" id="paymentStatus" value="completado" />
            </form>

            <div class="modal-footer border-secondary">
              <button class="btn btn-secondary" data-bs-dismiss="modal">
                Cancelar
              </button>
              <button id="saveClientBtn" class="btn btn-warning">
                <i data-feather="save" class="me-1"></i> Guardar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Ver Cliente -->
      <div class="modal fade" id="clientViewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content bg-dark border-0 text-white shadow-lg">
            <div class="modal-header border-secondary">
              <h5 class="modal-title text-warning">
                <i data-feather="user" class="me-1"></i> Perfil del cliente
              </h5>
              <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-4 text-center">
                  <img
                    id="viewPhoto"
                    src="https://via.placeholder.com/150"
                    alt="Avatar"
                    class="rounded-circle mb-2"
                    style="width: 130px; height: 130px; object-fit: cover" />
                  <h4 id="viewName" class="text-white mt-2 mb-0"></h4>
                  <div id="viewIdNumber" class="text-muted small mb-2"></div>
                  <div class="d-grid gap-2">
                    <button
                      id="btnRegisterDailyPayment"
                      class="btn btn-info btn-sm">
                      Registrar pago diario
                    </button>
                    <button
                      id="btnRenewMembership"
                      class="btn btn-outline-warning btn-sm">
                      Renovar membresía
                    </button>
                  </div>
                </div>

                <div class="col-md-8">
                  <div class="mb-3">
                    <strong>Contacto:</strong>
                    <p id="viewContact" class="mb-1"></p>
                    <strong>Correo:</strong>
                    <p id="viewEmail" class="mb-1"></p>
                    <strong>Estado de membresía:</strong>
                    <p id="viewMembership" class="mb-1"></p>
                    <strong>Último pago:</strong>
                    <p id="viewLastPayment" class="mb-1"></p>
                  </div>
                  <hr class="border-secondary" />
                </div>
              </div>
            </div>
            <div class="modal-footer border-secondary">
              <button class="btn btn-secondary" data-bs-dismiss="modal">
                Cerrar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Confirmar Eliminar -->
      <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
          <div class="modal-content bg-dark border-0 text-white shadow-lg">
            <div class="modal-body text-center">
              <p class="mb-3">
                ¿Estás seguro que deseas eliminar este cliente?
              </p>
              <div class="d-flex justify-content-center gap-2">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                  Cancelar
                </button>
                <button id="confirmDeleteBtn" class="btn btn-danger">
                  Eliminar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../js/app.js"></script>
</body>
@endsection
