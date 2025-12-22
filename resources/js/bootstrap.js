document.addEventListener("DOMContentLoaded", () => {
    initializePage();
});

const initializePage = () => {
    // -----------------------------
    // Configuración base SweetAlert2
    // -----------------------------
    const swalConfig = {
        background: "#1a1a1a",
        color: "#fff",
        confirmButtonColor: "#fcd52b",
        confirmButtonText: "Aceptar",
        customClass: {
            confirmButton: "text-black font-bold",
        },
    };

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
    newCategoryModalEl.addEventListener("show.bs.modal", (event) => {
        const button = event.relatedTarget;
        const modalTitle = newCategoryModalEl.querySelector(".modal-title");

        if (button && button.dataset.mode === "edit") {
            editingCategoryId = button.dataset.id;
            document.getElementById("categoryName").value =
                button.dataset.nombre;
            document.getElementById("categoryDescription").value =
                button.dataset.descripcion;
            modalTitle.textContent = "Editar Categoría";
            saveCategoryBtn.innerHTML =
                '<i data-feather="edit" class="me-1"></i>Actualizar';
        } else {
            editingCategoryId = null;
            newCategoryForm.reset();
            modalTitle.textContent = "Nueva Categoría";
            saveCategoryBtn.innerHTML =
                '<i data-feather="plus" class="me-1"></i>Guardar';
        }

        if (typeof feather !== "undefined") feather.replace();
    });

    newCategoryModalEl.addEventListener("hidden.bs.modal", () => {
        editingCategoryId = null;
        newCategoryForm.reset();
        const modalTitle = newCategoryModalEl.querySelector(".modal-title");
        modalTitle.textContent = "Nueva Categoría";
        saveCategoryBtn.innerHTML =
            '<i data-feather="plus" class="me-1"></i>Guardar';
    });

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
                        data-nombre="${cat.nombre}">
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
            Swal.fire({
                ...swalConfig,
                icon: "error",
                title: "Error",
                text: "No se pudieron cargar las categorías",
            });
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

        // --------- ELIMINAR CATEGORÍA ---------
        document.querySelectorAll(".delete-category-btn").forEach((btn) => {
            btn.addEventListener("click", async () => {
                categoryIdToDelete = btn.dataset.id;
                const nombre = btn.dataset.nombre;

                const result = await Swal.fire({
                    ...swalConfig,
                    icon: "warning",
                    title: "Eliminar Categoría",
                    text: `¿Deseas eliminar la categoría "${nombre}"?`,
                    showCancelButton: true,
                    cancelButtonText: "Cancelar",
                    confirmButtonText: "Sí, eliminar",
                });

                if (result.isConfirmed && categoryIdToDelete) {
                    const csrfToken = document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content");
                    try {
                        const res = await fetch(
                            apiUrl(`/admin/categorias/${categoryIdToDelete}`),
                            {
                                method: "DELETE",
                                headers: { "X-CSRF-TOKEN": csrfToken },
                            }
                        );
                        const data = await res.json();
                        if (res.ok) {
                            Swal.fire({
                                ...swalConfig,
                                icon: "success",
                                title: "Eliminado",
                                text:
                                    data.message ||
                                    "Categoría eliminada con éxito",
                                timer: 1200,
                                timerProgressBar: true,
                                showConfirmButton: false,
                            });
                            loadCategories();
                        } else {
                            Swal.fire({
                                ...swalConfig,
                                icon: "error",
                                title: "Error",
                                text:
                                    data.message ||
                                    "No se pudo eliminar la categoría",
                            });
                        }
                    } catch (err) {
                        console.error(err);
                        Swal.fire({
                            ...swalConfig,
                            icon: "error",
                            title: "Error",
                            text: "Error al conectar con el servidor.",
                        });
                    }
                }
            });
        });
    };

    // -----------------------------
    // GUARDAR CATEGORÍA
    // -----------------------------
    saveCategoryBtn.addEventListener("click", async (e) => {
        e.preventDefault();

        const nombre = document.getElementById("categoryName").value.trim();
        const descripcion = document
            .getElementById("categoryDescription")
            .value.trim();
        if (!nombre) return;

        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");
        const url = editingCategoryId
            ? apiUrl(`/admin/categorias/${editingCategoryId}`)
            : apiUrl("/admin/categorias");
        const method = editingCategoryId ? "PUT" : "POST";

        try {
            const res = await fetch(url, {
                method,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ nombre, descripcion }),
            });

            const data = await res.json();

            if (res.ok) {
                Swal.fire({
                    ...swalConfig,
                    icon: "success",
                    title: editingCategoryId ? "Actualizado" : "Creado",
                    text: data.message,
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false,
                }).then(() => {
                    // Cerrar modal, resetear form y recargar página
                    newCategoryForm.reset();
                    const modal = document.getElementById("newCategoryModal");
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    bsModal.hide();
                    location.reload();
                });
                loadCategories();
            } else {
                Swal.fire({
                    ...swalConfig,
                    icon: "error",
                    title: "Error",
                    text:
                        data.errors?.nombre?.[0] ||
                        data.message ||
                        "No se pudo guardar la categoría",
                });
            }
        } catch (err) {
            console.error(err);
            Swal.fire({
                ...swalConfig,
                icon: "error",
                title: "Error",
                text: "Error al conectar con el servidor",
            });
        }
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
            <td>
                ${prod.stock}
                ${
                    Number(prod.stock) === 0
                        ? '<span class="badge bg-danger ms-2">Agotado</span>'
                        : Number(prod.stock) <= 10
                        ? '<span class="badge bg-warning text-dark">Bajo</span>'
                        : ""
                }
            </td>

            <td>${prod.precio_formatted}</td>
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
                    data-nombre="${prod.nombre}">
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

    // LazyLoading Pagination
    let currentPage = 1;
    const perPage = 20;
    let loadingProducts = false;
    let lastPage = false;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting && !loadingProducts && !lastPage) {
                    loadProducts(true);
                }
            });
        },
        { root: null, rootMargin: "0px", threshold: 1.0 }
    );

    observer.observe(document.getElementById("scrollSentinel"));

    const loadProducts = async (reset = false) => {
        if (loadingProducts || lastPage) return;
        loadingProducts = true;

        if (reset) {
            currentPage = 1;
            lastPage = false;
            allProducts = [];
            productTable.innerHTML = "";
        }

        try {
            const res = await fetch(
                apiUrl(
                    `/admin/productos/list?page=${currentPage}&per_page=${perPage}`
                )
            );
            const data = await res.json();

            if (data.data.length === 0 || currentPage > data.last_page) {
                lastPage = true;
            } else {
                allProducts = [...allProducts, ...data.data];
                renderProducts(allProducts);
                currentPage++;
            }
        } catch (err) {
            console.error(err);
        } finally {
            loadingProducts = false;
        }
    };

    // -----------------------------
    // ELIMINAR PRODUCTOS
    // -----------------------------
    const attachProductEvents = () => {
        document.querySelectorAll(".delete-product-btn").forEach((btn) => {
            btn.addEventListener("click", async () => {
                productIdToDelete = btn.dataset.id;
                const nombre = btn.dataset.nombre;

                const result = await Swal.fire({
                    ...swalConfig,
                    icon: "warning",
                    title: "Eliminar Producto",
                    text: `¿Deseas eliminar el producto "${nombre}"?`,
                    showCancelButton: true,
                    cancelButtonText: "Cancelar",
                    confirmButtonText: "Sí, eliminar",
                });

                if (result.isConfirmed && productIdToDelete) {
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
                                ...swalConfig,
                                icon: "success",
                                title: "Eliminado",
                                text:
                                    data.message ||
                                    "Producto eliminado con éxito",
                                timer: 1200,
                                timerProgressBar: true,
                                showConfirmButton: false,
                            });
                            loadProducts(true);
                        } else {
                            Swal.fire({
                                ...swalConfig,
                                icon: "error",
                                title: "Error",
                                text:
                                    data.message ||
                                    "No se pudo eliminar el producto",
                            });
                        }
                    } catch (err) {
                        console.error(err);
                        Swal.fire({
                            ...swalConfig,
                            icon: "error",
                            title: "Error",
                            text: "Error al conectar con el servidor.",
                        });
                    }
                }
            });
        });
    };

    // -----------------------------
    // FILTROS
    // -----------------------------
    const filterProducts = () => {
        const category = filterCategory.value;
        const status = filterStatus.value.toLowerCase();
        const search = searchProduct.value.toLowerCase();

        const filtered = allProducts.filter((prod) => {
            const matchCategory = !category || prod.categoria_id == category;
            let matchStatus = true;
            if (status) {
                if (status === "bajo_stock")
                    matchStatus =
                        Number(prod.stock) > 0 && Number(prod.stock) <= 10;
                else if (status === "agotado")
                    matchStatus = Number(prod.stock) === 0;
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
                    ...swalConfig,
                    icon: "success",
                    title: editingProductId ? "Actualizado" : "Creado",
                    text: data.message,
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false,
                });
                document
                    .querySelector('#productModal [data-bs-dismiss="modal"]')
                    .click();
                loadProducts(true);
            } else {
                // -----------------------------
                // Manejo robusto de errores
                // -----------------------------
                let mensaje = data.message || "No se pudo guardar el producto";

                // Validación de nombre duplicado
                if (data.errors?.nombre?.[0]) {
                    if (
                        data.errors.nombre[0].toLowerCase().includes("unique")
                    ) {
                        mensaje =
                            "Ya existe un producto con ese nombre. Por favor, elige otro.";
                    } else {
                        mensaje = data.errors.nombre[0];
                    }
                }

                Swal.fire({
                    ...swalConfig,
                    icon: "error",
                    title: "Error",
                    text: mensaje,
                });
            }
        } catch (err) {
            console.error(err);
            Swal.fire({
                ...swalConfig,
                icon: "error",
                title: "Error",
                text: "Error al conectar con el servidor",
            });
        }
    });

    // -----------------------------
    // Inicializar
    // -----------------------------

    // ==============================
    //  EXPORTAR REPORTES INVENTARIO
    // ==============================

    const form = document.getElementById("reportsInventarioForm");
    const exportPdfBtn = document.getElementById("exportPdfInventarioBtn");
    const exportExcelBtn = document.getElementById("exportExcelInventarioBtn");

    function exportarInventario(tipo) {
        // Validar campos requeridos
        const inicio = document.getElementById(
            "reportStartDateInventario"
        ).value;
        const fin = document.getElementById("reportEndDateInventario").value;

        if (!inicio || !fin) {
            Swal.fire({
                icon: "warning",
                title: "Campos requeridos",
                text: "Selecciona un rango de fechas",
                background: "#1a1a1a",
                color: "#fff",
            });
            return;
        }
        // Validar rango de fechas
        if (new Date(inicio) > new Date(fin)) {
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

        // Construir URL
        let url = "";
        if (tipo === "pdf") {
            url = apiUrl(
                `/admin/resumen-inventario/pdf?inicio=${inicio}&fin=${fin}`
            );
        } else {
            url = apiUrl(
                `/admin/resumen-inventario/excel?inicio=${inicio}&fin=${fin}`
            );
        }

        // Abrir reporte
        window.open(url, "_blank");
    }

    // Eventos de exportación
    exportPdfBtn.addEventListener("click", () => exportarInventario("pdf"));
    exportExcelBtn.addEventListener("click", () => exportarInventario("excel"));
    loadCategories();
    loadProducts(true);
};
