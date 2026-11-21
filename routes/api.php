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

Route::prefix('mobile/v1/study')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\Mobile\StudyController::class, 'login'])
        ->middleware('throttle:10,1');
    Route::post('/login/google', [\App\Http\Controllers\Api\Mobile\StudyController::class, 'googleLogin'])
        ->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        $controller = \App\Http\Controllers\Api\Mobile\StudyController::class;
        Route::delete('/session', [$controller, 'logout']);
        Route::get('/resume', [$controller, 'resume']);
        Route::get('/my-courses', [$controller, 'myCourses']);
        Route::get('/my-questions', [$controller, 'myQuestions']);
        Route::get('/lessons/{lesson:slug}', [$controller, 'status']);
        Route::get('/lessons/{lesson:slug}/certificate', [$controller, 'certificate']);
        Route::post('/lessons/{lesson:slug}/certificate', [$controller, 'claimCertificate']);
        Route::post('/lessons/{lesson:slug}/enroll', [$controller, 'enroll']);
        Route::get('/lessons/{lesson:slug}/topics/{slug}', [$controller, 'topic']);
        Route::post('/lessons/{lesson:slug}/topics/{slug}/questions', [$controller, 'askTopicQuestion'])
            ->middleware('throttle:10,1,study-question:');
        Route::get('/lessons/{lesson:slug}/quizzes/{quiz}', [$controller, 'quiz']);
        Route::post('/lessons/{lesson:slug}/quizzes/{quiz}', [$controller, 'submitQuiz']);
        Route::post('/lessons/{lesson:slug}/topics/{slug}/complete', [$controller, 'complete']);
    });
});
