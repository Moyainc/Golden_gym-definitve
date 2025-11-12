document.addEventListener("DOMContentLoaded", async () => {
    /* ============================================================
     📅 FLATPICKR – Calendario Dashboard
  ============================================================ */
    const calendar = document.getElementById("datetimepicker-dashboard");
    if (calendar) {
        flatpickr(calendar, {
            inline: true,
            dateFormat: "Y-m-d",
            defaultDate: "today",
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: [
                        "Dom",
                        "Lun",
                        "Mar",
                        "Mié",
                        "Jue",
                        "Vie",
                        "Sáb",
                    ],
                    longhand: [
                        "Domingo",
                        "Lunes",
                        "Martes",
                        "Miércoles",
                        "Jueves",
                        "Viernes",
                        "Sábado",
                    ],
                },
                months: {
                    shorthand: [
                        "Ene",
                        "Feb",
                        "Mar",
                        "Abr",
                        "May",
                        "Jun",
                        "Jul",
                        "Ago",
                        "Sep",
                        "Oct",
                        "Nov",
                        "Dic",
                    ],
                    longhand: [
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
                    ],
                },
            },
        });
    }
});

document.addEventListener("DOMContentLoaded", () => {
    feather.replace();

    const formatNumber = (number, decimals = 2) => {
        let formatted = number;
        let suffix = "";

        if (number >= 1e9) {
            formatted = number / 1e9;
            suffix = "B";
            formatted = formatted.toFixed(2); // mantiene 2 decimales
        } else if (number >= 1e6) {
            formatted = number / 1e6;
            suffix = "M";
            formatted = formatted.toFixed(2); // mantiene 2 decimales
        } else if (number >= 1e3) {
            formatted = number / 1e3;
            suffix = "k";
            formatted = formatted.toFixed(0); // sin decimales
        } else {
            // Para números pequeños no mostrar decimales
            return { formatted: number.toString(), suffix: "" };
        }

        return { formatted, suffix };
    };

    const updateCard = (cardId, filter) => {
        // Normaliza el filtro por si viene en mayúsculas o con espacios
        const normalized = filter.trim().toLowerCase();

        return fetch(apiUrl(`admin/dashboard/data/cards?filter=${normalized}`))
            .then((res) => res.json())
            .then((data) => {
                const format = (num) => {
                    if (num >= 1e9) return (num / 1e9).toFixed(2) + "B";
                    if (num >= 1e6) return (num / 1e6).toFixed(2) + "M";
                    if (num >= 1e3) return (num / 1e3).toFixed(0) + "k";
                    return num;
                };

                // Aplica color al porcentaje según si subió, bajó o se mantuvo
                const setComparisonColor = (el, value) => {
                    el.textContent = `${value}%`;
                    el.classList.remove(
                        "text-success",
                        "text-danger",
                        "text-muted"
                    );

                    if (value > 0) el.classList.add("text-success");
                    else if (value < 0) el.classList.add("text-danger");
                    else el.classList.add("text-muted");
                };

                switch (cardId) {
                    case "Productos":
                        document.querySelector(
                            "#cardProductos h1"
                        ).innerHTML = `<span title="${data.ventas}">${format(
                            data.ventas
                        )}</span>`;
                        setComparisonColor(
                            document.querySelector(
                                "#cardProductos .comparison"
                            ),
                            data.comparisons.ventas
                        );
                        break;

                    case "Membresias":
                        document.querySelector(
                            "#cardMembresias h1"
                        ).innerHTML = `<span title="${
                            data.membresias
                        }">${format(data.membresias)}</span>`;
                        setComparisonColor(
                            document.querySelector(
                                "#cardMembresias .comparison"
                            ),
                            data.comparisons.membresias
                        );
                        break;

                    case "Total":
                        document.querySelector(
                            "#cardTotal h1"
                        ).innerHTML = `<span title="${data.total}">${format(
                            data.total
                        )}</span>`;
                        setComparisonColor(
                            document.querySelector("#cardTotal .comparison"),
                            data.comparisons.total
                        );
                        break;

                    case "Clientes":
                        document.querySelector(
                            "#cardClientes h1"
                        ).innerHTML = `<span title="${data.clientes}">${format(
                            data.clientes
                        )}</span>`;
                        setComparisonColor(
                            document.querySelector("#cardClientes .comparison"),
                            data.comparisons.clientes
                        );
                        break;
                }
            })
            .catch((err) => {
                console.error("Error updateCard", cardId, err);
            });
    };

    // Inicializamos todas las cards en "Semana"
    Promise.all([
        updateCard("Productos", "week"),
        updateCard("Membresias", "week"),
        updateCard("Total", "week"),
        updateCard("Clientes", "week"),
    ]).then(() => feather.replace());

    // Ahora escuchamos cada select por separado
    const filters = {
        filterProductos: "Productos",
        filterMembresias: "Membresias",
        filterTotal: "Total",
        filterClientes: "Clientes",
    };

    Object.entries(filters).forEach(([selectId, cardId]) => {
        const select = document.getElementById(selectId);
        if (!select) return;

        select.addEventListener("change", (e) => {
            const filter = e.target.value.toLowerCase(); // semana o mes
            updateCard(cardId, filter);
        });
    });

    // Gráfico mensual total
    fetch(apiUrl("admin/dashboard/data/chart/monthly"))
        .then((res) => res.json())
        .then((data) => {
            const ctx = document
                .getElementById("chartjs-dashboard-line")
                .getContext("2d");

            // Gradiente para el fondo
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, "rgba(252, 213, 43, 0.4)");
            gradient.addColorStop(1, "rgba(252, 213, 43, 0)");

            // Mapeo abreviaturas ingles a español
            const mesesMap = {
                Jan: "Ene",
                Feb: "Feb",
                Mar: "Mar",
                Apr: "Abr",
                May: "May",
                Jun: "Jun",
                Jul: "Jul",
                Aug: "Ago",
                Sep: "Sep",
                Oct: "Oct",
                Nov: "Nov",
                Dec: "Dic",
            };

            new Chart(ctx, {
                type: "line",
                data: {
                    // Convertimos cada etiqueta al español
                    labels: data.labels.map(
                        (label) => mesesMap[label] || label
                    ),
                    datasets: [
                        {
                            label: "Ingresos Totales",
                            data: data.totals,
                            borderColor: "#fcd52b",
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.3,
                            pointRadius: 5,
                            pointHoverRadius: 8,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: function (tooltipItems) {
                                    // Mostramos el mes completo en español
                                    const mesesLong = {
                                        Ene: "Enero",
                                        Feb: "Febrero",
                                        Mar: "Marzo",
                                        Abr: "Abril",
                                        May: "Mayo",
                                        Jun: "Junio",
                                        Jul: "Julio",
                                        Ago: "Agosto",
                                        Sep: "Septiembre",
                                        Oct: "Octubre",
                                        Nov: "Noviembre",
                                        Dic: "Diciembre",
                                    };
                                    const abrev = tooltipItems[0].label;
                                    return mesesLong[abrev] || abrev;
                                },
                                label: function (tooltipItem) {
                                    // Valor completo formateado tipo $200.000 sin decimales
                                    return `$ ${Number(
                                        tooltipItem.raw
                                    ).toLocaleString("es-CO", {
                                        maximumFractionDigits: 0,
                                    })}`;
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: function (value) {
                                    const formatted = formatNumber(value); // eje Y abreviado
                                    return (
                                        formatted.formatted + formatted.suffix
                                    );
                                },
                            },
                        },
                    },
                },
            });
        });

    // Gráfico mensual membresías
    fetch(apiUrl("admin/dashboard/data/chart/membership"))
        .then((res) => res.json())
        .then((data) => {
            const ctx = document
                .getElementById("chartjs-dashboard-bar")
                .getContext("2d");

            // Gradiente para las barras (puedes comentar estas líneas para desactivarlo)
            // const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            // gradient.addColorStop(0, "rgba(252, 213, 43, 0.8)");
            // gradient.addColorStop(1, "rgba(252, 213, 43, 0.2)");

            // Mapeo abreviaturas ingles → español
            const mesesMap = {
                Jan: "Ene",
                Feb: "Feb",
                Mar: "Mar",
                Apr: "Abr",
                May: "May",
                Jun: "Jun",
                Jul: "Jul",
                Aug: "Ago",
                Sep: "Sep",
                Oct: "Oct",
                Nov: "Nov",
                Dec: "Dic",
            };

            new Chart(ctx, {
                type: "bar",
                data: {
                    // Traducimos los labels al español
                    labels: data.labels.map(
                        (label) => mesesMap[label] || label
                    ),
                    datasets: [
                        {
                            label: "Membresías",
                            data: data.totals,
                            backgroundColor: "#fcd52b", // color sólido si no quieres degradado
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: function (tooltipItems) {
                                    // Mes completo en español
                                    const mesesLong = {
                                        Ene: "Enero",
                                        Feb: "Febrero",
                                        Mar: "Marzo",
                                        Abr: "Abril",
                                        May: "Mayo",
                                        Jun: "Junio",
                                        Jul: "Julio",
                                        Ago: "Agosto",
                                        Sep: "Septiembre",
                                        Oct: "Octubre",
                                        Nov: "Noviembre",
                                        Dic: "Diciembre",
                                    };
                                    const abrev = tooltipItems[0].label;
                                    return mesesLong[abrev] || abrev;
                                },
                                label: function (tooltipItem) {
                                    // Valor formateado en pesos sin decimales
                                    return `$ ${Number(
                                        tooltipItem.raw
                                    ).toLocaleString("es-CO", {
                                        maximumFractionDigits: 0,
                                    })}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            ticks: {
                                callback: function (val, index) {
                                    const label = data.labels[index];
                                    return mesesMap[label] || label; // abreviaturas en español
                                },
                            },
                        },
                        y: {
                            ticks: {
                                callback: function (value) {
                                    const formatted = formatNumber(value); // eje Y abreviado
                                    return (
                                        formatted.formatted + formatted.suffix
                                    );
                                },
                            },
                        },
                    },
                },
            });
        });

    const formatTipo = (tipo) => {
        // Reemplaza guiones bajos por espacios y capitaliza cada palabra
        return tipo
            .split("_")
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(" ");
    };

    const loadMovements = () => {
        fetch(apiUrl("admin/dashboard/data/last-movements"))
            .then((res) => res.json())
            .then((data) => {
                const tbody = document.querySelector("table tbody");
                tbody.innerHTML = "";

                data.forEach((m) => {
                    // Si el cliente es "-", poner "Venta rápida"
                    const cliente =
                        m.cliente && m.cliente.trim() !== "-"
                            ? m.cliente
                            : "Venta rápida";

                    const row = document.createElement("tr");
                    row.innerHTML = `
        <td>${formatTipo(m.tipo)}</td>
        <td>${cliente}</td>
        <td title="${m.monto}">$${Number(m.monto).toLocaleString("es-CO", {
                        minimumFractionDigits: 0,
                    })}</td>
        <td>${m.fecha}</td>
    `;
                    tbody.appendChild(row);
                });

                feather.replace();
            });
    };

    loadMovements();
});
