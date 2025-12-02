<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
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
use App\Http\Controllers\LegalEntityController;
use App\Http\Controllers\SubWarehouseController;

// ========================================
// RUTAS PÚBLICAS
// ========================================
Route::get('/', function () {
    return view('auth.login');
});

// ========================================
// RUTAS AUTENTICADAS
// ========================================
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Perfil de usuario
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
});

// ========================================
// RUTAS DE ADMINISTRADOR
// ========================================
Route::middleware(['auth', 'role:admin'])->group(function () {
    
    // ========================================
    // GESTIÓN DE USUARIOS
    // ========================================
    Route::resource('users', UserController::class);
    Route::put('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');

    // ========================================
    // CATÁLOGOS BÁSICOS
    // ========================================
    Route::resource('categories', CategoryController::class);
    Route::resource('subcategories', SubcategoryController::class);
    Route::resource('specialties', MedicalSpecialtyController::class);
    Route::resource('storage_locations', StorageLocationController::class);

    // ========================================
    // PROVEEDORES
    // ========================================
    Route::resource('suppliers', SupplierController::class);
    Route::patch('suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])
        ->name('suppliers.toggle-status');

    // ========================================
    // PRODUCTOS
    // ========================================
    // Rutas API/AJAX para productos (deben ir ANTES del resource)
    Route::get('api/products/search', [PurchaseOrderController::class, 'search'])
        ->name('products.search');
    Route::get('api/products/{product}/details', [PurchaseOrderController::class, 'getProductDetails'])
        ->name('products.details');
    
    // Resource de productos
    Route::resource('products', ProductController::class);

    // ========================================
    // UNIDADES DE PRODUCTOS (INVENTARIO)
    // ========================================
    Route::resource('product-units', ProductUnitController::class);

    // ========================================
    // LAYOUTS DE PRODUCTOS (UBICACIONES FÍSICAS)
    // ========================================
    // Rutas personalizadas ANTES del resource
    Route::get('product_layouts/search/products', [ProductLayoutController::class, 'searchProducts'])
        ->name('product_layouts.search-products');
    
    Route::post('product_layouts/{productLayout}/assign-product', [ProductLayoutController::class, 'assignProduct'])
        ->name('product_layouts.assign-product');
    
    Route::delete('product_layouts/{productLayout}/remove-product', [ProductLayoutController::class, 'removeProduct'])
        ->name('product_layouts.remove-product');
    
    // Resource de product layouts
    Route::resource('product_layouts', ProductLayoutController::class);

    // ========================================
    // ÓRDENES DE COMPRA
    // ========================================
    // Acciones especiales sobre órdenes (ANTES del resource)
    Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])
        ->name('purchase-orders.cancel');
    Route::post('purchase-orders/{purchaseOrder}/mark-paid', [PurchaseOrderController::class, 'markAsPaid'])
        ->name('purchase-orders.mark-paid');
    Route::post('purchase-orders/{purchaseOrder}/mark-unpaid', [PurchaseOrderController::class, 'markAsUnpaid'])
        ->name('purchase-orders.mark-unpaid');
    Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])
        ->name('purchase-orders.receive');
    
    // Resource de órdenes de compra
    Route::resource('purchase-orders', PurchaseOrderController::class);

    // ========================================
    // TRABAJOS DE IMPRESIÓN (PRINT JOBS)
    // ========================================
    Route::prefix('receipts/{receipt}')->name('receipts.')->group(function () {
        Route::get('print-jobs', [PrintJobMonitorController::class, 'show'])
            ->name('print-jobs');
        Route::post('print-jobs/retry', [PrintJobMonitorController::class, 'retry'])
            ->name('print-jobs.retry');
        Route::post('print-jobs/cancel', [PrintJobMonitorController::class, 'cancel'])
            ->name('print-jobs.cancel');
    });

    /**
     * RUTAS DE LEGAL ENTITIES
     */
    Route::resource('legal-entities', LegalEntityController::class);
    
    // Ruta adicional para cambiar el estado activo/inactivo
    Route::post('legal-entities/{legalEntity}/toggle-status', [LegalEntityController::class, 'toggleStatus'])
        ->name('legal-entities.toggle-status');

    /**
     * Rutas de sub warehouses
     */
    // Sub-Warehouses (Almacenes Virtuales)
    Route::post('/sub-warehouses', [SubWarehouseController::class, 'store'])->name('sub-warehouses.store');
    Route::put('/sub-warehouses/{subWarehouse}', [SubWarehouseController::class, 'update'])->name('sub-warehouses.update');
    Route::patch('/sub-warehouses/{subWarehouse}/toggle-status', [SubWarehouseController::class, 'toggleStatus'])->name('sub-warehouses.toggle-status');
    Route::delete('/sub-warehouses/{subWarehouse}', [SubWarehouseController::class, 'destroy'])->name('sub-warehouses.destroy');
   

    // ========================================
    // HISTORIAL DE RECEPCIONES (FUTURO)
    // ========================================
    // Route::get('receipts', [PurchaseOrderController::class, 'receiptsIndex'])->name('receipts.index');
    // Route::get('purchase-orders/{purchaseOrder}/receipts', [PurchaseOrderController::class, 'receipts'])->name('purchase-orders.receipts');
    // Route::get('receipts/{receipt}', [PurchaseOrderController::class, 'showReceipt'])->name('receipts.show');
});

require __DIR__.'/auth.php';