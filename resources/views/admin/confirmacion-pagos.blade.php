@extends('layouts.app')
@section('title', 'Confirmación - Golden Gym')

@section('content')
  <body>
    <div class="wrapper">
      <div class="main">
        <main class="content">
          <div class="container-fluid p-0">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h1 class="h3 text-white fw-bold mb-0">
                <i data-feather="check-circle" class="me-2 tittle-color"></i
                >Confirmación de Pagos
              </h1>

              <button
                class="btn btn-warning text-dark fw-bold shadow-sm"
                id="refreshPayments"
              >
                <i data-feather="refresh-cw" class="me-1"></i>Actualizar
              </button>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <div
                  class="card bg-black border-warning text-center p-3 shadow-sm"
                >
                  <h6 class="text-muted">Pagos Pendientes</h6>
                  <h3 class="text-warning mb-0 fw-bold" id="pendingPayments">
                    5
                  </h3>
                </div>
              </div>
              <div class="col-md-4">
                <div
                  class="card bg-black border-success text-center p-3 shadow-sm"
                >
                  <h6 class="text-muted">Pagos Confirmados</h6>
                  <h3 class="text-success mb-0 fw-bold" id="confirmedPayments">
                    12
                  </h3>
                </div>
              </div>
              <div class="col-md-4">
                <div
                  class="card bg-black border-danger text-center p-3 shadow-sm"
                >
                  <h6 class="text-muted">Pagos Rechazados</h6>
                  <h3 class="text-danger mb-0 fw-bold" id="rejectedPayments">
                    1
                  </h3>
                </div>
              </div>
            </div>

            <!-- FILTROS -->
            <div class="card border-0 shadow-sm mb-4">
              <div
                class="card-header border-0 d-flex justify-content-between align-items-center"
              >
                <h5 class="text-warning mb-0">
                  <i data-feather="filter" class="me-1"></i>Filtros
                </h5>
              </div>
              <div class="card-body">
                <div class="row g-2">
                  <div class="col-md-4">
                    <select
                      id="filterStatus"
                      class="form-select text-light border-secondary bg-dark"
                    >
                      <option value="">Estado del Pago</option>
                      <option>Pendiente</option>
                      <option>Confirmado</option>
                      <option>Rechazado</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <input
                      id="filterUser"
                      type="text"
                      class="form-control text-light border-secondary bg-dark"
                      placeholder="Buscar por usuario..."
                    />
                  </div>
                  <div class="col-md-4">
                    <input
                      id="filterDate"
                      type="date"
                      class="form-control text-light border-secondary bg-dark"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- TABLA DE PAGOS -->
            <div class="card border-0 shadow-sm mb-4">
              <div
                class="card-header border-0 d-flex justify-content-between align-items-center"
              >
                <h5 class="text-warning mb-0">
                  <i data-feather="dollar-sign" class="me-1"></i>Pagos
                  Pendientes de Confirmación
                </h5>
              </div>
              <div class="card-body table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="text-warning border-secondary">
                    <tr>
                      <th>ID</th>
                      <th>Usuario</th>
                      <th>Monto</th>
                      <th>Método</th>
                      <th>Fecha</th>
                      <th>Estado</th>
                      <th>Comprobante</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody id="paymentsTable" class="text-light">
                    <tr>
                      <td>#001</td>
                      <td>Carlos Gómez</td>
                      <td>$120.000</td>
                      <td>Transferencia</td>
                      <td>2025-10-12</td>
                      <td>
                        <span class="badge bg-warning text-dark"
                          >Pendiente</span
                        >
                      </td>
                      <td>
                        <button
                          class="btn btn-sm btn-outline-info view-proof"
                          data-bs-toggle="modal"
                          data-bs-target="#proofModal"
                        >
                          <i data-feather="image"></i>
                        </button>
                      </td>
                      <td>
                        <button
                          class="btn btn-sm btn-outline-success me-1 confirm-btn"
                        >
                          <i data-feather="check"></i>
                        </button>
                        <button
                          class="btn btn-sm btn-outline-danger reject-btn"
                        >
                          <i data-feather="x"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </main>

        <!-- MODAL COMPROBANTE -->
        <div
          class="modal fade"
          id="proofModal"
          tabindex="-1"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark text-light border-warning">
              <div class="modal-header border-warning">
                <h5 class="modal-title text-warning">
                  <i data-feather="image" class="me-1"></i>Comprobante de Pago
                </h5>
                <button
                  type="button"
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal"
                ></button>
              </div>
              <div class="modal-body text-center">
                <img
                  src="img/comprobantes/example.jpg"
                  alt="Comprobante"
                  class="img-fluid rounded shadow"
                />
              </div>
            </div>
          </div>
        </div>

        <div id="footer"></div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <script
      src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"
      defer
    ></script>
    <script
      src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
      defer
    ></script>

    <script src="../js/app.js"></script>
  </body>
  @endsection
