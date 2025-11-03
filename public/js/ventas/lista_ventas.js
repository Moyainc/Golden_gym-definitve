document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.getElementById("ventasTable");
    const filterCategoria = document.getElementById("filterCategoria");
    const filterFechaDesde = document.getElementById("filterFechaDesde");
    const filterFechaHasta = document.getElementById("filterFechaHasta");
    const searchVenta = document.getElementById("searchVenta");
    const paginationContainer = document.querySelector(".pagination-modern");

    const modal = new bootstrap.Modal(document.getElementById("detalleModal"));
    const modalBody = document.getElementById("detalleModalBody");

    const apiUrl = (path) =>
        `${window.location.origin}/golden_gym/public/${path.replace(
            /^\/+/,
            ""
        )}`;

    const showLoader = () => {
        const overlay = document.createElement("div");
        overlay.id = "loaderOverlay";
        overlay.style.position = "absolute";
        overlay.style.top = 0;
        overlay.style.left = 0;
        overlay.style.width = "100%";
        overlay.style.height = "100%";
        overlay.style.background = "rgba(0,0,0,0.1)";
        overlay.style.display = "flex";
        overlay.style.alignItems = "center";
        overlay.style.justifyContent = "center";
        overlay.style.zIndex = 10;
        overlay.innerHTML = `<div class="spinner-border text-warning" role="status" style="width:3rem; height:3rem;">
            <span class="visually-hidden">Cargando...</span>
        </div>`;
        tbody.closest(".table-responsive").appendChild(overlay);
    };

    const hideLoader = () => {
        const overlay = document.getElementById("loaderOverlay");
        if (overlay) overlay.remove();
    };

    const fetchVentas = async (page = 1) => {
        showLoader();
        const params = new URLSearchParams({
            categoria: filterCategoria.value,
            fecha_desde: filterFechaDesde.value,
            fecha_hasta: filterFechaHasta.value,
            page,
        });

        try {
            const res = await fetch(
                apiUrl(`/admin/ventas/list?${params.toString()}`)
            );
            if (!res.ok) throw new Error("Error al obtener los datos");
            let data = await res.json();

            const q = searchVenta.value.trim().toLowerCase();

            if (q !== "") {
                data.ventas = data.ventas
                    .map((venta) => {
                        const detallesFiltrados = venta.detalles.filter(
                            (det) =>
                                det.producto.toLowerCase().includes(q) ||
                                venta.metodo_pago.toLowerCase().includes(q) ||
                                venta.usuario.toLowerCase().includes(q)
                        );
                        return { ...venta, detalles: detallesFiltrados };
                    })
                    .filter((venta) => venta.detalles.length > 0);
            }

            renderTabla(data.ventas);
            renderPaginacion(data.pagination);
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Error al cargar las ventas</td></tr>`;
            console.error(err);
        } finally {
            hideLoader();
        }
    };

    const renderTabla = (ventas) => {
        tbody.innerHTML = "";
        if (!ventas.length) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted">No hay ventas</td></tr>`;
            return;
        }

        const fragment = document.createDocumentFragment();

        ventas.forEach((venta) => {
            venta.detalles.forEach((det) => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${det.producto}</td>
                    <td>${det.categoria}</td>
                    <td>${venta.metodo_pago}</td>
                    <td>${det.subtotal_formatted}</td>
                    <td>${
                        new Date(venta.fecha_venta).toISOString().split("T")[0]
                    }</td>
                    <td><span class="badge bg-${
                        venta.estado === "Completada" ? "success" : "secondary"
                    }">${venta.estado}</span></td>
                    <td>${venta.usuario}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-light verVentaBtn" data-id="${
                            venta.id
                        }">
                            <i data-feather="eye"></i>
                        </button>
                    </td>
                `;
                fragment.appendChild(tr);
            });
        });

        tbody.appendChild(fragment);
        if (feather) feather.replace();

        // 🔹 Botón ver venta
        document.querySelectorAll(".verVentaBtn").forEach((btn) => {
            btn.addEventListener("click", async () => {
                const id = btn.dataset.id;
                modalBody.innerHTML = `<div class="text-center py-3">Cargando...</div>`;
                modal.show();

                try {
                    const res = await fetch(apiUrl(`/admin/ventas/${id}`));
                    if (!res.ok) throw new Error("No se pudo cargar la venta");
                    const data = await res.json();

                    let html = `
                        <p><strong>Vendedor:</strong> ${data.usuario}</p>
                        <p><strong>Fecha:</strong> ${new Date(
                            data.fecha_venta
                        ).toLocaleDateString()}</p>
                        <p><strong>Método de pago:</strong> ${
                            data.metodo_pago
                        }</p>
                        <p><strong>Estado:</strong> ${data.estado}</p>
                        <table class="table table-sm table-bordered text-light mt-3">
                            <thead class="text-warning">
                                <tr>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    data.detalles.forEach((d) => {
                        html += `
                            <tr>
                                <td>${d.producto}</td>
                                <td>${d.categoria}</td>
                                <td>${d.cantidad}</td>
                                <td>${d.precio_unitario_formatted}</td>
                                <td>${d.subtotal_formatted}</td>
                            </tr>
                        `;
                    });

                    html += `</tbody></table>
                             <p class="mt-2"><strong>Total:</strong> ${data.total_formatted}</p>`;

                    modalBody.innerHTML = html;
                } catch (err) {
                    console.error(err);
                    modalBody.innerHTML = `<div class="text-center text-danger py-3">Error al cargar los detalles de la venta</div>`;
                }
            });
        });
    };

    const renderPaginacion = (pagination) => {
        paginationContainer.innerHTML = "";

        const createBtn = (label, page, disabled = false, active = false) => {
            const li = document.createElement("li");
            li.classList.add("page-item");
            const btn = document.createElement("button");
            btn.className = `page-btn btn btn-sm ${
                active ? "btn-warning text-dark" : "btn-outline-light"
            }`;
            btn.textContent = label;
            btn.disabled = disabled;
            btn.dataset.page = page;
            li.appendChild(btn);
            return li;
        };

        paginationContainer.appendChild(
            createBtn(
                "«",
                pagination.current_page - 1,
                pagination.current_page === 1
            )
        );
        for (let p = 1; p <= pagination.last_page; p++) {
            paginationContainer.appendChild(
                createBtn(p, p, false, p === pagination.current_page)
            );
        }
        paginationContainer.appendChild(
            createBtn(
                "»",
                pagination.current_page + 1,
                pagination.current_page === pagination.last_page
            )
        );

        paginationContainer.querySelectorAll(".page-btn").forEach((btn) => {
            btn.addEventListener("click", () => fetchVentas(btn.dataset.page));
        });
    };

    [filterCategoria, filterFechaDesde, filterFechaHasta].forEach((el) =>
        el.addEventListener("change", () => fetchVentas(1))
    );

    searchVenta.addEventListener("input", () => {
        clearTimeout(searchVenta._timeout);
        searchVenta._timeout = setTimeout(() => fetchVentas(1), 400);
    });

    fetchVentas();
});
