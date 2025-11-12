document.addEventListener("DOMContentLoaded", () => {
    // 🧩 Configuración global de SweetAlert2
    const SwalGlobal = Swal.mixin({
        background: "#1a1a1a",
        color: "#fff",
        confirmButtonColor: "#fcd52b",
        confirmButtonText: "Aceptar",
        customClass: {
            confirmButton: "text-black font-bold",
        },
    });

    const showAlert = (type, message, title = null) => {
        const icons = {
            error: "error",
            warning: "warning",
            success: "success",
            info: "info",
        };
        const colors = {
            error: "#e63946",
            warning: "#f8b400",
            success: "#fcd52b",
            info: "#4db6ac",
        };
        SwalGlobal.fire({
            icon: icons[type] || "info",
            title: title || (type === "success" ? "Éxito" : "Error"),
            text: message,
            confirmButtonColor: colors[type] || "#fcd52b",
        });
    };

    const identificacionInput = document.getElementById(
        "identificacionCliente"
    );
    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");
    const nombreClienteInput = document.getElementById("nombreCliente");
    const clienteIdInput = document.getElementById("cliente_id");
    const productosContainer = document.getElementById("productosContainer");
    const totalInput = document.getElementById("totalVenta");
    const guardarBtn = document.getElementById("guardarVentaDetalladaBtn");
    const metodoPagoSelect = document.getElementById("metodoPago");
    const usuarioId = document.getElementById("usuarioVenta").value;
    const btnClose = document.querySelector("#ventaRapidaModal .btn-close");
    let timeout = null;
    const suggestionBox = document.getElementById("suggestionBox");

    // 🔍 Búsqueda dinámica
    identificacionInput.addEventListener("input", () => {
        clearTimeout(timeout);
        const identificacion = identificacionInput.value.trim();

        if (identificacion.length < 3) {
            suggestionBox.style.display = "none";
            suggestionBox.innerHTML = "";
            nombreClienteInput.value = "";
            clienteIdInput.value = "";
            return;
        }

        timeout = setTimeout(async () => {
            try {
                const res = await fetch(
                    apiUrl(
                        `/admin/ventas/sugerir-clientes?identificacion=${identificacion}`
                    )
                );
                const data = await res.json();

                // Si no hay clientes, ocultar lista
                if (!data.success || data.clientes.length === 0) {
                    suggestionBox.style.display = "none";
                    suggestionBox.innerHTML = "";
                    return;
                }

                // Mostrar lista
                suggestionBox.style.display = "block";
                suggestionBox.innerHTML = data.clientes
                    .map(
                        (c) => `
            <li class="list-group-item list-group-item-action bg-dark text-light"
                data-id="${c.id}"
                data-nombre="${c.nombre_completo}"
                data-cedula="${c.cedula}">
              ${c.cedula} - ${c.nombre_completo}
            </li>`
                    )
                    .join("");
            } catch (err) {
                console.error("Error en sugerencias:", err);
            }
        }, 400);
    });

    // 👆 Seleccionar cliente de la lista
    suggestionBox.addEventListener("click", (e) => {
        if (e.target.matches("li[data-id]")) {
            identificacionInput.value = e.target.dataset.cedula;
            nombreClienteInput.value = e.target.dataset.nombre;
            clienteIdInput.value = e.target.dataset.id;

            suggestionBox.style.display = "none";
            suggestionBox.innerHTML = "";
        }
    });

    // Cerrar sugerencias si se hace clic afuera
    document.addEventListener("click", (e) => {
        if (
            !suggestionBox.contains(e.target) &&
            e.target !== identificacionInput
        ) {
            suggestionBox.style.display = "none";
        }
    });

    /* --------------------------
     🧩 CARGAR CATEGORÍAS
    --------------------------- */
    async function cargarCategorias() {
        try {
            const res = await fetch(apiUrl("/admin/categorias/list"));
            const categorias = await res.json();
            document.querySelectorAll(".categoria").forEach((select) => {
                select.innerHTML = '<option value="">Seleccione</option>';
                categorias.forEach((cat) => {
                    const opt = document.createElement("option");
                    opt.value = cat.id;
                    opt.textContent = cat.nombre;
                    select.appendChild(opt);
                });
            });
        } catch (err) {
            console.error("Error cargando categorías", err);
        }
    }

    cargarCategorias();

    /* --------------------------
     🧩 CARGAR PRODUCTOS SEGÚN CATEGORÍA
    --------------------------- */
    productosContainer.addEventListener("change", async (e) => {
        if (e.target.classList.contains("categoria")) {
            const categoriaId = e.target.value;
            const productoSelect = e.target
                .closest(".producto-item")
                .querySelector(".producto");
            productoSelect.innerHTML = '<option value="">Seleccione</option>';

            if (!categoriaId) return;

            try {
                const res = await fetch(apiUrl(`/admin/productos/list`));
                const productos = await res.json();
                const filtrados = productos.filter(
                    (p) => p.categoria_id == categoriaId
                );

                filtrados.forEach((p) => {
                    const opt = document.createElement("option");
                    opt.value = p.id;
                    opt.textContent = p.nombre;
                    opt.dataset.precio = p.precio;
                    productoSelect.appendChild(opt);
                });
            } catch (err) {
                console.error("Error cargando productos", err);
            }
        }

        if (
            e.target.classList.contains("producto") ||
            e.target.classList.contains("cantidad")
        ) {
            recalcularTotal();
        }
    });

    /* --------------------------
     🧩 CALCULAR SUBTOTAL Y TOTAL
    --------------------------- */
    function recalcularTotal() {
        let total = 0;

        productosContainer
            .querySelectorAll(".producto-item")
            .forEach((item) => {
                const productoSelect = item.querySelector(".producto");
                const cantidadInput = item.querySelector(".cantidad");
                const subtotalInput = item.querySelector(".subtotal");

                const precio = parseFloat(
                    productoSelect.selectedOptions[0]?.dataset.precio || 0
                );
                const cantidad = parseInt(cantidadInput.value || 0, 10);
                const subtotal = precio * cantidad;

                // Formatear el subtotal a moneda colombiana
                subtotalInput.value = subtotal.toLocaleString("es-CO", {
                    style: "currency",
                    currency: "COP",
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                });

                total += subtotal;
            });

        // Formatear el total general
        totalInput.value = total.toLocaleString("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        });

        return total;
    }

    /* --------------------------
     🧩 GUARDAR VENTA DETALLADA
    --------------------------- */

    guardarBtn.addEventListener("click", async () => {
        const clienteId = clienteIdInput.value || null;

        if (!clienteId) {
            showAlert(
                "warning",
                "Seleccione un cliente válido antes de registrar la venta.",
                "Cliente faltante"
            );

            return;
        }

        const productos = [];
        let camposIncompletos = false;

        productosContainer
            .querySelectorAll(".producto-item")
            .forEach((item) => {
                const categoria = item.querySelector(".categoria").value;
                const productoId = item.querySelector(".producto").value;
                const cantidad = parseInt(
                    item.querySelector(".cantidad").value || 0,
                    10
                );
                const subtotal =
                    parseFloat(
                        item.querySelector(".subtotal").value.replace("$", "")
                    ) || 0;

                // Verifica que todos los campos estén completos
                if (!categoria || !productoId || cantidad <= 0) {
                    camposIncompletos = true;
                }

                if (productoId && cantidad > 0) {
                    productos.push({
                        producto_id: productoId,
                        cantidad,
                        subtotal,
                    });
                }
            });

        // 🚫 Si hay campos vacíos o incompletos, no deja enviar
        if (camposIncompletos) {
            showAlert(
                "warning",
                "Asegúrate de que todos los productos tengan categoría, producto y cantidad válida antes de registrar la venta.",
                "Campos incompletos"
            );

            return;
        }

        if (productos.length === 0) {
            showAlert(
                "warning",
                "Agrega al menos un producto válido con cantidad mayor a 0.",
                "Productos inválidos"
            );

            return;
        }

        const total = recalcularTotal();

        const totalFormateado = total.toLocaleString("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        });

        const result = await SwalGlobal.fire({
            title: "Confirmar venta",
            html: `Total: <strong>${totalFormateado}</strong>`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Registrar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#fcd52b",
            cancelButtonColor: "#555",
            customClass: {
                confirmButton: "text-black font-bold",
                cancelButton: "text-white font-semibold",
            },
        });

        if (!result.isConfirmed) return;

        const payload = {
            cliente_id: clienteId,
            usuario_id: usuarioId,
            metodo_pago: metodoPagoSelect.value,
            total,
            productos,
        };

        try {
            const token = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

            const res = await fetch(apiUrl("/admin/ventas/registrar"), {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                    Accept: "application/json",
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (data.alert) {
                Swal.fire(data.alert);
                return;
            }

            if (res.ok && data.success) {
                showAlert("success", data.message, "Venta registrada");

                // ✅ Resetear formulario tras enviar exitosamente
                identificacionInput.value = "";
                nombreClienteInput.value = "";
                clienteIdInput.value = "";
                metodoPagoSelect.selectedIndex = 0;
                totalInput.value = "$0";

                resetQR(metodoPago, qrContainerDetallada, qrSmallDetallada);

                await cargarUltimasVentas();

                // Resetear todos los productos al estado inicial
                const primerItem =
                    productosContainer.querySelector(".producto-item");
                productosContainer.innerHTML = ""; // borra todos
                productosContainer.appendChild(primerItem.cloneNode(true)); // deja uno vacío

                // Vaciar valores del producto clonado
                const nuevo =
                    productosContainer.querySelector(".producto-item");
                nuevo.querySelectorAll("input, select").forEach((el) => {
                    if (el.tagName === "SELECT")
                        el.innerHTML = '<option value="">Seleccione</option>';
                    if (el.tagName === "INPUT")
                        el.value = el.classList.contains("cantidad") ? 1 : "$0";
                });

                // Volver a cargar categorías
                cargarCategorias();
            } else {
                showAlert(
                    "error",
                    data.message || "Error al registrar la venta.",
                    "Error"
                );
            }
        } catch (err) {
            console.error(err);
            showAlert(
                "error",
                "No se pudo registrar la venta. Intenta nuevamente.",
                "Error de conexión"
            );
        }
    });

    // ==============================
    // ⚡ VENTA RÁPIDA
    // ==============================
    const productosContainerModal = document.getElementById(
        "productosRapidosContainer"
    );
    const agregarBtnModal = document.getElementById("agregarProductoRapidaBtn");
    const totalInputModal = document.getElementById("totalRapida");
    const guardarBtnModal = document.getElementById("guardarRapidaBtn");
    const usuarioIdRapida = document.getElementById("usuario_id_rapida").value;
    const MAX_PRODUCTOS_RAPIDA = 3; // 🔹 límite máximo de productos

    // Evitar múltiples listeners (por si el script se ejecuta más de una vez)
    if (!window._ventaRapidaListenersAdded) {
        window._ventaRapidaListenersAdded = true;

        // 🧩 Cargar categorías y productos
        async function cargarCategoriasRapida(select) {
            try {
                const res = await fetch(apiUrl("/admin/categorias/list"));
                const data = await res.json();
                select.innerHTML =
                    '<option value="">Seleccione categoría</option>';
                data.forEach((cat) => {
                    select.innerHTML += `<option value="${cat.id}">${cat.nombre}</option>`;
                });
            } catch (error) {
                console.error("Error cargando categorías", error);
            }
        }

        async function cargarProductosRapida(categoriaId, selectProducto) {
            try {
                const res = await fetch(apiUrl(`/admin/productos/list`));
                const productos = await res.json();
                const filtrados = productos.filter(
                    (p) => p.categoria_id == categoriaId
                );

                selectProducto.innerHTML =
                    '<option value="">Seleccione producto</option>';

                filtrados.forEach((prod) => {
                    selectProducto.innerHTML += `
                <option value="${prod.id}" data-precio="${prod.precio}">
                    ${prod.nombre}
                </option>`;
                });
            } catch (error) {
                console.error("Error cargando productos", error);
            }
        }

        // 💰 Calcular total
        const calcularTotalRapida = () => {
            let total = 0;

            productosContainerModal
                .querySelectorAll(".producto-item")
                .forEach((item) => {
                    const precioRaw =
                        item
                            .querySelector(".precioRapida")
                            .value.replace(/\$|\./g, "") || "0";
                    const subtotal = parseFloat(precioRaw) || 0;

                    total += subtotal;
                });

            totalInputModal.value = `$${total.toLocaleString("es-CO")}`;
            return total;
        };

        // ➕ Agregar producto
        agregarBtnModal.addEventListener("click", () => {
            const totalProductos =
                productosContainerModal.querySelectorAll(
                    ".producto-item"
                ).length;

            // 🔸 Evitar agregar más del máximo permitido
            if (totalProductos >= MAX_PRODUCTOS_RAPIDA) {
                showAlert(
                    "warning",
                    `No puedes agregar más de ${MAX_PRODUCTOS_RAPIDA} productos.`,
                    "Límite alcanzado"
                );

                return;
            }

            const itemOriginal =
                productosContainerModal.querySelector(".producto-item");
            const nuevoItem = itemOriginal.cloneNode(true);

            // Reset de valores
            nuevoItem.querySelectorAll("input, select").forEach((el) => {
                if (el.tagName === "INPUT")
                    el.value = el.classList.contains("cantidadRapida")
                        ? 1
                        : "$0";
                if (el.tagName === "SELECT")
                    el.innerHTML = '<option value="">Seleccione...</option>';
            });

            productosContainerModal.appendChild(nuevoItem);
            cargarCategoriasRapida(nuevoItem.querySelector(".categoriaRapida"));
        });

        productosContainerModal.addEventListener("change", async (e) => {
            const item = e.target.closest(".producto-item");

            // 🔹 Cuando cambia la categoría
            if (e.target.classList.contains("categoriaRapida")) {
                await cargarProductosRapida(
                    e.target.value,
                    item.querySelector(".productoRapida")
                );
            }

            // 🔹 Cuando cambia el producto
            if (e.target.classList.contains("productoRapida")) {
                const productoSeleccionado = e.target.value;

                // 🚫 Validar duplicados
                const productosSeleccionados = [
                    ...productosContainerModal.querySelectorAll(
                        ".productoRapida"
                    ),
                ]
                    .map((p) => p.value)
                    .filter((v) => v !== "");

                const duplicado =
                    productosSeleccionados.filter(
                        (id) => id === productoSeleccionado
                    ).length > 1;

                if (duplicado) {
                    showAlert(
                        "warning",
                        "Este producto ya fue agregado. Por favor elige otro.",
                        "Producto repetido"
                    );

                    e.target.value = "";
                    item.querySelector(".precioRapida").value = "$0";
                    calcularTotalRapida();
                    return;
                }

                // ✅ Calcular subtotal inicial
                const precioUnitario =
                    parseFloat(
                        e.target.selectedOptions[0]?.getAttribute("data-precio")
                    ) || 0;
                const cantidad =
                    parseInt(item.querySelector(".cantidadRapida").value) || 1;
                const subtotal = precioUnitario * cantidad;

                item.querySelector(
                    ".precioRapida"
                ).value = `$${subtotal.toLocaleString("es-CO")}`;
                calcularTotalRapida();
            }

            // 🔹 Cuando cambia la cantidad
            if (e.target.classList.contains("cantidadRapida")) {
                const productoSelect = item.querySelector(".productoRapida");
                const precioUnitario =
                    parseFloat(
                        productoSelect.selectedOptions[0]?.getAttribute(
                            "data-precio"
                        )
                    ) || 0;
                const cantidad = parseInt(e.target.value) || 1;
                const subtotal = precioUnitario * cantidad;

                item.querySelector(
                    ".precioRapida"
                ).value = `$${subtotal.toLocaleString("es-CO")}`;
                calcularTotalRapida();
            }
        });

        // ❌ Eliminar producto (sin permitir eliminar el último)
        productosContainerModal.addEventListener("click", (e) => {
            if (e.target.classList.contains("eliminarProductoRapida")) {
                const items =
                    productosContainerModal.querySelectorAll(".producto-item");
                if (items.length > 1) {
                    e.target.closest(".producto-item").remove();
                    calcularTotalRapida();
                } else {
                    showAlert(
                        "info",
                        "Debe quedar al menos un producto.",
                        "Atención"
                    );
                }
            }
        });

        // 🧾 Guardar venta rápida
        guardarBtnModal.addEventListener("click", async () => {
            const form = document.getElementById("ventaRapidaForm");
            const metodoPago = document.getElementById("metodoRapida").value;
            const estado = document.getElementById("estadoRapida").value;
            const total = calcularTotalRapida();

            const productos = [...form.querySelectorAll(".productoRapida")].map(
                (p, i) => ({
                    producto_id: p.value,
                    cantidad: parseInt(
                        form.querySelectorAll(".cantidadRapida")[i].value
                    ),
                    subtotal:
                        parseFloat(
                            form
                                .querySelectorAll(".precioRapida")
                                [i].value.replace("$", "")
                        ) *
                        parseInt(
                            form.querySelectorAll(".cantidadRapida")[i].value
                        ),
                })
            );

            try {
                const res = await fetch(apiUrl("/admin/ventas/rapida"), {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": token,
                    },
                    body: JSON.stringify({
                        usuario_id: usuarioIdRapida,
                        metodo_pago: metodoPago,
                        total,
                        productos,
                        estado,
                    }),
                });

                const data = await res.json();

                if (data.alert) {
                    Swal.fire(data.alert);
                    return;
                }

                if (res.ok && data.success) {
                    showAlert(
                        "success",
                        data.message,
                        "Venta rápida registrada"
                    );

                    form.reset();
                    calcularTotalRapida();
                    await cargarUltimasVentas();

                    resetQR(metodoPago, qrContainerDetallada, qrSmallDetallada);

                    if (btnClose) btnClose.click();
                } else {
                    showAlert(
                        "error",
                        data.message || "Error al registrar venta rápida.",
                        "Error"
                    );
                }
            } catch (error) {
                console.error("Error al registrar venta rápida:", error);
                showAlert(
                    "error",
                    "No se pudo registrar la venta rápida.",
                    "Error"
                );
            }
        });

        cargarCategoriasRapida(document.querySelector(".categoriaRapida"));
    }

    async function cargarUltimasVentas() {
        try {
            const res = await fetch(apiUrl("/admin/ventas/ultimas"));
            const data = await res.json();

            if (!data.success) {
                console.error("Error al obtener ventas:", data.message);
                return;
            }

            const tbody = document.getElementById("ventasUltimas5");
            tbody.innerHTML = "";

            if (data.ventas.length === 0) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-secondary">
                        No hay ventas registradas aún.
                    </td>
                </tr>`;
                return;
            }

            data.ventas.forEach((v) => {
                const estadoConfig = {
                    Completada: { bg: "#28a745", color: "#fff" }, // Verde, texto negro
                    Pendiente: { bg: "#fcd52b", color: "#000" }, // Amarillo, texto negro
                    Anulada: { bg: "#dc3545", color: "#fff" }, // Rojo, texto blanco
                };

                const estado = v.estado ?? "N/A";
                const { bg, color } = estadoConfig[estado] || {
                    bg: "#6c757d",
                    color: "#fff",
                }; // Default gris

                const tr = document.createElement("tr");
                tr.innerHTML = `
                <td>${v.producto_nombre ?? "Desconocido"}</td>
                <td>${v.categoria_nombre ?? "Sin categoría"}</td>
                <td>${v.metodo_pago ?? "N/A"}</td>
                <td>$${parseFloat(v.producto_precio ?? 0).toLocaleString()}</td>
                <td>${v.cantidad ?? 1}</td>
                <td>$${parseFloat(v.subtotal ?? 0).toLocaleString()}</td>
                <td>
                    <span class="badge" style="background:${bg}; color:${color}; font-weight:600;">
                        ${estado}
                    </span>
                </td>
                `;
                tbody.appendChild(tr);
            });

            // 👑 Mostrar botón "Ver más ventas" si es admin
            const container = document.querySelector(".ventas-container");
            const existingBtn = document.getElementById("verMasVentasBtn");

            if (data.isAdmin && !existingBtn) {
                const btn = document.createElement("button");
                btn.id = "verMasVentasBtn";
                btn.className = "btn btn-warning mt-3 fw-bold";
                btn.textContent = "Ver más ventas";
                btn.addEventListener("click", () => {
                    window.location.href = apiUrl("admin/ventas/lista-ventas");
                });
                container.appendChild(btn);
            }
        } catch (error) {
            console.error("Error al cargar ventas:", error);
        }
    }

    // --------------------------
    // Elementos DOM
    // --------------------------
    const metodoPago = document.getElementById("metodoPago");
    const qrContainerDetallada = document.getElementById(
        "qrContainerDetallada"
    );
    const qrSmallDetallada = document.getElementById("qrSmallDetallada");

    const metodoRapida = document.getElementById("metodoRapida");
    const qrContainerRapida = document.getElementById("qrContainerRapida");
    const qrSmallRapida = document.getElementById("qrSmallRapida");

    const qrZoomModalEl = document.getElementById("qrZoomModal");
    const qrZoomContainer = document.getElementById("qrZoom");

    function resetQR(metodoSelect, qrContainer, qrSmall) {
        metodoSelect.value = "Efectivo"; // volver a efectivo
        qrContainer.style.display = "none"; // ocultar QR
        qrSmall.innerHTML = ""; // limpiar contenido del QR
    }

    function mostrarQRPequenoDetallada() {
        if (
            metodoPago.value === "Transferencia" &&
            typeof comprobanteIdDetallada !== "undefined"
        ) {
            qrContainerDetallada.classList.add("show");

            if (!qrSmallDetallada.innerHTML.trim()) {
                generarQR(qrSmallDetallada, comprobanteIdDetallada, 50);
            }

            setTimeout(() => qrSmallDetallada.classList.add("zoom"), 100);
        } else {
            qrSmallDetallada.classList.remove("zoom");
            setTimeout(() => {
                qrContainerDetallada.classList.remove("show");
                qrSmallDetallada.innerHTML = "";
            }); // espera a que termine la transición
        }
    }

    function mostrarQRPequenoRapida() {
        if (
            metodoRapida.value === "Transferencia" &&
            typeof comprobanteIdRapida !== "undefined"
        ) {
            qrContainerRapida.classList.add("show");

            if (!qrSmallRapida.innerHTML.trim()) {
                generarQR(qrSmallRapida, comprobanteIdRapida, 50);
            }

            setTimeout(() => qrSmallRapida.classList.add("zoom"), 100);
        } else {
            qrSmallRapida.classList.remove("zoom");
            setTimeout(() => {
                qrContainerRapida.classList.remove("show");
                qrSmallRapida.innerHTML = "";
            });
        }
    }


    metodoPago.addEventListener("change", mostrarQRPequenoDetallada);
    metodoRapida.addEventListener("change", mostrarQRPequenoRapida);

    // --------------------------
    // Zoom QR en modal
    // --------------------------
    function abrirQRZoom(comprobanteId) {
        qrZoomContainer.innerHTML = ""; // limpiar previo
        generarQR(qrZoomContainer, comprobanteId, 400); // QR grande

        const qrZoom = new bootstrap.Modal(qrZoomModalEl, {
            backdrop: true,
            keyboard: true,
        });
        qrZoom.show();
    }

    let comprobanteIdDetallada = null;
    let comprobanteIdRapida = null;

    qrSmallDetallada.addEventListener("click", () => {
        if (typeof comprobanteIdDetallada !== "undefined") {
            abrirQRZoom(comprobanteIdDetallada);
        }
    });

    qrSmallRapida.addEventListener("click", () => {
        if (typeof comprobanteIdRapida !== "undefined") {
            abrirQRZoom(comprobanteIdRapida);
        }
    });

    // Limpiar QR al cerrar modal zoom
    qrZoomModalEl.addEventListener("hidden.bs.modal", () => {
        qrZoomContainer.innerHTML = "";
    });

    // --------------------------
    // Función genérica para generar QR
    // --------------------------
    function generarQR(contenedor, comprobanteId, size = 150) {
        contenedor.innerHTML = ""; // limpiar previo
        const url = apiUrl(`/admin/comprobantes/upload/${comprobanteId}`);
        new QRCode(contenedor, {
            text: url,
            width: size,
            height: size,
        });
    }

    // --------------------------
    // Después de guardar venta
    // --------------------------
    guardarVentaDetalladaBtn.addEventListener("click", async () => {
        const idGenerado = await guardarVentaDetallada();
        comprobanteIdDetallada = idGenerado;
        mostrarQRPequenoDetallada();
    });

    guardarRapidaBtn.addEventListener("click", async () => {
        const idGenerado = await guardarVentaRapida();
        comprobanteIdRapida = idGenerado;
        mostrarQRPequenoRapida();
    });

    cargarUltimasVentas();
});
