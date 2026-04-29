<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\UsuarioController;
use App\Http\Controllers\DashboardController;

// ── Autenticación ──────────────────────────────────────────
Route::get('/',       [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

// ── Área autenticada ───────────────────────────────────────
Route::middleware(['auth', 'sesion.activa'])->group(function () {

    // Dashboard / Panel de Inicio (CU-02)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Cambiar contraseña propia (FA_004 de CU-03)
    Route::get('/perfil/password',   [UsuarioController::class, 'showCambiarPassword'])->name('perfil.password');
    Route::patch('/perfil/password', [UsuarioController::class, 'cambiarPassword'])->name('perfil.cambiar-password');

    // ── Gestión de Usuarios — solo Dueño (CU-03) ──────────
    Route::middleware('rol:dueno')->prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/',               [UsuarioController::class, 'index'])->name('index');
        Route::get('/crear',          [UsuarioController::class, 'create'])->name('create');
        Route::post('/',              [UsuarioController::class, 'store'])->name('store');
        Route::get('/{id}/editar',    [UsuarioController::class, 'edit'])->name('edit');
        Route::put('/{id}',           [UsuarioController::class, 'update'])->name('update');
        Route::patch('/{id}/estado',  [UsuarioController::class, 'toggleEstado'])->name('toggle-estado');
        Route::patch('/{id}/reset',   [UsuarioController::class, 'resetPassword'])->name('reset-password');
    });

    // Proveedores
    Route::middleware('rol:dueno')->prefix('proveedores')->name('proveedores.')->group(function () {
        Route::get('/',              [\App\Http\Controllers\ProveedorController::class, 'index'])->name('index');
        Route::get('/crear',         [\App\Http\Controllers\ProveedorController::class, 'create'])->name('create');
        Route::post('/',             [\App\Http\Controllers\ProveedorController::class, 'store'])->name('store');
        Route::get('/{id}',          [\App\Http\Controllers\ProveedorController::class, 'show'])->name('show');
        Route::get('/{id}/editar',   [\App\Http\Controllers\ProveedorController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [\App\Http\Controllers\ProveedorController::class, 'update'])->name('update');
        Route::delete('/{id}',       [\App\Http\Controllers\ProveedorController::class, 'destroy'])->name('destroy');
    });

    // Pedidos
    Route::middleware('rol:dueno')->prefix('pedidos')->name('pedidos.')->group(function () {
        Route::get('/',                      [\App\Http\Controllers\PedidoController::class, 'index'])->name('index');
        Route::get('/crear',                 [\App\Http\Controllers\PedidoController::class, 'create'])->name('create');
        Route::post('/',                     [\App\Http\Controllers\PedidoController::class, 'store'])->name('store');
        Route::get('/{id}',                  [\App\Http\Controllers\PedidoController::class, 'show'])->name('show');
        Route::get('/{id}/editar',           [\App\Http\Controllers\PedidoController::class, 'edit'])->name('edit');
        Route::put('/{id}',                  [\App\Http\Controllers\PedidoController::class, 'update'])->name('update');
        Route::patch('/{id}/recibir',        [\App\Http\Controllers\PedidoController::class, 'recibirPedido'])->name('recibir');
        Route::patch('/{id}/cancelar',       [\App\Http\Controllers\PedidoController::class, 'cancelarPedido'])->name('cancelar');
        Route::patch('/{id}/pagar',          [\App\Http\Controllers\PedidoController::class, 'marcarPagado'])->name('pagar');
    });

    // Categorías
    Route::middleware('rol:dueno,vendedor')->prefix('categorias')->name('categorias.')->group(function () {
        Route::get('/',            [\App\Http\Controllers\CategoriaController::class, 'index'])->name('index');
        Route::get('/crear',       [\App\Http\Controllers\CategoriaController::class, 'create'])->name('create')->middleware('rol:dueno');
        Route::post('/',           [\App\Http\Controllers\CategoriaController::class, 'store'])->name('store')->middleware('rol:dueno');
        Route::get('/{id}/editar', [\App\Http\Controllers\CategoriaController::class, 'edit'])->name('edit')->middleware('rol:dueno');
        Route::put('/{id}',        [\App\Http\Controllers\CategoriaController::class, 'update'])->name('update')->middleware('rol:dueno');
    });

    // Productos
    Route::middleware('rol:dueno,vendedor')->prefix('productos')->name('productos.')->group(function () {
        Route::get('/',                 [\App\Http\Controllers\ProductoController::class, 'index'])->name('index');
        Route::get('/buscar',           [\App\Http\Controllers\ProductoController::class, 'buscarAjax'])->name('buscar-ajax');
        Route::get('/crear',            [\App\Http\Controllers\ProductoController::class, 'create'])->name('create')->middleware('rol:dueno');
        Route::post('/',                [\App\Http\Controllers\ProductoController::class, 'store'])->name('store')->middleware('rol:dueno');
        Route::get('/{id}',             [\App\Http\Controllers\ProductoController::class, 'show'])->name('show');
        Route::get('/{id}/editar',      [\App\Http\Controllers\ProductoController::class, 'edit'])->name('edit')->middleware('rol:dueno');
        Route::put('/{id}',             [\App\Http\Controllers\ProductoController::class, 'update'])->name('update')->middleware('rol:dueno');
        Route::delete('/{id}',          [\App\Http\Controllers\ProductoController::class, 'destroy'])->name('destroy')->middleware('rol:dueno');
        // Agregar lote a producto
        Route::post('/{id}/lotes',      [\App\Http\Controllers\LoteController::class, 'store'])->name('lotes.store')->middleware('rol:dueno');
    });

    // Ventas
    Route::middleware('rol:dueno,vendedor')->prefix('ventas')->name('ventas.')->group(function () {
        Route::get('/',                    [\App\Http\Controllers\VentaController::class, 'index'])->name('index');
        Route::post('/',                   [\App\Http\Controllers\VentaController::class, 'store'])->name('store');
        Route::get('/historial',           [\App\Http\Controllers\VentaController::class, 'historial'])->name('historial');
        Route::get('/{id}',                [\App\Http\Controllers\VentaController::class, 'show'])->name('show');
        Route::patch('/{id}/cancelar',     [\App\Http\Controllers\VentaController::class, 'cancelar'])->name('cancelar')->middleware('rol:dueno');
    });

});
