<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
//use App\Http\Controllers\ManufacturerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MedicalSpecialtyController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StorageLocationController;
use App\Http\Controllers\PurchaseOrderReceiptController;
use App\Http\Controllers\PrintJobMonitorController;
use App\Http\Controllers\ProductLayoutController;


Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
    
    Route::resource('products', ProductController::class);
    //Route::resource('manufacturers', ManufacturerController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('specialties', MedicalSpecialtyController::class);
    Route::resource('subcategories', SubcategoryController::class);
    Route::resource('product-units', ProductUnitController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('storage_locations', StorageLocationController::class);

    // Ruta adicional para cambiar estado
    Route::patch('suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])
        ->name('suppliers.toggle-status');

    // ========================================
    // ÓRDENES DE COMPRA
    // ========================================
    Route::resource('purchase-orders', PurchaseOrderController::class);
    
    // Acciones sobre órdenes
    Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])
        ->name('purchase-orders.cancel');
    Route::post('purchase-orders/{purchaseOrder}/mark-paid', [PurchaseOrderController::class, 'markAsPaid'])
        ->name('purchase-orders.mark-paid');
    Route::post('purchase-orders/{purchaseOrder}/mark-unpaid', [PurchaseOrderController::class, 'markAsUnpaid'])
        ->name('purchase-orders.mark-unpaid');
    
    // ✅ RECEPCIÓN DE ÓRDENES (una sola ruta limpia)
    Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])
        ->name('purchase-orders.receive');
    
    // AJAX
    Route::get('api/products/{product}/details', [PurchaseOrderController::class, 'getProductDetails'])
        ->name('products.details');

    Route::get('/api/products/search', [PurchaseOrderController::class, 'search'])->name('products.search');

    Route::get('/receipts/{receipt}/print-jobs', [PrintJobMonitorController::class, 'show'])
        ->name('receipts.print-jobs');
    Route::post('/receipts/{receipt}/print-jobs/retry', [PrintJobMonitorController::class, 'retry'])
        ->name('receipts.print-jobs.retry');
    Route::post('/receipts/{receipt}/print-jobs/cancel', [PrintJobMonitorController::class, 'cancel'])
        ->name('receipts.print-jobs.cancel');
    //ruta de prodcutsLayout
    Route::resource('product_layouts', ProductLayoutController::class);


    // ========================================
    // HISTORIAL DE RECEPCIONES (opcional - para después)
    // ========================================
    // Route::get('receipts', [PurchaseOrderController::class, 'receiptsIndex'])->name('receipts.index');
    // Route::get('purchase-orders/{purchaseOrder}/receipts', [PurchaseOrderController::class, 'receipts'])->name('purchase-orders.receipts');
    // Route::get('receipts/{receipt}', [PurchaseOrderController::class, 'showReceipt'])->name('receipts.show');

    // Toggle status de usuarios
    Route::put('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->middleware(['auth', 'verified']) 
        ->name('users.toggle-status');
});

require __DIR__.'/auth.php';