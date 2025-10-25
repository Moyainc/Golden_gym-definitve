@extends('layouts.app')
@section('title', 'Productos - Golden Gym')

@section('content')

<body>
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
                        <option>Proteínas</option>
                        <option>Bebidas</option>
                        <option>Ropa</option>
                        <option>Accesorios</option>
                        <option>Batidos</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <select
                        id="filterStatus"
                        class="form-select text-light border-secondary">
                        <option value="">Disponibilidad</option>
                        <option>Activo</option>
                        <option>Bajo Stock</option>
                        <option>Agotado</option>
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
                  <div class="col-6">
                    <div
                      class="card bg-black border-warning text-center p-3 shadow-sm">
                      <h6 class="text-muted">Total Productos</h6>
                      <h3
                        id="totalProducts"
                        class="text-warning mb-0 fw-bold">
                        3
                      </h3>
                    </div>
                  </div>
                  <div class="col-6">
                    <div
                      class="card bg-black border-warning text-center p-3 shadow-sm">
                      <h6 class="text-muted">Stock Total</h6>
                      <h3 id="totalStock" class="text-warning mb-0 fw-bold">
                        53
                      </h3>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TABLA -->
          <div class="card border-0 shadow-sm mb-4">
            <div
              class="card-header d-flex justify-content-between align-items-center border-0">
              <h5 class="text-warning mb-0">
                <i data-feather="list" class="me-1"></i>Listado de Productos
              </h5>
            </div>
            <div class="card-body table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="text-warning border-secondary">
                  <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Stock</th>
                    <th>Precio</th>
                    <th>Ventas</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody id="productTable" class="text-light">
                  <tr>
                    <td>#001</td>
                    <td>Proteína Whey Gold</td>
                    <td>Proteínas</td>
                    <td>
                      <span class="badge text-black bg-success">45</span>
                    </td>
                    <td>$150.000</td>
                    <td>23</td>
                    <td>
                      <span class="badge text-black bg-success">Activo</span>
                    </td>
                    <td>
                      <button
                        class="btn btn-sm btn-outline-light me-1 edit-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#productModal"
                        data-mode="edit">
                        <i data-feather="edit"></i>
                      </button>
                      <button
                        class="btn btn-sm btn-outline-danger delete-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteProductModal">
                        <i data-feather="trash-2"></i>
                      </button>
                    </td>
                  </tr>
                  <tr>
                    <td>#002</td>
                    <td>Guantes de Entrenamiento</td>
                    <td>Accesorios</td>
                    <td>
                      <span class="badge text-black bg-warning">8</span>
                    </td>
                    <td>$60.000</td>
                    <td>12</td>
                    <td>
                      <span class="badge text-black bg-warning">Bajo Stock</span>
                    </td>
                    <td>
                      <button
                        class="btn btn-sm btn-outline-light me-1 edit-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#productModal"
                        data-mode="edit">
                        <i data-feather="edit"></i>
                      </button>
                      <button
                        class="btn btn-sm btn-outline-danger delete-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteProductModal">
                        <i data-feather="trash-2"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- LISTADO DE CATEGORÍAS -->
          <div class="card border-0 shadow-sm mb-4">
            <div
              class="card-header d-flex justify-content-between align-items-center border-0">
              <h5 class="text-warning mb-0">
                <i data-feather="folder" class="me-1"></i>Listado de
                Categorías
              </h5>
            </div>
            <div class="card-body table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="text-warning border-secondary">
                  <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody id="categoryTable" class="text-light">
                  <!-- Aquí el JS insertará las filas dinámicamente -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>

      <!-- MODAL Nueva / Editar Categoría -->
      <div
        class="modal fade"
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
                    class="form-control bg-black border-0 text-light"
                    required />
                </div>
                <div class="mb-3">
                  <label for="categoryDescription" class="form-label text-muted">Descripción</label>
                  <textarea
                    id="categoryDescription"
                    name="descripcion"
                    class="form-control bg-black border-0 text-light"
                    rows="2"></textarea>
                </div>

                <div class="modal-footer border-secondary">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                  </button>
                  <button
                    type="submit"
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

      <div class="modal fade" id="deleteCategoryModal" tabindex="-1">
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
      <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content bg-dark text-light border-warning">
            <div class="modal-header border-secondary">
              <h5
                class="modal-title text-warning fw-bold"
                id="productModalTitle">
                <i data-feather="package"></i> Nuevo Producto
              </h5>
              <button
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form id="productForm" class="row g-3">
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
                    <option>Proteínas</option>
                    <option>Bebidas</option>
                    <option>Ropa</option>
                    <option>Accesorios</option>
                    <option>Batidos</option>
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
                </div>
                <div class="col-md-4">
                  <label class="form-label">Estado</label>
                  <select
                    id="estadoProducto"
                    class="form-select text-light border-0">
                    <option>Activo</option>
                    <option>Inactivo</option>
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
    </div>
  </div>

  <script src="{{ asset('js/productos.js') }}"></script>
  <script src="../js/app.js"></script>
</body>
@endsection
