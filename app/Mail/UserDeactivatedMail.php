<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserDeactivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $userName;
    public string $reason;

    public function __construct(string $userName, string $reason)
    {
        $this->userName = $userName;
        $this->reason   = $reason;
    }

    public function build()
    {
        return $this->subject('Your EcomgstPro Account Has Been Deactivated')
                     ->view('emails.user-deactivated');
    }
}
