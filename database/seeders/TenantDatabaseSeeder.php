<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->addRolesAndPermissions();
    }

    /**
     * Adds the roles and permissions
     *
     * Every user has the basic user role and permissions. These are then extendet by
     * one additional roles like the admin role, etc.
     *
     * The naming of the permissions must match the pattern controller.function. However
     * the 'controller' part must be all lowercase and musn't have an instance of the word 'controller'.
     *
     * @return void
     */
    private function addRolesAndPermissions()
    {
        // Create basic permissions for users
        $userPermissions = collect(['feedback.store', 'projects.index', 'projects.show', 'timeframes.show', 'events.index'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Add a default user role
        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo($userPermissions);


        // Create permissions (regarding projects) for attendants
        $attendantProjectPermissions = collect(['projects.store'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Create permissions (regarding friendships) for attendants
        $attendantFriendshipPermissions = collect(['friendships.show_associated', 'friendships.store', 'friendships.accept', 'friendships.destroy'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Create permissions (regarding preferences) for attendants
        $attendantPreferencesPermissions = collect(['preferences.show_associated', 'preferences.store', 'preferences.destroy'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Create permissions (regarding timeframes) for attendants
        $attendantTimeframePermissions = collect(['timeframes.store'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Add attendant role
        $attendantRole = Role::create(['name' => 'attendant']);
        $attendantRole->givePermissionTo($attendantProjectPermissions);
        $attendantRole->givePermissionTo($attendantFriendshipPermissions);
        $attendantRole->givePermissionTo($attendantPreferencesPermissions);
        $attendantRole->givePermissionTo($attendantTimeframePermissions);
        // Add guest attendant role
        $guestAttendantRole = Role::create(['name' => 'guestAttendant']);
        $guestAttendantRole->givePermissionTo($attendantProjectPermissions);
        $guestAttendantRole->givePermissionTo($attendantTimeframePermissions);


        // Create permissions (regarding projects) for participants
        $participantProjectPermissions = collect(['projects.show_associated'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Add participant role
        $participantRole = Role::create(['name' => 'participant']);
        $participantRole->givePermissionTo($participantProjectPermissions);


        // Create permissions (regarding projects) for assistants
        $assistantProjectPermissions = collect(['projects.show_associated'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Create permissions (regarding preferences) for assistants
        $assistantPreferencesPermissions = collect(['preferences.show_associated', 'preferences.store', 'preferences.destroy'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Add assistant role
        $assistantRole = Role::create(['name' => 'assistant']);
        $assistantRole->givePermissionTo($assistantProjectPermissions);
        $assistantRole->givePermissionTo($assistantPreferencesPermissions);


        // Create permissions (regarding projects) for leaders
        $leaderProjectPermissions = collect(['projects.show_associated', 'projects.update_associated'])->map(function ($name) {
            if (!Permission::where('name', $name)->exists()) {
                return $this->createPermission($name);
            } else {
                return Permission::where('name', $name)->first();
            }
        });
        // Create permissions (regarding preferences) for leaders
        $leaderPreferencesPermissions = collect(['preferences.show_associated', 'preferences.store', 'preferences.destroy'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Create permissions (regarding timeframes) for leaders
        $leaderTimeframePermissions = collect(['timeframes.store', 'timeframes.update', 'timeframes.destroy'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Add leader role
        $leaderRole = Role::create(['name' => 'leader']);
        $leaderRole->givePermissionTo($leaderProjectPermissions);
        $leaderRole->givePermissionTo($leaderPreferencesPermissions);
        $leaderRole->givePermissionTo($leaderTimeframePermissions);
        // Add guest leader role
        $guestLeaderRole = Role::create(['name' => 'guestLeader']);
        $guestLeaderRole->givePermissionTo($leaderProjectPermissions);
        $guestLeaderRole->givePermissionTo($leaderTimeframePermissions);


        // Create permissions (regarding users) for admins
        $adminUserPermissions = collect(['users.index'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Create permissions (regarding feedback) for admins
        $adminFeedbackPermissions = collect(['feedback.index', 'feedback.show', 'feedback.destroy'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Create permissions (regarding projects) for admins
        $adminProjectPermissions = collect(['projects.toggleAuthorized', 'projects.destroy'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Create permissions (regarding friendships) for admins
        $adminFriendshipPermissions = collect(['friendships.index', 'friendships.show', 'friendships.authorise', 'friendships.decline'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Create permissions (regarding events) for admins
        $adminEventPermissions = collect(['events.store', 'events.update', 'events.syncPermissions', 'events.destroy'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Create permissions (regarding preferences) for admins
        $adminPreferencesPermissions = collect(['preferences.index', 'preferences.show'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Create permissions (regarding roles and permissions) for admins
        $adminRolesAndPermissionsPermissions = collect(['permissions.index', 'roles.index', 'roles.store', 'roles.togglePermission', 'roles.destroy'])->map(function ($name) {
            return $this->createPermission($name);
        });
        // Add admin role
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($adminUserPermissions);
        $adminRole->givePermissionTo($adminFeedbackPermissions);
        $adminRole->givePermissionTo($adminProjectPermissions);
        $adminRole->givePermissionTo($adminFriendshipPermissions);
        $adminRole->givePermissionTo($adminEventPermissions);
        $adminRole->givePermissionTo($adminPreferencesPermissions);
        $adminRole->givePermissionTo($adminRolesAndPermissionsPermissions);


        // unused permissions
        $this->createPermission('projects.authorize_associated');
    }

    /**
     * Creates a permission if it doesn't already exist.
     *
     * @return Permission
     */
    private function createPermission($name) {
        if (!Permission::where('name', $name)->exists()) {
            return Permission::create(['name' => $name]);
        } else {
            return Permission::where('name', $name)->first();
        }
    }
}
