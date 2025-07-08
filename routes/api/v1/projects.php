<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ProjectController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/projects/active', [ProjectController::class, 'getActiveProjects']);
    Route::post('/projects/{project}/members', [ProjectController::class, 'addMember']);
    Route::apiResource('projects', ProjectController::class);

});
