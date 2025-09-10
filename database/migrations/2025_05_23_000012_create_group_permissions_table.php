<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('category')->default('general');
            $table->timestamps();
        });

        Schema::create('group_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->string('role');
            $table->foreignId('permission_id')->constrained('group_permissions')->onDelete('cascade');
            $table->timestamps();
            
            // A role can have a permission only once per group
            $table->unique(['group_id', 'role', 'permission_id']);
        });

        // Seed default permissions
        $this->seedDefaultPermissions();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_role_permissions');
        Schema::dropIfExists('group_permissions');
    }

    /**
     * Seed default permissions.
     *
     * @return void
     */
    private function seedDefaultPermissions()
    {
        $permissions = [
            // Member management
            ['name' => 'View Members', 'slug' => 'view-members', 'description' => 'Can view group members', 'category' => 'members'],
            ['name' => 'Add Members', 'slug' => 'add-members', 'description' => 'Can add new members to the group', 'category' => 'members'],
            ['name' => 'Remove Members', 'slug' => 'remove-members', 'description' => 'Can remove members from the group', 'category' => 'members'],
            ['name' => 'Edit Member Roles', 'slug' => 'edit-member-roles', 'description' => 'Can change roles of group members', 'category' => 'members'],
            
            // Attendance
            ['name' => 'View Attendance', 'slug' => 'view-attendance', 'description' => 'Can view group attendance records', 'category' => 'attendance'],
            ['name' => 'Record Attendance', 'slug' => 'record-attendance', 'description' => 'Can record attendance for group meetings', 'category' => 'attendance'],
            ['name' => 'Edit Attendance', 'slug' => 'edit-attendance', 'description' => 'Can edit existing attendance records', 'category' => 'attendance'],
            
            // Events
            ['name' => 'View Events', 'slug' => 'view-events', 'description' => 'Can view group events', 'category' => 'events'],
            ['name' => 'Create Events', 'slug' => 'create-events', 'description' => 'Can create new events for the group', 'category' => 'events'],
            ['name' => 'Edit Events', 'slug' => 'edit-events', 'description' => 'Can edit existing group events', 'category' => 'events'],
            ['name' => 'Delete Events', 'slug' => 'delete-events', 'description' => 'Can delete group events', 'category' => 'events'],
            ['name' => 'Manage Event Resources', 'slug' => 'manage-event-resources', 'description' => 'Can add/edit/delete resources for events', 'category' => 'events'],
            ['name' => 'Manage Event Registrations', 'slug' => 'manage-event-registrations', 'description' => 'Can manage registrations for events', 'category' => 'events'],
            ['name' => 'Manage Event Reminders', 'slug' => 'manage-event-reminders', 'description' => 'Can set up and manage event reminders', 'category' => 'events'],
            ['name' => 'Share Events', 'slug' => 'share-events', 'description' => 'Can share events with other groups or members', 'category' => 'events'],
            
            // Communications
            ['name' => 'View Messages', 'slug' => 'view-messages', 'description' => 'Can view group messages', 'category' => 'communications'],
            ['name' => 'Send Messages', 'slug' => 'send-messages', 'description' => 'Can send messages to group members', 'category' => 'communications'],
            ['name' => 'Manage Announcements', 'slug' => 'manage-announcements', 'description' => 'Can create and manage group announcements', 'category' => 'communications'],
            ['name' => 'Manage Prayer Requests', 'slug' => 'manage-prayer-requests', 'description' => 'Can manage prayer requests for the group', 'category' => 'communications'],
            ['name' => 'Share Documents', 'slug' => 'share-documents', 'description' => 'Can share documents with the group', 'category' => 'communications'],
            
            // Group settings
            ['name' => 'View Group Settings', 'slug' => 'view-group-settings', 'description' => 'Can view group settings', 'category' => 'settings'],
            ['name' => 'Edit Group Settings', 'slug' => 'edit-group-settings', 'description' => 'Can edit group settings', 'category' => 'settings'],
            ['name' => 'Manage Role Permissions', 'slug' => 'manage-role-permissions', 'description' => 'Can manage role permissions for the group', 'category' => 'settings'],
            
            // Analytics
            ['name' => 'View Analytics', 'slug' => 'view-analytics', 'description' => 'Can view group analytics', 'category' => 'analytics'],
            ['name' => 'Export Reports', 'slug' => 'export-reports', 'description' => 'Can export group reports', 'category' => 'analytics'],
        ];

        DB::table('group_permissions')->insert($permissions);
    }
}
