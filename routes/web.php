<?php

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DevotionController;
use App\Http\Controllers\InstructorDashboardController;
use App\Http\Controllers\InstructorQuestionController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\LessonQuestionController;
use App\Http\Controllers\LessonTopicController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PrayerRequestController;
use App\Http\Controllers\PrayerTestimonyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\WatotoController;
use App\Models\Devotion;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $today = now('Africa/Dar_es_Salaam')->toDateString();

    $latestDevotions = Devotion::query()
        ->whereNotNull('published_at')
        ->whereDate('published_at', '<=', $today)
        ->orderByDesc('published_at')
        ->take(6)
        ->get();

    return view('home', compact('latestDevotions'));
})->name('home');

/*
|--------------------------------------------------------------------------
| Social Login
|--------------------------------------------------------------------------
*/
Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])
    ->name('google.login');

Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])
    ->name('google.callback');

Route::get('/auth/facebook', [SocialAuthController::class, 'redirectToFacebook'])
    ->name('facebook.login');

Route::get('/auth/facebook/callback', [SocialAuthController::class, 'handleFacebookCallback'])
    ->name('facebook.callback');

/*
|--------------------------------------------------------------------------
| Default Dashboard Redirect
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->role === 'admin') {
        return redirect('/admin');
    }

    if ($user->role === 'instructor') {
        return redirect()->route('instructor.dashboard');
    }

    return redirect()->route('student.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Static Pages
|--------------------------------------------------------------------------
*/
Route::view('/about', 'about')->name('about');
Route::view('/contact', 'contact')->name('contact');
Route::view('/changia', 'donation')->name('changia');

/*
|--------------------------------------------------------------------------
| Maombi na Ushuhuda
|--------------------------------------------------------------------------
*/
Route::get('/maombi-na-ushuhuda', [PrayerTestimonyController::class, 'index'])
    ->name('prayers.testimonies');

Route::post('/maombi', [PrayerRequestController::class, 'store'])
    ->name('prayers.store');

Route::post('/ushuhuda', [TestimonialController::class, 'store'])
    ->name('testimonials.store');

/*
|--------------------------------------------------------------------------
| Public Certificate Verification
|--------------------------------------------------------------------------
| This route must remain public because QR codes open this page without login.
|--------------------------------------------------------------------------
*/
Route::get('/certificates/verify/{certificateNumber}', [CertificateController::class, 'verify'])
    ->name('certificates.verify');

/*
|--------------------------------------------------------------------------
| Lessons - Public + Authenticated Learning Flow
|--------------------------------------------------------------------------
*/
Route::prefix('lessons')->name('lessons.')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Public Lesson Pages
    |--------------------------------------------------------------------------
    */
    Route::get('/', [LessonController::class, 'index'])
        ->name('index');

    Route::get('/{lesson:slug}', [LessonController::class, 'show'])
        ->name('show');

    /*
    |--------------------------------------------------------------------------
    | Authenticated Lesson Learning Pages
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth'])->group(function () {
        Route::get('/{lesson:slug}/learn', [LessonController::class, 'learn'])
            ->name('learn');

        Route::post('/{lesson:slug}/enroll', [LessonController::class, 'enroll'])
            ->name('enroll');

        Route::post('/{lesson:slug}/questions', [LessonQuestionController::class, 'store'])
            ->name('questions.store');

        Route::post('/{lesson:slug}/progress', [LessonController::class, 'markProgress'])
            ->name('progress');

        Route::patch('/{lesson:slug}/schedule', [LessonController::class, 'resetSchedule'])
            ->name('schedule.reset');

        /*
        |--------------------------------------------------------------------------
        | Student Topic Reading Flow
        |--------------------------------------------------------------------------
        */
        Route::get('/{lesson:slug}/topics/{topic:slug}', [LessonTopicController::class, 'show'])
            ->name('topics.show');

        Route::post('/{lesson:slug}/topics/{topic:slug}/complete', [LessonTopicController::class, 'complete'])
            ->name('topics.complete');
    });
});

/*
|--------------------------------------------------------------------------
| Children / Watoto
|--------------------------------------------------------------------------
*/
Route::prefix('children')->name('children.')->group(function () {
    Route::get('/', [WatotoController::class, 'index'])
        ->name('index');

    Route::get('/{slug}', [WatotoController::class, 'show'])
        ->name('show');

    Route::post('/{slug}/quiz', [WatotoController::class, 'submitQuiz'])
        ->name('quiz.submit');
});

/*
|--------------------------------------------------------------------------
| Devotions
|--------------------------------------------------------------------------
*/
Route::prefix('devotions')->name('devotions.')->group(function () {
    Route::get('/', [DevotionController::class, 'index'])
        ->name('index');

    Route::get('/{slug}', [DevotionController::class, 'show'])
        ->name('show');
});

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Student Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])
        ->name('student.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Instructor Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/instructor/dashboard', [InstructorDashboardController::class, 'index'])
        ->name('instructor.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Instructor Q&A
    |--------------------------------------------------------------------------
    */
    Route::get('/instructor/questions', [InstructorQuestionController::class, 'index'])
        ->name('instructor.questions.index');

    Route::get('/instructor/questions/{question}', [InstructorQuestionController::class, 'show'])
        ->name('instructor.questions.show');

    Route::put('/instructor/questions/{question}', [InstructorQuestionController::class, 'update'])
        ->name('instructor.questions.update');

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::get('/notifications/{notification}/read', [NotificationController::class, 'read'])
        ->name('notifications.read');

    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.markAllRead');

    /*
    |--------------------------------------------------------------------------
    | Quizzes
    |--------------------------------------------------------------------------
    */
    Route::get('/quiz/{quiz}', [QuizController::class, 'show'])
        ->name('quiz.show');

    Route::post('/quiz/{quiz}/submit', [QuizController::class, 'submit'])
        ->name('quiz.submit');

    /*
    |--------------------------------------------------------------------------
    | Certificates
    |--------------------------------------------------------------------------
    */
    Route::post('/lessons/{lesson}/certificate', [CertificateController::class, 'issue'])
        ->name('certificates.issue');

    Route::get('/certificates/{certificateNumber}', [CertificateController::class, 'show'])
        ->name('certificates.show');

    Route::get('/certificates/{certificateNumber}/download', [CertificateController::class, 'download'])
        ->name('certificates.download');

    /*
    |--------------------------------------------------------------------------
    | Certificate Print Preview
    |--------------------------------------------------------------------------
    | Use this only for testing the Browsershot print view in browser.
    |--------------------------------------------------------------------------
    */
    Route::get('/certificates/{certificateNumber}/print-preview', function (string $certificateNumber) {
        $certificate = \App\Models\Certificate::with(['user', 'lesson'])
            ->where('certificate_number', $certificateNumber)
            ->firstOrFail();

        abort_if($certificate->user_id !== auth()->id(), 403);

        return view('certificates.print', compact('certificate'));
    })->name('certificates.print-preview');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';