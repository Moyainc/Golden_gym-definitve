// Función: Abrir modal para crear entrenador
document.addEventListener("DOMContentLoaded", () => {
    const addBtn = document.getElementById("btnAddTrainer");
    const form = document.getElementById("trainerForm");
    const modalTitle = document.getElementById("trainerModalTitle");
    const preview = document.getElementById("previewImage");
    const idInput = document.getElementById("trainerId");
    const nameInput = document.getElementById("trainerName");
    const descInput = document.getElementById("trainerDescription");
    const imageInput = document.getElementById("trainerImageInput");

    if (addBtn) {
        addBtn.addEventListener("click", () => {
            modalTitle.textContent = "Nuevo Entrenador";

            form.reset();
            idInput.value = "";
            preview.classList.add("d-none");
            preview.src = "";

            form.action = apiUrl("/admin/entrenadores"); // Ruta store
            form.removeAttribute("data-editing"); // Marcar como creación

            const methodInput = document.querySelector(
                "#trainerForm input[name=_method]"
            );
            if (methodInput) methodInput.remove();
        });
    }

    // Vista previa de imagen
    if (imageInput) {
        imageInput.addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove("d-none");
            }
        });
    }
});

// Función: Abrir modal para editar entrenador
function editTrainer(btn) {
    const id = btn.getAttribute("data-id");
    const name = btn.getAttribute("data-name");
    const desc = btn.getAttribute("data-description");
    const img = btn.getAttribute("data-image");

    const modal = new bootstrap.Modal(document.getElementById("trainerModal"));

    const form = document.getElementById("trainerForm");
    const preview = document.getElementById("previewImage");

    document.getElementById("trainerModalTitle").textContent =
        "Editar Entrenador";

    document.getElementById("trainerId").value = id;
    document.getElementById("trainerName").value = name;
    document.getElementById("trainerDescription").value = desc;

    preview.src = "/storage/" + img;
    preview.classList.remove("d-none");

    form.action = apiUrl("/admin/entrenadores/") + id;

    const existingMethod = document.querySelector(
        "#trainerForm input[name=_method]"
    );
    if (!existingMethod) {
        const methodInput = document.createElement("input");
        methodInput.type = "hidden";
        methodInput.name = "_method";
        methodInput.value = "PUT";
        form.appendChild(methodInput);
    } else {
        existingMethod.value = "PUT";
    }

    modal.show();
}

// Confirmación de eliminación con tu plantilla SweetAlert2
function confirmDelete(event, form) {
    event.preventDefault();

    Swal.fire({
        icon: "warning",
        title: "¿Eliminar Entrenador?",
        text: "Esta acción no se puede deshacer.",
        background: "#1a1a1a",
        color: "#fff",
        showCancelButton: true,
        confirmButtonColor: "#fcd52b",
        cancelButtonColor: "#444",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        customClass: {
            confirmButton: "text-black font-bold",
            cancelButton: "text-light",
        },
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}


// Validaciones frontend antes de enviar
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("trainerForm");

    form.addEventListener("submit", function (event) {
        const name = document.getElementById("trainerName").value.trim();
        const desc = document.getElementById("trainerDescription").value.trim();

        if (name.length < 3) {
            event.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "El nombre debe tener al menos 3 caracteres.",
                background: "#1a1a1a",
                color: "#fff",
                confirmButtonColor: "#fcd52b",
                confirmButtonText: "Aceptar",
                customClass: { confirmButton: "text-black font-bold" },
            });
            return;
        }

        if (desc.length < 10) {
            event.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "La descripción debe contener al menos 10 caracteres.",
                background: "#1a1a1a",
                color: "#fff",
                confirmButtonColor: "#fcd52b",
                confirmButtonText: "Aceptar",
                customClass: { confirmButton: "text-black font-bold" },
            });
            return;
        }
    });
});
