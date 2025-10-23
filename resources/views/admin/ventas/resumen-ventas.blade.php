@extends('layouts.app')
@section('title', 'Resumen de ventas - Golden Gym')
@section('content')
  <body>
    <div class="wrapper">
      <div class="main">
        <div id="navbar"></div>

        <main class="content">
          <div class="container-fluid p-0">
            <div class="d-flex justify-content-between align-items-center my-4">
              <h1 class="h3 text-white fw-bold mb-0">
                <i data-feather="shopping-cart" class="me-2 tittle-color"></i
                >Resumen de Ventas
              </h1>
            </div>

            <!--  TARJETAS DE RESUMEN -->
            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <div
                  class="card bg-black border-warning text-center p-3 shadow-sm"
                >
                  <h6 class="text-muted">Total Ventas</h6>
                  <h3 id="totalVentas" class="text-warning mb-0 fw-bold">$0</h3>
                </div>
              </div>
              <div class="col-md-4">
                <div
                  class="card bg-black border-warning text-center p-3 shadow-sm"
                >
                  <h6 class="text-muted">N° de Ventas</h6>
                  <h3 id="numVentas" class="text-warning mb-0 fw-bold">0</h3>
                </div>
              </div>
              <div class="col-md-4">
                <div
                  class="card bg-black border-warning text-center p-3 shadow-sm"
                >
                  <h6 class="text-muted">Pendientes por Confirmar</h6>
                  <h3 id="ventasPendientes" class="text-warning mb-0 fw-bold">
                    0
                  </h3>
                </div>
              </div>
            </div>

            <!--  GRÁFICOS DE VENTAS -->
            <div class="row g-4">
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="card border-0 shadow-sm">
                    <div
                      class="card-header border-0 d-flex justify-content-between align-items-center"
                    >
                      <h5 class="text-warning mb-0">
                        <i data-feather="pie-chart" class="me-1"></i>Ventas por
                        Categoría
                      </h5>
                    </div>
                    <div class="card-body" style="height: 350px">
                      <canvas id="ventasCategoriaChart"></canvas>
                    </div>
                  </div>
                </div>

                <!-- VENTAS POR PRODUCTO -->
                <div class="col-md-6">
                  <div class="card border-0 shadow-sm">
                    <div
                      class="card-header border-0 d-flex justify-content-between align-items-center"
                    >
                      <h5 class="text-warning mb-0">
                        <i data-feather="trending-up" class="me-1"></i>Ventas
                        por Producto
                      </h5>
                    </div>
                    <div class="card-body" style="height: 350px">
                      <canvas id="ventasProductosChart"></canvas>
                    </div>
                  </div>
                </div>
              </div>

              <!-- TOTAL DE VENTAS POR MES  -->
              <div class="card border-0 shadow-sm mt-4">
                <div
                  class="card-header border-0 d-flex justify-content-between align-items-center"
                >
                  <h5 class="text-warning mb-0">
                    <i data-feather="bar-chart-2" class="me-1"></i>Total de
                    Ventas por Mes
                  </h5>
                </div>
                <div class="card-body" style="height: 400px">
                  <canvas id="ventasTotalesChart"></canvas>
                </div>
              </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
              <div class="card-body ventas-container">
                <div
                  class="d-flex justify-content-between align-items-center mb-3"
                >
                  <h5 class="text-warning mb-0">
                    <i data-feather="list" class="me-1"></i>Últimas Ventas
                  </h5>
                  <a
                    href="ventas.html"
                    class="btn btn-sm btn-outline-warning fw-bold"
                  >
                    Ver más <i data-feather="arrow-right" class="ms-1"></i>
                  </a>
                </div>

                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="text-warning border-secondary">
                      <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Método</th>
                        <th>Precio</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                      </tr>
                    </thead>
                    <tbody id="ventasTable" class="text-light">
                      <tr>
                        <td>#001</td>
                        <td>Proteína Whey</td>
                        <td>Proteínas</td>
                        <td>Efectivo</td>
                        <td>$150.000</td>
                        <td>2025-10-10</td>
                        <td>
                          <span class="badge bg-success text-black"
                            >Completada</span
                          >
                        </td>
                      </tr>
                      <tr>
                        <td>#002</td>
                        <td>Guantes</td>
                        <td>Accesorios</td>
                        <td>Transferencia</td>
                        <td>$60.000</td>
                        <td>2025-10-11</td>
                        <td>
                          <span class="badge bg-warning text-black"
                            >Pendiente</span
                          >
                        </td>
                      </tr>
                      <tr>
                        <td>#003</td>
                        <td>Batido de Fresa</td>
                        <td>Batidos</td>
                        <td>Tarjeta</td>
                        <td>$45.000</td>
                        <td>2025-10-12</td>
                        <td>
                          <span class="badge bg-success text-black"
                            >Completada</span
                          >
                        </td>
                      </tr>
                      <tr>
                        <td>#004</td>
                        <td>Toalla de Entrenamiento</td>
                        <td>Accesorios</td>
                        <td>Efectivo</td>
                        <td>$25.000</td>
                        <td>2025-10-13</td>
                        <td>
                          <span class="badge bg-success text-black"
                            >Completada</span
                          >
                        </td>
                      </tr>
                      <tr>
                        <td>#005</td>
                        <td>Proteína Vegana</td>
                        <td>Proteínas</td>
                        <td>Transferencia</td>
                        <td>$160.000</td>
                        <td>2025-10-14</td>
                        <td>
                          <span class="badge bg-warning text-black"
                            >Pendiente</span
                          >
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
    <script src="../../js/app.js"></script>
  </body>
@endsection
