@extends('layouts.app')
@section('title', 'Resumen de ventas - Golden Gym')
@section('content')

<div class="wrapper">
  <div class="main">
    <div id="navbar"></div>

    <main class="content">
      <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="h3 text-white fw-bold mb-0">
            <i data-feather="shopping-cart" class="me-2 tittle-color"></i>Resumen de Ventas
          </h1>
        </div>

        <!-- FILTROS Y EXPORTACIÓN -->
        <div class="d-flex flex-wrap flex-md-nowrap gap-2 align-items-center my-4">
          <div class="d-flex flex-wrap flex-md-nowrap gap-2 align-items-center">
            <small class="text-muted me-2">Filtrar ganancias:</small>

            <!-- Select Mes -->
            <select id="filterMonth" class="form-select form-select-sm bg-dark text-white border-0">
              <option value="">Todos los meses</option>
            </select>

            <!-- Select Año -->
            <select id="filterYear" class="form-select form-select-sm bg-dark text-white border-0">
              <option value="">Todos los años</option>
              @foreach ($anios as $anio)
              <option value="{{ $anio }}" {{ $anio == date('Y') ? 'selected' : '' }}>{{ $anio }}</option>
              @endforeach
            </select>

            <button id="applyFilter" class="btn btn-info py-3 ms-md-2 mt-2 mt-md-0 text-nowrap">Aplicar Filtro</button>
            <div class="d-flex flex-wrap align-items-center">
              <button
                type="button"
                class="btn btn-success py-3 ms-md-2 mt-2 mt-md-0 d-inline-flex align-items-center flex-nowrap text-nowrap"
                data-bs-toggle="modal"
                data-bs-target="#reportsVentasModal">
                <i data-feather="file-text" class="me-1"></i> Reportes
              </button>
            </div>
          </div>
        </div>



        <!-- MÉTRICAS -->
        <div class="row g-3 mb-4">
          <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted text-uppercase small mb-1">Total Ventas</h6>
                  <h5 id="totalVentas" class="text-warning fw-bold mb-0">$0</h5>
                </div>
                <div class="bg-warning bg-opacity-25 p-2 rounded">
                  <i data-feather="dollar-sign" class="text-warning" width="20"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted text-uppercase small mb-1">N° de Ventas</h6>
                  <h5 id="numVentas" class="text-success fw-bold mb-0">0</h5>
                </div>
                <div class="bg-success bg-opacity-25 p-2 rounded">
                  <i data-feather="shopping-cart" class="text-success" width="20"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted text-uppercase small mb-1">N° de Productos</h6>
                  <h5 id="numProductos" class="text-info fw-bold mb-0">0</h5>
                </div>
                <div class="bg-info bg-opacity-25 p-2 rounded">
                  <i data-feather="package" class="text-info" width="20"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted text-uppercase small mb-1">Pendientes por Confirmar</h6>
                  <h5 id="ventasPendientes" class="text-danger fw-bold mb-0">0</h5>
                </div>
                <div class="bg-danger bg-opacity-25 p-2 rounded">
                  <i data-feather="clock" class="text-danger" width="20"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- GRÁFICAS -->
        <div class="row g-4">
          <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="height: 500px;">
              <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h5 class="text-warning mb-0">
                  <i data-feather="pie-chart" class="me-1"></i>Ventas por Categoría
                </h5>
              </div>
              <div class="card-body" style="height: calc(100% - 60px);">
                <canvas id="ventasCategoriaChart" style="height:100%;"></canvas>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="height: 500px;">
              <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h5 class="text-warning mb-0">
                  <i data-feather="trending-up" class="me-1"></i>Ventas por Producto
                </h5>
              </div>
              <div class="card-body" style="height: calc(100% - 60px); overflow-y:auto;">
                <canvas id="ventasProductosChart" style="height:100%;"></canvas>
              </div>
            </div>
          </div>

        </div>

        <div class="card border-0 shadow-sm mt-4">
          <div class="card-header border-0 d-flex justify-content-between align-items-center">
            <h5 class="text-warning mb-0">
              <i data-feather="bar-chart-2" class="me-1"></i>Total de Ventas por Mes
            </h5>
          </div>
          <div class="card-body" style="height: 100%; min-height: 350px; position: relative;">
            <canvas id="ventasTotalesChart"></canvas>
          </div>
        </div>

        <!-- ULTIMAS VENTAS -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body ventas-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="text-warning mb-0">
                <i data-feather="list" class="me-1"></i>Últimas Ventas
              </h5>
              <a href="{{ route('admin.lista_ventas') }}" class="btn btn-sm btn-outline-warning fw-bold">
                Ver más <i data-feather="arrow-right" class="ms-1"></i>
              </a>
            </div>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="text-warning border-secondary">
                  <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Método</th>
                    <th>Precio</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody id="ventasTable" class="text-light"></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal para Reportes -->
    <div class="modal fade" id="reportsVentasModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-0 text-white shadow-lg">
          <div class="modal-header border-secondary">
            <h5 class="modal-title text-success">
              <i data-feather="file-text" class="me-1"></i> Generar Reporte de Ventas
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <form id="reportsVentasForm" class="modal-body">
            <div class="alert alert-info bg-opacity-10 border-info text-info">
              <i data-feather="info" class="me-1"></i>
              Selecciona un rango de fechas para filtrar las ventas a incluir en el reporte.
            </div>

            <div class="mb-3">
              <label class="form-label">Fecha inicio</label>
              <input id="reportStartDateVentas" name="inicio" type="date"
                class="form-control text-white border-0" required />
            </div>

            <div class="mb-3">
              <label class="form-label">Fecha fin</label>
              <input id="reportEndDateVentas" name="fin" type="date"
                class="form-control text-white border-0" required />
            </div>

            <div class="modal-footer border-secondary">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="button" id="exportPdfVentasBtn" class="btn btn-danger">
                <i data-feather="file" class="me-1"></i> Exportar PDF
              </button>
              <button type="button" id="exportExcelVentasBtn" class="btn btn-success">
                <i data-feather="download" class="me-1"></i> Exportar Excel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>


<script src="{{ asset('js/contabilidad/resumen-ventas.js') }}"></script>

@endsection