<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Task;
use App\Models\Attachment;
use App\Observers\V1\TaskObserver;
use App\Observers\V1\AttachmentObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register model observers
        Task::observe(TaskObserver::class);
        Attachment::observe(AttachmentObserver::class);
    }
}
