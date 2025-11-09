document.addEventListener("DOMContentLoaded", () => {
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

    const productosContainer = document.getElementById("productosContainer");
    const agregarBtn = document.getElementById("agregarProductoBtn");
    const totalInput = document.getElementById("totalVenta");
    const MAX_PRODUCTOS = 4;

    function formatCurrency(value) {
        // Si el valor no es número, devolver $0
        if (isNaN(value) || value <= 0) return "$0";

        // Formatear en pesos colombianos (sin decimales)
        return value.toLocaleString("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        })
    }

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

                // Mostrar cada subtotal formateado
                item.querySelector(".subtotal").value =
                    formatCurrency(subtotal);

                total += subtotal;
            });

        // Mostrar total general formateado
        totalInput.value = formatCurrency(total);
    }

    // Agregar producto
    agregarBtn.addEventListener("click", () => {
        const totalProductos =
            productosContainer.querySelectorAll(".producto-item").length;
        if (totalProductos >= MAX_PRODUCTOS) {
            showAlert(
                "warning",
                `No puedes agregar más de ${MAX_PRODUCTOS} productos por venta.`,
                "Límite alcanzado"
            );

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
                showAlert(
                    "info",
                    "Debe quedar al menos un producto.",
                    "Atención"
                );
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
                    showAlert(
                        "warning",
                        "Ya agregaste este producto en otra casilla.",
                        "Producto duplicado"
                    );

                    // Resetear el select
                    e.target.value = "";
                }
            }

            // Recalcular total después de cualquier cambio
            recalcularTotal();
        });
    });
});
