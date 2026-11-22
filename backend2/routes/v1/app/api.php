
<?php

use App\Http\Controllers\Api\General\AdminAreaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\General\StreetController;

Route::get('admin-areas/', [AdminAreaController::class, 'index'])
    ->name('admin_areas_geojson');

Route::get('admin-areas/lookup/', [AdminAreaController::class, 'lookup'])
    ->middleware('throttle:admin-area-lookup');

Route::get('/streets/geojson/', [StreetController::class, 'geoJson']);