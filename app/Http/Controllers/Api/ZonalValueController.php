<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZonalValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ZonalValueController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // ✅ Validate BEFORE charging a token: a real lookup needs at least a province.
        // (Previously an empty request deducted a token AND ran a ~2.3s full-table filesort.)
        $p = trim($request->string('province')->toString());
        $c = trim($request->string('city')->toString());
        if ($p === '') {
            return response()->json(['message' => 'province is required'], 400);
        }

        // The mobile app's Zonal/Browse list sends "X-ZV-Free-List" — it's a plain DB read, so
        // it does NOT cost a token. Map scans (which incur Google geocoding, via the website's
        // scan-area) and the website itself never send this header, so they keep metering.
        $freeList = $request->hasHeader('X-ZV-Free-List');

        // Admins have unlimited access (no deduction or checks)
        if (!$freeList && $user->role !== 'admin') {
            if ((int)($user->token_balance ?? 0) <= 0) {
                return response()->json(['message' => 'Out of tokens'], 402);
            }
            // Deduct 1 token per zonal search (atomic)
            DB::table('users')->where('id', $user->id)->decrement('token_balance', 1);
        }

        $q = ZonalValue::query();

        // ✅ OPTIMIZATION: Use exact match instead of TRIM + whereRaw (allows index usage)
        $q->where('province', $p);
        if ($c !== '') {
            $q->where('city_municipality', $c);
        }
        if ($b = trim($request->string('barangay')->toString())) {
            $q->where('barangay', $b);
        }
        if ($cls = trim($request->string('classification_code')->toString())) {
            $q->where('classification_code', $cls);
        }
        
        // Text search — also match barangay so a bare barangay name (e.g. "Sambag II")
        // typed without a city still resolves.
        if ($search = trim($request->string('q')->toString())) {
            $q->where(function($w) use ($search) {
                $w->where('street_location', 'LIKE', "%{$search}%")
                  ->orWhere('vicinity', 'LIKE', "%{$search}%")
                  ->orWhere('barangay', 'LIKE', "%{$search}%");
            });
        }

        // Optional value sort (powers "most expensive / cheapest" answers accurately).
        $sort = trim($request->string('sort')->toString());
        if ($sort === 'value_desc') {
            $q->orderByDesc('value_per_sqm');
        } elseif ($sort === 'value_asc') {
            $q->orderBy('value_per_sqm');
        } else {
            $q->orderBy('city_municipality')->orderBy('barangay')->orderBy('street_location'); // stable default
        }

        // ✅ PERFORMANCE: Ensure proper pagination
        $perPage = min(max((int) $request->integer('per_page', 16), 1), 100);
        $rows = $q->paginate($perPage);

        // ✅ Add caching headers
        return response()->json($rows)
            ->header('Cache-Control', 'public, max-age=20, stale-while-revalidate=60');
    }
}
