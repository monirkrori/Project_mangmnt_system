<?php

namespace App\Mail\V1;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Task $task;

    /**
     * Create a new message instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Task Status Updated')
            ->markdown('emails.tasks.status-changed')
            ->with([
                'task' => $this->task,
            ]);
    }
}
