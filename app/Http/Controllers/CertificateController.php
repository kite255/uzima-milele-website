<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Lesson;
use App\Models\QuizResult;
use App\Notifications\CertificateIssuedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Issue Certificate
    |--------------------------------------------------------------------------
    |
    | Certificate requirements:
    |
    | 1. Student must be logged in.
    | 2. Lesson must be published.
    | 3. Student must be enrolled.
    | 4. Every published module must be completed.
    | 5. Required final quiz must be passed.
    |
    | Module completion is handled by Module::isCompletedBy():
    |
    | - All published topics completed.
    | - Required module quiz attempted.
    |
    */
    public function issue(Lesson $lesson)
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */
        if (! $user) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Tafadhali ingia kwanza ili kutengeneza cheti.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Published Lesson
        |--------------------------------------------------------------------------
        */
        abort_if(! $lesson->is_published, 404);

        /*
        |--------------------------------------------------------------------------
        | Enrollment
        |--------------------------------------------------------------------------
        */
        $isEnrolled = $lesson->enrollments()
            ->where('user_id', $user->id)
            ->exists();

        if (! $isEnrolled) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with(
                    'error',
                    'Tafadhali jiunge na somo hili kwanza kabla ya kutengeneza cheti.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Load Published Lesson Structure
        |--------------------------------------------------------------------------
        */
        $lesson->load([
            'modules' => fn ($query) => $query
                ->where('is_published', true)
                ->orderBy('order'),

            'modules.topics' => fn ($query) => $query
                ->where('is_published', true)
                ->orderBy('order'),

            'modules.quizzes' => fn ($query) => $query
                ->where('is_published', true),

            'finalQuiz' => fn ($query) => $query
                ->where('is_published', true),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Published Modules
        |--------------------------------------------------------------------------
        */
        $modules = $lesson->modules;

        if ($modules->isEmpty()) {
            return back()->with(
                'error',
                'Somo hili halina modules zilizochapishwa. Cheti hakiwezi kutengenezwa.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Module Completion
        |--------------------------------------------------------------------------
        |
        | Student cannot receive a certificate until every published module
        | satisfies Module::isCompletedBy().
        |
        | IMPORTANT:
        |
        | This does not control navigation while learning.
        | The student can still move between modules freely.
        |
        */
        $incompleteModules = $modules
            ->filter(
                fn ($module) => ! $module->isCompletedBy($user)
            );

        if ($incompleteModules->isNotEmpty()) {

            /*
            |--------------------------------------------------------------------------
            | Check Modules Waiting for Quiz
            |--------------------------------------------------------------------------
            */
            $quizPendingModules = $incompleteModules
                ->filter(
                    fn ($module) =>
                        $module->completionStatusFor($user) === 'quiz_pending'
                );

            if ($quizPendingModules->isNotEmpty()) {
                $count = $quizPendingModules->count();

                return back()->with(
                    'error',
                    $count === 1
                        ? 'Kuna module ambayo mada zake zimekamilika lakini quiz yake bado haijafanywa. Fanya quiz hiyo kabla ya kutengeneza cheti.'
                        : 'Kuna modules ' . $count . ' ambazo quiz zake bado hazijafanywa. Fanya quiz hizo kabla ya kutengeneza cheti.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Other Incomplete Modules
            |--------------------------------------------------------------------------
            */
            return back()->with(
                'error',
                'Tafadhali kamilisha modules zote za somo kabla ya kutengeneza cheti.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Final Quiz
        |--------------------------------------------------------------------------
        |
        | An optional final quiz does not prevent certificate generation.
        |
        | A required final quiz must be passed.
        |
        */
        $finalQuiz = $lesson->finalQuiz;

        if ($finalQuiz && $finalQuiz->is_required) {
            $finalQuizPassed = QuizResult::query()
                ->where('user_id', $user->id)
                ->where('quiz_id', $finalQuiz->id)
                ->where('passed', true)
                ->exists();

            if (! $finalQuizPassed) {
                return back()->with(
                    'error',
                    'Tafadhali faulu jaribio la mwisho kabla ya kutengeneza cheti.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Issue Certificate
        |--------------------------------------------------------------------------
        |
        | firstOrCreate prevents duplicate certificates for the same
        | student and lesson.
        |
        */
        $certificate = Certificate::firstOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'certificate_number' => $this->generateCertificateNumber(),
                'issued_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        |
        | Send notification only when certificate has just been created.
        |
        */
        if ($certificate->wasRecentlyCreated) {
            $certificate->load([
                'lesson',
                'user',
            ]);

            $user->notify(
                new CertificateIssuedNotification($certificate)
            );
        }

        return redirect()
            ->route(
                'certificates.show',
                $certificate->certificate_number
            )
            ->with(
                'success',
                'Cheti kimetengenezwa kikamilifu.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Show Certificate
    |--------------------------------------------------------------------------
    */
    public function show(string $certificateNumber)
    {
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
            'certificates.show',
            compact('certificate')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Download Certificate PDF
    |--------------------------------------------------------------------------
    */
    public function download(string $certificateNumber)
    {
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

        $pdf = Pdf::loadView(
            'certificates.pdf',
            [
                'certificate' => $certificate,
            ]
        )
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 96,
            ]);

        return $pdf->download(
            $certificate->certificate_number . '.pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Print Preview
    |--------------------------------------------------------------------------
    */
    public function printPreview(string $certificateNumber)
    {
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
            'certificates.pdf',
            compact('certificate')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Public Certificate Verification
    |--------------------------------------------------------------------------
    */
    public function verify(string $certificateNumber)
    {
        $certificate = Certificate::with([
            'user',
            'lesson',
        ])
            ->where(
                'certificate_number',
                $certificateNumber
            )
            ->firstOrFail();

        return view(
            'certificates.verify',
            compact('certificate')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Certificate Number Generator
    |--------------------------------------------------------------------------
    */
    private function generateCertificateNumber(): string
    {
        do {
            $number =
                'UZM-'
                . now()->format('Y')
                . '-'
                . strtoupper(Str::random(8));
        } while (
            Certificate::where(
                'certificate_number',
                $number
            )->exists()
        );

        return $number;
    }
}