<?php

use App\Http\Controllers\Api\LocationApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('locations')->group(function () {
    Route::get('/hierarchy', [LocationApiController::class, 'hierarchy'])->name('api.locations.hierarchy');
    Route::post('/sub-locations', [LocationApiController::class, 'storeSubLocation'])->name('api.locations.sub-locations.store');
    Route::put('/sub-locations/{id}', [LocationApiController::class, 'updateSubLocation'])->name('api.locations.sub-locations.update');
    Route::delete('/sub-locations/{id}', [LocationApiController::class, 'destroySubLocation'])->name('api.locations.sub-locations.destroy');
    Route::get('/sub-locations/by-uzlah/{uzlahId}', [LocationApiController::class, 'getSubLocationsByUzlah'])->name('api.locations.sub-locations.by-uzlah');
});
