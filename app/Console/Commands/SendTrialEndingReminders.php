<?php

namespace App\Console\Commands;

use App\Mail\TrialEndingReminderMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendTrialEndingReminders extends Command
{
    protected $signature = 'trial:send-reminders';
    protected $description = 'Emails users whose free trial ends in 2 days, so they can pay before access is cut off';

    public function handle(): int
    {
        $windowStart = Carbon::now()->addDays(2)->startOfDay();
        $windowEnd   = Carbon::now()->addDays(2)->endOfDay();

        $users = User::where('subscription_status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [$windowStart, $windowEnd])
            ->whereNull('trial_reminder_sent_at')
            ->get();

        foreach ($users as $user) {
            Mail::to($user->email)->send(new TrialEndingReminderMail($user));
            $user->trial_reminder_sent_at = Carbon::now();
            $user->save();
            $this->info("Reminder sent to {$user->email}");
        }

        $this->info($users->count() . ' reminder email(s) sent.');
        return self::SUCCESS;
    }
}