<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public string $appName;

    public function __construct(string $code, string $appName = 'Zonal Value')
    {
        $this->code = $code;
        $this->appName = $appName;
    }

    public function build()
    {
        return $this->subject('Your verification code for ' . $this->appName)
            ->view('emails.otp')
            ->with(['code' => $this->code, 'appName' => $this->appName]);
    }
}
