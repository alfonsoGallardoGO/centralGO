<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RestletController;
use App\Http\Controllers\AccountingAccountController;
use App\Http\Controllers\ClaseController;
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
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('/dashboard');
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

});

Route::get('/netsuite/restlet/{scriptId}/{deployId}', [RestletController::class, 'getRestletResponse']);
Route::post('/netsuite/restlet/{scriptId}/{deployId}', [RestletController::class, 'postRestletResponse']);

// //Route::post('/purchase-orders', [SupplierPurchaseOrderController::class, 'storePurchaseOrder'])->name('purchase-orders.store');
