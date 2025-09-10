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
        Schema::create('pledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('pledge_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('frequency', ['one-time', 'weekly', 'biweekly', 'monthly', 'quarterly', 'annually'])->default('monthly');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Add pledge_id to donations table
        Schema::table('donations', function (Blueprint $table) {
            $table->foreignId('pledge_id')->nullable()->after('campaign_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropForeign(['pledge_id']);
            $table->dropColumn('pledge_id');
        });
        
        Schema::dropIfExists('pledges');
    }
};
