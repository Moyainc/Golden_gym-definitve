document.addEventListener("DOMContentLoaded", () => {
    // ---------------------------------------------------
    // VARIABLES DEL MODAL
    // ---------------------------------------------------
    const reportModalEl = document.getElementById("reportsModal");
    const reportModal = new bootstrap.Modal(reportModalEl, {
        backdrop: "static",
        keyboard: false,
    });
    const reportButton = document.querySelector(
        'button[data-report-type="membresias"]'
    );
    const reportStartDate = document.getElementById("reportStartDate");
    const reportEndDate = document.getElementById("reportEndDate");
    const includePagosDiarios = document.getElementById("includePagosDiarios");
    const modalPdfBtn = document.getElementById("exportPdfBtnModal");
    const modalExcelBtn = document.getElementById("exportExcelBtnModal");

    // ---------------------------------------------------
    // ABRIR MODAL DESDE BOTÓN
    // ---------------------------------------------------
    if (reportButton) {
        reportButton.addEventListener("click", () => {
            // Limpiar campos cada vez que se abre
            if (reportStartDate) reportStartDate.value = "";
            if (reportEndDate) reportEndDate.value = "";
            if (includePagosDiarios) includePagosDiarios.checked = false;

            reportModal.show();
        });
    }

    // ---------------------------------------------------
    // FUNCION DE EXPORTACIÓN DESDE MODAL
    // ---------------------------------------------------
    function exportModal(tipo) {
        const fechaInicio = reportStartDate?.value;
        const fechaFin = reportEndDate?.value;
        const incluirPagos = includePagosDiarios?.checked ? 1 : 0;

        if (!fechaInicio || !fechaFin) {
            Swal.fire({
                icon: "warning",
                title: "Campos requeridos",
                text: "Selecciona un rango de fechas",
                background: "#1a1a1a",
                color: "#fff",
            });
            return;
        }
        if (new Date(fechaInicio) > new Date(fechaFin)) {
            Swal.fire({
                icon: "warning",
                title: "Rango de fechas inválido",
                text: "La fecha de inicio no puede ser mayor que la fecha de fin.",
                confirmButtonColor: "#fcd52b",
                background: "#1e1e1e",
                color: "#fff",
            });
            return;
        }

        const params = new URLSearchParams({
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin,
            incluir_pagos_diarios: incluirPagos,
        });

        const url =
            tipo === "pdf"
                ? apiUrl(`admin/exportar/contabilidad-pdf?${params.toString()}`)
                : apiUrl(`admin/exportar/contabilidad-excel?${params.toString()}`);

        window.open(url, "_blank");
        reportModal.hide();
    }

    if (modalPdfBtn)
        modalPdfBtn.addEventListener("click", () => exportModal("pdf"));
    if (modalExcelBtn)
        modalExcelBtn.addEventListener("click", () => exportModal("excel"));

    // ---------------------------------------------------
    // INICIALIZAR FILTROS Y CARGAR DATOS
    // ---------------------------------------------------
    if (typeof inicializarFiltros === "function") inicializarFiltros();
    if (typeof cargarDatos === "function") cargarDatos();
    if (typeof cargarAniosDisponibles === "function") cargarAniosDisponibles();

    const applyFilterBtn = document.getElementById("applyFilter");
    const exportPdfBtnMain = document.getElementById("exportPdfBtn");
    const exportExcelBtnMain = document.getElementById("exportExcelBtn");
    const mesSelect = document.getElementById("filterMes");
    const anioSelect = document.getElementById("filterAnio");

    if (mesSelect) {
        mesSelect.addEventListener("change", () => {
            cargarDatos();
        });
    }

    if (anioSelect) {
        anioSelect.addEventListener("change", () => {
            cargarDatos();
        });
    }

    if (applyFilterBtn) {
        applyFilterBtn.addEventListener("click", (e) => {
            e.preventDefault();

            const fechaActual = new Date();
            const mesActual = (fechaActual.getMonth() + 1)
                .toString()
                .padStart(2, "0");
            const anioActual = fechaActual.getFullYear();


            if (mesSelect) mesSelect.value = mesActual;
            if (anioSelect) anioSelect.value = anioActual;

            cargarDatos();
        });
    }


    if (exportPdfBtnMain) {
        exportPdfBtnMain.addEventListener("click", () => {
            const mes = document.getElementById("filterMes")?.value;
            const anio = document.getElementById("filterAnio")?.value;
            if (!mes || !anio) return;
            window.location.href = apiUrl(`admin/exportar/contabilidad-pdf?mes=${mes}&anio=${anio}`);
        });
    }

    if (exportExcelBtnMain) {
        exportExcelBtnMain.addEventListener("click", () => {
            const mes = document.getElementById("filterMes")?.value;
            const anio = document.getElementById("filterAnio")?.value;
            if (!mes || !anio) return;
            window.location.href = apiUrl(`admin/exportar/contabilidad-excel?mes=${mes}&anio=${anio}`);
        });
    }
});
// ---------------------------------------------------
// VARIABLES GLOBALES PARA TABLA Y GRÁFICAS
// ---------------------------------------------------
let chartIngresos = null;
let chartTendencia = null;
let movimientosGlobal = [];
let paginaActual = 1;
const MOVIMIENTOS_POR_PAGINA = 10;

// ---------------------------------------------------
// FUNCIONES AUXILIARES
// ---------------------------------------------------
function inicializarFiltros() {
    const mesSelect = document.getElementById("filterMes");
    const anioSelect = document.getElementById("filterAnio");

    const fechaActual = new Date();
    const mesActual = fechaActual.getMonth() + 1;
    const anioActual = fechaActual.getFullYear();

    if (mesSelect) mesSelect.value = mesActual.toString().padStart(2, "0");
    if (anioSelect) anioSelect.value = anioActual;
}
// CARGAR AÑOS QUE TIENEN DATOS
function cargarAniosDisponibles() {
    fetch(apiUrl("admin/api/anios-disponibles"))
        .then((res) => res.json())
        .then((data) => {
            if (!data.success) return;
            const anioSelect = document.getElementById("filterAnio");
            if (!anioSelect) return;

            anioSelect.innerHTML = ""; // limpiar opciones
            data.anios.forEach((anio) => {
                const option = document.createElement("option");
                option.value = anio;
                option.textContent = anio;
                anioSelect.appendChild(option);
            });

            // Seleccionar último año por defecto
            anioSelect.value = Math.max(...data.anios);
        })
        .catch((err) => console.error("Error cargando años:", err));
}

// VALORES MONETARIOS ABREVIDADOS 1M
function formatearAbreviado(valor) {
    if (valor === null || valor === undefined || valor === "") return "$0";
    const numero = parseFloat(valor);
    if (isNaN(numero)) return "$0";

    if (numero >= 1_000_000_000)
        return `$${(numero / 1_000_000_000).toLocaleString("es-CO", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}B`;
    if (numero >= 1_000_000)
        return `$${(numero / 1_000_000).toLocaleString("es-CO", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}M`;
    if (numero >= 1_000)
        return `$${(numero / 1_000).toLocaleString("es-CO", {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        })}K`;

    return `$${numero.toLocaleString("es-CO", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

// VALORES MONETARIOS ABREVIADOS PARA GRÁFICAS
function formatearGrafica(valor) {
    if (valor >= 1_000_000) return "$" + (valor / 1_000_000).toFixed(1) + "M";
    if (valor >= 1_000) return "$" + (valor / 1_000).toFixed(0) + "K";
    return "$" + valor;
}
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

// ---------------------------------------------------
// CARGAR DATOS DESDE LA API
// ---------------------------------------------------
function cargarDatos() {
    const mes = document.getElementById("filterMes")?.value;
    const anio = document.getElementById("filterAnio")?.value;
    if (!mes || !anio) return;

    fetch(apiUrl(`admin/api/resumen-contabilidad?mes=${mes}&anio=${anio}`))
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                movimientosGlobal = data.movimientos.sort(
                    (a, b) => b.id - a.id
                );
                paginaActual = 1;
                actualizarCards(data.cards);
                mostrarPagina(paginaActual);
                actualizarGraficas(data.distribucion, data.tendencia);
            }
        })
        .catch((error) => console.error("[v3] Error cargando datos:", error));
}

// ---------------------------------------------------
// CARDS Y TABLA
// ---------------------------------------------------
function actualizarCards(cards) {
    if (!cards) return;
    document.getElementById("ingresoMembresias").textContent =
        formatearAbreviado(cards.ingreso_membresias);
    document.getElementById("ingresoDiarios").textContent = formatearAbreviado(
        cards.ingreso_diarios
    );
    document.getElementById("cantidadMembresias").textContent =
        cards.cantidad_membresias;
    document.getElementById("ingresoTotal").textContent = formatearAbreviado(
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


// Normaliza y mapea estados a etiquetas canónicas
function normalizarEstado(raw) {
    if (!raw && raw !== '') return 'Desconocido';
    const s = String(raw).trim().toLowerCase();

    if (['activa', 'activo', 'active'].includes(s)) return 'Activa';
    if (['próxima a vencer', 'proxima a vencer', 'próxima', 'proxima', 'por vencer'].includes(s)) return 'Próxima a vencer';
    if (['vencida', 'vencido', 'vencimiento'].includes(s)) return 'Vencida';
    if (['completada', 'completado', 'complet', 'pagado'].includes(s)) return 'Completada';
    if (['anulada', 'anulado', 'cancelada', 'cancelado'].includes(s)) return 'Anulada';

    return s.charAt(0).toUpperCase() + s.slice(1); // fallback capitalizado
}

function actualizarTabla(movimientos) {
    const tbody = document.getElementById("contabilidadTable");
    if (!tbody) return;

    if (!movimientos || movimientos.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="7" class="text-center text-muted">No hay datos para este período</td></tr>';
        return;
    }

    tbody.innerHTML = movimientos
        .map((mov) => {
            const estadoMostrar = normalizarEstado(mov.estado);

            return `
                <tr>
                    <td>
                        <span class="badge ${mov.tipo === "Membresía"
                    ? "bg-warning text-dark border border-dark"
                    : "bg-info text-dark"
                }">${mov.tipo}</span>
                    </td>
                    <td>${mov.nombre_completo}</td>
                    <td>${mov.nombre_usuario}</td>
                    <td>${mov.metodo_pago}</td>
                    <td class="fw-bold">${formatearCOP(mov.valor)}</td>
                    <td>${new Date(mov.fecha).toLocaleDateString("es-CO")}</td>
                    <td>
                        <span class="badge ${getEstadoBadgeClass(estadoMostrar)}">
                            ${estadoMostrar}
                        </span>
                    </td>
                </tr>
            `;
        })
        .join("");
}

// Filtro dinámico de búsqueda
document.addEventListener("input", (e) => {
    if (e.target.id === "filtroMovimientos") {
        const filtro = e.target.value.toLowerCase();
        const filas = document.querySelectorAll("#contabilidadTable tr");

        filas.forEach((fila) => {
            const textoFila = fila.textContent.toLowerCase();
            fila.style.display = textoFila.includes(filtro) ? "" : "none";
        });
    }
});


// ---------------------------------------------------
// PAGINACIÓN
// ---------------------------------------------------
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
            <button class="btn btn-sm ${i === paginaActual
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

// ---------------------------------------------------
// BADGES DE ESTADO
// ---------------------------------------------------
function getEstadoBadgeClass(estadoRaw) {
    const estado = normalizarEstado(estadoRaw);
    switch (estado) {
        case "Activa":
            return "bg-success";
        case "Próxima a vencer":
            return "bg-warning text-dark";
        case "Vencida":
            return "bg-danger";
        case "Completada":
            return "bg-primary";
        case "Anulada":
            return "bg-secondary text-light border border-light";
        default:
            return "bg-secondary";
    }
}


// ---------------------------------------------------
// GRÁFICAS
// ---------------------------------------------------

function actualizarGraficas(distribucion, tendencia) {
    // ---------------------------------------------------
    // GRÁFICO DE DISTRIBUCIÓN (DOUGHNUT)
    // ---------------------------------------------------
    const ctxIngresos = document
        .getElementById("chartIngresos")
        ?.getContext("2d");
    if (!ctxIngresos) return;
    if (chartIngresos) chartIngresos.destroy();

    const labelsConValores = [
        `Membresías ($${parseFloat(distribucion.membresias).toLocaleString(
            "es-CO"
        )})`,
        `Pagos Diarios ($${parseFloat(distribucion.diarios).toLocaleString(
            "es-CO"
        )})`,
    ];

    chartIngresos = new Chart(ctxIngresos, {
        type: "doughnut",
        data: {
            labels: labelsConValores,
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
                    labels: {
                        color: "#ccc",
                        font: { size: 14 },
                    },
                },
                title: { display: false },
                tooltip: {
                    callbacks: {
                        // Tooltip con valor completo en formato COP
                        label: function (context) {
                            const label = context.label.split(" (")[0]; // evita repetir el valor
                            const valor = context.raw;
                            return `${label}: $ ${parseFloat(
                                valor
                            ).toLocaleString("es-CO")}`;
                        },
                    },
                },
            },
            animation: {
                duration: 1200,
                easing: "easeOutQuart",
            },
        },
    });

    // ---------------------------------------------------
    // GRÁFICO DE TENDENCIA (LINE)
    // ---------------------------------------------------

    const fechaActual = new Date();
    const opciones = { month: "long", year: "numeric" };
    let mesActual = fechaActual.toLocaleDateString("es-ES", opciones);

    // Convertir primera letra a mayúscula
    mesActual = mesActual.charAt(0).toUpperCase() + mesActual.slice(1);


    const ctxTendencia = document
        .getElementById("chartTendencia")
        ?.getContext("2d");
    if (!ctxTendencia) return;
    if (chartTendencia) chartTendencia.destroy();

    const diasLabels = tendencia.map((d) => d.dia);
    const diasData = tendencia.map((d) => d.total);

    chartTendencia = new Chart(ctxTendencia, {
        type: "line",
        data: {
            labels: diasLabels,
            datasets: [
                {
                    label: `Tendencia de Ingresos (${mesActual})`,
                    data: diasData,
                    borderColor: "#FFD54F",
                    backgroundColor: "rgba(255, 213, 79, 0.2)",
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: diasData.map((v) => (v === 0 ? 0 : 5)),
                    pointHoverRadius: diasData.map((v) => (v === 0 ? 0 : 7)),
                    pointBackgroundColor: "#FFD54F",
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true },
                title: { display: false },
                tooltip: {
                    callbacks: {
                        // Tooltip con valor completo en formato COP
                        label: function (context) {
                            const valor = context.parsed.y;
                            return `$ ${parseFloat(valor).toLocaleString(
                                "es-CO"
                            )}`;
                        },
                    },
                },
            },
            scales: {
                y: {
                    ticks: {
                        color: "#cccc",
                        callback: function (value) {
                            return formatearGrafica(value); // formato abreviado en el eje
                        },
                    },
                    grid: { color: "rgba(255,255,255,0.1)" },
                },
                x: {
                    ticks: { color: "#cccc" },
                    grid: { color: "rgba(255,255,255,0.1)" },
                },
            },
        },
    });
}
