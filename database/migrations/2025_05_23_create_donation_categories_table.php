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
        Schema::create('donation_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_tax_deductible')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Insert default categories
        DB::table('donation_categories')->insert([
            [
                'name' => 'Tithe',
                'code' => 'tithe',
                'description' => 'Regular tithe contributions (10% of income)',
                'is_active' => true,
                'is_tax_deductible' => true,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Offering',
                'code' => 'offering',
                'description' => 'General offering contributions',
                'is_active' => true,
                'is_tax_deductible' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Missions',
                'code' => 'missions',
                'description' => 'Contributions for mission work',
                'is_active' => true,
                'is_tax_deductible' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_categories');
    }
};
