<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TaskController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('tasks/overdue', [TaskController::class, 'overdue']);
    Route::get('tasks/completed', [TaskController::class, 'completed']);
    Route::get('tasks/high-priority', [TaskController::class, 'highPriority']);
    Route::get('tasks/{task}/latest-attachment', [TaskController::class, 'latestAttachment']);
    Route::apiResource('tasks', TaskController::class);

});
