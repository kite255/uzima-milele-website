<?php

use App\Http\Controllers\InstructorEnrollmentLinkController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get(
        '/lessons/{lesson:slug}/join/{instructor}',
        [InstructorEnrollmentLinkController::class, 'join']
    )
        ->middleware('signed')
        ->name('lessons.instructor-join');

    Route::middleware('auth')->group(function () {
        Route::get(
            '/instructor/enrollment-links',
            [InstructorEnrollmentLinkController::class, 'index']
        )->name('instructor.enrollment-links.index');
    });
});
