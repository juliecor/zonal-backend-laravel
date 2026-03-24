<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZonalValue;
use Illuminate\Http\Request;

class ZonalValueController extends Controller
{
    public function index(Request $request)
    {
        $q = ZonalValue::query();

        // ✅ OPTIMIZATION: Use exact match instead of TRIM + whereRaw (allows index usage)
        if ($p = trim($request->string('province')->toString())) {
            $q->where('province', $p);
        }
        if ($c = trim($request->string('city')->toString())) {
            $q->where('city_municipality', $c);
        }
        if ($b = trim($request->string('barangay')->toString())) {
            $q->where('barangay', $b);
        }
        if ($cls = trim($request->string('classification_code')->toString())) {
            $q->where('classification_code', $cls);
        }
        
        // Text search with proper index usage
        if ($search = trim($request->string('q')->toString())) {
            $q->where(function($w) use ($search) {
                $w->where('street_location', 'LIKE', "%{$search}%")
                  ->orWhere('vicinity', 'LIKE', "%{$search}%");
            });
        }

        // ✅ PERFORMANCE: Ensure proper pagination
        $perPage = min(max((int) $request->integer('per_page', 16), 1), 100);
        $rows = $q->orderBy('city_municipality')
                  ->orderBy('barangay')
                  ->orderBy('street_location')  // stable sort order
                  ->paginate($perPage);

        // ✅ Add caching headers
        return response()->json($rows)
            ->header('Cache-Control', 'public, max-age=20, stale-while-revalidate=60');
    }
}
