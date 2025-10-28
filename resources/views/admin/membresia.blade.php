@extends('layouts.app')
@section('title', 'Membresía - Golden Gym')
@section('content')

<body>
  <div class="wrapper">
    <div class="main">
      <main class="content">
        <div class="container-fluid p-0">
          <!-- Título y controles -->
          <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 text-white mb-0 fw-bold">
              <i data-feather="credit-card" class="me-2 tittle-color"></i>Membresías
            </h1>
            <button
              id="btnAddMembership"
              class="btn btn-warning tittle-color btn-sm p-2"
              data-bs-toggle="modal"
              data-bs-target="#membershipModal">
              <i data-feather="plus-circle" class="me-1"></i> Nueva membresía
            </button>
          </div>

          <!-- Resumen de métricas -->
          <!-- === TOTALES DE MEMBRESÍAS Y DIARIAS === -->
          <div class="row g-3 mb-4">
            <!-- Total en Membresías -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm bg-dark-card h-100">
                <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="text-muted text-uppercase small mb-1">Total en Membresías</h6>
                    <h5 id="totalMemberships" class="text-warning fw-bold mb-0">$0</h5>
                  </div>
                  <div class="bg-warning bg-opacity-25 p-2 rounded">
                    <i data-feather="credit-card" class="text-warning" width="20"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Total en Diarias -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm bg-dark-card h-100">
                <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="text-muted text-uppercase small mb-1">Total en Diarias</h6>
                    <h5 id="totalDiarias" class="text-warning fw-bold mb-0">$0</h5>
                  </div>
                  <div class="bg-warning bg-opacity-25 p-2 rounded">
                    <i data-feather="clock" class="text-warning" width="20"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Total General -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm bg-dark-card h-100">
                <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="text-muted text-uppercase small mb-1">Total General</h6>
                    <h5 id="totalGeneral" class="text-success fw-bold mb-0">$0</h5>
                  </div>
                  <div class="bg-success bg-opacity-25 p-2 rounded">
                    <i data-feather="trending-up" class="text-success" width="20"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Membresías Activas -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm bg-dark-card h-100">
                <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="text-muted text-uppercase small mb-1">Membresías Activas</h6>
                    <h5 id="activeMemberships" class="text-info fw-bold mb-0">$0</h5>
                  </div>
                  <div class="bg-info bg-opacity-25 p-2 rounded">
                    <i data-feather="users" class="text-info" width="20"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>


          <!-- Tabla de membresías -->
          <div class="card border-0 shadow-sm">
            <div
              class="card-header border-secondary d-flex justify-content-between align-items-center">
              <h6 class="mb-0 tittle-color">
                <i data-feather="list" class="me-1"></i>Listado de Membresías
              </h6>
            </div>

            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover align-middle border-0 mb-0">
                  <thead>
                    <tr class="text-muted small">
                      <th>Nombre</th>
                      <th>Duración en Meses</th>
                      <th>Valor</th>
                      <th>Estado</th>
                      <th class="text-end">Acciones</th>
                    </tr>
                  </thead>
                  <tbody id="membershipsTableBody">
                    <!-- Aquí el JS insertará las filas dinámicamente -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </main>

      <div class="modal fade" id="membershipModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
          <div class="modal-content bg-dark border-0 shadow-lg">
            <div class="modal-header bg-dark border-secondary">
              <h5 class="modal-title text-warning">
                <i data-feather="plus-circle" class="me-1"></i> Membresía
              </h5>
              <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <form id="membershipForm" class="row g-3">
                <div class="col-11">
                  <label class="form-label">Nombre *</label>
                  <input
                    name="nombre"
                    type="text"
                    class="form-control text-white border-0"
                    placeholder="Ej: Mensual, Trimestral, Diaria..."
                    required />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Duración *</label>
                  <input
                    name="duracion_meses"
                    type="number"
                    class="form-control text-white border-0"
                    placeholder="Ej: 30"
                    required />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Unidad de tiempo *</label>
                  <select class="form-select text-white border-0" required>
                    <option value="meses">Meses</option>
                  </select>
                </div>

                <div class="col-6">
                  <label class="form-label">Valor base *</label>
                  <input
                    name="precio"
                    type="number"
                    class="form-control text-white border-0"
                    placeholder="Ej: 80000"
                    required />
                </div>

                <div class="col-6">
                  <label class="form-label">Estado *</label>
                  <select name="activo" class="form-select text-white border-0" required>
                    <option value="Activa">Activa</option>
                    <option value="Inactiva">Inactiva</option>
                  </select>
                </div>
              </form>
            </div>

            <div class="modal-footer bg-dark border-secondary">
              <button class="btn btn-secondary" data-bs-dismiss="modal">
                Cancelar
              </button>
              <button class="btn btn-warning" id="saveMembership">
                <i data-feather="save" class="me-1"></i>Guardar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- MODAL CONFIRMAR ELIMINACIÓN -->
      <div
        class="modal fade"
        id="deleteMembershipModal"
        tabindex="-1"
        aria-labelledby="deleteMembershipModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-dark text-light border-0 shadow-lg">
            <div class="modal-header bg-dark border-secondary">
              <h5
                class="modal-title text-danger fw-bold"
                id="deleteMembershipModalLabel">
                <i data-feather="alert-triangle" class="me-2"></i>Confirmar
                Eliminación
              </h5>
              <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"
                aria-label="Cerrar"></button>
            </div>

            <div class="modal-body text-center">
              <p class="mb-3">
                ¿Estás seguro de que deseas eliminar esta membresía?
              </p>
              <p class="text-muted small">
                Esta acción no se puede deshacer.
              </p>
            </div>

            <div
              class="modal-footer border-secondary bg-dark">
              <button
                type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal">
                Cancelar
              </button>
              <button
                type="button"
                id="confirmDeleteBtn"
                class="btn btn-danger fw-bold">
                Sí, eliminar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>  
  <script src="{{ asset('js/membresias.js') }}"></script>
  <script src="../js/app.js"></script>
</body>
@endsection
