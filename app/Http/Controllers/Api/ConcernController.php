<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Concern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            $concernPrefix = env('CONCERN_PREFIX', 'concerns');
            $attachmentPath = $request->file('attachment')->store($concernPrefix, 's3');
        }

        $c = Concern::create([
            'user_id' => $request->user()->id,
            'category' => $data['category'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => 'open',
            'attachment_path' => $attachmentPath,
        ]);

        $c->attachment_url = $c->attachment_path ? Storage::disk('s3')->url($c->attachment_path) : null;
        return response()->json($c, 201, [], JSON_UNESCAPED_SLASHES);
    }

    public function mine(Request $request)
    {
        $items = Concern::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(10);
        $items->getCollection()->transform(function ($c) {
            $c->attachment_url = $c->attachment_path ? Storage::disk('s3')->url($c->attachment_path) : null;
            $c->resolution_url = $c->resolution_path ? Storage::disk('s3')->url($c->resolution_path) : null;
            // include note for client visibility
            $c->resolution_note = $c->resolution_note ?? null;
            return $c;
        });
        return response()->json($items, 200, [], JSON_UNESCAPED_SLASHES);
    }
}
