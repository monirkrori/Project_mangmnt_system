<?php

namespace App\Listeners\V1;

use App\Events\V1\Project\ProjectCreated;
use Illuminate\Support\Facades\Log;

class HandleProjectCreation
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProjectCreated $event): void
    {
      Log::info("Project Created: '{$event->project->name}' (ID: {$event->project->id}) by user '{$event->creator->name}' (ID: {$event->creator->id}).");
    }
}
