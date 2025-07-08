<?php

use App\Jobs\V1\ProcessOverdueTasksJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



Schedule::job(new ProcessOverdueTasksJob)
    ->everyMinute()
    ->onSuccess(function () {
        Log::info('🕓 Overdue tasks processed successfully.');
    })
    ->onFailure(function () {
        Log::error('❌ Failed processing overdue tasks.');
    });



Schedule::command('app:clean-old-attachments')->daily();

Schedule::command('app:update-overdue-tasks')->daily();



