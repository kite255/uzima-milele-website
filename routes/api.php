<?php

use App\Http\Controllers\Api\Mobile\PublicContentController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile/v1')
    ->middleware('throttle:60,1')
    ->name('mobile.v1.')
    ->group(function () {
        Route::get('/lessons', [PublicContentController::class, 'lessons'])
            ->name('lessons.index');

        Route::get('/lessons/{slug}', [PublicContentController::class, 'lessonDetail'])
            ->name('lessons.show');
    });

Route::prefix('mobile/v1/study')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\Mobile\StudyController::class, 'login'])
        ->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        $controller = \App\Http\Controllers\Api\Mobile\StudyController::class;
        Route::delete('/session', [$controller, 'logout']);
        Route::get('/resume', [$controller, 'resume']);
        Route::get('/lessons/{lesson:slug}', [$controller, 'status']);
        Route::post('/lessons/{lesson:slug}/enroll', [$controller, 'enroll']);
        Route::get('/lessons/{lesson:slug}/topics/{slug}', [$controller, 'topic']);
        Route::post('/lessons/{lesson:slug}/topics/{slug}/complete', [$controller, 'complete']);
    });
});
