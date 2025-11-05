@extends('layouts.app')
@section('title', 'Confirmación - Golden Gym')

@section('content')


<div class="wrapper">
  <div class="main">
    <main class="content">
      <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="h3 text-white fw-bold mb-0">
            <i data-feather="check-circle" class="me-2 tittle-color"></i>Confirmación de Pagos
          </h1>

          <button
            class="btn btn-warning text-dark fw-bold shadow-sm"
            id="refreshPayments">
            <i data-feather="refresh-cw" class="me-1"></i>Actualizar
          </button>
        </div>

        <!-- Bloque de Estadísticas Modernas -->
        <div class="row g-4 mb-4">

          <!-- Pagos Pendientes -->
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-dark-card position-relative overflow-hidden">
              <div class="gradient-overlay bg-warning opacity-10"></div>
              <div class="card-body py-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted text-uppercase small mb-2">Pagos Pendientes</h6>
                  <h3 id="pendingPayments" class="fw-bold text-warning mb-0">5</h3>
                  <small class="text-secondary">Esperando confirmación</small>
                </div>
                <div class="icon-box bg-warning bg-opacity-10 p-3 rounded-3">
                  <i data-feather="clock" class="text-warning" width="28"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagos Confirmados -->
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-dark-card position-relative overflow-hidden">
              <div class="gradient-overlay bg-success opacity-10"></div>
              <div class="card-body py-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted text-uppercase small mb-2">Pagos Confirmados</h6>
                  <h3 id="confirmedPayments" class="fw-bold text-success mb-0">12</h3>
                  <small class="text-secondary">Pagos exitosos</small>
                </div>
                <div class="icon-box bg-success bg-opacity-10 p-3 rounded-3">
                  <i data-feather="check-circle" class="text-success" width="28"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagos Rechazados -->
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-dark-card position-relative overflow-hidden">
              <div class="gradient-overlay bg-danger opacity-10"></div>
              <div class="card-body py-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted text-uppercase small mb-2">Pagos Rechazados</h6>
                  <h3 id="rejectedPayments" class="fw-bold text-danger mb-0">1</h3>
                  <small class="text-secondary">Fallidos o anulados</small>
                </div>
                <div class="icon-box bg-danger bg-opacity-10 p-3 rounded-3">
                  <i data-feather="x-circle" class="text-danger" width="28"></i>
                </div>
              </div>
            </div>
          </div>
        </div>



        <!-- FILTROS -->
        <div class="card border-0 shadow-sm mb-4">
          <div
            class="card-header border-0 d-flex justify-content-between align-items-center">
            <h5 class="text-warning mb-0">
              <i data-feather="filter" class="me-1"></i>Filtros
            </h5>
          </div>
          <div class="card-body">
            <div class="row g-2">
              <div class="col-md-4">
                <select
                  id="filterStatus"
                  class="form-select text-light border-secondary bg-dark">
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
                  placeholder="Buscar por usuario..." />
              </div>
              <div class="col-md-4">
                <input
                  id="filterDate"
                  type="date"
                  class="form-control text-light border-secondary bg-dark" />
              </div>
            </div>
          </div>
        </div>

        <!-- TABLA DE PAGOS -->
        <div class="card border-0 shadow-sm mb-4">
          <div
            class="card-header border-0 d-flex justify-content-between align-items-center">
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
                    <span class="badge bg-warning text-dark">Pendiente</span>
                  </td>
                  <td>
                    <button
                      class="btn btn-sm btn-outline-info view-proof"
                      data-bs-toggle="modal"
                      data-bs-target="#proofModal">
                      <i data-feather="image"></i>
                    </button>
                  </td>
                  <td>
                    <button
                      class="btn btn-sm btn-outline-success me-1 confirm-btn">
                      <i data-feather="check"></i>
                    </button>
                    <button
                      class="btn btn-sm btn-outline-danger reject-btn">
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
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-light border-warning">
          <div class="modal-header border-warning">
            <h5 class="modal-title text-warning">
              <i data-feather="image" class="me-1"></i>Comprobante de Pago
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center">
            <img
              src="img/comprobantes/example.jpg"
              alt="Comprobante"
              class="img-fluid rounded shadow" />
          </div>
        </div>
      </div>
    </div>

    <div id="footer"></div>
  </div>
</div>


@endsection
