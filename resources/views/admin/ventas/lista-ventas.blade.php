@extends('layouts.app')
@section('title', 'Listado de ventas - Golden Gym')
@section('content')
  <body>
    <div class="wrapper">
      <div class="main">
        <div id="navbar"></div>

        <main class="content">
          <div class="container-fluid p-0">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h1 class="h3 text-white fw-bold mb-0">
                <i data-feather="list" class="me-2 tittle-color"></i>Listado de
                Ventas
              </h1>
            </div>

            <!-- FILTROS -->
            <div class="card border-0 shadow-sm mb-4">
              <div
                class="card-header border-0 d-flex justify-content-between align-items-center"
              >
                <h5 class="text-warning mb-0">
                  <i data-feather="filter" class="me-1"></i>Filtros de Búsqueda
                </h5>
              </div>
              <div class="card-body">
                <div class="row g-2 align-items-end">
                  <div class="col-md-3">
                    <label class="form-label text-light">Categoría</label>
                    <select
                      id="filterCategoria"
                      class="form-select text-light border-secondary"
                    >
                      <option value="">Todas</option>
                      <option>Proteínas</option>
                      <option>Batidos</option>
                      <option>Accesorios</option>
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label text-light">Fecha</label>
                    <input
                      type="date"
                      id="filterFecha"
                      class="form-control text-light border-secondary"
                    />
                  </div>

                  <div class="col-md-4">
                    <label class="form-label text-light">Buscar</label>
                    <input
                      type="text"
                      id="searchVenta"
                      class="form-control text-light border-secondary"
                      placeholder="Buscar por producto, método o estado..."
                    />
                  </div>

                  <div class="col-md-2">
                    <button
                      id="btnBuscar"
                      class="btn btn-warning text-dark fw-bold w-100 p-2"
                    >
                      <i data-feather="search"></i> Buscar
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- TABLA DE VENTAS CON PAGINACIÓN -->
            <div class="card border-0 shadow-sm mb-4">
              <div class="card-body table-responsive">
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
                    <!-- Se genera dinámicamente -->
                  </tbody>
                </table>
              </div>

              <!-- PAGINACIÓN MODERNA -->
              <div
                class="card-footer border-0 d-flex justify-content-between align-items-center py-3 px-3"
              >
                <nav aria-label="Paginación de ventas">
                  <ul
                    class="pagination-modern d-flex align-items-center gap-2 mb-0"
                  >
                    <li class="page-item">
                      <button class="page-btn prev" aria-label="Anterior">
                        &laquo;
                      </button>
                    </li>
                    <li class="page-item active">
                      <button class="page-btn active">1</button>
                    </li>
                    <li class="page-item">
                      <button class="page-btn">2</button>
                    </li>
                    <li class="page-item">
                      <button class="page-btn">3</button>
                    </li>
                    <li class="page-item">
                      <button class="page-btn next" aria-label="Siguiente">
                        &raquo;
                      </button>
                    </li>
                  </ul>
                </nav>
                <span
                  id="paginationInfo"
                  class="text-light small fw-light opacity-75"
                >
                  Mostrando 1–10 de 150 ventas
                </span>
              </div>
            </div>
          </div>
        </main>
        <div id="footer"></div>
      </div>
    </div>
    <sript src="../../js/app.js"></sript>
  </body>
@endsection
