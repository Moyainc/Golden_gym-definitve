document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");
    const attemptsContainer = document.getElementById("loginAttempts");
    const maxAttempts = 4; // total de intentos permitidos

    if (!loginForm) return;

    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const email = loginForm.querySelector('[name="correo"]').value.trim();
        const password = loginForm
            .querySelector('[name="password"]')
            .value.trim();

        if (!email || !password) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Por favor ingrese correo y contraseña.",
                background: "#1a1a1a",
                color: "#fff",
                confirmButtonColor: "#fcd52b",
                confirmButtonText: "Aceptar",
                customClass: { confirmButton: "text-black font-bold" },
            });
            return;
        }

        const formData = new FormData(loginForm);

        try {
            const response = await fetch(loginForm.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                credentials: "same-origin",
            });

            const data = await response.json();

            if (!response.ok) {
                // Incrementar intentos locales
                const key = `login_attempts_${email}`;
                let attempts = parseInt(localStorage.getItem(key)) || 0;
                attempts++;
                localStorage.setItem(key, attempts);

                const remaining = maxAttempts - attempts;

                // Mostrar mensaje solo a partir del 2° fallo
                if (attempts >= 2 && remaining > 0) {
                    attemptsContainer.textContent = `Te quedan ${remaining} intento(s) antes de que tu cuenta se bloquee.`;
                } else if (remaining <= 0) {
                    attemptsContainer.textContent = `Tu cuenta ha sido temporalmente bloqueada. Intenta más tarde.`;
                } else {
                    attemptsContainer.textContent = ""; // no mostrar nada en el primer fallo
                }

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "Correo o contraseña incorrectos",
                    background: "#1a1a1a",
                    color: "#fff",
                    confirmButtonColor: "#fcd52b",
                    confirmButtonText: "Aceptar",
                    customClass: { confirmButton: "text-black font-bold" },
                });

                return;
            }

            // Login exitoso
            attemptsContainer.textContent = "";
            localStorage.removeItem(`login_attempts_${email}`);

            const hora = new Date().getHours();
            const saludo =
                hora >= 5 && hora < 12
                    ? "Buenos días"
                    : hora >= 12 && hora < 19
                    ? "Buenas tardes"
                    : "Buenas noches";

            Swal.fire({
                icon: "success",
                title: `${saludo}, ${data.user?.nombre || "usuario"}!`,
                text: data.message || "Has iniciado sesión correctamente",
                background: "#1a1a1a",
                color: "#fff",
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
            }).then(() => {
                loginForm.reset();
                window.location.href = data.redirect || "/";
            });
        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ocurrió un error al procesar el inicio de sesión.",
                background: "#1a1a1a",
                color: "#fff",
                confirmButtonColor: "#fcd52b",
                confirmButtonText: "Aceptar",
                customClass: { confirmButton: "text-black font-bold" },
            });
        }
    });
});
