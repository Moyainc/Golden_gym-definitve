@extends('layouts.app')
@section('title', 'Panel - Golden Gym')

@section('content')

<div class="wrapper">
  <div class="main">
    <div id="navbar"></div>

    <main class="content custom-container">
      <div class="container-fluid p-0">
        <h1 class="text-light h1 mb-4">
          <strong>Panel</strong> de Control
        </h1>

        <div class="row">
          <div class="col-xl-6 col-xxl-5 d-flex flex-column justify-content-center">
            <div class="row g-3">
              <!-- CARD PRODUCTOS -->
              <div class="col-sm-6">
                <div class="card border-0 position-relative" id="cardProductos">
                  <div class="card-body">
                    <div class="row">
                      <div class="col mt-0">
                        <h5 class="card-title">Ventas</h5>
                      </div>
                      <div class="col-auto">
                        <span class="icon-bg"><i data-feather="dollar-sign"></i></span>
                      </div>
                    </div>
                    <h1 class="text-success mt-1 mb-3 fw-bold">0</h1>
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <span class="comparison text-danger">
                          <i class="mdi mdi-arrow-bottom-right"></i> 0%
                        </span>
                        <span class="text-secondary">Última</span>
                        <select class="border-0 text-warning fw-semibold bg-dark" id="filterProductos">
                          <option value="week">Semana</option>
                          <option value="month">Mes</option>
                        </select>
                      </div>
                      <a href="ventas.html" class="btn btn-sm btn-outline-warning rounded-pill px-3">Ver más</a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- CARD MEMBRESÍAS -->
              <div class="col-sm-6">
                <div class="card border-0 position-relative" id="cardMembresias">
                  <div class="card-body">
                    <div class="row">
                      <div class="col mt-0">
                        <h5 class="card-title">Membresías</h5>
                      </div>
                      <div class="col-auto">
                        <span class="icon-bg"><i data-feather="users"></i></span>
                      </div>
                    </div>
                    <h1 class="text-success mt-1 mb-3 fw-bold">0</h1>
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <span class="comparison text-success">
                          <i class="mdi mdi-arrow-bottom-right"></i> 0%
                        </span>
                        <span class="text-secondary">Última</span>
                        <select class="bg-dark border-0 text-warning fw-semibold" id="filterMembresias">
                          <option value="week">Semana</option>
                          <option value="month">Mes</option>
                        </select>
                      </div>
                      <a href="membresia.html" class="btn btn-sm btn-outline-warning rounded-pill px-3">Ver más</a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- CARD TOTAL -->
              <div class="col-sm-6">
                <div class="card border-0 position-relative" id="cardTotal">
                  <div class="card-body">
                    <div class="row">
                      <div class="col mt-0">
                        <h5 class="card-title">Total</h5>
                      </div>
                      <div class="col-auto">
                        <span class="icon-bg"><i data-feather="dollar-sign"></i></span>
                      </div>
                    </div>
                    <h1 class="text-success mt-1 mb-3 fw-bold">0</h1>
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <span class="comparison text-success">
                          <i class="mdi mdi-arrow-bottom-right"></i> 0%
                        </span>
                        <span class="text-secondary">Última</span>
                        <select class="border-0 text-warning fw-semibold bg-dark" id="filterTotal">
                          <option value="week">Semana</option>
                          <option value="month">Mes</option>
                        </select>
                      </div>
                      <a href="contabilidad.html" class="btn btn-sm btn-outline-warning rounded-pill px-3">Ver más</a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- CARD NUEVOS CLIENTES -->
              <div class="col-sm-6">
                <div class="card border-0 position-relative" id="cardClientes">
                  <div class="card-body">
                    <div class="row">
                      <div class="col mt-0">
                        <h5 class="card-title">Nuevos Clientes</h5>
                      </div>
                      <div class="col-auto">
                        <span class="icon-bg"><i data-feather="shopping-cart"></i></span>
                      </div>
                    </div>
                    <h1 class="text-success mt-1 mb-3 fw-bold">0</h1>
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <span class="comparison text-danger">
                          <i class="mdi mdi-arrow-bottom-right"></i> 0%
                        </span>
                        <span class="text-secondary">Última</span>
                        <select class="border-0 text-warning fw-semibold bg-dark" id="filterClientes">
                          <option value="week">Semana</option>
                          <option value="month">Mes</option>
                        </select>
                      </div>
                      <a href="clientes.html" class="btn btn-sm btn-outline-warning rounded-pill px-3">Ver más</a>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>


          <!-- GRÁFICOS Y TABLAS DE MOVIMIENTOS -->


          <div class="col-xl-6 col-xxl-7 d-flex align-items-center">
            <div class="card flex-fill w-100 border-0">
              <div class="card-header">
                <h5 class="card-title mb-0">
                  <i data-feather="activity" class="me-1 text-warning"></i>
                  Ingresos totales mensuales
                </h5>
              </div>
              <div class="card-body py-3">
                <div class="chart chart-sm">
                  <canvas id="chartjs-dashboard-line"></canvas>
                </div>
              </div>
            </div>
          </div>

          <div class="row g-3 item align-items-center justify-content-center">
            <!-- GRÁFICO BARRA DE MEMBRESÍAS -->
            <div class="col-xl-6 col-xxl-7 align-items-center">
              <div class="card">
                <div class="card-header">
                  <h5 class="card-title mb-0">
                    <i data-feather="activity" class="me-1 text-warning"></i>
                    Ingresos mensuales por Membresías
                  </h5>
                </div>
                <div class="card-body">
                  <div class="chart-container">
                    <canvas id="chartjs-dashboard-bar"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <!-- CALENDARIO -->
            <div class="col-12 col-md-8 col-xl-4">
              <div class="card border-0 h-auto p-3">
                <div class="card-header">
                  <h5 class="card-title mb-0">Calendario</h5>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center p-3">
                  <div id="datetimepicker-dashboard" class="calendar-wrapper"></div>
                </div>
              </div>
            </div>
          </div>



          <div class="row justify-content-center d-flex mt-3">
            <div class="col-12 col-lg-8 col-xxl-9 flex-fill">
              <div class="card p-4 border-secondary">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom border-secondary">
                  <h5 class="card-title mb-0">
                    <i data-feather="activity" class="me-2 text-warning"></i>
                    Últimos Movimientos
                  </h5>
                  <small class="text-muted">Resumen reciente</small>
                </div>
                <div class="table-responsive">
                  <table class="table table-hover align-middle my-0 text-light" id="tableMovimientos">
                    <thead class="text-warning">
                      <tr>
                        <th>Tipo</th>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Datos se llenan dinámicamente por JS -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
    </main>

  </div>
</div>


<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection
