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
        Schema::create('event_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_event_id')->constrained('group_events')->onDelete('cascade');
            $table->foreignId('shared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('shared_with_group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->foreignId('shared_with_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('shared_with_email')->nullable();
            $table->text('message')->nullable();
            $table->string('share_type')->default('view'); // view, register, edit
            $table->string('status')->default('pending'); // pending, accepted, declined
            $table->string('token')->unique()->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_shares');
    }
};
