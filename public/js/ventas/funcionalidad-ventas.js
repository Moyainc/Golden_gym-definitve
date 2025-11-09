document.addEventListener("DOMContentLoaded", () => {
    const usuarioId = document.getElementById("usuarioVenta")?.value || null;

    const productosContainer = document.getElementById("productosContainer");
    const agregarBtn = document.getElementById("agregarProductoBtn");
    const totalInput = document.getElementById("totalVenta");
    const MAX_PRODUCTOS = 4;

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
        productosContainer.addEventListener("change", (e) => {
            const item = e.target.closest(".producto-item");
            if (!item) return;

            // ⚡ Solo actuar si cambió un select de producto
            if (e.target.classList.contains("producto")) {
                const seleccionado = e.target.value;

                // Obtener todos los productos seleccionados en otros items
                const productosSeleccionados = Array.from(
                    productosContainer.querySelectorAll(".producto")
                )
                    .filter((p) => p !== e.target) // excluir el que cambió
                    .map((p) => p.value);

                if (
                    productosSeleccionados.includes(seleccionado) &&
                    seleccionado !== ""
                ) {
                    Swal.fire({
                        icon: "warning",
                        title: "Producto duplicado",
                        text: "Ya agregaste este producto en otra casilla.",
                        confirmButtonColor: "#f0ad4e",
                    });

                    // Resetear el select
                    e.target.value = "";
                }
            }

            // Recalcular total después de cualquier cambio
            recalcularTotal();
        });
    });
});
