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

            <button id="applyFilter" class="btn btn-sm btn-info ms-md-2 mt-2 mt-md-0">Filtrar</button>
          </div>

          <div class="btn-group ms-md-3 mt-2 mt-md-0" role="group">
            <button id="exportPdf" class="btn btn-sm btn-outline-light">Exportar PDF</button>
            <button id="exportExcel" class="btn btn-sm btn-outline-light">Exportar Excel</button>
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
            <div class="card border-0 shadow-sm">
              <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h5 class="text-warning mb-0">
                  <i data-feather="pie-chart" class="me-1"></i>Ventas por Categoría
                </h5>
              </div>
              <div class="card-body" style="height: 100%; min-height: 350px;">
                <canvas id="ventasCategoriaChart"></canvas>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card border-0 shadow-sm">
              <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h5 class="text-warning mb-0">
                  <i data-feather="trending-up" class="me-1"></i>Ventas por Producto
                </h5>
              </div>
              <div class="card-body" style="height: 100%; min-height: 350px;">
                <canvas id="ventasProductosChart"></canvas>
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
          <div class="card-body" style="height: 100%; min-height: 350px;">
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
  </div>
</div>


<script src="{{ asset('js/contabilidad/resumen-ventas.js') }}"></script>

@endsection
