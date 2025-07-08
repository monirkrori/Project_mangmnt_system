<?php

namespace App\Providers;

use App\Events\V1\CommentCreated;
use App\Events\V1\Task\TaskAssigned;
use App\Listeners\V1\NotifyTaskOfComment;
use App\Listeners\V1\SendTaskAssignedEmail;
use App\Listeners\V1\SendTaskAssignedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TaskAssigned::class => [
            SendTaskAssignedEmail::class,
            SendTaskAssignedNotification::class,
        ],
        CommentCreated::class => [
            NotifyTaskOfComment::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
