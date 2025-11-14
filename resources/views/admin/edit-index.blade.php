@extends('layouts.app')
@section('title', 'Entrenadores - Golden Gym')

@section('content')
<div class="wrapper">
  <div class="main">
    <main class="content">
      <div class="container-fluid p-0">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="h3 text-white fw-bold mb-0">
            <i data-feather="users" class="me-2 tittle-color"></i>
            Gestión de Entrenadores
          </h1>

          @if ($entrenadores->count() >= 6)
          <button class="btn btn-secondary fw-bold text-light shadow-sm" disabled>
            <i data-feather="x-circle" class="me-1"></i>
            Límite alcanzado
          </button>
          @else
          <button id="btnAddTrainer"
            class="btn btn-warning text-dark fw-bold shadow-sm d-flex align-items-center justify-content-center"
            data-bs-toggle="modal"
            data-bs-target="#trainerModal">
            <i data-feather="user-plus" class="me-1"></i>
            <span class="text-truncate">Nuevo Entrenador</span>
          </button>
          @endif
        </div>

        <div class="row g-4">

          <!-- CARD: ENTRENADORES -->
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm bg-dark-card">
              <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                <h5 class="text-warning mb-0">
                  <i data-feather="users" class="me-1"></i> Lista de Entrenadores
                </h5>
                <span class="badge bg-warning text-dark fw-bold"> Máximo 6 </span>
              </div>

              <div class="card-body">

                <div class="row g-3" id="trainerList">
                  @foreach ($entrenadores as $trainer)
                  <div class="col-md-6">
                    <div class="card trainer-card shadow-sm bg-dark border-secondary h-100">

                      <!-- Imagen -->
                      <img src="{{ asset('storage/' . $trainer->imagen) }}"
                        class="card-img-top trainer-img"
                        alt="Imagen entrenador">

                      <!-- Contenido -->
                      <div class="card-body">
                        <h5 class="text-warning fw-bold">{{ $trainer->nombre }}</h5>
                        <p class="text-muted small">{{ $trainer->descripcion }}</p>
                      </div>

                      <!-- Acciones -->
                      <div class="card-footer d-flex justify-content-between border-secondary">

                        <!-- Editar -->
                        <button
                          class="btn btn-sm btn-info text-dark fw-bold"
                          data-id="{{ $trainer->id }}"
                          data-name="{{ $trainer->nombre }}"
                          data-description="{{ $trainer->descripcion }}"
                          data-image="{{ $trainer->imagen }}"
                          onclick="editTrainer(this)">
                          <i data-feather="edit"></i>
                        </button>

                        <!-- Eliminar -->
                        <form method="POST" action="{{ route('entrenadores.destroy', $trainer->id) }}"
                          onsubmit="confirmDelete(event, this)">
                          @csrf
                          @method('DELETE')
                          <button class="btn btn-sm btn-danger fw-bold">
                            <i data-feather="trash"></i>
                          </button>
                        </form>

                      </div>
                    </div>
                  </div>
                  @endforeach
                </div>

                @if ($entrenadores->count() === 0)
                <div class="text-center py-5">
                  <i data-feather="slash" class="text-secondary mb-2" style="width:40px;height:40px;"></i>
                  <p class="text-muted">No hay entrenadores registrados todavía.</p>
                </div>
                @endif

              </div>
            </div>
          </div>

          <!-- CARD: REDES SOCIALES -->
          <div class="col-lg-4">
            <div class="card border-0 shadow-sm bg-dark-card">
              <div class="card-header border-secondary">
                <h5 class="text-warning mb-0">
                  <i data-feather="share-2" class="me-1"></i>
                  Enlaces de Redes Sociales
                </h5>
              </div>

              <div class="card-body">

                <form method="POST" action="">
                  @csrf

                  <label class="form-label text-muted">Facebook</label>
                  <input type="text" name="facebook" class="form-control input-dark mb-3"
                    value="">

                  <label class="form-label text-muted">Instagram</label>
                  <input type="text" name="instagram" class="form-control input-dark mb-3"
                    value="">

                  <label class="form-label text-muted">Twitter</label>
                  <input type="text" name="twitter" class="form-control input-dark mb-3"
                    value="">

                  <label class="form-label text-muted">YouTube</label>
                  <input type="text" name="youtube" class="form-control input-dark mb-3"
                    value="">

                  <button class="btn btn-warning fw-bold text-dark w-100 mt-3">
                    <i data-feather="save" class="me-1"></i> Guardar Cambios
                  </button>
                </form>

              </div>
            </div>
          </div>

        </div>

      </div>
    </main>
  </div>
</div>

<!-- MODAL: CREAR / EDITAR ENTRENADOR -->
<div class="modal fade modal-dark-style" id="trainerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-light border-secondary">

      <div class="modal-header border-secondary">
        <h5 class="modal-title text-warning fw-bold">
          <i data-feather="user-plus" class="me-1"></i>
          <span id="trainerModalTitle">Nuevo Entrenador</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form id="trainerForm" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" id="trainerId">

        <div class="modal-body">

          <img id="previewImage"
            src=""
            class="img-fluid rounded mb-3 d-none border border-secondary"
            alt="Vista previa">

          <label class="form-label text-muted">Nombre</label>
          <input type="text" name="nombre" id="trainerName"
            class="form-control text-light bg-dark border-secondary mb-3" required>

          <label class="form-label text-muted">Descripción</label>
          <textarea name="descripcion" id="trainerDescription"
            class="form-control text-light bg-dark border-secondary mb-3" rows="3"></textarea>

          <label class="form-label text-muted">Imagen</label>
          <input type="file" name="imagen" id="trainerImageInput"
            class="form-control text-light bg-dark border-secondary">
        </div>

        <div class="modal-footer border-secondary">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

          <button type="submit" class="btn btn-warning fw-bold text-dark">Guardar</button>
        </div>

      </form>
    </div>
  </div>
</div>

<script src="{{ asset('js/edit-index.js') }}"></script>
@endsection
