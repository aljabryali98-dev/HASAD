<?php

use App\Http\Controllers\AssociationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeneralDatabaseController;
use App\Http\Controllers\GeoExplorerController;
use App\Http\Controllers\GeographyController;
use App\Http\Controllers\ProductiveGroupController;
use App\Http\Controllers\UnifiedPersonnelController;
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

    // =========================================================================
    // 1. القسم الأول: الجمعيات التعاونية (Cooperative Associations Module)
    // =========================================================================
    Route::resource('associations', AssociationController::class);
    Route::post('associations/{association}/board-members', [AssociationController::class, 'storeBoardMember'])->name('associations.board_members.store');
    Route::post('associations/{association}/loans', [AssociationController::class, 'storeLoan'])->name('associations.loans.store');

    // =========================================================================
    // 2. القسم الثاني: المجاميع الإنتاجية (Productive Groups Module)
    // =========================================================================
    Route::resource('productive-groups', ProductiveGroupController::class);
    Route::post('productive-groups/{productive_group}/attach-facilitator', [ProductiveGroupController::class, 'attachFacilitator'])->name('productive-groups.attach_facilitator');

    // =========================================================================
    // 3. القسم الثالث - المحور (أ): الكوادر والفرسان والتدريب
    // =========================================================================
    Route::resource('personnel', UnifiedPersonnelController::class);

    // =========================================================================
    // 3. القسم الثالث - المحاور (ب، ج، د): قاعدة بيانات عامة
    // =========================================================================
    Route::prefix('general')->name('general.')->group(function () {
        // المشاريع والمبادرات
        Route::get('/projects', [GeneralDatabaseController::class, 'projects'])->name('projects.index');
        Route::post('/projects', [GeneralDatabaseController::class, 'storeProject'])->name('projects.store');
        Route::get('/awareness', [GeneralDatabaseController::class, 'awareness'])->name('awareness.index');
        Route::post('/awareness', [GeneralDatabaseController::class, 'storeAwareness'])->name('awareness.store');
        Route::get('/initiatives', [GeneralDatabaseController::class, 'initiatives'])->name('initiatives.index');
        Route::post('/initiatives', [GeneralDatabaseController::class, 'storeInitiative'])->name('initiatives.store');

        // الزراعة والمحاصيل
        Route::get('/crops', [GeneralDatabaseController::class, 'crops'])->name('crops.index');
        Route::post('/crops', [GeneralDatabaseController::class, 'storeCrop'])->name('crops.store');
        Route::get('/nurseries', [GeneralDatabaseController::class, 'nurseries'])->name('nurseries.index');
        Route::post('/nurseries', [GeneralDatabaseController::class, 'storeNursery'])->name('nurseries.store');
        Route::get('/farmers', [GeneralDatabaseController::class, 'farmers'])->name('farmers.index');
        Route::post('/farmers', [GeneralDatabaseController::class, 'storeFarmer'])->name('farmers.store');

        // القروض التمويلية البيضاء
        Route::get('/loans', [GeneralDatabaseController::class, 'loans'])->name('loans.index');
    });

    // =========================================================================
    // 4. مستكشف النطاق الجغرافي الشامل (360° Geo-Explorer)
    // =========================================================================
    Route::get('/geo-explorer', [GeoExplorerController::class, 'index'])->name('geo-explorer.index');

    // =========================================================================
    // 5. التقسيم الإداري والجغرافي (Geography & Administrative Divisions)
    // =========================================================================
    Route::prefix('geography')->name('geography.')->group(function () {
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
});
