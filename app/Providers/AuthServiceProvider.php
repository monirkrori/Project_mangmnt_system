<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Comment;
use App\Models\Attachment;
use App\Models\Team;
use App\Policies\V1\ProjectPolicy;
use App\Policies\V1\TaskPolicy;
use App\Policies\V1\CommentPolicy;
use App\Policies\V1\AttachmentPolicy;
use App\Policies\V1\TeamPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
        Comment::class => CommentPolicy::class,
        Attachment::class => AttachmentPolicy::class,
        Team::class => TeamPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
