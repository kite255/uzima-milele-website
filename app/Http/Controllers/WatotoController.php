<?php

namespace App\Http\Controllers;

use App\Models\WatotoQuiz;
use App\Models\WatotoQuizResult;
use App\Models\WatotoVideo;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WatotoController extends Controller
{
    public function index()
    {
        $featuredVideo = WatotoVideo::where('is_published', true)
            ->where('is_featured', true)
            ->latest()
            ->first();

        $videos = WatotoVideo::where('is_published', true)
            ->latest()
            ->get();

        $categories = WatotoVideo::where('is_published', true)
            ->whereNotNull('category')
            ->select('category')
            ->distinct()
            ->pluck('category');

        return view('watoto.index', compact('featuredVideo', 'videos', 'categories'));
    }

    public function show($slug)
    {
        $video = WatotoVideo::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $questions = $video->quizzes()
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        // Bind the displayed questions to this session to reject forged quiz IDs.
        request()->session()->put('watoto_quiz_questions.' . $video->id, $questions->pluck('id')->map(fn ($id) => (string) $id)->all());

        $relatedVideos = WatotoVideo::where('is_published', true)
            ->where('id', '!=', $video->id)
            ->when($video->category, fn ($query) => $query->where('category', $video->category))
            ->latest()
            ->take(3)
            ->get();

        return view('watoto.show', compact('video', 'questions', 'relatedVideos'));
    }

    public function submitQuiz(Request $request, $slug)
    {
        $video = WatotoVideo::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $validated = $request->validate([
            'answers' => ['required', 'array', 'min:1', 'max:5'],
            'answers.*' => ['required', 'string', 'in:A,B,C,D'],
        ]);
        $answers = $validated['answers'];
        $expectedIds = $request->session()->get('watoto_quiz_questions.' . $video->id, []);
        $submittedIds = array_map('strval', array_keys($answers));

        sort($expectedIds);
        sort($submittedIds);

        if (! $expectedIds || $submittedIds !== $expectedIds) {
            throw ValidationException::withMessages([
                'answers' => 'Jaribio halilingani na maswali yaliyoonyeshwa. Fungua somo tena kisha ujaribu.',
            ]);
        }

        $questions = WatotoQuiz::whereIn('id', $submittedIds)
            ->where('watoto_video_id', $video->id)
            ->where('is_active', true)
            ->get();

        if ($questions->count() !== count($expectedIds)) {
            throw ValidationException::withMessages([
                'answers' => 'Baadhi ya maswali yamebadilika. Fungua somo tena kisha ujaribu.',
            ]);
        }

        // Consume the displayed-question set to prevent accidental duplicate submissions.
        $request->session()->forget('watoto_quiz_questions.' . $video->id);

        $correct = 0;
        $results = [];

        foreach ($questions as $question) {
            $userAnswer = $answers[$question->id] ?? null;
            $isCorrect = $userAnswer === $question->correct_answer;

            if ($isCorrect) {
                $correct++;
            }

            $results[] = [
                'question' => $question,
                'user_answer' => $userAnswer,
                'is_correct' => $isCorrect,
            ];
        }

        $total = $questions->count();
        $score = $total > 0 ? round(($correct / $total) * 100) : 0;
        $passed = $score >= 50;

        WatotoQuizResult::create([
            'watoto_video_id' => $video->id,
            'user_name' => 'Guest', // Do not persist a child's or signed-in user's name for public quizzes.
            'score' => $score,
            'correct' => $correct,
            'total' => $total,
            'passed' => $passed,
        ]);

        return view('watoto.quiz-result', [
            'video' => $video,
            'questions' => $questions,
            'score' => $score,
            'correct' => $correct,
            'total' => $total,
            'passed' => $passed,
            'results' => $results,
        ]);
    }
}