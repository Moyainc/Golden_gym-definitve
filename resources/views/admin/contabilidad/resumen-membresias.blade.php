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
              <i data-feather="activity" class="me-2 tittle-color"></i> Resumen Contabilidad - Membresías
            </h1>
          </div>

          <!-- FILTROS DE MES Y EXPORTACIÓN -->
          <div class="d-flex flex-wrap flex-md-nowrap gap-2 align-items-center my-4">
            <!-- Bloque de filtros -->
            <div class="d-flex flex-wrap flex-md-nowrap gap-2 align-items-center">
              <small class="text-muted me-2">Filtrar por mes:</small>

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

              <button id="applyFilter" class="btn btn-sm btn-info ms-md-2 mt-2 mt-md-0">
                Aplicar Filtro
              </button>
            </div>

            <!-- Botones de exportación -->
            <div class="btn-group ms-md-3 mt-2 mt-md-0" role="group">
              <button id="exportPdf" class="btn btn-sm btn-outline-warning">
                <i data-feather="file-pdf" class="me-1" width="16"></i> PDF
              </button>
              <button id="exportExcel" class="btn btn-sm btn-outline-warning">
                <i data-feather="file" class="me-1" width="16"></i> Excel
              </button>
            </div>
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
          <div class="row g-3 mb-4">
            <!-- Gráfica de Ingresos por Tipo -->
            <div class="col-12 col-lg-6">
              <div class="card border-0 shadow-sm bg-dark-card">
                <div class="card-body">
                  <h5 class="text-warning mb-3">
                    <i data-feather="pie-chart" class="me-2"></i> Distribución de Ingresos
                  </h5>
                  <canvas id="chartIngresos" height="200"></canvas>
                </div>
              </div>
            </div>

            <!-- Gráfica de Tendencia Diaria -->
            <div class="col-12 col-lg-6">
              <div class="card border-0 shadow-sm bg-dark-card">
                <div class="card-body">
                  <h5 class="text-warning mb-3">
                    <i data-feather="trending-up" class="me-2"></i> Tendencia de Ingresos
                  </h5>
                  <canvas id="chartTendencia" height="200"></canvas>
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
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="text-warning border-secondary">
                    <tr>
                      <th>Tipo</th>
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
      </main>
    </div>
  </div>


  <!-- Usando asset() para cargar el JS -->
  <script src="{{ asset('js/resumen-membresias.js') }}"></script>

</body>
@endsection
