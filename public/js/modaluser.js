document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("addEditUserModal");
    const form = document.getElementById("formAddEditUser");
    const previewImg = document.getElementById("previewUserImage");
    const fileInput = document.getElementById("userPhoto");
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("userPassword");
    const eyeIcon = document.getElementById("eyeIcon");
    const modalTitle = document.getElementById("addEditUserModalLabel");
    const saveButton = form.querySelector("button[type='submit']");
    const DEFAULT_IMG = "{{ asset('storage/usuarios/default.png') }}";

    // Campos de contraseña
    const passwordGroup = document.querySelectorAll(".row.mb-3")[2]; // bloque de contraseñas
    let changePasswordBtn = null;

    const apiUrl = (path) => {
        const base = window.app?.baseUrl || window.location.origin;
        return `${base}/${path.replace(/^\/+/, "")}`;
    };

    // ---------------------- CONFIGURACIÓN GLOBAL SWEETALERT ----------------------
    const SwalGlobal = Swal.mixin({
        background: "#1a1a1a",
        color: "#fff",
        confirmButtonColor: "#fcd52b",
        confirmButtonText: "Aceptar",
        customClass: {
            confirmButton: "text-black font-bold",
        },
    });

    const showAlert = (type, message, title = null) => {
        const icons = {
            error: "error",
            warning: "warning",
            success: "success",
            info: "info",
        };
        const colors = {
            error: "#e63946",
            warning: "#f8b400",
            success: "#fcd52b",
            info: "#4db6ac",
        };
        SwalGlobal.fire({
            icon: icons[type] || "info",
            title: title || (type === "success" ? "Éxito" : "Error"),
            text: message,
            confirmButtonColor: colors[type] || "#fcd52b",
        });
    };

    // 🔄 Restablecer formulario
    const resetForm = (mode = "nuevo") => {
        form.reset();
        previewImg.src = DEFAULT_IMG;
        feather.replace();

        passwordGroup.classList.add("d-none");
        passwordGroup.classList.remove("d-flex");
        if (changePasswordBtn) {
            changePasswordBtn.remove();
            changePasswordBtn = null;
        }

        if (mode === "nuevo") {
            modalTitle.innerHTML = `<i data-feather="user-plus" class="me-1"></i> Nuevo usuario`;
            saveButton.innerHTML = `<i data-feather="save" class="me-1"></i> Guardar usuario`;
            form.action = form.dataset.url;

            passwordGroup.classList.remove("d-none");
            passwordGroup.classList.add("d-flex");
        }
    };

    // 📸 Vista previa imagen
    fileInput?.addEventListener("change", (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => (previewImg.src = ev.target.result);
            reader.readAsDataURL(file);
        } else {
            previewImg.src = DEFAULT_IMG;
        }
    });

    // 🔐 Mostrar / ocultar contraseña
    togglePassword?.addEventListener("click", () => {
        const isHidden = passwordInput.type === "password";
        passwordInput.type = isHidden ? "text" : "password";
        eyeIcon.setAttribute("data-feather", isHidden ? "eye-off" : "eye");
        feather.replace();
    });

    // ✏️ Modo edición
    document.querySelectorAll(".editRole").forEach((btn) => {
        btn.addEventListener("click", async () => {
            const userId = btn.dataset.id;
            resetForm("editar");

            try {
                const response = await fetch(
                    apiUrl(`/admin/usuarios/${userId}/edit`)
                );
                if (!response.ok)
                    throw new Error(
                        "No se pudieron cargar los datos del usuario."
                    );

                const data = await response.json();

                document.getElementById("userId").value = data.id;
                document.getElementById("userName").value =
                    data.nombre_completo;
                document.getElementById("userCedula").value = data.cedula;
                document.getElementById("userEmail").value = data.correo;
                document.getElementById("userPhone").value =
                    data.telefono || "";
                document.getElementById("userRole").value = data.rol;
                previewImg.src = data.foto
                    ? `/storage/${data.foto}`
                    : DEFAULT_IMG;

                modalTitle.innerHTML = `<i data-feather="edit" class="me-1"></i> Editar usuario`;
                saveButton.innerHTML = `<i data-feather="save" class="me-1"></i> Actualizar usuario`;
                form.action = apiUrl(`/admin/usuarios/${userId}`);
                passwordGroup.style.display = "none";

                changePasswordBtn = document.createElement("button");
                changePasswordBtn.type = "button";
                changePasswordBtn.className = "btn btn-outline-warning mb-3";
                changePasswordBtn.innerHTML = `<i data-feather="lock"></i> Cambiar contraseña`;
                passwordGroup.parentElement.insertBefore(
                    changePasswordBtn,
                    passwordGroup
                );

                feather.replace();

                changePasswordBtn.addEventListener("click", () => {
                    passwordGroup.classList.remove("d-none");
                    passwordGroup.classList.add("d-flex");
                    changePasswordBtn.remove();
                    changePasswordBtn = null;
                });
            } catch (error) {
                console.error("❌ Error al cargar usuario:", error);
                showAlert(
                    "error",
                    "No se pudieron cargar los datos del usuario."
                );
            }
        });
    });

    // 🚀 Envío del formulario (crear o editar)
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const userId = document.getElementById("userId").value;
        const isEdit = !!userId;

        try {
            const response = await fetch(form.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    "X-HTTP-Method-Override": isEdit ? "PUT" : "POST",
                },
            });

            let result;
            try {
                result = await response.json();
            } catch {
                result = null;
            }

            if (response.ok && result?.success) {
                Swal.fire({
                    icon: "success",
                    title: isEdit
                        ? "Usuario actualizado"
                        : "Usuario registrado",
                    text: result.message,
                    background: "#1a1a1a",
                    color: "#fff",
                    confirmButtonColor: "#fcd52b",
                    confirmButtonText: "Aceptar",
                    customClass: { confirmButton: "text-black font-bold" },
                }).then(() => {
                    resetForm();
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    bsModal.hide();
                    location.reload();
                });
            } else if (response.status === 422 && result?.errors) {
                const errores = Object.values(result.errors)
                    .map((e) => e.join("\n"))
                    .join("\n\n");
                showAlert("warning", errores, "Errores de validación");
            } else
                throw new Error(
                    result?.message || "Error al procesar el formulario."
                );
        } catch (error) {
            console.error("❌ Error al enviar el formulario:", error);
            showAlert(
                "error",
                "Ocurrió un error inesperado al procesar el formulario."
            );
        }
    });

    // 🧹 Limpiar formulario al cerrar modal
    modal.addEventListener("hidden.bs.modal", () => resetForm("nuevo"));

    // ---------------------- ELIMINACIÓN DE USUARIOS CON SWEETALERT ----------------------
    document.querySelectorAll(".deleteRole").forEach((btn) => {
        btn.addEventListener("click", async () => {
            const userId = btn.dataset.id;
            const userName = btn.dataset.name;

            const confirmDelete = await SwalGlobal.fire({
                title: `Eliminar usuario: ${userName}`,
                text: "¿Seguro que deseas eliminar este usuario?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
                confirmButtonColor: "#fcd52b",
                cancelButtonColor: "#e63946",
                customClass: {
                    confirmButton: "text-black font-bold",
                    cancelButton: "text-white font-bold",
                },
            });

            if (!confirmDelete.isConfirmed) return;

            try {
                const response = await fetch(
                    apiUrl(`/admin/usuarios/${userId}`),
                    {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,
                        },
                    }
                );

                const result = await response.json();

                if (response.ok && result.success) {
                    await showAlert(
                        "success",
                        result.message || "Usuario eliminado correctamente"
                    );
                    location.reload();
                } else
                    throw new Error(
                        result?.message || "Error al eliminar usuario"
                    );
            } catch (error) {
                console.error("❌ Error al eliminar usuario:", error);
                showAlert("error", "Ocurrió un error al eliminar el usuario.");
            }
        });
    });
});
