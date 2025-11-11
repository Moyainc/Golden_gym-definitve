document.addEventListener("DOMContentLoaded", () => {
    feather.replace();

    const ruta = "/admin/resumen-general/datos";
    const monthSelect = document.getElementById("filterMonth");
    const yearSelect = document.getElementById("filterYear");

    const cards = {
        membresias: document.getElementById("membresiasTotal"),
        productos: document.getElementById("productosTotal"),
        pagosDiarios: document.getElementById("pagosDiariosTotal"),
        general: document.getElementById("totalGeneral"),
    };

    const eyeButtons = document.querySelectorAll(".btn-eye");

    const mostrarCompletoPorCard = {
        membresiasTotal: false,
        productosTotal: false,
        pagosDiariosTotal: false,
        totalGeneral: false,
    };

    // Formateadores
    const formatearAbreviado = (valor) => {
        if (valor >= 1_000_000_000) {
            // Billones: 2 decimales
            return (
                "$" +
                (valor / 1_000_000_000).toFixed(2).replace(/\.?0+$/, "") +
                "B"
            );
        }
        if (valor >= 1_000_000) {
            // Millones: 2 decimales
            return (
                "$" + (valor / 1_000_000).toFixed(2).replace(/\.?0+$/, "") + "M"
            );
        }
        if (valor >= 1_000) {
            // Miles: sin decimales
            return "$" + Math.round(valor / 1_000) + "K";
        }
        return "$" + valor.toLocaleString("es-CO");
    };

    const formatearCompleto = (valor) => {
        return valor.toLocaleString("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0,
        });
    };

    const mostrarAlerta = (titulo, texto, icono = "error") => {
        Swal.fire({
            icon: icono,
            title: titulo,
            text: texto,
            background: "#1a1a1a",
            color: "#fff",
            confirmButtonColor: "#fcd52b",
            confirmButtonText: "Aceptar",
            customClass: { confirmButton: "text-black font-bold" },
        });
    };

    // Actualizar totales
    const actualizarCards = (totales) => {
        const asignarValor = (elemento, valor, id) => {
            elemento.dataset.valor = valor;
            elemento.textContent = formatearAbreviado(valor);
            mostrarCompletoPorCard[id] = false;
        };

        asignarValor(cards.membresias, totales.membresias, "membresiasTotal");
        asignarValor(cards.productos, totales.productos, "productosTotal");
        asignarValor(
            cards.pagosDiarios,
            totales.pagosDiarios,
            "pagosDiariosTotal"
        );
        asignarValor(cards.general, totales.general, "totalGeneral");
    };

    // Actualizar tabla últimos pagos
    const actualizarUltimos = (ultimos) => {
        const tbody = document.getElementById("latestRecords");
        tbody.innerHTML = "";

        if (!ultimos || ultimos.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-secondary">Sin registros recientes</td></tr>`;
            return;
        }

        ultimos.forEach((pago) => {
            const fila = document.createElement("tr");
            fila.innerHTML = `
                <td>${pago.fecha}</td>
                <td>${pago.concepto}</td>
                <td class="text-end">${formatearAbreviado(pago.monto)}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-warning ver-detalle" data-pago='${JSON.stringify(
                        pago
                    )}'>
                        <i data-feather="eye"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(fila);
        });

        feather.replace();

        document.querySelectorAll(".ver-detalle").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                const pago = JSON.parse(e.currentTarget.dataset.pago);

                document.getElementById("detailDate").textContent = pago.fecha;
                document.getElementById("detailConcept").textContent =
                    pago.concepto;

                const montoNumerico = Number(pago.monto);
                document.getElementById("detailValue").textContent =
                    montoNumerico.toLocaleString("es-CO", {
                        style: "currency",
                        currency: "COP",
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0,
                    });

                document.getElementById("detailMethod").textContent =
                    pago.metodo;

                // Asignar estado con colores
                const statusEl = document.getElementById("detailStatus");
                const estado = pago.estado || "Completada";
                statusEl.textContent = estado;

                // Limpiar clases previas
                // Limpiar clases previas
                statusEl.className = "px-2 py-1 rounded font-bold text-sm";

                // Asignar colores según estado (Bootstrap)
                switch (estado.toLowerCase()) {
                    case "activa":
                    case "completada":
                        statusEl.classList.add("bg-success", "text-white");
                        break;
                    case "proxima":
                    case "pendiente":
                        statusEl.classList.add("bg-warning", "text-dark");
                        break;
                    case "anulada":
                        statusEl.classList.add("bg-danger", "text-white");
                        break;
                    default:
                        statusEl.classList.add("bg-secondary", "text-white");
                }


                const modal = new bootstrap.Modal(
                    document.getElementById("recordDetailModal")
                );
                modal.show();
            });
        });
    };

    // Graficas
    let chartBarras, chartPastel, chartClientes;
    const nombresMeses = [
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

    const actualizarGraficas = (data) => {
        const ctxBar = document
            .getElementById("monthlyBarChart")
            .getContext("2d");
        const ctxPie = document
            .getElementById("earningsDoughnut")
            .getContext("2d");
        const ctxClients = document
            .getElementById("clientsChart")
            .getContext("2d");

        if (chartBarras) chartBarras.destroy();
        if (chartPastel) chartPastel.destroy();
        if (chartClientes) chartClientes.destroy();

        // Ingresos mensuales
        chartBarras = new Chart(ctxBar, {
            type: "bar",
            data: {
                labels: data.mensuales.map((m) => nombresMeses[m.mes - 1]),
                datasets: [
                    {
                        label: "Ingresos",
                        data: data.mensuales.map((m) => m.total),
                        backgroundColor: "#fcd52b",
                    },
                ],
            },
            options: {
                scales: {
                    x: { ticks: { color: "#ccc" } },
                    y: {
                        ticks: {
                            color: "#ccc",
                            callback: (val) => formatearAbreviado(val),
                        },
                    },
                },
                plugins: { legend: { labels: { color: "#ccc" } } },
            },
        });

        // Distribución por tipo de ingreso
        chartPastel = new Chart(ctxPie, {
            type: "doughnut",
            data: {
                labels: Object.keys(data.distribucion),
                datasets: [
                    {
                        data: Object.values(data.distribucion),
                        backgroundColor: ["#FFD700", "#4D96FF", "#FF6B6B"],
                    },
                ],
            },
            options: { plugins: { legend: { labels: { color: "#fff" } } } },
        });

        // Clientes activos vs nuevos
        chartClientes = new Chart(ctxClients, {
            type: "bar",
            data: {
                labels: data.clientes.meses, // ya vienen en formato "Nov 2025", "Dic 2025", etc.
                datasets: [
                    {
                        label: "Clientes activos",
                        data: data.clientes.activos.map((v) => v || 0),
                        backgroundColor: "rgba(54, 162, 235, 0.6)",
                        borderColor: "rgba(54, 162, 235, 1)",
                        borderWidth: 1,
                        order: 1,
                    },
                    {
                        label: "Clientes nuevos",
                        data: data.clientes.nuevos.map((v) => v || 0),
                        type: "line",
                        borderColor: "#fcd52b",
                        backgroundColor: "rgba(252,213,43,0.4)",
                        tension: 0.3,
                        borderWidth: 2,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: "#fcd52b",
                        order: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: "#fff", font: { size: 12 } } },
                    tooltip: {
                        backgroundColor: "#1a1a1a",
                        titleColor: "#fcd52b",
                        bodyColor: "#fff",
                        borderColor: "#333",
                        borderWidth: 1,
                    },
                },
                scales: {
                    x: {
                        ticks: { color: "#ccc" },
                        grid: { color: "rgba(255,255,255,0.1)" },
                    },
                    y: {
                        ticks: {
                            color: "#ccc",
                            stepSize: 1,
                            callback: (val) => Number(val),
                        },
                        grid: { color: "rgba(255,255,255,0.1)" },
                        beginAtZero: true,
                    },
                },
            },
        });
    };

    // Cargar datos
    const cargarDatos = async () => {
        const anio = yearSelect.value;
        const mes = monthSelect.value;
        let inicio = "",
            fin = "";

        if (mes && anio !== "todos") {
            const fechaInicio = new Date(anio, mes - 1, 1);
            const fechaFin = new Date(anio, mes, 0);
            inicio = fechaInicio.toISOString().split("T")[0];
            fin = fechaFin.toISOString().split("T")[0];
        }

        try {
            const url = apiUrl(
                `${ruta}?anio=${anio}&inicio=${inicio}&fin=${fin}`
            );
            const response = await fetch(url);
            if (!response.ok) throw new Error("Error en la petición");

            const data = await response.json();
            actualizarCards(data.totales);
            actualizarGraficas(data);
            actualizarUltimos(data.ultimos);
        } catch (err) {
            console.error(err);
            mostrarAlerta("Error", "No se pudieron cargar los datos.");
        }
    };

    // Actualizar al cambiar año o mes
    monthSelect.addEventListener("change", cargarDatos);
    yearSelect.addEventListener("change", cargarDatos);

    // Toggle de ojo para mostrar valores completos
    eyeButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            const targetId = btn.dataset.target;
            const elemento = document.getElementById(targetId);
            const valor = parseFloat(elemento.dataset.valor);

            mostrarCompletoPorCard[targetId] =
                !mostrarCompletoPorCard[targetId];
            elemento.textContent = mostrarCompletoPorCard[targetId]
                ? formatearCompleto(valor)
                : formatearAbreviado(valor);
            btn.innerHTML = mostrarCompletoPorCard[targetId]
                ? '<i data-feather="eye-off"></i>'
                : '<i data-feather="eye"></i>';
            feather.replace();
        });
    });

    cargarDatos();
});
