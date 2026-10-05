<?php

use App\Http\Controllers\InstructorEnrollmentLinkController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get(
        '/join/{code}',
        [InstructorEnrollmentLinkController::class, 'join']
    )
        ->where('code', '[A-Za-z0-9]{6}')
        ->name('lessons.instructor-join');

    Route::middleware('auth')->group(function () {
        Route::get(
            '/instructor/enrollment-links',
            [InstructorEnrollmentLinkController::class, 'index']
        )->name('instructor.enrollment-links.index');
    });
});
