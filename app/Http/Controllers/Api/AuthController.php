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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * OAuth client IDs allowed to sign in with Google. The idToken the app sends has an
     * "aud" claim equal to the WEB client ID it was configured with; we only trust tokens
     * minted for our own project. These are public (they ship in the app) — not secrets.
     * Filled in from google-services.json (client_type 3 = web, client_type 1 = android).
     */
    private const GOOGLE_CLIENT_IDS = [
        '804497166044-kf10m40uuvfe4tnb0t7389882e7ro97b.apps.googleusercontent.com',
    ];

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

    /**
     * "Continue with Google": the app sends a Google ID token. We verify it with Google,
     * then find-or-create the matching user and issue our own Sanctum token. Because Google
     * has already verified the email, no OTP step is needed.
     */
    public function google(Request $request)
    {
        $data = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        // Verify the token with Google (checks signature + expiry for us).
        try {
            $resp = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $data['id_token'],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Could not reach Google to verify your sign-in. Please try again.'], 503);
        }
        if (!$resp->ok()) {
            return response()->json(['message' => 'Google sign-in could not be verified. Please try again.'], 422);
        }
        $info = $resp->json();

        // The token must be minted for our app, verified, and not expired.
        $aud = $info['aud'] ?? null;
        $allowed = array_filter(self::GOOGLE_CLIENT_IDS);
        if (!empty($allowed) && !in_array($aud, $allowed, true)) {
            return response()->json(['message' => 'This Google sign-in is not authorized for this app.'], 422);
        }
        $emailVerified = ($info['email_verified'] ?? 'false');
        if ($emailVerified !== true && $emailVerified !== 'true') {
            return response()->json(['message' => 'Your Google email is not verified.'], 422);
        }
        if (isset($info['exp']) && (int) $info['exp'] < time()) {
            return response()->json(['message' => 'Your Google sign-in expired. Please try again.'], 422);
        }
        $email = strtolower(trim($info['email'] ?? ''));
        if ($email === '') {
            return response()->json(['message' => 'Google did not share an email address.'], 422);
        }

        // Find-or-create. Google-verified email is safe to match on directly.
        $user = User::where('email', $email)->first();
        if (!$user) {
            $first = $info['given_name'] ?? null;
            $last  = $info['family_name'] ?? null;
            $name  = $info['name'] ?? trim(($first ?? '').' '.($last ?? ''));
            $user = User::create([
                'name' => $name !== '' ? $name : $email,
                'first_name' => $first,
                'last_name' => $last,
                'email' => $email,
                'password' => Hash::make(Str::random(40)), // random — they'll use Google to sign in
                'role' => 'client',
                'token_balance' => 20, // same free tokens as email sign-up
                'email_verified_at' => Carbon::now(),
            ]);
        } elseif (!$user->email_verified_at) {
            $user->email_verified_at = Carbon::now();
            $user->save();
        }

        $token = $user->createToken('google')->plainTextToken;

        if ($user->avatar_path) {
            $user->avatar_url = Storage::disk('s3')->url($user->avatar_path);
        }

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
