<?php

use App\Http\Controllers\Api\Mobile\PublicContentController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile/v1')
    ->middleware('throttle:60,1')
    ->name('mobile.v1.')
    ->controller(PublicContentController::class)
    ->group(function () {
        Route::get('/devotions', 'devotions')->name('devotions.index');
        Route::get('/devotions/{slug}', 'devotionDetail')->name('devotions.show');
        Route::get('/lessons', 'lessons')->name('lessons.index');
        Route::get('/lessons/{slug}', 'lessonDetail')->name('lessons.show');
        Route::get('/children', 'children')->name('children.index');
        Route::get('/children/{slug}', 'childDetail')->name('children.show');
    });
