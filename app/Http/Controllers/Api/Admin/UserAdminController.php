<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ExpoPush;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $rows = User::query()
            ->select(['id','name','first_name','middle_name','last_name','phone','email','role','token_balance','avatar_path','created_at'])
            ->orderByDesc('created_at')
            ->paginate(min(max((int)$request->integer('per_page', 20),1),100));
        // Attach the S3 avatar URL (same logic as ProfileController) so the admin
        // panel can show real profile photos instead of initials.
        $rows->getCollection()->transform(function ($u) {
            $u->avatar_url = $u->avatar_path ? Storage::disk('s3')->url($u->avatar_path) : null;
            return $u;
        });
        return response()->json($rows);
    }

    public function addTokens(Request $request, User $user)
    {
        $data = $request->validate([
            'add' => ['required','integer','min:1','max:100000'],
        ]);
        $user->increment('token_balance', $data['add']);

        // Notify the user their balance was topped up (best-effort push).
        ExpoPush::toUser(
            $user->fresh(),
            'Credits added ✅',
            "You've received {$data['add']} search credits — happy searching!",
            ['type' => 'credits_topup'],
        );

        return response()->json(['ok'=>true, 'user'=>$user->fresh()]);
    }
}
