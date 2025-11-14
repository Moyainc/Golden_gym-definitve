document.addEventListener("DOMContentLoaded", () => {
    // ----------------------------
    // Modal: Crear / Editar Entrenador
    // ----------------------------
    const addBtn = document.getElementById("btnAddTrainer");
    const trainerForm = document.getElementById("trainerForm");
    const modalTitle = document.getElementById("trainerModalTitle");
    const preview = document.getElementById("previewImage");
    const idInput = document.getElementById("trainerId");
    const nameInput = document.getElementById("trainerName");
    const descInput = document.getElementById("trainerDescription");
    const imageInput = document.getElementById("trainerImageInput");

    if (addBtn) {
        addBtn.addEventListener("click", () => {
            modalTitle.textContent = "Nuevo Entrenador";
            trainerForm.reset();
            idInput.value = "";
            preview.classList.add("d-none");
            preview.src = "";
            trainerForm.action = apiUrl("/admin/entrenadores"); // Ruta store
            trainerForm.removeAttribute("data-editing");

            const methodInput = document.querySelector(
                "#trainerForm input[name=_method]"
            );
            if (methodInput) methodInput.remove();
        });
    }

    if (imageInput) {
        const previewContainer = document.getElementById("previewContainer");

        imageInput.addEventListener("change", (e) => {
            const file = e.target.files[0];

            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove("d-none");
                previewContainer.classList.remove("d-none"); // mostrar contenedor
            } else {
                preview.src = "";
                preview.classList.add("d-none");
                previewContainer.classList.add("d-none"); // ocultar contenedor si no hay imagen
            }
        });
    }

    // ----------------------------
    // Validación de Entrenador
    // ----------------------------
    if (trainerForm) {
        trainerForm.addEventListener("submit", (event) => {
            const name = nameInput.value.trim();
            const desc = descInput.value.trim();

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
    }

    // ----------------------------
    // Modal: Editar Entrenador
    // ----------------------------
    window.editTrainer = function (btn) {
        const id = btn.getAttribute("data-id");
        const name = btn.getAttribute("data-name");
        const desc = btn.getAttribute("data-description");
        const img = btn.getAttribute("data-image");

        const modal = new bootstrap.Modal(
            document.getElementById("trainerModal")
        );

        modalTitle.textContent = "Editar Entrenador";
        idInput.value = id;
        nameInput.value = name;
        descInput.value = desc;

        preview.src = "/storage/" + img;
        preview.classList.remove("d-none");

        trainerForm.action = apiUrl("/admin/entrenadores/") + id;

        let existingMethod = document.querySelector(
            "#trainerForm input[name=_method]"
        );
        if (!existingMethod) {
            const methodInput = document.createElement("input");
            methodInput.type = "hidden";
            methodInput.name = "_method";
            methodInput.value = "PUT";
            trainerForm.appendChild(methodInput);
        } else {
            existingMethod.value = "PUT";
        }

        modal.show();
    };

    // ----------------------------
    // Confirmación de eliminación
    // ----------------------------
    window.confirmDelete = function (event, form) {
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
    };

    // ----------------------------
    // Formulario de Redes Sociales
    // ----------------------------
    const socialForm = document.getElementById("socialLinksForm");
    if (socialForm) {
        const facebookInput = document.getElementById("facebook");
        const instagramInput = document.getElementById("instagram");
        const twitterInput = document.getElementById("twitter");
        const youtubeInput = document.getElementById("youtube");

        // Precargar links desde BD
        fetch(apiUrl("/admin/get-links"))
            .then((res) => res.json())
            .then((data) => {
                if (data) {
                    facebookInput.value = data.facebook ?? "";
                    instagramInput.value = data.instagram ?? "";
                    twitterInput.value = data.twitter ?? "";
                    youtubeInput.value = data.youtube ?? "";
                }
            })
            .catch((err) => console.error("Error al cargar enlaces:", err));

        // Enviar formulario
        socialForm.addEventListener("submit", (e) => {
            e.preventDefault();

            const facebook = facebookInput.value.trim();
            const instagram = instagramInput.value.trim();
            const twitter = twitterInput.value.trim();
            const youtube = youtubeInput.value.trim();

            if (!facebook && !instagram && !twitter && !youtube) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Debe llenar al menos un enlace.",
                    background: "#1a1a1a",
                    color: "#fff",
                    confirmButtonColor: "#fcd52b",
                    confirmButtonText: "Aceptar",
                    customClass: { confirmButton: "text-black font-bold" },
                });
                return;
            }

            const data = {};
            if (facebook) data.facebook = facebook;
            if (instagram) data.instagram = instagram;
            if (twitter) data.twitter = twitter;
            if (youtube) data.youtube = youtube;

            fetch(apiUrl("/admin/social-links"), {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        "meta[name='csrf-token']"
                    ).content,
                },
                body: JSON.stringify(data),
            })
                .then((res) => res.json())
                .then((response) => {
                    Swal.fire({
                        icon: "success",
                        title: "Éxito",
                        text: "Los enlaces de redes sociales se han guardado correctamente.",
                        background: "#1a1a1a",
                        color: "#fff",
                        confirmButtonColor: "#fcd52b",
                        confirmButtonText: "Aceptar",
                        customClass: { confirmButton: "text-black font-bold" },
                    });
                })
                .catch((err) => {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un problema al guardar los enlaces.",
                        background: "#1a1a1a",
                        color: "#fff",
                        confirmButtonColor: "#fcd52b",
                        confirmButtonText: "Aceptar",
                        customClass: { confirmButton: "text-black font-bold" },
                    });
                });
        });
    }
});
