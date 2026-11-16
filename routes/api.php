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
