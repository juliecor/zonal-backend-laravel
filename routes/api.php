<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ZonalValueController;
use App\Http\Controllers\Api\FacetController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\UserAdminController;
use App\Http\Controllers\Api\TokenRequestController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ConcernController;
use App\Http\Controllers\Api\Admin\ConcernAdminController;

// Auth endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require Bearer token via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
	Route::get('/me', [AuthController::class, 'me']);
	Route::post('/logout', [AuthController::class, 'logout']);

	// Profile (client & admin)
	Route::get('/profile', [ProfileController::class, 'show']);
	Route::put('/profile', [ProfileController::class, 'update']);
	Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
	Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar']);

	// Concerns (client)
	Route::post('/concerns', [ConcernController::class, 'create']);
	Route::get('/concerns/mine', [ConcernController::class, 'mine']);

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

		// Concerns (admin)
		Route::get('/concerns', [ConcernAdminController::class, 'index']);
		Route::post('/concerns/{concern}/resolve', [ConcernAdminController::class, 'resolve']);
	});

	// Client token-requests
	Route::post('/token-requests', [TokenRequestController::class, 'create']);
	Route::get('/token-requests/mine', [TokenRequestController::class, 'mine']);

	// Report logs (client)
	Route::post('/reports', [ReportController::class, 'create']);
	Route::get('/reports/mine', [ReportController::class, 'mine']);
});
