@extends('layouts.app')
@section('title', 'Listado de ventas - Golden Gym')
@section('content')

<div class="wrapper">
  <div class="main">
    <div id="navbar"></div>

    <main class="content">
      <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="h3 text-white fw-bold mb-0">
            <i data-feather="list" class="me-2 tittle-color"></i>Listado de Ventas
          </h1>
        </div>

        <!-- FILTROS -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header border-0 d-flex justify-content-between align-items-center">
            <h5 class="text-warning mb-0">
              <i data-feather="filter" class="me-1"></i>Filtros de Búsqueda
            </h5>
          </div>
          <div class="card-body">
            <div class="row g-2 align-items-end">
              <!-- Categoría -->
              <div class="col-md-3">
                <label class="form-label text-light">Categoría</label>
                <select id="filterCategoria" class="form-select text-light border-secondary">
                  <option value="">Todas</option>
                  @foreach(\App\Models\Categoria::orderBy('nombre')->get() as $cat)
                  <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Fecha Desde -->
              <div class="col-md-2">
                <label class="form-label text-light">Desde</label>
                <input type="date" id="filterFechaDesde" class="form-control text-light border-secondary" />
              </div>

              <!-- Fecha Hasta -->
              <div class="col-md-2">
                <label class="form-label text-light">Hasta</label>
                <input type="date" id="filterFechaHasta" class="form-control text-light border-secondary" />
              </div>

              <!-- Buscar -->
              <div class="col-md-3">
                <label class="form-label text-light">Buscar</label>
                <input type="text" id="searchVenta" class="form-control text-light border-secondary"
                  placeholder="Producto, método, estado o vendedor..." />
              </div>
            </div>
          </div>
        </div>


        <!-- TABLA -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="text-warning border-secondary">
                <tr>
                  <th>Producto</th>
                  <th>Categoría</th>
                  <th>Método</th>
                  <th>Precio</th>
                  <th>Fecha</th>
                  <th>Estado</th>
                  <th>Vendedor</th>
                  <th>Acciones</th> <!-- Nueva columna para el ojito -->
                </tr>
              </thead>
              <tbody id="ventasTable" class="text-light">
                <!-- filas se llenan dinámicamente -->
              </tbody>
            </table>

          </div>

          <div class="card-footer border-0 d-flex justify-content-between align-items-center py-3 px-3">
            <nav aria-label="Paginación de ventas">
              <ul class="pagination-modern d-flex align-items-center gap-2 mb-0 list-unstyled">
                <!-- botones de paginación dinámicos -->
              </ul>
            </nav>
          </div>


          <!-- PAGINACIÓN -->
          <div class="card-footer border-0 d-flex justify-content-between align-items-center py-3 px-3">
            <nav aria-label="Paginación de ventas">
              <ul class="pagination-modern d-flex align-items-center gap-2 mb-0 list-unstyled">
                <!-- Aquí JS llenará la paginación -->
              </ul>
            </nav>

            <span id="paginationInfo" class="text-light small fw-light opacity-75">
              <!-- JS actualizará info de página -->
            </span>
          </div>
        </div>

      </div>
    </main>
    <div id="footer"></div>
  </div>
</div>

<!-- Modal para ver detalles de venta -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-dark text-light">
      <div class="modal-header border-0">
        <h5 class="modal-title text-warning">Detalle de la Venta</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detalleModalBody">
        <!-- Contenido cargado dinámicamente -->
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('js/ventas/lista_ventas.js') }}"></script>

@endsection
