<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'user_id', 'payment_id', 'plan_id',
        'amount', 'currency', 'issued_at',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'issued_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /** Generates the next sequential invoice number, e.g. INV-2026-000123 */
    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $nextId = (self::max('id') ?? 0) + 1;
        return 'INV-' . $year . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }
}