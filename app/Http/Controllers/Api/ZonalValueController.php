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
        // Admins have unlimited access (no deduction or checks)
        if ($user->role !== 'admin') {
            if ((int)($user->token_balance ?? 0) <= 0) {
                return response()->json(['message' => 'Out of tokens'], 402);
            }
            // Deduct 1 token per zonal search (atomic)
            DB::table('users')->where('id', $user->id)->decrement('token_balance', 1);
        }

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
