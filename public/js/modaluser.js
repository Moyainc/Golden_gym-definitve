document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("addEditUserModal");
    const form = document.getElementById("formAddEditUser");
    const modalTitle = document.getElementById("addEditUserModalLabel");
    const saveButton = form.querySelector("button[type='submit']");
    const previewImg = document.getElementById("previewUserImage");

    // ⚙️ Función para limpiar el formulario
    const resetForm = () => {
        form.reset();
        document.getElementById("userId").value = "";
        previewImg.src = "{{ asset('storage/usuarios/default.png') }}";
        modalTitle.innerHTML = `<i data-feather="user-plus" class="me-1"></i> Nuevo usuario`;
        saveButton.innerHTML = `<i data-feather="save" class="me-1"></i> Guardar usuario`;
        feather.replace();
    };

    // 🧩 Botón "Agregar nuevo usuario"
    document.querySelectorAll(".addUserBtn").forEach((btn) => {
        btn.addEventListener("click", () => resetForm());
    });

    // 🧩 Botón "Editar"
    document.querySelectorAll(".editRole").forEach((btn) => {
        btn.addEventListener("click", async function () {
            const userId = this.dataset.id;

            // Cambiar encabezado y botón
            modalTitle.innerHTML = `<i data-feather="edit-3" class="me-1"></i> Editar usuario`;
            saveButton.innerHTML = `<i data-feather="save" class="me-1"></i> Actualizar usuario`;
            feather.replace();

            // Petición AJAX
            const response = await fetch(`${BASE_URL}/admin/usuarios/${userId}/edit`);
            const data = await response.json();

            // Rellenar campos
            document.getElementById("userId").value = data.id;
            document.getElementById("userName").value = data.nombre_completo;
            document.getElementById("userEmail").value = data.correo;
            document.getElementById("userRole").value = data.rol;
            document.getElementById("userCedula").value = data.cedula ?? "";
            previewImg.src = data.foto || "{{ asset('storage/usuarios/default.png') }}";
        });
    });

    // 📸 Vista previa imagen
    const fileInput = document.getElementById("userPhoto");
    fileInput?.addEventListener("change", (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => (previewImg.src = ev.target.result);
            reader.readAsDataURL(file);
        }
    });

    // 🔐 Mostrar / ocultar contraseña
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("userPassword");
    const eyeIcon = document.getElementById("eyeIcon");

    if (togglePassword && passwordInput && eyeIcon) {
        togglePassword.addEventListener("click", () => {
            const isPassword = passwordInput.type === "password";
            passwordInput.type = isPassword ? "text" : "password";
            eyeIcon.setAttribute("data-feather", isPassword ? "eye-off" : "eye");
            feather.replace();
        });
    }

    // 🚀 Envío del formulario (crear o actualizar)
    form.addEventListener("submit", async function (e) {
        e.preventDefault();
        const id = document.getElementById("userId").value;
        const formData = new FormData(form);

        const url = id ? `/admin/usuarios/${id}` : `/admin/usuarios`;
        const method = id ? "PUT" : "POST";

        const response = await fetch(url, {
            method,
            body: formData,
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            },
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: "success",
                title: result.message,
                confirmButtonColor: "#f8b400",
            }).then(() => location.reload());
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: result.message || "Error al guardar usuario",
                confirmButtonColor: "#e63946",
            });
        }
    });

    // 🔄 Reset al cerrar modal
    modal.addEventListener("hidden.bs.modal", resetForm);
});
