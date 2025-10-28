@extends('layouts.app')
@section('title', 'Contabilidad - Golden Gym')
@section('content')

<body>
  <div class="wrapper">
    <div class="main">
      <main class="content">
        <div class="container-fluid p-0">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 text-white mb-0 fw-bold">
              <i data-feather="bar-chart-2" class="me-2 tittle-color"></i>Contabilidad & Reportes
            </h1>
          </div>
        </div>

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

        <!-- === RESUMEN GENERAL === -->
        <div class="row g-3 mt-2 mb-3">
          <!-- Membresías -->
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted mb-1">Membresías</h6>
                  <h5 class="mb-0 text-white fw-bold" id="membresiasCantidad">0</h5>
                  <small class="text-success" id="membresiasTotal">$0</small>
                </div>
                <div class="bg-info bg-opacity-25 p-2 rounded">
                  <i data-feather="user-check" class="text-info" width="20"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Productos -->
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted mb-1">Productos</h6>
                  <h5 class="mb-0 text-white fw-bold" id="productosCantidad">0</h5>
                  <small class="text-success" id="productosTotal">$0</small>
                </div>
                <div class="bg-warning bg-opacity-25 p-2 rounded">
                  <i data-feather="shopping-bag" class="text-warning" width="20"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagos diarios -->
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted mb-1">Pagos diarios</h6>
                  <h5 class="mb-0 text-white fw-bold" id="pagosDiariosCantidad">0</h5>
                  <small class="text-success" id="pagosDiariosTotal">$0</small>
                </div>
                <div class="bg-primary bg-opacity-25 p-2 rounded">
                  <i data-feather="dollar-sign" class="text-primary" width="20"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Total general -->
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted mb-1">Total general</h6>
                  <h5 class="mb-0 text-white fw-bold" id="totalGeneral">$0</h5>
                  <small class="text-muted">Suma global</small>
                </div>
                <div class="bg-success bg-opacity-25 p-2 rounded">
                  <i data-feather="bar-chart-2" class="text-success" width="20"></i>
                </div>
              </div>
            </div>
          </div>
        </div>



        <div class="row g-3 mt-2">
          <!-- Gráfico de ingresos mensuales -->
          <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-header d-flex justify-content-between align-items-center border-bottom border-secondary py-2">
                <h6 class="mb-0 tittle-color">
                  <i data-feather="bar-chart" class="me-1"></i>Ingresos mensuales
                </h6>
                <small class="text-muted">Comparativa por meses</small>
              </div>
              <div class="card-body p-3">
                <canvas id="monthlyBarChart" height="110"></canvas>
              </div>
              <div class="card-footer border-top border-secondary text-muted small py-2">
                <span id="monthlySummary">Resumen: -</span>
              </div>
            </div>
          </div>

          <!-- Gráfico circular -->
          <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-header d-flex justify-content-between align-items-center border-bottom border-secondary py-2">
                <h6 class="mb-0 tittle-color">
                  <i data-feather="pie-chart" class="me-1"></i>Distribución de ganancias
                </h6>
                <small class="text-muted">% por fuente</small>
              </div>
              <div class="card-body d-flex flex-column align-items-center p-3">
                <canvas id="earningsDoughnut" width="220" height="220"></canvas>
                <div id="earningsLegend" class="mt-3 small text-white"></div>
              </div>
              <div class="card-footer border-top border-secondary text-center text-muted small py-2">
                <span class="text-success fw-bold">↑ 8.5%</span> más ingresos este mes
              </div>
            </div>
          </div>

          <!-- Gráfico de tendencia -->
          <div class="col-12">
            <div class="card border-0 shadow-sm bg-dark-card">
              <div class="card-header d-flex justify-content-between align-items-center border-bottom border-secondary py-2">
                <h6 class="mb-0 tittle-color">
                  <i data-feather="trending-up" class="me-1"></i>Tendencia de ingresos
                </h6>
                <small class="text-muted">Últimos 12 meses</small>
              </div>
              <div class="card-body p-3">
                <canvas id="revenueLineChart" height="80"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Segunda fila -->
        <div class="row g-3 mt-2">
          <!-- Reportes -->
          <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-header border-bottom border-secondary py-2">
                <h6 class="mb-0 tittle-color">
                  <i data-feather="file-text" class="me-1"></i>Generar reportes
                </h6>
              </div>
              <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-2 mb-3">
                  <button id="btnQuickPdf" class="btn btn-sm btn-warning">Reporte PDF (rápido)</button>
                  <button id="btnDetailedPdf" class="btn btn-sm btn-outline-warning">PDF detallado</button>
                  <button id="btnExportXLS" class="btn btn-sm btn-info">Exportar a Excel</button>
                </div>
                <div class="small text-muted">
                  Puedes generar reportes con el rango seleccionado. Para PDF detallado se captura el área visible.
                </div>
              </div>
            </div>
          </div>

          <!-- Últimos registros -->
          <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-header border-bottom border-secondary d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0 tittle-color">
                  <i data-feather="list" class="me-1"></i>Últimos registros
                </h6>
                <button id="btnViewAll" class="btn btn-sm btn-outline-info">Ver todo</button>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-dark table-hover mb-0 small align-middle">
                    <thead class="text-muted">
                      <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th class="text-end">Valor</th>
                        <th class="text-center">Acción</th>
                      </tr>
                    </thead>
                    <tbody id="latestRecords"></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </main>

      <div class="modal fade" id="recordDetailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div
            class="modal-content bg-dark text-white border border-secondary">
            <div class="modal-header">
              <h5 class="modal-title text-info">Detalle del Registro</h5>
              <button
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <p><strong>Fecha:</strong> <span id="detailDate"></span></p>
              <p>
                <strong>Concepto:</strong> <span id="detailConcept"></span>
              </p>
              <p><strong>Valor:</strong> <span id="detailValue"></span></p>
              <p>
                <strong>Método de pago:</strong>
                <span id="detailMethod"></span>
              </p>
              <p><strong>Estado:</strong> <span id="detailStatus"></span></p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">
                Cerrar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="../../js/app.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
@endsection
