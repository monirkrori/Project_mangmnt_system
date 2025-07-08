<?php

use App\Jobs\V1\ProcessLargeAttachmentJob;
use App\Models\Attachment;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-job', function () {
    $attachment = Attachment::latest()->first();
    ProcessLargeAttachmentJob::dispatch($attachment, ['compress']);
    return 'Job dispatched!';
});

