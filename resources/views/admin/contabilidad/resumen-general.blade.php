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
            <option value="">Todos los meses</option>
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
            <option value="todos">Todos los años</option>
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
            <div class="card border-0 shadow-sm bg-dark-card h-100" style="height: 350px;">
              <div class="card-header d-flex justify-content-between align-items-center border-bottom border-secondary py-2">
                <h6 class="mb-0 tittle-color"><i data-feather="bar-chart" class="me-1"></i>Ingresos mensuales</h6>
                <small class="text-muted">Comparativa por meses</small>
              </div>
              <div class="card-body p-3 d-flex align-items-center justify-content-center">
                <div class="chart-container w-100 h-100">
                  <canvas id="monthlyBarChart"></canvas>
                </div>
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
                <canvas id="earningsDoughnut"></canvas>
                <div id="earningsLegend" class="mt-3 small text-white"></div>
              </div>
            </div>
          </div>
        </div>


        <div class="row g-3 mt-2">
          <!-- Tendencia de ingresos -->
          <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm bg-dark-card" style="height: 350px;">
              <div class="card-header d-flex justify-content-between align-items-center border-bottom border-secondary py-2">
                <h6 class="mb-0 tittle-color">
                  <i data-feather="users" class="me-1"></i> Clientes activos vs nuevos
                </h6>
                <small class="text-muted">Últimos 12 meses</small>
              </div>
              <div class="card-body p-3 d-flex align-items-center justify-content-center">
                <div class="chart-container w-100 h-100">
                  <canvas id="clientsChart"></canvas>
                </div>
              </div>
            </div>
          </div>

          <!-- Últimos registros -->
          <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm bg-dark-card" style="height: 350px;">
              <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0 tittle-color">
                  <i data-feather="list" class="me-1 my-2"></i> Últimos registros
                </h6>
              </div>

              <div class="card-body p-0 d-flex flex-column">
                <div class="table-responsive grow p-2" style="overflow-y: auto;">
                  <table class="table table-hover small align-middle border-0 mb-0">
                    <thead class="text-muted border-bottom border-secondary">
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
        <div class="modal fade modal-dark-style mt-0 py-2" id="recordDetailModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-border-custom">
            <div class="modal-content border-0 shadow-lg" id="recordDetailModalContent">

              <!-- Header -->
              <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-warning">
                  <i data-feather="info" class="me-2"></i> Detalle del Registro
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>

              <!-- Línea divisoria -->
              <hr class="border-warning opacity-25 mx-4 mb-0 mt-2" />

              <!-- Cuerpo -->
              <div id="recordDetailModalBody" class="modal-body py-4 px-4"
                style="max-height: 60vh; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #fcd52b #1a1a1a;">
                <div class="row g-3">
                  <div class="col-12">
                    <p><strong>Fecha:</strong> <span id="detailDate" class="text-light"></span></p>
                    <p><strong>Concepto:</strong> <span id="detailConcept" class="text-light"></span></p>
                    <p><strong>Valor:</strong> <span id="detailValue" class="text-light"></span></p>
                    <p><strong>Método de pago:</strong> <span id="detailMethod" class="text-light"></span></p>
                    <p><strong>Estado:</strong> <span id="detailStatus" class="text-light"></span></p>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div class="modal-footer border-0 pt-0 d-flex justify-content-end">
                <button type="button" class="btn fw-bold px-4"
                  style="background-color: #fcd52b; color: #000; border-radius: 10px; font-weight: 600;"
                  data-bs-dismiss="modal">
                  Cerrar
                </button>
              </div>
            </div>
          </div>
        </div>
<<<<<<< HEAD
        
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
=======

>>>>>>> b3e1283f668a73b90d2bc9b71ed2f7e0453cd2cc
    </main>
  </div>
</div>

<script src="{{ asset('js/contabilidad/resumen-general.js') }}"></script>

@endsection