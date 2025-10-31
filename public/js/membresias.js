document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("membershipForm");
    const saveBtn = document.getElementById("saveMembership");
    const tableBody = document.getElementById("membershipsTableBody");
    let editingMembershipId = null;

    const apiUrl = (path) => {
        const base = window.app?.baseUrl || window.location.origin;
        return `${base}/${path.replace(/^\/+/, "")}`;
    };
    const feather = window.feather;
    const bootstrap = window.bootstrap;
    const Swal = window.Swal;

    // 🚀 Cargar membresías
    loadMembresias();

    function loadMembresias() {
        fetch(apiUrl("admin/membresias/list/json"))
            .then((res) => res.json())
            .then((data) => {
                const tbody = document.getElementById("membershipsTableBody");
                tbody.innerHTML = "";

                if (!data || data.length === 0) {
                    tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">
                            No hay membresías registradas.
                        </td>
                    </tr>`;
                    feather.replace();
                    return;
                }

                data.forEach((m) => {
                    const duracion = m.duracion ? m.duracion : "—";
                    const tipoDuracion = m.tipo_duracion
                        ? m.tipo_duracion === "meses"
                            ? m.duracion == 1
                                ? "Mes"
                                : "Meses"
                            : m.duracion == 1
                            ? "Día"
                            : "Días"
                        : "—";
                    const precio = `$${parseInt(m.precio).toLocaleString(
                        "es-CO"
                    )}`;

                    const row = `
                    <tr data-id="${m.id}">
                        <td class="nombre">${m.nombre}</td>
                        <td class="duracion">${duracion}</td>
                        <td class="tipo_duracion">${tipoDuracion}</td>
                        <td class="precio text-nowrap">${precio}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-light me-1 editMembership" data-id="${m.id}">
                                <i data-feather="edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger deleteMembership" data-id="${m.id}">
                                <i data-feather="trash-2"></i>
                            </button>
                        </td>
                    </tr>`;
                    tbody.insertAdjacentHTML("beforeend", row);
                });

                feather.replace();
            })
            .catch((err) => console.error("Error cargando membresías:", err));
    }

    // ➕ NUEVA MEMBRESÍA
    document
        .getElementById("addMembershipBtn")
        .addEventListener("click", () => {
            editingMembershipId = null;
            form.reset();
            document.getElementById("membershipModalLabel").innerHTML =
                '<i data-feather="plus" class="me-1"></i> Nueva membresía';
            const modal = new bootstrap.Modal(
                document.getElementById("membershipModal")
            );
            modal.show();
            feather.replace();
        });

    // ✏️ EDITAR MEMBRESÍA
    tableBody.addEventListener("click", (e) => {
        const editBtn = e.target.closest(".editMembership");
        if (!editBtn) return;

        const id = editBtn.dataset.id;
        editingMembershipId = id;

        fetch(apiUrl(`admin/membresias/json/${id}`))
            .then((res) => res.json())
            .then((m) => {
                document.getElementById("membershipModalLabel").innerHTML =
                    '<i data-feather="edit" class="me-1"></i> Editar membresía';
                document.getElementById("nombre").value = m.nombre;
                document.getElementById("duracion").value = m.duracion ?? "";
                document.getElementById("tipo_duracion").value =
                    m.tipo_duracion ?? "dias";
                document.getElementById("precio").value = m.precio ?? "";

                const modal = new bootstrap.Modal(
                    document.getElementById("membershipModal")
                );
                modal.show();
                feather.replace();
            })
            .catch(() =>
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo cargar la información de la membresía",
                    background: "#1a1a1a",
                    color: "#fff",
                    confirmButtonColor: "#fcd52B",
                })
            );
    });

    // 💾 GUARDAR / ACTUALIZAR
    saveBtn.addEventListener("click", (e) => {
        e.preventDefault();
        const formData = new FormData(form);

        const url = editingMembershipId
            ? apiUrl(`admin/membresias/json/${editingMembershipId}`)
            : apiUrl(`admin/membresias/json`);

        if (editingMembershipId) formData.append("_method", "PUT");

        fetch(url, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
                Accept: "application/json",
            },
            body: formData,
        })
            .then((res) => {
                if (!res.ok)
                    return res.json().then((err) => Promise.reject(err));
                return res.json();
            })
            .then((data) => {
                if (data.success) {
                    Swal.fire({
                        icon: "success",
                        title: editingMembershipId
                            ? "Membresía actualizada correctamente"
                            : "Membresía registrada correctamente",
                        background: "#1a1a1a",
                        color: "#fff",
                        confirmButtonColor: "#fcd52B",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    const modal = bootstrap.Modal.getInstance(
                        document.getElementById("membershipModal")
                    );
                    modal.hide();
                    form.reset();
                    editingMembershipId = null;
                    loadMembresias();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data.message || "No se pudo guardar la membresía",
                        background: "#1a1a1a",
                        color: "#fff",
                        confirmButtonColor: "#fcd52B",
                    });
                }
            })
            .catch((err) => {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: err.message || "Ocurrió un problema con la solicitud",
                    background: "#1a1a1a",
                    color: "#fff",
                    confirmButtonColor: "#fcd52B",
                });
            });
    });

    // 🗑️ ELIMINAR MEMBRESÍA
    tableBody.addEventListener("click", (e) => {
        const btn = e.target.closest(".deleteMembership");
        if (!btn) return;

        const id = btn.dataset.id;

        Swal.fire({
            title: "¿Estás seguro?",
            text: "Esta acción eliminará la membresía permanentemente",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#fcd52B",
            cancelButtonColor: "#1a1a1a",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
            background: "#1a1a1a",
            color: "#fff",
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append("_method", "DELETE");

                fetch(apiUrl(`admin/membresias/json/${id}`), {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                        Accept: "application/json",
                    },
                    body: formData,
                })
                    .then((res) => {
                        if (!res.ok)
                            return res
                                .json()
                                .then((err) => Promise.reject(err));
                        return res.json();
                    })
                    .then((data) => {
                        if (data.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Membresía eliminada correctamente",
                                background: "#1a1a1a",
                                color: "#fff",
                                confirmButtonColor: "#fcd52B",
                                timer: 1500,
                                showConfirmButton: false,
                            });
                            loadMembresias();
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text:
                                    data.message ||
                                    "No se pudo eliminar la membresía",
                                background: "#1a1a1a",
                                color: "#fff",
                                confirmButtonColor: "#fcd52B",
                            });
                        }
                    })
                    .catch((error) => {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text:
                                error.message ||
                                "Ocurrió un problema con la solicitud",
                            background: "#1a1a1a",
                            color: "#fff",
                            confirmButtonColor: "#fcd52B",
                        });
                    });
            }
        });
    });
});
