document.addEventListener("DOMContentLoaded", () => {
    const paymentsTable = document.getElementById("paymentsTable");
    const pendingCount = document.getElementById("pendingPayments");
    const modalImg = document.getElementById("modalProofImage");
    const modalFile = document.getElementById("modalProofFile");
    const saveBtn = document.getElementById("saveProofBtn");
    const deleteBtn = document.getElementById("deleteProofBtn");

    let currentPagoId = null;
    let currentTipo = null;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    const cargarPagos = async () => {
        const res = await fetch(apiUrl("admin/pagos/pendientes/json"));
        const pagosPendientes = await res.json();
        renderTable(pagosPendientes);
    };

    const renderTable = (pagosPendientes) => {
        paymentsTable.innerHTML = "";

        pagosPendientes.forEach((pago) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${pago.transaccion}</td>
                <td>${pago.cliente}</td>
                <td>${pago.vendedor}</td>
                <td>$${pago.monto}</td>
                <td>${pago.metodo}</td>
                <td>${pago.fecha}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-info view-proof" data-bs-toggle="modal" data-bs-target="#proofModal" data-id="${
                        pago.id
                    }" data-tipo="${pago.tipo}" data-proof="${
                pago.foto_comprobante ?? ""
            }">
                        <i data-feather="image"></i>
                    </button>
                </td>
            `;
            paymentsTable.appendChild(row);
        });

        pendingCount.textContent = pagosPendientes.length;
        feather.replace();

        document.querySelectorAll(".view-proof").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                currentPagoId = e.currentTarget.dataset.id;
                currentTipo = e.currentTarget.dataset.tipo;

                const proofPath = e.currentTarget.dataset.proof;
                if (proofPath) {
                    // Construir la URL de manera segura sin duplicar /
                    modalImg.src = apiUrl(
                        `storage/${proofPath.replace(/^\/+/, "")}`
                    );
                } else {
                    modalImg.src = "";
                }

                modalFile.value = "";
            });
        });

        modalFile.addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    modalImg.src = ev.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        saveBtn.addEventListener("click", async () => {
            if (!modalFile.files[0]) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Debes seleccionar un archivo de imagen.",
                    background: "#1a1a1a",
                    color: "#fff",
                    confirmButtonColor: "#fcd52b",
                });
                return;
            }
            if (!currentPagoId || !currentTipo) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo identificar el pago seleccionado.",
                    background: "#1a1a1a",
                    color: "#fff",
                    confirmButtonColor: "#fcd52b",
                });
                return;
            }

            const formData = new FormData();
            formData.append("comprobante", modalFile.files[0]);
            formData.append("pago_id", currentPagoId);
            formData.append("tipo", currentTipo);
            formData.append("_token", csrfToken);

            try {
                const res = await fetch(
                    apiUrl("admin/pagos/subir-comprobante"),
                    {
                        method: "POST",
                        body: formData,
                    }
                );

                const data = await res.json();

                if (!res.ok)
                    throw new Error(data.error || "Error al subir comprobante");

                Swal.fire({
                    icon: "success",
                    title: "Éxito",
                    text: data.message || "Comprobante enviado correctamente",
                    background: "#1a1a1a",
                    color: "#fff",
                    confirmButtonColor: "#fcd52b",
                });

                cargarPagos();
                const proofModal = bootstrap.Modal.getInstance(
                    document.getElementById("proofModal")
                );
                if (proofModal) proofModal.hide();
                modalFile.value = "";
                modalImg.src = "";
            } catch (error) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: error.message || "No se pudo subir el comprobante",
                    background: "#1a1a1a",
                    color: "#fff",
                    confirmButtonColor: "#fcd52b",
                });
            }
        });

        deleteBtn.addEventListener("click", async () => {
            if (!currentPagoId || !currentTipo) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo identificar el pago seleccionado.",
                    background: "#1a1a1a",
                    color: "#fff",
                    confirmButtonColor: "#fcd52b",
                });
                return;
            }

            const confirm = await Swal.fire({
                icon: "warning",
                title: "Eliminar comprobante",
                text: "¿Seguro que deseas eliminar este comprobante?",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
                background: "#1a1a1a",
                color: "#fff",
                confirmButtonColor: "#fcd52b",
            });

            if (confirm.isConfirmed) {
                try {
                    const res = await fetch(
                        apiUrl("admin/pagos/eliminarComprobante"),
                        {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": csrfToken,
                                Accept: "application/json",
                            },
                            body: JSON.stringify({
                                pago_id: currentPagoId,
                                tipo: currentTipo,
                            }),
                        }
                    );

                    const data = await res.json();

                    if (!res.ok)
                        throw new Error(
                            data.error || "Error al eliminar comprobante"
                        );

                    Swal.fire({
                        icon: "success",
                        title: "Eliminado",
                        text:
                            data.message ||
                            "Comprobante eliminado correctamente",
                        background: "#1a1a1a",
                        color: "#fff",
                        confirmButtonColor: "#fcd52b",
                    });

                    modalImg.src = "";
                    modalFile.value = "";
                    cargarPagos();
                } catch (error) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text:
                            error.message ||
                            "No se pudo eliminar el comprobante",
                        background: "#1a1a1a",
                        color: "#fff",
                        confirmButtonColor: "#fcd52b",
                    });
                }
            }
        });
    };

    cargarPagos();
});
