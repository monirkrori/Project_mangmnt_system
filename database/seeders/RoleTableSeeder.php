<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleTableSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $owner = Role::firstOrCreate(['name' => 'team_owner']);
        $owner->syncPermissions([
            'create-team', 'update-team', 'delete-team', 'view-team',
            'invite-user', 'remove-user', 'view-users',
            'view-notifications',
            'manage-team-member'
        ]);

        $manager = Role::firstOrCreate(['name' => 'project_manager']);
        $manager->syncPermissions([
            'create-project', 'update-project', 'delete-project', 'view-project',
            'create-task', 'update-task', 'delete-task', 'view-task',
            'add-comment', 'update-comment', 'delete-comment',
            'add-attachment', 'delete-attachment',
            'view-notifications',
            'manage-project-member'
        ]);

        $member = Role::firstOrCreate(['name' => 'member']);
        $member->syncPermissions([
            'view-project', 'view-task',
            'add-comment', 'add-attachment',
            'view-notifications'
        ]);
    }
}
