// CATEGORIASSSS

//guardar Categorias
document.addEventListener("DOMContentLoaded", () => {
    document
        .getElementById("saveCategoryBtn")
        .addEventListener("click", async (e) => {
            e.preventDefault();

            const form = document.getElementById("newCategoryForm");
            const formData = new FormData(form);

            try {
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content");

                const response = await fetch("/admin/categorias", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok) {
                    Swal.fire({
                        icon: "success",
                        title: "¡Éxito!",
                        text: data.message,
                        confirmButtonColor: "#ffc107",
                    });
                    form.reset();
                    // Aquí puedes refrescar la lista de categorías si tienes una tabla
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data.message || "No se pudo crear la categoría.",
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Hubo un problema al conectar con el servidor.",
                });
            }
        });

    //Editar Categoria

    document
        .getElementById("updateCategoryBtn")
        ?.addEventListener("click", async (e) => {
            e.preventDefault();

            const id = document.getElementById("editCategoryId").value;
            const form = document.getElementById("editCategoryForm");
            const formData = new FormData(form);

            try {
                const response = await fetch(`/admin/categorias/${id}`, {
                    method: "POST", // Laravel no acepta PUT con FormData directo
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "X-HTTP-Method-Override": "PUT",
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok) {
                    Swal.fire({
                        icon: "success",
                        title: "¡Actualizado!",
                        text: data.message,
                        confirmButtonColor: "#ffc107",
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text:
                            data.message ||
                            "No se pudo actualizar la categoría.",
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Error de conexión con el servidor.",
                });
            }

            //Eliminar Categoria
            btn.addEventListener("click", async () => {
                const id = btn.dataset.id;

                const confirm = await Swal.fire({
                    title: "¿Eliminar categoría?",
                    text: "Esta acción no se puede deshacer.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, eliminar",
                    cancelButtonText: "Cancelar",
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#6c757d",
                });

                if (confirm.isConfirmed) {
                    try {
                        const response = await fetch(
                            `/admin/categorias/${id}`,
                            {
                                method: "POST",
                                headers: {
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                    "X-HTTP-Method-Override": "DELETE",
                                },
                            }
                        );

                        const data = await response.json();

                        if (response.ok) {
                            Swal.fire({
                                icon: "success",
                                title: "Eliminado",
                                text: data.message,
                                confirmButtonColor: "#ffc107",
                            });
                            // Aquí podrías remover la fila de la tabla sin recargar
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
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Error al conectar con el servidor.",
                        });
                    }
                }
            });

            //Enviar lista de categorias

            document.addEventListener("DOMContentLoaded", () => {
                const categoryTable = document.getElementById("categoryTable");
                const saveCategoryBtn =
                    document.getElementById("saveCategoryBtn");
                const form = document.getElementById("newCategoryForm");

                // 🔹 Función para renderizar las categorías en la tabla
                const renderCategories = (categorias) => {
                    categoryTable.innerHTML = ""; // limpiar tabla

                    categorias.forEach((cat) => {
                        const row = document.createElement("tr");
                        row.innerHTML = `
                <td>#C${String(cat.id).padStart(3, "0")}</td>
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
                    >
                        <i data-feather="trash-2"></i>
                    </button>
                </td>
            `;
                        categoryTable.appendChild(row);
                    });

                    // Volver a dibujar los íconos de Feather
                    if (typeof feather !== "undefined") {
                        feather.replace();
                    }

                    // Activar eventos para los botones eliminar (cuando los tengas implementados)
                    // attachDeleteEvents();
                };

                // 🔹 Cargar categorías desde el backend
                const loadCategories = async () => {
                    try {
                        const response = await fetch("/admin/categorias/list");
                        const data = await response.json();
                        renderCategories(data);
                    } catch (error) {
                        console.error("Error cargando categorías:", error);
                    }
                };

                // 🔹 Guardar nueva categoría
                saveCategoryBtn.addEventListener("click", async (e) => {
                    e.preventDefault();

                    const formData = new FormData(form);

                    // Agregar manualmente los campos del formulario si no tienen 'name'
                    const nombre = document
                        .getElementById("categoryName")
                        .value.trim();
                    const descripcion = document
                        .getElementById("categoryDescription")
                        .value.trim();
                    formData.set("nombre", nombre);
                    formData.set("descripcion", descripcion);

                    try {
                        const csrfToken = document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content");

                        const response = await fetch("/admin/categorias", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": csrfToken,
                            },
                            body: formData,
                        });

                        const data = await response.json();

                        if (response.ok) {
                            Swal.fire({
                                icon: "success",
                                title: "¡Éxito!",
                                text: data.message,
                                confirmButtonColor: "#ffc107",
                            });

                            // 🔥 Recargar categorías
                            await loadCategories();

                            // 🔥 Cerrar el modal
                            const modal = bootstrap.Modal.getInstance(
                                document.getElementById("newCategoryModal")
                            );
                            modal.hide();

                            form.reset();
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text:
                                    data.message ||
                                    "No se pudo crear la categoría.",
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

                // 🔹 Ejecutar carga inicial
                loadCategories();
            });

            // Función para eliminar categorías con SweetAlert2
            const attachDeleteEvents = () => {
                document
                    .querySelectorAll(".delete-category-btn")
                    .forEach((btn) => {
                        btn.addEventListener("click", async () => {
                            const id = btn.dataset.id;

                            const confirm = await Swal.fire({
                                title: "¿Eliminar categoría?",
                                text: "Esta acción no se puede deshacer.",
                                icon: "warning",
                                showCancelButton: true,
                                confirmButtonText: "Sí, eliminar",
                                cancelButtonText: "Cancelar",
                                confirmButtonColor: "#d33",
                                cancelButtonColor: "#6c757d",
                            });

                            if (confirm.isConfirmed) {
                                try {
                                    const res = await fetch(
                                        `/admin/categorias/${id}`,
                                        {
                                            method: "POST",
                                            headers: {
                                                "X-CSRF-TOKEN":
                                                    "{{ csrf_token() }}",
                                                "X-HTTP-Method-Override":
                                                    "DELETE",
                                            },
                                        }
                                    );

                                    const data = await res.json();
                                    if (res.ok) {
                                        Swal.fire({
                                            icon: "success",
                                            title: "Eliminado",
                                            text: data.message,
                                            confirmButtonColor: "#ffc107",
                                        });
                                        loadCategories(); // recargar tabla
                                    } else {
                                        Swal.fire({
                                            icon: "error",
                                            title: "Error",
                                            text:
                                                data.message ||
                                                "No se pudo eliminar.",
                                        });
                                    }
                                } catch (error) {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Error",
                                        text: "Error al conectar con el servidor.",
                                    });
                                }
                            }
                        });
                    });
            };

            // Llamar la carga inicial
            await loadCategories();
        });
});
