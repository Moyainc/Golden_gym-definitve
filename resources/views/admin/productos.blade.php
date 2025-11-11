@extends('layouts.app')
@section('title', 'Productos - Golden Gym')

@section('content')

<div class="wrapper">
  <div class="main">
    <main class="content">
      <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="h3 text-white fw-bold mb-0">
            <i data-feather="package" class="me-2 tittle-color"></i>
            Inventario de Productos
          </h1>


          <div class="d-flex gap-2">
            <button
              class="btn btn-warning text-dark fw-bold shadow-sm"
              data-bs-toggle="modal"
              data-bs-target="#newCategoryModal">
              <i data-feather="folder-plus" class="me-1"></i>Nueva Categoría
            </button>

            <button
              class="btn btn-warning text-dark fw-bold shadow-sm"
              data-bs-toggle="modal"
              data-bs-target="#productModal"
              data-mode="create">
              <i data-feather="plus-circle" class="me-1"></i>Nuevo Producto
            </button>

            <button
              type="button"
              class="btn btn-success py-3 ms-md-2 mt-2 mt-md-0 d-inline-flex align-items-center flex-nowrap text-nowrap"
              data-bs-toggle="modal"
              data-bs-target="#reportsInventarioModal">
              <i data-feather="file-text" class="me-1"></i> Reportes
            </button>

          </div>
        </div>

        <!-- FILTROS -->
        <div class="row g-3 mb-4">
          <div class="col-md-8">
            <div class="card border-0 shadow-sm">
              <div
                class="card-header border-0 d-flex justify-content-between align-items-center">
                <h5 class="text-warning mb-0">
                  <i data-feather="filter" class="me-1"></i>Filtros
                </h5>
              </div>
              <div class="card-body">
                <div class="row g-2">
                  <div class="col-md-4">
                    <select
                      id="filterCategory"
                      class="form-select text-light border-secondary">
                      <option value="">Tipo de producto</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <select
                      id="filterStatus"
                      class="form-select text-light border-secondary">
                      <option value="">Disponibilidad</option>
                      <option>Activo</option>
                      <option>Inactivo</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <input
                      id="searchProduct"
                      type="text"
                      class="form-control text-light border-secondary"
                      placeholder="Buscar producto..." />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TARJETAS DE RESUMEN -->
          <div class="col-md-4 d-flex align-items-center">
            <div class="w-100">
              <div class="row g-2">
                <!-- Total Productos -->
                <div class="col-6 resumen-card-col">
                  <div class=" card border-0 shadow-sm bg-dark-card h-100">
                    <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                      <div>
                        <h6 class="text-muted text-uppercase small mb-1">Total Productos</h6>
                        <h5 id="totalProducts" class="text-warning fw-bold mb-0">$0</h5>
                      </div>
                      <div class="bg-warning bg-opacity-25 p-2 rounded">
                        <i data-feather="package" class="text-warning" width="20"></i>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Stock Total -->
                <div class="col-6 resumen-card-col"">
                  <div class=" card border-0 shadow-sm bg-dark-card h-100">
                  <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                    <div>
                      <h6 class="text-muted text-uppercase small mb-1">Stock Total</h6>
                      <h5 id="totalStock" class="text-info fw-bold mb-0">0</h5>
                    </div>
                    <div class="bg-info bg-opacity-25 p-2 rounded">
                      <i data-feather="layers" class="text-info" width="20"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>


      </div>

      <!-- TABLA -->
      <div class="row g-4">
        <!-- Tabla Productos -->
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm mb-4 table-card">
            <div class="card-header d-flex justify-content-between align-items-center border-0 py-4 px-3">
              <h5 class="text-warning mb-0">
                <i data-feather="list" class="me-1"></i>Listado de Productos
              </h5>
            </div>
            <div class="card-body table-responsive py-2 px-3">
              <table class="table table-hover table-sm mb-0">
                <thead class="text-warning border-secondary">
                  <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Stock</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody id="productTable" class="text-light">
                  <!-- JS insertará filas aquí -->
                </tbody>
              </table>

            </div>
          </div>
        </div>

        <!-- Tabla Categorías -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm mb-4 table-card">
            <div class="card-header d-flex justify-content-between align-items-center border-0 py-4 px-3">
              <h5 class="text-warning mb-0">
                <i data-feather="folder" class="me-1"></i>Listado de Categorías
              </h5>
            </div>
            <div class="card-body table-responsive py-2 px-3">
              <table class="table table-hover table-sm mb-0">
                <thead class="text-warning border-secondary">
                  <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody id="categoryTable" class="text-light">
                  <!-- JS inserta filas aquí -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
  </div>
  </main>

  <!-- MODAL Nueva / Editar Categoría -->

</div>
</div>

<div
  class="modal fade modal-dark-style"
  id="newCategoryModal"
  tabindex="-1"
  aria-labelledby="newCategoryModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div
      class="modal-content bg-dark text-light border-secondary shadow-lg">
      <div class="modal-header border-secondary">
        <h5
          class="modal-title text-warning fw-bold"
          id="newCategoryModalLabel">
          <i data-feather="folder-plus" class="me-1"></i>
          <span id="categoryModalTitle">Agregar Nueva Categoría</span>
        </h5>
        <button
          type="button"
          class="btn-close btn-close-white"
          data-bs-dismiss="modal"
          aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <form id="newCategoryForm" method="POST" action="{{ route('admin.categorias.store') }}">
          @csrf
          <div class="mb-3">
            <label for="categoryName" class="form-label text-muted">Nombre</label>
            <input
              type="text"
              name="nombre"
              id="categoryName"
              class="form-control border-0 text-light"
              required />
          </div>
          <div class="mb-3">
            <label for="categoryDescription" class="form-label text-muted">Descripción</label>
            <textarea
              id="categoryDescription"
              name="descripcion"
              class="form-control  border-0 text-light"
              rows="2"></textarea>
          </div>

          <div class="modal-footer border-secondary">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              Cancelar
            </button>
            <button
              type="button"
              id="saveCategoryBtn"
              class="btn btn-warning text-dark fw-bold">
              <i data-feather="save" class="me-1"></i>Guardar
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-dark-style" id="deleteCategoryModal" tabindex="-1">
  <div class="modal-dialog modal-dark">
    <div class="modal-content bg-dark text-white border-0">
      <div class="modal-header border-secondary">
        <h5 class="modal-title text-danger">Eliminar Categoría</h5>
        <button
          type="button"
          class="btn-close btn-close-white"
          data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        ¿Estás seguro de que deseas eliminar esta categoría?
      </div>
      <div class="modal-footer border-0">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          Cancelar
        </button>
        <button class="btn btn-danger fw-bold">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL Crear / Editar Producto -->
<div class="modal fade modal-dark-style" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark text-light border-warning">
      <div class="modal-header border-secondary">
        <h5
          class="modal-title tittle-color fw-bold"
          id="productModalTitle">
          <i data-feather="package"></i> Nuevo Producto
        </h5>
        <button
          class="btn-close btn-close-white"
          data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="productForm" class="row g-3">
          <!-- Campo oculto para saber si estamos editando y almacenar el ID -->
          <input type="hidden" id="productId" />

          <div class="col-md-6">
            <label class="form-label">Nombre del Producto</label>
            <input
              type="text"
              class="form-control text-light border-0"
              id="nombreProducto"
              required />
          </div>

          <div class="col-md-6">
            <label class="form-label">Categoría</label>
            <select
              id="categoriaProducto"
              class="form-select text-light border-0"
              required>
              <!-- JS insertará aquí las opciones dinámicamente -->
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Stock</label>
            <input
              type="number"
              class="form-control text-light border-0"
              id="stockProducto"
              required />
          </div>

          <div class="col-md-4">
            <label class="form-label">Precio</label>
            <input
              type="number"
              class="form-control text-light border-0"
              id="precioProducto"
              required />
            <small class="tittle-color fst-italic">No digite puntos ni comas</small>
          </div>
          <div class="col-md-4">
            <label class="form-label">Estado</label>
            <select
              id="estadoProducto"
              class="form-select text-light border-0">
              <option value="Activo">Activo</option>
              <option value="Inactivo">Inactivo</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer border-secondary">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          Cancelar
        </button>
        <button
          id="guardarProducto"
          class="btn btn-warning text-dark fw-bold">
          Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal fade" id="deleteProductModal" tabindex="-1">
  <div class="modal-dialog modal-dark">
    <div class="modal-content bg-dark text-white border-0">
      <div class="modal-header border-secondary">
        <h5 class="modal-title text-danger">Eliminar Producto</h5>
        <button
          type="button"
          class="btn-close btn-close-white"
          data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        ¿Estás seguro de que deseas eliminar este producto?
      </div>
      <div class="modal-footer border-0">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          Cancelar
        </button>
        <button class="btn btn-danger fw-bold">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Reportes -->
<div class="modal fade" id="reportsInventarioModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark border-0 text-white shadow-lg">
      <div class="modal-header border-secondary">
        <h5 class="modal-title text-success">
          <i data-feather="file-text" class="me-1"></i> Generar Reporte de Inventario
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form id="reportsInventarioForm" class="modal-body">
        <div class="alert alert-info bg-opacity-10 border-info text-info">
          <i data-feather="info" class="me-1"></i>
          Selecciona un rango de fechas para filtrar el inventario a incluir en el reporte.
        </div>

        <div class="mb-3">
          <label class="form-label">Fecha inicio</label>
          <input id="reportStartDateInventario" name="inicio" type="date"
            class="form-control text-white border-0" required />
        </div>

        <div class="mb-3">
          <label class="form-label">Fecha fin</label>
          <input id="reportEndDateInventario" name="fin" type="date"
            class="form-control text-white border-0" required />
        </div>

        <div class="modal-footer border-secondary">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" id="exportPdfInventarioBtn" class="btn btn-danger">
            <i data-feather="file" class="me-1"></i> Exportar PDF
          </button>
          <button type="button" id="exportExcelInventarioBtn" class="btn btn-success">
            <i data-feather="download" class="me-1"></i> Exportar Excel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="{{ asset('js/productos.js') }}"></script>

@endsection
