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

          <!-- FILTROS DE MES Y EXPORTACIÓN -->
          <div class="d-flex flex-wrap flex-md-nowrap gap-2 align-items-center my-4">
            <!-- Bloque de filtros -->
            <div class="d-flex flex-wrap flex-md-nowrap gap-2 align-items-center">
              <small class="text-muted me-2">Filtrar ganancias:</small>

              <select id="filterMes" class="form-control bg-dark form-control-sm text-white border-0">
                <option value="01">Enero</option>
                <option value="02">Febrero</option>
                <option value="03">Marzo</option>
                <option value="04">Abril</option>
                <option value="05">Mayo</option>
                <option value="06">Junio</option>
                <option value="07">Julio</option>
                <option value="08">Agosto</option>
                <option value="09">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
              </select>

              <select id="filterAnio" class="form-control bg-dark form-control-sm text-white border-0">
                <option value="2024">2024</option>
                <option value="2025">2025</option>
                <option value="2026">2026</option>
              </select>

              <button id="applyFilter" class="btn btn-info py-1 ms-md-2 mt-2 mt-md-0 text-nowrap">Limpiar</button>
            </div>

            <!-- Boton de exportación -->
            <button
              type="button"
              class="btn btn-success py-1 ms-md-2 mt-2 mt-md-0"
              data-report-type="membresias">
              <i data-feather="file-text" class="me-1"></i> Reportes
            </button>
          </div>

          <!-- TARJETAS DE RESUMEN GENERAL -->
          <!-- Agregando 4 cards dinámicas para mostrar ingresos -->
          <div class="row g-3 mb-4">
            <!-- Card 1: Ingresos Membresías -->
            <div class="col-12 col-sm-6 col-lg-3">
              <div class="card border-0 shadow-sm bg-dark-card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <small class="text-muted d-block mb-2">Ingresos Membresías</small>
                      <h4 class="text-warning mb-0" id="ingresoMembresias">$0.00</h4>
                    </div>
                    <i data-feather="credit-card" class="text-warning opacity-75"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Card 2: Ingresos Pagos Diarios -->
            <div class="col-12 col-sm-6 col-lg-3">
              <div class="card border-0 shadow-sm bg-dark-card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <small class="text-muted d-block mb-2">Ingresos Pagos Diarios</small>
                      <h4 class="text-info mb-0" id="ingresoDiarios">$0.00</h4>
                    </div>
                    <i data-feather="dollar-sign" class="text-info opacity-75"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Card 3: Total de Membresías -->
            <div class="col-12 col-sm-6 col-lg-3">
              <div class="card border-0 shadow-sm bg-dark-card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <small class="text-muted d-block mb-2">Total de Membresías</small>
                      <h4 class="text-success mb-0" id="cantidadMembresias">0</h4>
                    </div>
                    <i data-feather="users" class="text-success opacity-75"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Card 4: Ingresos Totales -->
            <div class="col-12 col-sm-6 col-lg-3">
              <div class="card border-0 shadow-sm bg-dark-card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <small class="text-muted d-block mb-2">Ingresos Totales</small>
                      <h4 class="text-primary mb-0" id="ingresoTotal">$0.00</h4>
                    </div>
                    <i data-feather="trending-up" class="text-primary opacity-75"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- GRÁFICAS -->
          <div class="row g-3 mb-4 align-items-stretch">
            <!-- Gráfica de Distribución de Ingresos -->
            <div class="col-12 col-lg-6 d-flex">
              <div class="card flex-fill border-0 shadow-sm bg-dark-card">
                <div class="card-body d-flex flex-column justify-content-between">
                  <h5 class="text-warning mb-3">
                    <i data-feather="pie-chart" class="me-2"></i>
                    Distribución de Ingresos
                  </h5>

                  <!-- Contenedor más pequeño solo para este gráfico -->
                  <div class="chart-container-sm grow d-flex justify-content-center align-items-center">
                    <canvas id="chartIngresos"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <!-- Gráfica de Tendencia de Ingresos -->
            <div class="col-12 col-lg-6 d-flex">
              <div class="card flex-fill border-0 shadow-sm bg-dark-card">
                <div class="card-body d-flex flex-column justify-content-between">
                  <h5 class="text-warning mb-3">
                    <i data-feather="trending-up" class="me-2"></i>
                    Tendencia de Ingresos
                  </h5>

                  <!-- Contenedor más alto para este gráfico -->
                  <div class="chart-container-lg grow d-flex justify-content-center align-items-center">
                    <canvas id="chartTendencia"></canvas>
                  </div>
                </div>
              </div>
            </div>
          </div>



          <!-- TABLA DE ÚLTIMOS MOVIMIENTOS -->
          <div class="card border-0 shadow-sm mb-5">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-warning mb-0">
                  <i data-feather="list" class="me-1"></i> Últimos Movimientos Contables
                </h5>
                <input
                  type="text"
                  id="filtroMovimientos"
                  class="form-control form-control-sm w-auto bg-dark text-white border-secondary"
                  placeholder="Buscar..."
                  style="min-width: 200px;" />
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="text-warning border-secondary">
                    <tr>
                      <th>Tipo</th>
                      <th>Cliente / Miembro</th>
                      <th>Registrado por</th>
                      <th>Método de Pago</th>
                      <th>Valor</th>
                      <th>Fecha</th>
                      <th>Estado</th>
                    </tr>
                  </thead>
                  <tbody id="contabilidadTable" class="text-light">
                    <tr>
                      <td colspan="7" class="text-center text-muted">Cargando datos...</td>
                    </tr>
                  </tbody>

                </table>
                <div id="paginacionMovimientos" class="text-center mt-3"></div>
              </div>
            </div>
          </div>
        </div>
        <!-- Modal para Reportes -->
        <div class="modal fade" id="reportsModal" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark border-0 text-white shadow-lg">
              <div class="modal-header border-secondary">
                <h5 class="modal-title text-success"> <i data-feather="file-text" class="me-1"></i> Generar Reporte de Membresías </h5> <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <form id="reportsForm" class="modal-body">
                <div class="alert alert-info bg-opacity-10 border-info text-info">
                  <i data-feather="info" class="me-1"></i> Selecciona un rango de fechas para filtrar por fecha de registro
                </div>
                <div class="mb-3">
                  <label class="form-label">Fecha inicio</label>
                  <input id="reportStartDate" name="fecha_inicio" type="date" class="form-control text-white border-0" required />
                </div>
                <div class="mb-3">
                  <label class="form-label">Fecha fin</label>
                  <input id="reportEndDate" name="fecha_fin" type="date" class="form-control text-white border-0" required />
                </div>
                <div class="mb-3">
                  <label class="form-label">Estado de membresía (opcional)</label>
                  <select id="reportEstado" name="estado" class="form-select text-white border-0">
                    <option value="">Todos los estados</option>
                    <option value="activa">Activas</option>
                    <option value="proxima">Próximas a vencer</option>
                    <option value="vencida">Vencidas</option>
                  </select>
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="includePagosDiarios" />
                  <label class="form-check-label" for="includePagosDiarios"> Incluir pagos diarios en el reporte </label>
                </div>
                <div class="modal-footer border-secondary">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="button" id="exportPdfBtnModal" class="btn btn-danger">
                    <i data-feather="file" class="me-1"> </i> Exportar PDF
                  </button>
                  <button type="button" id="exportExcelBtnModal" class="btn btn-success">
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

  <!-- Agregar librería Chart.js para gráficas -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
  <!-- Usando asset() para cargar el JS -->
  <script src="{{ asset('js/contabilidad/resumen-membresias.js') }}"></script>

</body>
@endsection
