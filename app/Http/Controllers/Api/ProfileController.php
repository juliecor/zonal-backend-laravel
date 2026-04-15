<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'first_name' => ['nullable','string','max:100'],
            'middle_name' => ['nullable','string','max:100'],
            'last_name' => ['nullable','string','max:100'],
            'phone' => ['nullable','string','max:30'],
            'address' => ['nullable','string','max:255'],
            'company' => ['nullable','string','max:255'],
            'bio' => ['nullable','string','max:1000'],
            'name' => ['nullable','string','max:255'],
        ]);

        if (!empty($data['first_name']) || !empty($data['last_name'])) {
            $data['name'] = trim(($data['first_name'] ?? $user->first_name ?? '') . ' ' . ($data['last_name'] ?? $user->last_name ?? '')) ?: ($data['name'] ?? $user->name);
        }

        $user->fill($data);
        $user->save();

        return response()->json($user);
    }

    public function uploadAvatar(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'avatar' => ['required','image','mimes:jpg,jpeg,png,webp','max:4096'], // 4MB
        ]);

        $file = $request->file('avatar');

        $path = $file->store('avatars', 'public');

        // delete old if present
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->avatar_path = $path;
        $user->save();

        return response()->json([
            'avatar_url' => asset('storage/'.$path),
            'user' => $user,
        ]);
    }

    public function deleteAvatar(Request $request)
    {
        $user = $request->user();
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }
        $user->avatar_path = null;
        $user->save();
        return response()->json(['ok' => true, 'user' => $user]);
    }
}
