<?php

use App\Http\Controllers\AssociationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeneralDatabaseController;
use App\Http\Controllers\GeoExplorerController;
use App\Http\Controllers\GeographyController;
use App\Http\Controllers\ProductiveGroupController;
use App\Http\Controllers\SmartImportController;
use App\Http\Controllers\UnifiedPersonnelController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// نقطة تهيئة قاعدة البيانات السحابية وإنشاء حساب مدير النظام
Route::get('/deploy/migrate', function (Request $request) {
    try {
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = Artisan::output();

        User::updateOrCreate(
            ['email' => 'admin@hasad.local'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '777000000',
                'is_active' => true,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم إنشاء الجداول وتجهيز حساب مدير النظام بنجاح!',
            'credentials' => [
                'email' => 'admin@hasad.local',
                'password' => 'password',
            ],
            'login_url' => url('/login'),
            'migrate_output' => $migrateOutput,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// نقطة سريعة لإنشاء أو إعادة ضبط حساب مدير النظام فوراً
Route::get('/deploy/admin', function () {
    try {
        $user = User::updateOrCreate(
            ['email' => 'admin@hasad.local'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '777000000',
                'is_active' => true,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم إنشاء حساب مدير النظام وتفعيله بنجاح!',
            'credentials' => [
                'email' => 'admin@hasad.local',
                'password' => 'password',
            ],
            'login_url' => url('/login'),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// بوابة تثبيت تطبيق حصاد (PWA Universal Install Portal)
Route::view('/app/install', 'pwa.install')->name('pwa.install');

// مسارات المصادقة للضيوف (Guest Authentication Routes)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// المسارات المحمية بصلاحيات الدخول (Protected Routes)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // مساعد حصاد الذكي المدمج (Hasad AI Copilot API)
    Route::post('/api/copilot/query', [\App\Http\Controllers\Api\CopilotApiController::class, 'query'])->name('api.copilot.query');

    // =========================================================================
    // 1. القسم الأول: الجمعيات التعاونية (Cooperative Associations Module)
    // =========================================================================
    Route::middleware('permission:associations.create')->group(function () {
        Route::get('associations/create', [AssociationController::class, 'create'])->name('associations.create');
        Route::post('associations', [AssociationController::class, 'store'])->name('associations.store');
    });

    Route::middleware('permission:associations.view')->group(function () {
        Route::get('associations', [AssociationController::class, 'index'])->name('associations.index');
        Route::get('associations/{association}', [AssociationController::class, 'show'])->name('associations.show')->whereNumber('association');
        Route::get('associations/{association}/print', [AssociationController::class, 'printDossier'])->name('associations.print')->whereNumber('association');
        Route::get('associations/{association}/shareholders/{shareholder}/receipt', [AssociationController::class, 'printShareholderReceipt'])->name('associations.shareholders.receipt')->whereNumber('association');
    });

    Route::middleware('permission:associations.edit')->group(function () {
        Route::get('associations/{association}/edit', [AssociationController::class, 'edit'])->name('associations.edit')->whereNumber('association');
        Route::put('associations/{association}', [AssociationController::class, 'update'])->name('associations.update')->whereNumber('association');
        Route::patch('associations/{association}', [AssociationController::class, 'update'])->whereNumber('association');
        Route::post('associations/{association}/board-members', [AssociationController::class, 'storeBoardMember'])->name('associations.board_members.store')->whereNumber('association');
        Route::post('associations/{association}/loans', [AssociationController::class, 'storeLoan'])->name('associations.loans.store')->whereNumber('association');
        Route::post('associations/{association}/shareholders', [AssociationController::class, 'storeShareholder'])->name('associations.shareholders.store')->whereNumber('association');
        Route::post('associations/{association}/personnel', [AssociationController::class, 'storePersonnel'])->name('associations.personnel.store')->whereNumber('association');
    });

    Route::middleware('permission:associations.delete')->group(function () {
        Route::delete('associations/{association}', [AssociationController::class, 'destroy'])->name('associations.destroy')->whereNumber('association');
    });

    // =========================================================================
    // 2. القسم الثاني: المجاميع الإنتاجية (Productive Groups Module)
    // =========================================================================
    Route::middleware('permission:productive_groups.create')->group(function () {
        Route::get('productive-groups/create', [ProductiveGroupController::class, 'create'])->name('productive-groups.create');
        Route::post('productive-groups', [ProductiveGroupController::class, 'store'])->name('productive-groups.store');
    });

    Route::middleware('permission:productive_groups.view')->group(function () {
        Route::get('productive-groups', [ProductiveGroupController::class, 'index'])->name('productive-groups.index');
        Route::get('productive-groups/{productive_group}', [ProductiveGroupController::class, 'show'])->name('productive-groups.show')->whereNumber('productive_group');
    });

    Route::middleware('permission:productive_groups.edit')->group(function () {
        Route::get('productive-groups/{productive_group}/edit', [ProductiveGroupController::class, 'edit'])->name('productive-groups.edit')->whereNumber('productive_group');
        Route::put('productive-groups/{productive_group}', [ProductiveGroupController::class, 'update'])->name('productive-groups.update')->whereNumber('productive_group');
        Route::patch('productive-groups/{productive_group}', [ProductiveGroupController::class, 'update'])->whereNumber('productive_group');
        Route::post('productive-groups/{productive_group}/attach-facilitator', [ProductiveGroupController::class, 'attachFacilitator'])->name('productive-groups.attach_facilitator')->whereNumber('productive_group');
    });

    Route::middleware('permission:productive_groups.delete')->group(function () {
        Route::delete('productive-groups/{productive_group}', [ProductiveGroupController::class, 'destroy'])->name('productive-groups.destroy')->whereNumber('productive_group');
    });

    // =========================================================================
    // 3. القسم الثالث - المحور (أ): الكوادر والفرسان والتدريب
    // =========================================================================
    Route::middleware('permission:personnel.create')->group(function () {
        Route::get('personnel/create', [UnifiedPersonnelController::class, 'create'])->name('personnel.create');
        Route::post('personnel', [UnifiedPersonnelController::class, 'store'])->name('personnel.store');
    });

    Route::middleware('permission:personnel.view')->group(function () {
        Route::get('personnel', [UnifiedPersonnelController::class, 'index'])->name('personnel.index');
        Route::get('personnel/{personnel}', [UnifiedPersonnelController::class, 'show'])->name('personnel.show')->whereNumber('personnel');
    });

    Route::middleware('permission:personnel.edit')->group(function () {
        Route::get('personnel/{personnel}/edit', [UnifiedPersonnelController::class, 'edit'])->name('personnel.edit')->whereNumber('personnel');
        Route::put('personnel/{personnel}', [UnifiedPersonnelController::class, 'update'])->name('personnel.update')->whereNumber('personnel');
        Route::patch('personnel/{personnel}', [UnifiedPersonnelController::class, 'update'])->whereNumber('personnel');
    });

    Route::middleware('permission:personnel.delete')->group(function () {
        Route::delete('personnel/{personnel}', [UnifiedPersonnelController::class, 'destroy'])->name('personnel.destroy')->whereNumber('personnel');
    });

    // =========================================================================
    // 3. القسم الثالث - المحاور (ب، ج، د): قاعدة بيانات عامة
    // =========================================================================
    Route::prefix('general')->name('general.')->group(function () {
        // المشاريع والمبادرات
        Route::get('/projects', [GeneralDatabaseController::class, 'projects'])->name('projects.index')->middleware('permission:projects.view');
        Route::post('/projects', [GeneralDatabaseController::class, 'storeProject'])->name('projects.store')->middleware('permission:projects.manage');
        Route::get('/awareness', [GeneralDatabaseController::class, 'awareness'])->name('awareness.index')->middleware('permission:projects.view');
        Route::post('/awareness', [GeneralDatabaseController::class, 'storeAwareness'])->name('awareness.store')->middleware('permission:projects.manage');
        Route::get('/initiatives', [GeneralDatabaseController::class, 'initiatives'])->name('initiatives.index')->middleware('permission:projects.view');
        Route::post('/initiatives', [GeneralDatabaseController::class, 'storeInitiative'])->name('initiatives.store')->middleware('permission:projects.manage');

        // الزراعة والمحاصيل
        Route::get('/crops', [GeneralDatabaseController::class, 'crops'])->name('crops.index')->middleware('permission:crops.view');
        Route::post('/crops', [GeneralDatabaseController::class, 'storeCrop'])->name('crops.store')->middleware('permission:crops.manage');
        Route::get('/nurseries', [GeneralDatabaseController::class, 'nurseries'])->name('nurseries.index')->middleware('permission:crops.view');
        Route::post('/nurseries', [GeneralDatabaseController::class, 'storeNursery'])->name('nurseries.store')->middleware('permission:crops.manage');
        Route::get('/farmers', [GeneralDatabaseController::class, 'farmers'])->name('farmers.index')->middleware('permission:crops.view');
        Route::post('/farmers', [GeneralDatabaseController::class, 'storeFarmer'])->name('farmers.store')->middleware('permission:crops.manage');

        // القروض التمويلية البيضاء
        Route::get('/loans', [GeneralDatabaseController::class, 'loans'])->name('loans.index')->middleware('permission:loans.view');
    });

    // =========================================================================
    // 4. مستكشف النطاق الجغرافي الشامل (360° Geo-Explorer)
    // =========================================================================
    Route::get('/geo-explorer/print', [GeoExplorerController::class, 'printDossier'])->name('geo-explorer.print')->middleware('permission:geography.view');
    Route::get('/geo-explorer', [GeoExplorerController::class, 'index'])->name('geo-explorer.index')->middleware('permission:geography.view');

    // =========================================================================
    // 5. التقسيم الإداري والجغرافي (Geography & Administrative Divisions)
    // =========================================================================
    Route::prefix('geography')->name('geography.')->middleware('permission:geography.manage')->group(function () {
        Route::get('/', [GeographyController::class, 'index'])->name('index');
        Route::get('/manage', [GeographyController::class, 'manage'])->name('manage');
        Route::post('/districts', [GeographyController::class, 'storeDistrict'])->name('districts.store');
        Route::post('/sub-districts', [GeographyController::class, 'storeSubDistrict'])->name('sub_districts.store');
        Route::post('/villages', [GeographyController::class, 'storeVillage'])->name('villages.store');
        Route::post('/sub-locations', [GeographyController::class, 'storeVillage'])->name('sub_locations.store');
        Route::put('/sub-locations/{id}', [GeographyController::class, 'updateSubLocation'])->name('sub_locations.update');
        Route::delete('/sub-locations/{id}', [GeographyController::class, 'destroySubLocation'])->name('sub_locations.destroy');
        Route::post('/sub-locations/bulk', [GeographyController::class, 'storeBulkSubLocations'])->name('sub_locations.bulk');
        Route::post('/import-excel', [GeographyController::class, 'importExcel'])->name('import_excel');
        Route::get('/download-template', [GeographyController::class, 'downloadTemplate'])->name('download_template');
    });

    // المسارات المتتالية للربط الديناميكي (Cascading AJAX Endpoints)
    Route::prefix('geo')->name('geo.')->group(function () {
        Route::get('/districts/{governorate}', [GeographyController::class, 'getDistricts'])->name('districts');
        Route::get('/sub-districts/{district}', [GeographyController::class, 'getSubDistricts'])->name('sub_districts');
        Route::get('/villages/{sub_district}', [GeographyController::class, 'getVillages'])->name('villages');
    });

    // =========================================================================
    // 6. مركز الاستيراد الذكي الشامل من ملفات الإكسل (Smart Excel Import Hub)
    // =========================================================================
    Route::prefix('import/smart')->name('import.smart')->group(function () {
        Route::get('/', [SmartImportController::class, 'index'])->middleware('permission:import.view');
        Route::post('/upload', [SmartImportController::class, 'upload'])->name('.upload')->middleware('permission:import.execute');
        Route::post('/master', [SmartImportController::class, 'runMaster'])->name('.master')->middleware('permission:import.execute');
    });

    // =========================================================================
    // 7. إدارة المستخدمين والصلاحيات (Users & Roles Management)
    // =========================================================================
    Route::middleware('permission:users.view')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
    });

    Route::middleware('permission:users.manage')->group(function () {
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });
});
