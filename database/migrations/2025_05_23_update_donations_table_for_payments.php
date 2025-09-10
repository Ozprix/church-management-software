<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            // Add payment status field
            $table->string('payment_status')->default('pending')->after('transaction_id');

            // Add refund fields
            $table->string('refund_id')->nullable()->after('payment_status');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('refund_id');

            // Add gateway data field for storing additional payment information
            $table->json('gateway_data')->nullable();

            // Add indexes
            $table->index('payment_status');
            $table->index('refund_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'refund_id',
                'refund_amount',
                'gateway_data',
            ]);
        });
    }
};
