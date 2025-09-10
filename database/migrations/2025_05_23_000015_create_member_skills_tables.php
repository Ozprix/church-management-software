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
        // Create skills table
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create interests table
        Schema::create('interests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create member_skills pivot table
        Schema::create('member_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->enum('proficiency_level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('intermediate');
            $table->text('notes')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            
            // Unique constraint to prevent duplicates
            $table->unique(['member_id', 'skill_id']);
        });

        // Create member_interests pivot table
        Schema::create('member_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->foreignId('interest_id')->constrained()->onDelete('cascade');
            $table->enum('interest_level', ['low', 'medium', 'high'])->default('medium');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicates
            $table->unique(['member_id', 'interest_id']);
        });

        // Create spiritual_gifts table
        Schema::create('spiritual_gifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('scripture_reference')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create member_spiritual_gifts pivot table
        Schema::create('member_spiritual_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->foreignId('spiritual_gift_id')->constrained('spiritual_gifts')->onDelete('cascade');
            $table->enum('strength_level', ['low', 'medium', 'high'])->default('medium');
            $table->text('notes')->nullable();
            $table->boolean('is_assessed')->default(false);
            $table->date('assessment_date')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicates
            $table->unique(['member_id', 'spiritual_gift_id']);
        });

        // Create availability table for member availability tracking
        Schema::create('member_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_recurring')->default(true);
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Index for efficient queries
            $table->index(['member_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_availability');
        Schema::dropIfExists('member_spiritual_gifts');
        Schema::dropIfExists('spiritual_gifts');
        Schema::dropIfExists('member_interests');
        Schema::dropIfExists('member_skills');
        Schema::dropIfExists('interests');
        Schema::dropIfExists('skills');
    }
};
