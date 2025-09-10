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
        Schema::table('group_events', function (Blueprint $table) {
            // Add category relationship
            $table->foreignId('category_id')->nullable()->after('event_type')->constrained('event_categories')->nullOnDelete();
            
            // Add registration settings
            $table->boolean('registration_required')->default(false)->after('notify_members');
            $table->integer('registration_capacity')->nullable()->after('registration_required');
            $table->timestamp('registration_deadline')->nullable()->after('registration_capacity');
            $table->boolean('allow_guests')->default(true)->after('registration_deadline');
            $table->integer('max_guests_per_registration')->default(0)->after('allow_guests');
            
            // Add reminder settings
            $table->boolean('send_reminders')->default(true)->after('max_guests_per_registration');
            
            // Add sharing settings
            $table->boolean('is_shareable')->default(true)->after('send_reminders');
            $table->boolean('is_public')->default(false)->after('is_shareable');
            $table->string('access_code')->nullable()->after('is_public');
            
            // Add reporting fields
            $table->integer('view_count')->default(0)->after('access_code');
            $table->integer('registration_count')->default(0)->after('view_count');
            $table->integer('attendance_count')->default(0)->after('registration_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_events', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'category_id',
                'registration_required',
                'registration_capacity',
                'registration_deadline',
                'allow_guests',
                'max_guests_per_registration',
                'send_reminders',
                'is_shareable',
                'is_public',
                'access_code',
                'view_count',
                'registration_count',
                'attendance_count'
            ]);
        });
    }
};
