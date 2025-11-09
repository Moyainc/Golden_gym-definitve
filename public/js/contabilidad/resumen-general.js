document.addEventListener("DOMContentLoaded", () => {
    feather.replace();

    const ruta = "/admin/resumen-general/datos";
    const monthSelect = document.getElementById("filterMonth");
    const yearSelect = document.getElementById("filterYear");
    const applyFilter = document.getElementById("applyFilter");

    const cards = {
        membresias: document.getElementById("membresiasTotal"),
        productos: document.getElementById("productosTotal"),
        pagosDiarios: document.getElementById("pagosDiariosTotal"),
        general: document.getElementById("totalGeneral"),
    };

    const eyeButtons = document.querySelectorAll(".btn-eye");

    // === Estados individuales para cada card ===
    const mostrarCompletoPorCard = {
        membresiasTotal: false,
        productosTotal: false,
        pagosDiariosTotal: false,
        totalGeneral: false,
    };

    // === Formateadores ===
    const formatearAbreviado = (valor) => {
        if (valor >= 1_000_000_000)
            return (
                "$" +
                (valor / 1_000_000_000).toFixed(1).replace(/\.0$/, "") +
                "B"
            );
        if (valor >= 1_000_000)
            return (
                "$" + (valor / 1_000_000).toFixed(1).replace(/\.0$/, "") + "M"
            );
        if (valor >= 1_000)
            return "$" + (valor / 1_000).toFixed(1).replace(/\.0$/, "") + "K";
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

    // === Actualizar totales ===
    const actualizarCards = (totales) => {
        const asignarValor = (elemento, valor, id) => {
            elemento.dataset.valor = valor; // Guarda valor numérico real
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

    // === Actualizar tabla de últimos pagos ===
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
                    <button class="btn btn-sm btn-outline-info ver-detalle" data-pago='${JSON.stringify(
                        pago
                    )}'>
                        <i data-feather="eye"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(fila);
        });

        feather.replace();

        // Modal con valor completo
        document.querySelectorAll(".ver-detalle").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                const pago = JSON.parse(e.currentTarget.dataset.pago);

                document.getElementById("detailDate").textContent = pago.fecha;
                document.getElementById("detailConcept").textContent =
                    pago.concepto;

                // Convertir a número y formatear correctamente a COP sin decimales
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
                document.getElementById("detailStatus").textContent =
                    "Completado";

                const modal = new bootstrap.Modal(
                    document.getElementById("recordDetailModal")
                );
                modal.show();
            });
        });
    };

    // === Graficas ===
    let chartBarras, chartPastel, chartLinea;

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
        const ctxLine = document
            .getElementById("revenueLineChart")
            .getContext("2d");

        if (chartBarras) chartBarras.destroy();
        if (chartPastel) chartPastel.destroy();
        if (chartLinea) chartLinea.destroy();

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
                    x: { ticks: { color: "#fff" } },
                    y: {
                        ticks: {
                            color: "#fff",
                            callback: (val) => formatearAbreviado(val),
                        },
                    },
                },
                plugins: { legend: { labels: { color: "#fff" } } },
            },
        });

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

        chartLinea = new Chart(ctxLine, {
            type: "line",
            data: {
                labels: data.tendencia.map((t) => t.periodo),
                datasets: [
                    {
                        label: "Tendencia",
                        data: data.tendencia.map((t) => t.total),
                        borderColor: "#fcd52b",
                        tension: 0.3,
                    },
                ],
            },
            options: {
                scales: {
                    x: { ticks: { color: "#fff" } },
                    y: {
                        ticks: {
                            color: "#fff",
                            callback: (val) => formatearAbreviado(val),
                        },
                    },
                },
                plugins: { legend: { labels: { color: "#fff" } } },
            },
        });
    };

    // === Cargar datos ===
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

    // Actualiza los datos automáticamente al cambiar el año o el mes
    monthSelect.addEventListener("change", () => cargarDatos());
    yearSelect.addEventListener("change", () => cargarDatos());

    //  Control individual de cada ojitoo
    eyeButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            const targetId = btn.dataset.target;
            const elemento = document.getElementById(targetId);
            const valor = parseFloat(elemento.dataset.valor);

            mostrarCompletoPorCard[targetId] =
                !mostrarCompletoPorCard[targetId];
            const mostrarCompleto = mostrarCompletoPorCard[targetId];

            elemento.textContent = mostrarCompleto
                ? formatearCompleto(valor)
                : formatearAbreviado(valor);
            btn.innerHTML = mostrarCompleto
                ? '<i data-feather="eye-off"></i>'
                : '<i data-feather="eye"></i>';
            feather.replace();
        });
    });

    cargarDatos();
});
