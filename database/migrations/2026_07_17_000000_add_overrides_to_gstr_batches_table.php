<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gstr_batches', function (Blueprint $table) {
            $table->longText('doc_issue_override')->nullable()->after('total_tax');
            $table->longText('hsn_override')->nullable()->after('doc_issue_override');
        });
    }

    public function down(): void
    {
        Schema::table('gstr_batches', function (Blueprint $table) {
            $table->dropColumn(['doc_issue_override', 'hsn_override']);
        });
    }
};
