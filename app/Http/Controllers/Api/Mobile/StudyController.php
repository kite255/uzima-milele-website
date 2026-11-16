<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonProgress;
use App\Models\LessonTopic;
use App\Models\User;
use App\Notifications\LessonEnrolledNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class StudyController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $request->ensureIsNotRateLimited();

        $provider = Auth::guard('web')->getProvider();
        $credentials = $request->only('email', 'password') + ['role' => 'student'];
        $student = $provider->retrieveByCredentials($credentials);

        $valid = $student instanceof User
            ? $provider->validateCredentials($student, $credentials)
            : Hash::check($credentials['password'], '$2y$12$RWSbZfEGsD/by0t8LOVzauoj4MTCY6s8RzJm5CSOk876gSlD/ECEy');

        if (! $student instanceof User || ! $valid) {
            RateLimiter::hit($request->throttleKey());
            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        RateLimiter::clear($request->throttleKey());
        $token = $student->createToken('Uzima Milele Flutter', ['study'], now()->addDay());

        return response()->json(['data' => ['token' => $token->plainTextToken]])
            ->header('Cache-Control', 'no-store');
    }

    public function logout(Request $request)
    {
        $this->student($request)->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function status(Request $request, Lesson $lesson): JsonResponse
    {
        $student = $this->student($request);
        abort_unless($lesson->is_published, 404);
        $enrolled = $this->isEnrolled($student, $lesson);
        $canStart = $lesson->canBeStartedBy($student);
        $resume = null;
        $completed = [];

        if ($enrolled && $canStart) {
            $modules = $this->modules($lesson);
            $topics = $modules->flatMap(fn ($module) => $module->publishedTopics);
            $done = LessonProgress::query()->where('user_id', $student->id)
                ->where('lesson_id', $lesson->id)->pluck('lesson_topic_id')->flip();
            $completed = $topics->filter(fn ($topic) => $done->has($topic->id))->pluck('slug')->values();
            foreach ($modules as $module) {
                $resume = $module->publishedTopics->first(fn ($topic) => ! $done->has($topic->id))?->slug;
                if ($resume || ! $module->isCompletedBy($student)) {
                    break;
                }
            }
        }

        return $this->data([
            'enrolled' => $enrolled,
            'can_start' => $canStart,
            'completed_topics' => $completed,
            'resume_topic' => $resume,
        ]);
    }

    public function enroll(Request $request, Lesson $lesson): JsonResponse
    {
        $student = $this->student($request);
        $this->checkLesson($student, $lesson);

        $created = DB::transaction(function () use ($student, $lesson) {
            User::query()->whereKey($student->id)->lockForUpdate()->firstOrFail();
            if ($this->isEnrolled($student, $lesson)) {
                return false;
            }

            LessonEnrollment::createForLesson(
                user: $student,
                lesson: $lesson,
                pace: $lesson->default_study_pace ?? Lesson::PACE_REGULAR,
            );

            return true;
        });

        if ($created) {
            $student->notify(new LessonEnrolledNotification($lesson));
        }

        return $this->status($request, $lesson);
    }

    public function topic(Request $request, Lesson $lesson, string $slug): JsonResponse
    {
        $student = $this->student($request);
        $topic = $this->accessibleTopic($student, $lesson, $slug);

        return $this->data([
            'title' => $topic->title,
            'content' => PublicContentController::plain($topic->content),
            'completed' => $topic->isCompletedBy($student),
            'can_complete' => $this->canComplete($student, $topic),
        ]);
    }

    public function complete(Request $request, Lesson $lesson, string $slug): JsonResponse
    {
        $student = $this->student($request);
        $topic = $this->accessibleTopic($student, $lesson, $slug);
        abort_unless($this->canComplete($student, $topic), 422, 'Faulu quiz ya lazima kabla ya kukamilisha mada hii.');

        LessonProgress::query()->firstOrCreate([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'lesson_topic_id' => $topic->id,
        ], ['completed_at' => now()]);

        return $this->data(['completed' => true]);
    }

    private function student(Request $request): User
    {
        $student = $request->user();
        abort_unless(
            $student instanceof User && $student->role === 'student'
            && $student->currentAccessToken() instanceof PersonalAccessToken
            && $student->tokenCan('study'),
            401,
        );

        return $student;
    }

    private function checkLesson(User $student, Lesson $lesson): void
    {
        abort_unless($lesson->is_published, 404);
        abort_unless($lesson->canBeStartedBy($student), 403, 'Kamilisha somo lililotangulia kwanza.');
    }

    private function isEnrolled(User $student, Lesson $lesson): bool
    {
        return $student->lessonEnrollments()->where('lesson_id', $lesson->id)->exists();
    }

    private function modules(Lesson $lesson)
    {
        return $lesson->modules()->where('is_published', true)->reorder('order')->orderBy('id')
            ->with('publishedTopics')->get();
    }

    private function accessibleTopic(User $student, Lesson $lesson, string $slug): LessonTopic
    {
        $this->checkLesson($student, $lesson);
        abort_unless($this->isEnrolled($student, $lesson), 403, 'Jiunge na somo hili kwanza.');

        $modules = $this->modules($lesson);
        $topic = $modules->flatMap(fn ($module) => $module->publishedTopics)->firstWhere('slug', $slug);
        abort_unless($topic, 404);

        foreach ($modules as $module) {
            if ($module->id === $topic->module_id) {
                break;
            }
            abort_unless($module->isCompletedBy($student), 403, 'Kamilisha moduli iliyotangulia kwanza.');
        }

        return $topic;
    }

    private function canComplete(User $student, LessonTopic $topic): bool
    {
        $quiz = $topic->quiz()->where('is_published', true)->where('is_required', true)->first();

        return ! $quiz || $quiz->results()->where('user_id', $student->id)->where('passed', true)->exists();
    }

    private function data(array $data): JsonResponse
    {
        return response()->json(['data' => $data])->header('Cache-Control', 'no-store');
    }
}
