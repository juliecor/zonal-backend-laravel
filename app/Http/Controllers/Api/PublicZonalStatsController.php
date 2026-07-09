<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZonalValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Public TEASER stats for the website's SEO pages (zonalvalue.ph/zonal-value/*).
 *
 * Follows the same principle as the public facets endpoints: aggregate numbers
 * and a handful of sample rows are public; the full street-by-street dataset
 * stays behind auth + token charging on /zonal-values. Heavily cached and
 * throttled — this endpoint can never be used to bulk-extract the data.
 */
class PublicZonalStatsController extends Controller
{
    private const CACHE_TTL = 60 * 60 * 12; // 12h
    private const MAX_ROWS = 30000;         // safety cap per city
    private const MIN_BRGY_RECORDS = 12;    // barangay must be meaty enough for a page
    private const MAX_BRGY = 60;

    public function show(Request $request)
    {
        $province = trim((string) $request->query('province', ''));
        $city = trim((string) $request->query('city', ''));
        $barangay = trim((string) $request->query('barangay', ''));
        if ($province === '' || $city === '') {
            return response()->json(['ok' => false, 'error' => 'province and city are required'], 400);
        }

        $key = 'pubstats:' . md5(mb_strtolower("$province|$city|$barangay"));
        $payload = Cache::remember($key, self::CACHE_TTL, function () use ($province, $city, $barangay) {
            return $this->build($province, $city, $barangay);
        });

        return response()->json($payload)->header('Cache-Control', 'public, max-age=3600');
    }

    private function build(string $province, string $city, string $barangay): array
    {
        // Exact match first (uses the place index); loose fallback stays inside
        // the province slice so it can't scan the whole table.
        $rows = ZonalValue::where('province', $province)
            ->where('city_municipality', $city)
            ->limit(self::MAX_ROWS)
            ->get(['barangay', 'street_location', 'vicinity', 'classification_code', 'value_per_sqm']);
        if ($rows->isEmpty()) {
            $rows = ZonalValue::where('province', $province)
                ->where('city_municipality', 'LIKE', $city . '%')
                ->limit(self::MAX_ROWS)
                ->get(['barangay', 'street_location', 'vicinity', 'classification_code', 'value_per_sqm']);
        }

        $items = [];
        foreach ($rows as $r) {
            $v = (float) $r->value_per_sqm;
            if ($v <= 0) continue;
            $items[] = [
                'v' => $v,
                'street' => trim((string) ($r->street_location ?: $r->vicinity ?: '')),
                'barangay' => trim((string) ($r->barangay ?? '')),
                'cls' => strtoupper(trim((string) ($r->classification_code ?? ''))),
            ];
        }

        // Barangay directory (always computed city-wide, before any filter).
        $groups = [];
        foreach ($items as $it) {
            $b = $it['barangay'];
            if ($b === '') continue;
            $groups[$b] = ($groups[$b] ?? 0) + 1;
        }
        arsort($groups);
        $barangayList = [];
        foreach ($groups as $name => $count) {
            if ($count < self::MIN_BRGY_RECORDS) continue;
            $barangayList[] = ['name' => $name, 'count' => $count];
            if (count($barangayList) >= self::MAX_BRGY) break;
        }

        if ($barangay !== '') {
            $needle = $this->norm($barangay);
            $items = array_values(array_filter($items, fn ($it) => $this->norm($it['barangay']) === $needle));
        }

        if (count($items) < 2) {
            return ['ok' => true, 'count' => count($items), 'stats' => null, 'barangay_list' => $barangayList];
        }

        usort($items, fn ($a, $b) => $a['v'] <=> $b['v']);
        $vals = array_column($items, 'v');
        $n = count($vals);
        $median = $n % 2 ? $vals[intdiv($n - 1, 2)] : ($vals[$n / 2 - 1] + $vals[$n / 2]) / 2;

        // Per-classification breakdown (top 6 by row count).
        $byClass = [];
        foreach ($items as $it) {
            $byClass[$it['cls'] ?: '—'][] = $it['v'];
        }
        uasort($byClass, fn ($a, $b) => count($b) <=> count($a));
        $classes = [];
        foreach (array_slice($byClass, 0, 6, true) as $code => $list) {
            sort($list);
            $m = count($list);
            $classes[] = [
                'code' => $code,
                'count' => $m,
                'min' => $list[0],
                'max' => $list[$m - 1],
                'median' => $m % 2 ? $list[intdiv($m - 1, 2)] : ($list[$m / 2 - 1] + $list[$m / 2]) / 2,
            ];
        }

        // Teaser samples only: cheapest, two middle picks, most expensive.
        $samples = [];
        $seen = [];
        foreach ([0, (int) floor($n * 0.35), (int) floor($n * 0.7), $n - 1] as $i) {
            $it = $items[max(0, min($n - 1, $i))];
            $k = $it['street'] . '|' . $it['barangay'] . '|' . $it['v'];
            if ($it['street'] !== '' && !isset($seen[$k])) {
                $seen[$k] = true;
                $samples[] = ['street' => $it['street'], 'barangay' => $it['barangay'], 'cls' => $it['cls'], 'value' => $it['v']];
            }
        }

        $uniqueBrgys = [];
        foreach ($items as $it) {
            if ($it['barangay'] !== '') $uniqueBrgys[$this->norm($it['barangay'])] = true;
        }

        return [
            'ok' => true,
            'count' => $n,
            'stats' => [
                'min' => $vals[0],
                'max' => $vals[$n - 1],
                'median' => $median,
                'barangays' => count($uniqueBrgys),
                'classes' => $classes,
                'samples' => $samples,
            ],
            'barangay_list' => $barangayList,
        ];
    }

    private function norm(string $s): string
    {
        $s = mb_strtoupper($s);
        $s = preg_replace('/\(.*?\)/', '', $s);
        $s = str_replace('Ñ', 'N', $s);
        $s = preg_replace('/\bBRGY\.?\b|\bBARANGAY\b/', '', $s);
        $s = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $s);
        return trim(preg_replace('/\s+/', ' ', $s));
    }
}
