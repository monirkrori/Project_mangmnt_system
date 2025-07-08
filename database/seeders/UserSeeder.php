<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Create project manager
        $pm = User::create([
            'name' => 'Project Manager',
            'email' => 'pm@example.com',
            'password' => Hash::make('password'),
        ]);
        $pm->assignRole('project_manager');

        // Create team owner
        $teamOwner = User::create([
            'name' => 'Team Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
        ]);
        $teamOwner->assignRole('team_owner');

        User::factory(10)->create()->each(function ($user) {
            $user->assignRole('member');
        });
    }
}
