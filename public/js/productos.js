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



// --------------------- Productossss ====================


document.addEventListener("DOMContentLoaded", () => {
    const productTable = document.getElementById("productTable");
    const productForm = document.getElementById("productForm");
    const productModalEl = document.getElementById("productModal");
    const productModal = new bootstrap.Modal(productModalEl);
    const saveProductBtn = document.getElementById("guardarProducto");
    const categoriaSelect = document.getElementById("categoriaProducto");
    const totalProductsEl = document.getElementById("totalProducts");
    const totalStockEl = document.getElementById("totalStock");

    let editingProductId = null;
    let productIdToDelete = null;
    let allProducts = [];

    // -----------------------------
    // Cargar categorías dinámicamente
    // -----------------------------
    const loadCategorias = async () => {
        try {
            const res = await fetch("/admin/categorias/list");
            const categorias = await res.json();
            categoriaSelect.innerHTML = ""; // limpiar opciones
            categorias.forEach((cat) => {
                const option = document.createElement("option");
                option.value = cat.id;
                option.textContent = cat.nombre;
                categoriaSelect.appendChild(option);

                const filterOption = document.createElement("option");
                filterOption.value = cat.id;
                filterOption.textContent = cat.nombre;
                filterCategory.appendChild(filterOption);
            });
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "No se pudieron cargar las categorías", "error");
        }
    };

    // -----------------------------
    // Render tabla
    // -----------------------------
    const renderProducts = (productos) => {
        productTable.innerHTML = "";
        let totalStock = 0;
        productos.forEach((prod) => {
          totalStock += Number(prod.stock);
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${prod.nombre}</td>
                <td>${prod.categoria.nombre}</td>
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
        attachProductEvents();
    };

    // -----------------------------
    // Cargar productos
    // -----------------------------
    const loadProducts = async () => {
        await loadCategorias(); // asegurar que las categorías estén cargadas antes de abrir modal
        try {
            const res = await fetch("/admin/productos/list");
            const data = await res.json();
            allProducts = data;
            renderProducts(allProducts);
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "No se pudieron cargar los productos", "error");
        }
    };

    // --------------- FILTROOOOOOOO ================

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

            const matchSearch = prod.nombre.toLowerCase().includes(search);

            return matchCategory && matchStatus && matchSearch;
        });

        renderProducts(filtered);
    };

    filterCategory.addEventListener("change", filterProducts);
    filterStatus.addEventListener("change", filterProducts);
    searchProduct.addEventListener("input", filterProducts);

    // -----------------------------
    // Abrir modal para editar/crear
    // -----------------------------
    productModalEl.addEventListener("show.bs.modal", (event) => {
        const button = event.relatedTarget;
        const title = productModalEl.querySelector(".modal-title");

        if (button.dataset.mode === "create") {
            editingProductId = null;
            productForm.reset();
            title.innerHTML = `<i data-feather="package"></i> Nuevo Producto`;
            saveProductBtn.textContent = "Guardar";
            // Seleccionar la primera categoría disponible
            if (categoriaSelect.options.length > 0) {
                categoriaSelect.value = categoriaSelect.options[0].value;
            }
        } else {
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
    // Guardar o actualizar producto
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
                ? `/admin/productos/${editingProductId}`
                : "/admin/productos";
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
                });
                productForm.reset();
                productModal.hide();
                await loadProducts();
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
    // Eventos editar y eliminar
    // -----------------------------
    const attachProductEvents = () => {
        document.querySelectorAll(".delete-product-btn").forEach((btn) => {
            btn.addEventListener("click", () => {
                productIdToDelete = btn.dataset.id;
            });
        });

        document
            .querySelector("#deleteProductModal .btn-danger")
            .addEventListener("click", async () => {
                if (!productIdToDelete) return;
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content");
                try {
                    const res = await fetch(
                        `/admin/productos/${productIdToDelete}`,
                        {
                            method: "DELETE",
                            headers: { "X-CSRF-TOKEN": csrfToken },
                        }
                    );
                    const data = await res.json();
                    if (res.ok) {
                        Swal.fire("Eliminado", data.message, "success");
                        productIdToDelete = null;
                        const modalEl =
                            document.getElementById("deleteProductModal");
                        bootstrap.Modal.getInstance(modalEl).hide();
                        await loadProducts();
                    } else {
                        Swal.fire("Error", data.message, "error");
                    }
                } catch (err) {
                    console.error(err);
                    Swal.fire(
                        "Error",
                        "Error al conectar con el servidor",
                        "error"
                    );
                }
            });
    };

    loadProducts();
});


