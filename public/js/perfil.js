document.addEventListener("DOMContentLoaded", () => {
    const profileForm = document.getElementById("profileForm");
    const inputPhoto = document.getElementById("inputPhoto");
    const profileImagePreview = document.getElementById("profileImagePreview");
    const btnChangePhoto = document.getElementById("btnChangePhoto");
    const btnDeletePhoto = document.getElementById("btnDeletePhoto");

    const passwordForm = document.getElementById("passwordChangeForm");
    const currentPassword = document.getElementById("currentPassword");
    const newPassword = document.getElementById("newPassword");
    const confirmPassword = document.getElementById("confirmPassword");
    const passwordStrength = document.getElementById("passwordStrength");
    const passwordMatch = document.getElementById("passwordMatch");

    const apiUrl = (path) => {
        const base = window.app?.baseUrl || window.location.origin;
        return `${base}/${path.replace(/^\/+/, "")}`;
    };

    // ---------------------- UTILIDADES ----------------------
    const isEmailValid = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    const isPhoneValid = (phone) => /^\d{7,15}$/.test(phone); // ajusta según tus reglas

    const evaluatePasswordStrength = (password) => {
        let score = 0;
        if (password.length >= 8) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[a-z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[\W]/.test(password)) score++;
        if (score <= 2) return "Débil";
        if (score <= 4) return "Media";
        return "Fuerte";
    };

    // ---------------------- CARGAR PERFIL ----------------------
    fetch(apiUrl("/admin/perfil"))
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                const user = data.user;
                document.getElementById("userName").value =
                    user.nombre_completo;
                document.getElementById("userEmail").value = user.correo;
                document.getElementById("userPhone").value = user.telefono;
                document.getElementById("userCedula").value = user.cedula;
                document.getElementById("profileUserName").innerText =
                    user.nombre_completo;
                document.getElementById("profileUserRole").innerText = user.rol;
                document.getElementById("profileCreatedAt").innerText =
                    "Último acceso: " + user.ultimo_acceso || "N/A";
                profileImagePreview.src =
                    user.foto_url || "{{ asset('img/avatars/avatar-4.jpg') }}";
                if (user.foto_url) {
                    const btnDeletePhoto =
                        document.getElementById("btnDeletePhoto");
                    btnDeletePhoto.classList.remove("d-none");
                }
            }
        });

    // ---------------------- FOTO DE PERFIL ----------------------
    btnChangePhoto.addEventListener("click", () => inputPhoto.click());

    inputPhoto.addEventListener("change", () => {
        const file = inputPhoto.files[0];
        if (file) profileImagePreview.src = URL.createObjectURL(file);
    });

    if (btnDeletePhoto) {
        btnDeletePhoto.addEventListener("click", async () => {
            const confirmDelete = await Swal.fire({
                title: "Eliminar foto",
                text: "¿Seguro que deseas eliminar tu foto de perfil?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
            });

            if (!confirmDelete.isConfirmed) return;

            const res = await fetch(apiUrl("/admin/perfil/eliminar-foto"), {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                },
            });
            const data = await res.json();

            if (data.success) {
                profileImagePreview.src =
                    "{{ asset('img/avatars/avatar-4.jpg') }}";
                btnDeletePhoto.remove();
                Swal.fire("Eliminada", data.message, "success");
            } else {
                Swal.fire(
                    "Error",
                    data.message || "Error al eliminar la foto",
                    "error"
                );
            }
        });
    }

    // ---------------------- GUARDAR PERFIL ----------------------
    profileForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const nombreInput = document.getElementById("userName");
        const correoInput = document.getElementById("userEmail");
        const telefonoInput = document.getElementById("userPhone");
        const cedulaInput = document.getElementById("userCedula");

        const nombre = nombreInput.value.trim();
        const correo = correoInput.value.trim();
        const telefono = telefonoInput.value.trim();
        const cedula = cedulaInput.value.trim();

        if (!nombre)
            return Swal.fire("Error", "El nombre es obligatorio", "error");
        if (!correo || !isEmailValid(correo))
            return Swal.fire("Error", "Correo inválido", "error");
        if (!telefono || !isPhoneValid(telefono))
            return Swal.fire("Error", "Teléfono inválido", "error");

        // Detectar cambios reales
        const originalNombre = nombreInput.dataset.original || "";
        const originalCorreo = correoInput.dataset.original || "";
        const originalTelefono = telefonoInput.dataset.original || "";
        const originalCedula = cedulaInput.dataset.original || "";

        const fotoCambiada = !!inputPhoto.files[0];
        const datosCambiados =
            nombre !== originalNombre ||
            correo !== originalCorreo ||
            telefono !== originalTelefono ||
            cedula !== originalCedula;

        if (!fotoCambiada && !datosCambiados) {
            return Swal.fire(
                "Aviso",
                "No se detectaron cambios para actualizar",
                "info"
            );
        }

        // Preparar formData
        const formData = new FormData(profileForm);
        if (inputPhoto.files[0]) formData.append("foto", inputPhoto.files[0]);

        const res = await fetch(apiUrl("/admin/perfil/actualizar"), {
            method: "POST",
            body: formData,
            headers: {
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
            },
        });

        const data = await res.json();

        if (data.success) Swal.fire("Éxito", data.message, "success");
        else
            Swal.fire(
                "Error",
                data.message || "Error al actualizar perfil",
                "error"
            );
    });

    // ---------------------- CONTRASEÑA ----------------------
    // Mostrar/Ocultar contraseñas
    document.querySelectorAll(".toggle-password").forEach((icon) => {
        icon.addEventListener("click", () => {
            const target = document.getElementById(icon.dataset.target);
            if (target.type === "password") target.type = "text";
            else target.type = "password";
            icon.classList.toggle("feather-eye-off");
        });
    });

    newPassword.addEventListener("input", () => {
        passwordStrength.innerText =
            "Fuerza: " + evaluatePasswordStrength(newPassword.value);
    });

    confirmPassword.addEventListener("input", () => {
        passwordMatch.innerText =
            newPassword.value !== confirmPassword.value
                ? "Las contraseñas no coinciden"
                : "";
    });

    passwordForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        // Validaciones
        if (!currentPassword.value)
            return Swal.fire("Error", "Ingresa tu contraseña actual", "error");
        if (!newPassword.value)
            return Swal.fire("Error", "Ingresa nueva contraseña", "error");
        if (newPassword.value !== confirmPassword.value)
            return Swal.fire("Error", "Las contraseñas no coinciden", "error");
        if (evaluatePasswordStrength(newPassword.value) === "Débil")
            return Swal.fire("Error", "Contraseña demasiado débil", "error");

        const payload = {
            current_password: currentPassword.value,
            new_password: newPassword.value,
            new_password_confirmation: confirmPassword.value,
        };

        const res = await fetch(apiUrl("/admin/perfil/cambiar-contrasena"), {
            method: "POST",
            body: JSON.stringify(payload),
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
            },
        });

        const data = await res.json();

        if (data.success) {
            Swal.fire("Éxito", data.message, "success");
            passwordForm.reset();
            passwordStrength.innerText = "Fuerza: Débil";
            passwordMatch.innerText = "";
        } else {
            Swal.fire(
                "Error",
                data.message || "Error al cambiar contraseña",
                "error"
            );
        }
    });
});
