document.addEventListener("DOMContentLoaded", () => {
    // Registro
    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        registerForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            const password = registerForm.password.value;
            const passwordConfirm = registerForm.password_confirmation.value;

            if (password !== passwordConfirm) {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    html: "Las contraseñas no coinciden",
                });
                return;
            }

            const formData = new FormData(registerForm);

            try {
                const res = await fetch(registerForm.action, {
                    method: "POST",
                    body: formData,
                });
                const data = await res.json();

                if (!res.ok) {
                    let errores = "";
                    if (data.errors) {
                        for (const key in data.errors) {
                            errores += data.errors[key].join("<br>") + "<br>";
                        }
                    } else {
                        errores = data.message || "Error inesperado";
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        html: errores,
                    });
                } else {
                    Swal.fire({
                        icon: "success",
                        title: "Registro exitoso",
                        text: data.message || "Cuenta creada correctamente",
                    }).then(() => {
                        registerForm.reset();
                        window.location.href = loginUrl; // define loginUrl en algún lugar
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Ocurrió un error al procesar el registro",
                });
            }
        });
    }

    // Login
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            const email = loginForm
                .querySelector('[name="correo"]')
                .value.trim();
            const password = loginForm
                .querySelector('[name="password"]')
                .value.trim();

            if (!email || !password) {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Por favor ingrese correo y contraseña.",
                });
                return;
            }

            const formData = new FormData(loginForm);

            try {
                const formData = new FormData(loginForm);

                const response = await fetch(loginForm.action, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    credentials: "same-origin", // ✅ clave para enviar cookies de sesión
                });

                const data = await response.json();

                if (!response.ok) {
                    let errores = "";
                    if (data.errors) {
                        for (const key in data.errors) {
                            errores += data.errors[key].join(", ") + "\n";
                        }
                    } else {
                        errores = data.message || "Error inesperado";
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: errores,
                    });
                } else {
                    const hora = new Date().getHours();
                    let saludo = "";

                    if (hora >= 5 && hora < 12) {
                        saludo = "Buenos días";
                    } else if (hora >= 12 && hora < 19) {
                        saludo = "Buenas tardes";
                    } else {
                        saludo = "Buenas noches";
                    }

                    const nombre = data.user?.nombre || "usuario";
                    Swal.fire({
                        icon: "success",
                        title: `${saludo}, ${nombre}!`,
                        text:
                            data.message || "Has iniciado sesión correctamente",
                    }).then(() => {
                        loginForm.reset();
                        window.location.href = data.redirect || "/";
                    });
                }
            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Ocurrió un error al procesar el inicio de sesión.",
                });
            }
        });
    }
});
