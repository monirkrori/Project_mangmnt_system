<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AttachmentController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/{type}/{id}/attachments', [AttachmentController::class, 'store']);
    Route::patch('/attachments/{attachment}', [AttachmentController::class, 'update']);
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show']);
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);
});
