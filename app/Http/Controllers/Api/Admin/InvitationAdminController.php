<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Mail\UserInvitationMail;

class InvitationAdminController extends Controller
{
    public function invite(Request $request)
    {
        $data = $request->validate([
            'email' => ['nullable','email'],
            'emails' => ['nullable','array'],
            'emails.*' => ['email'],
            'redirect_url' => ['nullable','url'],
        ]);

        $emails = [];
        if (!empty($data['email'])) {
            $emails[] = strtolower(trim($data['email']));
        }
        if (!empty($data['emails'])) {
            foreach ($data['emails'] as $e) {
                $emails[] = strtolower(trim($e));
            }
        }
        $emails = array_values(array_unique(array_filter($emails)));

        if (empty($emails)) {
            throw ValidationException::withMessages([
                'emails' => ['Please provide at least one valid email.']
            ]);
        }

        $frontendBase = $data['redirect_url'] ?? env('FRONTEND_URL', 'http://localhost:3000');
        $appName = env('APP_NAME', config('app.name', 'Zonal Value'));

        $results = [
            'sent' => [],
            'failed' => []
        ];

        foreach ($emails as $email) {
            try {
                $token = Str::random(32);
                // Simple invite link: point to register with email prefilled; token can be included for future validation
                $inviteUrl = rtrim($frontendBase, '/') . '/register?email=' . urlencode($email) . '&invite=' . $token;
                Mail::to($email)->cc($this->mailCcIfSet())->send(new UserInvitationMail($inviteUrl, $appName));
                $results['sent'][] = $email;
            } catch (\Throwable $e) {
                Log::error('Failed to send invitation', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
                $results['failed'][] = $email;
            }
        }

        return response()->json([
            'message' => 'Invitation processing complete.',
            'results' => $results,
        ]);
    }

    private function mailCcIfSet(): array
    {
        $cc = env('MAIL_CC');
        if (!$cc) return [];
        return array_map('trim', explode(',', $cc));
    }
}
