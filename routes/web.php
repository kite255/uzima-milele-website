<?php

use App\Http\Controllers\AdminCenterDashboardController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DevotionController;
use App\Http\Controllers\EmailSubscriberController;
use App\Http\Controllers\InstructorDashboardController;
use App\Http\Controllers\InstructorQuestionController;
use App\Http\Controllers\InstructorStudentController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\LessonQuestionController;
use App\Http\Controllers\LessonTopicController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PrayerRequestController;
use App\Http\Controllers\PrayerTestimonyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\WatotoController;

use App\Models\Certificate;
use App\Models\Devotion;
use App\Models\EmailSubscriber;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Home / Nyumbani
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

    return view(
        'home',
        compact('latestDevotions')
    );
})->name('home');


/*
|--------------------------------------------------------------------------
| Google Login
|--------------------------------------------------------------------------
|
| Keep OAuth callback URLs technical because they are configured with Google.
|
*/
Route::get(
    '/auth/google',
    [GoogleAuthController::class, 'redirect']
)->name('google.login');

Route::get(
    '/auth/google/callback',
    [GoogleAuthController::class, 'callback']
)->name('google.callback');


/*
|--------------------------------------------------------------------------
| Email Subscription / Usajili wa Barua Pepe
|--------------------------------------------------------------------------
|
| Public subscription page, subscription endpoint, unsubscribe link and
| subscriber preference management.
|
*/
Route::view(
    '/jiandikishe-tafakari',
    'subscriptions.create'
)->name('subscriptions.create');

Route::post(
    '/jiandikishe',
    [EmailSubscriberController::class, 'store']
)->name('email-subscribers.store');


/*
|--------------------------------------------------------------------------
| Unsubscribe / Kujiondoa
|--------------------------------------------------------------------------
*/
Route::get(
    '/email/unsubscribe/{token}',
    [EmailSubscriberController::class, 'unsubscribe']
)->name('email-subscribers.unsubscribe');


/*
|--------------------------------------------------------------------------
| Email Preferences / Mapendeleo ya Barua Pepe
|--------------------------------------------------------------------------
*/
Route::get(
    '/email/preferences/{token}',
    [EmailSubscriberController::class, 'preferences']
)->name('email-subscribers.preferences');

Route::patch(
    '/email/preferences/{token}',
    [EmailSubscriberController::class, 'updatePreferences']
)->name('email-subscribers.preferences.update');


/*
|--------------------------------------------------------------------------
| Dashboard Redirect
|--------------------------------------------------------------------------
*/
Route::get('/dashibodi', function () {
    $user = auth()->user();

    if (! $user) {
        return redirect()->route('login');
    }

    if ($user->role === 'admin') {
        return redirect('/admin');
    }

    if ($user->role === 'instructor') {
        return redirect()->route(
            'instructor.dashboard'
        );
    }

    return redirect()->route(
        'student.dashboard'
    );
})
    ->middleware('auth')
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| Static Public Pages
|--------------------------------------------------------------------------
*/
Route::view(
    '/kuhusu-sisi',
    'about'
)->name('about');

Route::view(
    '/wasiliana-nasi',
    'contact'
)->name('contact');

Route::view(
    '/changia',
    'donation'
)->name('changia');


/*
|--------------------------------------------------------------------------
| Maombi na Ushuhuda
|--------------------------------------------------------------------------
*/
Route::get(
    '/maombi-na-ushuhuda',
    [PrayerTestimonyController::class, 'index']
)->name('prayers.testimonies');

Route::post(
    '/maombi',
    [PrayerRequestController::class, 'store']
)->name('prayers.store');

Route::post(
    '/ushuhuda',
    [TestimonialController::class, 'store']
)->name('testimonials.store');


/*
|--------------------------------------------------------------------------
| Public Certificate Verification / Uhakiki wa Cheti
|--------------------------------------------------------------------------
*/
Route::get(
    '/vyeti/hakiki/{certificateNumber}',
    [CertificateController::class, 'verify']
)->name('certificates.verify');


/*
|--------------------------------------------------------------------------
| Lessons / Jifunze Biblia
|--------------------------------------------------------------------------
*/
Route::prefix('jifunze-biblia')
    ->name('lessons.')
    ->group(function () {

        Route::get(
            '/',
            [LessonController::class, 'index']
        )->name('index');

        Route::get(
            '/{lesson:slug}',
            [LessonController::class, 'show']
        )->name('show');

        Route::middleware('auth')
            ->group(function () {

                Route::get(
                    '/{lesson:slug}/jifunze',
                    [LessonController::class, 'learn']
                )->name('learn');

                Route::post(
                    '/{lesson:slug}/jiunge',
                    [LessonController::class, 'enroll']
                )->name('enroll');

                Route::post(
                    '/{lesson:slug}/maswali',
                    [LessonQuestionController::class, 'store']
                )->name('questions.store');

                Route::post(
                    '/{lesson:slug}/maendeleo',
                    [LessonController::class, 'markProgress']
                )->name('progress');

                Route::patch(
                    '/{lesson:slug}/ratiba',
                    [LessonController::class, 'resetSchedule']
                )->name('schedule.reset');

                Route::get(
                    '/{lesson:slug}/mada/{topic:slug}',
                    [LessonTopicController::class, 'show']
                )->name('topics.show');

                Route::post(
                    '/{lesson:slug}/mada/{topic:slug}/kamilisha',
                    [LessonTopicController::class, 'complete']
                )->name('topics.complete');
            });
    });


/*
|--------------------------------------------------------------------------
| Watoto
|--------------------------------------------------------------------------
*/
Route::prefix('watoto')
    ->name('children.')
    ->group(function () {

        Route::get(
            '/',
            [WatotoController::class, 'index']
        )->name('index');

        Route::get(
            '/{slug}',
            [WatotoController::class, 'show']
        )->name('show');

        Route::post(
            '/{slug}/jaribio',
            [WatotoController::class, 'submitQuiz']
        )->name('quiz.submit');
    });


/*
|--------------------------------------------------------------------------
| Tafakari
|--------------------------------------------------------------------------
*/
Route::prefix('tafakari')
    ->name('devotions.')
    ->group(function () {

        Route::get(
            '/',
            [DevotionController::class, 'index']
        )->name('index');

        Route::get(
            '/{slug}',
            [DevotionController::class, 'show']
        )->name('show');
    });


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Devotion Email Preview
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/admin/devotions/{devotion}/email-preview',
            function (Devotion $devotion) {

                $subscriber = EmailSubscriber::query()
                    ->where('status', 'subscribed')
                    ->first();

                return view(
                    'emails.devotions.daily',
                    [
                        'devotion' => $devotion,
                        'subscriber' => $subscriber,
                    ]
                );
            }
        )->name('devotions.email.preview');


        /*
        |--------------------------------------------------------------------------
        | Admin Center Dashboard
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/kituo-cha-msimamizi/dashibodi',
            [AdminCenterDashboardController::class, 'index']
        )->name('admin.center.dashboard');


        /*
        |--------------------------------------------------------------------------
        | Student Dashboard
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/mwanafunzi/dashibodi',
            [StudentDashboardController::class, 'index']
        )->name('student.dashboard');


        /*
        |--------------------------------------------------------------------------
        | Instructor Dashboard
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/mwalimu/dashibodi',
            [InstructorDashboardController::class, 'index']
        )->name('instructor.dashboard');


        Route::get(
            '/mwalimu/timu/{lesson}/{instructor}/wanafunzi',
            [InstructorDashboardController::class, 'teamStudents']
        )->name('instructor.team.students');


        Route::get(
            '/mwalimu/wanafunzi/{enrollment}',
            [InstructorStudentController::class, 'show']
        )->name('instructor.students.show');


        Route::post(
            '/mwalimu/wanafunzi/{enrollment}/ufuatiliaji',
            [InstructorStudentController::class, 'storeFollowUp']
        )->name(
            'instructor.students.follow-ups.store'
        );


        /*
        |--------------------------------------------------------------------------
        | Instructor Questions
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/mwalimu/maswali',
            [InstructorQuestionController::class, 'index']
        )->name('instructor.questions.index');

        Route::get(
            '/mwalimu/maswali/{question}',
            [InstructorQuestionController::class, 'show']
        )->name('instructor.questions.show');

        Route::put(
            '/mwalimu/maswali/{question}',
            [InstructorQuestionController::class, 'update']
        )->name('instructor.questions.update');


        /*
        |--------------------------------------------------------------------------
        | Notifications / Arifa
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/arifa',
            [NotificationController::class, 'index']
        )->name('notifications.index');

        Route::get(
            '/arifa/{notification}/soma',
            [NotificationController::class, 'read']
        )->name('notifications.read');

        Route::post(
            '/arifa/soma-zote',
            [NotificationController::class, 'markAllAsRead']
        )->name('notifications.markAllRead');


        /*
        |--------------------------------------------------------------------------
        | Quizzes / Majaribio
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/jaribio/{quiz}',
            [QuizController::class, 'show']
        )->name('quiz.show');

        Route::post(
            '/jaribio/{quiz}/wasilisha',
            [QuizController::class, 'submit']
        )->name('quiz.submit');


        /*
        |--------------------------------------------------------------------------
        | Certificates / Vyeti
        |--------------------------------------------------------------------------
        */
        Route::post(
            '/jifunze-biblia/{lesson}/cheti',
            [CertificateController::class, 'issue']
        )->name('certificates.issue');

        Route::get(
            '/vyeti/{certificateNumber}',
            [CertificateController::class, 'show']
        )->name('certificates.show');

        Route::get(
            '/vyeti/{certificateNumber}/pakua',
            [CertificateController::class, 'download']
        )->name('certificates.download');

        Route::get(
            '/vyeti/{certificateNumber}/hakiki-uchapishaji',
            function (string $certificateNumber) {

                $certificate = Certificate::with([
                    'user',
                    'lesson',
                ])
                    ->where(
                        'certificate_number',
                        $certificateNumber
                    )
                    ->firstOrFail();

                abort_if(
                    $certificate->user_id !== auth()->id(),
                    403
                );

                return view(
                    'certificates.print',
                    compact('certificate')
                );
            }
        )->name('certificates.print-preview');


        /*
        |--------------------------------------------------------------------------
        | Font Sample
        |--------------------------------------------------------------------------
        */
        Route::get('/font-sample', function () {
            return view('font-sample');
        });


        /*
        |--------------------------------------------------------------------------
        | Profile / Wasifu
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/wasifu',
            [ProfileController::class, 'edit']
        )->name('profile.edit');

        Route::patch(
            '/wasifu',
            [ProfileController::class, 'update']
        )->name('profile.update');

        Route::delete(
            '/wasifu',
            [ProfileController::class, 'destroy']
        )->name('profile.destroy');
    });


/*
|--------------------------------------------------------------------------
| Legacy English URL Redirects
|--------------------------------------------------------------------------
*/
Route::redirect(
    '/about',
    '/kuhusu-sisi',
    301
);

Route::redirect(
    '/contact',
    '/wasiliana-nasi',
    301
);

Route::redirect(
    '/devotions',
    '/tafakari',
    301
);

Route::get(
    '/devotions/{slug}',
    function (string $slug) {
        return redirect()->route(
            'devotions.show',
            $slug,
            301
        );
    }
);

Route::redirect(
    '/children',
    '/watoto',
    301
);

Route::get(
    '/children/{slug}',
    function (string $slug) {
        return redirect()->route(
            'children.show',
            $slug,
            301
        );
    }
);

Route::redirect(
    '/lessons',
    '/jifunze-biblia',
    301
);

Route::get(
    '/lessons/{lesson}',
    function (string $lesson) {
        return redirect()->route(
            'lessons.show',
            $lesson,
            301
        );
    }
);

Route::redirect(
    '/dashboard',
    '/dashibodi',
    301
);

Route::redirect(
    '/profile',
    '/wasifu',
    301
);


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';