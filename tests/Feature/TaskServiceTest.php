<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_assignable_users()
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'member']);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user1 = User::factory()->create();
        $user1->assignRole('member');

        $user2 = User::factory()->create();
        $user2->assignRole('member');

        $user3 = User::factory()->create();
        $user3->assignRole('member');

        $service = new TaskService();
        $assignableUsers = $service->getAssignableUsers($admin);

        $this->assertCount(3, $assignableUsers);
        $this->assertFalse($assignableUsers->contains('id', $admin->id));
    }

}
