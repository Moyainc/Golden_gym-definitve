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

    // 🔄 Restablecer formulario
    const resetForm = () => {
        form.reset();
        previewImg.src = DEFAULT_IMG;
        modalTitle.innerHTML = `<i data-feather="user-plus" class="me-1"></i> Nuevo usuario`;
        saveButton.innerHTML = `<i data-feather="save" class="me-1"></i> Guardar usuario`;
        feather.replace();
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

    // 🚀 Envío del formulario por AJAX
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                },
            });

            // Intentar convertir a JSON solo si la respuesta tiene contenido
            let result;
            try {
                result = await response.json();
            } catch {
                result = null;
            }

            if (response.ok && result?.success) {
                Swal.fire({
                    icon: "success",
                    title: "Usuario registrado",
                    text: result.message,
                    confirmButtonColor: "#f8b400",
                }).then(() => {
                    resetForm();
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    bsModal.hide();
                    location.reload();
                });
            } else if (response.status === 422 && result?.errors) {
                // ⚠️ Mostrar errores de validación
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
                    result?.message || "Error al registrar el usuario"
                );
            }
        } catch (error) {
            console.error("❌ Error al enviar el formulario:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ocurrió un error inesperado al registrar el usuario.",
                confirmButtonColor: "#e63946",
            });
        }
    });

    // 🧹 Limpiar formulario al cerrar modal
    modal.addEventListener("hidden.bs.modal", resetForm);
});
