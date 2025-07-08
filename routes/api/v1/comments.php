<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CommentController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/{type}/{id}/comments', [CommentController::class, 'index']);
    Route::get('/{type}/{id}/comments/count', [CommentController::class, 'count']);
    Route::post('/{type}/{id}/comments', [CommentController::class, 'store']);

    Route::get('/comments/{comment}', [CommentController::class, 'show']);
    Route::patch('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
});
