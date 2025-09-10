<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupCommunicationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Group messages table
        Schema::create('group_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('members')->onDelete('cascade');
            $table->text('message');
            $table->string('message_type')->default('text'); // text, image, file, etc.
            $table->string('attachment_path')->nullable();
            $table->string('attachment_type')->nullable();
            $table->boolean('is_announcement')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Group message recipients table
        Schema::create('group_message_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_message_id')->constrained('group_messages')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Ensure a message can only be sent to a member once
            $table->unique(['group_message_id', 'member_id']);
        });

        // Group prayer requests table
        Schema::create('group_prayer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['active', 'answered', 'archived'])->default('active');
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_private')->default(false);
            $table->timestamp('answered_at')->nullable();
            $table->text('answer_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Group prayer request responses table
        Schema::create('group_prayer_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prayer_request_id')->constrained('group_prayer_requests')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->text('response');
            $table->boolean('is_praying')->default(true);
            $table->timestamps();
            
            // Ensure a member can only respond to a prayer request once
            $table->unique(['prayer_request_id', 'member_id']);
        });

        // Group documents table
        Schema::create('group_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('members')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_type');
            $table->integer('file_size');
            $table->string('category')->nullable();
            $table->boolean('is_public')->default(false);
            $table->integer('download_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Group document access table
        Schema::create('group_document_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('group_documents')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->timestamp('last_accessed_at')->nullable();
            $table->integer('access_count')->default(0);
            $table->timestamps();
            
            // Ensure a document access record is unique per member
            $table->unique(['document_id', 'member_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_document_access');
        Schema::dropIfExists('group_documents');
        Schema::dropIfExists('group_prayer_responses');
        Schema::dropIfExists('group_prayer_requests');
        Schema::dropIfExists('group_message_recipients');
        Schema::dropIfExists('group_messages');
    }
}
