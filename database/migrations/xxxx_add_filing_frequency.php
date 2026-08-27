<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gstr_clients', function (Blueprint $table) {
            $table->enum('filing_frequency', ['monthly', 'quarterly'])
                  ->default('monthly')
                  ->after('state_code');
        });

        Schema::table('gstr_transactions', function (Blueprint $table) {
            $table->string('filing_period', 20)->nullable()->after('period');
            $table->index(['gstin', 'filing_period'], 'idx_gstin_filing_period');
        });

        Schema::table('gstr_batches', function (Blueprint $table) {
            $table->string('filing_period', 20)->nullable()->after('period');
            $table->enum('frequency', ['monthly', 'quarterly'])->default('monthly')->after('filing_period');
            $table->index(['client_gstin', 'filing_period'], 'idx_client_filing_period');
        });
    }

    public function down(): void
    {
        Schema::table('gstr_clients', function (Blueprint $table) {
            $table->dropColumn('filing_frequency');
        });
        Schema::table('gstr_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_gstin_filing_period');
            $table->dropColumn('filing_period');
        });
        Schema::table('gstr_batches', function (Blueprint $table) {
            $table->dropIndex('idx_client_filing_period');
            $table->dropColumn(['filing_period', 'frequency']);
        });
    }
};
