@extends('layouts.app')
@section('title', 'Confirmación - Golden Gym')

@section('content')
<div class="wrapper">
  <div class="main">
    <main class="content">
      <div class="container-fluid p-0">

        <!-- Pagos Pendientes -->
        <div class="row g-4 mb-4">
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-dark-card position-relative overflow-hidden">
              <div class="gradient-overlay bg-warning opacity-10"></div>
              <div class="card-body py-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted text-uppercase small mb-2">Pagos Pendientes</h6>
                  <h3 id="pendingPayments" class="fw-bold text-warning mb-0">0</h3>
                  <small class="text-secondary">Esperando confirmación</small>
                </div>
                <div class="icon-box bg-warning bg-opacity-10 p-3 rounded-3">
                  <i data-feather="clock" class="text-warning" width="28"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabla de Pagos Pendientes -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header border-0 d-flex justify-content-between align-items-center">
            <h5 class="text-warning mb-0">
              <i data-feather="dollar-sign" class="me-1"></i>Pagos Pendientes
            </h5>
          </div>
          <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="text-warning border-secondary">
                <tr>
                  <th>Transaccion</th>
                  <th>Cliente</th>
                  <th>Vendedor</th>
                  <th>Monto</th>
                  <th>Método</th>
                  <th>Fecha</th>
                  <th>Comprobante</th>
                </tr>
              </thead>
              <tbody id="paymentsTable" class="text-light">
                <!-- Filas generadas dinámicamente -->
              </tbody>
            </table>

            <div class="card-footer border-0 d-flex justify-content-between align-items-center py-3 px-3">
              <nav aria-label="Paginación de pagos">
                <ul class="pagination-modern d-flex align-items-center gap-2 mb-0 list-unstyled">
                  <!-- generado por JS -->
                </ul>
              </nav>

              <span id="paginationInfo" class="text-light small fw-light opacity-75">
                <!-- info de paginación -->
              </span>
            </div>


          </div>
        </div>

        <!-- Modal Comprobante -->
        <div class="modal fade modal-dark-style" id="proofModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark text-light border-warning">
              <div class="modal-header border-warning">
                <h5 class="modal-title text-warning">
                  <i data-feather="image" class="me-1"></i>Comprobante de Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body text-center">
                <!-- Imagen de vista previa -->
                <img
                  id="modalProofImage"
                  src=""
                  alt="Comprobante"
                  class="img-fluid rounded shadow mb-3"
                  style="max-width: 100%; max-height: 350px;" />

                <!-- Input para subir nueva imagen -->
                <input
                  type="file"
                  id="modalProofFile"
                  class="form-control mb-3"
                  accept="image/*" />

                <!-- Botones -->
                <div class="d-flex justify-content-center gap-2">
                  <button id="saveProofBtn" class="btn btn-sm btn-outline-success">
                    <i data-feather="check"></i> Enviar
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>




      </div>
    </main>
  </div>
</div>

<script src="{{ asset('js/comprobantes/pendientes.js') }}"></script>
@endsection
