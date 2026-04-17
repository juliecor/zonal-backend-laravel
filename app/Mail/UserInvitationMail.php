<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $inviteUrl;
    public string $appName;

    public function __construct(string $inviteUrl, string $appName = 'PH Zonal')
    {
        $this->inviteUrl = $inviteUrl;
        $this->appName = $appName;
    }

    public function build()
    {
        return $this->subject('You\'re invited to ' . $this->appName)
            ->view('emails.invitation')
            ->with([
                'inviteUrl' => $this->inviteUrl,
                'appName' => $this->appName,
            ]);
    }
}
