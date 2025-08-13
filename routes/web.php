<!-- web.php -->
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SAPLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\WorkcenterController;
use App\Http\Controllers\ManufactController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ProductionMappingController;

// Halaman utama
Route::get('/', fn () => view('auth.login'));

// SAP Login Routes
Route::middleware('web')->group(function () {
    Route::get('/sap-login', [SAPLoginController::class, 'showLoginForm'])->name('sap.login');
    Route::post('/sap-login', [SAPLoginController::class, 'login'])->name('sap.login.submit');
    Route::post('/sap-logout', [SAPLoginController::class, 'logout'])->name('sap.logout');
});

// Routes setelah login dan verifikasi
Route::middleware(['auth', 'verified'])->group(function () {

    // ================= Dashboard & Report =================
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::get('/report', fn () => view('report'))->name('report');
    Route::get('/report-pp', function () {
        $user = Auth::user();
        if ($user && $user->role === 'ceo') {
            return redirect()->route('approval-po');
        }
        return view('report-pp');
    })->name('report-pp');

    // ================= Workcenter =================
    Route::middleware(['sap.auth'])->group(function () {
        Route::get('/workcenter', [WorkcenterController::class, 'index'])->name('workcenter');
        Route::post('/workcenter/form/store', [WorkcenterController::class, 'store'])->name('workcenter.form.store');
        Route::post('/workcenter/submit-data', [WorkcenterController::class, 'submitData'])->name('workcenter.submitData');
        Route::get('/workcenter/sap-data', [WorkcenterController::class, 'fetchFromSAP'])->name('workcenter.fetch');
        Route::get('/workcenter/result_detail', [WorkcenterController::class, 'resultDetail'])->name('workcenter.result_detail');

        Route::post('/sap/save-edit', [WorkcenterController::class, 'saveEdit'])->name('sap.save_edit');
        Route::post('/edit/save-massal', [WorkcenterController::class, 'saveEditMassal'])->name('edit.save.massal');
        Route::post('/sap/save-edit-pv', [WorkcenterController::class, 'saveEditPv'])->name('sap.save_edit_pv');

        Route::get('/release-order/{aufnr}', [WorkcenterController::class, 'releaseOrderDirect'])->name('release.order.direct');
        Route::get('/sap/convert-direct', [WorkcenterController::class, 'convertPlannedOrder'])->name('convert.order.direct');
        Route::post('/convert/order/massal', [WorkcenterController::class, 'convertMassalPlannedOrders'])->name('convert.order.massal');
    });

    // ================= Manufacturing =================
    Route::middleware(['sap.auth'])->group(function () {
        Route::get('/manufact/{plant}', [ManufactController::class, 'show'])->name('manufact.show');
        Route::get('/manufact/{plant}/{category}', [ManufactController::class, 'detail'])->name('manufact.detail');
        Route::post('/manufact/sync/{code}', [ManufactController::class, 'syncFromSAP'])->name('manufact.sync');
        Route::get('/manufact/data2/detail/{code}', [ManufactController::class, 'showDetail'])->name('manufact.data2.detail');
        Route::post('/production-mapping', [ProductionMappingController::class, 'store'])->name('production.store');
        
        // Component Management Routes
        Route::post('/manufact/add-component', [ManufactController::class, 'addComponent'])->name('manufact.add.component');
        Route::post('/manufact/delete-component', [ManufactController::class, 'deleteComponent'])->name('manufact.delete.component');
        
        // NEW: AJAX route for getting updated T_DATA4
        Route::get('/manufact/tdata4/{aufnr}', [ManufactController::class, 'getTData4'])->name('manufact.get.tdata4');
    });
    
    Route::get('/manufact/data3/{kdauf}/{kdpos}', [ManufactController::class, 'getTData3']);
    Route::post('/reschedule', [ManufactController::class, 'rescheduleOrder'])->name('sap.reschedule');

    // ================= Purchase Order =================
    Route::middleware(['sap.auth'])->group(function () {
        Route::get('/purchase-order', [PurchaseOrderController::class, 'index'])->name('purchase-order');
        Route::get('/purchase-order/fetch', [PurchaseOrderController::class, 'fetchBothPlants'])->name('purchase-order.fetch');
        Route::get('/menu-po/{lokasi}', [PurchaseOrderController::class, 'menuPo'])->name('po.menu');
        Route::get('/po/menu/{lokasi}/{kategori}', [PurchaseOrderController::class, 'menuSubKategori'])->name('po.subkategori');
        Route::get('/result-po', [PurchaseOrderController::class, 'show'])->name('po.result');
        Route::get('/result-po/{lokasi}', [PurchaseOrderController::class, 'fetchPO'])->name('po.result.lokasi');
        Route::get('/po/detail/{ebeln}', [PurchaseOrderController::class, 'getDetailByEbeln'])->name('po.detail');
        Route::get('/po-text/{ebeln}', [PurchaseOrderController::class, 'getTextByEbeln']);
        Route::post('/purchase-order/reject', [PurchaseOrderController::class, 'reject'])->name('purchase-order.reject');
        Route::post('/purchase-order/release', [PurchaseOrderController::class, 'release'])->name('purchase-order.release');

        Route::post('/receive-po-data', [PurchaseOrderController::class, 'receivePOData'])->name('purchase-order.receive-data');
        Route::post('/receive-po-data', [PurchaseOrderController::class, 'receivePOData']);
        
        // Route sementara untuk membersihkan data TEXT yang mengandung default message
        Route::get('/admin/clean-po-text', [PurchaseOrderController::class, 'cleanOldTextData'])
            ->name('admin.clean-po-text');
    });
    // ================= Menu =================
    Route::middleware(['sap.auth'])->group(function () {
        Route::get('/menu', [MenuController::class, 'index'])->name('menu');
        Route::get('/menu/{plant}', [MenuController::class, 'show'])->name('menu.show');
    });

    // ================= Profile =================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Laravel Breeze/Fortify Routes
require __DIR__.'/auth.php';