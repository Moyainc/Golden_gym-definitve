document.addEventListener("DOMContentLoaded", () => {
    const emailGroup = document.getElementById("emailGroup");
    const specificClientEmail = document.getElementById("specificClientEmail");
    const emailForm = document.getElementById("emailForm");
    const sendEmailBtn = document.getElementById("sendEmailBtn");

    // Función para actualizar el estado de los campos
    const updateFields = () => {
        if (emailGroup.value) {
            specificClientEmail.disabled = true;
            specificClientEmail.value = "";
        } else {
            specificClientEmail.disabled = false;
        }

        if (specificClientEmail.value) {
            emailGroup.disabled = true;
            emailGroup.value = "";
        } else {
            emailGroup.disabled = false;
        }
    };


    emailGroup.addEventListener("change", updateFields);
    specificClientEmail.addEventListener("input", updateFields);

    // Validación antes de enviar
    emailForm.addEventListener("submit", (e) => {
        if (!emailGroup.value && !specificClientEmail.value) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Debes seleccionar un grupo o escribir un correo específico.",
                background: "#1a1a1a",
                color: "#fff",
                confirmButtonColor: "#fcd52b",
                confirmButtonText: "Aceptar",
                customClass: {
                    confirmButton: "text-black font-bold",
                },
            });
            return;
        }

        if (emailGroup.value && specificClientEmail.value) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No puedes enviar al grupo y al correo específico al mismo tiempo.",
                background: "#1a1a1a",
                color: "#fff",
                confirmButtonColor: "#fcd52b",
                confirmButtonText: "Aceptar",
                customClass: {
                    confirmButton: "text-black font-bold",
                },
            });
            return;
        }


    });
});
