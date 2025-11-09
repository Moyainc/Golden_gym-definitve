<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PagoDiarioController;
use App\Http\Controllers\ResumenMembresiasController;
use App\Http\Controllers\ResumenVentasController;
use App\Http\Controllers\PerfilController;
// =========================
// 🟢 RUTAS PÚBLICAS (guest)
// =========================
use App\Http\Controllers\ResumenGeneralController;




Route::middleware(['guest'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('inicio');
    Route::get('sign-in', [HomeController::class, 'login'])->name('login');
    Route::post('sign-in', [LoginController::class, 'loginUsuario'])->name('usuario.login');
});


//RUTAS PROTEGIDAS (auth)
Route::middleware(['auth'])->group(function () {


    //Dashboard y perfil (acceso general a todos los roles)

    Route::get('admin/profile', [HomeController::class, 'profile'])->name('admin.profile');
    Route::get('/admin/perfil', [PerfilController::class, 'showProfile'])->name('perfil.show');
    Route::post('/admin/perfil/actualizar', [PerfilController::class, 'updateProfile'])->name('perfil.update');
    Route::post('/admin/perfil/cambiar-contrasena', [PerfilController::class, 'changePassword'])->name('perfil.changePassword');
    Route::post('/admin/perfil/eliminar-foto', [PerfilController::class, 'deletePhoto'])->name('perfil.eliminar-foto');


    // _------------------------------------- ADMINISTRADOR ==================================

    Route::middleware(['checkrole:Administrador'])->group(function () {

        Route::get('admin/confirmacion-pagos', [HomeController::class, 'confirmacion_pagos'])->name('admin.confirmacion_pagos');
        Route::get('admin/contabilidad/resumen-ventas', [HomeController::class, 'resumen_ventas'])->name('admin.resumen.ventas');
        Route::get('admin/contabilidad/resumen-general', [HomeController::class, 'resumen_general'])->name('admin.resumen.general');

        // ADMIN → CONTABILIDAD / MEMBRESÍAS
        Route::get('admin/contabilidad/resumen-membresias', [ResumenMembresiasController::class, 'index'])->name('admin.resumen.membresias');
        Route::get('admin/api/resumen-contabilidad', [ResumenMembresiasController::class, 'getResumenContabilidad']);
        Route::get('admin/exportar/contabilidad-pdf', [ResumenMembresiasController::class, 'exportContabilidadPdf']);
        Route::get('admin/exportar/contabilidad-excel', [ResumenMembresiasController::class, 'exportContabilidadExcel']);
        Route::get('/admin/api/anios-disponibles', [ResumenMembresiasController::class, 'getAniosDisponibles']);



        // ADMIN → CLIENTES
        Route::get('admin/clientes', [HomeController::class, 'clientes'])->name('admin.clientes');
        Route::prefix('admin/clientes')->group(function () {
            Route::get('/list/json', [ClienteController::class, 'getClientes']);
            Route::get('/json/{id}', [ClienteController::class, 'show']);
            Route::post('/json', [ClienteController::class, 'store']);
            Route::put('/json/{id}', [ClienteController::class, 'update']);
            Route::delete('/json/{id}', [ClienteController::class, 'destroy']);
            Route::get('/membresias/json', [ClienteController::class, 'getMembresias']);
            Route::post('/json/{id}/renovar', [ClienteController::class, 'renovarMembresia']);
            Route::get('/export/pdf', [ClienteController::class, 'exportPdf'])->name('clientes.export.pdf');
            Route::get('/export/excel', [ClienteController::class, 'exportExcel'])->name('clientes.export.excel');
        });
        // ADMIN → CLIENTES → PAGOS DIARIOS
        Route::prefix('admin/pagos-diarios')->group(function () {
            Route::get('/list/json', [PagoDiarioController::class, 'index']);
            Route::post('/json', [PagoDiarioController::class, 'store']);
            Route::delete('/json/{id}', [PagoDiarioController::class, 'destroy']);
        });
        // 🔸 Usuarios
        Route::get('/admin/soporte', [HomeController::class, 'soporte'])->name('admin.soporte');
        Route::get('/admin/usuarios', [HomeController::class, 'usuarios'])->name('admin.usuarios');
        Route::post('/admin/usuarios', [HomeController::class, 'store'])->name('admin.usuarios.store');
        Route::get('/admin/usuarios/{id}/edit', [HomeController::class, 'editUsuario'])->name('admin.usuarios.edit');
        Route::put('/admin/usuarios/{id}', [HomeController::class, 'updateUsuario'])->name('admin.usuarios.update');
        Route::delete('/admin/usuarios/{id}', [HomeController::class, 'destroyUsuario'])->name('admin.usuarios.destroy');



        //Membresias
        Route::get('/admin/membresias/estadisticas', [MembresiaController::class, 'estadisticas']);
        Route::get('admin/membresias', [HomeController::class, 'membresias'])->name('admin.membresias');
        Route::prefix('admin/membresias')->group(function () {
            Route::get('/list/json', [MembresiaController::class, 'index']);
            Route::get('/json/{id}', [MembresiaController::class, 'edit']);
            Route::post('/json', [MembresiaController::class, 'store']);
            Route::put('/json/{id}', [MembresiaController::class, 'update']);
            Route::delete('/json/{id}', [MembresiaController::class, 'destroy']);
        });


        //contabilidad y pagos
        Route::get('admin/confirmacion-pagos', [HomeController::class, 'confirmacion_pagos'])->name('admin.confirmacion_pagos');
        Route::get('admin/contabilidad/resumen-ventas', [HomeController::class, 'resumen_ventas'])->name('admin.resumen.ventas');
        Route::get('admin/contabilidad/resumen-membresias', [HomeController::class, 'resumen_membresias'])->name('admin.resumen.membresias');
        Route::get('admin/contabilidad/resumen-general', [HomeController::class, 'resumen_general'])->name('admin.resumen.general');
    });




    //------------------------------------- RECEPCIONISTA ==================================

    Route::middleware(['checkrole:Administrador,Recepcionista'])->group(function () {
        // Clientes
        Route::get('admin/clientes', [HomeController::class, 'clientes'])->name('admin.clientes');
        Route::prefix('admin/clientes')->group(function () {
            Route::get('/list/json', [ClienteController::class, 'getClientes']);
            Route::get('/json/{id}', [ClienteController::class, 'show']);
            Route::post('/json', [ClienteController::class, 'store']);
            Route::put('/json/{id}', [ClienteController::class, 'update']);
            Route::delete('/json/{id}', [ClienteController::class, 'destroy']);
            Route::get('/membresias/json', [ClienteController::class, 'getMembresias']);
            Route::post('/json/{id}/renovar', [ClienteController::class, 'renovarMembresia']);
            Route::get('/export/pdf', [ClienteController::class, 'exportPdf'])->name('clientes.export.pdf');
            Route::get('/export/excel', [ClienteController::class, 'exportExcel'])->name('clientes.export.excel');
        });


        //Categorias
        Route::get('/admin/categorias/list', [CategoriaController::class, 'getCategorias'])->name('admin.categorias.list');
        Route::post('/admin/categorias', [CategoriaController::class, 'store'])->name('admin.categorias.store');
        Route::get('/admin/categorias/{id}/edit', [CategoriaController::class, 'edit'])->name('admin.categorias.edit');
        Route::put('/admin/categorias/{id}', [CategoriaController::class, 'update'])->name('admin.categorias.update');
        Route::delete('/admin/categorias/{id}', [CategoriaController::class, 'destroy'])->name('admin.categorias.destroy');


        //Productos
        Route::get('admin/productos', [HomeController::class, 'productos'])->name('admin.productos');
        Route::get('/admin/productos/list', [ProductoController::class, 'list'])->name('admin.productos.list');
        Route::post('/admin/productos', [ProductoController::class, 'store'])->name('admin.productos.store');
        Route::put('/admin/productos/{id}', [ProductoController::class, 'update'])->name('admin.productos.update');
        Route::delete('/admin/productos/{id}', [ProductoController::class, 'destroy'])->name('admin.productos.destroy');


        //Pagos diarios

        Route::prefix('admin/pagos-diarios')->group(function () {
            Route::get('/list/json', [PagoDiarioController::class, 'index']);
            Route::post('/json', [PagoDiarioController::class, 'store']);
            Route::delete('/json/{id}', [PagoDiarioController::class, 'destroy']);
        });


        // Ventas básicas

        Route::get('admin/ventas/vender', [HomeController::class, 'vender'])->name('admin.vender');
        Route::get('admin/ventas/list', [VentaController::class, 'listVentas'])->name('admin.list_ventas');
        Route::get('admin/ventas/lista-ventas', [HomeController::class, 'lista_ventas'])->name('admin.lista_ventas');
        Route::get('/admin/ventas/buscar', [VentaController::class, 'buscarCliente'])->name('ventas.buscarCliente');
        Route::post('/admin/ventas/registrar', [VentaController::class, 'store'])->name('ventas.store');
        Route::get('/admin/ventas/sugerir-clientes', [VentaController::class, 'sugerirClientes'])->name('ventas.sugerirClientes');
        Route::post('/admin/ventas/rapida', [VentaController::class, 'storeFast'])->name('ventas.storeFast');
        Route::get('/admin/ventas/ultimas', [VentaController::class, 'ultimasVentas'])->name('ventas.ultimas');
        Route::get('/admin/ventas/{id}', [VentaController::class, 'showVentaDetalle'])->name('ventas.detalle');
    });

  //                   ----------------------------- CONTADOR =============================

  Route::middleware(['checkrole:Administrador,Contador'])->group(function () {
    // Acceso solo a contabilidad / resumen
    Route::get('admin/contabilidad/resumen-ventas', [ResumenVentasController::class, 'index'])->name('admin.resumen.ventas');
    Route::get('admin/contabilidad/resumen-membresias', [HomeController::class, 'resumen_membresias'])->name('admin.resumen.membresias');
    Route::get('admin/contabilidad/resumen-general', [HomeController::class, 'resumen_general'])->name('admin.resumen.general');
    Route::get('admin/confirmacion-pagos', [HomeController::class, 'confirmacion_pagos'])->name('admin.confirmacion_pagos');
    Route::get('admin/resumen-ventas/data', [ResumenVentasController::class, 'data'])->name('resumen.ventas.data');
    Route::get('admin/resumen-ventas/ultimas', [ResumenVentasController::class, 'ultimasVentas'])->name('resumen.ventas.ultimas');
    Route::get('resumen-ventas/pdf', [ResumenVentasController::class, 'exportVentasPdf'])->name('admin.resumen_ventas.pdf');
    Route::get('resumen-ventas/excel', [ResumenVentasController::class, 'exportVentasExcel'])->name('admin.resumen_ventas.excel');

    Route::get('admin/dashboard', [HomeController::class, 'dashboard'])->name('admin.dashboard');


    // Moya
    // ADMIN → CONTABILIDAD / MEMBRESÍAS
    Route::get('admin/contabilidad/resumen-membresias', [ResumenMembresiasController::class, 'index'])->name('admin.resumen.membresias');
    Route::get('admin/api/resumen-contabilidad', [ResumenMembresiasController::class, 'getResumenContabilidad']);
    Route::get('admin/exportar/contabilidad-pdf', [ResumenMembresiasController::class, 'exportContabilidadPdf']);
    Route::get('admin/exportar/contabilidad-excel', [ResumenMembresiasController::class, 'exportContabilidadExcel']);
    Route::get('admin/resumen-general/datos', [ResumenGeneralController::class, 'obtenerDatos'])->name('admin.general.datos');
  });


  //Logout (todos los roles)
  Route::get('errors/page-blank', [HomeController::class, 'pageBlank'])->name('errors.page-blank');
  Route::post('/logout', [LoginController::class, 'logoutUsuario'])->name('usuario.logout');

});
