document.addEventListener("DOMContentLoaded", () => {
    const identificacionInput = document.getElementById(
        "identificacionCliente"
    );
    const nombreClienteInput = document.getElementById("nombreCliente");
    const clienteIdInput = document.getElementById("cliente_id");
    const productosContainer = document.getElementById("productosContainer");
    const agregarBtn = document.getElementById("agregarProductoBtn");
    const totalInput = document.getElementById("totalVenta");
    const guardarBtn = document.getElementById("guardarVentaDetalladaBtn");
    const metodoPagoSelect = document.getElementById("metodoPago");
    const usuarioId = document.getElementById("usuarioVenta").value;
    const MAX_PRODUCTOS = 5;
    let timeout = null;
    const suggestionBox = document.getElementById("suggestionBox");
    const apiUrl = (path) => {
        const base = `${window.location.origin}/golden_gym/public`;
        return `${base}/${path.replace(/^\/+/, "")}`;
    };


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

                subtotalInput.value = `$${subtotal.toFixed(1)}`;
                total += subtotal;
            });

        totalInput.value = `$${total.toFixed(1)}`;
        return total;
    }

    /* --------------------------
     🧩 GUARDAR VENTA DETALLADA
    --------------------------- */
    guardarBtn.addEventListener("click", async () => {
        const clienteId = clienteIdInput.value || null;

        if (!clienteId) {
            Swal.fire({
                icon: "warning",
                title: "Cliente faltante",
                text: "Seleccione un cliente válido antes de registrar la venta.",
            });
            return;
        }

        const productos = [];
        productosContainer
            .querySelectorAll(".producto-item")
            .forEach((item) => {
                const productoId = item.querySelector(".producto").value;
                const cantidad = parseInt(
                    item.querySelector(".cantidad").value || 0,
                    10
                );
                const subtotal =
                    parseFloat(
                        item.querySelector(".subtotal").value.replace("$", "")
                    ) || 0;

                if (productoId && cantidad > 0) {
                    productos.push({
                        producto_id: productoId,
                        cantidad,
                        subtotal,
                    });
                }
            });

        if (productos.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "Productos inválidos",
                text: "Agrega al menos un producto válido con cantidad mayor a 0.",
            });
            return;
        }

        const total = recalcularTotal();

        const result = await Swal.fire({
            title: "Confirmar venta",
            html: `Total: <strong>${totalInput.value}</strong>`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Registrar",
            cancelButtonText: "Cancelar",
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

            if (res.ok && data.success) {
                Swal.fire({
                    icon: "success",
                    title: "Venta registrada",
                    text: data.message,
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "Error al registrar la venta.",
                });
            }
        } catch (err) {
            console.error(err);
            Swal.fire({
                icon: "error",
                title: "Error de conexión",
                text: "No se pudo registrar la venta. Intenta nuevamente.",
            });
        }
    });
});
