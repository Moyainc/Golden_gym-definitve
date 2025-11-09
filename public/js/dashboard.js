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
        } else if (number >= 1e6) {
            formatted = number / 1e6;
            suffix = "M";
        } else if (number >= 1e3) {
            formatted = number / 1e3;
            suffix = "k";
        } else {
            // Para números pequeños no mostrar decimales
            return { formatted: number.toString(), suffix: "" };
        }

        formatted = formatted.toFixed(decimals);
        return { formatted, suffix };
    };

    const updateCards = (filter = "week") => {
        fetch(apiUrl(`admin/dashboard/data/cards?filter=${filter}`))
            .then((res) => res.json())
            .then((data) => {
                const ventas = formatNumber(data.ventas);
                const membresias = formatNumber(data.membresias);
                const total = formatNumber(data.total);
                const clientes = formatNumber(data.clientes);

                document.querySelector(
                    "#cardProductos h1"
                ).innerHTML = `<span title="${data.ventas}">${ventas.formatted}${ventas.suffix}</span>`;

                document.querySelector(
                    "#cardMembresias h1"
                ).innerHTML = `<span title="${data.membresias}">${membresias.formatted}${membresias.suffix}</span>`;

                document.querySelector(
                    "#cardTotal h1"
                ).innerHTML = `<span title="${data.total}">${total.formatted}${total.suffix}</span>`;

                document.querySelector(
                    "#cardClientes h1"
                ).innerHTML = `<span title="${data.clientes}">${clientes.formatted}${clientes.suffix}</span>`;

                // Comparaciones
                document.querySelector(
                    "#cardProductos .comparison"
                ).textContent = `${data.comparisons.ventas}%`;

                document.querySelector(
                    "#cardMembresias .comparison"
                ).textContent = `${data.comparisons.membresias}%`;

                document.querySelector(
                    "#cardTotal .comparison"
                ).textContent = `${data.comparisons.total}%`;

                document.querySelector(
                    "#cardClientes .comparison"
                ).textContent = `${data.comparisons.clientes}%`;
            });
    };

    // Inicial
    updateCards("week");

    // Filtros
    document
        .querySelectorAll(
            "#filterProductos, #filterMembresias, #filterTotal, #filterClientes"
        )
        .forEach((select) => {
            select.addEventListener("change", (e) => {
                const filter = e.target.value.toLowerCase();
                updateCards(filter);
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
                    <td title="${m.monto}">$${Number(m.monto).toLocaleString("es-CO", { minimumFractionDigits: 0 })}</td>
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
