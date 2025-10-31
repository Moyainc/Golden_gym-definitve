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
              <div class="row g-2 mb-3">
                <div class="col-3">
                  <label class="form-label">Foto</label>
                  <div class="border border-secondary rounded p-1 text-center" style="height:80px; display:flex; align-items:center; justify-content:center;">
                    <img id="fotoProducto" src="" alt="Producto" style="max-height:70px; max-width:70px;" />
                  </div>
                </div>

                <div class="col-4">
                  <label class="form-label">Categoría</label>
                  <select id="categoriaVenta" class="form-select text-light border-0">
                    <option value="">Seleccione</option>
                  </select>
                </div>
                <div class="col-5">
                  <label class="form-label">Producto</label>
                  <select id="productoVenta" class="form-select text-light border-0">
                    <option value="">Seleccione producto</option>
                  </select>
                </div>
                <div class="col-4 justify-content-center">
                  <label class="form-label">Cliente</label>
                  <input type="text" id="identificacionCliente" class="form-control" placeholder="Ingrese ID">
                  <small id="nombreCliente" class="text-success mt-1 d-block"></small>
                  <!-- JS llenará con los nombres de clientes -->
                  </select>
                </div>

                <!-- Usuario (oculto) -->
                <input type="hidden" id="usuarioVenta" value="{{ auth()->user()->id }}" />
              </div>

              <div class="row g-2 mb-3 align-items-end">
                <div class="col-4">
                  <label class="form-label">Precio</label>
                  <input type="text" id="precioVenta" class="form-control text-light border-0" readonly />
                </div>
                <div class="col-3">
                  <label class="form-label">Cantidad</label>
                  <input type="number" id="cantidadVenta" class="form-control text-light border-0" min="1" value="1" />
                </div>
                <div class="col-5">
                  <label class="form-label">Total</label>
                  <input type="text" id="totalVenta" class="form-control text-light border-0" readonly value="$0" />
                </div>
              </div>

              <div class="mb-3 justify-content-center">
                <label for="descripcionVenta" class="form-label">Descripción</label>
                <textarea id="descripcionVenta" class="form-control text-light border-secondary" style="width: 80%;" rows="3" placeholder="Escribe una breve descripción de la venta..."></textarea>
              </div>

              <div class="row g-2 align-items-end mb-3">
                <div class="col-2">
                  <label class="form-label">Método de Pago</label>
                  <select id="metodoPago" class="form-select text-light border-0">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Transferencia">Transferencia</option>
                  </select>
                </div>

                <div class="col-auto mb-1">
                  <label class="form-label d-block">&nbsp;</label> <!-- para alinear con el select -->
                  <button type="button" class="btn btn-warning text-dark fw-bold" id="guardarVentaDetalladaBtn">
                    Guardar Venta
                  </button>
                </div>
              </div>

              <input type="hidden" id="estadoVenta" value="Completada" />

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
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-success">
          <div class="modal-header border-success">
            <h5 class="modal-title text-success">
              <i data-feather="zap" class="me-1"></i>Venta Rápida
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <form id="ventaRapidaForm">
              <div class="row g-2 mb-3">
                <div class="col-3">
                  <label class="form-label">Foto</label>
                  <div class="border border-secondary rounded p-1 text-center" style="height:60px; display:flex; align-items:center; justify-content:center;">
                    <img id="fotoProductoRapida" src="" alt="Producto" style="max-height:50px; max-width:50px;" />
                  </div>
                </div>
                <div class="col-9">
                  <label class="form-label">Producto</label>
                  <select id="productoRapido" class="form-select text-light border-0">
                    <option value="">Seleccione producto</option>
                  </select>
                </div>
              </div>

              <div class="row g-2 mb-3 align-items-end">
                <div class="col-6">
                  <label class="form-label">Cantidad</label>
                  <input type="number" id="cantidadRapida" class="form-control text-light border-0" min="1" value="1" />
                </div>
                <div class="col-6">
                  <label class="form-label">Total</label>
                  <input type="text" id="totalRapida" class="form-control text-light border-0" readonly value="$0" />
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Método de Pago</label>
                <select id="metodoRapida" class="form-select text-light border-0">
                  <option value="Efectivo">Efectivo</option>
                  <option value="Transferencia">Transferencia</option>
                </select>
              </div>

              <input type="hidden" id="estadoRapida" value="Completada" />
              <button type="button" class="btn btn-success text-dark fw-bold" id="guardarRapidaBtn">Vender</button>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>



<script src="../../js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


</html>
@endsection
