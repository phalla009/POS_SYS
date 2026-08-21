<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\UserManagerController;
use App\Http\Controllers\BakongKhqrController;
use App\Http\Controllers\InventoryItemController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TypeController;

// Public auth routes
Route::get('/', [LoginController::class, 'showLoginForm']);
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes — require login
Route::middleware('auth')->group(function () {

    // Dashboard home
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    // routes/web.php
    Route::delete('orders/bulk-destroy', [OrderController::class, 'bulkDestroy'])->name('orders.bulkDestroy');
    Route::get('/orders/{order}/invoice_partial', [OrderController::class, 'invoicePartial'])->name('orders.invoicePartial');
    Route::get('/orders/invoice_combined', [OrderController::class, 'invoiceCombined'])->name('orders.invoiceCombined');
    // ✅ Downloadable PDF version of the combined invoice — reuses the same
    // orders + view as invoiceCombined() so print and download stay in sync.
    Route::get('/orders/invoice_combined/pdf', [OrderController::class, 'invoiceCombinedPdf'])->name('orders.invoiceCombinedPdf');
    Route::get('/barcodes', [BarcodeController::class, 'index'])->name('barcodes.index');


    Route::resource('types', TypeController::class);
    Route::resource('units', UnitController::class)
        ->parameters(['units' => 'id'])
        ->names('units');

    Route::delete('units-bulk-destroy', [UnitController::class, 'bulkDestroy'])
        ->name('units.bulk-destroy');
    // Product import/export — MUST be registered before the products resource route,
    // otherwise GET /products/export gets swallowed by GET /products/{product} (show),
    // since "export" would be treated as the {product} route parameter and 404 via findOrFail().
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');

    // Product bulk-destroy — MUST also be registered before the products resource route,
    // otherwise DELETE /products/bulk-destroy gets swallowed by DELETE /products/{product}
    // (destroy), since "bulk-destroy" would be treated as the {product} route parameter
    // and 404 via route-model-binding's findOrFail().
    Route::delete('products/bulk-destroy', [ProductController::class, 'bulkDestroy'])->name('products.bulkDestroy');

    // Resource controllers
    Route::resource('products', ProductController::class)->names('products');
    Route::resource('orders', OrderController::class)->names('orders');
    Route::delete('/orders/bulk-destroy', [OrderController::class, 'bulkDestroy'])
    ->name('orders.bulkDestroy');

    // Category bulk-destroy — same ordering rule: must come before the categories
    // resource route or DELETE /categories/bulk-destroy 404s the same way.
    Route::delete('/categories/bulk-destroy', [CategoryController::class, 'bulkDestroy'])
    ->name('categories.bulkDestroy');
    Route::resource('categories', CategoryController::class)->names('categories');

    Route::resource('customers', CustomerController::class)->names('customers');
    Route::resource('inventorys', InventoryController::class)->names('inventorys');
    Route::delete('/inventory-items/bulk-destroy', [InventoryItemController::class, 'bulkDestroy'])
    ->name('inventory-items.bulkDestroy');

    Route::resource('inventory-items', InventoryItemController::class);
    Route::resource('inventory', InventoryController::class);
    Route::resource('payments', PaymentController::class)->names('payments');

    Route::resource('userroles', RoleController::class)->parameters([
        'userroles' => 'role'
    ]);

    Route::resource('usermanagers', UserManagerController::class);
    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    // Payment actions
    Route::get('/payments/payment/{order}', [PaymentController::class, 'payment'])->name('payments.payment');
    Route::post('/payments/store', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/search', [PaymentController::class, 'search'])->name('payments.search');

    Route::post('/pos/khqr/generate', [BakongKhqrController::class, 'khqrGenerate'])->name('pos.khqr.generate');
    Route::post('/pos/khqr/check-payment', [BakongKhqrController::class, 'checkPayment'])->name('pos.khqr.check');
    Route::get('/pos/display', function () {
        return view('pos.customer-display');
    })->name('pos.display');

});
