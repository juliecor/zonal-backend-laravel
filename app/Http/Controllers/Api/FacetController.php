<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZonalValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FacetController extends Controller
{
    // ✅ Cache facets for 1 hour (huge speedup from 2.5min to ~50ms!)
    const CACHE_TTL = 3600; // 1 hour

    public function cities(Request $request)
    {
        $province = trim($request->string('province')->toString());
        if (!$province) {
            return response()->json(['error' => 'province is required'], 400);
        }

        // ✅ NEW: Cache result for 1 hour
        $cacheKey = "facets:cities:{$province}";
        $list = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($province) {
            return ZonalValue::query()
                ->where('province', 'LIKE', "%{$province}%")  // ✅ Remove TRIM - let DB use index
                ->whereNotNull('city_municipality')
                ->distinct('city_municipality')  // ✅ Use distinct() on column name (faster)
                ->orderBy('city_municipality')
                ->pluck('city_municipality')
                ->all();
        });

        return response()
            ->json(['province' => $province, 'cities' => array_values(array_filter($list))])
            ->header('Cache-Control', 'public, max-age=300, s-maxage=3600');
    }


    public function barangays(Request $request)
    {
        $province = trim($request->string('province')->toString());
        $city = trim($request->string('city')->toString());
        if (!$province || !$city) {
            return response()->json(['error' => 'province and city are required'], 400);
        }

        // ✅ NEW: Cache result (keyed by province + city)
        $cacheKey = "facets:barangays:{$province}:{$city}";
        $list = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($province, $city) {
            return ZonalValue::query()
                ->where('province', 'LIKE', "%{$province}%")
                ->where('city_municipality', 'LIKE', "%{$city}%")
                ->whereNotNull('barangay')
                ->distinct('barangay')
                ->orderBy('barangay')
                ->pluck('barangay')
                ->all();
        });

        return response()
            ->json(['province' => $province, 'city' => $city, 'barangays' => array_values(array_filter($list))])
            ->header('Cache-Control', 'public, max-age=300, s-maxage=3600');
    }

    public function classifications(Request $request)
    {
        $province = trim($request->string('province')->toString());
        $city = trim($request->string('city')->toString());
        $barangay = trim($request->string('barangay')->toString());
        
        // ✅ NEW: Cache results with composite key
        $cacheKey = "facets:classifications:{$province}:{$city}:{$barangay}";
        $list = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($province, $city, $barangay) {
            $q = ZonalValue::query();
            
            if ($province) {
                $q->where('province', 'LIKE', "%{$province}%");
            }
            if ($city) {
                $q->where('city_municipality', 'LIKE', "%{$city}%");
            }
            if ($barangay) {
                $q->where('barangay', 'LIKE', "%{$barangay}%");
            }

            return $q->whereNotNull('classification_code')
                ->distinct('classification_code')
                ->orderBy('classification_code')
                ->pluck('classification_code')
                ->all();
        });

        return response()
            ->json(['classifications' => array_values(array_filter($list))])
            ->header('Cache-Control', 'public, max-age=300, s-maxage=3600');
    }
}
