function closeModalProperly(modalId) {
    const modalEl = document.getElementById(modalId);
    if (!modalEl) return;
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) {
        modalInstance.hide();
        // Esperar a que termine la animación antes de eliminar la instancia
        modalEl.addEventListener(
            "hidden.bs.modal",
            () => {
                modalInstance.dispose(); // 💥 destruye la instancia para permitir reabrir
                const form = modalEl.querySelector("form");
                if (form) form.reset();
            },
            { once: true }
        );
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("clientForm");
    const photoInput = document.getElementById("clientPhoto");
    const photoPreview = document.getElementById("clientPhotoPreview");
    const membershipSelect = document.getElementById("clientMembershipType");
    const startInput = document.getElementById("clientMembershipStart");
    const endInput = document.getElementById("clientMembershipEnd");
    const priceInput = document.getElementById("membershipPrice");
    const tableBody = document.getElementById("clientsTableBody");
    const searchInput = document.getElementById("searchClients");
    const clientsCountSpan = document.getElementById("clientsCount");
    const clientsFilteredLabel = document.getElementById(
        "clientsFilteredLabel"
    );
    const modalTitle = document.getElementById("modalTitle");
    const Swal = window.Swal;
    const feather = window.feather;
    const bootstrap = window.bootstrap;
    const editClientPhotoPreview = document.getElementById(
        "editClientPhotoPreview"
    );

    let allClients = [];
    let currentFilter = "todas";
    let editingClientId = null;

    // === Helpers ===
    function apiUrl(path) {
        return `${window.app.baseUrl}/${path}`;
    }

    function addMonthsSafe(date, months) {
        const d = new Date(date.getTime());
        const day = d.getDate();
        d.setDate(1);
        d.setMonth(d.getMonth() + months);
        const daysInTargetMonth = new Date(
            d.getFullYear(),
            d.getMonth() + 1,
            0
        ).getDate();
        d.setDate(Math.min(day, daysInTargetMonth));
        return d;
    }

    function formatDateLocalYMD(date) {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, "0");
        const dd = String(date.getDate()).padStart(2, "0");
        return `${yyyy}-${mm}-${dd}`;
    }

    function formatDateDisplay(dateString) {
        if (!dateString) return "N/A";
        const parts = dateString.split("-");
        if (parts.length === 3) {
            const date = new Date(
                parts[0],
                Number.parseInt(parts[1]) - 1,
                parts[2]
            );
            return date.toLocaleDateString("es-CO", {
                year: "numeric",
                month: "short",
                day: "numeric",
            });
        }
        return "N/A";
    }

    function getEstadoBadge(estado) {
        const badges = {
            activa: '<span class="badge bg-success bg-opacity-75">Activa</span>',
            proxima:
                '<span class="badge bg-warning bg-opacity-75 text-dark">Próxima a vencer</span>',
            vencida:
                '<span class="badge bg-danger bg-opacity-75">Vencida</span>',
            sin_membresia:
                '<span class="badge bg-secondary bg-opacity-75">Sin membresía</span>',
        };
        return badges[estado] || badges["sin_membresia"];
    }

    function getDefaultPhoto() {
        return apiUrl("/img/avatars/user.png");
    }

    // === Vista previa de foto ===
    if (photoInput) {
        photoInput.addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    photoPreview.src = ev.target.result;
                    photoPreview.style.display = "block";
                };
                reader.readAsDataURL(file);
            } else {
                photoPreview.src = getDefaultPhoto();
            }
        });
    }

    const editClientPhoto = document.getElementById("editClientPhoto");
    if (editClientPhoto) {
        editClientPhoto.addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    editClientPhotoPreview.src = ev.target.result;
                    editClientPhotoPreview.style.display = "block";
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // === Cargar membresías ===
    async function loadMemberships() {
        try {
            const res = await fetch(apiUrl("admin/membresias/list/json"));
            if (!res.ok) throw new Error("Error en la solicitud");
            const data = await res.json();
            membershipSelect.innerHTML = "";
            if (!data.length) {
                membershipSelect.innerHTML = `<option value="">No hay membresías disponibles</option>`;
                return;
            }
            membershipSelect.innerHTML = `<option value="">-- Seleccione una membresía --</option>`;
            data.forEach((m) => {
                const duracionTexto = m.duracion
                    ? `${m.duracion} ${
                          m.tipo_duracion === "meses" ? "mes" : "día"
                      }${m.duracion > 1 ? "s" : ""}`
                    : "Sin duración";
                membershipSelect.innerHTML += `<option value="${m.id}" data-precio="${m.precio}" data-duracion="${m.duracion}" data-tipo="${m.tipo_duracion}" data-nombre="${m.nombre}"> ${m.nombre} (${duracionTexto}) </option>`;
            });
        } catch (err) {
            console.error("Error cargando membresías:", err);
            membershipSelect.innerHTML = `<option value="">Error al cargar membresías</option>`;
        }
    }

    async function loadEditMemberships() {
        try {
            const res = await fetch(apiUrl("admin/membresias/list/json"));
            if (!res.ok) throw new Error("Error en la solicitud");
            const data = await res.json();
            const editMembershipSelect = document.getElementById(
                "editClientMembershipType"
            );
            editMembershipSelect.innerHTML = `<option value="">Selecciona una membresía</option>`;
            if (!data.length) {
                editMembershipSelect.innerHTML = `<option value="">No hay membresías disponibles</option>`;
                return data;
            }
            data.forEach((m) => {
                const duracionTexto = m.duracion
                    ? `${m.duracion} ${
                          m.tipo_duracion === "meses" ? "mes" : "día"
                      }${m.duracion > 1 ? "s" : ""}`
                    : "Sin duración";
                editMembershipSelect.innerHTML += `<option value="${m.id}" data-precio="${m.precio}" data-duracion="${m.duracion}" data-tipo="${m.tipo_duracion}" data-nombre="${m.nombre}"> ${m.nombre} (${duracionTexto}) </option>`;
            });
            return data;
        } catch (err) {
            console.error("Error cargando membresías:", err);
            return [];
        }
    }

    // === Cargar clientes ===
    let currentPage = 1;
    async function loadClients(page = 1) {
        try {
            const res = await fetch(
                apiUrl(`admin/clientes/list/json?page=${page}`)
            );
            if (!res.ok) throw new Error("Error al cargar clientes");
            const data = await res.json();
            allClients = data.data || [];
            clientsCountSpan.textContent = allClients.length;
            applyCurrentFilter();
            renderClientPagination(data.pagination);
        } catch (err) {
            console.error("Error cargando clientes:", err);
            tableBody.innerHTML = `<tr> <td colspan="9" class="text-center text-danger">Error al cargar clientes</td> </tr>`;
        }
    }

    function renderClientPagination(pagination) {
        const totalPaginas = pagination.total_pages || 1;
        const paginacionContainer =
            document.getElementById("paginacionClientes");
        if (!paginacionContainer) return;
        let html = "";
        if (pagination.current_page > 1) {
            html += `<button class="btn btn-sm btn-outline-warning text-light mx-1" data-page="${
                pagination.current_page - 1
            }"> &laquo; </button>`;
        }
        for (let i = 1; i <= totalPaginas; i++) {
            const isActive = i === pagination.current_page;
            html += `<button class="btn btn-sm mx-1 ${
                isActive
                    ? "btn-warning fw-bold text-dark"
                    : "btn-outline-warning text-light"
            }" data-page="${i}" style="${
                isActive ? "color:#000 !important;" : ""
            }"> ${i} </button>`;
        }
        if (pagination.current_page < totalPaginas) {
            html += `<button class="btn btn-sm btn-outline-warning text-light mx-1" data-page="${
                pagination.current_page + 1
            }"> &raquo; </button>`;
        }
        paginacionContainer.innerHTML = html;
        paginacionContainer
            .querySelectorAll("button[data-page]")
            .forEach((btn) => {
                btn.addEventListener("click", (e) => {
                    const page = parseInt(e.target.getAttribute("data-page"));
                    if (!isNaN(page)) {
                        currentPage = page;
                        loadClients(page);
                    }
                });
            });
    }

    function renderClients(clients) {
        if (!clients.length) {
            tableBody.innerHTML = `<tr> <td colspan="9" class="text-center text-muted">No hay clientes registrados</td> </tr>`;
            return;
        }
        tableBody.innerHTML = clients
            .map((cliente, index) => {
                const fotoUrl =
                    cliente.foto && cliente.foto !== ""
                        ? `${window.app.baseUrl}/storage/${cliente.foto}`
                        : apiUrl("/img/avatars/user.png");

                    return `
                    <tr>

                    <td>${index + 1}</td>
                    <td>
                        <img src="${fotoUrl}" alt="Foto" class="rounded-circle" style="width: 45px; height: 45px; object-fit: cover;" onerror="this.src='${getDefaultPhoto()}'" />
                    </td>
                    <td class="fw-semibold text-white">${
                        cliente.nombre_completo
                    }</td>
                    <td class="text-muted">${cliente.cedula || "N/A"}</td>
                    <td>
                        <span class="badge bg-primary bg-opacity-75">${
                            cliente.membresia_nombre || "Sin membresía"
                        }</span>
                    </td>
                    <td class="text-muted small">${formatDateDisplay(
                        cliente.fecha_inicio
                    )}</td>
                    <td class="text-muted small">${formatDateDisplay(
                        cliente.fecha_fin
                    )}</td>
                    <td>${getEstadoBadge(cliente.estado)}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-success me-1 btn-view" data-id="${
                            cliente.id
                        }" title="Ver cliente">
                            <i data-feather="eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning me-1 btn-edit" data-id="${
                            cliente.id
                        }" title="Editar">
                            <i data-feather="edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info me-1 btn-renew" data-id="${
                            cliente.id
                        }" title="Renovar membresía">
                            <i data-feather="refresh-cw"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${
                            cliente.id
                        }" title="Eliminar">
                            <i data-feather="trash-2"></i>
                        </button>
                    </td>
                </tr>`;
            })
            .join("");
        feather.replace();
    }

    // === Filtros ===
    function applyCurrentFilter() {
        let filtered = allClients;
        if (currentFilter === "activa") {
            filtered = allClients.filter((c) => c.estado === "activa");
        } else if (currentFilter === "proxima") {
            filtered = allClients.filter((c) => c.estado === "proxima");
        } else if (currentFilter === "vencida") {
            filtered = allClients.filter((c) => c.estado === "vencida");
        }
        renderClients(filtered);
    }

    document.querySelectorAll(".filter-btn").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            const filterValue = e.currentTarget.dataset.filter;
            document.querySelectorAll(".filter-btn").forEach((b) => {
                b.classList.remove("btn-warning");
                b.classList.add("btn-dark");
            });
            e.currentTarget.classList.remove("btn-dark");
            e.currentTarget.classList.add("btn-warning");
            currentFilter = filterValue;
            const filterLabels = {
                activa: "Activas",
                proxima: "Próximas a vencer",
                vencida: "Vencidas",
                todas: "Todas",
            };
            clientsFilteredLabel.textContent =
                filterLabels[filterValue] || "Todas";
            applyCurrentFilter();
        });
    });

    // === Búsqueda ===
    if (searchInput) {
        searchInput.addEventListener("input", (e) => {
            const query = e.target.value.toLowerCase();
            const filtered = allClients.filter(
                (c) =>
                    c.nombre_completo.toLowerCase().includes(query) ||
                    (c.correo && c.correo.toLowerCase().includes(query)) ||
                    (c.cedula && c.cedula.toLowerCase().includes(query))
            );
            renderClients(filtered);
        });
    }

    // === Calcular fechas y precio al elegir membresía ===
    if (membershipSelect) {
        membershipSelect.addEventListener("change", (e) => {
            const option = e.target.selectedOptions[0];
            if (!option || !option.value) {
                startInput.value = "";
                endInput.value = "";
                priceInput.value = "";
                return;
            }
            const duracion =
                Number.parseInt(option.getAttribute("data-duracion")) || 0;
            const tipoDuracion = option.getAttribute("data-tipo");
            const precio =
                Number.parseFloat(option.getAttribute("data-precio")) || 0;
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, "0");
            const dd = String(today.getDate()).padStart(2, "0");
            const startDate = `${yyyy}-${mm}-${dd}`;
            let endDate = today;
            if (tipoDuracion === "meses") {
                endDate = addMonthsSafe(today, duracion);
            } else if (tipoDuracion === "dias") {
                endDate = new Date(today);
                endDate.setDate(today.getDate() + duracion);
            }
            const endYyyy = endDate.getFullYear();
            const endMm = String(endDate.getMonth() + 1).padStart(2, "0");
            const endDd = String(endDate.getDate()).padStart(2, "0");
            const endDateStr = `${endYyyy}-${endMm}-${endDd}`;
            startInput.value = startDate;
            endInput.value = endDateStr;
            priceInput.value = new Intl.NumberFormat("es-CO", {
                style: "currency",
                currency: "COP",
                minimumFractionDigits: 0,
            }).format(precio);
        });
    }

    const createClientFormModal = new bootstrap.Modal(
        document.getElementById("clientFormModal")
    );
    const editClientFormModal = new bootstrap.Modal(
        document.getElementById("editClientModal")
    );

    // === Modal CREAR ===
    document
        .getElementById("clientFormModal")
        ?.addEventListener("show.bs.modal", (e) => {
            modalTitle.textContent = "Registrar Cliente";
            form.reset();
            photoPreview.src = getDefaultPhoto();
            photoPreview.style.display = "none";
            document.getElementById("clientId").value = "";
            editingClientId = null;
        });

    // === Modal EDITAR ===
    document
        .getElementById("editClientModal")
        ?.addEventListener("show.bs.modal", (e) => {});

    // === Crear/editar cliente ===
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const clientId = document.getElementById("clientId")?.value;
            const url = clientId
                ? apiUrl(`admin/clientes/json/${clientId}`)
                : apiUrl("admin/clientes/json");
            if (clientId) formData.append("_method", "PUT");
            try {
                const res = await fetch(url, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                        Accept: "application/json",
                    },
                    body: formData,
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Cliente registrado correctamente",
                        timer: 1500,
                        showConfirmButton: false,
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                    createClientFormModal.hide();
                    form.reset();
                    photoPreview.src = getDefaultPhoto();
                    loadClients();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error al guardar el cliente",
                        text:
                            data.message ||
                            "Revisa los campos e inténtalo de nuevo.",
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: "error",
                    title: "Error en la solicitud",
                    text: error.message,
                    background: "#1a1a1a",
                    color: "#fff",
                });
            }
        });
    }

    const editClientForm = document.getElementById("editClientForm");
    if (editClientForm) {
        editClientForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(editClientForm);
            const clientId = document.getElementById("editClientId")?.value;
            if (!clientId) return;
            formData.append("_method", "PUT");
            try {
                const res = await fetch(
                    apiUrl(`admin/clientes/json/${clientId}`),
                    {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                            Accept: "application/json",
                        },
                        body: formData,
                    }
                );
                const data = await res.json();
                if (data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Cliente actualizado correctamente",
                        timer: 1500,
                        showConfirmButton: false,
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                    editClientFormModal.hide();
                    loadClients();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error al actualizar",
                        text: data.message,
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: "error",
                    title: "Error en la solicitud",
                    text: error.message,
                    background: "#1a1a1a",
                    color: "#fff",
                });
            }
        });
    }

    // === Botones en tabla ===
    if (tableBody) {
        tableBody.addEventListener("click", async (e) => {
            const btn = e.target.closest("button");
            if (!btn) return;
            const clientId = btn.dataset.id;

            // EDITAR
            if (btn.classList.contains("btn-edit")) {
                try {
                    const res = await fetch(
                        apiUrl(`admin/clientes/json/${clientId}`)
                    );
                    const cliente = await res.json();
                    editingClientId = clientId;
                    document.getElementById("editClientId").value = cliente.id;
                    document.getElementById("editClientName").value =
                        cliente.nombre_completo;
                    document.getElementById("editClientEmail").value =
                        cliente.correo || "";
                    document.getElementById("editClientPhone").value =
                        cliente.telefono || "";
                    document.getElementById("editClientIdNumber").value =
                        cliente.cedula || "";
                    document.getElementById("editClientEps").value =
                        cliente.eps || "";
                    document.getElementById("editClientEmergency").value =
                        cliente.numero_emergencia || "";
                    await loadEditMemberships();
                    const editMembershipSelect = document.getElementById(
                        "editClientMembershipType"
                    );
                    if (editMembershipSelect && cliente.membresia_actual) {
                        editMembershipSelect.value =
                            cliente.membresia_actual.membresia_id;
                    }
                    if (cliente.foto) {
                        editClientPhotoPreview.src = `${window.app.baseUrl}/storage/${cliente.foto}`;
                        editClientPhotoPreview.style.display = "block";
                    } else {
                        editClientPhotoPreview.src = getDefaultPhoto();
                        editClientPhotoPreview.style.display = "none";
                    }
                    editClientFormModal.show();
                } catch (err) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "No se pudo cargar la información del cliente",
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                }
            }

            // VER CLIENTE
            if (btn.classList.contains("btn-view")) {
                try {
                    const res = await fetch(
                        apiUrl(`admin/clientes/json/${clientId}`)
                    );
                    const cliente = await res.json();
                    const viewModal = new bootstrap.Modal(
                        document.getElementById("viewClientModal")
                    );
                    document.getElementById("viewClientName").textContent =
                        cliente.nombre_completo;
                    document.getElementById("viewClientIdNumber").textContent =
                        cliente.cedula || "N/A";
                    document.getElementById("viewClientEmail").textContent =
                        cliente.correo || "N/A";
                    document.getElementById("viewClientPhone").textContent =
                        cliente.telefono || "N/A";
                    document.getElementById("viewClientEps").textContent =
                        cliente.eps || "N/A";
                    document.getElementById("viewClientEmergency").textContent =
                        cliente.numero_emergencia || "N/A";
                    document.getElementById(
                        "viewClientMembership"
                    ).textContent =
                        cliente.membresia_actual?.membresia?.nombre ||
                        "Sin membresía";
                    document.getElementById("viewClientStart").textContent =
                        cliente.fecha_inicio || "N/A";
                    document.getElementById("viewClientEnd").textContent =
                        cliente.fecha_fin || "N/A";
                    document.getElementById("viewClientEstado").innerHTML =
                        getEstadoBadge(cliente.estado);
                    document.getElementById("viewClientPhoto").src =
                        cliente.foto
                            ? `${window.app.baseUrl}/storage/${cliente.foto}`
                            : getDefaultPhoto();
                    viewModal.show();
                } catch (err) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "No se pudo cargar la información del cliente",
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                }
            }

            // RENOVAR
            if (btn.classList.contains("btn-renew")) {
                try {
                    const resMem = await fetch(
                        apiUrl("admin/membresias/list/json")
                    );
                    const membresias = await resMem.json();

                    const inputOptions = {};
                    membresias.forEach((m) => {
                        const duracionTexto = m.duracion
                            ? `${m.duracion} ${
                                  m.tipo_duracion === "meses" ? "mes" : "día"
                              }${m.duracion > 1 ? "s" : ""}`
                            : "Sin duración";
                        inputOptions[m.id.toString()] = `${
                            m.nombre
                        } - ${duracionTexto} - ${new Intl.NumberFormat(
                            "es-CO",
                            {
                                style: "currency",
                                currency: "COP",
                                minimumFractionDigits: 0,
                            }
                        ).format(m.precio)}`;
                    });

                    const { value: membresia_id } = await Swal.fire({
                        title: "Renovar membresía",
                        input: "select",
                        inputOptions: inputOptions,
                        inputPlaceholder: "Seleccione una membresía",
                        showCancelButton: true,
                        confirmButtonText: "Siguiente",
                        background: "#2d2d2d",
                        color: "#fff",
                        didOpen: () => {
                            const select =
                                Swal.getPopup().querySelector("select");
                            if (select) {
                                select.style.backgroundColor = "#1a1a1a";
                                select.style.color = "#fff";
                                select.style.border = "1px solid #555";
                                select.style.borderRadius = "6px";
                                select.style.padding = "8px";
                            }
                        },
                    });
                    if (!membresia_id) return;

                    const { value: metodo_pago } = await Swal.fire({
                        title: "Método de pago",
                        input: "select",
                        inputOptions: {
                            efectivo: "Efectivo",
                            transferencia: "Transferencia",
                        },
                        inputPlaceholder: "Seleccione el método de pago",
                        showCancelButton: true,
                        confirmButtonText: "Renovar",
                        background: "#2d2d2d",
                        color: "#fff",
                        didOpen: () => {
                            const select =
                                Swal.getPopup().querySelector("select");
                            if (select) {
                                select.style.backgroundColor = "#1a1a1a";
                                select.style.color = "#fff";
                                select.style.border = "1px solid #555";
                                select.style.borderRadius = "6px";
                                select.style.padding = "8px";
                            }
                        },
                    });
                    if (!metodo_pago) return;

                    const formData = new FormData();
                    formData.append("membresia_id", membresia_id);
                    formData.append("metodo_pago", metodo_pago);
                    const res = await fetch(
                        apiUrl(`admin/clientes/json/${clientId}/renovar`),
                        {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": document
                                    .querySelector('meta[name="csrf-token"]')
                                    .getAttribute("content"),
                                Accept: "application/json",
                            },
                            body: formData,
                        }
                    );
                    const data = await res.json();
                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Membresía renovada correctamente",
                            timer: 1500,
                            showConfirmButton: false,
                            background: "#1a1a1a",
                            color: "#fff",
                        });
                        loadClients();
                    } else {
                        throw new Error(
                            data.message || "No se pudo renovar la membresía"
                        );
                    }
                } catch (err) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: err.message,
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                }
            }

            // ELIMINAR
            if (btn.classList.contains("btn-delete")) {
                const result = await Swal.fire({
                    title: "¿Estás seguro?",
                    text: "Esta acción no se puede deshacer",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, eliminar",
                    cancelButtonText: "Cancelar",
                    background: "#1a1a1a",
                    color: "#fff",
                });
                if (result.isConfirmed) {
                    try {
                        const formData = new FormData();
                        formData.append("_method", "DELETE");
                        const res = await fetch(
                            apiUrl(`admin/clientes/json/${clientId}`),
                            {
                                method: "POST",
                                headers: {
                                    "X-CSRF-TOKEN": document
                                        .querySelector(
                                            'meta[name="csrf-token"]'
                                        )
                                        .getAttribute("content"),
                                    Accept: "application/json",
                                },
                                body: formData,
                            }
                        );
                        const data = await res.json();
                        if (data.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Cliente eliminado",
                                timer: 1500,
                                showConfirmButton: false,
                                background: "#1a1a1a",
                                color: "#fff",
                            });
                            loadClients();
                        }
                    } catch (err) {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "No se pudo eliminar el cliente",
                            background: "#1a1a1a",
                            color: "#fff",
                        });
                    }
                }
            }
        });
    }

    // === Exportar reportes ===
    const exportPdfBtn = document.getElementById("exportPdfBtn");
    const exportExcelBtn = document.getElementById("exportExcelBtn");
    const reportStartDate = document.getElementById("reportStartDate");
    const reportEndDate = document.getElementById("reportEndDate");
    const reportEstado = document.getElementById("reportEstado");
    const includePagosDiarios = document.getElementById("includePagosDiarios");

    if (reportStartDate && reportEndDate) {
        const today = new Date();
        const lastMonth = new Date();
        lastMonth.setMonth(today.getMonth() - 1);
        reportStartDate.valueAsDate = lastMonth;
        reportEndDate.valueAsDate = today;
    }

    if (exportPdfBtn) {
        exportPdfBtn.addEventListener("click", () => {
            const fechaInicio = reportStartDate.value;
            const fechaFin = reportEndDate.value;
            const estado = reportEstado.value;
            const incluirPagosDiarios = includePagosDiarios.checked ? 1 : 0;
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
                incluir_pagos_diarios: incluirPagosDiarios,
            });
            if (estado) params.append("estado", estado);
            window.open(
                apiUrl(`admin/clientes/export/pdf?${params.toString()}`),
                "_blank"
            );
            bootstrap.Modal.getInstance(
                document.getElementById("reportsModal")
            ).hide();
        });
    }

    if (exportExcelBtn) {
        exportExcelBtn.addEventListener("click", () => {
            const fechaInicio = reportStartDate.value;
            const fechaFin = reportEndDate.value;
            const estado = reportEstado.value;
            const incluirPagosDiarios = includePagosDiarios.checked ? 1 : 0;
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
                incluir_pagos_diarios: incluirPagosDiarios,
            });
            if (estado) params.append("estado", estado);
            window.open(
                apiUrl(`admin/clientes/export/excel?${params.toString()}`),
                "_blank"
            );
            bootstrap.Modal.getInstance(
                document.getElementById("reportsModal")
            ).hide();
        });
    }

    // === Inicializar ===
    loadMemberships();
    loadClients();
});
