@extends('layouts.app')
@section('title', 'Ventas - Golden Gym')
@section('content')

<body>
  <div class="wrapper">
    <div class="main">
      <div id="navbar"></div>
      <main class="content">
        <div class="container-fluid p-0">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-white fw-bold mb-0">
              <i data-feather="shopping-cart" class="me-2 tittle-color"></i>Ventas
            </h1>

            <button
              class="btn btn-warning text-dark fw-bold shadow-sm"
              data-bs-toggle="modal"
              data-bs-target="#nuevaVentaModal">
              <i data-feather="plus-circle" class="me-1"></i>Nueva Venta
            </button>
          </div>

          <!-- TABLA DE VENTAS -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body ventas-container">
              <h5 class="text-warning mb-3">
                <i data-feather="list" class="me-1"></i>Últimas Ventas
              </h5>

              <div class="ventas-scroll">
                <table class="table table-hover align-middle mb-0">
                  <thead class="text-warning border-secondary">
                    <tr>
                      <th>ID</th>
                      <th>Producto</th>
                      <th>Categoría</th>
                      <th>Método</th>
                      <th>Precio</th>
                      <th>Cantidad</th>
                      <th>Total</th>
                      <th>Estado</th>
                    </tr>
                  </thead>
                  <tbody id="ventasUltimas5" class="text-light">
                    <!-- Aquí JS insertará las últimas 5 ventas -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </main>

      <!-- Modal Detalle de Venta -->
      <div
        class="modal fade"
        id="detalleVentaModal"
        tabindex="-1"
        aria-labelledby="detalleVentaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div
            class="modal-content bg-dark text-light border-warning shadow-lg">
            <div
              class="modal-header border-0 d-flex justify-content-between align-items-center">
              <h5
                class="modal-title text-warning fw-bold"
                id="detalleVentaLabel">
                <i data-feather="shopping-bag" class="me-1"></i> Detalle de la
                Venta
              </h5>
              <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"
                aria-label="Close"></button>
            </div>

            <div class="modal-body">
              <div class="row mb-3">
                <div class="col-md-6">
                  <p><strong>ID:</strong> <span id="ventaId">#001</span></p>
                  <p>
                    <strong>Producto:</strong>
                    <span id="ventaProducto">Proteína Whey</span>
                  </p>
                  <p>
                    <strong>Categoría:</strong>
                    <span id="ventaCategoria">Proteínas</span>
                  </p>
                </div>
                <div class="col-md-6">
                  <p>
                    <strong>Método de pago:</strong>
                    <span id="ventaMetodo">Efectivo</span>
                  </p>
                  <p>
                    <strong>Precio:</strong>
                    <span id="ventaPrecio">$150.000</span>
                  </p>
                  <p>
                    <strong>Fecha:</strong>
                    <span id="ventaFecha">2025-10-10</span>
                  </p>
                </div>
              </div>

              <div class="border-top border-secondary pt-3">
                <p>
                  <strong>Estado:</strong>
                  <span id="ventaEstado" class="badge bg-success text-black">Completada</span>
                </p>
                <p>
                  <strong>Descripción:</strong><br />
                  <span id="ventaDescripcion">Venta correspondiente al producto de alta demanda en
                    línea de suplementos nutricionales.</span>
                </p>
              </div>
            </div>

            <div class="modal-footer border-0 d-flex justify-content-between">
              <button
                type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal">
                Cerrar
              </button>
              <button type="button" class="btn btn-warning text-dark fw-bold">
                <i data-feather="printer" class="me-1"></i> Imprimir Recibo
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Nueva Venta -->
      <div
        class="modal fade"
        id="nuevaVentaModal"
        tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-dark text-light border-warning">
            <div class="modal-header border-warning">
              <h5 class="modal-title text-warning">
                <i data-feather="plus-circle" class="me-1"></i>Nueva Venta
              </h5>
              <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <form id="ventaForm">
                <div class="row g-2 mb-3">
                  <div class="col-6">
                    <label class="form-label">Categoría</label>
                    <select
                      id="categoriaVenta"
                      class="form-select text-light border-0">
                      <option value="">Seleccione</option>
                      <option value="Proteínas">Proteínas</option>
                      <option value="Batidos">Batidos</option>
                      <option value="Accesorios">Accesorios</option>
                    </select>
                  </div>
                  <div class="col-6">
                    <label class="form-label">Producto</label>
                    <select
                      id="productoVenta"
                      class="form-select text-light border-0">
                      <option value="">Seleccione producto</option>
                    </select>
                  </div>
                </div>

                <div class="row g-2 mb-3 align-items-end">
                  <div class="col-4">
                    <label class="form-label">Precio</label>
                    <input
                      type="text"
                      id="precioVenta"
                      class="form-control text-light border-0"
                      readonly />
                  </div>
                  <div class="col-3">
                    <label class="form-label">Cantidad</label>
                    <input
                      type="number"
                      id="cantidadVenta"
                      class="form-control text-light border-0"
                      min="1"
                      max="10"
                      value="1" />
                  </div>
                  <div class="col-5">
                    <label class="form-label">Total</label>
                    <input
                      type="text"
                      id="totalVenta"
                      class="form-control text-light border-0"
                      readonly
                      value="$0" />
                  </div>
                </div>
                <div class="col-9">
                  <label for="descripcionVenta" class="form-label">Descripción</label>
                  <textarea
                    id="descripcionVenta"
                    class="form-control text-light border-secondary"
                    rows="3"
                    placeholder="Escribe una breve descripción de la venta..."></textarea>
                </div>

                <div class="col-4 mt-2">
                  <label class="form-label">Método de Pago</label>
                  <select
                    id="metodoPago"
                    class="form-select text-light border-0">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Transferencia">Transferencia</option>
                  </select>
                </div>

                <div
                  class="mb-3 mt-2"
                  id="comprobanteContainer"
                  style="display: none">
                  <label class="form-label">Subir Comprobante</label>
                  <input
                    type="file"
                    id="comprobantePago"
                    class="form-control text-light border-0"
                    accept="image/*,application/pdf" />
                </div>

                <!-- Estado oculto -->
                <input type="hidden" id="estadoVenta" value="Completada" />
              </form>
            </div>

            <div
              class="modal-footer border-warning d-flex justify-content-between">
              <button class="btn btn-secondary" data-bs-dismiss="modal">
                Cancelar
              </button>
              <button
                class="btn btn-warning text-dark fw-bold"
                id="guardarVentaBtn">
                Guardar Venta
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="../../js/app.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
@endsection
