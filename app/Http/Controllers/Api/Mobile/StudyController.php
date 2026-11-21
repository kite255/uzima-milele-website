<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\LessonProgress;
use App\Models\LessonTopic;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Notifications\LessonEnrolledNotification;
use App\Services\GradeStudyQuiz;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

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

        return $this->studySession($student);
    }

    public function googleLogin(Request $request): JsonResponse
    {
        // Socialite verifies the signature and audience for JWTs. Reject opaque
        // access tokens so this endpoint always takes Socialite's ID token path.
        $input = $request->validate([
            'id_token' => ['required', 'string', 'min:101', 'max:8192', 'regex:/^[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/D'],
        ]);
        $clientId = config('services.google.mobile_web_client_id') ?: config('services.google.client_id');
        abort_if(blank($clientId), 503, 'Kuingia kwa Google hakujasanidiwa.');

        try {
            $identity = Socialite::buildProvider(GoogleProvider::class, [
                'client_id' => $clientId,
                'client_secret' => '',
                'redirect' => '',
            ])
                ->setHttpClient(new Client(['connect_timeout' => 3, 'timeout' => 8]))
                ->userFromToken($input['id_token']);
        } catch (\Exception) {
            throw ValidationException::withMessages(['id_token' => 'Utambulisho wa Google haujathibitishwa.']);
        }

        $claims = $identity->getRaw();
        $subject = $identity->getId();
        $email = $identity->getEmail();
        if (! is_string($subject) || $subject === '' || strlen($subject) > 255 ||
            ! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL) ||
            ($claims['email_verified'] ?? null) !== true ||
            ! is_int($claims['exp'] ?? null) || $claims['exp'] <= time()) {
            throw ValidationException::withMessages(['id_token' => 'Utambulisho wa Google haujathibitishwa.']);
        }

        $student = DB::transaction(function () use ($subject, $email) {
            $linked = User::query()->where('google_id', $subject)->first();
            if ($linked) {
                return $linked->role === 'student' ? $linked : null;
            }

            // Google controls @gmail.com addresses. Other email providers need
            // an existing Google subject link, even if Google verified the email.
            if (! str_ends_with(strtolower($email), '@gmail.com')) {
                return null;
            }
            $user = User::query()->where('email', strtolower($email))->lockForUpdate()->first();
            if (! $user || $user->role !== 'student' || $user->email_verified_at === null ||
                ($user->google_id !== null && $user->google_id !== $subject)) {
                return null;
            }

            $user->update(['google_id' => $subject]);

            return $user;
        });
        abort_unless($student instanceof User, 403, 'Akaunti hii ya Google haijaunganishwa na akaunti ya mwanafunzi.');

        return $this->studySession($student);
    }

    private function studySession(User $student): JsonResponse
    {
        $expiresAt = now()->addDay();
        $token = $student->createToken('Uzima Milele Flutter', ['study'], $expiresAt);

        return response()->json(['data' => [
            'token' => $token->plainTextToken,
            'name' => $student->name,
            'expires_at' => $expiresAt->toIso8601String(),
        ]])
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
        $progress = $enrolled && $canStart ? $this->progress($student, $lesson) : null;

        return $this->data([
            'enrolled' => $enrolled,
            'can_start' => $canStart,
            'completed_topics' => $progress['completed_topics'] ?? [],
            'locked_topics' => $progress['locked_topics'] ?? [],
            'resume_topic' => ($progress['topic'] ?? null)?->slug,
            'resume_quiz' => $this->quizSummary($progress['quiz'] ?? null),
            'lesson_completed' => $progress['lesson_completed'] ?? false,
        ]);
    }

    public function resume(Request $request): JsonResponse
    {
        $student = $this->student($request);
        $includeQuizzes = $request->boolean('include_quizzes');
        $lastCompletion = LessonProgress::query()
            ->where('user_id', $student->id)
            ->whereColumn('lesson_id', 'lesson_enrollments.lesson_id')
            ->selectRaw('MAX(completed_at)');
        $enrollments = $student->lessonEnrollments()
            ->whereHas('lesson', fn ($query) => $query->where('is_published', true))
            ->with('lesson')
            ->orderByDesc($lastCompletion)
            ->orderByDesc('enrolled_at')
            ->orderByDesc('id')
            ->get();

        foreach ($enrollments as $enrollment) {
            $lesson = $enrollment->lesson;
            if (! $lesson?->is_published || ! $lesson->canBeStartedBy($student)) {
                continue;
            }

            $progress = $this->progress($student, $lesson);
            $topic = $progress['topic'];
            $quiz = $includeQuizzes ? $progress['quiz'] : null;
            if ($topic || $quiz) {
                return $this->data([
                    'lesson' => ['slug' => $lesson->slug, 'title' => $lesson->title],
                    'topic' => $topic ? ['slug' => $topic->slug, 'title' => $topic->title] : null,
                    'quiz' => $this->quizSummary($quiz),
                ]);
            }
        }

        return $this->data(['lesson' => null, 'topic' => null, 'quiz' => null]);
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

        $completed = $topic->isCompletedBy($student);
        $quiz = $completed ? null : $this->pendingTopicQuiz($student, $topic);

        return $this->data([
            'title' => $topic->title,
            'content' => PublicContentController::plain($topic->content),
            'completed' => $completed,
            'can_complete' => $quiz === null,
            'quiz' => $this->quizSummary($quiz),
        ]);
    }

    public function complete(Request $request, Lesson $lesson, string $slug): JsonResponse
    {
        $student = $this->student($request);
        $topic = $this->accessibleTopic($student, $lesson, $slug);
        abort_if(
            ! $topic->isCompletedBy($student) && $this->pendingTopicQuiz($student, $topic),
            422,
            'Faulu quiz ya lazima kabla ya kukamilisha mada hii.'
        );

        LessonProgress::query()->firstOrCreate([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'lesson_topic_id' => $topic->id,
        ], ['completed_at' => now()]);

        return $this->data(['completed' => true]);
    }

    public function quiz(Request $request, Lesson $lesson, Quiz $quiz): JsonResponse
    {
        $this->loadQuiz($this->student($request), $lesson, $quiz);

        return $this->data([
            'title' => $quiz->title,
            'questions' => $quiz->questions->map(fn (Question $question) => [
                'id' => $question->id,
                'text' => $question->question,
                'options' => $this->questionOptions($question),
            ])->values(),
        ]);
    }

    public function submitQuiz(Request $request, Lesson $lesson, Quiz $quiz, GradeStudyQuiz $grader): JsonResponse
    {
        $student = $this->student($request);
        $this->loadQuiz($student, $lesson, $quiz);
        $rules = ['answers' => ['required', 'array:'.$quiz->questions->pluck('id')->implode(',')]];
        foreach ($quiz->questions as $question) {
            $rules['answers.'.$question->id] = [
                'required',
                'string',
                Rule::in(array_column($this->questionOptions($question), 'value')),
            ];
        }
        $answers = $request->validate($rules)['answers'];
        ['result' => $result] = $grader->grade($quiz, $student, $answers);

        return $this->data($result);
    }

    private function loadQuiz(User $student, Lesson $lesson, Quiz $quiz): void
    {
        $this->checkLesson($student, $lesson);
        abort_unless($this->isEnrolled($student, $lesson), 403, 'Jiunge na somo hili kwanza.');
        abort_unless($quiz->is_published && $quiz->is_required, 404);

        if ($quiz->lesson_topic_id) {
            $topic = $quiz->topic;
            abort_unless($topic && $topic->module?->lesson_id === $lesson->id, 404);
            $this->accessibleTopic($student, $lesson, $topic->slug);
            abort_unless($topic->quiz()->whereKey($quiz->id)->exists(), 404);
        } else {
            $progress = $this->progress($student, $lesson);
            abort_unless($progress['quiz']?->is($quiz), 403, 'Kamilisha sehemu iliyotangulia kwanza.');
        }

        $quiz->load(['questions' => fn ($query) => $query->active()]);
        abort_if($quiz->questions->isEmpty() || $quiz->questions->contains(
            fn (Question $question) => count($this->questionOptions($question)) < 2
        ), 422, 'Mtihani huu bado hauko tayari.');
    }

    private function questionOptions(Question $question): array
    {
        if ($question->type === 'true_false') {
            return [
                ['value' => 'true', 'label' => 'Kweli'],
                ['value' => 'false', 'label' => 'Siyo kweli'],
            ];
        }

        if ($question->type !== 'multiple_choice') {
            return [];
        }

        $options = $question->options;
        if (is_string($options)) {
            $options = json_decode($options, true);
        }

        $published = [];
        foreach (is_array($options) ? $options : [] as $index => $option) {
            $label = is_array($option) ? ($option['text'] ?? $option['label'] ?? null) : $option;
            if (is_string($label) && trim($label) !== '') {
                $published[] = ['value' => (string) $index, 'label' => $label];
            }
        }

        return $published;
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

    private function progress(User $student, Lesson $lesson): array
    {
        $modules = $this->modules($lesson);
        $topics = $modules->flatMap(fn ($module) => $module->publishedTopics);
        $done = LessonProgress::query()->where('user_id', $student->id)
            ->where('lesson_id', $lesson->id)->pluck('lesson_topic_id')->flip();
        $completed = $topics->filter(fn ($topic) => $done->has($topic->id))->pluck('slug')->values();

        $resume = null;
        $quiz = null;
        $locked = [];
        $blocked = false;
        foreach ($modules as $module) {
            if ($blocked) {
                $locked = [...$locked, ...$module->publishedTopics->pluck('slug')->all()];
                continue;
            }
            $resume = $module->publishedTopics->first(fn ($topic) => ! $done->has($topic->id));
            if ($resume || ! $module->isCompletedBy($student)) {
                $blocked = true;
                if (! $resume && $module->publishedTopics->isNotEmpty()) {
                    $quiz = $module->requiredPublishedQuiz();
                }
            }
        }

        if (! $blocked && $modules->isNotEmpty() && ! $lesson->hasPassedRequiredFinalQuizBy($student)) {
            $quiz = $lesson->getPublishedFinalQuiz();
        }

        return [
            'topic' => $resume,
            'quiz' => $quiz,
            'completed_topics' => $completed,
            'locked_topics' => $locked,
            'lesson_completed' => ! $blocked && $modules->isNotEmpty() && $quiz === null,
        ];
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

    private function pendingTopicQuiz(User $student, LessonTopic $topic): ?Quiz
    {
        $quiz = $topic->quiz()->where('is_published', true)->where('is_required', true)->first();

        return $quiz && ! $quiz->results()->where('user_id', $student->id)->where('passed', true)->exists()
            ? $quiz
            : null;
    }

    private function quizSummary(?Quiz $quiz): ?array
    {
        return $quiz ? ['id' => $quiz->id, 'title' => $quiz->title] : null;
    }

    private function data(array $data): JsonResponse
    {
        return response()->json(['data' => $data])->header('Cache-Control', 'no-store');
    }
}
