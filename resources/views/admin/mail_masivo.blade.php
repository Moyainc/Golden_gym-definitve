@extends('layouts.app')
@section('title', 'Enviar Correo - Golden Gym')

@section('content')

<div class="wrapper">
  <div class="main">
    <main class="content">
      <div class="container-fluid p-0">


        <div class="d-flex align-items-center justify-content-between mb-4">
          <h1 class="h3 text-white mb-0 fw-bold">
            <i data-feather="mail" class="me-2 tittle-color"></i>Enviar Correo
          </h1>
        </div>

        <div class="row g-3">
          <div class="col-12">
            <div class="card border-0 shadow-lg rounded-3">
              <div class="card-header border-0 d-flex justify-content-between align-items-center ">
                <h6 class="mb-0 text-warning fw-bold">
                  <i data-feather="edit" class="me-1"></i> Redactar Correo
                </h6>
              </div>

              <div class="card-body">
                <form id="emailForm" class="row g-3" enctype="multipart/form-data">
                  <div class="col-12">
                    <label for="emailSubject" class="form-label text-white fw-semibold">Asunto *</label>
                    <input type="text" id="emailSubject" name="subject" class="form-control text-white border-0 " required placeholder="Ingresa el asunto del correo">
                  </div>


                  <div class="col-12">
                    <label for="emailMessage" class="form-label text-white fw-semibold">Mensaje *</label>
                    <textarea id="emailMessage" name="message" rows="6" class="form-control text-white border-0 " required placeholder="Escribe tu mensaje aquí"></textarea>
                  </div>


                  <div class="col-md-6">
                    <label for="emailGroup" class="form-label text-white fw-semibold">Seleccionar Grupo *</label>
                    <select id="emailGroup" name="group" class="form-select text-white border-0" required>
                      <option value="">-- Seleccione un grupo --</option>
                      <option value="todos">Todos los clientes activos</option>
                      <option value="activos_mensual">Clientes con membresía activa mensual</option>
                      <option value="proxima_mensual">Clientes con membresía mensual próximos</option>
                      <option value="proxima_vencer">Clientes con membresía próxima a vencer</option>
                      <option value="vencido">Clientes con membresía vencida</option>
                      <option value="sin_membresia">Clientes sin membresía</option>
                      <option value="administradores">Usuarios administradores</option>
                      <option value="recepcionistas">Usuarios recepcionistas</option>
                    </select>
                  </div>


                  <div class="col-md-6">
                    <label for="specificClientEmail" class="form-label text-white fw-semibold">
                      O escribir correo de cliente/usuario específico
                    </label>
                    <input type="email" id="specificClientEmail" name="client_email" class="form-control text-white border-0" placeholder="correo@cliente.com">
                    <small class="text-muted">Si escribes un correo aquí, se ignorará la selección del grupo.</small>
                  </div>


                  <div class="col-12">
                    <label class="form-label text-white fw-semibold">Adjuntar imágenes</label>
                    <div class="d-flex align-items-center gap-3">
                      <input
                        id="emailImages"
                        name="images[]"
                        type="file"
                        accept="image/*"
                        multiple
                        class="form-control text-white border-0" />
                    </div>
                    <small class="text-muted">Puedes adjuntar varias imágenes (jpg, png, gif).</small>
                    <div id="imagePreviewContainer" class="mt-2 d-flex flex-wrap gap-2"></div>
                  </div>

                  <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                    <button type="submit" id="sendEmailBtn" class="btn btn-warning">
                      <i data-feather="send" class="me-1"></i> Enviar Correo
                    </button>
                  </div>

                </form>
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>
</div>

<script src="{{ asset('js/correos.js') }}"></script>

@endsection
