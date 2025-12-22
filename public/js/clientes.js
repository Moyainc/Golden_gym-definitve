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
            vencida: '<span class="badge bg-dark bg-opacity-75">Vencida</span>',
            anulado:
                '<span class="badge bg-danger bg-opacity-50">Anulado</span>',
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
    let currentSearch = "";

    async function loadClients(page = 1) {
        try {
            const res = await fetch(
                apiUrl(
                    `admin/clientes/list/json?page=${page}&estado=${currentFilter}&search=${encodeURIComponent(currentSearch)}`
                )
            );
            if (!res.ok) throw new Error("Error al cargar clientes");
            const data = await res.json();

            allClients = data.data || [];
            clientsCountSpan.textContent = data.pagination.total;

            // Usamos la página actual del backend
            currentPage = data.pagination.current_page;

           

            renderClients(allClients, currentPage, data.pagination.per_page);
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

    function renderClients(clients, current_page = 1, per_page = 10) {
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

                const globalIndex = (current_page - 1) * per_page + index + 1;

                const deleteButton =
                    window.userRole === "Administrador"
                        ? `<button class="btn btn-sm btn-outline-danger btn-delete" data-id="${cliente.id}" title="Eliminar">
                       <i data-feather="trash-2"></i>
                   </button>`
                        : "";

                return `<tr>
                    <td>${globalIndex}</td>
                   <td>
  <div class="photo-popup-container">
    <img src="${fotoUrl}" alt="Foto" class="rounded-circle"
         style="width:45px;height:45px;object-fit:cover;" />

    <img src="${fotoUrl}" class="photo-popup" alt="Foto ampliada"
         onerror="this.src='${getDefaultPhoto()}'" />
  </div>
</td>
                    <td class="fw-semibold text-white">${
                        cliente.nombre_completo
                    }</td>
                    <td class="text-muted">${cliente.cedula || "N/A"}</td>
                    <td>
                        <span class="badge bg-primary bg-opacity-75">${
                            cliente.estado === "anulado"
                                ? "Anulada"
                                : cliente.membresia_nombre || "Sin membresía"
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
                        <button class="btn btn-sm btn-outline-secondary me-1 btn-cancel-membership" data-id="${
                            cliente.id
                        }" title="Anular membresía actual">
                            <i data-feather="x-circle"></i>
                        </button>
                        ${deleteButton}
                    </td>
                </tr>`;
            })
            .join("");

        feather.replace();
    }

    document.addEventListener("click", async (e) => {
        if (e.target.closest(".btn-cancel-membership")) {
            const id = e.target.closest(".btn-cancel-membership").dataset.id;

            const confirmCancel = await Swal.fire({
                title: "¿Anular membresía?",
                text: "Esta acción anulará el último pago de membresía de este cliente. ¿Deseas continuar?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, anular",
                cancelButtonText: "Cancelar",
                background: "#1a1a1a",
                color: "#fff",
                confirmButtonColor: "#ffc107", // amarillo principal del sitio
                cancelButtonColor: "#6c757d", // gris suave para cancelar
            });

            if (confirmCancel.isConfirmed) {
                try {
                    const response = await fetch(
                        apiUrl(`admin/clientes/json/${id}/anular`),
                        {
                            method: "POST", // ✅ correcto según tus rutas
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector(
                                    'meta[name="csrf-token"]'
                                ).content,
                            },
                        }
                    );

                    // Intenta convertir la respuesta a JSON
                    const data = await response.json();

                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Membresía anulada correctamente",
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false,
                            background: "#1a1a1a",
                            color: "#fff",
                        });
                        loadClients(currentPage); // ✅ refresca la tabla
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error al anular",
                            text: data.message,
                            background: "#1a1a1a",
                            color: "#fff",
                        });
                    }
                } catch (error) {
                    console.error(error);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "No se pudo procesar la solicitud. Verifica la conexión o el servidor.",
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                }
            }
        }
    });

    

    document.querySelectorAll(".filter-btn").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            const filterValue = e.currentTarget.dataset.filter;

            // Reset todos los botones
            document.querySelectorAll(".filter-btn").forEach((b) => {
                b.classList.remove("btn-warning");
                b.classList.add("btn-dark");
                const span = b.querySelector("span");
                if (span) span.classList.remove("text-black");
                if (span) span.classList.add("text-light");
            });

            // Activar el botón seleccionado
            e.currentTarget.classList.remove("btn-dark");
            e.currentTarget.classList.add("btn-warning");
            const spanActive = e.currentTarget.querySelector("span");
            if (spanActive) {
                spanActive.classList.remove("text-light");
                spanActive.classList.add("text-black");
            }

            currentFilter = filterValue;
            currentPage = 1;
            loadClients(1);

        });
    });

    // === Búsqueda ===
    if (searchInput) {
    searchInput.addEventListener("input", (e) => {
        currentSearch = e.target.value.trim();
        currentPage = 1;
        loadClients(1);
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
    document.getElementById("clientFormModal")
    ?.addEventListener("show.bs.modal", (e) => {

    modalTitle.textContent = "Registrar Cliente";

    const clientId = document.getElementById("clientId").value;

    if (!clientId) {
        // Cargar borrador si existe
        const saved = localStorage.getItem("newClientDraft");
        if (saved) {
            const draft = JSON.parse(saved);
            for (let key in draft) {
                if (form.elements[key]) {
                    form.elements[key].value = draft[key];
                }
            }
        }
    }

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
                    localStorage.removeItem("newClientDraft");
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
                        function formatDate(dateString) {
                            if (!dateString) return "";
                            return dateString.split("T")[0].split(" ")[0]; // soporte para ambos formatos
                        }
                    document.getElementById("editStartDate").value =
                        formatDate(cliente.membresia_actual.fecha_inicio);
                    document.getElementById("editEndDate").value =
                        formatDate(cliente.membresia_actual.fecha_fin);
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
                        cliente.estado === "anulado"
                            ? "Anulada"
                            : cliente.membresia_actual?.membresia?.nombre ||
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
                    // === Obtener membresías ===
                    const resMem = await fetch(apiUrl("admin/membresias/list/json"));
                    const membresias = await resMem.json();

                    const optionsMemb = {};
                    membresias.forEach((m) => {
                        const durTxt = m.duracion
                            ? `${m.duracion} ${m.tipo_duracion === "meses" ? "mes" : "día"}${m.duracion > 1 ? "s" : ""}`
                            : "Sin duración";

                        optionsMemb[m.id] = `${m.nombre} - ${durTxt} - ${new Intl.NumberFormat(
                            "es-CO",
                            { style: "currency", currency: "COP", minimumFractionDigits: 0 }
                        ).format(m.precio)}`;
                    });

                    // === Modal SweetAlert Unificado ===
                    const { value: formValues } = await Swal.fire({
                        title: "Renovar membresía",
                        html: `
                <div style="margin-bottom: 15px; text-align:left;">
                    <label><b>Membresía</b></label>
                    <select id="swal-membresia" class="swal2-input" style="margin-top:6px; width:100%;">
                        <option value="">Seleccione...</option>
                        ${Object.entries(optionsMemb)
                                .map(([id, text]) => `<option value="${id}">${text}</option>`)
                                .join("")}
                    </select>
                </div>

                <div style="margin-bottom: 15px; text-align:left;">
                    <label><b>Método de pago</b></label>
                    <select id="swal-metodo" class="swal2-input" style="margin-top:6px; width:100%;">
                        <option value="">Seleccione...</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </div>

                <div style="margin-bottom: 15px; text-align:left;">
                    <label><b>Fecha inicio</b></label>
                    <input type="date" id="swal-fecha-inicio" class="swal2-input" style="margin-top:6px; width:80%;">
                </div>
            `,
                        background: "#2d2d2d",
                        color: "#fff",
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: "Renovar",
                        cancelButtonText: "Cancelar",
                        customClass: {
                            popup: "swal-dark-popup",
                            actions: "swal-actions-centered"
                        },
                        didOpen: () => {
                            // Fecha por defecto
                            document.getElementById("swal-fecha-inicio").value =
                                new Date().toISOString().slice(0, 10);

                            // Estilos de inputs
                            document.querySelectorAll(".swal2-input").forEach((el) => {
                                el.style.background = "#1b1b1b";
                                el.style.color = "#fff";
                                el.style.border = "1px solid #555";
                                el.style.borderRadius = "6px";
                            });

                            // Estilo botones SweetAlert
                            const confirmBtn = Swal.getConfirmButton();
                            const cancelBtn = Swal.getCancelButton();

                            confirmBtn.style.background = "#685aff";
                            confirmBtn.style.padding = "10px 20px";
                            confirmBtn.style.borderRadius = "6px";

                            cancelBtn.style.background = "#6c757d";
                            cancelBtn.style.padding = "10px 20px";
                            cancelBtn.style.borderRadius = "6px";

                            // Centrar botones
                            Swal.getActions().style.justifyContent = "center";
                            Swal.getActions().style.gap = "15px";
                            Swal.getActions().style.marginTop = "20px";
                        },
                        preConfirm: () => {
                            const membresia = document.getElementById("swal-membresia").value;
                            const metodo = document.getElementById("swal-metodo").value;
                            const fechaInicio = document.getElementById("swal-fecha-inicio").value;

                            if (!membresia) return Swal.showValidationMessage("Debe seleccionar una membresía");
                            if (!metodo) return Swal.showValidationMessage("Debe seleccionar un método de pago");
                            if (!fechaInicio) return Swal.showValidationMessage("Debe seleccionar una fecha de inicio");

                            return { membresia, metodo, fechaInicio };
                        }
                    });

                    if (!formValues) return;

                    // === Enviar al backend ===
                    const formData = new FormData();
                    formData.append("membresia_id", formValues.membresia);
                    formData.append("metodo_pago", formValues.metodo);
                    formData.append("fecha_inicio", formValues.fechaInicio);
                    formData.append("usuario_id", "{{ auth()->user()->id }}");

                    const res = await fetch(apiUrl(`admin/clientes/json/${clientId}/renovar`), {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                            Accept: "application/json",
                        },
                        body: formData,
                    });

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
                        throw new Error(data.message || "Error al renovar");
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
        exportPdfBtn.addEventListener("click", async () => {
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

            try {
                const response = await fetch(
                    apiUrl(`admin/clientes/export/pdf?${params.toString()}`)
                );

                // Verificar si la respuesta es JSON (error 404)
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
                    const data = await response.json();
                    if (!data.success) {
                        Swal.fire({
                            icon: "info",
                            title: "Sin resultados",
                            text: data.message,
                            background: "#1a1a1a",
                            color: "#fff",
                        });
                        return;
                    }
                }

                // Si todo está bien, descarga el PDF
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = `reporte-clientes-${fechaInicio}_a_${fechaFin}.pdf`;
                a.click();
                window.URL.revokeObjectURL(url);

                bootstrap.Modal.getInstance(
                    document.getElementById("reportsModal")
                ).hide();
            } catch (error) {
                console.error("Error al generar el PDF:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Ocurrió un error al generar el reporte.",
                    background: "#1a1a1a",
                    color: "#fff",
                });
            }
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

            // 👇 Hacemos fetch para validar antes de descargar
            fetch(apiUrl(`admin/clientes/export/excel?${params.toString()}`))
                .then(async (response) => {
                    if (!response.ok) {
                        const errorData = await response.json();
                        Swal.fire({
                            icon: "info",
                            title: "Sin resultados",
                            text:
                                errorData.message ||
                                "No se encontraron clientes en ese rango de fechas.",
                            background: "#1a1a1a",
                            color: "#fff",
                        });
                        throw new Error("Sin clientes");
                    }

                    // Si hay datos, descargamos el archivo
                    return response.blob();
                })
                .then((blob) => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement("a");
                    a.href = url;
                    a.download = `reporte-clientes_${fechaInicio}_al_${fechaFin}.xlsx`;
                    a.click();
                    window.URL.revokeObjectURL(url);
                    bootstrap.Modal.getInstance(
                        document.getElementById("reportsModal")
                    ).hide();
                })
                .catch((error) => {
                    if (error.message !== "Sin clientes") {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Ocurrió un problema al generar el Excel.",
                            background: "#1a1a1a",
                            color: "#fff",
                        });
                    }
                });
        });
    }

    // === Inicializar ===
    loadMemberships();
    loadClients();
});
