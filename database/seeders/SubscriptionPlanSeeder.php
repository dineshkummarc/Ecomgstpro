<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['name' => 'Monthly',   'slug' => 'monthly',   'duration_months' => 1,  'price_per_month' => 99,  'total_price' => 99,   'gstin_limit' => 50],
            ['name' => 'Quarterly', 'slug' => 'quarterly', 'duration_months' => 3,  'price_per_month' => 95,  'total_price' => 285,  'gstin_limit' => 50],
            ['name' => 'Yearly',    'slug' => 'yearly',    'duration_months' => 12, 'price_per_month' => 87,  'total_price' => 1044, 'gstin_limit' => 50],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}

// Run with: php artisan db:seed --class=SubscriptionPlanSeeder
