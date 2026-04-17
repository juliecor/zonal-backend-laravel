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
    public ?string $brandLogoUrl = null;
    public ?string $brandName = null;
    public ?string $brandLink = null;

    public function __construct(
        string $inviteUrl,
        string $appName = 'PH Zonal',
        ?string $brandLogoUrl = null,
        ?string $brandName = null,
        ?string $brandLink = null
    )
    {
        $this->inviteUrl = $inviteUrl;
        $this->appName = $appName;
        // Allow override via args, otherwise pick up from env with sane defaults
        $this->brandLogoUrl = $brandLogoUrl ?? env('BRAND_LOGO_URL');
        $this->brandName = $brandName ?? env('BRAND_NAME', 'Filipinohomes');
        $this->brandLink = $brandLink ?? env('BRAND_LINK_URL', 'https://filipinohomes.com');
    }

    public function build()
    {
        return $this->subject('You\'re invited to ' . $this->appName)
            ->view('emails.invitation')
            ->with([
                'inviteUrl' => $this->inviteUrl,
                'appName' => $this->appName,
                'brandLogoUrl' => $this->brandLogoUrl,
                'brandName' => $this->brandName,
                'brandLink' => $this->brandLink,
            ]);
    }
}
