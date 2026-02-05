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
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\SurgicalChecklistController;
use App\Http\Controllers\ChecklistItemController;
use App\Http\Controllers\PreAssembledPackageController;
use App\Http\Controllers\ScheduledSurgeryController;
use App\Http\Controllers\SurgeryPreparationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ChecklistConditionalController;
use App\Http\Controllers\PurchaseOrderBulkImportController;


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


    //RUTA PARA CARGAR CONFIGURACIONES DE HOSPITALES
Route::get('/api/hospitals/{hospital}/configs', [HospitalController::class, 'getConfigs'])->name('api.hospitals.configs');

Route::prefix('checklist-items/{item}/conditionals')->name('checklist-conditionals.')->group(function () {
        Route::get('/', [ChecklistConditionalController::class, 'index'])->name('index');
        Route::post('/', [ChecklistConditionalController::class, 'store'])->name('store');
        Route::put('/{conditional}', [ChecklistConditionalController::class, 'update'])->name('update');
        Route::delete('/{conditional}', [ChecklistConditionalController::class, 'destroy'])->name('destroy');
        Route::post('/preview', [ChecklistConditionalController::class, 'preview'])->name('preview');
    });

Route::get('/conditional-form-data', [ChecklistConditionalController::class, 'getFormData'])->name('conditional-form-data');


    // ====================================================================
    // MÓDULO 1: SURGICAL CHECKLISTS (Plantillas de Check Lists)
    // ====================================================================
    Route::prefix('checklists')->name('checklists.')->group(function () {
        
        // CRUD básico
        Route::get('/', [SurgicalChecklistController::class, 'index'])->name('index');
        Route::get('/create', [SurgicalChecklistController::class, 'create'])->name('create');
        Route::post('/', [SurgicalChecklistController::class, 'store'])->name('store');
        Route::get('/{checklist}', [SurgicalChecklistController::class, 'show'])->name('show');
        Route::get('/{checklist}/edit', [SurgicalChecklistController::class, 'edit'])->name('edit');
        Route::put('/{checklist}', [SurgicalChecklistController::class, 'update'])->name('update');
        Route::delete('/{checklist}', [SurgicalChecklistController::class, 'destroy'])->name('destroy');

        // Gestión de items
        Route::get('/{checklist}/items', [SurgicalChecklistController::class, 'items'])->name('items');
        
        // Duplicar check list
        Route::post('/{checklist}/duplicate', [SurgicalChecklistController::class, 'duplicate'])->name('duplicate');
    });

    // ====================================================================
    // MÓDULO 2: CHECKLIST ITEMS (Items y Condicionales)
    // ====================================================================
    Route::prefix('checklist-items')->name('checklist-items.')->group(function () {
        
        // Agregar item a un check list
        Route::post('/checklists/{checklist}', [ChecklistItemController::class, 'store'])->name('store');
        
        // Actualizar item
        Route::put('/{item}', [ChecklistItemController::class, 'update'])->name('update');
        
        // Eliminar item
        Route::delete('/{item}', [ChecklistItemController::class, 'destroy'])->name('destroy');
        
        // Reordenar items
        Route::post('/checklists/{checklist}/reorder', [ChecklistItemController::class, 'reorder'])->name('reorder');
        
        // ---- CONDICIONALES ----
        
        // Agregar condicional a un item
        //Route::post('/{item}/conditionals', [ChecklistItemController::class, 'addConditional'])->name('conditionals.add');
        
        // Eliminar condicional
        Route::delete('/conditionals/{conditional}', [ChecklistItemController::class, 'removeConditional'])->name('conditionals.remove');
    });

    // ====================================================================
    // MÓDULO 3: PRE-ASSEMBLED PACKAGES (Paquetes Pre-Armados)
    // ====================================================================
    Route::prefix('pre-assembled')->name('pre-assembled.')->group(function () {
        
        // CRUD básico
        Route::get('/', [PreAssembledPackageController::class, 'index'])->name('index');
        Route::get('/create', [PreAssembledPackageController::class, 'create'])->name('create');
        Route::post('/', [PreAssembledPackageController::class, 'store'])->name('store');
        Route::get('/{preAssembled}', [PreAssembledPackageController::class, 'show'])->name('show');
        Route::get('/{preAssembled}/edit', [PreAssembledPackageController::class, 'edit'])->name('edit');
        Route::put('/{preAssembled}', [PreAssembledPackageController::class, 'update'])->name('update');
        Route::delete('/{preAssembled}', [PreAssembledPackageController::class, 'destroy'])->name('destroy');

        // Gestión de contenido del paquete
        Route::post('/{preAssembled}/add-product', [PreAssembledPackageController::class, 'addProduct'])->name('add-product');
        Route::post('/{preAssembled}/remove-product', [PreAssembledPackageController::class, 'removeProduct'])->name('remove-product');
        Route::post('/{preAssembled}/bulk-scan', [PreAssembledPackageController::class, 'bulkScan'])->name('bulk-scan');
        
        // Cambiar estado
        Route::post('/{preAssembled}/update-status', [PreAssembledPackageController::class, 'updateStatus'])->name('update-status');
    });

    // ====================================================================
    // MÓDULO 4: SCHEDULED SURGERIES (Cirugías Programadas)
    // ====================================================================
    Route::prefix('surgeries')->name('surgeries.')->group(function () {
        
        // CRUD básico
        Route::get('/', [ScheduledSurgeryController::class, 'index'])->name('index');
        Route::get('/create', [ScheduledSurgeryController::class, 'create'])->name('create');
        Route::post('/', [ScheduledSurgeryController::class, 'store'])->name('store');
        Route::get('/{surgery}', [ScheduledSurgeryController::class, 'show'])->name('show');
        Route::get('/{surgery}/edit', [ScheduledSurgeryController::class, 'edit'])->name('edit');
        Route::put('/{surgery}', [ScheduledSurgeryController::class, 'update'])->name('update');
        
        // Cancelar cirugía
        Route::post('/{surgery}/cancel', [ScheduledSurgeryController::class, 'cancel'])->name('cancel');
        
        // Ver check list aplicado
        Route::get('/{surgery}/checklist', [ScheduledSurgeryController::class, 'viewChecklist'])->name('checklist');

        // ---- PREPARACIONES (FLUJO COMPLETO) ----
        Route::prefix('{surgery}/preparations')->name('preparations.')->group(function () {
            
            // 1. Iniciar preparación
            Route::post('/start', [SurgeryPreparationController::class, 'start'])->name('start');
            
            // 2. Seleccionar paquete pre-armado
            Route::get('/select-package', [SurgeryPreparationController::class, 'selectPackage'])->name('selectPackage');
            Route::post('/assign-package', [SurgeryPreparationController::class, 'assignPackage'])->name('assignPackage');
            
            // 3. Ver comparación (Check List vs Pre-Armado)
            Route::get('/compare', [SurgeryPreparationController::class, 'compare'])->name('compare');
            
            // 4. Surtir faltantes (Picking)
            Route::get('/picking', [SurgeryPreparationController::class, 'picking'])->name('picking');

            //MODO MANUAL
            Route::post('/scan-barcode', [SurgeryPreparationController::class, 'scanBarcode'])->name('scanBarcode');

            //MODO RFID Y CONFIRMACION
            Route::get('/search-epc', [SurgeryPreparationController::class, 'searchByEPC'])->name('searchEPC');
            
            //MODO RFID CONFIRMAR Y AGREGAR
            Route::post('/confirm-rfid', [SurgeryPreparationController::class, 'confirmRFID'])->name('confirmRFID');

            //DEPRECADO MANTENER POR COMPATIBILIDAD
            Route::post('scan', [SurgeryPreparationController::class, 'scanProduct'])->name('scan');

            Route::post('/add-picked-product', [SurgeryPreparationController::class, 'addPickedProduct'])->name('add-picked-product');
            
            // 5. Verificar y completar
            Route::post('verify', [SurgeryPreparationController::class, 'verify'])->name('verify');
            Route::post('cancel', [SurgeryPreparationController::class, 'cancel'])->name('cancel');
            
            // 6. Resumen
            Route::get('summary', [SurgeryPreparationController::class, 'summary'])->name('summary');
            Route::get('status', [SurgeryPreparationController::class, 'status'])->name('status');
            Route::get('items', [SurgeryPreparationController::class, 'items'])->name('items');
        });
    });

    

    // ====================================================================
    // MÓDULO 5: INVOICES (Remisiones)
    // ====================================================================
    Route::prefix('invoices')->name('invoices.')->group(function () {
        
        // Listado
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        
        // Crear desde cirugía
        Route::get('/create-from-surgery/{surgery}', [InvoiceController::class, 'createFromSurgery'])->name('createFromSurgery');
        Route::post('/store-from-surgery/{surgery}', [InvoiceController::class, 'store'])->name('store');
        
        // Ver remisión
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        
        // Acciones sobre remisión
        Route::post('/{invoice}/issue', [InvoiceController::class, 'issue'])->name('issue');
        Route::post('/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('cancel');
        Route::post('/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid'])->name('markAsPaid');
        
        // Generar PDF
        Route::get('/{invoice}/pdf', [InvoiceController::class, 'generatePdf'])->name('pdf');
        Route::get('/{invoice}/preview-pdf', [InvoiceController::class, 'previewPdf'])->name('previewPdf');
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

    });

      Route::get('/dashboard', function () {
        // Datos del dashboard
        $availablePackages = \App\Models\PreAssembledPackage::available()->count();
        $activeChecklists = \App\Models\SurgicalChecklist::active()->count();
        $pendingInvoices = \App\Models\Invoice::where('status', 'draft')->count();

        return view('dashboard', compact(
            'availablePackages',
            'activeChecklists',
            'pendingInvoices'
        ));
    })->name('dashboard');

    // ========================================
    // Tipos de Productos
    // ========================================
    Route::resource('product_types', \App\Http\Controllers\ProductTypeController::class);


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

    Route::post('products/import/confirm', [ProductImportController::class, 'confirmImport'])
    ->name('products.import.confirm');
        
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

    // Descargar template CSV básico
    Route::get('/bulk-import/template', [PurchaseOrderBulkImportController::class, 'downloadTemplate'])
        ->name('purchase-orders.bulk-import.template');
    
    // Descargar template CSV con catálogo de productos
    Route::get('/bulk-import/template-catalog', [PurchaseOrderBulkImportController::class, 'downloadTemplateWithCatalog'])
        ->name('purchase-orders.bulk-import.template-catalog');
    
    // Procesar archivo CSV (AJAX)
    Route::post('/bulk-import/process', [PurchaseOrderBulkImportController::class, 'import'])
        ->name('purchase-orders.bulk-import.process');
    
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
    // HOSPITALES ruta tipo recurso
    // ========================================
    Route::prefix('hospitals')->group(function () {
       Route::resource('hospitals', HospitalController::class);
    });

   

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