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
        Schema::create('group_attendance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_attendance_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visitor_name')->nullable();
            $table->string('visitor_phone')->nullable();
            $table->string('visitor_email')->nullable();
            $table->boolean('is_first_time')->default(false);
            $table->string('attendance_status')->default('present'); // present, absent, excused
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Ensure a member or visitor can only be recorded once per attendance record
            $table->unique(['group_attendance_id', 'member_id'], 'unique_member_attendance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_attendance_details');
    }
};
