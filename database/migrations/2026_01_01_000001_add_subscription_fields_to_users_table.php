<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('plan_type', ['trial', 'monthly', 'quarterly', 'yearly'])
                  ->default('trial')->after('email');
            $table->timestamp('trial_started_at')->nullable()->after('plan_type');
            $table->timestamp('trial_ends_at')->nullable()->after('trial_started_at');
            $table->enum('subscription_status', ['trial', 'active', 'expired', 'cancelled'])
                  ->default('trial')->after('trial_ends_at');
            $table->timestamp('subscription_ends_at')->nullable()->after('subscription_status');
            $table->string('current_device_token', 100)->nullable()->after('subscription_ends_at');
            $table->timestamp('trial_reminder_sent_at')->nullable()->after('current_device_token');
            $table->integer('gstin_limit')->default(5)->after('trial_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'plan_type', 'trial_started_at', 'trial_ends_at',
                'subscription_status', 'subscription_ends_at',
                'current_device_token', 'trial_reminder_sent_at', 'gstin_limit',
            ]);
        });
    }
};
