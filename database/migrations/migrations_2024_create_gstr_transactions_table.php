<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gstr_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_id')->index();
            $table->string('client_id');
            $table->string('gstin');
            $table->string('platform')->index();
            $table->string('period'); // MM/YYYY format
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();

            // Supply details
            $table->string('place_of_supply'); // State code
            $table->string('supply_type')->default('B2C'); // B2C, B2B, etc
            $table->string('nature_of_supply')->default('Sale');

            // Product details
            $table->string('hsn_code')->nullable();
            $table->string('sac_code')->nullable();
            $table->string('product_description')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('uqc')->nullable();

            // Tax details
            $table->decimal('taxable_value', 15, 2)->default(0);
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->decimal('cgst_amount', 15, 2)->default(0);
            $table->decimal('sgst_amount', 15, 2)->default(0);
            $table->decimal('igst_amount', 15, 2)->default(0);
            $table->decimal('cess_amount', 15, 2)->default(0);
            $table->decimal('tcs_amount', 15, 2)->default(0);

            // Totals
            $table->decimal('invoice_value', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);

            // Reference data
            $table->string('buyer_gstin')->nullable();
            $table->string('buyer_name')->nullable();
            $table->string('seller_state')->nullable();

            // Processing metadata
            $table->string('status')->default('pending'); // pending, processed, error
            $table->text('raw_data')->nullable();
            $table->text('parsing_notes')->nullable();
            $table->float('confidence_score')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'period']);
            $table->index(['batch_id', 'platform']);
            $table->index(['place_of_supply', 'gst_rate']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gstr_transactions');
    }
};
