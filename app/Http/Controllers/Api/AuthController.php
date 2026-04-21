<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash as FacadesHash;
use App\Models\EmailOtp;
use App\Mail\OtpCodeMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')),
            'first_name' => $data['first_name'] ?? null,
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'client',
            'token_balance' => 20, // initial free tokens
        ]);

        // Create OTP and send mail
        EmailOtp::where('user_id',$user->id)->delete();
        $code = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);
        EmailOtp::create([
            'user_id' => $user->id,
            'sent_to' => $user->email,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'last_sent_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);
        try { Mail::to($user->email)->send(new OtpCodeMail($code, env('APP_NAME','Zonal Value'))); } catch (\Throwable $e) {}

        return response()->json([
            'pending_verification' => true,
            'user_id' => $user->id,
            'email' => $user->email,
            'resend_cooldown' => 30,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        // Optionally set abilities like ['search:read'] in future
        $token = $user->createToken('web')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        if ($user && $user->avatar_path) {
            $user->avatar_url = Storage::disk('s3')->url($user->avatar_path);
        } else {
            $user->avatar_url = null;
        }
        return response()->json($user, 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
