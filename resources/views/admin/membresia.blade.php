@extends('layouts.app')
@section('title', 'Membresía - Golden Gym')
@section('content')


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
            id="addMembershipBtn"
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
              <table class="table align-middle mt-3">
                <thead>
                  <tr class="text-uppercase text-secondary">
                    <th>Nombre</th>
                    <th>Duración</th>
                    <th>Tipo de duración</th>
                    <th>Precio</th>
                    <th class="text-end">Acciones</th>
                  </tr>
                </thead>

                <tbody id="membershipsTableBody">
                  @forelse ($membresias as $m)
                  <tr data-id="{{ $m->id }}">
                    <!-- Nombre -->
                    <td class="nombre">{{ $m->nombre ?? '—' }}</td>

                    <!-- Duración: solo el número -->
                    <td class="duracion" data-value="{{ $m->duracion }}">
                      {{ $m->duracion !== null && $m->duracion !== '' ? $m->duracion : '—' }}
                    </td>

                    <!-- Tipo de duración: muestra texto legible -->
                    <td class="tipo_duracion" data-value="{{ $m->tipo_duracion }}">
                      @if ($m->tipo_duracion === 'meses')
                      {{ $m->duracion == 1 ? 'Mes' : 'Meses' }}
                      @elseif ($m->tipo_duracion === 'dias')
                      {{ $m->duracion == 1 ? 'Día' : 'Días' }}
                      @else
                      —
                      @endif
                    </td>

                    <!-- Precio: formateado -->
                    <td class="precio text-nowrap" data-value="{{ $m->precio }}">
                      ${{ number_format((float) $m->precio, 0, ',', '.') }}
                    </td>

                    <!-- Acciones: botones con data-id -->
                    <td class="text-end">
                      <button
                        class="btn btn-sm btn-outline-light me-1 editMembership"
                        data-id="{{ $m->id }}"
                        title="Editar"
                        type="button">
                        <i data-feather="edit"></i>
                      </button>

                      <button
                        class="btn btn-sm btn-outline-danger deleteMembership"
                        data-id="{{ $m->id }}"
                        title="Eliminar"
                        type="button">
                        <i data-feather="trash-2"></i>
                      </button>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted py-3">
                      No hay membresías registradas.
                    </td>
                  </tr>
                  @endforelse
                </tbody>
              </table>

            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal compartido (Agregar / Editar) -->
    <div class="modal fade" id="membershipModal" tabindex="-1" aria-labelledby="membershipModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-0 shadow-lg">
          <div class="modal-header border-secondary">
            <h5 class="modal-title tittle-color" id="membershipModalLabel">
              <i data-feather="plus" class="me-1"></i> Nueva membresía
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>

          <div class="modal-body">
            <form id="membershipForm" enctype="multipart/form-data">
              @csrf
              <input type="hidden" id="membershipId" name="id" />

              <!-- Nombre -->
              <div class="mb-3">
                <label for="nombre" class="form-label text-white">Nombre</label>
                <input
                  type="text"
                  id="nombre"
                  name="nombre"
                  class="form-control text-white border-0"
                  placeholder="Ej. Mensual, Quincenal, Anual"
                  required />
              </div>

              <!-- Duración -->
              <div class="row">
                <div class="col-6 mb-3">
                  <label for="duracion" class="form-label text-white">Duración</label>
                  <input
                    type="number"
                    id="duracion"
                    name="duracion"
                    class="form-control text-white border-0"
                    min="1"
                    placeholder="Ej. 1"
                    required />
                </div>
                <div class="col-6 mb-3">
                  <label for="tipo_duracion" class="form-label text-white">Tipo de duración</label>
                  <select
                    id="tipo_duracion"
                    name="tipo_duracion"
                    class="form-select text-white border-0"
                    required>
                    <option value="meses">Meses</option>
                    <option value="dias">Días</option>
                  </select>
                </div>
              </div>

              <!-- Precio -->
              <div class="mb-3">
                <label for="precio" class="form-label text-white">Precio</label>
                <input
                  type="number"
                  id="precio"
                  name="precio"
                  class="form-control text-white border-0"
                  min="0"
                  step="0.01"
                  placeholder="Ej. 50000"
                  required />
                <small class="form-text text-secondary">
                  Digite el precio sin puntos ni comas.
                </small>
              </div>
            </form>
          </div>

          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal">
              Cancelar
            </button>
            <button
              id="saveMembership"
              type="button"
              class="btn btn-warning tittle-color">
              <i data-feather="save" class="me-1"></i> Guardar
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
@endsection
