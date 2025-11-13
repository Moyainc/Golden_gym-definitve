@extends('layouts.app')
@section('title', 'Pagos Confirmados - Golden Gym')

@section('content')
<div class="wrapper">
  <div class="main">
    <div id="navbar"></div>

    <main class="content">
      <div class="container-fluid p-0">
        <!-- Título principal -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="h3 text-white fw-bold mb-0">
            <i data-feather="check-circle" class="me-2 tittle-color"></i>Pagos Confirmados
          </h1>
        </div>

        <!-- Filtros -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header border-0 d-flex justify-content-between align-items-center">
            <h5 class="text-warning mb-0">
              <i data-feather="filter" class="me-1"></i>Filtros de Búsqueda
            </h5>
          </div>
          <div class="card-body">
            <div class="row g-2 align-items-end">
              <!-- Método -->
              <div class="col-md-2">
                <label class="form-label text-light">Comprobante</label>
                <select id="filterMetodo" class="form-select form-select-sm">
                  <option value="">Todos</option>
                  <option value="Efectivo">Sin comprobante</option>
                </select>
              </div>

              <!-- Fecha Desde -->
              <div class="col-md-2">
                <label class="form-label text-light">Desde</label>
                <input type="date" id="filterFechaDesde" class="form-control form-select-sm" />
              </div>

              <!-- Fecha Hasta -->
              <div class="col-md-2">
                <label class="form-label text-light">Hasta</label>
                <input type="date" id="filterFechaHasta" class="form-control form-select-sm" />
              </div>

              <!-- Buscar -->
              <div class="col-md-3">
                <label class="form-label text-light">Buscar</label>
                <input type="text" id="searchPago" class="form-control form-select-sm"
                  placeholder="Cliente, vendedor o transacción..." />
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="text-white mb-0">
            <i data-feather="list"></i>
            <span id="contadorPagos">Cargando pagos...</span>
          </h6>
        </div>

        <!-- Tabla -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body table-responsive">
            <table id="tablaPagosConfirmados" class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Transacción</th>
                  <th>Cliente</th>
                  <th>Vendedor</th>
                  <th>Método</th>
                  <th>Monto</th>
                  <th>Fecha</th>
                  <th>Comprobante</th>
                </tr>
              </thead>
              <tbody>
                <!-- Aquí el JS inyectará las filas -->
              </tbody>
            </table>

          </div>

          <!-- Paginación -->
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
    </main>

    <div id="footer"></div>
  </div>
</div>


<!-- Modal para ver o editar comprobante -->
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

        <!-- Botones -->
        <div class="d-flex justify-content-center gap-2">
          <button id="deleteProofBtn" class="btn btn-sm btn-outline-danger">
            <i data-feather="trash-2"></i> Eliminar
          </button>
        </div>
      </div>
    </div>
  </div>
</div>


<script src="{{ asset('js/comprobantes/confirmados.js') }}"></script>
@endsection
