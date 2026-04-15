<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Concern;
use Illuminate\Http\Request;

class ConcernAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $q = Concern::with('user')->orderByDesc('created_at');
        if ($status) $q->where('status', $status);
        $items = $q->paginate(20);
        $items->getCollection()->transform(function ($c) {
            $c->attachment_url = $c->attachment_path ? asset('storage/'.$c->attachment_path) : null;
            return $c;
        });
        return response()->json($items);
    }

    public function resolve(Request $request, Concern $concern)
    {
        $concern->status = 'resolved';
        $concern->save();
        return response()->json(['ok' => true, 'concern' => $concern]);
    }
}
