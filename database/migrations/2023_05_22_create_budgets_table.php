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
        if (!Schema::hasTable('budgets')) {
            Schema::create('budgets', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('amount', 10, 2);
                $table->decimal('spent_amount', 10, 2)->default(0);
                $table->date('start_date');
                $table->date('end_date');
                $table->string('category');
                $table->enum('status', ['active', 'inactive', 'completed'])->default('active');
                $table->timestamps();
            });
        }

        // Add budget_id to expenses table if it doesn't exist
        if (Schema::hasTable('expenses') && !Schema::hasColumn('expenses', 'budget_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->foreignId('budget_id')->nullable()->constrained('budgets')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['budget_id']);
            $table->dropColumn('budget_id');
        });
        
        Schema::dropIfExists('budgets');
    }
};
