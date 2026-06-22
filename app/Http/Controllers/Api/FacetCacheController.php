<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FacetCache;
use Illuminate\Http\Request;

class FacetCacheController extends Controller
{
    // GET /api/facet-cache?key=...  → the cached dropdown list, if present.
    public function show(Request $request)
    {
        $key = trim($request->query('key', ''));
        if ($key === '') {
            return response()->json(['found' => false]);
        }

        $row = FacetCache::where('cache_key', $key)->first();
        if (!$row) {
            return response()->json(['found' => false]);
        }

        $payload = json_decode($row->payload, true);
        if (!is_array($payload)) {
            $payload = [];
        }

        return response()->json([
            'found' => true,
            'payload' => array_values($payload),
            'age_seconds' => $row->refreshed_at ? now()->diffInSeconds($row->refreshed_at) : null,
            'refreshed_at' => optional($row->refreshed_at)->toIso8601String(),
        ]);
    }

    // POST /api/facet-cache  { key, payload:[...] }  → upsert the cached list.
    public function store(Request $request)
    {
        $key = trim($request->input('key', ''));
        $payload = $request->input('payload');

        if ($key === '' || $payload === null) {
            return response()->json(['ok' => false, 'error' => 'key, payload required'], 422);
        }

        // Accept either an array or an already-encoded JSON string.
        $json = is_string($payload) ? $payload : json_encode(array_values((array) $payload));

        FacetCache::updateOrCreate(
            ['cache_key' => $key],
            ['payload' => $json, 'refreshed_at' => now()]
        );

        return response()->json(['ok' => true]);
    }
}
