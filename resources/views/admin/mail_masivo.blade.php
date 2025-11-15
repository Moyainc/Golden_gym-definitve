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

                                <form id="emailForm"
                                    class="row g-3"
                                    enctype="multipart/form-data"
                                    method="POST"
                                    action="{{ route('admin.mail_masivo.send') }}">

                                    @csrf

                                    <div class="col-12">
                                        <label class="form-label text-white fw-semibold">Asunto *</label>
                                        <input type="text" name="asunto" class="form-control text-white border-0"
                                            required placeholder="Ingresa el asunto del correo">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label text-white fw-semibold">Mensaje *</label>
                                        <textarea name="mensaje" rows="6" class="form-control text-white border-0"
                                            required placeholder="Escribe tu mensaje aquí"></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label text-white fw-semibold">Seleccionar Grupo *</label>
                                        <select name="segmento" class="form-select text-white border-0" required>
                                            <option value="">-- Seleccione un grupo --</option>

                                            {{-- SOLO GRUPOS EXISTENTES EN EL CONTROLADOR --}}
                                            <option value="todos">Todos</option>
                                            <option value="clientes">Clientes</option>
                                            <option value="usuarios">Usuarios</option>
                                            <option value="membresias_activas">Membresías Activas</option>
                                            <option value="membresias_proximas">Membresías Próximas</option>
                                            <option value="membresias_vencidas">Membresías Vencidas</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label text-white fw-semibold">Adjuntar imágenes (opcional)</label>
                                        <input name="imagenes[]" type="file" accept="image/*" multiple
                                            class="form-control text-white border-0" />
                                        <small class="text-muted">Puedes adjuntar varias imágenes (jpg, png, gif).</small>
                                        <div id="imagePreviewContainer" class="mt-2 d-flex flex-wrap gap-2"></div>
                                    </div>

                                    <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                                        <button type="reset" class="btn btn-secondary">Cancelar</button>
                                        <button type="submit" class="btn btn-warning">
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
        <!-- MODAL DE CONFIRMACIÓN -->
        <!-- MODAL DE CONFIRMACIÓN -->
        <div class="modal fade modal-dark-style" id="confirmModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content bg-dark text-light border-warning">

                    <!-- HEADER igual al de productos -->
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title tittle-color fw-bold">
                            <i data-feather="send"></i> Confirmar Envío de Correo
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <p><strong>Asunto:</strong> <span id="previewAsunto"></span></p>

                        <p><strong>Mensaje:</strong></p>
                        <div id="previewMensaje" class="p-2 border rounded border-secondary"></div>

                        <p class="mt-3"><strong>Grupo seleccionado:</strong>
                            <span id="previewGrupo" class="text-info"></span>
                        </p>

                        <p class="mt-3"><strong>Total destinatarios:</strong>
                            <span id="previewCantidad" class="text-warning fw-bold"></span>
                        </p>

                        <p class="mt-3"><strong>Imágenes adjuntas:</strong></p>
                        <div id="previewImagenes" class="d-flex flex-wrap gap-2"></div>

                    </div>

                    <!-- FOOTER igual al de productos -->
                    <div class="modal-footer border-secondary">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button id="confirmSendBtn" class="btn btn-warning text-dark fw-bold">
                            Confirmar y Enviar
                        </button>
                    </div>

                </div>
            </div>
        </div>


    </div>
</div>

<script>
    document.getElementById("emailForm").addEventListener("submit", function(e) {
        e.preventDefault(); // Detener envío

        let asunto = document.querySelector("input[name='asunto']").value;
        let mensaje = document.querySelector("textarea[name='mensaje']").value;
        let segmento = document.querySelector("select[name='segmento']").value;
        let imagenes = document.querySelector("input[name='imagenes[]']").files;

        if (!segmento) {
            alert("Debes seleccionar un segmento");
            return;
        }

        // 1. Consultar cantidad por AJAX
        fetch("{{ route('admin.mail_masivo.count') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    segmento
                })
            })
            .then(res => res.json())
            .then(data => {

                // Llenar modal
                document.getElementById("previewAsunto").textContent = asunto;
                document.getElementById("previewMensaje").innerHTML = mensaje.replace(/\n/g, "<br>");
                document.getElementById("previewGrupo").textContent = segmento;
                document.getElementById("previewCantidad").textContent = data.cantidad;

                // Mostrar imágenes
                let cont = document.getElementById("previewImagenes");
                cont.innerHTML = "";
                for (let img of imagenes) {
                    let url = URL.createObjectURL(img);
                    cont.innerHTML += `<img src="${url}" class="rounded border" width="100">`;
                }

                // Abrir modal
                let modal = new bootstrap.Modal(document.getElementById("confirmModal"));
                modal.show();

                // Confirmar envío real
                document.getElementById("confirmSendBtn").onclick = () => {
                    modal.hide();
                    e.target.submit(); // Enviar formulario definitivo
                };
            });

    });
</script>

@endsection