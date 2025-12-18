<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MedicalSpecialtyController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StorageLocationController;
use App\Http\Controllers\PurchaseOrderReceiptController;
use App\Http\Controllers\PrintJobMonitorController;
use App\Http\Controllers\ProductLayoutController;
use App\Http\Controllers\LegalEntityController;
use App\Http\Controllers\SubWarehouseController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SurgicalKitController;
use App\Http\Controllers\ProductImportController;
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
    // IMPORTACIÓN DE PRODUCTOS
    // ========================================
    Route::get('products/import', [ProductImportController::class, 'showImportForm'])
        ->name('products.import.form');

    Route::get('products/import/template', [ProductImportController::class, 'downloadTemplate'])
        ->name('products.import.template');

    Route::post('products/import/preview', [ProductImportController::class, 'preview'])
        ->name('products.import.preview');

    Route::post('products/import', [ProductImportController::class, 'import'])
        ->name('products.import');
    
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
    Route::get('api/products/search', [PurchaseOrderController::class, 'search'])
        ->name('products.search');
    Route::get('api/products/{product}/details', [PurchaseOrderController::class, 'getProductDetails'])
        ->name('products.details');
    Route::get('/api/products/search-api', [ProductController::class, 'searchApi'])
        ->name('products.searchApi');
    
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

    // ========================================
    // HOSPITALES
    // ========================================
    Route::prefix('hospitals')->name('hospitals.')->group(function () {
        // CRUD
        Route::get('/', [HospitalController::class, 'index'])->name('index');
        Route::get('/create', [HospitalController::class, 'create'])->name('create');
        Route::post('/', [HospitalController::class, 'store'])->name('store');
        Route::get('/{hospital}', [HospitalController::class, 'show'])->name('show');
        Route::get('/{hospital}/edit', [HospitalController::class, 'edit'])->name('edit');
        Route::put('/{hospital}', [HospitalController::class, 'update'])->name('update');
        Route::delete('/{hospital}', [HospitalController::class, 'destroy'])->name('destroy');
        
        // Acciones especiales
        Route::post('/{hospital}/toggle-status', [HospitalController::class, 'toggleStatus'])
            ->name('toggle-status');
    });

    // API para Select2 de Hospitales
    Route::get('/api/hospitals/select2', [HospitalController::class, 'select2'])
        ->name('api.hospitals.select2');

    // ========================================
    // DOCTORES
    // ========================================
    Route::prefix('doctors')->name('doctors.')->group(function () {
        // CRUD
        Route::get('/', [DoctorController::class, 'index'])->name('index');
        Route::get('/create', [DoctorController::class, 'create'])->name('create');
        Route::post('/', [DoctorController::class, 'store'])->name('store');
        Route::get('/{doctor}', [DoctorController::class, 'show'])->name('show');
        Route::get('/{doctor}/edit', [DoctorController::class, 'edit'])->name('edit');
        Route::put('/{doctor}', [DoctorController::class, 'update'])->name('update');
        Route::delete('/{doctor}', [DoctorController::class, 'destroy'])->name('destroy');
        
        // Acciones especiales
        Route::post('/{doctor}/toggle-status', [DoctorController::class, 'toggleStatus'])
            ->name('toggle-status');
    });

    // API para Select2 de Doctores
    Route::get('/api/doctors/select2', [DoctorController::class, 'select2'])
        ->name('api.doctors.select2');

    // ========================================
    // COTIZACIONES ⭐ PRINCIPAL
    // ========================================
    Route::prefix('quotations')->name('quotations.')->group(function () {
        // CRUD
        Route::get('/', [QuotationController::class, 'index'])->name('index');
        Route::get('/create', [QuotationController::class, 'create'])->name('create');
        Route::post('/', [QuotationController::class, 'store'])->name('store');
        Route::get('/{quotation}', [QuotationController::class, 'show'])->name('show');
        Route::get('/{quotation}/edit', [QuotationController::class, 'edit'])->name('edit');
        Route::put('/{quotation}', [QuotationController::class, 'update'])->name('update');
        Route::delete('/{quotation}', [QuotationController::class, 'destroy'])->name('destroy');
        
        // GESTIÓN DE PRODUCTOS
        Route::post('/{quotation}/add-item', [QuotationController::class, 'addItem'])
            ->name('add-item');
        Route::delete('/{quotation}/items/{item}', [QuotationController::class, 'removeItem'])
            ->name('remove-item');
        
        // FLUJO DE CIRUGÍA ⭐
        // 1. Enviar a cirugía
        Route::post('/{quotation}/send-to-surgery', [QuotationController::class, 'sendToSurgery'])
            ->name('send-to-surgery');
        
        // 2. Registrar retorno
        Route::get('/{quotation}/return', [QuotationController::class, 'showReturnForm'])
            ->name('return-form');
        Route::post('/{quotation}/return', [QuotationController::class, 'registerReturn'])
            ->name('register-return');
        
        // 3. Generar ventas
        Route::post('/{quotation}/generate-sales', [QuotationController::class, 'generateSales'])
            ->name('generate-sales');
    });

    // ========================================
    // VENTAS
    // ========================================
    Route::prefix('sales')->name('sales.')->group(function () {
        // Listado y detalle
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
        
        // Exportación
        Route::get('/export/csv', [SaleController::class, 'export'])->name('export');
        
        // Estadísticas
        Route::get('/reports/statistics', [SaleController::class, 'statistics'])->name('statistics');
    });

    // ========================================
    // PREARMADOS QUIRÚRGICOS ⭐ NUEVO
    // ========================================
    Route::prefix('surgical-kits')->name('surgical-kits.')->group(function () {
        // CRUD básico
        Route::get('/', [SurgicalKitController::class, 'index'])->name('index');
        Route::get('/create', [SurgicalKitController::class, 'create'])->name('create');
        Route::post('/', [SurgicalKitController::class, 'store'])->name('store');
        Route::get('/{surgicalKit}', [SurgicalKitController::class, 'show'])->name('show');
        Route::get('/{surgicalKit}/edit', [SurgicalKitController::class, 'edit'])->name('edit');
        Route::put('/{surgicalKit}', [SurgicalKitController::class, 'update'])->name('update');
        Route::delete('/{surgicalKit}', [SurgicalKitController::class, 'destroy'])->name('destroy');
        
        // Verificación de stock
        Route::get('/{surgicalKit}/check-stock', [SurgicalKitController::class, 'checkStock'])
            ->name('check-stock');
        
        // Aplicación a cotizaciones
        Route::get('/{surgicalKit}/select-quotation', [SurgicalKitController::class, 'selectQuotation'])
            ->name('select-quotation');
        Route::post('/{surgicalKit}/apply-to-quotation', [SurgicalKitController::class, 'applyToQuotation'])
            ->name('apply-to-quotation');
        
        // Acciones adicionales
        Route::post('/{surgicalKit}/toggle-active', [SurgicalKitController::class, 'toggleActive'])
            ->name('toggle-active');
        Route::post('/{surgicalKit}/duplicate', [SurgicalKitController::class, 'duplicate'])
            ->name('duplicate');
    });

    // ========================================
    // LEGAL ENTITIES (RAZONES SOCIALES)
    // ========================================
    Route::resource('legal-entities', LegalEntityController::class);
    Route::post('legal-entities/{legalEntity}/toggle-status', [LegalEntityController::class, 'toggleStatus'])
        ->name('legal-entities.toggle-status');

    // ========================================
    // SUB-WAREHOUSES (ALMACENES VIRTUALES)
    // ========================================
    Route::resource('sub-warehouses', SubWarehouseController::class);
    Route::patch('sub-warehouses/{subWarehouse}/toggle-status', [SubWarehouseController::class, 'toggleStatus'])
        ->name('sub-warehouses.toggle-status');
});

require __DIR__.'/auth.php';