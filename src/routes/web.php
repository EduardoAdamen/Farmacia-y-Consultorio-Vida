<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\UsuarioController;
use App\Http\Controllers\PanelController;

// ── Autenticación ──────────────────────────────────────────
Route::get('/',       [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

// ── Área autenticada ───────────────────────────────────────
Route::middleware(['auth', 'sesion.activa'])->group(function () {

    // Panel de Inicio (CU-02)
    Route::get('/panel-inicio', [PanelController::class, 'index'])->name('panel-inicio');

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
    // buscar-ajax es accesible también por médico (lo necesita al generar recetas)
    Route::middleware('rol:dueno,vendedor,medico')
         ->get('/productos/buscar', [\App\Http\Controllers\ProductoController::class, 'buscarAjax'])
         ->name('productos.buscar-ajax');

    Route::middleware('rol:dueno,vendedor')->prefix('productos')->name('productos.')->group(function () {
        Route::get('/',                 [\App\Http\Controllers\ProductoController::class, 'index'])->name('index');
        Route::get('/crear',            [\App\Http\Controllers\ProductoController::class, 'create'])->name('create')->middleware('rol:dueno');
        Route::post('/',                [\App\Http\Controllers\ProductoController::class, 'store'])->name('store')->middleware('rol:dueno');
        Route::get('/{id}',             [\App\Http\Controllers\ProductoController::class, 'show'])->name('show');
        Route::get('/{id}/editar',      [\App\Http\Controllers\ProductoController::class, 'edit'])->name('edit')->middleware('rol:dueno');
        Route::put('/{id}',             [\App\Http\Controllers\ProductoController::class, 'update'])->name('update')->middleware('rol:dueno');
        Route::delete('/{id}',          [\App\Http\Controllers\ProductoController::class, 'destroy'])->name('destroy')->middleware('rol:dueno');
        Route::patch('/{id}/activar',   [\App\Http\Controllers\ProductoController::class, 'activar'])->name('activar')->middleware('rol:dueno');
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

    // Citas Médicas
    Route::middleware('rol:medico,vendedor,dueno')->prefix('citas')->name('citas.')->group(function () {
        Route::get('/',                          [\App\Http\Controllers\CitaController::class, 'index'])->name('index');
        Route::get('/crear',                     [\App\Http\Controllers\CitaController::class, 'create'])->name('create');
        Route::post('/',                         [\App\Http\Controllers\CitaController::class, 'store'])->name('store');
        Route::get('/verificar-disponibilidad',  [\App\Http\Controllers\CitaController::class, 'verificarDisponibilidad'])->name('verificar-disponibilidad');
        Route::get('/{id}',                      [\App\Http\Controllers\CitaController::class, 'show'])->name('show');
        Route::get('/{id}/editar',               [\App\Http\Controllers\CitaController::class, 'edit'])->name('edit');
        Route::put('/{id}',                      [\App\Http\Controllers\CitaController::class, 'update'])->name('update');
    });

    // Expedientes Clínicos — lectura: médico y dueño | escritura: solo médico (middleware inline)
    // buscar-ajax también accesible por vendedor (necesita buscar expedientes al crear citas)
    Route::middleware('rol:medico,dueno,vendedor')
         ->get('/expedientes/buscar', [\App\Http\Controllers\ExpedienteController::class, 'buscarAjax'])
         ->name('expedientes.buscar-ajax');

    // IMPORTANTE: rutas literales (/crear) van ANTES del wildcard (/{id}) para evitar conflictos.
    Route::middleware('rol:medico,dueno')->prefix('expedientes')->name('expedientes.')->group(function () {
        Route::get('/',                  [\App\Http\Controllers\ExpedienteController::class, 'index'])->name('index');
        Route::get('/crear',             [\App\Http\Controllers\ExpedienteController::class, 'create'])->name('create')->middleware('rol:medico');
        Route::post('/',                 [\App\Http\Controllers\ExpedienteController::class, 'store'])->name('store')->middleware('rol:medico');
        Route::get('/{id}',              [\App\Http\Controllers\ExpedienteController::class, 'show'])->name('show');
        Route::get('/{id}/editar',       [\App\Http\Controllers\ExpedienteController::class, 'edit'])->name('edit')->middleware('rol:medico');
        Route::put('/{id}',              [\App\Http\Controllers\ExpedienteController::class, 'update'])->name('update')->middleware('rol:medico');
        Route::patch('/{id}/archivar',   [\App\Http\Controllers\ExpedienteController::class, 'archivar'])->name('archivar')->middleware('rol:medico');
        Route::patch('/{id}/desarchivar',[\App\Http\Controllers\ExpedienteController::class, 'desarchivar'])->name('desarchivar')->middleware('rol:medico');
    });

    // Vista de impresión de recetas (Dueño y Médico)
    Route::middleware('rol:medico,dueno')
         ->get('/recetas/{id}/imprimir', [\App\Http\Controllers\RecetaController::class, 'imprimir'])
         ->name('recetas.imprimir');

    // Consultas médicas — lectura: médico y dueño | escritura: solo médico (middleware inline)
    // IMPORTANTE: rutas literales (/nueva) van ANTES del wildcard (/{id}) para evitar conflictos.
    Route::middleware('rol:medico,dueno')->prefix('consultas')->name('consultas.')->group(function () {
        Route::get('/nueva',         [\App\Http\Controllers\ConsultaController::class, 'create'])->name('create')->middleware('rol:medico');
        Route::post('/',             [\App\Http\Controllers\ConsultaController::class, 'store'])->name('store')->middleware('rol:medico');
        Route::get('/{id}',          [\App\Http\Controllers\ConsultaController::class, 'show'])->name('show');
        Route::get('/{id}/editar',   [\App\Http\Controllers\ConsultaController::class, 'edit'])->name('edit')->middleware('rol:medico');
        Route::put('/{id}',          [\App\Http\Controllers\ConsultaController::class, 'update'])->name('update')->middleware('rol:medico');
        Route::patch('/{id}/notas',  [\App\Http\Controllers\ConsultaController::class, 'updateNotas'])->name('update-notas')->middleware('rol:medico');
    });

    // Recetas — solo médico
    Route::middleware('rol:medico')->prefix('recetas')->name('recetas.')->group(function () {
        Route::get('/consulta/{consultaId}/crear', [\App\Http\Controllers\RecetaController::class, 'create'])->name('create');
        Route::post('/consulta/{consultaId}',      [\App\Http\Controllers\RecetaController::class, 'store'])->name('store');
    });

    // Reportes (Sprint 8)
    Route::middleware('rol:dueno')->prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/',           [\App\Http\Controllers\ReporteController::class, 'index'])->name('index');
        Route::get('/ventas',     [\App\Http\Controllers\ReporteController::class, 'ventas'])->name('ventas');
        Route::get('/consultas',  [\App\Http\Controllers\ReporteController::class, 'consultas'])->name('consultas');
        Route::get('/inventario', [\App\Http\Controllers\ReporteController::class, 'inventario'])->name('inventario');
    });

});
