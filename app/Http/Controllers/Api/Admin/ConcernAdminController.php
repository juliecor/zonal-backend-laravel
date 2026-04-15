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
            $c->resolution_url = $c->resolution_path ? asset('storage/'.$c->resolution_path) : null;
            return $c;
        });
        return response()->json($items);
    }

    public function resolve(Request $request, Concern $concern)
    {
        $data = $request->validate([
            'resolution' => ['required','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'note' => ['nullable','string','max:500'],
        ]);

        if ($request->hasFile('resolution')) {
            $path = $request->file('resolution')->store('concerns/resolutions', 'public');
            $concern->resolution_path = $path;
        }
        if (!empty($data['note'])) {
            $concern->resolution_note = $data['note'];
        }
        $concern->status = 'resolved';
        $concern->save();
        $concern->resolution_url = $concern->resolution_path ? asset('storage/'.$concern->resolution_path) : null;
        return response()->json(['ok' => true, 'concern' => $concern]);
    }
}
