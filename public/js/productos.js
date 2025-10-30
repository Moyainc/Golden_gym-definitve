document.addEventListener("DOMContentLoaded", () => {
    initializePage();
});

const initializePage = () => {
    // -----------------------------
    // Utilidades y API
    // -----------------------------
    const apiUrl = (path) => {
        const base = window.app?.baseUrl || window.location.origin;
        return `${base}/${path.replace(/^\/+/, "")}`;
    };

    // -----------------------------
    // Variables globales
    // -----------------------------
    let editingProductId = null;
    let productIdToDelete = null;
    let allProducts = [];

    let editingCategoryId = null;
    let categoryIdToDelete = null;

    // -----------------------------
    // Modales
    // -----------------------------
    const productModalEl = document.getElementById("productModal");
    let productModal = bootstrap.Modal.getInstance(productModalEl);
    if (!productModal) productModal = new bootstrap.Modal(productModalEl);

    const newCategoryModalEl = document.getElementById("newCategoryModal");
    const newCategoryModal = new bootstrap.Modal(newCategoryModalEl);

    const deleteCategoryModalEl = document.getElementById(
        "deleteCategoryModal"
    );
    const deleteCategoryModal = new bootstrap.Modal(deleteCategoryModalEl);

    // -----------------------------
    // Formularios
    // -----------------------------
    const newCategoryForm = document.getElementById("newCategoryForm");
    const saveCategoryBtn = document.getElementById("saveCategoryBtn");
    const productForm = document.getElementById("productForm");
    const saveProductBtn = document.getElementById("guardarProducto");

    // -----------------------------
    // LIMPIAR MODALES AL ABRIR NUEVO
    // -----------------------------
    // Modal Categoría
    newCategoryModalEl.addEventListener("show.bs.modal", (event) => {
        const button = event.relatedTarget;
        const modalTitle = newCategoryModalEl.querySelector(".modal-title");

        if (button && button.dataset.mode === "edit") {
            // 👉 Modo editar
            editingCategoryId = button.dataset.id;
            document.getElementById("categoryName").value =
                button.dataset.nombre;
            document.getElementById("categoryDescription").value =
                button.dataset.descripcion;
            modalTitle.textContent = "Editar Categoría";
            saveCategoryBtn.innerHTML =
                '<i data-feather="edit" class="me-1"></i>Actualizar';
        } else {
            // 👉 Modo crear (nuevo)
            editingCategoryId = null;
            newCategoryForm.reset();
            modalTitle.textContent = "Nueva Categoría";
            saveCategoryBtn.innerHTML =
                '<i data-feather="plus" class="me-1"></i>Guardar';
        }

        if (typeof feather !== "undefined") feather.replace();
    });

    // ✅ Cuando se cierra el modal (en cualquier caso)
    newCategoryModalEl.addEventListener("hidden.bs.modal", () => {
        editingCategoryId = null; // Reinicia el estado
        newCategoryForm.reset();
        const modalTitle = newCategoryModalEl.querySelector(".modal-title");
        modalTitle.textContent = "Nueva Categoría";
        saveCategoryBtn.innerHTML =
            '<i data-feather="plus" class="me-1"></i>Guardar';
    });

    // Modal Producto
    productModalEl.addEventListener("show.bs.modal", async (event) => {
        await loadCategoriasForProducts();
        const button = event.relatedTarget;
        const title = productModalEl.querySelector(".modal-title");

        if (button && button.dataset.mode === "create") {
            editingProductId = null;
            productForm.reset();
            title.innerHTML = `<i data-feather="package"></i> Nuevo Producto`;
            saveProductBtn.textContent = "Guardar";
            document.getElementById("categoriaProducto").value =
                document.getElementById("categoriaProducto").options[0]
                    ?.value || "";
        } else if (button && button.dataset.mode === "edit") {
            editingProductId = button.dataset.id;
            title.innerHTML = `<i data-feather="package"></i> Editar Producto`;
            saveProductBtn.textContent = "Actualizar";
            document.getElementById("nombreProducto").value =
                button.dataset.nombre;
            document.getElementById("categoriaProducto").value =
                button.dataset.categoria;
            document.getElementById("stockProducto").value =
                button.dataset.stock;
            document.getElementById("precioProducto").value =
                button.dataset.precio;
            document.getElementById("estadoProducto").value =
                button.dataset.estado;
        }

        if (typeof feather !== "undefined") feather.replace();
    });

    // -----------------------------
    // CATEGORÍAS
    // -----------------------------
    const categoryTable = document.getElementById("categoryTable");

    const renderCategories = (categorias) => {
        categoryTable.innerHTML = "";
        categorias.forEach((cat) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${cat.nombre}</td>
                <td>${cat.descripcion ?? "—"}</td>
                <td>
                    <button class="btn btn-sm btn-outline-light me-1 edit-category-btn"
                        data-id="${cat.id}"
                        data-nombre="${cat.nombre}"
                        data-descripcion="${cat.descripcion ?? ""}"
                        data-bs-toggle="modal"
                        data-bs-target="#newCategoryModal"
                        data-mode="edit">
                        <i data-feather="edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-category-btn"
                        data-id="${cat.id}"
                        data-nombre="${cat.nombre}"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteCategoryModal">
                        <i data-feather="trash-2"></i>
                    </button>
                </td>`;
            categoryTable.appendChild(row);
        });
        if (typeof feather !== "undefined") feather.replace();
        attachCategoryEvents();
    };

    const loadCategories = async () => {
        try {
            const res = await fetch(apiUrl("/admin/categorias/list"));
            const data = await res.json();
            renderCategories(data);
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "No se pudieron cargar las categorías", "error");
        }
    };

    const attachCategoryEvents = () => {
        document.querySelectorAll(".edit-category-btn").forEach((btn) => {
            btn.addEventListener("click", () => {
                editingCategoryId = btn.dataset.id;
                document.getElementById("categoryName").value =
                    btn.dataset.nombre;
                document.getElementById("categoryDescription").value =
                    btn.dataset.descripcion;
                const modalTitle = document.querySelector(
                    "#newCategoryModal .modal-title"
                );
                modalTitle.textContent = "Editar Categoría";
                saveCategoryBtn.innerHTML = `<i data-feather="edit" class="me-1"></i>Actualizar`;
                if (typeof feather !== "undefined") feather.replace();
            });
        });

        document.querySelectorAll(".delete-category-btn").forEach((btn) => {
            btn.addEventListener("click", () => {
                categoryIdToDelete = btn.dataset.id;
                const modalBody = document.querySelector(
                    "#deleteCategoryModal .modal-body"
                );
                modalBody.textContent = `¿Deseas eliminar la categoría "${btn.dataset.nombre}"?`;

                // Escuchamos el botón de confirmar eliminación dentro del modal
                const confirmDeleteBtn = document.querySelector(
                    "#deleteCategoryModal .btn-danger"
                );

                // Remover listeners previos para evitar duplicados
                confirmDeleteBtn.replaceWith(confirmDeleteBtn.cloneNode(true));

                document
                    .querySelector("#deleteCategoryModal .btn-danger")
                    .addEventListener("click", async () => {
                        if (!categoryIdToDelete) return;

                        const csrfToken = document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content");

                        try {
                            const res = await fetch(
                                apiUrl(
                                    `/admin/categorias/${categoryIdToDelete}`
                                ),
                                {
                                    method: "DELETE",
                                    headers: { "X-CSRF-TOKEN": csrfToken },
                                }
                            );

                            const data = await res.json();

                            if (res.ok) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Eliminado",
                                    text:
                                        data.message ||
                                        "Categoría eliminada con éxito",
                                    confirmButtonColor: "#ffc107",
                                }).then(() => {
                                    // ✅ Cierra el modal nativamente
                                    document
                                        .querySelector(
                                            '#deleteCategoryModal [data-bs-dismiss="modal"]'
                                        )
                                        .click();

                                    // ✅ Recargar tabla
                                    loadCategories();
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    text:
                                        data.message ||
                                        "No se pudo eliminar la categoría.",
                                });
                            }
                        } catch (err) {
                            console.error(err);
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Error al conectar con el servidor.",
                            });
                        }
                    });
            });
        });
    };

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
            let url = apiUrl("/admin/categorias");
            let method = "POST";

            if (editingCategoryId) {
                url = apiUrl(`/admin/categorias/${editingCategoryId}`);
                method = "POST";
                formData.append("_method", "PUT");
            }

            const res = await fetch(url, {
                method,
                headers: { "X-CSRF-TOKEN": csrfToken },
                body: formData,
            });

            const data = await res.json().catch(() => ({}));

            if (res.ok) {
                Swal.fire({
                    icon: "success",
                    title: "¡Éxito!",
                    text: data.message || "Operación exitosa",
                    confirmButtonColor: "#ffc107",
                }).then(() => {
                    // ✅ Cierra el modal con click nativo
                    document
                        .querySelector(
                            '#newCategoryModal [data-bs-dismiss="modal"]'
                        )
                        .click();
                    loadCategories();
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "No se pudo guardar la categoría",
                });
            }
        } catch (err) {
            console.error(err);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Error al conectar con el servidor.",
            });
        }
    });

    newCategoryModalEl.addEventListener("show.bs.modal", () => {
        // Si no se está editando, limpia todo
        if (!editingCategoryId) {
            newCategoryForm.reset();
            document.getElementById("categoryModalLabel").textContent =
                "Nueva Categoría";
            saveCategoryBtn.textContent = "Guardar";
        }
    });

    // Cuando se cierra el modal (en cualquier caso)
    newCategoryModalEl.addEventListener("hidden.bs.modal", () => {
        editingCategoryId = null; // 🔥 Reinicia el estado de edición
        newCategoryForm.reset(); // Limpia el formulario
        document.getElementById("categoryModalLabel").textContent =
            "Nueva Categoría";
        saveCategoryBtn.textContent = "Guardar";
    });

    // -----------------------------
    // PRODUCTOS
    // -----------------------------
    const productTable = document.getElementById("productTable");
    const categoriaSelect = document.getElementById("categoriaProducto");
    const totalProductsEl = document.getElementById("totalProducts");
    const totalStockEl = document.getElementById("totalStock");
    const filterCategory = document.getElementById("filterCategory");
    const filterStatus = document.getElementById("filterStatus");
    const searchProduct = document.getElementById("searchProduct");

    const loadCategoriasForProducts = async () => {
        try {
            const res = await fetch(apiUrl("/admin/categorias/list"));
            const categorias = await res.json();
            categoriaSelect.innerHTML = "";
            filterCategory.innerHTML = '<option value="">Todas</option>';
            categorias.forEach((cat) => {
                categoriaSelect.innerHTML += `<option value="${cat.id}">${cat.nombre}</option>`;
                filterCategory.innerHTML += `<option value="${cat.id}">${cat.nombre}</option>`;
            });
        } catch (err) {
            console.error(err);
        }
    };

    const renderProducts = (productos) => {
        productTable.innerHTML = "";
        let totalStock = 0;

        productos.forEach((prod) => {
            totalStock += Number(prod.stock);
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${prod.nombre}</td>
                <td>${prod.categoria?.nombre || "Sin categoría"}</td>
                <td>${prod.stock}</td>
                <td>$${prod.precio}</td>
                <td>${prod.estado}</td>
                <td>
                    <button class="btn btn-sm btn-outline-light me-1 edit-product-btn"
                        data-id="${prod.id}"
                        data-nombre="${prod.nombre}"
                        data-categoria="${prod.categoria_id}"
                        data-stock="${prod.stock}"
                        data-precio="${prod.precio}"
                        data-estado="${prod.estado}"
                        data-bs-toggle="modal"
                        data-bs-target="#productModal"
                        data-mode="edit">
                        <i data-feather="edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-product-btn"
                        data-id="${prod.id}"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteProductModal">
                        <i data-feather="trash-2"></i>
                    </button>
                </td>`;
            productTable.appendChild(row);
        });

        totalProductsEl.textContent = productos.length;
        totalStockEl.textContent = totalStock;
        if (typeof feather !== "undefined") feather.replace();
    };

    const loadProducts = async () => {
        await loadCategoriasForProducts();
        try {
            const res = await fetch(apiUrl("/admin/productos/list"));
            const data = await res.json();
            allProducts = data;
            renderProducts(allProducts);
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "No se pudieron cargar los productos", "error");
        }
    };

    // -----------------------------
    // FILTROS Y GUARDADO
    // -----------------------------
    const filterProducts = () => {
        const category = filterCategory.value;
        const status = filterStatus.value.toLowerCase();
        const search = searchProduct.value.toLowerCase();

        const filtered = allProducts.filter((prod) => {
            const matchCategory = !category || prod.categoria_id == category;
            let matchStatus = true;
            if (status) {
                if (status === "bajo stock")
                    matchStatus = prod.stock > 0 && prod.stock <= 5;
                else if (status === "agotado") matchStatus = prod.stock == 0;
                else matchStatus = prod.estado.toLowerCase() === status;
            }
            return (
                matchCategory &&
                matchStatus &&
                prod.nombre.toLowerCase().includes(search)
            );
        });

        renderProducts(filtered);
    };

    filterCategory.addEventListener("change", filterProducts);
    filterStatus.addEventListener("change", filterProducts);
    searchProduct.addEventListener("input", filterProducts);

    // -----------------------------
    // GUARDAR PRODUCTO
    // -----------------------------
    saveProductBtn.addEventListener("click", async (e) => {
        e.preventDefault();
        const formData = {
            nombre: document.getElementById("nombreProducto").value.trim(),
            categoria_id: document.getElementById("categoriaProducto").value,
            stock: document.getElementById("stockProducto").value,
            precio: document.getElementById("precioProducto").value,
            estado: document.getElementById("estadoProducto").value,
        };
        try {
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");
            const url = editingProductId
                ? apiUrl(`/admin/productos/${editingProductId}`)
                : apiUrl("/admin/productos");
            const method = editingProductId ? "PUT" : "POST";

            const res = await fetch(url, {
                method,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(formData),
            });

            const data = await res.json();

            if (res.ok) {
                Swal.fire({
                    icon: "success",
                    title: editingProductId ? "Actualizado" : "Creado",
                    text: data.message,
                    confirmButtonColor: "#ffc107",
                }).then(() => {
                    document
                        .querySelector(
                            '#productModal [data-bs-dismiss="modal"]'
                        )
                        .click();
                    loadProducts();
                });
            } else {
                Swal.fire(
                    "Error",
                    data.message || "No se pudo guardar el producto",
                    "error"
                );
            }
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "Error al conectar con el servidor", "error");
        }
    });

    // -----------------------------
    // ELIMINAR PRODUCTOS
    // -----------------------------
    document.body.addEventListener("click", async (e) => {
        if (e.target.closest(".delete-product-btn")) {
            const btn = e.target.closest(".delete-product-btn");
            productIdToDelete = btn.dataset.id;
        }

        if (e.target.matches("#deleteProductModal .btn-danger")) {
            if (!productIdToDelete) return;

            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

            try {
                const res = await fetch(
                    apiUrl(`/admin/productos/${productIdToDelete}`),
                    {
                        method: "DELETE",
                        headers: { "X-CSRF-TOKEN": csrfToken },
                    }
                );

                const data = await res.json();

                if (res.ok) {
                    Swal.fire({
                        icon: "success",
                        title: "Eliminado",
                        text: data.message || "Producto eliminado con éxito",
                        confirmButtonColor: "#ffc107",
                    }).then(() => {
                        // ✅ Cierra el modal correcto (deleteProductModal)
                        document
                            .querySelector(
                                '#deleteProductModal [data-bs-dismiss="modal"]'
                            )
                            .click();

                        // ✅ Recarga productos
                        loadProducts();
                    });
                } else {
                    Swal.fire(
                        "Error",
                        data.message || "No se pudo eliminar",
                        "error"
                    );
                }
            } catch (err) {
                console.error(err);
                Swal.fire(
                    "Error",
                    "Error al conectar con el servidor",
                    "error"
                );
            }
        }
    });

    // -----------------------------
    // Inicializar
    // -----------------------------
    loadCategories();
    loadProducts();
};
