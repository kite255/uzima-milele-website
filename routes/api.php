
<?php

use App\Http\Controllers\Api\Mobile\PublicContentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Uzima Milele Mobile API - Version 1
|--------------------------------------------------------------------------
|
| Public endpoints for published ministry content.
| Protected student and account features will use authenticated routes.
|
*/

Route::prefix('mobile/v1')
    ->middleware('throttle:60,1')
    ->name('mobile.v1.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Tafakari - Devotions
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/devotions',
            [PublicContentController::class, 'devotions']
        )->name('devotions.index');

        Route::get(
            '/devotions/{slug}',
            [PublicContentController::class, 'devotionDetail']
        )->name('devotions.show');

        /*
        |--------------------------------------------------------------------------
        | Masomo ya Biblia - Bible Lessons
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/lessons',
            [PublicContentController::class, 'lessons']
        )->name('lessons.index');

        Route::get(
            '/lessons/{slug}',
            [PublicContentController::class, 'lessonDetail']
        )->name('lessons.show');

        /*
        |--------------------------------------------------------------------------
        | Uzima Milele Watoto
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/children',
            [PublicContentController::class, 'children']
        )->name('children.index');

        Route::get(
            '/children/{slug}',
            [PublicContentController::class, 'childDetail']
        )->name('children.show');

        /*
        |--------------------------------------------------------------------------
        | Ushuhuda - Published Testimonies
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/testimonials',
            [PublicContentController::class, 'testimonials']
        )->name('testimonials.index');
    });
