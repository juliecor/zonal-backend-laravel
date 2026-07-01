<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        if ($user && $user->avatar_path) {
            $user->avatar_url = Storage::disk('s3')->url($user->avatar_path);
        } else {
            $user->avatar_url = null;
        }
        return response()->json($user, 200, [], JSON_UNESCAPED_SLASHES);
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

        $avatarPrefix = env('AVATAR_PREFIX', 'avatars');
        $path = $file->store($avatarPrefix, 's3');

        // delete old if present (handle legacy local or new S3 paths)
        if ($user->avatar_path) {
            if (Storage::disk('s3')->exists($user->avatar_path)) {
                Storage::disk('s3')->delete($user->avatar_path);
            } elseif (Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }
        }

        $user->avatar_path = $path;
        $user->save();

        return response()->json([
            'avatar_url' => Storage::disk('s3')->url($path),
            'user' => $user,
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function deleteAvatar(Request $request)
    {
        $user = $request->user();
        if ($user->avatar_path) {
            if (Storage::disk('s3')->exists($user->avatar_path)) {
                Storage::disk('s3')->delete($user->avatar_path);
            } elseif (Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }
        }
        $user->avatar_path = null;
        $user->save();
        return response()->json(['ok' => true, 'user' => $user]);
    }

    /**
     * Permanently delete the signed-in user's account and all associated personal data.
     * Required by Google Play's account-deletion policy (in-app deletion for sign-up apps).
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        // Remove the profile photo from storage
        if ($user->avatar_path) {
            if (Storage::disk('s3')->exists($user->avatar_path)) {
                Storage::disk('s3')->delete($user->avatar_path);
            } elseif (Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }
        }

        // Revoke all access tokens, then remove associated records and the account itself.
        $user->tokens()->delete();
        DB::table('token_requests')->where('user_id', $user->id)->delete();
        DB::table('reports')->where('user_id', $user->id)->delete();
        DB::table('concerns')->where('user_id', $user->id)->delete();
        DB::table('email_otps')->where('user_id', $user->id)->delete();
        $user->delete();

        return response()->json(['ok' => true]);
    }
}
