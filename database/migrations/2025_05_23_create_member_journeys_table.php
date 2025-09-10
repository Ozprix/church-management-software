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
        Schema::create('member_journeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->string('stage')->default('visitor'); // visitor, regular, committed, leader
            $table->date('stage_date');
            $table->string('previous_stage')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // Add journey_stage to members table
        Schema::table('members', function (Blueprint $table) {
            $table->string('journey_stage')->default('visitor')->after('membership_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_journeys');
        
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('journey_stage');
        });
    }
};
