<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TokenRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TokenRequestController extends Controller
{
    // Client: create a request
    public function create(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'quantity' => ['required','integer','min:1','max:100000'],
            'message' => ['nullable','string','max:2000']
        ]);

        $tr = TokenRequest::create([
            'user_id' => $user->id,
            'quantity' => $data['quantity'],
            'message' => $data['message'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json(['ok'=>true,'request'=>$tr], 201);
    }

    // Client: list own requests
    public function mine(Request $request)
    {
        $user = $request->user();
        $rows = TokenRequest::where('user_id', $user->id)->orderByDesc('created_at')->paginate(20);
        return response()->json($rows);
    }

    // Admin: list all requests
    public function adminIndex(Request $request)
    {
        $status = $request->string('status')->toString();
        $q = TokenRequest::query()->with('user')->orderByDesc('created_at');
        if ($status) $q->where('status', $status);
        return response()->json($q->paginate(50));
    }

    // Admin: approve -> add tokens and mark approved
    public function approve(Request $request, TokenRequest $tokenRequest)
    {
        if ($tokenRequest->status !== 'pending') {
            return response()->json(['message' => 'Already processed'], 409);
        }
        DB::transaction(function () use ($tokenRequest) {
            User::where('id', $tokenRequest->user_id)->increment('token_balance', $tokenRequest->quantity);
            $tokenRequest->update(['status' => 'approved']);
        });

        // Best-effort push notification to the user's device (via Expo).
        $user = User::find($tokenRequest->user_id);
        if ($user && !empty($user->expo_push_token)) {
            try {
                Http::acceptJson()->timeout(6)->post('https://exp.host/--/api/v2/push/send', [
                    'to' => $user->expo_push_token,
                    'title' => 'Search credits approved ✅',
                    'body' => "Your request for {$tokenRequest->quantity} credits was approved — happy searching!",
                    'sound' => 'default',
                    'priority' => 'high',
                    'data' => ['type' => 'credits_approved'],
                ]);
            } catch (\Throwable $e) { /* ignore push failures */ }
        }

        return response()->json(['ok'=>true, 'request'=>$tokenRequest->fresh()]);
    }

    // Admin: deny request
    public function deny(Request $request, TokenRequest $tokenRequest)
    {
        if ($tokenRequest->status !== 'pending') {
            return response()->json(['message' => 'Already processed'], 409);
        }
        $tokenRequest->update(['status' => 'denied']);
        return response()->json(['ok'=>true, 'request'=>$tokenRequest->fresh()]);
    }
}
