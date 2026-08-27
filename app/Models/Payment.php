<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'plan_id',
        'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature',
        'amount', 'currency', 'status',
        'plan_started_at', 'plan_ends_at',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'plan_started_at' => 'datetime',
        'plan_ends_at'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
