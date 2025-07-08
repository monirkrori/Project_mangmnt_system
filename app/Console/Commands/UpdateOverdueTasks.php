<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class UpdateOverdueTasks extends Command
{
    protected $signature = 'app:update-overdue-tasks';
    protected $description = 'Update task is_over status based on due_date';

    public function handle()
    {
        $now = now();

        $overdue = Task::where('due_date', '<', $now)
            ->where('is_overdue', false)
            ->update(['is_overdue' => true]);

        $reset = Task::where('due_date', '>=', $now)
            ->where('is_overdue', true)
            ->update(['is_overdue' => false]);

        Log::info('✅ UpdateOverdueTasks command executed', [
            'overdue_updated' => $overdue,
            'reset_to_false' => $reset,
        ]);

        $this->info("✅ Tasks updated. Overdue marked: $overdue | Reset to false: $reset");
    }
}
