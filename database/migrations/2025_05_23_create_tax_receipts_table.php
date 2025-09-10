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
        Schema::create('tax_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->string('receipt_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->date('donation_date')->nullable();
            $table->date('issue_date');
            $table->timestamp('sent_at')->nullable();
            $table->integer('tax_year');
            $table->boolean('is_annual')->default(false);
            $table->enum('status', ['issued', 'sent', 'voided'])->default('issued');
            $table->string('file_path')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->timestamps();
        });

        // Add annual_tax_receipt_id to donations table
        // $table->foreignId('annual_tax_receipt_id')->nullable()->after('receipt_sent_at')->constrained('tax_receipts')->onDelete('set null'); // Moved to update donations migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropForeign(['annual_tax_receipt_id']);
            $table->dropColumn('annual_tax_receipt_id');
        });

        Schema::dropIfExists('tax_receipts');
    }
};
