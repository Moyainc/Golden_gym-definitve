document.addEventListener("DOMContentLoaded", () => {
    const meses = [
        "Enero",
        "Febrero",
        "Marzo",
        "Abril",
        "Mayo",
        "Junio",
        "Julio",
        "Agosto",
        "Septiembre",
        "Octubre",
        "Noviembre",
        "Diciembre",
    ];

    const filterMonth = document.getElementById("filterMonth");
    const filterYear = document.getElementById("filterYear");
    const totalVentasEl = document.getElementById("totalVentas");
    const numVentasEl = document.getElementById("numVentas");
    const numProductosEl = document.getElementById("numProductos");
    const ventasPendientesEl = document.getElementById("ventasPendientes");
    const ventasTable = document.getElementById("ventasTable");

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

    // Formateo en pesos colombianos
    function formatearCOP(valor) {
        if (valor === null || valor === undefined || valor === "") return "$0";
        const entero = Math.round(parseFloat(valor));
        return entero.toLocaleString("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0,
        });
    }

    // Abreviar valores para ejes
    function formatAbreviado(valor) {
        const entero = Math.round(valor);
        if (entero >= 1000000) return Math.floor(entero / 1000000) + "M";
        if (entero >= 1000) return Math.floor(entero / 1000) + "k";
        return entero.toString();
    }

    // Rellenar filtro de meses
    meses.forEach((mes, index) => {
        const option = document.createElement("option");
        option.value = index + 1;
        option.textContent = mes;
        filterMonth.appendChild(option);
    });


    const applyFilterBtn = document.getElementById("applyFilter");

    const hoy = new Date();
    const mesActual = hoy.getMonth() + 1;
    const anioActual = hoy.getFullYear();


    filterMonth.value = mesActual;
    filterYear.value = anioActual;

    // Al hacer clic en Aplicar Filtro, volver al mes y año actual
    applyFilterBtn.addEventListener("click", (e) => {
        e.preventDefault();
        filterMonth.value = mesActual;
        filterYear.value = anioActual;
        cargarDatos(); // tu función existente
    });

    async function cargarDatos() {
        const params = new URLSearchParams();
        if (filterMonth.value) params.append("mes", filterMonth.value);
        if (filterYear.value) params.append("anio", filterYear.value);

        try {
            const res = await fetch(
                apiUrl(`/admin/resumen-ventas/data?${params}`)
            );
            const data = await res.json();
            if (!data.success) throw new Error("Error al obtener datos");

            totalVentasEl.innerText = formatearCOP(data.totalVentas);
            numVentasEl.innerText = data.numVentas;
            numProductosEl.innerText = data.numProductos;
            ventasPendientesEl.innerText = data.pendientes;

            const ventasPorProductoValores = Object.values(
                data.ventasPorProducto
            ).map((v) => Math.round(v));
            const ventasPorMesValores = Object.values(data.ventasPorMes).map(
                (v) => Math.round(v)
            );
            const ventasPorCategoriaValores = Object.values(
                data.ventasPorCategoria
            ).map((v) => Math.round(v));

            // --- Pie Chart Categoría ---
            if (chartCategoria) chartCategoria.destroy();
            chartCategoria = new Chart(ctxCategoria, {
                type: "pie",
                data: {
                    labels: Object.keys(data.ventasPorCategoria),
                    datasets: [
                        {
                            data: ventasPorCategoriaValores,
                            backgroundColor: [
                                "#FFD700", // Amarillo dorado (armoniza con botones)
                                "#FFB347", // Amarillo-naranja suave
                                "#4D96FF", // Azul brillante
                                "#9B5DE5", // Morado vibrante
                                "#FF69B4", // Rosa fuerte
                                "#6BCB77", // Verde fresco
                                "#00CED1", // Turquesa
                                "#F4A261", // Amarillo-anaranjado pastel
                                "#8AC926", // Verde lima vivo
                            ],
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: (ctx) => formatearCOP(ctx.raw),
                            },
                        },
                        legend: {
                            labels: {
                                color: "#cccc",
                                generateLabels: (chart) =>
                                    chart.data.labels.map((label, i) => ({
                                        text: `${label}: ${formatAbreviado(
                                            chart.data.datasets[0].data[i]
                                        )}`,
                                        fillStyle:
                                            chart.data.datasets[0]
                                                .backgroundColor[i],
                                        strokeStyle: "#cccc",
                                        hidden: false,
                                        index: i,
                                    })),
                            },
                        },
                    },
                },
            });

            // --- Bar Chart Productos ---
            if (chartProducto) chartProducto.destroy();
            // Limitar altura según cantidad de productos
            const chartHeight = Math.min(
                ventasPorProductoValores.length * 40,
                600
            );
            ctxProducto.canvas.parentNode.style.height = chartHeight + "px";

            chartProducto = new Chart(ctxProducto, {
                type: "bar",
                data: {
                    labels: Object.keys(data.ventasPorProducto),
                    datasets: [
                        {
                            label: "Ventas por Producto",
                            data: ventasPorProductoValores,
                            backgroundColor: "#ffd52b",
                            hoverBackgroundColor: "#fab300",
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: "y",
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: (ctx) => formatearCOP(ctx.raw),
                            },
                        },
                        legend: { labels: { color: "#cccc" } },
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: "#cccc",
                                callback: (v) => formatAbreviado(v),
                            },
                            grid: { color: "rgba(255,255,255,0.1)" },
                        },
                        y: {
                            ticks: { color: "#cccc" },
                            grid: { color: "rgba(255,255,255,0.1)" },
                        },
                    },
                },
            });

            // --- Line Chart Ventas por Mes ---
            const mesKeys = Object.keys(data.ventasPorMes);

            const mesLabelsAbreviado = mesKeys.map((k) => {
                const [year, month] = k.split("-");
                const nombreMes = new Date(year, month - 1).toLocaleString(
                    "es-ES",
                    {
                        month: "short", // abreviado
                    }
                );
                return nombreMes.charAt(0).toUpperCase() + nombreMes.slice(1);
            });

            const mesLabelsCompleto = mesKeys.map((k) => {
                const [year, month] = k.split("-");
                const nombreMes = new Date(year, month - 1).toLocaleString(
                    "es-ES",
                    {
                        month: "long", // completo
                    }
                );
                return nombreMes.charAt(0).toUpperCase() + nombreMes.slice(1);
            });

            if (chartMes) chartMes.destroy();
            chartMes = new Chart(ctxMes, {
                type: "line",
                data: {
                    labels: mesLabelsAbreviado, // Abreviado en eje X
                    datasets: [
                        {
                            label: "Total Ventas",
                            data: ventasPorMesValores,
                            borderColor: "#FAD730",
                            backgroundColor: "rgba(225,178,11,0.2)",
                            fill: true,
                            pointBackgroundColor: "#FAD730",
                            pointRadius: 5,
                            tension: 0.4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const mesCompleto =
                                        mesLabelsCompleto[ctx.dataIndex]; // mes completo
                                    const valor = ctx.raw;
                                    return `${mesCompleto}: ${formatearCOP(
                                        valor
                                    )}`;
                                },
                            },
                        },
                        legend: { labels: { color: "#cccc" } },
                    },
                    scales: {
                        x: {
                            ticks: { color: "#cccc" },
                            grid: { color: "rgba(255,255,255,0.1)" },
                        },
                        y: {
                            ticks: {
                                color: "#cccc",
                                callback: (v) => formatAbreviado(v),
                            },
                            grid: { color: "rgba(255,255,255,0.1)" },
                        },
                    },
                },
            });
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: err.message,
                background: "#1a1a1a",
                color: "#fff",
                confirmButtonColor: "#fcd52b",
                confirmButtonText: "Aceptar",
                customClass: { confirmButton: "text-black font-bold" },
            });
        }
    }

    async function cargarUltimasVentas() {
        try {
            const res = await fetch(apiUrl("/admin/resumen-ventas/ultimas"));
            const data = await res.json();
            if (!data.success)
                throw new Error("Error al obtener últimas ventas");

            ventasTable.innerHTML = "";
            let filas = [];
            data.ventas.forEach((v) => {
                v.detalles.forEach((d) => {
                    filas.push(`
          <tr>
            <td>${d.producto}</td>
            <td>${d.categoria}</td>
            <td>${v.metodo_pago}</td>
            <td>${formatearCOP(d.subtotal)}</td>
            <td>${new Date(v.fecha_venta).toLocaleDateString()}</td>
            <td><span class="badge ${v.estado === "Completada" ? "bg-success" : "bg-warning"
                        } text-black">${v.estado}</span></td>
          </tr>
        `);
                });
            });

            // Limitar a 10 filas
            filas = filas.slice(0, 10);
            ventasTable.innerHTML = filas.join("");
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: err.message,
                background: "#1a1a1a",
                color: "#fff",
                confirmButtonColor: "#fcd52b",
                confirmButtonText: "Aceptar",
                customClass: { confirmButton: "text-black font-bold" },
            });
        }
    }

    filterMonth.addEventListener("change", cargarDatos);
    filterYear.addEventListener("change", cargarDatos);

    cargarDatos();
    cargarUltimasVentas();
});


// === EXPORTAR REPORTES DE VENTAS ===
document.addEventListener("DOMContentLoaded", () => {
    const exportPdfVentasBtn = document.getElementById("exportPdfVentasBtn");
    const exportExcelVentasBtn = document.getElementById("exportExcelVentasBtn");
    const reportsVentasForm = document.getElementById("reportsVentasForm");


    const validarFormulario = () => {


        const inicio = reportsVentasForm.querySelector("#reportStartDateVentas").value;
        const fin = reportsVentasForm.querySelector("#reportEndDateVentas").value;
        // === Validación del formulario ===
        if (!inicio || !fin) {
            Swal.fire({
                icon: "warning",
                title: "Campos requeridos",
                text: "Selecciona un rango de fechas",
                background: "#1a1a1a",
                color: "#fff",
            })
            return
        }

        if (inicio && fin && new Date(inicio) > new Date(fin)) {
            Swal.fire({
                icon: "warning",
                title: "Fechas inválidas",
                text: "La fecha de inicio no puede ser mayor que la fecha de fin.",
                background: "#1a1a1a",
                color: "#fff",
                confirmButtonColor: "#fcd52b",
                confirmButtonText: "Aceptar",
                customClass: { confirmButton: "text-black font-bold" },
            });
            return false;
        }

        return true;
    };

    // === PDF ===
    exportPdfVentasBtn.addEventListener("click", e => {
        e.preventDefault();
        if (!validarFormulario()) return;

        const inicio = reportsVentasForm.querySelector("#reportStartDateVentas").value;
        const fin = reportsVentasForm.querySelector("#reportEndDateVentas").value;

        // Enviar con los mismos nombres que espera el controlador
        const params = new URLSearchParams({
            fecha_inicio: inicio,
            fecha_fin: fin
        });

        const url = apiUrl("/admin/resumen-ventas/pdf") + "?" + params.toString();
        window.open(url, "_blank");
    });

    // === EXCEL ===
    exportExcelVentasBtn.addEventListener("click", e => {
        e.preventDefault();
        if (!validarFormulario()) return;

        const inicio = reportsVentasForm.querySelector("#reportStartDateVentas").value;
        const fin = reportsVentasForm.querySelector("#reportEndDateVentas").value;

        const params = new URLSearchParams({
            fecha_inicio: inicio,
            fecha_fin: fin
        });

        const url = apiUrl("/admin/resumen-ventas/excel") + "?" + params.toString();
        window.open(url, "_blank");
    });
});
