@extends('layouts.app')
@section('title', 'Ventas - Golden Gym')
@section('content')

<div class="wrapper">
  <div class="main">
    <div id="navbar"></div>
    <main class="content">
      <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="h3 text-white fw-bold mb-0">
            <i data-feather="shopping-cart" class="me-2 tittle-color"></i>Ventas
          </h1>

          <!-- Botón Venta Rápida -->
          <button
            class="btn btn-success text-dark fw-bold shadow-sm"
            data-bs-toggle="modal"
            data-bs-target="#ventaRapidaModal">
            <i data-feather="zap" class="me-1"></i>Venta Rápida
          </button>
        </div>

        <!-- VENTA DETALLADA EN LA PÁGINA -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body text-light">
            <h5 class="text-warning mb-3">Nueva Venta Detallada</h5>
            <form id="ventaDetalladaForm">

              <div id="productosContainer">
                <div class="row g-2 mb-3 producto-item align-items-end">
                  <!-- Foto del producto, inicialmente oculta -->

                  <div class="col-2 categoria-col">
                    <label class="form-label">Categoría</label>
                    <select name="categoria[]" class="form-select text-light border-0 categoria">
                      <option value="">Seleccione</option>
                    </select>
                  </div>

                  <div class="col-4 producto-col">
                    <label class="form-label">Producto</label>
                    <select name="producto[]" class="form-select text-light border-0 producto">
                      <option value="">Seleccione</option>
                    </select>
                  </div>

                  <div class="col-2">
                    <label class="form-label">Cantidad</label>
                    <input type="number" name="cantidad[]" class="form-control text-light border-0 cantidad" min="1" value="1" />
                  </div>

                  <div class="col-2">
                    <label class="form-label">Subtotal</label>
                    <input type="text" name="subtotal[]" class="form-control text-light border-0 subtotal" readonly value="$0" />
                  </div>

                  <div class="col-1 mb-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm eliminarProducto">X</button>
                  </div>
                </div>
              </div>



              <button type="button" class="btn btn-secondary mb-3" id="agregarProductoBtn">Agregar Producto</button>

              <div class="row g-2 align-items-end mb-3">
                <div class="col-3">
                  <label class="form-label">Total Final</label>
                  <input type="text" id="totalVenta" class="form-control text-light border-0" readonly value="$0" />
                  <input type="hidden" id="usuarioVenta" value="{{ auth()->user()->id }}" />
                </div>
                <div class="col-2">
                  <label class="form-label">Método de Pago</label>
                  <select id="metodoPago" class="form-select text-light border-0">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Transferencia">Transferencia</option>
                  </select>
                </div>
                <div class="col-auto mb-1">
                  <button type="button" class="btn btn-warning text-dark fw-bold" id="guardarVentaDetalladaBtn">
                    Guardar Venta
                  </button>
                </div>
              </div>
            </form>
          </div>
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
                    <th>Foto</th>
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
                  <!-- JS insertará las últimas 5 ventas -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal Venta Rápida -->
    <div class="modal fade" id="ventaRapidaModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" style="max-width: 680px;">
        <div class="modal-content bg-dark text-light border-success">
          <div class="modal-header border-success">
            <h5 class="modal-title text-success">
              <i data-feather="zap" class="me-1"></i>Venta Rápida
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <form id="ventaRapidaForm">

              <div id="productosRapidosContainer">
                <!-- Producto inicial -->
                <div class="row g-2 mb-3 producto-item align-items-end">
                  <div class="col-3 categoria-col">
                    <label class="form-label">Categoría</label>
                    <select name="categoria[]" class="form-select text-light border-0 categoriaRapida">
                      <option value="">Seleccione categoría</option>
                    </select>
                  </div>

                  <div class="col-3 producto-col">
                    <label class="form-label">Producto</label>
                    <select name="producto[]" class="form-select text-light border-0 productoRapida">
                      <option value="">Seleccione producto</option>
                    </select>
                  </div>

                  <div class="col-2">
                    <label class="form-label">Precio</label>
                    <input type="text" name="precio[]" class="form-control text-light border-0 precioRapida" readonly value="$0" />
                  </div>

                  <div class="col-2">
                    <label class="form-label">Cantidad</label>
                    <input type="number" name="cantidad[]" class="form-control text-light border-0 cantidadRapida" min="1" value="1" />
                  </div>

                  <div class="col-2 mb-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm eliminarProductoRapida">X</button>
                  </div>
                </div>
              </div>

              <button type="button" class="btn btn-secondary mb-3" id="agregarProductoRapidaBtn">Agregar Producto</button>

              <div class="row g-2 mb-3 align-items-end">
                <!-- Método de Pago -->
                <div class="col-3">
                  <label for="metodoRapida" class="form-label">Método de Pago</label>
                  <select id="metodoRapida" class="form-select text-light border-0">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Transferencia">Transferencia</option>
                  </select>
                </div>

                <!-- Total Final -->
                <div class="col-3">
                  <label for="totalRapida" class="form-label">Total Final</label>
                  <input type="text" id="totalRapida" class="form-control text-light border-0" readonly value="$0" />
                </div>

                <!-- Botón Vender -->
                <div class="col-3 mb-1">
                  <input type="hidden" id="estadoRapida" value="Completada" />
                  <button type="button" class="btn btn-success text-dark fw-bold" id="guardarRapidaBtn">
                    Vender
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>




  </div>
</div>



<script src="../../js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/ventas/ventas.js') }}"></script>


</html>
@endsection
