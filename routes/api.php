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
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\Api\GeocodeCacheController;
use App\Http\Controllers\Api\FacetCacheController;

// Geocode cache (public, no token deduction) — saves Google geocoding cost.
Route::get('/geocode-cache', [GeocodeCacheController::class, 'show']);
Route::post('/geocode-cache', [GeocodeCacheController::class, 'store']);

// Facet (dropdown list) cache (public) — durable cache of city/barangay lists so
// the slow first SpreadSimple fetch happens once globally, not per cold instance.
Route::get('/facet-cache', [FacetCacheController::class, 'show']);
Route::post('/facet-cache', [FacetCacheController::class, 'store']);
// Nearest cached zonal value to a coordinate ("scan a place → get its value").
Route::get('/geocode-nearest', [GeocodeCacheController::class, 'nearest']);
// Cached zonal points inside the visible map box (viewport loading).
Route::get('/geocode-in-bounds', [GeocodeCacheController::class, 'inBounds']);

// Facet name-lists (public, no token) — city/barangay/classification NAMES only, not
// values. Public so dropdowns + the AI assistant work without a login (the ₱ values
// stay behind auth via /zonal-values). Fixes Laguna/Cabuyao not showing when logged out.
Route::get('/facets/cities', [FacetController::class, 'cities']);
Route::get('/facets/barangays', [FacetController::class, 'barangays']);
Route::get('/facets/classifications', [FacetController::class, 'classifications']);
// Authoritative (province, city) index → lets the frontend route a clicked location to
// the province our DB actually stores it under (fixes post-split LGUs like Malita).
Route::get('/facets/city-province-index', [FacetController::class, 'cityProvinceIndex']);

// Auth endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/otp/verify', [OtpController::class, 'verify']);
Route::post('/otp/resend', [OtpController::class, 'resend']);
Route::post('/login/otp/request', [OtpController::class, 'requestLogin']);
Route::post('/login/otp/verify', [OtpController::class, 'verifyLogin']);

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

		// Invitations (admin)
		Route::post('/invitations', [\App\Http\Controllers\Api\Admin\InvitationAdminController::class, 'invite']);
	});

	// Client token-requests
	Route::post('/token-requests', [TokenRequestController::class, 'create']);
	Route::get('/token-requests/mine', [TokenRequestController::class, 'mine']);

	// Report logs (client)
	Route::post('/reports', [ReportController::class, 'create']);
	Route::get('/reports/mine', [ReportController::class, 'mine']);

	// Generic uploads
	Route::post('/upload', [UploadController::class, 'upload']);
});
