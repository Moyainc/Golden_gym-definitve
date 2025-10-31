<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PagoDiarioController;
// RUTAS PÚBLICAS
Route::get('/', [HomeController::class, 'index'])->name('inicio');
Route::get('sign-in', [HomeController::class, 'login'])->name('user.login');
Route::get('sign-up', [HomeController::class, 'register'])->name('user.register');
Route::post('sign-up', [HomeController::class, 'registerUsuario'])->name('usuario.register');
Route::post('sign-in', [HomeController::class, 'loginUsuario'])->name('usuario.login');


// RUTAS ADMIN (DASHBOARD Y PERFIL)
Route::get('admin/dashboard', [HomeController::class, 'dashboard'])->name('admin.dashboard');
Route::get('admin/profile', [HomeController::class, 'profile'])->name('admin.profile');


// ADMIN → CONTABILIDAD / PAGOS

Route::get('admin/confirmacion-pagos', [HomeController::class, 'confirmacion_pagos'])->name('admin.confirmacion_pagos');
Route::get('admin/contabilidad/resumen-ventas', [HomeController::class, 'resumen_ventas'])->name('admin.resumen.ventas');
Route::get('admin/contabilidad/resumen-membresias', [HomeController::class, 'resumen_membresias'])->name('admin.resumen.membresias');
Route::get('admin/contabilidad/resumen-general', [HomeController::class, 'resumen_general'])->name('admin.resumen.general');

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

// ADMIN → VENTAS
Route::get('admin/ventas/vender', [HomeController::class, 'vender'])->name('admin.vender');
Route::get('admin/ventas/lista-ventas', [HomeController::class, 'lista_ventas'])->name('admin.lista_ventas');


// ADMIN → USUARIOS
Route::get('admin/usuarios', [HomeController::class, 'usuarios'])->name('admin.usuarios');
Route::post('/admin/usuarios', [HomeController::class, 'store'])->name('admin.usuarios.store');
Route::get('/admin/usuarios/{id}/edit', [HomeController::class, 'editUsuario'])->name('admin.usuarios.edit');
Route::put('/admin/usuarios/{id}', [HomeController::class, 'updateUsuario'])->name('admin.usuarios.update');
Route::delete('/admin/usuarios/{id}', [HomeController::class, 'destroyUsuario'])->name('admin.usuarios.destroy');


// ADMIN → PRODUCTOS (vista general)
Route::get('admin/productos', [HomeController::class, 'productos'])->name('admin.productos');


// ADMIN → CATEGORÍAS (CRUD con CategoriaController)
Route::get('/admin/categorias/list', [CategoriaController::class, 'getCategorias'])->name('admin.categorias.list');
Route::post('/admin/categorias', [CategoriaController::class, 'store'])->name('admin.categorias.store');
Route::get('/admin/categorias/{id}/edit', [CategoriaController::class, 'edit'])->name('admin.categorias.edit');
Route::put('/admin/categorias/{id}', [CategoriaController::class, 'update'])->name('admin.categorias.update');
Route::delete('/admin/categorias/{id}', [CategoriaController::class, 'destroy'])->name('admin.categorias.destroy');


// ADMIN → MEMBRESÍAS
Route::get('admin/membresias', [HomeController::class, 'membresias'])->name('admin.membresias');
Route::prefix('admin/membresias')->group(function () {
    Route::get('/list/json', [MembresiaController::class, 'index']); // listado JSON
    Route::get('/json/{id}', [MembresiaController::class, 'edit']); // obtener una membresía específica
    Route::post('/json', [MembresiaController::class, 'store']); // guardar
    Route::put('/json/{id}', [MembresiaController::class, 'update']); // actualizar
    Route::delete('/json/{id}', [MembresiaController::class, 'destroy']); // 👈 nueva ruta para eliminar
});



Route::get('admin/productos/list', [ProductoController::class, 'list'])->name('admin.productos.list');
