document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("registerForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault(); // evita el submit normal

        const password = form.password.value;
        const passwordConfirm = form.password_confirmation.value;

        if (password !== passwordConfirm) {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                html: "Las contraseñas no coinciden",
            });
            return;
        }

        const formData = new FormData(form);

        try {
            const res = await fetch(form.action, {
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
                    form.reset();
                    window.location.href = loginUrl;
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
});
