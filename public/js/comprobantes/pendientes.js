document.addEventListener("DOMContentLoaded", () => {
    const paymentsTable = document.getElementById("paymentsTable");
    const pendingCount = document.getElementById("pendingPayments");
    const modalImg = document.getElementById("modalProofImage");
    const modalFile = document.getElementById("modalProofFile");
    const saveBtn = document.getElementById("saveProofBtn");
    // const deleteBtn = document.getElementById("deleteProofBtn"); // eliminado
    const paginationContainer = document.querySelector(".pagination-modern");
    const paginationInfo = document.getElementById("paginationInfo");

    let currentPagoId = null;
    let currentTipo = null;
    let currentPage = 1;
    const recordsPerPage = 12;
    const maxPages = 3;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    let pagosPendientes = [];

    const cargarPagos = async () => {
        const res = await fetch(apiUrl("admin/pagos/pendientes/json"));
        pagosPendientes = await res.json();
        currentPage = 1;
        renderTable();
        renderPagination();
    };

    const renderTable = () => {
        paymentsTable.innerHTML = "";

        if (pagosPendientes.length === 0) {
            paymentsTable.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-light py-4">
                        No hay registros disponibles
                    </td>
                </tr>`;
            paginationContainer.innerHTML = "";
            paginationInfo.textContent = "";
            pendingCount.textContent = "0";
            return;
        }

        const start = (currentPage - 1) * recordsPerPage;
        const end = start + recordsPerPage;
        const visibleData = pagosPendientes.slice(start, end);

        visibleData.forEach((pago) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${pago.transaccion}</td>
                <td>${pago.cliente}</td>
                <td>${pago.vendedor}</td>
                <td>$${pago.monto}</td>
                <td>${pago.metodo}</td>
                <td>${pago.fecha}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-info view-proof"
                        data-bs-toggle="modal"
                        data-bs-target="#proofModal"
                        data-id="${pago.id}"
                        data-tipo="${pago.tipo}"
                        data-proof="${pago.foto_comprobante ?? ""}">
                        <i data-feather="image"></i>
                    </button>
                </td>
            `;
            paymentsTable.appendChild(row);
        });

        pendingCount.textContent = pagosPendientes.length;
        feather.replace();
        setupProofEvents();

        const totalPages = Math.min(
            Math.ceil(pagosPendientes.length / recordsPerPage),
            maxPages
        );
        paginationInfo.textContent = `Página ${currentPage} de ${totalPages}`;
    };

    const renderPagination = () => {
        paginationContainer.innerHTML = "";

        const totalPages = Math.min(
            Math.ceil(pagosPendientes.length / recordsPerPage),
            maxPages
        );

        if (totalPages <= 1) return;

        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement("li");
            li.innerHTML = `
                <button class="btn ${
                    i === currentPage
                        ? "btn-warning text-black fw-bold"
                        : "btn-outline-warning text-light"
                } btn-sm rounded-pill px-3">${i}</button>
            `;
            li.querySelector("button").addEventListener("click", () => {
                currentPage = i;
                renderTable();
                renderPagination();
            });
            paginationContainer.appendChild(li);
        }
    };

    const setupProofEvents = () => {
        document.querySelectorAll(".view-proof").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                currentPagoId = e.currentTarget.dataset.id;
                currentTipo = e.currentTarget.dataset.tipo;

                const proofPath = e.currentTarget.dataset.proof;
                modalImg.src = proofPath
                    ? apiUrl(`storage/${proofPath.replace(/^\/+/, "")}`)
                    : "";
                modalFile.value = "";
            });
        });
    };

    modalFile.addEventListener("change", (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => (modalImg.src = ev.target.result);
            reader.readAsDataURL(file);
        }
    });

    saveBtn.addEventListener("click", async () => {
        if (!modalFile.files[0]) {
            return Swal.fire({
                icon: "error",
                title: "Error",
                text: "Debes seleccionar un archivo de imagen.",
                background: "#1a1a1a",
                color: "#fff",
                confirmButtonColor: "#fcd52b",
                confirmButtonText: "Aceptar",
                customClass: { confirmButton: "text-black font-bold" },
            });
        }
        if (!currentPagoId || !currentTipo) {
            return Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo identificar el pago seleccionado.",
                background: "#1a1a1a",
                color: "#fff",
                confirmButtonColor: "#fcd52b",
                confirmButtonText: "Aceptar",
                customClass: { confirmButton: "text-black font-bold" },
            });
        }

        const formData = new FormData();
        formData.append("comprobante", modalFile.files[0]);
        formData.append("pago_id", currentPagoId);
        formData.append("tipo", currentTipo);
        formData.append("_token", csrfToken);

        try {
            const res = await fetch(apiUrl("admin/pagos/subir-comprobante"), {
                method: "POST",
                body: formData,
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error);

            Swal.fire({
                icon: "success",
                title: "Éxito",
                text: data.message || "Comprobante enviado correctamente",
                background: "#1a1a1a",
                color: "#fff",
                confirmButtonColor: "#fcd52b",
                confirmButtonText: "Aceptar",
                customClass: { confirmButton: "text-black font-bold" },
            });

            cargarPagos();
            bootstrap.Modal.getInstance(
                document.getElementById("proofModal")
            )?.hide();
        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.message || "No se pudo subir el comprobante",
                background: "#1a1a1a",
                color: "#fff",
                confirmButtonColor: "#fcd52b",
                confirmButtonText: "Aceptar",
                customClass: { confirmButton: "text-black font-bold" },
            });
        }
    });

    cargarPagos();
});
