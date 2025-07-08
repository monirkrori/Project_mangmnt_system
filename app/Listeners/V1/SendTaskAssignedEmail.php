<?php

namespace App\Listeners\V1;

use App\Events\V1\Task\TaskAssigned;
use App\Mail\V1\TaskAssignedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTaskAssignedEmail implements ShouldQueue
{
    public function handle(TaskAssigned $event): void
    {
        $task = $event->task;

        // Log: بداية تنفيذ الليسنر
        Log::info('📥 Listener: SendTaskAssignedEmail started', [
            'task_id' => $task->id,
        ]);

        // تحقق من وجود المستخدم
        if (! $task->assignedUser || ! $task->assignedUser->email) {
            Log::warning('❌ No assigned user or email found', [
                'task_id' => $task->id,
            ]);
            return;
        }

        try {
            // Log: محاولة إرسال الإيميل
            Log::info('📧 Attempting to send email to assigned user', [
                'email' => $task->assignedUser->email,
                'task_name' => $task->name,
            ]);

            Mail::to($task->assignedUser->email)
                ->send(new TaskAssignedMail($task));

            // Log: نجاح الإرسال
            Log::info('✅ Email sent successfully to assigned user', [
                'task_id' => $task->id,
            ]);

        } catch (\Exception $e) {
            // Log: في حالة الفشل
            Log::error('🚨 Failed to send task assigned email', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
            ]);

            throw $e; // حتى يُعاد المحاولة تلقائيًا من الـ queue
        }
    }
}
