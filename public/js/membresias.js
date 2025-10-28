document.addEventListener("DOMContentLoaded", () => {
    const membershipsTableBody = document.getElementById(
        "membershipsTableBody"
    );
    const membershipModalEl = document.getElementById("membershipModal");
    const membershipModal = new bootstrap.Modal(membershipModalEl);
    const saveMembershipBtn = document.getElementById("saveMembership");
    const membershipForm = document.getElementById("membershipForm");

    let editingMembershipId = null;
    let membershipIdToDelete = null;

    // 🔹 Modal de eliminación (nuevo)
    const deleteModalEl = document.getElementById("deleteMembershipModal");
    const deleteModal = new bootstrap.Modal(deleteModalEl);
    const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");

    // Render tabla
    const renderMemberships = (membresias, metricsData) => {
        membershipsTableBody.innerHTML = "";

        membresias.forEach((m) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${m.nombre}</td>
                <td>${m.duracion_meses}</td>
                <td>$${m.precio}</td>
                <td>${m.activo}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-light me-1 edit-membership-btn"
                        data-id="${m.id}"
                        data-nombre="${m.nombre}"
                        data-precio="${m.precio}"
                        data-duracion="${m.duracion_meses}"
                        data-activo="${m.activo}"
                        data-bs-toggle="modal"
                        data-bs-target="#membershipModal">
                        <i data-feather="edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-membership-btn"
                        data-id="${m.id}"
                        data-nombre="${m.nombre}"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteMembershipModal">
                        <i data-feather="trash-2"></i>
                    </button>
                </td>`;
            membershipsTableBody.appendChild(row);
        });

        if (typeof feather !== "undefined") feather.replace();
        attachMembershipEvents();
    };

    // Cargar membresías
    const loadMemberships = async () => {
        try {
            const res = await fetch(apiUrl("/admin/membresias/list"));
            const data = await res.json();
            renderMemberships(data.membresias);

            // 🔹 Reasignar instancia modal si algo cambió en el DOM
            bootstrap.Modal.getInstance(membershipModalEl)?.dispose();
            new bootstrap.Modal(membershipModalEl);
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "No se pudieron cargar las membresías", "error");
        }
    };

    // Abrir modal para crear/editar
    membershipModalEl.addEventListener("show.bs.modal", (event) => {
        const button = event.relatedTarget;
        const title = membershipModalEl.querySelector(".modal-title");
        editingMembershipId = null;
        membershipForm.reset();

        if (button.id === "btnAddMembership") {
            title.innerHTML = `<i data-feather="plus-circle" class="me-1"></i> Nueva membresía`;
            saveMembershipBtn.textContent = "Guardar";
        } else {
            editingMembershipId = button.dataset.id;
            title.innerHTML = `<i data-feather="plus-circle" class="me-1"></i> Editar membresía`;
            saveMembershipBtn.textContent = "Actualizar";

            membershipForm.querySelector("input[name='nombre']").value =
                button.dataset.nombre;
            membershipForm.querySelector("input[name='precio']").value =
                button.dataset.precio;
            membershipForm.querySelector("input[name='duracion_meses']").value =
                button.dataset.duracion;
            membershipForm.querySelector("select[name='activo']").value =
                button.dataset.activo;
        }

        if (typeof feather !== "undefined") feather.replace();
    });

    // Guardar/Actualizar membresía
    saveMembershipBtn.addEventListener("click", async (e) => {
        e.preventDefault();

        const formData = {
            nombre: membershipForm.querySelector("input[name='nombre']").value,
            precio: membershipForm.querySelector("input[name='precio']").value,
            duracion_meses: membershipForm.querySelector(
                "input[name='duracion_meses']"
            ).value,
            activo: membershipForm.querySelector("select[name='activo']").value,
        };

        try {
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");
            const url = editingMembershipId
                ? apiUrl(`admin/membresias/${editingMembershipId}/edit`)
                : apiUrl("admin/membresias");
            const method = editingMembershipId ? "PUT" : "POST";
            
            const res = await fetch(url, {
                method,
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify(formData),
            });

            const data = await res.json();

            if (res.ok) {
                Swal.fire("Éxito", data.message, "success");
                membershipForm.reset();

                // 🔹 Asegurarte de tener la instancia activa del modal antes de cerrarlo
                const modalInstance =
                    bootstrap.Modal.getInstance(membershipModalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }

                await loadMemberships();
            } else {
                Swal.fire(
                    "Error",
                    data.message || "No se pudo guardar",
                    "error"
                );
            }
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "Error al conectar con el servidor", "error");
        }
    });

    // Editar/Eliminar eventos
    const attachMembershipEvents = () => {
        // 🔹 Ya no usamos SweetAlert aquí — solo abrimos el modal real
        document.querySelectorAll(".delete-membership-btn").forEach((btn) => {
            btn.addEventListener("click", () => {
                membershipIdToDelete = btn.dataset.id;
                deleteModal.show(); // abre el modal de eliminación
            });
        });
    };

    // 🔹 Confirmar eliminación desde el modal Bootstrap
    confirmDeleteBtn.addEventListener("click", async () => {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");

        try {
            const res = await fetch(
                `/admin/membresias/${membershipIdToDelete}`,
                {
                    method: "DELETE",
                    headers: { "X-CSRF-TOKEN": csrfToken },
                }
            );
            const data = await res.json();

            if (res.ok) {
                deleteModal.hide(); // cierra el modal
                Swal.fire("Eliminado", data.message, "success");
                await loadMemberships(); // recarga la tabla
            } else {
                Swal.fire("Error", data.message, "error");
            }
        } catch (err) {
            Swal.fire("Error", "No se pudo conectar con el servidor", "error");
        }
    });

    loadMemberships();
});
