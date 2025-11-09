document.addEventListener("DOMContentLoaded", () => {
    const dailyPaymentForm = document.getElementById("dailyPaymentForm")
    const dailyPaymentsTableBody = document.getElementById("dailyPaymentsTableBody")
    const dailyPaymentDate = document.getElementById("dailyPaymentDate")
    const paginationContainer = document.getElementById("paginationContainer")
    const Swal = window.Swal
    const feather = window.feather
    const bootstrap = window.bootstrap

    let currentPage = 1
    const perPage = 5

    // === Helpers ===
    function apiUrl(path) {
        return `${window.app.baseUrl}/${path}`
    }

    // === Fecha predeterminada ===
    if (dailyPaymentDate) {
        const now = new Date()
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset())
        dailyPaymentDate.value = now.toISOString().split("T")[0]
    }

    // === Cargar pagos diarios ===
    async function loadDailyPayments(page = 1) {
        try {
            const res = await fetch(apiUrl(`admin/pagos-diarios/list/json?page=${page}`))
            if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`)

            const contentType = res.headers.get("content-type")
            if (!contentType || !contentType.includes("application/json")) {
                throw new Error("El servidor no devolvió JSON válido")
            }

            const resData = await res.json()
            renderDailyPayments(resData.data || [])
            renderPagination(resData.pagination || {})
        } catch (err) {
            console.error("Error cargando pagos diarios:", err)
            dailyPaymentsTableBody.innerHTML = `
            <tr>
              <td colspan="6" class="text-center text-danger">
                <strong>Error al cargar pagos diarios:</strong> ${err.message}
              </td>
            </tr>`
        }
    }

    // === Formato de fecha ===
    function formatDateDisplay(dateString) {
        if (!dateString) return "—"
        let date
        if (dateString.includes("T")) date = new Date(dateString)
        else date = new Date(dateString + "T00:00:00")
        if (isNaN(date)) return "—"
        return date.toLocaleDateString("es-CO", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        })
    }

    // === Renderizar pagos en tabla ===
    function renderDailyPayments(pagos) {
        if (!pagos.length) {
            dailyPaymentsTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <i data-feather="info" class="me-1"></i> No hay pagos diarios registrados
                </td>
            </tr>`
            feather.replace()
            return
        }

        dailyPaymentsTableBody.innerHTML = pagos
            .map((pago, index) => {
                const metodoBadge = {
                    efectivo: '<span class="badge bg-success">Efectivo</span>',
                    transferencia: '<span class="badge bg-info">Transferencia</span>',
                    tarjeta: '<span class="badge bg-warning text-dark">Tarjeta</span>',
                }

                return `
                <tr>
                    <td>${(currentPage - 1) * perPage + index + 1}</td>
                    <td class="fw-semibold text-white">${pago.nombre_cliente || "—"}</td>
                    <td class="text-center">
                        <span class="badge bg-primary text-white fw-bold">Pago Diario</span>
                    </td>
                    <td class="text-muted small">${formatDateDisplay(pago.fecha)}</td>
                    <td>${metodoBadge[pago.metodo_pago] || pago.metodo_pago || "—"}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-danger btn-delete-payment"
                            data-id="${pago.id}" title="Eliminar">
                            <i data-feather="trash-2"></i>
                        </button>
                    </td>
                </tr>`
            })
            .join("")
        feather.replace()
    }

    function renderPagination(pagination) {
        // Si el backend no envía current_page, usa la variable global
        const activePage = pagination.current_page || currentPage || 1
        const totalPaginas = pagination.total_pages || 1
        const paginacionContainer = document.getElementById("paginacionPagosDiarios")
        if (!paginacionContainer) return

        let html = ""

        // Botón anterior
        if (activePage > 1) {
            html += `
            <button class="btn btn-sm btn-outline-warning text-light mx-1"
                    data-page="${activePage - 1}">
                &laquo;
            </button>`
        }

        // Botones numerados
        for (let i = 1; i <= totalPaginas; i++) {
            const isActive = i === activePage

            html += `
            <button class="btn btn-sm mx-1 ${isActive
                    ? "btn-warning fw-bold text-dark" // fondo amarillo + texto negro
                    : "btn-outline-warning text-light" // borde amarillo + texto blanco
                }" 
            data-page="${i}"
            style="${isActive ? 'color:#000 !important;' : ''}">
                ${i}
            </button>`
        }

        // Botón siguiente
        if (activePage < totalPaginas) {
            html += `
            <button class="btn btn-sm btn-outline-warning text-light mx-1"
                    data-page="${activePage + 1}">
                &raquo;
            </button>`
        }

        paginacionContainer.innerHTML = html

        // Delegación de eventos
        paginacionContainer.querySelectorAll("button[data-page]").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                const page = parseInt(e.target.getAttribute("data-page"))
                if (!isNaN(page) && page >= 1 && page <= totalPaginas) {
                    currentPage = page
                    loadDailyPayments(page)
                }
            })
        })
    }
    // === Registrar nuevo pago diario ===
    if (dailyPaymentForm) {
        dailyPaymentForm.addEventListener("submit", async (e) => {
            e.preventDefault()
            const formData = new FormData(dailyPaymentForm)

            try {
                const res = await fetch(apiUrl("admin/pagos-diarios/json"), {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                        Accept: "application/json",
                    },
                    body: formData,
                })

                const data = await res.json()

                if (data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Pago diario registrado",
                        timer: 1500,
                        showConfirmButton: false,
                        background: "#1a1a1a",
                        color: "#fff",
                    })

                    const modal = bootstrap.Modal.getInstance(document.getElementById("dailyPaymentModal"))
                    modal.hide()
                    dailyPaymentForm.reset()
                    dailyPaymentDate.valueAsDate = new Date()
                    loadDailyPayments(currentPage)
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error al guardar el pago",
                        text: data.message || "Revisa los campos e inténtalo de nuevo.",
                        background: "#1a1a1a",
                        color: "#fff",
                    })
                }
            } catch (error) {
                Swal.fire({
                    icon: "error",
                    title: "Error en la solicitud",
                    text: error.message || "No se pudo enviar el formulario.",
                    background: "#1a1a1a",
                    color: "#fff",
                })
            }
        })
    }

    // === Eliminar pago diario ===
    if (dailyPaymentsTableBody) {
        dailyPaymentsTableBody.addEventListener("click", async (e) => {
            const btn = e.target.closest("button")
            if (!btn) return

            const paymentId = btn.dataset.id

            if (btn.classList.contains("btn-delete-payment")) {
                const result = await Swal.fire({
                    title: "¿Eliminar este pago?",
                    text: "Esta acción no se puede deshacer",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, eliminar",
                    cancelButtonText: "Cancelar",
                    background: "#1a1a1a",
                    color: "#fff",
                })

                if (result.isConfirmed) {
                    try {
                        const formData = new FormData()
                        formData.append("_method", "DELETE")

                        const res = await fetch(apiUrl(`admin/pagos-diarios/json/${paymentId}`), {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                                Accept: "application/json",
                            },
                            body: formData,
                        })

                        const data = await res.json()

                        if (data.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Pago eliminado",
                                timer: 1500,
                                showConfirmButton: false,
                                background: "#1a1a1a",
                                color: "#fff",
                            })
                            loadDailyPayments(currentPage)
                        } else {
                            throw new Error("Error al eliminar")
                        }
                    } catch (err) {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "No se pudo eliminar el pago",
                            background: "#1a1a1a",
                            color: "#fff",
                        })
                    }
                }
            }
        })
    }

    // === Inicialización ===
    loadDailyPayments(currentPage)
})
