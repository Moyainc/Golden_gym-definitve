document.addEventListener("DOMContentLoaded", async () => {
    const SwalGlobal = Swal.mixin({
        background: "#1a1a1a",
        color: "#fff",
        confirmButtonColor: "#fcd52b",
        confirmButtonText: "Aceptar",
        customClass: {
            confirmButton: "text-black font-bold",
        },
    });

    const cuerpoTabla = document.querySelector("#tablaPagosConfirmados tbody");
    const modalImg = document.getElementById("modalProofImage");
    const deleteBtn = document.getElementById("deleteProofBtn");
    let currentId = null;

    const cargarPagosConfirmados = async () => {
        const response = await fetch(apiUrl("/admin/pagos/confirmados/json"));
        const data = await response.json();

        cuerpoTabla.innerHTML = "";

        if (!data.length) {
            cuerpoTabla.innerHTML = `
                <tr><td colspan="7" class="text-center text-muted">No hay pagos confirmados</td></tr>`;
            return;
        }

        data.forEach((pago) => {
            const fila = document.createElement("tr");
            fila.innerHTML = `
                <td>${pago.transaccion}</td>
                <td>${pago.cliente}</td>
                <td>${pago.vendedor}</td>
                <td>${pago.metodo}</td>
                <td>$${Number(pago.monto).toLocaleString()}</td>
                <td>${pago.fecha}</td>
                <td class="text-center">
                    ${
                        pago.foto_comprobante
                            ? `<button class="btn btn-sm btn-outline-info view-proof" data-id="${
                                  pago.id
                              }" data-foto="${apiUrl(
                                  `/storage/${pago.foto_comprobante}`
                              )}">
                                   <i data-feather="image"></i>
                               </button>`
                            : `<span class="text-muted">Sin comprobante</span>`
                    }
                </td>
            `;
            cuerpoTabla.appendChild(fila);
        });

        feather.replace();

        document.querySelectorAll(".view-proof").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                const { id, foto } = e.currentTarget.dataset;
                currentId = id;
                modalImg.src = foto;
                const modal = new bootstrap.Modal(
                    document.getElementById("proofModal")
                );
                modal.show();
            });
        });
    };

    deleteBtn.addEventListener("click", async () => {
        if (!currentId) return;

        const confirm = await SwalGlobal.fire({
            icon: "warning",
            title: "¿Eliminar comprobante?",
            text: "Esta acción eliminará la imagen del comprobante.",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        });

        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(
                apiUrl("/admin/pagos/eliminar-comprobante"),
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: JSON.stringify({ id: currentId }),
                }
            );

            const data = await res.json();
            if (!res.ok) throw new Error(data.error || "Error al eliminar");

            SwalGlobal.fire({
                icon: "success",
                title: "Eliminado",
                text: data.message,
            });

            bootstrap.Modal.getInstance(
                document.getElementById("proofModal")
            ).hide();
            await cargarPagosConfirmados();
        } catch (err) {
            SwalGlobal.fire({
                icon: "error",
                title: "Error",
                text: err.message,
            });
        }
    });

    await cargarPagosConfirmados();
});
