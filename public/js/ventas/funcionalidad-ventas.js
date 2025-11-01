document.addEventListener("DOMContentLoaded", () => {
    const usuarioId = document.getElementById("usuarioVenta")?.value || null;

    const productosContainer = document.getElementById("productosContainer");
    const agregarBtn = document.getElementById("agregarProductoBtn");
    const totalInput = document.getElementById("totalVenta");
    const MAX_PRODUCTOS = 5;

    function recalcularTotal() {
        let total = 0;
        productosContainer
            .querySelectorAll(".producto-item")
            .forEach((item) => {
                const cantidad =
                    parseFloat(item.querySelector(".cantidad").value) || 0;
                const precio =
                    parseFloat(
                        item.querySelector(".producto").selectedOptions[0]
                            ?.dataset.precio
                    ) || 0;
                const subtotal = cantidad * precio;
                item.querySelector(".subtotal").value = `$${subtotal.toFixed(
                    2
                )}`;
                total += subtotal;
            });
        totalInput.value = `$${total.toFixed(2)}`;
    }

    // Agregar producto
    agregarBtn.addEventListener("click", () => {
        const totalProductos =
            productosContainer.querySelectorAll(".producto-item").length;
        if (totalProductos >= MAX_PRODUCTOS) {
            Swal.fire({
                icon: "warning",
                title: "Límite alcanzado",
                text: `No puedes agregar más de ${MAX_PRODUCTOS} productos por venta.`,
                confirmButtonColor: "#f0ad4e",
            });
            return;
        }

        const template = productosContainer.querySelector(".producto-item");
        const nuevo = template.cloneNode(true);

        // Resetear valores
        nuevo.querySelector(".categoria").value = "";
        nuevo.querySelector(".producto").value = "";
        nuevo.querySelector(".cantidad").value = 1;
        nuevo.querySelector(".subtotal").value = "$0";

        productosContainer.appendChild(nuevo);
        recalcularTotal();
    });

    // Eliminar producto con delegación de eventos
    productosContainer.addEventListener("click", (e) => {
        if (e.target.classList.contains("eliminarProducto")) {
            const items = productosContainer.querySelectorAll(".producto-item");
            if (items.length > 1) {
                e.target.closest(".producto-item").remove();
                recalcularTotal();
            } else {
                Swal.fire({
                    icon: "info",
                    title: "Atención",
                    text: "Debe quedar al menos un producto.",
                    confirmButtonColor: "#5bc0de",
                });
            }
        }
    });

    // Recalcular total al cambiar producto o cantidad
    productosContainer.addEventListener("change", (e) => {
        const item = e.target.closest(".producto-item");
        if (!item) return;
        recalcularTotal();
    });

    const productosRapidosContainer = document.getElementById(
        "productosRapidosContainer"
    );
    const agregarBtnRapida = document.getElementById(
        "agregarProductoRapidaBtn"
    );
    const MAX_PRODUCTOS_RAPIDA = 4;

    // Agregar un nuevo producto rápido
    agregarBtnRapida.addEventListener("click", () => {
        const totalProductos =
            productosRapidosContainer.querySelectorAll(".producto-item").length;

        if (totalProductos >= MAX_PRODUCTOS_RAPIDA) {
            Swal.fire({
                icon: "warning",
                title: "Límite alcanzado",
                text: `No puedes agregar más de ${MAX_PRODUCTOS_RAPIDA} productos.`,
                confirmButtonColor: "#f0ad4e",
            });
            return;
        }

        const itemOriginal =
            productosRapidosContainer.querySelector(".producto-item");
        const nuevo = itemOriginal.cloneNode(true);

        // Reset valores
        nuevo.querySelector("select").value = "";
        nuevo.querySelector("input").value = 1;

        productosRapidosContainer.appendChild(nuevo);
    });

    // Eliminar producto rápido
    productosRapidosContainer.addEventListener("click", (e) => {
        if (e.target.classList.contains("eliminarProductoRapida")) {
            const items =
                productosRapidosContainer.querySelectorAll(".producto-item");

            if (items.length > 1) {
                e.target.closest(".producto-item").remove();
            } else {
                Swal.fire({
                    icon: "info",
                    title: "Atención",
                    text: "Debe quedar al menos un producto.",
                    confirmButtonColor: "#5bc0de",
                });
            }
        }
    });
});
