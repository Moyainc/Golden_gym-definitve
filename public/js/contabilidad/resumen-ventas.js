document.addEventListener("DOMContentLoaded", () => {
    const filterStart = document.getElementById("filterStart");
    const filterEnd = document.getElementById("filterEnd");

    const totalVentasEl = document.getElementById("totalVentas");
    const numVentasEl = document.getElementById("numVentas");
    const numProductosEl = document.getElementById("numProductos");
    const ventasPendientesEl = document.getElementById("ventasPendientes");
    const ventasTable = document.getElementById("ventasTable");

    const apiUrl = (path) =>
        `${window.location.origin}/golden_gym/public/${path.replace(
            /^\/+/,
            ""
        )}`;

    const ctxCategoria = document
        .getElementById("ventasCategoriaChart")
        .getContext("2d");
    const ctxProducto = document
        .getElementById("ventasProductosChart")
        .getContext("2d");
    const ctxMes = document
        .getElementById("ventasTotalesChart")
        .getContext("2d");

    let chartCategoria, chartProducto, chartMes;

    // ✅ Formatear precios como moneda colombiana
    const formatPrecio = (valor) => {
        const num = Number(valor);
        if (isNaN(num)) return "$0";
        return new Intl.NumberFormat("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(num);
    };

    // ✅ Cargar métricas y gráficos
    async function cargarDatos() {
        const params = new URLSearchParams();
        if (filterStart.value) params.append("fecha_inicio", filterStart.value);
        if (filterEnd.value) params.append("fecha_fin", filterEnd.value);

        try {
            const res = await fetch(
                apiUrl(`/admin/resumen-ventas/data?${params}`)
            );
            const data = await res.json();
            if (!data.success) throw new Error("Error al obtener datos");

            // 🧾 Métricas principales
            totalVentasEl.innerText = formatPrecio(data.totalVentas);
            numVentasEl.innerText = data.numVentas;
            numProductosEl.innerText = data.numProductos;
            ventasPendientesEl.innerText = data.pendientes;

            // 📊 Datos para las gráficas
            const categoriaLabels = Object.keys(data.ventasPorCategoria);
            const categoriaData = Object.values(data.ventasPorCategoria).map(
                Number
            );

            const productoLabels = Object.keys(data.ventasPorProducto);
            const productoData = Object.values(data.ventasPorProducto).map(
                Number
            );

            // ✅ Convertir "2025-11" → "nov. 2025" con orden cronológico
            const mesKeys = Object.keys(data.ventasPorMes);
            const mesLabels = mesKeys.map((key) => {
                const [year, month] = key.split("-");
                const date = new Date(year, month - 1);
                return date.toLocaleString("es-ES", {
                    month: "short",
                    year: "numeric",
                });
            });
            const mesData = Object.values(data.ventasPorMes).map(Number);

            // 🥧 Gráfica Categoría
            if (chartCategoria) chartCategoria.destroy();
            chartCategoria = new Chart(ctxCategoria, {
                type: "pie",
                data: {
                    labels: categoriaLabels,
                    datasets: [
                        {
                            data: categoriaData,
                            backgroundColor: [
                                "#f59e0b",
                                "#10b981",
                                "#3b82f6",
                                "#ef4444",
                                "#8b5cf6",
                            ],
                        },
                    ],
                },
                options: {
                    responsive: true, // 🔹 hace que se ajuste al contenedor
                    maintainAspectRatio: false, // 🔹 ocupa todo el alto de la card
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: (ctx) =>
                                    `${ctx.label}: ${formatPrecio(ctx.raw)}`,
                            },
                        },
                        legend: {
                            labels: { color: "#fff" },
                        },
                    },
                },
            });

            // 📦 Gráfica Producto
            if (chartProducto) chartProducto.destroy();
            chartProducto = new Chart(ctxProducto, {
                type: "bar",
                data: {
                    labels: productoLabels,
                    datasets: [
                        {
                            label: "Ventas por Producto",
                            data: productoData,
                            backgroundColor: "#3b82f6",
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // 🔹 importante para llenar la card
                    indexAxis: "y",
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: (ctx) => formatPrecio(ctx.raw),
                            },
                        },
                        legend: { labels: { color: "#fff" } },
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: "#fff",
                                callback: (val) => formatPrecio(val),
                            },
                            grid: { color: "rgba(255,255,255,0.1)" },
                        },
                        y: {
                            ticks: { color: "#fff" },
                            grid: { color: "rgba(255,255,255,0.1)" },
                        },
                    },
                },
            });

            // 📈 Gráfica Ventas por Mes
            if (chartMes) chartMes.destroy();
            chartMes = new Chart(ctxMes, {
                type: "line",
                data: {
                    labels: mesLabels,
                    datasets: [
                        {
                            label: "Total Ventas",
                            data: mesData,
                            borderColor: "#f59e0b",
                            backgroundColor: "rgba(245,158,11,0.3)",
                            pointBackgroundColor: "#f59e0b",
                            pointRadius: 5,
                            fill: true,
                            tension: 0.4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // 🔹 ocupa todo el alto de la card
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: (ctx) => formatPrecio(ctx.raw),
                            },
                        },
                        legend: { labels: { color: "#fff" } },
                    },
                    scales: {
                        x: {
                            ticks: { color: "#fff" },
                            grid: { color: "rgba(255,255,255,0.1)" },
                        },
                        y: {
                            ticks: {
                                color: "#fff",
                                callback: (val) => formatPrecio(val),
                            },
                            grid: { color: "rgba(255,255,255,0.1)" },
                        },
                    },
                },
            });
        } catch (err) {
            Swal.fire("Error", err.message, "error");
        }
    }

    // ✅ Últimas 5 ventas
    async function cargarUltimasVentas() {
        try {
            const res = await fetch(apiUrl("/admin/resumen-ventas/ultimas"));
            const data = await res.json();
            if (!data.success)
                throw new Error("Error al obtener últimas ventas");

            ventasTable.innerHTML = "";
            data.ventas.forEach((v) => {
                v.detalles.forEach((d) => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>${d.producto}</td>
                        <td>${d.categoria}</td>
                        <td>${v.metodo_pago}</td>
                        <td>${formatPrecio(d.subtotal)}</td>
                        <td>${new Date(v.fecha_venta).toLocaleDateString()}</td>
                        <td>
                            <span class="badge ${
                                v.estado === "Completada"
                                    ? "bg-success"
                                    : "bg-warning"
                            } text-black">${v.estado}</span>
                        </td>
                    `;
                    ventasTable.appendChild(tr);
                });
            });
        } catch (err) {
            Swal.fire("Error", err.message, "error");
        }
    }

    // 🎯 Filtros
    filterStart.addEventListener("change", cargarDatos);
    filterEnd.addEventListener("change", cargarDatos);
    document.getElementById("applyFilter").addEventListener("click", () => {
        filterStart.value = "";
        filterEnd.value = "";
        cargarDatos();
    });

    cargarDatos();
    cargarUltimasVentas();
});
