<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('inicio');
Route::get('admin/clientes', [HomeController::class, 'clientes'])->name('admin.clientes');
Route::get('admin/confirmacion-pagos', [HomeController::class, 'confirmacion_pagos'])->name('admin.confirmacion_pagos');
Route::get('admin/contabilidad', [HomeController::class, 'contabilidad'])->name('admin.contabilidad');
Route::get('admin/dashboard', [HomeController::class, 'dashboard'])->name('admin.dashboard');
Route::get('admin/membresias', [HomeController::class, 'membresias'])->name('admin.membresias');
Route::get('admin/productos', [HomeController::    class, 'productos'])->name('admin.productos');
Route::get('admin/profile', [HomeController::class, 'profile'])->name('admin.profile');
Route::get('admin/sign-in', [HomeController::class, 'login'])->name('admin.login');
Route::get('admin/usuarios', [HomeController::class, 'usuarios'])->name('admin.usuarios');
Route::get('admin/ventas/resumen-ventas', [HomeController::class, 'resumen_ventas'])->name('admin.resumen_ventas');
Route::get('admin/ventas/vender', [HomeController::class, 'vender'])->name('admin.vender');
Route::get('admin/ventas/lista-ventas', [HomeController::class, 'lista_ventas'])->name('admin.lista_ventas');
Route::post('/admin/usuarios', [HomeController::class, 'store'])->name('admin.usuarios.store');
Route::get('/admin/usuarios/{id}/edit', [HomeController::class, 'editUsuario'])->name('admin.usuarios.edit');
Route::put('/admin/usuarios/{id}', [HomeController::class, 'updateUsuario'])->name('admin.usuarios.update');
Route::delete('/admin/usuarios/{id}', [HomeController::class, 'destroy'])->name('admin.usuarios.destroy');
