<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GeoZonal;
use Illuminate\Http\Request;

class GeocodeCacheController extends Controller
{
    // Days a cached Google coordinate is considered fresh (Google TOS allows ~30).
    private const FRESH_DAYS = 30;

    // GET /api/geocode-cache?key=...  → returns the saved coordinate if present.
    public function show(Request $request)
    {
        $key = trim($request->query('key', ''));
        if ($key === '') {
            return response()->json(['found' => false]);
        }

        $row = GeoZonal::where('address_key', $key)->first();
        if (!$row) {
            return response()->json(['found' => false]);
        }

        $stale = !$row->geocoded_at || $row->geocoded_at->lt(now()->subDays(self::FRESH_DAYS));

        return response()->json([
            'found' => true,
            'stale' => $stale, // caller re-geocodes if true (keeps within Google's 30-day rule)
            'lat' => (float) $row->lat,
            'lon' => (float) $row->lon,
            'label' => $row->label,
            'value_per_sqm' => $row->value_per_sqm,
            'classification_code' => $row->classification_code,
        ]);
    }

    // POST /api/geocode-cache  → upsert a coordinate (and optional zonal value).
    public function store(Request $request)
    {
        $key = trim($request->input('key', ''));
        $lat = $request->input('lat');
        $lon = $request->input('lon');

        if ($key === '' || $lat === null || $lon === null) {
            return response()->json(['ok' => false, 'error' => 'key, lat, lon required'], 422);
        }

        GeoZonal::updateOrCreate(
            ['address_key' => $key],
            [
                'lat' => $lat,
                'lon' => $lon,
                'label' => $request->input('label'),
                'value_per_sqm' => $request->input('value_per_sqm'),
                'classification_code' => $request->input('classification_code'),
                'province' => $request->input('province'),
                'city_municipality' => $request->input('city'),
                'barangay' => $request->input('barangay'),
                'street_location' => $request->input('street'),
                'source' => $request->input('source', 'google'),
                'geocoded_at' => now(),
            ]
        );

        return response()->json(['ok' => true]);
    }

    // GET /api/geocode-in-bounds?minLat=&maxLat=&minLon=&maxLon=&limit=
    // → cached zonal points (with a value) inside the visible map box. Capped so
    // the frontend only ever loads what the user can see.
    public function inBounds(Request $request)
    {
        $minLat = (float) $request->query('minLat');
        $maxLat = (float) $request->query('maxLat');
        $minLon = (float) $request->query('minLon');
        $maxLon = (float) $request->query('maxLon');
        $limit = max(1, min((int) $request->query('limit', 300), 500));

        if (!$minLat || !$maxLat || !$minLon || !$maxLon) {
            return response()->json(['ok' => false, 'error' => 'bounds required'], 422);
        }

        $rows = GeoZonal::query()
            ->whereNotNull('value_per_sqm')
            ->whereBetween('lat', [$minLat, $maxLat])
            ->whereBetween('lon', [$minLon, $maxLon])
            ->limit($limit)
            ->get([
                'lat', 'lon', 'value_per_sqm', 'classification_code',
                'street_location', 'barangay', 'city_municipality', 'province',
            ]);

        return response()->json([
            'ok' => true,
            'count' => $rows->count(),
            'points' => $rows->map(fn ($x) => [
                'lat' => (float) $x->lat,
                'lon' => (float) $x->lon,
                'value_per_sqm' => $x->value_per_sqm,
                'classification_code' => $x->classification_code,
                'street' => $x->street_location,
                'barangay' => $x->barangay,
                'city' => $x->city_municipality,
                'province' => $x->province,
            ])->values(),
        ]);
    }

    // GET /api/geocode-nearest?lat=&lon=&radius=  → nearest cached zonal point(s)
    // with a value. "Scan a coordinate → get the zonal value registered to it."
    public function nearest(Request $request)
    {
        $lat = (float) $request->query('lat');
        $lon = (float) $request->query('lon');
        $radius = (float) $request->query('radius', 2000); // meters
        $radius = max(50, min($radius, 50000));

        if (!$lat || !$lon) {
            return response()->json(['found' => false, 'error' => 'lat, lon required'], 422);
        }

        // Bounding box first (uses the lat/lon index), then exact Haversine sort.
        $dLat = $radius / 111000.0;
        $dLon = $radius / (111000.0 * max(cos(deg2rad($lat)), 0.01));

        $rows = GeoZonal::query()
            ->whereNotNull('value_per_sqm')
            ->whereBetween('lat', [$lat - $dLat, $lat + $dLat])
            ->whereBetween('lon', [$lon - $dLon, $lon + $dLon])
            ->selectRaw(
                '*, (6371000 * 2 * ASIN(SQRT(POWER(SIN(RADIANS(? - lat)/2),2) + '
                . 'COS(RADIANS(?))*COS(RADIANS(lat))*POWER(SIN(RADIANS(? - lon)/2),2)))) AS distance_m',
                [$lat, $lat, $lon]
            )
            ->orderBy('distance_m')
            ->limit(25)
            ->get();

        if ($rows->isEmpty()) {
            return response()->json(['found' => false]);
        }

        $n = $rows->first();
        return response()->json([
            'found' => true,
            'distance_m' => round($n->distance_m),
            'lat' => (float) $n->lat,
            'lon' => (float) $n->lon,
            'value_per_sqm' => $n->value_per_sqm,
            'classification_code' => $n->classification_code,
            'street' => $n->street_location,
            'barangay' => $n->barangay,
            'city' => $n->city_municipality,
            'province' => $n->province,
            'label' => $n->label,
            // All nearby points (incl. the nearest) with coords — for plotting on the map.
            'points' => $rows->map(fn ($x) => [
                'lat' => (float) $x->lat,
                'lon' => (float) $x->lon,
                'value_per_sqm' => $x->value_per_sqm,
                'classification_code' => $x->classification_code,
                'street' => $x->street_location,
                'distance_m' => round($x->distance_m),
            ])->values(),
            'others' => $rows->slice(1)->map(fn ($x) => [
                'value_per_sqm' => $x->value_per_sqm,
                'classification_code' => $x->classification_code,
                'street' => $x->street_location,
                'distance_m' => round($x->distance_m),
            ])->values(),
        ]);
    }
}
