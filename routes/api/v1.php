<?php

use Illuminate\Support\Facades\Route;

// API Version 1 grouping
Route::prefix('v1')->group(function () {
    require __DIR__ . '/v1/auth.php';
    require __DIR__ . '/v1/users.php';
    require __DIR__ . '/v1/teams.php';
    require __DIR__ . '/v1/projects.php';
    require __DIR__ . '/v1/tasks.php';
    require __DIR__ . '/v1/comments.php';
    require __DIR__ . '/v1/attachments.php';
});
