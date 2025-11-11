@extends('layouts.app')
@section('title', 'Contabilidad - Golden Gym')
@section('content')

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

      <!-- === FILTROS === -->
      <div class="d-flex flex-wrap flex-md-nowrap gap-2 align-items-center my-4">
        <div class="d-flex flex-wrap flex-md-nowrap gap-2 align-items-center">
          <small class="text-muted me-2">Filtrar ganancias:</small>

          <!-- Filtro de mes -->
          <select id="filterMonth" class="form-select form-select-sm bg-dark text-white border-0">
            <option value="">Mes (todos)</option>
            <option value="1">Enero</option>
            <option value="2">Febrero</option>
            <option value="3">Marzo</option>
            <option value="4">Abril</option>
            <option value="5">Mayo</option>
            <option value="6">Junio</option>
            <option value="7">Julio</option>
            <option value="8">Agosto</option>
            <option value="9">Septiembre</option>
            <option value="10">Octubre</option>
            <option value="11">Noviembre</option>
            <option value="12">Diciembre</option>
          </select>

          <!-- Filtro de año dinámico -->
          <select id="filterYear" class="form-select form-select-sm bg-dark text-white border-0">
            <option value="todos">Año (todos)</option>
            @foreach(App\Models\PagoGeneral::selectRaw('YEAR(fecha_pago) as anio')->distinct()->orderBy('anio', 'asc')->pluck('anio') as $anio)
            <option value="{{ $anio }}">{{ $anio }}</option>
            @endforeach
          </select>

          <button id="applyFilter" class="btn btn-info py-3 ms-md-2 mt-2 mt-md-0 d-inline-flex align-items-center flex-nowrap text-nowrap">
            Aplicar Filtro
          </button>
        </div>

        <button
          type="button"
          class="btn btn-success py-3 ms-md-2 mt-2 mt-md-0 d-inline-flex align-items-center flex-nowrap text-nowrap"
          data-bs-toggle="modal"
          data-bs-target="#reportsModal">
          <i data-feather="file-text" class="me-1"></i> Reportes
        </button>
      </div>

      <!-- === RESUMEN GENERAL === -->
      <div class="row g-3 mb-4">
        <!-- Membresías -->
        <div class="col-md-3 col-sm-6">
          <div class="card border-0 shadow-sm bg-dark-card h-100 text-center">
            <div class="card-body py-3 px-3 d-flex flex-column justify-content-between align-items-center">
              <div class="bg-info bg-opacity-25 p-2 rounded mb-2">
                <i data-feather="user-check" class="text-info" width="22"></i>
              </div>
              <div>
                <h6 class="text-muted text-uppercase small mb-1">Membresías</h6>
                <h5 id="membresiasTotal" class="text-info fw-bold mb-2">$0</h5>
              </div>
              <button class="btn btn-sm btn-outline-secondary btn-eye mt-auto" data-target="membresiasTotal">
                <i data-feather="eye" width="14"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Productos -->
        <div class="col-md-3 col-sm-6">
          <div class="card border-0 shadow-sm bg-dark-card h-100 text-center">
            <div class="card-body py-3 px-3 d-flex flex-column justify-content-between align-items-center">
              <div class="bg-warning bg-opacity-25 p-2 rounded mb-2">
                <i data-feather="shopping-bag" class="text-warning" width="22"></i>
              </div>
              <div>
                <h6 class="text-muted text-uppercase small mb-1">Productos</h6>
                <h5 id="productosTotal" class="text-warning fw-bold mb-2">$0</h5>
              </div>
              <button class="btn btn-sm btn-outline-secondary btn-eye mt-auto" data-target="productosTotal">
                <i data-feather="eye" width="14"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Pagos diarios -->
        <div class="col-md-3 col-sm-6">
          <div class="card border-0 shadow-sm bg-dark-card h-100 text-center">
            <div class="card-body py-3 px-3 d-flex flex-column justify-content-between align-items-center">
              <div class="bg-primary bg-opacity-25 p-2 rounded mb-2">
                <i data-feather="dollar-sign" class="text-primary" width="22"></i>
              </div>
              <div>
                <h6 class="text-muted text-uppercase small mb-1">Pagos diarios</h6>
                <h5 id="pagosDiariosTotal" class="text-primary fw-bold mb-2">$0</h5>
              </div>
              <button class="btn btn-sm btn-outline-secondary btn-eye mt-auto" data-target="pagosDiariosTotal">
                <i data-feather="eye" width="14"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Total general -->
        <div class="col-md-3 col-sm-6">
          <div class="card border-0 shadow-sm bg-dark-card h-100 text-center">
            <div class="card-body py-3 px-3 d-flex flex-column justify-content-between align-items-center">
              <div class="bg-success bg-opacity-25 p-2 rounded mb-2">
                <i data-feather="bar-chart-2" class="text-success" width="22"></i>
              </div>
              <div>
                <h6 class="text-muted text-uppercase small mb-1">Total General</h6>
                <h5 id="totalGeneral" class="text-success fw-bold mb-2">$0</h5>
              </div>
              <button class="btn btn-sm btn-outline-secondary btn-eye mt-auto" data-target="totalGeneral">
                <i data-feather="eye" width="14"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- === GRÁFICOS === -->
        <div class="row g-3 mt-2">
          <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-header d-flex justify-content-between align-items-center border-bottom border-secondary py-2">
                <h6 class="mb-0 tittle-color"><i data-feather="bar-chart" class="me-1"></i>Ingresos mensuales</h6>
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

          <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-header d-flex justify-content-between align-items-center border-bottom border-secondary py-2">
                <h6 class="mb-0 tittle-color"><i data-feather="pie-chart" class="me-1"></i>Distribución de ganancias</h6>
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

          <div class="col-12">
            <div class="card border-0 shadow-sm bg-dark-card">
              <div class="card-header d-flex justify-content-between align-items-center border-bottom border-secondary py-2">
                <h6 class="mb-0 tittle-color"><i data-feather="trending-up" class="me-1"></i>Tendencia de ingresos</h6>
                <small class="text-muted">Últimos 12 meses</small>
              </div>
              <div class="card-body p-3">
                <canvas id="revenueLineChart" height="80"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- === REPORTES Y TABLA === -->
        <div class="row g-3 mt-2">
          <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-header border-bottom border-secondary py-2">
                <h6 class="mb-0 tittle-color"><i data-feather="file-text" class="me-1"></i>Generar reportes</h6>
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

          <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm bg-dark-card h-100">
              <div class="card-header border-bottom border-secondary d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0 tittle-color"><i data-feather="list" class="me-1"></i>Últimos registros</h6>
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

        <!-- Modal detalle -->
        <div class="modal fade" id="recordDetailModal" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border border-secondary">
              <div class="modal-header">
                <h5 class="modal-title text-info">Detalle del Registro</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <p><strong>Fecha:</strong> <span id="detailDate"></span></p>
                <p><strong>Concepto:</strong> <span id="detailConcept"></span></p>
                <p><strong>Valor:</strong> <span id="detailValue"></span></p>
                <p><strong>Método de pago:</strong> <span id="detailMethod"></span></p>
                <p><strong>Estado:</strong> <span id="detailStatus"></span></p>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Modal para Reportes -->
        <div class="modal fade" id="reportsModal" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark border-0 text-white shadow-lg">
              <div class="modal-header border-secondary">
                <h5 class="modal-title text-success">
                  <i data-feather="file-text" class="me-1"></i> Generar Reporte Financiero General
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>

              <form id="reportsForm" class="modal-body">
                <div class="alert alert-info bg-opacity-10 border-info text-info">
                  <i data-feather="info" class="me-1"></i>
                  Selecciona un rango de fechas para generar el reporte financiero general.
                </div>

                <div class="mb-3">
                  <label class="form-label">Fecha inicio</label>
                  <input
                    id="reportStartDate"
                    name="fecha_inicio"
                    type="date"
                    class="form-control text-white border-0"
                     />
                </div>

                <div class="mb-3">
                  <label class="form-label">Fecha fin</label>
                  <input
                    id="reportEndDate"
                    name="fecha_fin"
                    type="date"
                    class="form-control text-white border-0"
                     />
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
    </main>
  </div>
</div>

<script src="{{ asset('js/contabilidad/resumen-general.js') }}"></script>

@endsection