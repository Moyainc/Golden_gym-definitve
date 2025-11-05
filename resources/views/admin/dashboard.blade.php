@extends('layouts.app')
@section('title', 'Panel - Golden Gym')

@section('content')

<div class="wrapper">
  <div class="main">
    <div id="navbar"></div>

    <main class="content">
      <div class="container-fluid p-0">
        <h1 class="text-light h1 mb-4">
          <strong>Panel</strong> de Control
        </h1>

        <div class="row">
          <div class="col-xl-6 col-xxl-5 d-flex">
            <div class="w-100">
              <div class="row">
                <div class="col-sm-6">
                  <div class="card border-0 position-relative">
                    <div class="card-body">
                      <div class="row">
                        <div class="col mt-0">
                          <h5 class="card-title">Productos</h5>
                        </div>
                        <div class="col-auto">
                          <span class="icon-bg"><i data-feather="truck"></i></span>
                        </div>
                      </div>

                      <h1 class="text-success mt-1 mb-3 fw-bold">2.382</h1>

                      <div
                        class="d-flex justify-content-between align-items-center">
                        <div>
                          <span class="text-danger">
                            <i class="mdi mdi-arrow-bottom-right"></i>
                            -3.65%
                          </span>
                          <span class="text-secondary">Última</span>
                          <select
                            class="border-0 text-warning fw-semibold bg-dark"
                            id="filterProductos">
                            <option>Semana</option>
                            <option>Mes</option>
                          </select>
                        </div>

                        <a
                          href="productos.html"
                          class="btn btn-sm btn-outline-warning rounded-pill px-3">
                          Ver más
                        </a>
                      </div>
                    </div>
                  </div>

                  <div class="card border-0 mt-3 position-relative">
                    <div class="card-body">
                      <div class="row">
                        <div class="col mt-0">
                          <h5 class="card-title">Membresías</h5>
                        </div>
                        <div class="col-auto">
                          <span class="icon-bg"><i data-feather="users"></i></span>
                        </div>
                      </div>

                      <h1 class="text-success mt-1 mb-3 fw-bold">14.212</h1>

                      <div
                        class="d-flex justify-content-between align-items-center">
                        <div>
                          <span class="text-success">
                            <i class="mdi mdi-arrow-bottom-right"></i> 5.25%
                          </span>
                          <span class="text-secondary">Última</span>
                          <select
                            class="bg-dark border-0 text-warning fw-semibold"
                            id="filterMembresias">
                            <option>Semana</option>
                            <option>Mes</option>
                          </select>
                        </div>

                        <a
                          href="membresia.html"
                          class="btn btn-sm btn-outline-warning rounded-pill px-3">
                          Ver más
                        </a>
                      </div>
                    </div>
                  </div>
                </div>


                <div class="col-sm-6">
                  <!-- TOTAL -->
                  <div class="card border-0 position-relative">
                    <div class="card-body">
                      <div class="row">
                        <div class="col mt-0">
                          <h5 class="card-title">Total</h5>
                        </div>
                        <div class="col-auto">
                          <span class="icon-bg"><i data-feather="dollar-sign"></i></span>
                        </div>
                      </div>

                      <h1 class="text-success mt-1 mb-3 fw-bold">
                        $21.300
                      </h1>

                      <div
                        class="d-flex justify-content-between align-items-center">
                        <div>
                          <span class="text-success">
                            <i class="mdi mdi-arrow-bottom-right"></i> 6.65%
                          </span>
                          <span class="text-secondary">Última</span>
                          <select
                            class="border-0 text-warning fw-semibold bg-dark"
                            id="filterTotal">
                            <option>Semana</option>
                            <option>Mes</option>
                          </select>
                        </div>

                        <a
                          href="contabilidad.html"
                          class="btn btn-sm btn-outline-warning rounded-pill px-3">
                          Ver más
                        </a>
                      </div>
                    </div>
                  </div>

                  <!-- NUEVOS CLIENTES -->
                  <div class="card border-0 mt-3">
                    <div class="card-body">
                      <div class="row">
                        <div class="col mt-0">
                          <h5 class="card-title">Nuevos Clientes</h5>
                        </div>
                        <div class="col-auto">
                          <span class="icon-bg"><i data-feather="shopping-cart"></i></span>
                        </div>
                      </div>

                      <h1 class="text-success mt-1 mb-3 fw-bold">64</h1>

                      <div
                        class="d-flex justify-content-between align-items-center">
                        <div>
                          <span class="text-danger">
                            <i class="mdi mdi-arrow-bottom-right"></i>
                            -2.25%
                          </span>
                          <span class="text-secondary">Última</span>
                          <select
                            class="border-0 text-warning fw-semibold bg-dark"
                            id="filterClientes">
                            <option>Semana</option>
                            <option>Mes</option>
                          </select>
                        </div>

                        <a
                          href="clientes.html"
                          class="btn btn-sm btn-outline-warning rounded-pill px-3">
                          Ver más
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- GRAFICO A LA DERECHA -->
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
        </div>

        <div class="row justify-content-center gap-2">
          <div class="col-12 col-md-6 col-xxl-3 d-flex order-1 order-xxl-1">
            <div class="card flex-fill">
              <div class="card-header">
                <h5 class="card-title mb-0">Calendario</h5>
              </div>
              <div class="card-body d-flex">
                <div class="align-self-center w-100">
                  <div class="chart">
                    <div id="datetimepicker-dashboard"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-8 col-xxl-8 d-flex">
            <div class="card flex-fill w-100">
              <div class="card-header">
                <h5 class="card-title mb-0">
                  <i data-feather="activity" class="me-1 text-warning"></i>
                  Ingresos mensuales por Membresías
                </h5>
              </div>
              <div class="card-body d-flex w-200">
                <div class="align-self-center chart chart-lg">
                  <canvas id="chartjs-dashboard-bar"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row justify-content-center d-flex">
          <div class="col-12 col-lg-8 col-xxl-9 flex-fill">
            <div class="card p-4 border-secondary">
              <div
                class="card-header d-flex justify-content-between align-items-center border-bottom border-secondary">
                <h5 class="card-title mb-0">
                  <i data-feather="activity" class="me-2 text-warning"></i>
                  Últimos Movimientos
                </h5>
                <small class="text-muted">Resumen reciente</small>
              </div>

              <table class="table table-hover align-middle my-0 text-light">
                <thead class="text-warning">
                  <tr>
                    <th>Tipo</th>
                    <th>Cliente</th>
                    <th>Detalle</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                    <th class="text-center">Estado</th>
                  </tr>
                </thead>

                <tbody>
                  <tr>
                    <td>
                      <i
                        data-feather="user-check"
                        class="me-2 text-success"></i>
                      Membresía
                    </td>
                    <td>Andrés Gómez</td>
                    <td>
                      <span class="badge bg-secondary">Mensual</span>
                    </td>
                    <td>$120.000</td>
                    <td>12 Oct 2025</td>
                    <td class="text-center">
                      <span class="badge bg-success">Pagado</span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                      <i
                        data-feather="shopping-bag"
                        class="me-2 text-primary"></i>
                      Producto
                    </td>
                    <td>Laura Torres</td>
                    <td>
                      <span class="badge bg-info text-dark">Proteína 2lb</span>
                    </td>
                    <td>$45.000</td>
                    <td>11 Oct 2025</td>
                    <td class="text-center">
                      <span class="badge bg-warning text-dark">Pendiente</span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                      <i
                        data-feather="dollar-sign"
                        class="me-2 text-info"></i>
                      Pago Diario
                    </td>
                    <td>Juan Pérez</td>
                    <td>
                      <span class="badge bg-light text-dark">Ingreso puntual</span>
                    </td>
                    <td>$10.000</td>
                    <td>11 Oct 2025</td>
                    <td class="text-center">
                      <span class="badge bg-success">Pagado</span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                      <i data-feather="user-x" class="me-2 text-danger"></i>
                      Membresía
                    </td>
                    <td>María López</td>
                    <td>
                      <span class="badge bg-danger">Anual</span>
                    </td>
                    <td>$1.200.000</td>
                    <td>10 Oct 2025</td>
                    <td class="text-center">
                      <span class="badge bg-danger">Vencida</span>
                    </td>
                  </tr>

                  <tr>
                    <td>
                      <i
                        data-feather="shopping-bag"
                        class="me-2 text-warning"></i>
                      Producto
                    </td>
                    <td>Carlos Díaz</td>
                    <td>
                      <span class="badge bg-secondary">Guantes Gym</span>
                    </td>
                    <td>$30.000</td>
                    <td>09 Oct 2025</td>
                    <td class="text-center">
                      <span class="badge bg-success">Pagado</span>
                    </td>
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

<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection
