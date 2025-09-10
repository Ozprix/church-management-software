<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupAnalyticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->date('date');
            $table->integer('total_members')->default(0);
            $table->integer('active_members')->default(0);
            $table->integer('new_members')->default(0);
            $table->integer('exited_members')->default(0);
            $table->integer('total_attendance')->default(0);
            $table->integer('total_visitors')->default(0);
            $table->integer('total_events')->default(0);
            $table->integer('total_event_attendees')->default(0);
            $table->float('attendance_rate', 8, 2)->default(0);
            $table->float('growth_rate', 8, 2)->default(0);
            $table->float('engagement_score', 8, 2)->default(0);
            $table->json('additional_metrics')->nullable();
            $table->timestamps();
            
            // Ensure only one record per group per date
            $table->unique(['group_id', 'date']);
        });
        
        Schema::create('group_member_engagement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->date('date');
            $table->integer('attendance_count')->default(0);
            $table->integer('event_attendance_count')->default(0);
            $table->integer('communication_count')->default(0);
            $table->integer('leadership_activities')->default(0);
            $table->float('engagement_score', 8, 2)->default(0);
            $table->json('engagement_details')->nullable();
            $table->timestamps();
            
            // Ensure only one record per group per member per date
            $table->unique(['group_id', 'member_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_member_engagement');
        Schema::dropIfExists('group_analytics');
    }
}
