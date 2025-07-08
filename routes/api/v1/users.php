<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AdminUserController;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('users/{user}/toggle', [AdminUserController::class, 'toggleStatus']);
    Route::apiResource('users', AdminUserController::class);
});
