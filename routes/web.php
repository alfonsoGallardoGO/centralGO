<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RestletController;
use App\Http\Controllers\AccountingAccountController;
use App\Http\Controllers\ClaseController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\VistaController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [PortalController::class, 'indexDashboard'])->name('/dashboard');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('catalogo/departamentos', [DepartmentController::class, 'index'])->name('/catalogo/departamentos');
    Route::post('catalogo/departamentos/store', [DepartmentController::class, 'store'])->name('catalogo.departamentos.store');
    Route::put('catalogo/departamentos/{department}', [DepartmentController::class, 'update'])->name('catalogo.departamentos.update');
    Route::delete('catalogo/departamentos/{department}', [DepartmentController::class, 'destroy'])->name('catalogo.departamentos.destroy');
    Route::post('catalogo/departamentos/multiple', [DepartmentController::class, 'destroyMultiple'])
    ->name('catalogo.departamentos.destroyMultiple');

    Route::get('catalogo/categorias-gastos', [ExpenseController::class, 'index'])->name('/catalogo/categorias-gastos');
    Route::post('catalogo/categorias-gastos/store', [ExpenseController::class, 'store'])->name('catalogo.categorias-gastos.store');
    Route::put('catalogo/categorias-gastos/{expense}', [ExpenseController::class, 'update'])->name('catalogo.categorias-gastos.update');
    Route::delete('catalogo/categorias-gastos/{expense}', [ExpenseController::class, 'destroy'])->name('catalogo.categorias-gastos.destroy');
    Route::post('catalogo/categorias-gastos/multiple', [ExpenseController::class, 'destroyMultiple'])
    ->name('catalogo.categorias-gastos.destroyMultiple');

    Route::get('catalogo/ubicaciones', [LocationController::class, 'index'])->name('/catalogo/ubicaciones');
    Route::post('catalogo/ubicaciones/store', [LocationController::class, 'store'])->name('catalogo.ubicaciones.store');
    Route::put('catalogo/ubicaciones/{location}', [LocationController::class, 'update'])->name('catalogo.ubicaciones.update');
    Route::delete('catalogo/ubicaciones/{location}', [LocationController::class, 'destroy'])->name('catalogo.ubicaciones.destroy');
    Route::post('catalogo/ubicaciones/multiple', [LocationController::class, 'destroyMultiple'])
    ->name('catalogo.ubicaciones.destroyMultiple');

    Route::get('catalogo/cuentas-contables', [AccountingAccountController::class, 'index'])->name('/catalogo/cuentas-contables');
    Route::post('catalogo/cuentas-contables/store', [AccountingAccountController::class, 'store'])->name('catalogo.cuentas-contables.store');
    Route::put('catalogo/cuentas-contables/{accountingAccount}', [AccountingAccountController::class, 'update'])->name('catalogo.cuentas-contables.update');
    Route::delete('catalogo/cuentas-contables/{accountingAccount}', [AccountingAccountController::class, 'destroy'])->name('catalogo.cuentas-contables.destroy');
    Route::post('catalogo/cuentas-contables/multiple', [AccountingAccountController::class, 'destroyMultiple'])
    ->name('catalogo.cuentas-contables.destroyMultiple');

    Route::get('catalogo/clases', [ClaseController::class, 'index'])->name('/catalogo/clases');
    Route::post('catalogo/clases/store', [ClaseController::class, 'store'])->name('catalogo.clases.store');
    Route::put('catalogo/clases/{clase}', [ClaseController::class, 'update'])->name('catalogo.clases.update');
    Route::delete('catalogo/clases/{clase}', [ClaseController::class, 'destroy'])->name('catalogo.clases.destroy');
    Route::post('catalogo/clases/multiple', [ClaseController::class, 'destroyMultiple'])
    ->name('catalogo.clases.destroyMultiple');

    Route::get('catalogo/portales', [PortalController::class, 'index'])->name('/catalogo/portales');
    Route::post('catalogo/portales/store', [PortalController::class, 'store'])->name('catalogo.portales.store');
    Route::put('catalogo/portales/{portal}', [PortalController::class, 'update'])->name('catalogo.portales.update');
    Route::delete('catalogo/portales/{portal}', [PortalController::class, 'destroy'])->name('catalogo.portales.destroy');
    Route::post('catalogo/portales/multiple', [PortalController::class, 'destroyMultiple'])
    ->name('catalogo.portales.destroyMultiple');

});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/seguridad/roles', [RoleController::class, 'index'])->name('/seguridad/roles');
    Route::post('/seguridad/roles', [RoleController::class, 'store'])->name('seguridad.roles.store');
    Route::put('/seguridad/roles/{id}/permissions', [RoleController::class, 'updateRolePermissions'])->name('seguridad.roles.updatePermissions');
    Route::delete('/seguridad/roles/{id}', [RoleController::class, 'destroy'])->name('seguridad.roles.destroy');

    Route::get('/seguridad/permisos', [PermissionController::class, 'index'])->name('/seguridad/permisos');
    Route::post('/seguridad/permisos', [PermissionController::class, 'store'])->name('seguridad.permisos.store');
    Route::put('/seguridad/permisos/{id}', [PermissionController::class, 'update'])->name('seguridad.permisos.update');
    Route::delete('/seguridad/permisos/{id}', [PermissionController::class, 'destroy'])->name('seguridad.permisos.destroy');

    Route::get('/seguridad/vistas', [VistaController::class, 'index'])->name('/seguridad/vistas');
    Route::post('/seguridad/vistas', [VistaController::class, 'store'])->name('seguridad.vistas.store');
    Route::put('/seguridad/vistas/{id}', [VistaController::class, 'update'])->name('seguridad.vistas.update');
    Route::delete('/seguridad/vistas/{id}', [VistaController::class, 'destroy'])->name('seguridad.vistas.destroy');
    Route::post('/seguridad/vistas/multiple', [VistaController::class, 'destroyMultiple'])->name('seguridad.vistas.destroyMultiple');

    Route::get('/seguridad/modulos', [ModuloController::class, 'index'])->name('/seguridad/modulos');
    Route::post('/seguridad/modulos', [ModuloController::class, 'store'])->name('seguridad.modulos.store');
    Route::put('/seguridad/modulos/{modulo}', [ModuloController::class, 'update'])->name('seguridad.modulos.update');
    Route::delete('/seguridad/modulos/{modulo}', [ModuloController::class, 'destroy'])->name('seguridad.modulos.destroy');
    Route::post('/seguridad/modulos/multiple', [ModuloController::class, 'destroyMultiple'])->name('seguridad.modulos.destroyMultiple');

    Route::get('/seguridad/usuarios', [UsuariosController::class, 'index'])->name('/seguridad/usuarios');
    Route::get('/seguridad/usuarios/{user}', [UsuariosController::class, 'edit'])->name('seguridad.usuarios.edit');
    Route::put('/seguridad/usuarios/{user}', [UsuariosController::class, 'update'])->name('seguridad.usuarios.update');
    Route::put('/seguridad/usuarios/{user}/permissions', [UsuariosController::class, 'updatePermissions'])->name('seguridad.usuarios.updatePermissions');
});

Route::get('/netsuite/restlet/{scriptId}/{deployId}', [RestletController::class, 'getRestletResponse']);
Route::post('/netsuite/restlet/{scriptId}/{deployId}', [RestletController::class, 'postRestletResponse']);

// //Route::post('/purchase-orders', [SupplierPurchaseOrderController::class, 'storePurchaseOrder'])->name('purchase-orders.store');
