<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialEndingReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Your Ecomgstpro free trial ends in 2 days ⏳')
            ->view('emails.trial_ending')
            ->with([
                'name'         => $this->user->name,
                'trialEndDate' => $this->user->trial_ends_at->format('d M Y'),
                'pricingUrl'   => route('subscription.pricing'),
            ]);
    }
}