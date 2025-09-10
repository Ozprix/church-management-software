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
        Schema::create('integration_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('integration_type'); // 'google_calendar', 'sms', etc.
            
            // OAuth tokens and settings
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Integration-specific settings
            $table->json('settings')->nullable(); // Store provider-specific settings
            $table->json('preferences')->nullable(); // User preferences for this integration
            
            // For group-specific integrations
            $table->foreignId('group_id')->nullable()->constrained()->onDelete('cascade');
            
            // Metadata
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate integrations
            $table->unique(['user_id', 'integration_type', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_settings');
    }
};
