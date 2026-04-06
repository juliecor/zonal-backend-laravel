<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ZonalValueController;
use App\Http\Controllers\Api\FacetController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\UserAdminController;
use App\Http\Controllers\Api\TokenRequestController;
use App\Http\Controllers\Api\ReportController;

// Auth endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require Bearer token via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
	Route::get('/me', [AuthController::class, 'me']);
	Route::post('/logout', [AuthController::class, 'logout']);

	Route::get('/zonal-values', [ZonalValueController::class, 'index']);

	// Facet endpoints
	Route::get('/facets/cities', [FacetController::class, 'cities']);
	Route::get('/facets/barangays', [FacetController::class, 'barangays']);
	Route::get('/facets/classifications', [FacetController::class, 'classifications']);
    
	// Admin-only endpoints
	Route::middleware('admin')->prefix('admin')->group(function () {
		Route::get('/users', [UserAdminController::class, 'index']);
		Route::post('/users/{user}/tokens', [UserAdminController::class, 'addTokens']);
		Route::get('/token-requests', [TokenRequestController::class, 'adminIndex']);
		Route::post('/token-requests/{tokenRequest}/approve', [TokenRequestController::class, 'approve']);
		Route::post('/token-requests/{tokenRequest}/deny', [TokenRequestController::class, 'deny']);
		Route::get('/reports', [ReportController::class, 'adminIndex']);
	});

	// Client token-requests
	Route::post('/token-requests', [TokenRequestController::class, 'create']);
	Route::get('/token-requests/mine', [TokenRequestController::class, 'mine']);

	// Report logs (client)
	Route::post('/reports', [ReportController::class, 'create']);
	Route::get('/reports/mine', [ReportController::class, 'mine']);
});
