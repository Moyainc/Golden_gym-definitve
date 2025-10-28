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

    // 🔄 Restablecer formulario
    const resetForm = (mode = "nuevo") => {
        form.reset();
        previewImg.src = DEFAULT_IMG;
        feather.replace();

        // Ocultar campos de contraseña y quitar botón "Cambiar contraseña"
        passwordGroup.classList.add("d-none");
        passwordGroup.classList.remove("d-flex");
        if (changePasswordBtn) {
            changePasswordBtn.remove();
            changePasswordBtn = null;
        }

        if (mode === "nuevo") {
            modalTitle.innerHTML = `<i data-feather="user-plus" class="me-1"></i> Nuevo usuario`;
            saveButton.innerHTML = `<i data-feather="save" class="me-1"></i> Guardar usuario`;
            form.action = "{{ route('admin.usuarios.store') }}";

            form.action = form.dataset.url;

            // Mostrar campos de contraseña en fila
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
                const response = await fetch(apiUrl(`/admin/usuarios/${userId}/edit`));
                if (!response.ok)
                    throw new Error(
                        "No se pudieron cargar los datos del usuario."
                    );

                const data = await response.json();

                // 🧩 Rellenar los campos
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

                // ⚙️ Ajustes del modal
                modalTitle.innerHTML = `<i data-feather="edit" class="me-1"></i> Editar usuario`;
                saveButton.innerHTML = `<i data-feather="save" class="me-1"></i> Actualizar usuario`;
                form.action = apiUrl(`/admin/usuarios/${userId}`);
                passwordGroup.style.display = "none"; // ocultar contraseñas

                // 🔁 Botón "Cambiar contraseña"
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
                    passwordGroup.classList.add("d-flex"); // mantener fila
                    changePasswordBtn.remove();
                    changePasswordBtn = null;
                });
            } catch (error) {
                console.error("❌ Error al cargar usuario:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudieron cargar los datos del usuario.",
                    confirmButtonColor: "#e63946",
                });
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
                method: isEdit ? "POST" : "POST",
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
                    confirmButtonColor: "#f8b400",
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
                Swal.fire({
                    icon: "warning",
                    title: "Errores de validación",
                    text: errores,
                    confirmButtonColor: "#f8b400",
                });
            } else {
                throw new Error(
                    result?.message || "Error al procesar el formulario."
                );
            }
        } catch (error) {
            console.error("❌ Error al enviar el formulario:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ocurrió un error inesperado al procesar el formulario.",
                confirmButtonColor: "#e63946",
            });
        }
    });

    // 🧹 Limpiar formulario al cerrar modal
    modal.addEventListener("hidden.bs.modal", () => resetForm("nuevo"));




    const deleteModal = document.getElementById("deleteUserModal");
    const deleteUserName = document.getElementById("deleteUserName");
    const confirmDeleteBtn = document.getElementById("confirmDeleteUser");
    let selectedUserId = null;

    // Abrir modal de eliminar
    document.querySelectorAll(".deleteRole").forEach((btn) => {
        btn.addEventListener("click", () => {
            selectedUserId = btn.dataset.id;
            const name = btn.dataset.name;
            deleteUserName.textContent = name;

            const bsModal = new bootstrap.Modal(deleteModal);
            bsModal.show();
        });
    });

    // Confirmar eliminación
    confirmDeleteBtn.addEventListener("click", async () => {
        if (!selectedUserId) return;

        try {
            const response = await fetch(apiUrl(`/admin/usuarios/${selectedUserId}`), {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                },
            });

            const result = await response.json();

            if (response.ok && result.success) {
                Swal.fire({
                    icon: "success",
                    title: "Usuario eliminado",
                    text: result.message,
                    confirmButtonColor: "#f8b400",
                }).then(() => {
                    const bsModal = bootstrap.Modal.getInstance(deleteModal);
                    bsModal.hide();
                    location.reload();
                });
            } else {
                throw new Error(result?.message || "Error al eliminar usuario");
            }
        } catch (error) {
            console.error("❌ Error al eliminar usuario:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ocurrió un error al eliminar el usuario.",
                confirmButtonColor: "#e63946",
            });
        }
    });
});
