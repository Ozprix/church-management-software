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
        Schema::create('group_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('meeting_type')->default('regular'); // regular, special, online
            $table->text('notes')->nullable();
            $table->unsignedInteger('total_attendees')->default(0);
            $table->unsignedInteger('total_visitors')->default(0);
            $table->unsignedInteger('total_first_timers')->default(0);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate attendance records for the same group on the same date
            $table->unique(['group_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_attendances');
    }
};
