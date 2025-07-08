<?php

namespace App\Mail\V1;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function build()
    {
        return $this->subject('you have a new task ' . $this->task->name)
            ->view('emails.tasks.task-assigned')
            ->with(['task' => $this->task]);
    }
}
