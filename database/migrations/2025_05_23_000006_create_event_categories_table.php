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
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#3b82f6'); // Default blue color
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default categories
        DB::table('event_categories')->insert([
            ['name' => 'Meeting', 'color' => '#3b82f6', 'description' => 'Regular group meetings', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Outreach', 'color' => '#10b981', 'description' => 'Community outreach activities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Social', 'color' => '#8b5cf6', 'description' => 'Social gatherings and fellowship', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Training', 'color' => '#f59e0b', 'description' => 'Training and development sessions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Prayer', 'color' => '#ef4444', 'description' => 'Prayer meetings and vigils', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Study', 'color' => '#6366f1', 'description' => 'Bible study and educational events', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Service', 'color' => '#14b8a6', 'description' => 'Service projects and volunteering', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'color' => '#6b7280', 'description' => 'Other types of events', 'created_at' => now(), 'updated_at' => now()]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_categories');
    }
};
