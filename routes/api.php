<?php

use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\LayerController;
use App\Http\Controllers\LayupController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::apiResource('suppliers', SupplierController::class);

Route::prefix('suppliers/{supplier}')->group(function () {
    Route::apiResource('layups', LayupController::class);

    Route::prefix('layups/{layup}')->group(function () {
        Route::apiResource('layers', LayerController::class);
    });

    Route::get('export', [ImportExportController::class, 'export']);
    Route::post('import', [ImportExportController::class, 'import']);
});
