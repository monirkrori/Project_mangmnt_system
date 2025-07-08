<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TeamController;

// Routes for team management
Route::post('/teams/{team}/add-member', [TeamController::class, 'addMember']);
Route::post('/teams/{team}/remove-member', [TeamController::class, 'removeMember']);
Route::apiResource('teams', TeamController::class);

