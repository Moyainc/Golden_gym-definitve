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
            new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: "Ingresos Totales",
                            data: data.totals,
                            borderColor: "#fcd52b",
                            backgroundColor: "rgba(252, 213, 43, 0.2)",
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            ticks: {
                                callback: function (value) {
                                    const formatted = formatNumber(value);
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
            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: "Membresías",
                            data: data.totals,
                            backgroundColor: "#fcd52b",
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            ticks: {
                                callback: function (value) {
                                    const formatted = formatNumber(value);
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

    // Últimos movimientos
    const loadMovements = () => {
        fetch(apiUrl("admin/dashboard/data/last-movements"))
            .then((res) => res.json())
            .then((data) => {
                const tbody = document.querySelector("table tbody");
                tbody.innerHTML = "";
                data.forEach((m) => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                    <td>${formatTipo(m.tipo)}</td>
                    <td>${m.cliente}</td>
                    <td>${m.detalle}</td>
                    <td title="${m.monto}">$${Number(m.monto).toLocaleString(
                        "es-CO",
                        { minimumFractionDigits: 0 }
                    )}</td>
                    <td>${m.fecha}</td>
                    <td class="text-center">${m.metodo_pago}</td>
                `;
                    tbody.appendChild(row);
                });
                feather.replace();
            });
    };
    loadMovements();
});
