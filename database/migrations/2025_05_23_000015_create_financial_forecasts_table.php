<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancialForecastsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financial_forecasts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('period_type', ['monthly', 'quarterly', 'annual']);
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('forecast_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forecast_id')->constrained('financial_forecasts')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['income', 'expense']);
            $table->enum('category', ['donation', 'tithe', 'offering', 'event', 'fundraising', 'salary', 'utilities', 'maintenance', 'ministry', 'missions', 'other']);
            $table->decimal('amount', 12, 2);
            $table->date('expected_date')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable(); // monthly, quarterly, etc.
            $table->timestamps();
        });

        Schema::create('budget_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets');
            $table->string('department')->nullable();
            $table->string('ministry')->nullable();
            $table->string('project')->nullable();
            $table->string('category');
            $table->decimal('allocated_amount', 12, 2);
            $table->decimal('used_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('financial_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('metric_name');
            $table->string('category')->nullable();
            $table->decimal('value', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('financial_metrics');
        Schema::dropIfExists('budget_allocations');
        Schema::dropIfExists('forecast_items');
        Schema::dropIfExists('financial_forecasts');
    }
}
