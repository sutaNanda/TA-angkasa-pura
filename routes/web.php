<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\ChecklistController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Technician\DashboardController;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
*/
// Grouping route auth agar lebih rapi (Opsional tapi disarankan)
Route::controller(AuthController::class)->group(function() {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login')->name('login.post');
    Route::post('/logout', 'logout')->name('logout');
});

// Registrasi Publik (Karyawan)
Route::controller(App\Http\Controllers\Auth\RegisterController::class)->group(function() {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register')->name('register.post');
});

// Password Setup (Forced Reset)
Route::middleware(['auth'])->group(function() {
    Route::get('/password/setup', [App\Http\Controllers\Auth\PasswordSetupController::class, 'show'])->name('password.setup');
    Route::put('/password/setup', [App\Http\Controllers\Auth\PasswordSetupController::class, 'update'])->name('password.update');
});

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('user.tickets.index')->with('success', 'Email berhasil diverifikasi.');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');
    Route::post('/session/keep-alive', function () {
        return response()->json(['status' => 'success', 'message' => 'Session extended']);
    })->name('session.keep-alive');
});

// Redirect halaman awal berdasarkan role user
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'teknisi') {
            return redirect()->route('technician.dashboard');
        } elseif ($user->role === 'user') {
            return redirect()->route('user.tickets.index');
        }
    }
    return redirect()->route('login');
});

// ====================================================
// GROUP ROUTE ADMIN (KOORDINATOR & MANAJER)
// ====================================================
// Kita buat custom pengecekan role manual untuk dua role agar lebih simple tanpa mengubah `RoleMiddleware` secara besar-besaran
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,manajer', 'read_only_manager'])->group(function () {

    // 1. Dashboard
    // Route::get('/dashboard', function () {
    //     return view('admin.dashboard');
    // })->name('dashboard');
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

// 2. MASTER DATA: ASET
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        
        // Specific API Helper Routes (Must be above /{id} to avoid collision)
        Route::get('/by-location/{id}', [AssetController::class, 'getByLocation'])->name('by-location');
        Route::get('/hardware-by-location/{id}', [AssetController::class, 'getHardwareByLocation'])->name('hardware-by-location');
        Route::get('/by-category/{id}', [AssetController::class, 'getByCategory'])->name('by-category');
        Route::get('/by-categories', [AssetController::class, 'getByCategories'])->name('by-categories');

        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::get('/{id}', [AssetController::class, 'show'])->name('show');
        Route::put('/{id}', [AssetController::class, 'update'])->name('update');
        Route::delete('/{id}', [AssetController::class, 'destroy'])->name('destroy');
    });

    // 3. MASTER DATA: LOKASI (API TREE)
    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
    Route::prefix('api/locations')->name('locations.')->group(function() {
        Route::get('/tree', [LocationController::class, 'getTree'])->name('tree');
        Route::post('/', [LocationController::class, 'store'])->name('store');
        Route::put('/{id}', [LocationController::class, 'update'])->name('update');
        Route::delete('/{id}', [LocationController::class, 'destroy'])->name('destroy');
    });

    // 4. MASTER DATA: CHECKLISTS & KATEGORI
    Route::get('/checklists', [ChecklistController::class, 'index'])->name('checklists.index');
    Route::get('/checklists/{id}', [ChecklistController::class, 'show'])->name('checklists.show');
    Route::post('/checklists', [ChecklistController::class, 'store'])->name('checklists.store');
    Route::put('/checklists/{id}', [ChecklistController::class, 'update'])->name('checklists.update');
    Route::delete('/checklists/{id}', [ChecklistController::class, 'destroy'])->name('checklists.destroy');

    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{id}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    // 5. OPERASIONAL: RIWAYAT PATROLI
    Route::get('/maintenances', [App\Http\Controllers\Admin\MaintenanceController::class, 'index'])->name('maintenances.index');
    Route::get('/maintenances/{id}', [App\Http\Controllers\Admin\MaintenanceController::class, 'show'])->name('maintenances.show');
    
    // Task Management (Reschedule)
    Route::get('/maintenance-tasks', [App\Http\Controllers\Admin\MaintenanceController::class, 'tasks'])->name('maintenances.tasks');
    Route::post('/maintenance-tasks/{id}/reschedule', [App\Http\Controllers\Admin\MaintenanceController::class, 'reschedule'])->name('maintenances.reschedule');

    // Maintenance Plans (PM System - Rule-Based)
    Route::prefix('plans')->name('plans.')->group(function() {
        Route::get('/', [App\Http\Controllers\Admin\MaintenancePlanController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\MaintenancePlanController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\MaintenancePlanController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\MaintenancePlanController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\MaintenancePlanController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\MaintenancePlanController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle', [App\Http\Controllers\Admin\MaintenancePlanController::class, 'toggleActive'])->name('toggle');
        Route::post('/generate-now', [App\Http\Controllers\Admin\MaintenancePlanController::class, 'generateNow'])->name('generate-now');
    });

    // 6. MANAJEMEN TIKET (WORK ORDER)
    Route::get('/work-orders', [App\Http\Controllers\Admin\WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::post('/work-orders', [App\Http\Controllers\Admin\WorkOrderController::class, 'store'])->name('work-orders.store');
    Route::get('/work-orders/{id}', [App\Http\Controllers\Admin\WorkOrderController::class, 'show'])->name('work-orders.show');
    Route::put('/work-orders/{id}/assign', [App\Http\Controllers\Admin\WorkOrderController::class, 'assign'])->name('work-orders.assign');
    Route::post('/work-orders/verify-all', [App\Http\Controllers\Admin\WorkOrderController::class, 'verifyAll'])->name('work-orders.verify-all');
    Route::post('/work-orders/{id}/verify', [App\Http\Controllers\Admin\WorkOrderController::class, 'verify'])->name('work-orders.verify');
    Route::post('/work-orders/{id}/reopen', [App\Http\Controllers\Admin\WorkOrderController::class, 'reopen'])->name('work-orders.reopen');

    // 7. PENGATURAN & KEAMANAN
    Route::put('/profile/update', [App\Http\Controllers\Admin\UserController::class, 'updateProfile'])->name('profile.update');
    Route::resource('users', App\Http\Controllers\Admin\UserController::class)->except(['create', 'show', 'edit']);
    Route::get('/audit-logs', [App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit.index');

    // ===========================
    // 8. KHUSUS MANAJER: EXPORT & LAPORAN
    // ===========================
    Route::middleware(['role:manajer'])->group(function() {
        // Export Master Data
        Route::get('/export/locations-assets', [App\Http\Controllers\Admin\ExportController::class, 'exportAssetLocation'])->name('locations.export');
        
        // Export Operasional
        Route::get('/export/maintenances/{id}', [App\Http\Controllers\Admin\ExportController::class, 'exportMaintenance'])->name('maintenances.export');
        Route::get('/export/all-maintenances', [App\Http\Controllers\Admin\ExportController::class, 'exportMaintenances'])->name('export.maintenances');
        Route::get('/export/work-orders', [App\Http\Controllers\Admin\ExportController::class, 'exportWorkOrders'])->name('work-orders.export');
        
        // Export Manajemen & Audit
        Route::get('/export/technician-productivity', [App\Http\Controllers\Admin\UserReportController::class, 'exportProductivity'])->name('export.technician-productivity');
        Route::get('/export/audit-logs', [App\Http\Controllers\Admin\ExportController::class, 'exportAuditLogs'])->name('audit.export');

        // Modul Laporan (Reporting)
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/work-orders', [App\Http\Controllers\Admin\WorkOrderReportController::class, 'index'])->name('work-orders.index');
            Route::get('/work-orders/pdf', [App\Http\Controllers\Admin\WorkOrderReportController::class, 'printPdf'])->name('work-orders.pdf');
            
            Route::get('/assets', [App\Http\Controllers\Admin\AssetReportController::class, 'index'])->name('assets.index');
            Route::get('/assets/pdf', [App\Http\Controllers\Admin\AssetReportController::class, 'printPdf'])->name('assets.pdf');
            
            Route::get('/patrol-logs', [App\Http\Controllers\Admin\PatrolLogReportController::class, 'index'])->name('patrol-logs.index');
            Route::get('/patrol-logs/pdf', [App\Http\Controllers\Admin\PatrolLogReportController::class, 'printPdf'])->name('patrol-logs.pdf');
        });
    });

});


// ====================================================
// GROUP ROUTE TEKNISI (MOBILE WEB)
Route::prefix('technician')->name('technician.')->middleware(['auth', 'role:teknisi'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/history', [App\Http\Controllers\Technician\HistoryController::class, 'index'])->name('history.index');

    // Route::get('/dashboard', function () {
    //     return view('technician.dashboard');
    // })->name('dashboard');

    Route::put('/tasks/{id}/handover', [App\Http\Controllers\Technician\TaskController::class, 'handover'])->name('tasks.handover');
    Route::put('/tasks/{id}/claim', [App\Http\Controllers\Technician\TaskController::class, 'claim'])->name('tasks.claim');
    Route::put('/tasks/{id}/start', [App\Http\Controllers\Technician\TaskController::class, 'start'])->name('tasks.start'); // [NEW] Link Start
    Route::put('/tasks/{id}/complete', [App\Http\Controllers\Technician\TaskController::class, 'complete'])->name('tasks.complete');
    Route::get('/tasks', [App\Http\Controllers\Technician\TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{id}', [App\Http\Controllers\Technician\TaskController::class, 'show'])->name('tasks.show');
    // Route::get('/tasks', function () {
    //     return view('technician.tasks.index');
    // })->name('tasks.index');

    // Route::get('/history', function () {
    //     return view('technician.history.index');
    // })->name('history.index');

    Route::controller(App\Http\Controllers\Technician\ProfileController::class)->group(function() {
        Route::get('/profile', 'index')->name('profile.index');
        Route::put('/profile/update', 'update')->name('profile.update');
        Route::get('/profile/update', fn() => redirect()->route('technician.profile.index')); // Fallback anti-error
        Route::put('/profile/password', 'updatePassword')->name('profile.password');
    });
    // Route::get('/profile', function () {
    //     return view('technician.profile.index');
    // })->name('profile.index');

    // Fitur Scan & Patroli
    // Route::get('/scan', function () {
    //     return view('technician.scan');
    // })->name('scan');

    Route::get('/scan', [App\Http\Controllers\Technician\ScanController::class, 'index'])->name('scan'); // Buka Kamera
    Route::post('/scan/process', [App\Http\Controllers\Technician\ScanController::class, 'process'])->name('scan.process'); // Cek QR Database
    Route::get('/scan/location/{id}', [App\Http\Controllers\Technician\ScanController::class, 'show'])->name('scan.show'); // Tampil Aset

    // Data Referensi (Read-Only)
    Route::get('/locations/tree', [App\Http\Controllers\Technician\LocationController::class, 'getTree'])->name('locations.tree');
    Route::get('/assets/by-location/{id}', [App\Http\Controllers\Technician\AssetController::class, 'getByLocation'])->name('assets.by-location');
    
    Route::get('/assets', [App\Http\Controllers\Technician\AssetController::class, 'index'])->name('assets.index');
    Route::get('/assets/{id}', [App\Http\Controllers\Technician\AssetController::class, 'show'])->name('assets.show');
    Route::get('/locations', [App\Http\Controllers\Technician\LocationController::class, 'index'])->name('locations.index');
    Route::get('/locations/{id}', [App\Http\Controllers\Technician\LocationController::class, 'show'])->name('locations.show');
    Route::post('/locations/scan', [App\Http\Controllers\Technician\LocationController::class, 'scan'])->name('locations.scan');


    // Inspection/Checklist Routes
    Route::get('/inspection/{assetId}', [App\Http\Controllers\Technician\InspectionController::class, 'show'])->name('inspection.show');
    Route::post('/inspection', [App\Http\Controllers\Technician\InspectionController::class, 'store'])->name('inspection.store');

    // Unified Location Inspection
    Route::get('/location-inspection/{locationId}', [App\Http\Controllers\Technician\LocationInspectionController::class, 'inspect'])->name('locations.inspect');
    Route::post('/location-inspection/{locationId}', [App\Http\Controllers\Technician\LocationInspectionController::class, 'store'])->name('locations.inspect.store');

    // Unified Maintenance Inspection (Grouped by Location)
    Route::get('/maintenance-inspection/{maintenance}', [App\Http\Controllers\Technician\LocationInspectionController::class, 'inspectMaintenance'])->name('locations.maintenance.inspect');
    Route::post('/maintenance-inspection/{maintenance}', [App\Http\Controllers\Technician\LocationInspectionController::class, 'storeMaintenance'])->name('locations.maintenance.inspect.store');

    Route::get('/maintenance-group-inspection', [App\Http\Controllers\Technician\LocationInspectionController::class, 'inspectMaintenanceGroup'])->name('locations.maintenance.inspect_group');
    Route::post('/maintenance-group-inspection', [App\Http\Controllers\Technician\LocationInspectionController::class, 'storeMaintenanceGroup'])->name('locations.maintenance.inspect_group.store');

    // Preventive Maintenance Routes
    Route::get('/maintenance/{maintenanceId}/start', [App\Http\Controllers\Technician\MaintenanceController::class, 'start'])->name('maintenance.start');
    Route::post('/maintenance/{maintenanceId}/complete', [App\Http\Controllers\Technician\MaintenanceController::class, 'complete'])->name('maintenance.complete');

    Route::get('/maintenance/area', function () {
        return view('technician.maintenance.area_check');
    })->name('maintenance.area');

    // Work Order (LK) Creation Routes
    Route::get('/lk/create', [App\Http\Controllers\Technician\LkController::class, 'create'])->name('lk.create');
    Route::post('/lk', [App\Http\Controllers\Technician\LkController::class, 'store'])->name('lk.store');

});

// ====================================================
// GROUP ROUTE USER (PELAPOR / KARYAWAN)
// ====================================================
Route::prefix('user')->name('user.')->middleware(['auth', 'role:user', 'verified'])->group(function () {
    
    // Dashboard User = List Tiket Saya
    Route::get('/dashboard', [App\Http\Controllers\User\TicketController::class, 'index'])->name('tickets.index');
    
    // Manajemen Tiket (Create Only)
    Route::get('/tickets/create', [App\Http\Controllers\User\TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [App\Http\Controllers\User\TicketController::class, 'store'])->name('tickets.store');
    
    // API Helper untuk Cascading Dropdown
    Route::get('/api/locations/{parentId}', [App\Http\Controllers\User\TicketController::class, 'getLocations'])->name('api.locations');
    Route::get('/api/assets/{locationId}', [App\Http\Controllers\User\TicketController::class, 'getAssets'])->name('api.assets');

    // Profil User
    Route::controller(App\Http\Controllers\User\ProfileController::class)->group(function() {
        Route::get('/profile', 'index')->name('profile.index');
        Route::put('/profile/update', 'update')->name('profile.update');
        Route::get('/profile/update', fn() => redirect()->route('user.profile.index')); // Fallback anti-error
        Route::put('/profile/password', 'updatePassword')->name('profile.password');
    });

});
