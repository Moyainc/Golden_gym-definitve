document.addEventListener("DOMContentLoaded", () => {
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

    // Helper reusable para alertas
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
            success: "#f8b400",
            info: "#4db6ac",
        };

        SwalGlobal.fire({
            icon: icons[type] || "info",
            title: title || (type === "success" ? "Éxito" : "Error"),
            text: message,
            confirmButtonColor: colors[type] || "#fcd52b",
        });
    };

    // ---------------------- UTILIDADES ----------------------
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

    const isEmailValid = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    const isPhoneValid = (phone) => /^\d{7,15}$/.test(phone);
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
                if (user.foto_url && btnDeletePhoto) {
                    btnDeletePhoto.classList.remove("d-none");
                }
            }
        })
        .catch((error) => {
            console.error("❌ Error al cargar usuario:", error);
            showAlert("error", "No se pudieron cargar los datos del usuario.");
        });

    // ---------------------- FOTO DE PERFIL ----------------------
    btnChangePhoto.addEventListener("click", () => inputPhoto.click());

    inputPhoto.addEventListener("change", () => {
        const file = inputPhoto.files[0];
        if (file) profileImagePreview.src = URL.createObjectURL(file);
    });

    if (btnDeletePhoto) {
        btnDeletePhoto.addEventListener("click", async () => {
            const confirmDelete = await SwalGlobal.fire({
                title: "Eliminar foto",
                text: "¿Seguro que deseas eliminar tu foto de perfil?",
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
                    showAlert(
                        "success",
                        data.message || "Foto eliminada correctamente"
                    );
                } else
                    showAlert(
                        "error",
                        data.message || "Error al eliminar la foto"
                    );
            } catch (err) {
                console.error(err);
                showAlert("error", "Error al eliminar la foto");
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

        if (!nombre) return showAlert("error", "El nombre es obligatorio");
        if (!correo || !isEmailValid(correo))
            return showAlert("error", "Correo inválido");
        if (!telefono || !isPhoneValid(telefono))
            return showAlert("error", "Teléfono inválido");

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

        if (!fotoCambiada && !datosCambiados)
            return showAlert(
                "info",
                "No se detectaron cambios para actualizar",
                "Aviso"
            );

        const formData = new FormData(profileForm);
        if (inputPhoto.files[0]) formData.append("foto", inputPhoto.files[0]);

        try {
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
            if (data.success) showAlert("success", data.message);
            else
                showAlert(
                    "error",
                    data.message || "Error al actualizar perfil"
                );
        } catch (err) {
            console.error(err);
            showAlert("error", "Error al actualizar perfil");
        }
    });

    // ---------------------- CONTRASEÑA ----------------------
    document.querySelectorAll(".toggle-password").forEach((icon) => {
        icon.addEventListener("click", () => {
            const target = document.getElementById(icon.dataset.target);
            target.type = target.type === "password" ? "text" : "password";
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
        if (!currentPassword.value)
            return showAlert("error", "Ingresa tu contraseña actual");
        if (!newPassword.value)
            return showAlert("error", "Ingresa nueva contraseña");
        if (newPassword.value !== confirmPassword.value)
            return showAlert("error", "Las contraseñas no coinciden");
        if (evaluatePasswordStrength(newPassword.value) === "Débil")
            return showAlert("error", "Contraseña demasiado débil");

        const payload = {
            current_password: currentPassword.value,
            new_password: newPassword.value,
            new_password_confirmation: confirmPassword.value,
        };

        try {
            const res = await fetch(
                apiUrl("/admin/perfil/cambiar-contrasena"),
                {
                    method: "POST",
                    body: JSON.stringify(payload),
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                }
            );
            const data = await res.json();
            if (data.success) {
                showAlert("success", data.message);
                passwordForm.reset();
                passwordStrength.innerText = "Fuerza: Débil";
                passwordMatch.innerText = "";
            } else
                showAlert(
                    "error",
                    data.message || "Error al cambiar contraseña"
                );
        } catch (err) {
            console.error(err);
            showAlert("error", "Error al cambiar contraseña");
        }
    });
});
