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
        Schema::create('recurring_donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('donation_categories')->onDelete('set null');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->string('payment_gateway');
            $table->enum('frequency', ['weekly', 'biweekly', 'monthly', 'quarterly', 'biannually', 'annually']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_donation_date');
            $table->boolean('is_active')->default(true);
            $table->string('gateway_subscription_id')->nullable();
            $table->string('gateway_customer_id')->nullable();
            $table->json('gateway_data')->nullable();
            $table->foreignId('last_donation_id')->nullable()->constrained('donations')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Add recurring_donation_id to donations table
        Schema::table('donations', function (Blueprint $table) {
            $table->foreignId('recurring_donation_id')->nullable()->after('campaign_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropForeign(['recurring_donation_id']);
            $table->dropColumn('recurring_donation_id');
        });

        Schema::dropIfExists('recurring_donations');
    }
};
