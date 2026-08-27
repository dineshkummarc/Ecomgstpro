<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Payment;
use App\Models\Invoice;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'company_name',
        'email_verified_at',
        'plan',
        'status',
        'current_device_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'trial_started_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'trial_reminder_sent_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if the user is locked out of features due to expired or cancelled subscription.
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return in_array($this->subscription_status, ['expired', 'cancelled']);
    }

    /**
     * Check if the user has an active paid subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription_status === 'active'
            && $this->subscription_ends_at
            && Carbon::now()->lessThanOrEqualTo($this->subscription_ends_at);
    }

    /**
     * Check if the user is currently on a valid trial.
     */
    public function isOnTrial(): bool
    {
        return $this->subscription_status === 'trial'
            && $this->trial_ends_at
            && Carbon::now()->lessThanOrEqualTo($this->trial_ends_at);
    }

    /**
     * Get the remaining days of the free trial.
     */
    public function trialDaysLeft(): int
    {
        if (!$this->trial_ends_at) {
            return 0;
        }
        $diff = Carbon::now()->diffInDays($this->trial_ends_at, false);
        return max(0, (int) ceil($diff));
    }

    /**
     * Get a formatted label for the GSTIN limit.
     */
    public function gstinLimitLabel(): string
    {
        return $this->gstin_limit ? (string) $this->gstin_limit : 'Unlimited';
    }

    /**
     * Get the actual numeric GSTIN limit for the current plan.
     * Used by ClientController::store() to enforce the limit before saving.
     *
     * - If `gstin_limit` column is explicitly set on the user, that value wins.
     * - Otherwise: 5 on trial, 50 on any active paid plan, 0 if expired/no plan.
     *
     * Note: gstinLimitLabel() shows "Unlimited" for paid users (no gstin_limit set),
     * but this method still caps them at 50. If you want a true no-cap plan,
     * change the `return 50;` below to `return PHP_INT_MAX;`.
     */
    public function currentGstinLimit(): int
    {
        if (!is_null($this->gstin_limit)) {
            return (int) $this->gstin_limit;
        }

        if ($this->hasActiveSubscription()) {
            return 50;
        }

        if ($this->isOnTrial()) {
            return 5;
        }

        return 0;
    }

    /**
     * Get the payments for the user.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the invoices for the user.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
