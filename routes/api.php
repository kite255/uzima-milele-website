<?php

use App\Http\Controllers\Api\Mobile\PublicContentController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile/v1')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::get('/devotions', [PublicContentController::class, 'devotions']);
        Route::get('/lessons', [PublicContentController::class, 'lessons']);
    });
