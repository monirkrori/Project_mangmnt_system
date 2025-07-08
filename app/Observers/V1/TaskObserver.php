<?php

namespace App\Observers\V1;

use App\Events\V1\Task\TaskCompleted;
use App\Models\Task;

class TaskObserver
{

    public function creating(Task $task): void
    {
        if (is_null($task->status)) {
            $task->status = 'pending';
        }
    }


    public function updated(Task $task): void
    {
        if ($task->isDirty('status') && $task->status === 'completed') {
            event(new TaskCompleted($task));
        }
    }
}
