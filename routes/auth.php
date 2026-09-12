<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Guest Authentication Routes
|--------------------------------------------------------------------------
|
| These routes are available only to users who are not logged in.
|
| Route names remain Laravel's standard names so existing application code
| continues working. Only the browser URLs are translated into Kiswahili.
|
*/

Route::middleware('guest')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Registration / Jisajili
    |--------------------------------------------------------------------------
    */

    Route::get(
        'jisajili',
        [RegisteredUserController::class, 'create']
    )->name('register');


    Route::post(
        'jisajili',
        [RegisteredUserController::class, 'store']
    );


    /*
    |--------------------------------------------------------------------------
    | Login / Ingia
    |--------------------------------------------------------------------------
    */

    Route::get(
        'ingia',
        [AuthenticatedSessionController::class, 'create']
    )->name('login');


    Route::post(
        'ingia',
        [AuthenticatedSessionController::class, 'store']
    );


    /*
    |--------------------------------------------------------------------------
    | Forgot Password / Umesahau Nenosiri
    |--------------------------------------------------------------------------
    */

    Route::get(
        'umesahau-nenosiri',
        [PasswordResetLinkController::class, 'create']
    )->name('password.request');


    Route::post(
        'umesahau-nenosiri',
        [PasswordResetLinkController::class, 'store']
    )->name('password.email');


    /*
    |--------------------------------------------------------------------------
    | Reset Password / Weka Upya Nenosiri
    |--------------------------------------------------------------------------
    */

    Route::get(
        'weka-upya-nenosiri/{token}',
        [NewPasswordController::class, 'create']
    )->name('password.reset');


    Route::post(
        'weka-upya-nenosiri',
        [NewPasswordController::class, 'store']
    )->name('password.store');
});


/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
|
| These routes require the user to already be authenticated.
|
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Email Verification / Thibitisha Barua Pepe
    |--------------------------------------------------------------------------
    */

    Route::get(
        'thibitisha-barua-pepe',
        EmailVerificationPromptController::class
    )->name('verification.notice');


    Route::get(
        'thibitisha-barua-pepe/{id}/{hash}',
        VerifyEmailController::class
    )
        ->middleware([
            'signed',
            'throttle:6,1',
        ])
        ->name('verification.verify');


    Route::post(
        'barua-pepe/tuma-uthibitisho',
        [EmailVerificationNotificationController::class, 'store']
    )
        ->middleware('throttle:6,1')
        ->name('verification.send');


    /*
    |--------------------------------------------------------------------------
    | Confirm Password / Thibitisha Nenosiri
    |--------------------------------------------------------------------------
    */

    Route::get(
        'thibitisha-nenosiri',
        [ConfirmablePasswordController::class, 'show']
    )->name('password.confirm');


    Route::post(
        'thibitisha-nenosiri',
        [ConfirmablePasswordController::class, 'store']
    );


    /*
    |--------------------------------------------------------------------------
    | Update Password / Badili Nenosiri
    |--------------------------------------------------------------------------
    */

    Route::put(
        'nenosiri',
        [PasswordController::class, 'update']
    )->name('password.update');


    /*
    |--------------------------------------------------------------------------
    | Logout / Ondoka
    |--------------------------------------------------------------------------
    */

    Route::post(
        'ondoka',
        [AuthenticatedSessionController::class, 'destroy']
    )->name('logout');
});


/*
|--------------------------------------------------------------------------
| Legacy English Authentication URLs
|--------------------------------------------------------------------------
|
| These routes preserve older browser bookmarks and existing links.
|
| New application-generated URLs will use Kiswahili because the named
| routes above now point to the Kiswahili paths.
|
*/

/*
|--------------------------------------------------------------------------
| Register
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->get(
    'register',
    function () {
        return redirect()->route(
            'register',
            [],
            301
        );
    }
);


/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->get(
    'login',
    function () {
        return redirect()->route(
            'login',
            [],
            301
        );
    }
);


/*
|--------------------------------------------------------------------------
| Forgot Password
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->get(
    'forgot-password',
    function () {
        return redirect()->route(
            'password.request',
            [],
            301
        );
    }
);


/*
|--------------------------------------------------------------------------
| Old Password Reset Links
|--------------------------------------------------------------------------
|
| We keep the old reset URL functional instead of simply redirecting it.
| This is safer for previously generated password-reset links.
|
*/

Route::middleware('guest')->get(
    'reset-password/{token}',
    [NewPasswordController::class, 'create']
);


/*
|--------------------------------------------------------------------------
| Old Email Verification Links
|--------------------------------------------------------------------------
|
| Existing signed verification emails may still contain this URL.
| Keeping the route allows those existing links to remain valid.
|
*/

Route::middleware('auth')->get(
    'verify-email/{id}/{hash}',
    VerifyEmailController::class
)
    ->middleware([
        'signed',
        'throttle:6,1',
    ]);


/*
|--------------------------------------------------------------------------
| Old Email Verification Notice
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->get(
    'verify-email',
    function () {
        return redirect()->route(
            'verification.notice',
            [],
            301
        );
    }
);


/*
|--------------------------------------------------------------------------
| Old Confirm Password URL
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->get(
    'confirm-password',
    function () {
        return redirect()->route(
            'password.confirm',
            [],
            301
        );
    }
);