<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\QuizResult;
use App\Notifications\CertificateIssuedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function issue(Lesson $lesson)
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()
                ->route('login')
                ->with('error', 'Tafadhali ingia kwanza ili kutengeneza cheti.');
        }

        abort_if(! $lesson->is_published, 404);

        /*
        |--------------------------------------------------------------------------
        | Rule 1: Student must be enrolled
        |--------------------------------------------------------------------------
        */
        $isEnrolled = $lesson->enrollments()
            ->where('user_id', $user->id)
            ->exists();

        if (! $isEnrolled) {
            return redirect()
                ->route('lessons.show', $lesson->slug)
                ->with('error', 'Tafadhali jiunge na somo hili kwanza kabla ya kutengeneza cheti.');
        }

        /*
        |--------------------------------------------------------------------------
        | Rule 2: Student must complete all published topics
        |--------------------------------------------------------------------------
        */
        $lesson->load([
            'modules' => fn ($query) => $query
                ->where('is_published', true)
                ->orderBy('order'),

            'modules.topics' => fn ($query) => $query
                ->where('is_published', true)
                ->orderBy('order'),
        ]);

        $topicIds = $lesson->modules
            ->flatMap(fn ($module) => $module->topics)
            ->pluck('id')
            ->values();

        $totalTopics = $topicIds->count();

        $completedTopics = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->whereIn('lesson_topic_id', $topicIds)
            ->distinct('lesson_topic_id')
            ->count('lesson_topic_id');

        if ($totalTopics === 0 || $completedTopics < $totalTopics) {
            return back()->with('error', 'Tafadhali kamilisha mada zote kabla ya kutengeneza cheti.');
        }

        /*
        |--------------------------------------------------------------------------
        | Rule 3: If final quiz exists and is required, student must pass it
        |--------------------------------------------------------------------------
        */
        $finalQuiz = $lesson->finalQuiz()
            ->where('is_published', true)
            ->first();

        if ($finalQuiz && $finalQuiz->is_required) {
            $finalQuizPassed = QuizResult::where('user_id', $user->id)
                ->where('quiz_id', $finalQuiz->id)
                ->where('passed', true)
                ->exists();

            if (! $finalQuizPassed) {
                return back()->with('error', 'Tafadhali faulu jaribio la mwisho kabla ya kutengeneza cheti.');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Rule 4: Issue certificate once only
        |--------------------------------------------------------------------------
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

        if ($certificate->wasRecentlyCreated) {
            $certificate->load(['lesson', 'user']);

            $user->notify(
                new CertificateIssuedNotification($certificate)
            );
        }

        return redirect()
            ->route('certificates.show', $certificate->certificate_number)
            ->with('success', 'Cheti kimetengenezwa kikamilifu.');
    }

    public function show(string $certificateNumber)
    {
        $certificate = Certificate::with(['user', 'lesson'])
            ->where('certificate_number', $certificateNumber)
            ->firstOrFail();

        abort_if($certificate->user_id !== auth()->id(), 403);

        return view('certificates.show', compact('certificate'));
    }

    public function download(string $certificateNumber)
    {
        $certificate = Certificate::with(['user', 'lesson'])
            ->where('certificate_number', $certificateNumber)
            ->firstOrFail();

        abort_if($certificate->user_id !== auth()->id(), 403);

        $pdf = Pdf::loadView('certificates.pdf', compact('certificate'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        return $pdf->download($certificate->certificate_number . '.pdf');
    }

    private function generateCertificateNumber(): string
    {
        do {
            $number = 'UZM-' . now()->format('Y') . '-' . strtoupper(Str::random(8));
        } while (Certificate::where('certificate_number', $number)->exists());

        return $number;
    }
}