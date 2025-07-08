<?php

namespace App\Listeners\V1;

use App\Events\V1\Project\ProjectUpdated;
use Illuminate\Support\Facades\Log;

class HandleProjectUpdates
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
    public function handle(ProjectUpdated $event): void
    {
       $changesString = http_build_query($event->changes, '', ', ');

        // مثال: تسجيل التغييرات التي تمت بالضبط
        Log::info("Project Updated: '{$event->project->name}' (ID: {$event->project->id}) by user '{$event->updater->name}'. Changes: [{$changesString}]");

    }
}
