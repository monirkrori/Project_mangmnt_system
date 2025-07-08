<?php

namespace Tests\Unit;

use App\Models\Task;
use PHPUnit\Framework\TestCase;

class TaskModelTest extends TestCase
{

    public function test_priority_accessor_and_mutator()
    {
        $task = new Task(['priority' => 'HIGH']);
        $this->assertEquals('High', $task->priority);
    }

    public function test_status_text_accessor()
    {
        $task = new Task(['status' => 'in_progress']);
        $this->assertEquals('In progress', $task->status_text);
    }
}
