document.addEventListener("DOMContentLoaded", () => {
    const profileForm = document.getElementById("profileForm");
    const inputPhoto = document.getElementById("inputPhoto");
    const profileImagePreview = document.getElementById("profileImagePreview");
    const btnChangePhoto = document.getElementById("btnChangePhoto");

    const passwordForm = document.getElementById("passwordChangeForm");
    const currentPassword = document.getElementById("currentPassword");
    const newPassword = document.getElementById("newPassword");
    const confirmPassword = document.getElementById("confirmPassword");
    const passwordStrength = document.getElementById("passwordStrength");
    const passwordMatch = document.getElementById("passwordMatch");
    const btnDeletePhoto = document.getElementById("btnDeletePhoto");

    const apiUrl = (path) => {
        const base = window.app?.baseUrl || window.location.origin;
        return `${base}/${path.replace(/^\/+/, "")}`;
    };

    if (btnDeletePhoto) {
        btnDeletePhoto.addEventListener("click", async () => {
            if (!confirm("¿Seguro que deseas eliminar tu foto de perfil?"))
                return;

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
                btnDeletePhoto.remove(); // ocultar el botón
                alert(data.message);
            } else {
                alert(data.message || "Error al eliminar la foto");
            }
        });
    }

    // 🔹 Cargar perfil al entrar
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
                    "Último acceso: " + user.ultimo_acceso;
                profileImagePreview.src = user.foto_url;
            }
        });

    // 🔹 Cambiar foto
    btnChangePhoto.addEventListener("click", () => inputPhoto.click());

    inputPhoto.addEventListener("change", () => {
        const file = inputPhoto.files[0];
        if (file) {
            profileImagePreview.src = URL.createObjectURL(file);
        }
    });

    // 🔹 Guardar perfil
    profileForm.addEventListener("submit", async (e) => {
        e.preventDefault();
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
        if (data.success) {
            alert(data.message);
        } else {
            alert("Error al actualizar perfil");
        }
    });

    // 🔹 Cambiar contraseña
    function evaluatePasswordStrength(password) {
        let score = 0;
        if (password.length >= 8) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[a-z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[\W]/.test(password)) score++;
        if (score <= 2) return "Débil";
        if (score <= 4) return "Media";
        return "Fuerte";
    }

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
        if (newPassword.value !== confirmPassword.value) {
            passwordMatch.innerText = "Las contraseñas no coinciden";
            return;
        }

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
            alert(data.message);
            passwordForm.reset();
            passwordStrength.innerText = "Fuerza: Débil";
        } else {
            alert(data.message || "Error al cambiar contraseña");
        }
    });
});
