document.addEventListener("DOMContentLoaded", () => {
    const paymentsTable = document.getElementById("paymentsTable");
    const pendingCount = document.getElementById("pendingPayments");

    // Función para cargar pagos pendientes desde el servidor
    const cargarPagos = async () => {
        const res = await fetch(apiUrl('admin/pagos/pendientes/json')); // Ruta que devolverá JSON
        const pagosPendientes = await res.json();
        renderTable(pagosPendientes);
    };

    // Función para renderizar la tabla
    const renderTable = (pagosPendientes) => {
        paymentsTable.innerHTML = "";

        pagosPendientes.forEach((pago, index) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${pago.cliente}</td>
                <td>$${pago.monto}</td>
                <td>${pago.metodo}</td>
                <td>${pago.fecha}</td>
                <td>
                    <button class="btn btn-sm btn-outline-info view-proof" data-bs-toggle="modal" data-bs-target="#proofModal" data-proof="">
                        <i data-feather="image"></i>
                    </button>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-success me-1 confirm-btn" data-id="${pago.id}" data-tipo="${pago.tipo}"><i data-feather="check"></i></button>
                </td>
            `;
            paymentsTable.appendChild(row);
        });

        pendingCount.textContent = pagosPendientes.length;
        feather.replace();

        // Subir comprobante
        document.querySelectorAll(".confirm-btn").forEach((btn) => {
            btn.addEventListener("click", async (e) => {
                const pagoId = e.currentTarget.dataset.id;
                const tipo = e.currentTarget.dataset.tipo;

                const { value: file } = await Swal.fire({
                    title: "Subir Comprobante",
                    input: "file",
                    inputAttributes: {
                        accept: "image/*",
                        "aria-label": "Sube tu comprobante",
                    },
                    showCancelButton: true,
                    confirmButtonText: "Subir",
                    background: "#1a1a1a",
                    color: "#fff",
                    confirmButtonColor: "#fcd52b",
                });

                if (file) {
                    const formData = new FormData();
                    formData.append("comprobante", file);
                    formData.append("pago_id", pagoId);
                    formData.append("tipo", tipo);
                    formData.append("_token", "{{ csrf_token() }}");

                    const res = await fetch(
                        apiUrl('admin/pagos/subirComprobante'),
                        {
                            method: "POST",
                            body: formData,
                        }
                    );

                    if (res.ok) {
                        Swal.fire({
                            icon: "success",
                            title: "Éxito",
                            text: "Comprobante subido correctamente",
                            background: "#1a1a1a",
                            color: "#fff",
                            confirmButtonColor: "#fcd52b",
                        });

                        // Recargar tabla
                        cargarPagos();
                    }
                }
            });
        });
    };

    // Carga inicial de pagos pendientes
    cargarPagos();
});
