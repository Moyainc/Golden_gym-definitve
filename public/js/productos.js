document.addEventListener("DOMContentLoaded", () => {
    const categoryTable = document.getElementById("categoryTable");
    const newCategoryForm = document.getElementById("newCategoryForm");
    const saveCategoryBtn = document.getElementById("saveCategoryBtn");
    const deleteCategoryModalEl = document.getElementById(
        "deleteCategoryModal"
    );
    const deleteCategoryModal = new bootstrap.Modal(deleteCategoryModalEl);
    const newCategoryModalEl = document.getElementById("newCategoryModal");
    const newCategoryModal = new bootstrap.Modal(newCategoryModalEl);
    let editingCategoryId = null; // null = crear, si tiene valor = editar
    let categoryIdToDelete = null;

    newCategoryModalEl.addEventListener("show.bs.modal", (event) => {
        const triggerButton = event.relatedTarget; // botón que abrió el modal
        if (!triggerButton.classList.contains("edit-category-btn")) {
            // Si NO es un botón de editar, es "crear"
            editingCategoryId = null;

            // Limpiar formulario
            newCategoryForm.reset();

            // Cambiar título y botón a "Guardar"
            const modalTitle = newCategoryModalEl.querySelector(".modal-title");
            modalTitle.textContent = "Nueva Categoría";

            saveCategoryBtn.innerHTML = `<i data-feather="save" class="me-1"></i>Guardar`;

            if (typeof feather !== "undefined") feather.replace();
        }
    });

    // ---------------------------
    // Función para renderizar la tabla
    // ---------------------------
    const renderCategories = (categorias) => {
        categoryTable.innerHTML = "";
        categorias.forEach((cat) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${cat.nombre}</td>
                <td>${cat.descripcion ?? "—"}</td>
                <td>
                    <button
                        class="btn btn-sm btn-outline-light me-1 edit-category-btn"
                        data-id="${cat.id}"
                        data-nombre="${cat.nombre}"
                        data-descripcion="${cat.descripcion ?? ""}"
                        data-bs-toggle="modal"
                        data-bs-target="#newCategoryModal"
                    >
                        <i data-feather="edit"></i>
                    </button>
                    <button
                        class="btn btn-sm btn-outline-danger delete-category-btn"
                        data-id="${cat.id}"
                        data-nombre="${cat.nombre}"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteCategoryModal"
                    >
                        <i data-feather="trash-2"></i>
                    </button>
                </td>
            `;
            categoryTable.appendChild(row);
        });

        if (typeof feather !== "undefined") feather.replace();
        attachEditEvents();
        attachDeleteEvents();
    };

    // ---------------------------
    // Cargar categorías desde backend
    // ---------------------------
    const loadCategories = async () => {
        try {
            const response = await fetch("/admin/categorias/list");
            const data = await response.json();
            renderCategories(data);
        } catch (error) {
            console.error("Error cargando categorías:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudieron cargar las categorías.",
            });
        }
    };

    // ---------------------------
    // Editar categoría
    // ---------------------------
    const attachEditEvents = () => {
        document.querySelectorAll(".edit-category-btn").forEach((btn) => {
            btn.addEventListener("click", () => {
                editingCategoryId = btn.dataset.id;
                const name = btn.dataset.nombre;
                const description = btn.dataset.descripcion;

                document.getElementById("categoryName").value = name;
                document.getElementById("categoryDescription").value =
                    description;

                const modalTitle = document.querySelector(
                    "#newCategoryModal .modal-title"
                );
                modalTitle.textContent = `Editar Categoría`;

                saveCategoryBtn.innerHTML = `<i data-feather="edit" class="me-1"></i>Actualizar`;

                if (typeof feather !== "undefined") feather.replace();
            });
        });
    };

    // ---------------------------
    // Crear o actualizar categoría
    // ---------------------------
    saveCategoryBtn.addEventListener("click", async (e) => {
        e.preventDefault();

        const formData = new FormData(newCategoryForm);
        formData.set(
            "nombre",
            document.getElementById("categoryName").value.trim()
        );
        formData.set(
            "descripcion",
            document.getElementById("categoryDescription").value.trim()
        );

        try {
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

            let url = "/admin/categorias";
            let method = "POST";

            if (editingCategoryId) {
                url = `/admin/categorias/${editingCategoryId}`;
                method = "POST"; // Laravel usa POST + _method
                formData.append("_method", "PUT");
            }

            const response = await fetch(url, {
                method: method,
                headers: { "X-CSRF-TOKEN": csrfToken },
                body: formData,
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok) {
                Swal.fire({
                    icon: "success",
                    title: "¡Éxito!",
                    text: editingCategoryId
                        ? data.message || "Categoría actualizada exitosamente."
                        : data.message || "Categoría creada exitosamente.",
                    confirmButtonColor: "#ffc107",
                });

                newCategoryForm.reset();
                const modal = bootstrap.Modal.getInstance(
                    document.getElementById("newCategoryModal")
                );
                modal.hide();

                // Reset modal a modo "crear"
                editingCategoryId = null;
                const modalTitle = document.querySelector(
                    "#newCategoryModal .modal-title"
                );
                modalTitle.textContent = "Nueva Categoría";
                saveCategoryBtn.innerHTML = `<i data-feather="save" class="me-1"></i>Guardar`;

                await loadCategories();
            } else if (response.status === 422) {
                const firstError =
                    data.errors?.nombre?.[0] ||
                    data.message ||
                    "Error de validación.";
                Swal.fire({
                    icon: "warning",
                    title: "Validación",
                    text: firstError,
                    confirmButtonColor: "#ffc107",
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "No se pudo guardar la categoría.",
                });
            }
        } catch (error) {
            console.error("Error al guardar la categoría:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Hubo un problema al conectar con el servidor.",
            });
        }
    });

    // ---------------------------
    // Eliminar categoría
    // ---------------------------
    const attachDeleteEvents = () => {
        document.querySelectorAll(".delete-category-btn").forEach((btn) => {
            btn.addEventListener("click", () => {
                categoryIdToDelete = btn.dataset.id;
                const categoryName = btn.dataset.nombre;

                const modalBody = document.querySelector(
                    "#deleteCategoryModal .modal-body"
                );
                modalBody.textContent = `¿Deseas eliminar la categoría "${categoryName}"?`;
            });
        });

        document
            .querySelector("#deleteCategoryModal .btn-danger")
            .addEventListener("click", async () => {
                if (!categoryIdToDelete) return;

                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content");

                try {
                    const response = await fetch(
                        `/admin/categorias/${categoryIdToDelete}`,
                        {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": csrfToken,
                                "X-HTTP-Method-Override": "DELETE",
                            },
                        }
                    );

                    const data = await response.json().catch(() => ({}));

                    if (response.ok) {
                        Swal.fire({
                            icon: "success",
                            title: "Eliminado",
                            text: data.message,
                            confirmButtonColor: "#ffc107",
                        });

                        categoryIdToDelete = null;
                        deleteCategoryModal.hide();
                        await loadCategories();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text:
                                data.message ||
                                "No se pudo eliminar la categoría.",
                        });
                    }
                } catch (error) {
                    console.error(error);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Error al conectar con el servidor.",
                    });
                }
            });
    };

    // ---------------------------
    // Inicializar
    // ---------------------------
    loadCategories();
});
