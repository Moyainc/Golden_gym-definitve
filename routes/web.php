<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;


// RUTAS PÚBLICAS
Route::get('/', [HomeController::class, 'index'])->name('inicio');
Route::get('sign-in', [HomeController::class, 'login'])->name('user.login');
Route::get('sign-up', [HomeController::class, 'register'])->name('user.register');
Route::post('sign-up', [HomeController::class, 'registerUsuario'])->name('usuario.register');
Route::post('sign-in', [HomeController::class, 'loginUsuario'])->name('usuario.login');


// RUTAS ADMIN (DASHBOARD Y PERFIL)
Route::get('admin/dashboard', [HomeController::class, 'dashboard'])->name('admin.dashboard');
Route::get('admin/profile', [HomeController::class, 'profile'])->name('admin.profile');


// ADMIN → CLIENTES / CONTABILIDAD / PAGOS
Route::get('admin/clientes', [HomeController::class, 'clientes'])->name('admin.clientes');
Route::get('admin/contabilidad', [HomeController::class, 'contabilidad'])->name('admin.contabilidad');
Route::get('admin/confirmacion-pagos', [HomeController::class, 'confirmacion_pagos'])->name('admin.confirmacion_pagos');


// ADMIN → VENTAS
Route::get('admin/ventas/resumen-ventas', [HomeController::class, 'resumen_ventas'])->name('admin.resumen_ventas');
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
Route::get('admin/membresias/list', [MembresiaController::class, 'list'])->name('admin.membresias.list');
Route::post('admin/membresias', [MembresiaController::class, 'store'])->name('admin.membresias.store');
Route::put('admin/membresias/{id}/edit', [MembresiaController::class, 'update'])->name('admin.membresias.update');
Route::delete('admin/membresias/{id}', [MembresiaController::class, 'destroy'])->name('admin.membresias.destroy');


Route::get('admin/productos/list', [ProductoController::class, 'list'])->name('admin.membresias.list');
