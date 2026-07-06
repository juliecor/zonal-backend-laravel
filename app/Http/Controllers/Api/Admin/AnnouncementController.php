<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ExpoPush;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /** Admin-only: broadcast a push notification (announcement / reminder) to all users. */
    public function send(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'body'  => ['required', 'string', 'max:500'],
        ]);

        $tokens = User::whereNotNull('expo_push_token')
            ->where('expo_push_token', '!=', '')
            ->pluck('expo_push_token')
            ->all();

        $sent = ExpoPush::toTokens($tokens, $data['title'], $data['body'], ['type' => 'announcement']);

        return response()->json(['ok' => true, 'sent' => $sent]);
    }
}
