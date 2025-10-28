@extends('layouts.app')
@section('title', 'Contabilidad - Golden Gym')
@section('content')

<body>
  <div class="wrapper">
    <div class="main">
      <div id="navbar"></div>

      <main class="content">
        <div class="container-fluid p-0">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-white fw-bold mb-0">
              <i data-feather="activity" class="me-2 tittle-color"></i> Resumen Membresías
            </h1>
          </div>

          <!-- FILTROS DE FECHA Y EXPORTACIÓN -->
          <div class="d-flex flex-wrap flex-md-nowrap gap-2 align-items-center my-4">
            <!-- Bloque de filtros -->
            <div class="d-flex flex-wrap flex-md-nowrap gap-2 align-items-center">
              <small class="text-muted me-2">Filtrar ganancias:</small>

              <input
                id="filterStart"
                type="date"
                class="form-control bg-dark form-control-sm text-white border-0" />

              <span class="text-muted mx-1">—</span>

              <input
                id="filterEnd"
                type="date"
                class="form-control bg-dark form-control-sm text-white border-0" />

              <button id="applyFilter" class="btn btn-sm btn-info ms-md-2 mt-2 mt-md-0">
                Aplicar
              </button>
            </div>

            <!-- Botones de exportación -->
            <div class="btn-group ms-md-3 mt-2 mt-md-0" role="group">
              <button id="exportPdf" class="btn btn-sm btn-outline-light">
                Exportar PDF
              </button>
              <button id="exportExcel" class="btn btn-sm btn-outline-light">
                Exportar Excel
              </button>
            </div>
          </div>


          <!-- TARJETAS DE RESUMEN GENERAL -->
          <!-- === TOTALES FINANCIEROS === -->
          <div class="row g-3 mb-4">
            <!-- Ingresos Totales -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm bg-dark-card h-100">
                <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="text-muted mb-1">Ingresos Totales</h6>
                    <h5 id="totalIngresos" class="mb-0 text-warning fw-bold">$0</h5>
                  </div>
                  <div class="bg-warning bg-opacity-25 p-2 rounded">
                    <i data-feather="trending-up" class="text-warning" width="20"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Total Ventas -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm bg-dark-card h-100">
                <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="text-muted mb-1">Total Ventas</h6>
                    <h5 id="totalVentas" class="mb-0 text-warning fw-bold">$0</h5>
                  </div>
                  <div class="bg-warning bg-opacity-25 p-2 rounded">
                    <i data-feather="shopping-cart" class="text-warning" width="20"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Total Membresías -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm bg-dark-card h-100">
                <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="text-muted mb-1">Total Membresías</h6>
                    <h5 id="totalMembresias" class="mb-0 text-warning fw-bold">$0</h5>
                  </div>
                  <div class="bg-warning bg-opacity-25 p-2 rounded">
                    <i data-feather="credit-card" class="text-warning" width="20"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Ganancia Neta -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm bg-dark-card h-100">
                <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="text-muted mb-1">Ganancia Neta</h6>
                    <h5 id="gananciaNeta" class="mb-0 text-warning fw-bold">$0</h5>
                  </div>
                  <div class="bg-success bg-opacity-25 p-2 rounded">
                    <i data-feather="dollar-sign" class="text-success" width="20"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>


          <!-- GRÁFICOS -->
          <div class="row g-4">
            <div class="col-md-6">
              <div class="card border-0 shadow-sm">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                  <h5 class="text-warning mb-0">
                    <i data-feather="bar-chart-2" class="me-1"></i> Ingresos por Mes
                  </h5>
                </div>
                <div class="card-body" style="height: 350px">
                  <canvas id="ingresosPorMesChart"></canvas>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card border-0 shadow-sm">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                  <h5 class="text-warning mb-0">
                    <i data-feather="pie-chart" class="me-1"></i> Distribución de Ingresos
                  </h5>
                </div>
                <div class="card-body" style="height: 350px">
                  <canvas id="distribucionIngresosChart"></canvas>
                </div>
              </div>
            </div>
          </div>

          <!-- TABLA DE DETALLES -->
          <div class="card border-0 shadow-sm mt-4 mb-5">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-warning mb-0">
                  <i data-feather="list" class="me-1"></i> Últimos Movimientos Contables
                </h5>
                <a href="{{ route('admin.resumen.general') }}" class="btn btn-sm btn-outline-warning fw-bold">
                  Ver más <i data-feather="arrow-right" class="ms-1"></i>
                </a>
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="text-warning border-secondary">
                    <tr>
                      <th>ID</th>
                      <th>Tipo</th>
                      <th>Detalle</th>
                      <th>Método de Pago</th>
                      <th>Valor</th>
                      <th>Fecha</th>
                      <th>Estado</th>
                    </tr>
                  </thead>
                  <tbody id="contabilidadTable" class="text-light">
                    <tr>
                      <td>#001</td>
                      <td>Membresía</td>
                      <td>Juan Pérez - Mensual</td>
                      <td>Efectivo</td>
                      <td>$80.000</td>
                      <td>2025-10-01</td>
                      <td><span class="badge bg-success text-black">Completado</span></td>
                    </tr>
                    <tr>
                      <td>#002</td>
                      <td>Venta</td>
                      <td>Proteína Whey</td>
                      <td>Transferencia</td>
                      <td>$150.000</td>
                      <td>2025-10-05</td>
                      <td><span class="badge bg-warning text-black">Pendiente</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="../../js/app.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
@endsection
