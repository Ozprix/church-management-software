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
            // Add category relationship
            $table->foreignId('category_id')->nullable()->after('member_id')
                ->constrained('donation_categories');

            // Add project relationship
            $table->foreignId('project_id')->nullable()->after('category_id')
                ->constrained('projects');

            // Add gifting functionality
            $table->foreignId('recipient_id')->nullable()->after('member_id')
                ->constrained('members');

            // Add gift message
            $table->text('gift_message')->nullable();

            // Add anonymous flag
            $table->boolean('is_anonymous')->default(false)->after('amount');

            // Add recurring donation fields
            $table->date('recurring_start_date')->nullable()->after('recurring_frequency');
            $table->date('recurring_end_date')->nullable()->after('recurring_start_date');

            // Add receipt information
            $table->string('receipt_number')->nullable()->after('transaction_id');
            $table->boolean('receipt_sent')->default(false)->after('receipt_number');
            $table->timestamp('receipt_sent_at')->nullable()->after('receipt_sent');

            // Add annual tax receipt relationship
            $table->foreignId('annual_tax_receipt_id')->nullable()->after('receipt_sent_at')
                ->constrained('tax_receipts')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['project_id']);
            $table->dropForeign(['recipient_id']);
            $table->dropForeign(['annual_tax_receipt_id']);

            $table->dropColumn([
                'category_id',
                'project_id',
                'recipient_id',
                'gift_message',
                'is_anonymous',
                'recurring_start_date',
                'recurring_end_date',
                'receipt_number',
                'receipt_sent',
                'receipt_sent_at',
                'annual_tax_receipt_id'
            ]);
        });
    }
};
