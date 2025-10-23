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
                <i data-feather="bar-chart-2" class="me-2 tittle-color"></i
                >Contabilidad & Reportes
              </h1>

              <div class="d-flex gap-2 align-items-center">
                <div class="d-flex gap-1 align-items-center">
                  <small class="text-muted me-2">Filtrar ganancias:</small>
                  <input
                    id="filterStart"
                    type="date"
                    class="form-control bg-dark form-control-sm text-white border-0"
                  />
                  <span class="text-muted mx-1">—</span>
                  <input
                    id="filterEnd"
                    type="date"
                    class="form-control bg-dark form-control-sm text-white border-0"
                  />
                  <button id="applyFilter" class="btn btn-sm btn-info ms-2">
                    Aplicar
                  </button>
                </div>

                <div class="btn-group ms-3" role="group">
                  <button id="exportPdf" class="btn btn-sm btn-outline-light">
                    Exportar PDF
                  </button>
                  <button id="exportExcel" class="btn btn-sm btn-outline-light">
                    Exportar Excel
                  </button>
                </div>
              </div>
            </div>

            <div class="row g-3 mb-3" id="reportArea">
              <div class="card border-0 shadow-sm bg-dark-card mb-2">
                <div
                  class="card-header d-flex justify-content-between align-items-center p-2"
                  data-bs-toggle="collapse"
                  data-bs-target="#membershipDetails"
                  style="cursor: pointer"
                >
                  <div class="d-flex align-items-center gap-2">
                    <i data-feather="users" class="feather-lg tittle-color"></i>
                    <span class="tittle-color fw-bold fs-6"
                      >Total Membresías</span
                    >
                  </div>
                  <div id="totalMemberships" class="h5 mb-0 text-white">$0</div>
                </div>

                <div id="membershipDetails" class="collapse">
                  <div class="card-body p-2">
                    <div class="d-flex gap-1 mb-2 flex-wrap align-items-center">
                      <input
                        type="date"
                        id="membershipStart"
                        class="form-control form-control-sm bg-dark text-white border-0"
                        style="width: 130px"
                      />
                      <input
                        type="date"
                        id="membershipEnd"
                        class="form-control form-control-sm bg-dark text-white border-0"
                        style="width: 130px"
                      />
                      <select
                        id="membershipType"
                        class="form-select form-select-sm bg-dark text-white border-0"
                        style="width: 120px"
                      >
                        <option value="all" selected>Todos</option>
                        <option value="mensual">Mensual</option>
                        <option value="anual">Anual</option>
                      </select>
                      <button
                        class="btn btn-sm btn-warning"
                        id="applyMembershipFilter"
                        style="height: calc(1.5em + 0.5rem + 2px)"
                      >
                        Aplicar
                      </button>
                    </div>

                    <div class="row g-2 mb-2">
                      <div class="col-12 col-md-4">
                        <div
                          class="card bg-gradient-dark shadow-sm text-center p-1"
                        >
                          <div class="small text-muted">Membresías Totales</div>
                          <div
                            class="h6 text-warning"
                            id="totalMembershipsNumber"
                          >
                            0 → $0
                          </div>
                          <small class="text-success">↑ 12% ayer</small>
                        </div>
                      </div>
                      <div class="col-12 col-md-4">
                        <div
                          class="card bg-gradient-dark shadow-sm text-center p-1"
                        >
                          <div class="small text-muted">Pagos Diarios</div>
                          <div
                            class="h6 text-info"
                            id="totalDailyPaymentsNumber"
                          >
                            0 → $0
                          </div>
                          <small class="text-danger">↓ 5% ayer</small>
                        </div>
                      </div>
                      <div class="col-12 col-md-4">
                        <div
                          class="card bg-gradient-dark shadow-sm text-center p-1"
                        >
                          <div class="small text-muted">Total General</div>
                          <div
                            class="h6 text-success"
                            id="totalMembershipsCombined"
                          >
                            0 → $0
                          </div>
                          <small class="text-white">Acumulado</small>
                        </div>
                      </div>
                    </div>

                    <div class="row g-2 mb-2">
                      <div class="col-12 col-md-6">
                        <div class="card border-0 bg-dark-card shadow-sm p-2">
                          <div class="small text-muted mb-1">
                            Membresías Diarias
                          </div>
                          <canvas
                            id="dailyMembershipChart"
                            height="100"
                          ></canvas>
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="card border-0 bg-dark-card shadow-sm p-2">
                          <div class="small text-muted mb-1">Pagos Diarios</div>
                          <canvas id="dailyPaymentChart" height="100"></canvas>
                        </div>
                      </div>
                    </div>

                    <div
                      class="d-flex justify-content-between align-items-center mt-2 flex-wrap small text-muted"
                    >
                      <span
                        >Última actualización:
                        <span id="lastUpdateMembership">-</span></span
                      >
                    </div>
                  </div>
                </div>
              </div>

              <div class="card border-0 shadow-sm bg-dark-card mb-2">
                <div
                  class="card-header d-flex justify-content-between align-items-center p-2"
                  data-bs-toggle="collapse"
                  data-bs-target="#salesDetails"
                  style="cursor: pointer"
                >
                  <div class="d-flex align-items-center gap-2">
                    <i
                      data-feather="shopping-cart"
                      class="feather-lg tittle-color"
                    ></i>
                    <span class="tittle-color fw-bold fs-6">Total Ventas</span>
                  </div>
                  <div id="totalProducts" class="h5 mb-0 text-white">$0</div>
                </div>

                <div id="salesDetails" class="collapse">
                  <div class="card-body p-2">
                    <div class="d-flex gap-1 mb-2 flex-wrap align-items-center">
                      <input
                        type="date"
                        id="salesStart"
                        class="form-control form-control-sm bg-dark text-white border-0"
                        style="width: 130px"
                      />
                      <input
                        type="date"
                        id="salesEnd"
                        class="form-control form-control-sm bg-dark text-white border-0"
                        style="width: 130px"
                      />
                      <select
                        id="salesType"
                        class="form-select form-select-sm bg-dark text-white border-0"
                        style="width: 120px"
                      >
                        <option value="all" selected>Todos</option>
                        <option value="producto">Producto</option>
                        <option value="servicio">Servicio</option>
                      </select>
                      <button
                        class="btn btn-sm btn-warning"
                        id="applySalesFilter"
                        style="height: calc(1.5em + 0.5rem + 2px)"
                      >
                        Aplicar
                      </button>
                    </div>

                    <div class="row g-2 mb-2">
                      <div class="col-12 col-md-4">
                        <div
                          class="card bg-gradient-dark shadow-sm text-center p-1"
                        >
                          <div class="small text-muted">Ventas Diarias</div>
                          <div
                            class="h6 text-warning"
                            id="totalDailySalesNumber"
                          >
                            0 → $0
                          </div>
                          <small class="text-success">↑ 8% ayer</small>
                        </div>
                      </div>
                      <div class="col-12 col-md-4">
                        <div
                          class="card bg-gradient-dark shadow-sm text-center p-1"
                        >
                          <div class="small text-muted">Total Productos</div>
                          <div class="h6 text-info" id="totalProductsNumber">
                            0 → $0
                          </div>
                          <small class="text-muted">Hoy</small>
                        </div>
                      </div>
                      <div class="col-12 col-md-4">
                        <div
                          class="card bg-gradient-dark shadow-sm text-center p-1"
                        >
                          <div class="small text-muted">Total General</div>
                          <div
                            class="h6 text-success"
                            id="totalSalesCombinedNumber"
                          >
                            0 → $0
                          </div>
                          <small class="text-white">Acumulado</small>
                        </div>
                      </div>
                    </div>

                    <div class="row g-2 mb-2">
                      <div class="col-12 col-md-6">
                        <div class="card border-0 bg-dark-card shadow-sm p-2">
                          <div class="small text-muted mb-1">
                            Ventas Diarias
                          </div>
                          <canvas id="dailySalesChart" height="100"></canvas>
                        </div>
                      </div>
                      <div class="col-12 col-md-6">
                        <div class="card border-0 bg-dark-card shadow-sm p-2">
                          <div class="small text-muted mb-1">
                            Ingresos por Producto/Servicio
                          </div>
                          <canvas id="productSalesChart" height="100"></canvas>
                        </div>
                      </div>
                    </div>

                    <div
                      class="d-flex justify-content-between align-items-center mt-2 flex-wrap small text-muted"
                    >
                      <span
                        >Última actualización:
                        <span id="lastUpdateSales">-</span></span
                      >
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-3">
                <a href="/membresias" class="text-decoration-none">
                  <div
                    class="card border-0 shadow-sm h-100 bg-dark-card cursor-pointer hover-scale"
                  >
                    <div class="card-body d-flex align-items-center gap-3">
                      <div class="icon-box">
                        <i
                          data-feather="users"
                          class="feather-lg tittle-color"
                        ></i>
                      </div>
                      <div class="flex-grow-1">
                        <div class="small text-muted">Total Membresías</div>
                        <div id="totalMemberships" class="h4 mb-0 text-white">
                          0
                        </div>
                      </div>
                      <div
                        id="membershipDelta"
                        class="text-end small text-success"
                      >
                      </div>
                    </div>
                  </div>
                </a>
              </div>

              <div class="col-12 col-md-3">
                <a href="/productos" class="text-decoration-none">
                  <div
                    class="card border-0 shadow-sm h-100 bg-dark-card cursor-pointer hover-scale"
                  >
                    <div class="card-body d-flex align-items-center gap-3">
                      <div class="icon-box">
                        <i
                          data-feather="shopping-bag"
                          class="feather-lg tittle-color"
                        ></i>
                      </div>
                      <div class="flex-grow-1">
                        <div class="small text-muted">Total Productos</div>
                        <div id="totalProducts" class="h4 mb-0 text-white">
                          0
                        </div>
                      </div>
                      <div
                        id="productsDelta"
                        class="text-end small text-danger"
                      ></div>
                    </div>
                  </div>
                </a>
              </div>

              <div class="col-12 col-md-3">
                <a href="/pagos" class="text-decoration-none">
                  <div
                    class="card border-0 shadow-sm h-100 bg-dark-card cursor-pointer hover-scale"
                  >
                    <div class="card-body d-flex align-items-center gap-3">
                      <div class="icon-box">
                        <i
                          data-feather="credit-card"
                          class="feather-lg text-info"
                        ></i>
                      </div>
                      <div class="flex-grow-1">
                        <div class="small text-muted">Total Pagos Diarios</div>
                        <div id="totalDailyPayments" class="h4 mb-0 text-white">
                          $0
                        </div>
                      </div>
                      <div
                        id="dailyDelta"
                        class="text-end small text-success"
                      ></div>
                    </div>
                  </div>
                </a>
              </div>

              <div class="col-12 col-md-3">
                <div class="card border-0 shadow-sm h-100 bg-dark-card">
                  <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box">
                      <i
                        data-feather="dollar-sign"
                        class="feather-lg tittle-color"
                      ></i>
                    </div>
                    <div class="flex-grow-1">
                      <div class="small text-muted">
                        Total ingresos (mes seleccionado)
                      </div>
                      <div id="totalRevenue" class="h4 mb-0 text-white">$0</div>
                    </div>
                    <div
                      id="revenueDelta"
                      class="text-end small text-success"
                    ></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row g-3">
              <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm bg-dark-card">
                  <div
                    class="card-header d-flex justify-content-between align-items-center border-bottom border-secondary"
                  >
                    <h6 class="mb-0 tittle-color">
                      <i data-feather="bar-chart" class="me-1"></i>Ingresos
                      mensuales
                    </h6>
                    <small class="text-muted">Comparativa por meses</small>
                  </div>
                  <div class="card-body">
                    <canvas id="monthlyBarChart" height="120"></canvas>
                  </div>
                  <div
                    class="card-footer border-top border-secondary text-muted small"
                  >
                    <span id="monthlySummary">Resumen: -</span>
                  </div>
                </div>
              </div>

              <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm bg-dark-card">
                  <div
                    class="card-header d-flex justify-content-between align-items-center border-bottom border-secondary"
                  >
                    <h6 class="mb-0 tittle-color">
                      <i data-feather="pie-chart" class="me-1"></i>Distribución
                      de ganancias
                    </h6>
                    <small class="text-muted">% por fuente</small>
                  </div>

                  <div class="card-body d-flex flex-column align-items-center">
                    <canvas
                      id="earningsDoughnut"
                      width="220"
                      height="220"
                    ></canvas>

                    <div
                      id="earningsLegend"
                      class="mt-3 small text-white"
                    ></div>
                  </div>

                  <div
                    class="card-footer border-top border-secondary text-center text-muted small"
                  >
                    <span class="text-success fw-bold">↑ 8.5%</span> más
                    ingresos este mes
                  </div>
                </div>
              </div>

              <div class="col-12">
                <div class="card border-0 shadow-sm bg-dark-card">
                  <div
                    class="card-header d-flex justify-content-between align-items-center border-bottom border-secondary"
                  >
                    <h6 class="mb-0 tittle-color">
                      <i data-feather="trending-up" class="me-1"></i>Tendencia
                      ingresos
                    </h6>
                    <small class="text-muted">Últimos 12 meses</small>
                  </div>
                  <div class="card-body">
                    <canvas id="revenueLineChart" height="80"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <div class="row g-3 mt-3">
              <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm bg-dark-card">
                  <div class="card-header border-bottom border-secondary">
                    <h6 class="mb-0 tittle-color">
                      <i data-feather="file-text" class="me-1"></i>Generar
                      reportes
                    </h6>
                  </div>
                  <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap mb-2">
                      <button id="btnQuickPdf" class="btn btn-sm btn-warning">
                        Reporte PDF (rápido)
                      </button>
                      <button
                        id="btnDetailedPdf"
                        class="btn btn-sm btn-outline-warning"
                      >
                        PDF detallado
                      </button>
                      <button id="btnExportXLS" class="btn btn-sm btn-info">
                        Exportar a Excel
                      </button>
                    </div>
                    <div class="small text-muted">
                      Puedes generar reportes con el rango seleccionado. Para
                      PDF detallado se captura el área visible.
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm bg-dark-card">
                  <div
                    class="card-header border-bottom border-secondary d-flex justify-content-between align-items-center"
                  >
                    <h6 class="mb-0 tittle-color">
                      <i data-feather="list" class="me-1"></i>Últimos registros
                    </h6>
                    <button id="btnViewAll" class="btn btn-sm btn-outline-info">
                      Ver todo
                    </button>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table
                        class="table table-dark table-hover mb-0 small align-middle"
                      >
                        <thead class="text-muted">
                          <tr>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th class="text-end">Valor</th>
                            <th class="text-center">Acción</th>
                          </tr>
                        </thead>
                        <tbody id="latestRecords">
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>

        <div class="modal fade" id="recordDetailModal" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div
              class="modal-content bg-dark text-white border border-secondary"
            >
              <div class="modal-header">
                <h5 class="modal-title text-info">Detalle del Registro</h5>
                <button
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal"
                ></button>
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
    <script src="../js/app.js"></script>
  </body>
@endsection
