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
        body: JSON.stringify({ segmento })
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