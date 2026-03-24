<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ZonalValueController;
use App\Http\Controllers\Api\FacetController;

Route::get('/zonal-values', [ZonalValueController::class, 'index']);

// Facet endpoints
Route::get('/facets/cities', [FacetController::class, 'cities']);
Route::get('/facets/barangays', [FacetController::class, 'barangays']);
Route::get('/facets/classifications', [FacetController::class, 'classifications']);
