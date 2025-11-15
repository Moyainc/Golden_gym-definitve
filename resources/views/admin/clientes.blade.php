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

            <div class="d-flex flex-column flex-md-row gap-2 align-items-stretch">
              <div class="input-group input-group-sm grow">
                <span class="input-group-text bg-secondary border-0 text-muted">
                  <i data-feather="search" class="feather-sm"></i>
                </span>
                <input
                  id="searchClients"
                  class="form-control form-control-sm bg-dark text-white border-0"
                  placeholder="Buscar cliente por nombre, correo o identificación..." />
              </div>
              <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
                <button
                  type="button"
                  class="btn btn-success btn-sm grow"
                  data-bs-toggle="modal"
                  data-bs-target="#reportsModal">
                  <i data-feather="file-text" class="me-1"></i> Reportes
                </button>

                <button
                  type="button"
                  class="btn btn-info btn-sm grow"
                  data-bs-toggle="modal"
                  data-bs-target="#dailyPaymentModal">
                  <i data-feather="dollar-sign" class="me-1"></i> Pago Diario
                </button>

                <button
                  type="button"
                  class="btn btn-warning tittle-color btn-sm grow"
                  data-bs-toggle="modal"
                  data-bs-target="#clientFormModal">
                  <i data-feather="user-plus" class="me-1"></i> Nuevo cliente
                </button>
              </div>

            </div>

          </div>

          <!-- Reorganizando layout: filtros arriba, tabla abajo -->
          <div class="row g-3">
            <!-- Filtros -->
            <div class="col-12">
              <div class="card border-0 shadow-lg">
                <div
                  class="card-header border-0 d-flex justify-content-between align-items-center">
                  <h6 class="mb-0 text-warning fw-bold">
                    <i data-feather="filter" class="me-1"></i>Filtros Rápidos - Membresías
                  </h6>
                  <div class="d-flex gap-3 align-items-center">
                    <small class="text-muted">
                      <strong>Total clientes:</strong>
                      <span id="clientsCount" class="text-white">0</span>
                    </small>
                    <small class="text-muted">
                      <strong>Filtrados:</strong>
                      <span id="clientsFilteredLabel" class="text-white">Todas</span>
                    </small>
                  </div>
                </div>

                <div class="card-body">
                  <div class="row g-2">
                    <div class="col-md-3">
                      <button
                        class="filter-btn btn btn-dark w-100 d-flex align-items-center justify-content-center gap-2"
                        data-filter="activa">
                        <i data-feather="check-circle" class="text-success"></i>
                        <span class="text-light">Activas</span>
                      </button>
                    </div>

                    <div class="col-md-3">
                      <button
                        class="filter-btn btn btn-dark w-100 d-flex align-items-center justify-content-center gap-2"
                        data-filter="proxima">
                        <i data-feather="clock" class="text-warning"></i>
                        <span class="text-light">Próximas a vencer</span>
                      </button>
                    </div>

                    <div class="col-md-3">
                      <button
                        class="filter-btn btn btn-dark w-100 d-flex align-items-center justify-content-center gap-2"
                        data-filter="vencida">
                        <i data-feather="x-circle" class="text-danger"></i>
                        <span class="text-light">Vencidas</span>
                      </button>
                    </div>

                    <div class="col-md-3">
                      <button class="filter-btn btn btn-warning w-100 d-flex align-items-center justify-content-center gap-2" data-filter="todas">
                        <i data-feather="grid" class="text-secondary"></i>
                        <span>Todas</span>
                      </button>

                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tabla de clientes -->
            <div class="col-12">
              <div class="card border-0 shadow-sm">
                <div
                  class="card-header border-bottom border-secondary d-flex justify-content-between align-items-center">
                  <h6 class="mb-0 tittle-color">
                    <i data-feather="user-check" class="me-1"></i>Últimos 10 Clientes
                  </h6>
                </div>

                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                      <thead>
                        <tr class="text-muted small">
                          <th>#</th>
                          <th>Foto</th>
                          <th>Nombre</th>
                          <th>Identificación</th>
                          <th>Membresía</th>
                          <th>Fecha Inicio</th>
                          <th>Fecha Fin</th>
                          <th>Estado</th>
                          <th class="text-end">Acciones</th>
                        </tr>
                      </thead>
                      <tbody id="clientsTableBody">
                        <tr>
                          <td colspan="9" class="text-center text-muted">Cargando clientes...</td>
                        </tr>
                      </tbody>
                    </table>
                    <div id="paginacionClientes" class="text-center mt-3"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tabla de pagos diarios -->
            <div class="col-12">
              <div class="card border-0 shadow-sm">
                <div
                  class="card-header border-bottom border-secondary d-flex justify-content-between align-items-center">
                  <h6 class="mb-0 text-info">
                    <i data-feather="dollar-sign" class="me-1"></i>Pagos Diarios Recientes
                  </h6>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                      <thead>
                        <tr class="text-muted small">
                          <th>#</th>
                          <th>Nombre</th>
                          <th>Tipo</th>
                          <th>Fecha de Pago</th>
                          <th>Método de Pago</th>
                          <th class="text-end">Acciones</th>
                        </tr>
                      </thead>
                      <tbody id="dailyPaymentsTableBody">
                        <tr>
                          <td colspan="6" class="text-center text-muted">Cargando pagos diarios...</td>
                        </tr>
                      </tbody>
                    </table>
                    <div id="paginacionPagosDiarios" class="text-center mt-3"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      <!-- MODAL CREAR CLIENTE -->
      <div class="modal fade modal-dark-style" id="clientFormModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content bg-dark border-0 p-0 text-white shadow-lg">
            <div class="modal-header border-secondary">
              <h5 class="modal-title text-warning">
                <i data-feather="user-plus" class="me-1"></i> <span id="modalTitle">Registrar Cliente</span>
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
              <form id="clientForm" class="row g-3 p-3" enctype="multipart/form-data">
                <input type="hidden" id="clientId" name="id" />
                <input type="hidden" id="usuarioId" name="usuario_id" value="{{ auth()->user()->id }}">

                <!-- Foto del cliente -->
                <div class="col-12 text-center">
                  <label class="form-label">Foto del cliente</label>
                  <div class="d-flex justify-content-center align-items-center gap-3">
                    <img
                      id="clientPhotoPreview"
                      src="{{ asset('img/avatars/user.png') }}"
                      alt="Foto del cliente"
                      class="rounded-circle"
                      style="width:100px;height:100px;object-fit:cover;" />
                    <div>
                      <input
                        id="clientPhoto"
                        name="foto"
                        type="file"
                        accept="image/*"
                        capture="environment"
                        class="form-control text-white border-0 mb-2" />
                      <small class="text-muted">Selecciona una imagen o toma una foto</small>
                    </div>
                  </div>
                </div>

                <hr class="border-secondary" />

                <!-- Información personal -->
                <div class="col-md-6">
                  <label class="form-label">Nombre completo *</label>
                  <input
                    id="clientName"
                    name="nombre_completo"
                    type="text"
                    class="form-control text-white border-0"
                    required />
                </div>


                <div class="col-md-6">
                  <label class="form-label">Identificación *</label>
                  <input
                    id="clientIdNumber"
                    name="cedula"
                    type="number"
                    class="form-control text-white border-0"
                    required />
                </div>

                <!-- Contacto -->
                <div class="col-md-6">
                  <label class="form-label">Correo electrónico *</label>
                  <input
                    id="clientEmail"
                    name="correo"
                    type="email"
                    class="form-control text-white border-0"
                    required />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Teléfono</label>
                  <input
                    id="clientPhone"
                    name="telefono"
                    type="tel"
                    class="form-control text-white border-0" />
                </div>

                <div class="col-md-6">
                  <label class="form-label">EPS</label>
                  <input
                    id="clientEps"
                    name="eps"
                    type="text"
                    class="form-control text-white border-0" />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Número de emergencia</label>
                  <input
                    id="clientEmergency"
                    name="numero_emergencia"
                    type="tel"
                    class="form-control text-white border-0" />
                </div>


                <hr class="border-secondary" />

                <!-- Membresía -->
                <div class="col-md-6">
                  <label class="form-label">Tipo de membresía *</label>
                  <select
                    id="clientMembershipType"
                    name="membresia_id"
                    class="form-select text-white border-0"
                    required>
                    <option value="">-- Seleccione una membresía --</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Método de pago *</label>
                  <select
                    id="clientPaymentMethod"
                    name="metodo_pago"
                    class="form-select text-white border-0"
                    required>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Transferencia">Transferencia</option>
                  </select>
                </div>

                <!-- Fechas -->
                <div class="col-md-4">
                  <label class="form-label">Fecha inicio</label>
                  <input
                    id="clientMembershipStart"
                    name="fecha_inicio"
                    type="date"
                    class="form-control text-white border-0"
                    readonly />
                </div>

                <div class="col-md-4">
                  <label class="form-label">Fecha fin</label>
                  <input
                    id="clientMembershipEnd"
                    name="fecha_fin"
                    type="date"
                    class="form-control text-white border-0"
                    readonly />
                </div>

                <!-- Precio -->
                <div class="col-md-4">
                  <label class="form-label">Precio</label>
                  <input
                    id="membershipPrice"
                    name="monto"
                    type="text"
                    class="form-control text-warning fw-bold border-0"
                    readonly />
                </div>

                <div class="modal-footer border-secondary">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" id="saveClientBtn" class="btn btn-warning">
                    <i data-feather="save" class="me-1"></i> Guardar cliente
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- MODAL VER CLIENTE -->
      <div class="modal fade modal-dark-style" id="viewClientModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content bg-dark border-0 text-white shadow-lg">
            <div class="modal-header border-secondary">
              <h5 class="modal-title text-warning">
                <i data-feather="eye" class="me-1"></i> Ver Cliente
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body row g-3">
              <!-- FOTO -->
              <div class="col-12 text-center">
                <label class="form-label">Foto del cliente</label>
                <div class="d-flex justify-content-center align-items-center">
                  <img
                    id="viewClientPhoto"
                    src="{{ asset('img/avatars/user.png') }}"
                    alt="Foto del cliente"
                    class="rounded-circle shadow"
                    style="width:140px; height:140px; object-fit:cover; border:3px solid #ffc107;" />

                </div>
                <h5 class="mt-3 text-warning fw-bold" id="viewClientName">Nombre del cliente</h5>
                <p class="text-muted mb-0">Cédula: <span id="viewClientIdNumber">N/A</span></p>
              </div>

              <hr class="border-secondary" />

              <!-- INFORMACIÓN PERSONAL -->
              <div class="col-md-6">
                <label class="form-label">Correo electrónico</label>
                <div class="form-control border-0 text-white" style="background-color:#1e1e1e;" id="viewClientEmail">—</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Teléfono</label>
                <div class="form-control border-0 text-white" style="background-color:#1e1e1e;" id="viewClientPhone">—</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">EPS</label>
                <div class="form-control border-0 text-white" style="background-color:#1e1e1e;" id="viewClientEps">—</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Número de emergencia</label>
                <div class="form-control border-0 text-white" style="background-color:#1e1e1e;" id="viewClientEmergency">—</div>
              </div>

              <hr class="border-secondary" />

              <!-- MEMBRESÍA -->
              <div class="col-md-6">
                <label class="form-label">Tipo de membresía</label>
                <div class="form-control border-0 text-white" style="background-color:#1e1e1e;" id="viewClientMembership">—</div>
              </div>

              <div class="col-md-3">
                <label class="form-label">Fecha inicio</label>
                <div class="form-control border-0 text-white" style="background-color:#1e1e1e;" id="viewClientStart">—</div>
              </div>

              <div class="col-md-3">
                <label class="form-label">Fecha fin</label>
                <div class="form-control border-0 text-white" style="background-color:#1e1e1e;" id="viewClientEnd">—</div>
              </div>

              <!-- ESTADO -->
              <div class="col-12 text-center mt-3">
                <label class="form-label d-block">Estado</label>
                <div id="viewClientEstado" class="fs-5 fw-semibold text-info">Activo</div>
              </div>
            </div>

            <div class="modal-footer border-secondary">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
          </div>
        </div>
      </div>


      <!-- MODAL EDITAR CLIENTE -->
      <div class="modal fade modal-dark-style" id="editClientModal" tabindex="-1" aria-labelledby="editClientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content bg-dark border-0 text-white shadow-lg">
            <div class="modal-header border-secondary">
              <h5 class="modal-title text-warning" id="editClientModalLabel">
                <i data-feather="edit" class="me-1"></i> Editar Cliente
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editClientForm" class="modal-body row g-3" enctype="multipart/form-data">
              <input type="hidden" id="editClientId" name="id">

              <!-- Foto del cliente en modal editar -->
              <div class="col-12 text-center">
                <label class="form-label">Foto del cliente</label>
                <div class="d-flex justify-content-center align-items-center gap-3">
                  <img
                    id="editClientPhotoPreview"
                    src="{{ asset('img/avatars/user.png') }}"
                    alt="Foto del cliente"
                    class="rounded-circle"
                    style="width:100px;height:100px;object-fit:cover;" />
                  <div>
                    <input
                      id="editClientPhoto"
                      name="foto"
                      type="file"
                      accept="image/*"
                      capture="environment"
                      class="form-control text-white border-0 mb-2" />
                    <small class="text-muted">Selecciona una imagen o toma una foto</small>
                  </div>
                </div>
              </div>

              <hr class="border-secondary" />

              <div class="col-md-6">
                <label class="form-label">Nombre Completo *</label>
                <input type="text" class="form-control text-white border-0" id="editClientName" name="nombre_completo" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Cédula *</label>
                <input type="text" class="form-control text-white border-0" id="editClientIdNumber" name="cedula" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Correo *</label>
                <input type="email" class="form-control text-white border-0" id="editClientEmail" name="correo" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Teléfono</label>
                <input type="tel" class="form-control text-white border-0" id="editClientPhone" name="telefono">
              </div>

              <div class="col-md-6">
                <label class="form-label">EPS</label>
                <input type="text" class="form-control text-white border-0" id="editClientEps" name="eps">
              </div>

              <div class="col-md-6">
                <label class="form-label">Número de emergencia</label>
                <input type="tel" class="form-control text-white border-0" id="editClientEmergency" name="numero_emergencia">
              </div>

              <hr class="border-secondary" />

              <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" id="editClientSaveBtn" class="btn btn-warning">
                  <i data-feather="save" class="me-1"></i> Guardar cambios
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal para Pago Diario -->
      <div class="modal fade modal-dark-style" id="dailyPaymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-dark border-0 text-white shadow-lg">
            <div class="modal-header border-secondary">
              <h5 class="modal-title text-info">
                <i data-feather="dollar-sign" class="me-1"></i> Registrar Pago Diario
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="dailyPaymentForm" class="modal-body">
              <input type="hidden" name="usuario_id" value="{{ auth()->id() }}">
              <div class="mb-3">
                <label class="form-label">Nombre del cliente *</label>
                <input
                  id="dailyPaymentName"
                  name="nombre_cliente"
                  type="text"
                  class="form-control text-white border-0"
                  required />
              </div>

              <div class="mb-3">
                <label class="form-label">Monto *</label>
                <input
                  id="dailyPaymentAmount"
                  name="monto"
                  type="number"
                  step="0.01"
                  min="0"
                  class="form-control text-white border-0"
                  required />
              </div>

              <div class="mb-3">
                <label class="form-label">Fecha del pago *</label>
                <input
                  id="dailyPaymentDate"
                  name="fecha"
                  value="{{ now()->format('Y-m-d') }}"
                  type="date"
                  class="form-control text-white border-0"
                  required />
              </div>

              <div class="mb-3">
                <label class="form-label">Método de pago *</label>
                <select
                  id="dailyPaymentMethod"
                  name="metodo_pago"
                  class="form-select text-white border-0"
                  required>
                  <option value="efectivo">Efectivo</option>
                  <option value="transferencia">Transferencia</option>
                </select>
              </div>

              <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-info">
                  <i data-feather="save" class="me-1"></i> Guardar pago
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal para Reportes -->
      <div class="modal fade modal-dark-style" id="reportsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-dark border-0 text-white shadow-lg">
            <div class="modal-header border-secondary">
              <h5 class="modal-title text-success">
                <i data-feather="file-text" class="me-1"></i> Generar Reporte de Clientes
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="reportsForm" class="modal-body">
              <div class="alert alert-info bg-opacity-10 border-info text-info">
                <i data-feather="info" class="me-1"></i>
                Selecciona un rango de fechas para filtrar los clientes por fecha de registro
              </div>

              <div class="mb-3">
                <label class="form-label">Fecha inicio</label>
                <input
                  id="reportStartDate"
                  name="fecha_inicio"
                  type="date"
                  class="form-control text-white border-0"
                  required />
              </div>

              <div class="mb-3">
                <label class="form-label">Fecha fin</label>
                <input
                  id="reportEndDate"
                  name="fecha_fin"
                  type="date"
                  class="form-control text-white border-0"
                  required />
              </div>

              <div class="mb-3">
                <label class="form-label">Estado de membresía (opcional)</label>
                <select
                  id="reportEstado"
                  name="estado"
                  class="form-select text-white border-0">
                  <option value="">Todos los estados</option>
                  <option value="activa">Activas</option>
                  <option value="proxima">Próximas a vencer</option>
                  <option value="vencida">Vencidas</option>
                </select>
              </div>

              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="includePagosDiarios" />
                <label class="form-check-label" for="includePagosDiarios">
                  Incluir pagos diarios en el reporte
                </label>
              </div>

              <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="exportPdfBtn" class="btn btn-danger">
                  <i data-feather="file" class="me-1"></i> Exportar PDF
                </button>
                <button type="button" id="exportExcelBtn" class="btn btn-success">
                  <i data-feather="download" class="me-1"></i> Exportar Excel
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="{{ asset('js/clientes.js') }}"></script>
  <script src="{{ asset('js/pagosdiarios.js') }}"></script>
</body>
@endsection
