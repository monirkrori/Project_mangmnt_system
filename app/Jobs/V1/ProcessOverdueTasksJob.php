<?php

namespace App\Jobs\V1;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOverdueTasksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    public function handle(): void
    {
        // تحديث المهام المتأخرة (التي لم تُنجز بعد)
        $overdueCount = Task::where('due_date', '<', now())
            ->where('status', '!=', 'completed')
            ->update(['is_overdue' => true]);

        // إعادة ضبط المهام غير المتأخرة
        $notOverdueCount = Task::where(function ($query) {
            $query->where('due_date', '>=', now())
                ->orWhere('status', 'completed');
        })
            ->update(['is_overdue' => false]);

        Log::info("✅ ProcessOverdueTasksJob completed", [
            'overdue_updated' => $overdueCount,
            'reset_to_false' => $notOverdueCount,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('❌ ProcessOverdueTasksJob failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
