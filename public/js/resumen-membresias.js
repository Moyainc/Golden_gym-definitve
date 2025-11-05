document.addEventListener("DOMContentLoaded", () => {
    inicializarFiltros();
    cargarDatos();

    document
        .getElementById("applyFilter")
        .addEventListener("click", cargarDatos);
    document.getElementById("exportPdf").addEventListener("click", exportarPdf);
    document
        .getElementById("exportExcel")
        .addEventListener("click", exportarExcel);
});

let chartIngresos = null;
let chartTendencia = null;
let movimientosGlobal = []; // Guardará todos los movimientos
let paginaActual = 1;
const MOVIMIENTOS_POR_PAGINA = 10;

function inicializarFiltros() {
    const mesSelect = document.getElementById("filterMes");
    const anioSelect = document.getElementById("filterAnio");

    const fechaActual = new Date();
    const mesActual = fechaActual.getMonth() + 1;
    const anioActual = fechaActual.getFullYear();

    mesSelect.value = mesActual.toString().padStart(2, "0");
    anioSelect.value = anioActual;
}

// ✅ Función para formatear valores en pesos colombianos
function formatearCOP(valor) {
    if (valor === null || valor === undefined || valor === "") return "$0";
    const numero = parseFloat(valor);
    if (isNaN(numero)) return "$0";
    return numero.toLocaleString("es-CO", {
        style: "currency",
        currency: "COP",
        minimumFractionDigits: 0,
    });
}

function cargarDatos() {
    const mes = document.getElementById("filterMes").value;
    const anio = document.getElementById("filterAnio").value;

    fetch(apiUrl(`/admin/api/resumen-contabilidad?mes=${mes}&anio=${anio}`))
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                movimientosGlobal = data.movimientos.sort(
                    (a, b) => b.id - a.id
                ); // Orden DESC
                paginaActual = 1;
                actualizarCards(data.cards);
                mostrarPagina(paginaActual);
                actualizarGraficas(data.distribucion, data.tendencia);
            }
        })
        .catch((error) => console.error("[v3] Error cargando datos:", error));
}

function actualizarCards(cards) {
    document.getElementById("ingresoMembresias").textContent = formatearCOP(
        cards.ingreso_membresias
    );
    document.getElementById("ingresoDiarios").textContent = formatearCOP(
        cards.ingreso_diarios
    );
    document.getElementById("cantidadMembresias").textContent =
        cards.cantidad_membresias;
    document.getElementById("ingresoTotal").textContent = formatearCOP(
        cards.ingreso_total
    );
}

function mostrarPagina(numPagina) {
    const inicio = (numPagina - 1) * MOVIMIENTOS_POR_PAGINA;
    const fin = inicio + MOVIMIENTOS_POR_PAGINA;
    const movimientosPagina = movimientosGlobal.slice(inicio, fin);
    actualizarTabla(movimientosPagina);
    actualizarPaginacion();
}

function actualizarTabla(movimientos) {
    const tbody = document.getElementById("contabilidadTable");

    if (!movimientos || movimientos.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="5" class="text-center text-muted">No hay datos para este período</td></tr>';
        return;
    }

    tbody.innerHTML = movimientos
        .map(
            (mov) => `
        <tr>
            <td><span class="badge ${
                mov.tipo === "Membresía"
                    ? "bg-warning text-dark border border-dark"
                    : "bg-info text-dark"
            }">${mov.tipo}</span></td>
            <td>${mov.metodo_pago}</td>
            <td class="fw-bold">${formatearCOP(mov.valor)}</td>
            <td>${new Date(mov.fecha).toLocaleDateString("es-CO")}</td>
            <td><span class="badge ${getEstadoBadgeClass(mov.estado)}">${
                mov.estado
            }</span></td>
        </tr>`
        )
        .join("");
}

function actualizarPaginacion() {
    const totalPaginas = Math.ceil(
        movimientosGlobal.length / MOVIMIENTOS_POR_PAGINA
    );
    const paginacionContainer = document.getElementById(
        "paginacionMovimientos"
    );
    if (!paginacionContainer) return;

    let html = "";
    for (let i = 1; i <= totalPaginas; i++) {
        html += `
            <button class="btn btn-sm ${
                i === paginaActual
                    ? "btn-warning text-dark"
                    : "btn-outline-warning"
            } mx-1"
                    onclick="cambiarPagina(${i})">${i}</button>`;
    }
    paginacionContainer.innerHTML = html;
}

function cambiarPagina(pagina) {
    paginaActual = pagina;
    mostrarPagina(pagina);
}

function getEstadoBadgeClass(estado) {
    switch (estado) {
        case "Activa":
            return "bg-success";
        case "Próxima a vencer":
            return "bg-warning text-dark";
        case "Vencida":
            return "bg-danger";
        case "Completado":
            return "bg-primary";
        default:
            return "bg-secondary";
    }
}

function actualizarGraficas(distribucion, tendencia) {
    // === GRÁFICA DE DISTRIBUCIÓN ===
    const ctxIngresos = document
        .getElementById("chartIngresos")
        .getContext("2d");
    if (chartIngresos) chartIngresos.destroy();

    chartIngresos = new Chart(ctxIngresos, {
        type: "doughnut",
        data: {
            labels: ["Membresías", "Pagos Diarios"],
            datasets: [
                {
                    data: [distribucion.membresias, distribucion.diarios],
                    backgroundColor: ["#FFD54F", "#4FC3F7"],
                    borderColor: "#222",
                    borderWidth: 2,
                    hoverOffset: 8,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "bottom",
                    labels: { color: "#fff", font: { size: 14 } },
                },
                title: {
                    display: true,
                    text: "Distribución de Ingresos",
                    color: "#fff",
                    font: { size: 16, weight: "bold" },
                },
                tooltip: {
                    callbacks: {
                        label: (context) => formatearCOP(context.parsed),
                    },
                },
            },
        },
    });

    // === GRÁFICA DE TENDENCIA (LÍNEA) ===
    const ctxTendencia = document
        .getElementById("chartTendencia")
        .getContext("2d");
    if (chartTendencia) chartTendencia.destroy();

    const diasLabels = tendencia.map((d) => d.dia);
    const diasData = tendencia.map((d) => d.total);

    chartTendencia = new Chart(ctxTendencia, {
        type: "line",
        data: {
            labels: diasLabels,
            datasets: [
                {
                    label: "Ingresos Diarios",
                    data: diasData,
                    borderColor: "#FFD54F",
                    backgroundColor: "rgba(255, 213, 79, 0.2)",
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: "#FFD54F",
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: "#fff" } },
                title: {
                    display: true,
                    text: "Tendencia de Ingresos Diarios",
                    color: "#fff",
                    font: { size: 16, weight: "bold" },
                },
                tooltip: {
                    callbacks: {
                        label: (context) => formatearCOP(context.parsed.y),
                    },
                },
            },
            scales: {
                y: {
                    ticks: {
                        color: "#fff",
                        callback: (value) => formatearCOP(value),
                    },
                    grid: { color: "rgba(255,255,255,0.1)" },
                },
                x: {
                    ticks: { color: "#fff" },
                    grid: { color: "rgba(255,255,255,0.1)" },
                },
            },
        },
    });
}

function exportarPdf() {
    const mes = document.getElementById("filterMes").value;
    const anio = document.getElementById("filterAnio").value;
    window.location.href = `/golden-gym/public/admin/exportar/contabilidad-pdf?mes=${mes}&anio=${anio}`;
}

function exportarExcel() {
    const mes = document.getElementById("filterMes").value;
    const anio = document.getElementById("filterAnio").value;
    window.location.href = `/golden-gym/public/admin/exportar/contabilidad-excel?mes=${mes}&anio=${anio}`;
}
