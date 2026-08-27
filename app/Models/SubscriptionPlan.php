<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'duration_months',
        'price_per_month', 'total_price', 'gstin_limit', 'is_active',
    ];

    protected $casts = [
        'price_per_month' => 'decimal:2',
        'total_price'     => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'plan_id');
    }

    /** e.g. "Save 12%" badge text vs monthly base price */
    public function discountLabel(): ?string
    {
        $monthly = self::where('slug', 'monthly')->value('price_per_month');
        if (!$monthly || $this->slug === 'monthly') {
            return null;
        }
        $percent = round((1 - ($this->price_per_month / $monthly)) * 100);
        return $percent > 0 ? "Save {$percent}%" : null;
    }
}
