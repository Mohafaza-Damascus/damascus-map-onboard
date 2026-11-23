
<?php

use App\Http\Controllers\Api\General\AdminAreaController;
use App\Http\Controllers\Api\General\StreetController;
use Illuminate\Support\Facades\Route;

Route::get('admin-areas/', [AdminAreaController::class, 'index'])
    ->name('admin_areas_geojson');

Route::get('admin-areas/lookup/', [AdminAreaController::class, 'lookup'])
    ->middleware('throttle:admin-area-lookup');

Route::get('streets/geojson', [StreetController::class, 'geojson'])
    ->name('streets_geojson');
