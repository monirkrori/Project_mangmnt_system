<?php

namespace App\Jobs\V1;

use App\Mail\V1\TaskStatusChangedMail;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTaskStatusEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Task $task;

    /**
     * Create a new job instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Assuming the task has an assigned user with email
        $recipient = $this->task->assignedTo;

        if ($recipient && $recipient->email) {
            Mail::to($recipient->email)
                ->send(new TaskStatusChangedMail($this->task));
        }
    }
}
