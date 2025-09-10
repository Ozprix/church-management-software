<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            // Add foreign key for member_statuses
            $table->foreignId('membership_status_id')->nullable()->after('user_id')
                  ->constrained('member_statuses')->onDelete('set null');
            
            // Add new fields
            $table->string('middle_name')->nullable()->after('last_name');
            $table->string('maiden_name')->nullable()->after('middle_name');
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed', 'separated'])->nullable()->after('gender');
            $table->string('occupation')->nullable()->after('marital_status');
            $table->string('employer')->nullable()->after('occupation');
            $table->string('work_phone')->nullable()->after('phone');
            $table->string('emergency_contact_name')->nullable()->after('work_phone');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relation')->nullable()->after('emergency_contact_phone');
            $table->string('baptism_status')->nullable()->after('membership_date');
            $table->date('baptism_date')->nullable()->after('baptism_status');
            $table->string('baptism_location')->nullable()->after('baptism_date');
            $table->string('membership_type')->nullable()->after('baptism_location');
            $table->date('membership_end_date')->nullable()->after('membership_date');
            $table->text('notes')->nullable()->after('custom_fields');
            $table->softDeletes();
            
            // Add indexes for frequently queried fields
            $table->index('first_name');
            $table->index('last_name');
            $table->index('email');
            $table->index('phone');
            $table->index('membership_status_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['membership_status_id']);
            
            // Drop columns
            $table->dropColumn([
                'membership_status_id',
                'middle_name',
                'maiden_name',
                'marital_status',
                'occupation',
                'employer',
                'work_phone',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relation',
                'baptism_status',
                'baptism_date',
                'baptism_location',
                'membership_type',
                'membership_end_date',
                'notes'
            ]);
            
            // Drop indexes
            $table->dropIndex(['first_name']);
            $table->dropIndex(['last_name']);
            $table->dropIndex(['email']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['membership_status_id']);
            
            // Drop soft deletes
            $table->dropSoftDeletes();
        });
    }
};
