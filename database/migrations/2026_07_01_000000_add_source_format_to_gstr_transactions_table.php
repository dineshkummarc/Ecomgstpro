<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds `source_format` to gstr_transactions so we can tell, per platform
 * per period, whether the data came from a "GSTR-1 Ready" file or a
 * "Sales/MTR Report" file. Needed to block duplicate uploads of the
 * SAME underlying data in two different formats (Flipkart sends both,
 * and they overlap 100% — uploading both = double counted transactions).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gstr_transactions', function (Blueprint $table) {
            $table->string('source_format', 50)->nullable()->after('platform');
            $table->index(['user_id', 'gstin', 'period', 'platform', 'status'], 'gstr_tx_dup_check_idx');
        });
    }

    public function down(): void
    {
        Schema::table('gstr_transactions', function (Blueprint $table) {
            $table->dropIndex('gstr_tx_dup_check_idx');
            $table->dropColumn('source_format');
        });
    }
};
