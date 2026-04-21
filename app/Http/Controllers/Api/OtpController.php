<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpCodeMail;
use App\Models\EmailOtp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    private function generateCode(): string
    {
        return str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);
    }

    public function resend(Request $request)
    {
        $data = $request->validate([ 'user_id' => ['required','integer','exists:users,id'] ]);
        $user = User::findOrFail($data['user_id']);

        $existing = EmailOtp::where('user_id', $user->id)->latest()->first();
        $now = Carbon::now();
        if ($existing && $existing->last_sent_at && $existing->last_sent_at->diffInSeconds($now) < 30) {
            return response()->json(['message' => 'Please wait before requesting another code.'], 429);
        }

        // Invalidate previous OTPs
        EmailOtp::where('user_id', $user->id)->delete();

        $code = $this->generateCode();
        $otp = EmailOtp::create([
            'user_id' => $user->id,
            'sent_to' => $user->email,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'last_sent_at' => $now,
            'expires_at' => $now->copy()->addMinutes(10),
        ]);

        try { Mail::to($user->email)->send(new OtpCodeMail($code, env('APP_NAME','Zonal Value'))); } catch (\Throwable $e) { Log::error('Mail send failed', ['e'=>$e->getMessage()]); }

        return response()->json(['ok'=>true]);
    }

    // ── Passwordless login: request code by email ─────────────────────────────
    public function requestLogin(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email']
        ]);
        $user = User::where('email', $data['email'])->first();
        if (!$user) return response()->json(['message' => 'Email not registered'], 404);

        $existing = EmailOtp::where('user_id', $user->id)->latest()->first();
        $now = Carbon::now();
        if ($existing && $existing->last_sent_at && $existing->last_sent_at->diffInSeconds($now) < 30) {
            return response()->json(['message' => 'Please wait before requesting another code.'], 429);
        }

        EmailOtp::where('user_id', $user->id)->delete();
        $code = $this->generateCode();
        EmailOtp::create([
            'user_id' => $user->id,
            'sent_to' => $user->email,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'last_sent_at' => $now,
            'expires_at' => $now->copy()->addMinutes(10),
        ]);
        try { Mail::to($user->email)->send(new OtpCodeMail($code, env('APP_NAME','Zonal Value'))); } catch (\Throwable $e) { Log::error('Mail send failed', ['e'=>$e->getMessage()]); }
        return response()->json(['ok'=>true,'user_id'=>$user->id,'resend_cooldown'=>30]);
    }

    public function verifyLogin(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required','integer','exists:users,id'],
            'code' => ['required','string','size:6'],
        ]);
        $user = User::findOrFail($data['user_id']);
        $otp = EmailOtp::where('user_id',$user->id)->latest()->first();
        if (!$otp) return response()->json(['message'=>'No code found. Please resend.'], 422);
        if (Carbon::now()->greaterThan($otp->expires_at)) { EmailOtp::where('user_id',$user->id)->delete(); return response()->json(['message'=>'Code expired. Please resend.'], 422); }
        if ($otp->attempts >= 5) return response()->json(['message'=>'Too many attempts. Please resend.'], 429);
        if (!Hash::check($data['code'], $otp->code_hash)) { $otp->attempts = $otp->attempts + 1; $otp->save(); return response()->json(['message'=>'Invalid code.'], 422); }

        if (!$user->email_verified_at) { $user->email_verified_at = Carbon::now(); }
        $user->save();
        EmailOtp::where('user_id',$user->id)->delete();
        $token = $user->createToken('web')->plainTextToken;
        return response()->json(['user'=>$user,'token'=>$token]);
    }
    public function verify(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required','integer','exists:users,id'],
            'code' => ['required','string','size:6'],
        ]);
        $user = User::findOrFail($data['user_id']);
        $otp = EmailOtp::where('user_id',$user->id)->latest()->first();
        if (!$otp) return response()->json(['message'=>'No code found. Please resend.'], 422);
        if (Carbon::now()->greaterThan($otp->expires_at)) {
            EmailOtp::where('user_id',$user->id)->delete();
            return response()->json(['message'=>'Code expired. Please resend.'], 422);
        }
        if ($otp->attempts >= 5) return response()->json(['message'=>'Too many attempts. Please resend.'], 429);

        if (!Hash::check($data['code'], $otp->code_hash)) {
            $otp->attempts = $otp->attempts + 1;
            $otp->save();
            return response()->json(['message'=>'Invalid code.'], 422);
        }

        $user->email_verified_at = Carbon::now();
        $user->save();
        EmailOtp::where('user_id',$user->id)->delete();

        $token = $user->createToken('web')->plainTextToken;
        return response()->json(['user'=>$user,'token'=>$token]);
    }
}
