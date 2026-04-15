<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Concern;
use Illuminate\Http\Request;

class ConcernController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->validate([
            'category' => ['nullable','string','max:100'],
            'subject' => ['required','string','max:200'],
            'message' => ['required','string','max:5000'],
            'attachment' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('concerns', 'public');
        }

        $c = Concern::create([
            'user_id' => $request->user()->id,
            'category' => $data['category'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => 'open',
            'attachment_path' => $attachmentPath,
        ]);

        $c->attachment_url = $c->attachment_path ? asset('storage/'.$c->attachment_path) : null;
        return response()->json($c, 201);
    }

    public function mine(Request $request)
    {
        $items = Concern::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(10);
        $items->getCollection()->transform(function ($c) {
            $c->attachment_url = $c->attachment_path ? asset('storage/'.$c->attachment_path) : null;
            return $c;
        });
        return response()->json($items);
    }
}
