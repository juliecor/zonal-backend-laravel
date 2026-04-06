<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;

class ReportController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'street' => 'nullable|string',
            'barangay' => 'nullable|string',
            'city' => 'nullable|string',
            'province' => 'nullable|string',
            'zonal_value' => 'nullable|string',
            'sqm' => 'nullable|numeric',
            'meta' => 'nullable|array',
        ]);
        $data['user_id'] = $user->id;
        $r = Report::create($data);
        return response()->json(['ok' => true, 'data' => $r]);
    }

    public function mine(Request $request)
    {
        $user = $request->user();
        $q = Report::where('user_id', $user->id);

        if ($request->filled('city')) {
            $q->where('city', 'like', '%' . $request->query('city') . '%');
        }
        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->query('to'));
        }

        $perPage = min(max((int)$request->query('per_page', 10), 1), 100);
        $items = $q->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'ok' => true,
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    // Admin: list all users' reports, grouped/sorted by user then date desc
    public function adminIndex(Request $request)
    {
        $q = Report::with(['user:id,name,email']);
        if ($request->filled('q')) {
            $term = $request->query('q');
            $q->whereHas('user', function ($qq) use ($term) {
                $qq->where('name', 'like', "%$term%")->orWhere('email', 'like', "%$term%");
            });
        }
        if ($request->filled('city')) {
            $q->where('city', 'like', '%'.$request->query('city').'%');
        }
        $perPage = min(max((int)$request->query('per_page', 20), 1), 100);
        // Sort primarily by user then created desc
        $q->orderBy('user_id')->orderByDesc('created_at');
        $items = $q->paginate($perPage);
        return response()->json([
            'ok' => true,
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }
}
