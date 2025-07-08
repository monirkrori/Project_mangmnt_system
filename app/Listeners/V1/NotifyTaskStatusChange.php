<?php

namespace App\Listeners\V1;

use App\Events\V1\Task\TaskStatusUpdated;
use App\Jobs\V1\SendTaskStatusEmail;
use Illuminate\Support\Facades\Log;

class NotifyTaskStatusChange
{
    /**
     * Handle the event.
     */
    public function handle(TaskStatusUpdated $event): void
    {
        $task = $event->task;

        // Log info for debug
        Log::info("Task status changed", [
            'task_id' => $task->id,
            'new_status' => $task->status,
            'updated_by' => auth()->user()?->id
        ]);

        // Dispatch email job
        SendTaskStatusEmail::dispatch($task);
    }
}
