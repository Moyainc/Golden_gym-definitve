document.addEventListener("DOMContentLoaded", async () => {
    // Configuración global de SweetAlert2
    const SwalGlobal = Swal.mixin({
        background: "#1a1a1a",
        color: "#fff",
        confirmButtonColor: "#fcd52b",
        confirmButtonText: "Aceptar",
        customClass: { confirmButton: "text-black font-bold" },
    });

    const cuerpoTabla = document.querySelector("#tablaPagosConfirmados tbody");
    const contadorPagos = document.getElementById("contadorPagos");
    const paginacion = document.querySelector(".pagination-modern");
    const paginationInfo = document.getElementById("paginationInfo");
    const modalImg = document.getElementById("modalProofImage");
    const deleteBtn = document.getElementById("deleteProofBtn");

    // Filtros
    const filterMetodo = document.getElementById("filterMetodo");
    const filterDesde = document.getElementById("filterFechaDesde");
    const filterHasta = document.getElementById("filterFechaHasta");
    const searchPago = document.getElementById("searchPago");

    let pagos = [];
    let paginaActual = 1;
    const registrosPorPagina = 15;
    const maxPaginas = 3;
    let currentId = null;

    // Cargar pagos desde el servidor
    const cargarPagosConfirmados = async () => {
        const response = await fetch(apiUrl("/admin/pagos/confirmados/json"));
        pagos = await response.json();
        aplicarFiltros();
    };

    // Aplicar filtros activos
    const aplicarFiltros = () => {
        let filtrados = [...pagos];

        const metodo = filterMetodo.value;
        const desde = filterDesde.value ? new Date(filterDesde.value) : null;
        const hasta = filterHasta.value ? new Date(filterHasta.value) : null;
        const busqueda = searchPago.value.toLowerCase();

        if (metodo === "Efectivo") {
            filtrados = filtrados.filter((p) => !p.foto_comprobante);
        }

        if (desde) {
            filtrados = filtrados.filter((p) => new Date(p.fecha) >= desde);
        }

        if (hasta) {
            filtrados = filtrados.filter((p) => new Date(p.fecha) <= hasta);
        }

        if (busqueda) {
            filtrados = filtrados.filter(
                (p) =>
                    p.cliente.toLowerCase().includes(busqueda) ||
                    p.vendedor.toLowerCase().includes(busqueda) ||
                    p.transaccion.toLowerCase().includes(busqueda)
            );
        }

        renderTabla(filtrados);
    };

    // Renderizar tabla y paginación
    const renderTabla = (filtrados) => {
        cuerpoTabla.innerHTML = "";
        contadorPagos.textContent = `${filtrados.length} pagos encontrados`;

        if (!filtrados.length) {
            cuerpoTabla.innerHTML = `
                <tr><td colspan="7" class="text-center text-muted">No hay pagos que coincidan</td></tr>`;
            paginacion.innerHTML = "";
            paginationInfo.textContent = "";
            return;
        }

        const totalPaginas = Math.ceil(filtrados.length / registrosPorPagina);
        const paginasVisibles = Math.min(totalPaginas, maxPaginas);

        // Calcular registros de la página actual
        const inicio = (paginaActual - 1) * registrosPorPagina;
        const fin = inicio + registrosPorPagina;
        const pagina = filtrados.slice(inicio, fin);

        pagina.forEach((pago) => {
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
                            ? `<button class="btn btn-sm btn-outline-info view-proof"
                                data-id="${pago.id}"
                                data-foto="${apiUrl(
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

        // Info de paginación
        paginationInfo.textContent = `Página ${paginaActual} de ${paginasVisibles}`;

        // Renderizar botones de paginación
        paginacion.innerHTML = "";
        for (let i = 1; i <= paginasVisibles; i++) {
            const li = document.createElement("li");
            li.innerHTML = `
                <button class="btn btn-sm ${
                    i === paginaActual
                        ? "btn-warning text-dark fw-bold"
                        : "btn-outline-warning"
                }">${i}</button>`;
            li.querySelector("button").addEventListener("click", () => {
                paginaActual = i;
                renderTabla(filtrados);
            });
            paginacion.appendChild(li);
        }

        // Asignar evento a botones de comprobante
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

    // Eliminar comprobante
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

    // Listeners de filtros
    [filterMetodo, filterDesde, filterHasta].forEach((el) =>
        el.addEventListener("change", aplicarFiltros)
    );
    searchPago.addEventListener("input", aplicarFiltros);

    // Cargar datos iniciales
    await cargarPagosConfirmados();
});
