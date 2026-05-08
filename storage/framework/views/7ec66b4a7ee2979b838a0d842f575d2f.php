

<?php $__env->startSection('title', $lesson->title); ?>

<?php $__env->startSection('content'); ?>

<?php
    $totalModules = $modulesCount ?? $lesson->modules->count();

    $totalTopics = $totalTopics ?? $lesson->modules
        ->flatMap(fn ($module) => $module->topics)
        ->count();

    $topicQuestionsCount = $lesson->modules
        ->flatMap(fn ($module) => $module->topics)
        ->filter(fn ($topic) => $topic->quiz)
        ->flatMap(fn ($topic) => $topic->quiz->questions)
        ->count();

    $moduleQuestionsCount = $lesson->modules
        ->flatMap(fn ($module) => $module->quizzes ?? collect())
        ->flatMap(fn ($quiz) => $quiz->questions)
        ->count();

    $finalQuestionsCount = $lesson->finalQuiz
        ? $lesson->finalQuiz->questions->count()
        : 0;

    $totalQuestions = $questionsCount ?? ($topicQuestionsCount + $moduleQuestionsCount + $finalQuestionsCount);

    $loginUrl = route('login') . '?redirect=' . urlencode(route('lessons.show', $lesson));

    $instructorName = $lesson->instructor?->name;
    $ministryName = $lesson->instructor?->ministry_name ?: 'Uzima Milele Ministry';
    $ministryBio = $lesson->instructor?->ministry_bio
        ?: 'Uzima Milele Ministry hutoa elimu ya Biblia, afya, na jamii kupitia mifumo ya kidijitali kwa lugha ya Kiswahili.';

    $showInstructorName = $instructorName
        && strtolower(trim($instructorName)) !== strtolower(trim($ministryName));

    $studentIsEnrolled = auth()->check()
        ? auth()->user()->lessonEnrollments()->where('lesson_id', $lesson->id)->exists()
        : false;

    $enrollment = auth()->check()
        ? auth()->user()->lessonEnrollments()->where('lesson_id', $lesson->id)->first()
        : null;
?>

<section class="bg-navy text-white py-14">
    <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-2 gap-10 items-center">

        <div>
            <a href="<?php echo e(route('lessons.index')); ?>" class="text-white/80 hover:text-white text-sm font-bold">
                ← Rudi kwenye Masomo
            </a>

            <p class="mt-6 text-accent font-bold uppercase text-sm">
                Somo la Biblia
            </p>

            <h1 class="mt-3 text-4xl md:text-5xl font-black leading-tight">
                <?php echo e($lesson->title); ?>

            </h1>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lesson->description): ?>
                <p class="mt-5 text-white/80 text-lg leading-relaxed">
                    <?php echo e($lesson->description); ?>

                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="mt-7 flex flex-wrap gap-3 text-sm">
                <span class="bg-white/10 rounded-full px-4 py-2 font-bold">
                    Moduli <?php echo e($totalModules); ?>

                </span>

                <span class="bg-white/10 rounded-full px-4 py-2 font-bold">
                    Mada <?php echo e($totalTopics); ?>

                </span>

                <span class="bg-white/10 rounded-full px-4 py-2 font-bold">
                    Maswali <?php echo e($totalQuestions); ?>

                </span>

                <span class="bg-white/10 rounded-full px-4 py-2 font-bold">
                    Cheti
                </span>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($studentIsEnrolled && $enrollment): ?>
                <p class="mt-5 text-sm text-white/80">
                    Ulijiunga tarehe
                    <span class="font-bold text-white">
                        <?php echo e(optional($enrollment->enrolled_at)->format('d M Y, H:i')); ?>

                    </span>
                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($studentIsEnrolled): ?>
                        <a href="<?php echo e(route('lessons.learn', $lesson)); ?>"
                           class="inline-flex justify-center rounded-xl bg-accent text-navy font-black px-7 py-4 hover:opacity-90 transition">
                            Endelea Kujifunza →
                        </a>

                        <a href="<?php echo e(route('student.dashboard')); ?>"
                           class="inline-flex justify-center rounded-xl bg-white/10 text-white font-bold px-7 py-4 hover:bg-white/20 transition">
                            Nenda Dashboard
                        </a>
                    <?php else: ?>
                        <form method="POST" action="<?php echo e(route('lessons.enroll', $lesson)); ?>">
                            <?php echo csrf_field(); ?>

                            <button type="submit"
                                    class="inline-flex justify-center rounded-xl bg-accent text-navy font-black px-7 py-4 hover:opacity-90 transition">
                                Jiunge na Somo →
                            </button>
                        </form>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <a href="<?php echo e($loginUrl); ?>"
                       class="inline-flex justify-center rounded-xl bg-accent text-navy font-black px-7 py-4 hover:opacity-90 transition">
                        Ingia Kuanza Kujifunza →
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <a href="#course-content"
                   class="inline-flex justify-center rounded-xl bg-white/10 text-white font-bold px-7 py-4 hover:bg-white/20 transition">
                    Tazama Yaliyomo
                </a>
            </div>
        </div>

        <div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lesson->cover_image): ?>
                <img src="<?php echo e(asset('storage/' . $lesson->cover_image)); ?>"
                     alt="<?php echo e($lesson->title); ?>"
                     class="w-full h-80 object-cover rounded-3xl shadow-xl">
            <?php else: ?>
                <div class="w-full h-80 bg-primary/20 rounded-3xl shadow-xl flex items-center justify-center font-black text-2xl">
                    Somo la Uzima Milele
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

    </div>
</section>

<section class="bg-gray-50 py-14">
    <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-3 gap-8">

        <main class="lg:col-span-2 space-y-8">

            <div class="bg-white rounded-2xl shadow-sm p-6 md:p-8">
                <h2 class="text-2xl font-black text-navy">
                    Kuhusu Somo Hili
                </h2>

                <div class="mt-5 prose max-w-none text-gray-700 prose-headings:text-navy prose-a:text-primary">
                    <?php echo $lesson->content; ?>

                </div>
            </div>

            <div id="course-content" class="bg-white rounded-2xl shadow-sm p-6 md:p-8">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <h2 class="text-2xl font-black text-navy">
                        Yaliyomo kwenye Somo
                    </h2>

                    <span class="text-sm bg-primary/10 text-primary font-bold px-4 py-2 rounded-full">
                        Mada <?php echo e($totalTopics); ?>

                    </span>
                </div>

                <div class="space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $lesson->modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $moduleQuiz = $module->quiz ?? $module->quizzes?->first();
                        ?>

                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                            <div class="bg-gray-50 px-5 py-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-black text-navy">
                                            <?php echo e($module->title); ?>

                                        </h3>

                                        <p class="text-sm text-gray-500 mt-1">
                                            Mada <?php echo e($module->topics->count()); ?>

                                        </p>
                                    </div>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($moduleQuiz): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($studentIsEnrolled): ?>
                                                <a href="<?php echo e(route('quiz.show', $moduleQuiz->id)); ?>"
                                                   class="shrink-0 text-xs bg-primary text-white font-bold px-3 py-2 rounded-lg hover:bg-primaryDark transition">
                                                    Jaribio la Moduli
                                                </a>
                                            <?php else: ?>
                                                <span class="shrink-0 text-xs bg-gray-100 text-gray-500 font-bold px-3 py-2 rounded-lg">
                                                    Jiunge kwanza
                                                </span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php else: ?>
                                            <span class="shrink-0 text-xs bg-gray-100 text-gray-500 font-bold px-3 py-2 rounded-lg">
                                                Ingia kwanza
                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>

                            <div class="divide-y divide-gray-100">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $module->topics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                    <div class="px-5 py-4">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p class="font-bold text-gray-800">
                                                    <?php echo e($topic->order); ?>. <?php echo e($topic->title); ?>

                                                </p>

                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($topic->quiz): ?>
                                                    <p class="mt-1 text-xs text-gray-500">
                                                        Maswali <?php echo e($topic->quiz->questions->count()); ?>

                                                        · Alama ya kufaulu <?php echo e($topic->quiz->pass_mark); ?>%
                                                        · <?php echo e($topic->quiz->is_required ? 'Lazima' : 'Hiari'); ?>

                                                    </p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($topic->quiz): ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($studentIsEnrolled): ?>
                                                        <a href="<?php echo e(route('quiz.show', $topic->quiz->id)); ?>"
                                                           class="text-xs bg-accent text-navy font-bold px-3 py-2 rounded-lg hover:opacity-90 transition">
                                                            Fanya Jaribio
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-xs bg-gray-100 text-gray-500 font-bold px-3 py-2 rounded-lg">
                                                            Jiunge kwanza
                                                        </span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-xs bg-gray-100 text-gray-500 font-bold px-3 py-2 rounded-lg">
                                                        Ingia kwanza
                                                    </span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-xs bg-gray-100 text-gray-500 font-bold px-3 py-1 rounded-full">
                                                    Mada
                                                </span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                    <div class="px-5 py-4 text-gray-500 text-sm">
                                        Hakuna mada zilizoongezwa bado.
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-gray-500">
                            Hakuna moduli zilizoongezwa bado.
                        </p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lesson->finalQuiz): ?>
    <?php
        $studentIsEnrolled = $isEnrolled ?? $studentIsEnrolled ?? false;

        $completedTopicIds = auth()->check()
            ? \App\Models\LessonProgress::where('user_id', auth()->id())
                ->where('lesson_id', $lesson->id)
                ->pluck('lesson_topic_id')
                ->unique()
                ->toArray()
            : [];

        $allTopicsCompleted = ($totalTopics ?? 0) > 0
            && count($completedTopicIds) >= ($totalTopics ?? 0);
    ?>

    <div class="mt-6 rounded-2xl border border-primary/20 bg-primary/5 p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="font-black text-navy text-xl">
                    Jaribio la Mwisho
                </h3>

                <p class="mt-1 text-sm text-gray-600">
                    Maswali <?php echo e($lesson->finalQuiz->questions->count()); ?>

                    · Alama ya kufaulu <?php echo e($lesson->finalQuiz->pass_mark); ?>%
                    · <?php echo e($lesson->finalQuiz->is_required ? 'Lazima kwa cheti' : 'Hiari'); ?>

                </p>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($studentIsEnrolled && $allTopicsCompleted): ?>
                    <a href="<?php echo e(route('quiz.show', $lesson->finalQuiz->id)); ?>"
                       class="inline-flex justify-center rounded-xl bg-primary text-white font-bold px-6 py-3 hover:bg-primaryDark transition">
                        Fanya Jaribio la Mwisho
                    </a>

                <?php elseif($studentIsEnrolled && ! $allTopicsCompleted): ?>
                    <span class="inline-flex justify-center rounded-xl bg-gray-100 text-gray-500 font-bold px-6 py-3 cursor-not-allowed">
                        Kamilisha mada zote kwanza
                    </span>

                <?php else: ?>
                    <form method="POST" action="<?php echo e(route('lessons.enroll', $lesson)); ?>">
                        <?php echo csrf_field(); ?>

                        <button type="submit"
                                class="inline-flex justify-center rounded-xl bg-accent text-navy font-bold px-6 py-3 hover:opacity-90 transition">
                            Jiunge Kwanza
                        </button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php else: ?>
                <a href="<?php echo e($loginUrl); ?>"
                   class="inline-flex justify-center rounded-xl bg-accent text-navy font-bold px-6 py-3 hover:opacity-90 transition">
                    Ingia Kwanza
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

             
<div class="bg-white rounded-2xl shadow-sm p-6 md:p-8">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-black text-navy">
                Maswali na Majibu
            </h2>

            <p class="mt-2 text-gray-600">
                Uliza swali kuhusu somo hili au soma majibu yaliyotolewa.
            </p>
        </div>

        <span class="shrink-0 text-sm bg-primary/10 text-primary font-bold px-4 py-2 rounded-full">
            <?php echo e($lesson->publishedQuestions->count()); ?> Maswali
        </span>
    </div>

  
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEnrolled): ?>
        <form method="POST"
              action="<?php echo e(route('lessons.questions.store', $lesson->slug)); ?>"
              class="mb-8 rounded-3xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 p-6 md:p-8 shadow-sm">
            <?php echo csrf_field(); ?>

            <div class="mb-4">
                <label for="question" class="block text-lg font-black text-navy">
                    Uliza Swali
                </label>

                <p class="mt-1 text-sm text-gray-500">
                    Andika swali lako kwa uwazi ili mwalimu aweze kujibu vizuri.
                </p>
            </div>

            <div>
                <textarea id="question"
                          name="question"
                          rows="5"
                          required
                          placeholder="Mfano: Je, maana ya maombi ya kweli ni nini katika maisha ya Mkristo?"
                          class="w-full rounded-2xl border border-gray-300 bg-white px-5 py-4 text-base text-navy placeholder-gray-400 shadow-sm outline-none resize-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"><?php echo e(old('question')); ?></textarea>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['question'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-2 text-sm font-bold text-red-600">
                        <?php echo e($message); ?>

                    </p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="mt-5 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-500">
                    Swali lako litaonekana kwenye sehemu ya Maswali na Majibu.
                </p>

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-primary px-8 py-3.5 text-white font-black shadow-md transition hover:bg-primaryDark hover:shadow-lg w-full sm:w-auto">
                    Tuma Swali
                </button>
            </div>
        </form>
    <?php else: ?>
        <div class="mb-8 rounded-2xl bg-yellow-50 border border-yellow-200 p-5">
            <p class="text-yellow-700 font-bold">
                Tafadhali jiunge na somo hili kwanza ili uweze kuuliza swali.
            </p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php else: ?>
    <div class="mb-8 rounded-2xl bg-yellow-50 border border-yellow-200 p-5">
        <p class="text-yellow-700 font-bold">
            Tafadhali ingia kwenye akaunti yako ili uweze kuuliza swali.
        </p>

        <a href="<?php echo e(route('login')); ?>"
           class="mt-3 inline-flex rounded-xl bg-primary px-5 py-2 text-white font-bold hover:bg-primaryDark transition">
            Ingia Sasa
        </a>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="space-y-5">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $lesson->publishedQuestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-full bg-primary/10 text-primary flex items-center justify-center font-black shrink-0">
                        <?php echo e(strtoupper(substr($question->user->name ?? 'M', 0, 1))); ?>

                    </div>

                    <div class="flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                            <p class="font-black text-navy">
                                <?php echo e($question->user->name ?? 'Mwanafunzi'); ?>

                            </p>

                            <p class="text-xs text-gray-400">
                                <?php echo e($question->created_at->format('d M Y, H:i')); ?>

                            </p>
                        </div>

                        <p class="mt-3 text-gray-700 leading-relaxed">
                            <?php echo e($question->question); ?>

                        </p>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($question->answer): ?>
                            <div class="mt-5 rounded-2xl bg-primary/5 border border-primary/10 p-5">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-black text-primary">
                                        Jibu la Mwalimu
                                    </p>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($question->answered_at): ?>
                                        <p class="text-xs text-gray-400">
                                            <?php echo e($question->answered_at->format('d M Y, H:i')); ?>

                                        </p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <p class="mt-3 text-gray-700 leading-relaxed">
                                    <?php echo e($question->answer); ?>

                                </p>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($question->answeredBy): ?>
                                    <p class="mt-3 text-xs text-gray-500">
    Imejibiwa na:
    <span class="font-bold text-navy">
        Mwalimu wa Uzima Milele
    </span>
</p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="mt-4 inline-flex rounded-full bg-gray-100 px-4 py-2 text-xs font-bold text-gray-500">
                                Bado halijajibiwa
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="rounded-2xl bg-gray-50 border border-gray-100 p-8 text-center">
                <h3 class="text-lg font-black text-navy">
                    Hakuna maswali bado.
                </h3>

                <p class="mt-2 text-gray-500">
                    Kuwa wa kwanza kuuliza swali kuhusu somo hili.
                </p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>



        </main>

        <aside class="space-y-6 lg:sticky lg:top-24 h-fit">

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="text-xl font-black text-navy">
                    Anza Somo Hili
                </h3>

                <p class="mt-3 text-gray-600">
                    Jiunge na somo ili lijitokeze kwenye dashibodi yako na uweze kufuatilia maendeleo yako.
                </p>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($studentIsEnrolled && $enrollment): ?>
                    <div class="mt-4 rounded-xl bg-green-50 border border-green-200 p-4">
                        <p class="text-sm font-bold text-green-700">
                            Umejiunga na somo hili
                        </p>

                        <p class="text-xs text-green-700 mt-1">
                            <?php echo e(optional($enrollment->enrolled_at)->format('d M Y, H:i')); ?>

                        </p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mt-6 space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($studentIsEnrolled): ?>
                            <a href="<?php echo e(route('lessons.learn', $lesson)); ?>"
                               class="w-full inline-flex justify-center rounded-xl bg-primary text-white font-bold px-6 py-3 hover:bg-primaryDark transition">
                                Endelea Kujifunza
                            </a>

                            <a href="<?php echo e(route('student.dashboard')); ?>"
                               class="w-full inline-flex justify-center rounded-xl bg-gray-100 text-navy font-bold px-6 py-3 hover:bg-gray-200 transition">
                                Nenda Dashboard
                            </a>
                        <?php else: ?>
                            <form method="POST" action="<?php echo e(route('lessons.enroll', $lesson)); ?>">
                                <?php echo csrf_field(); ?>

                                <button type="submit"
                                        class="w-full inline-flex justify-center rounded-xl bg-primary text-white font-bold px-6 py-3 hover:bg-primaryDark transition">
                                    Jiunge na Somo
                                </button>
                            </form>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo e($loginUrl); ?>"
                           class="w-full inline-flex justify-center rounded-xl bg-primary text-white font-bold px-6 py-3 hover:bg-primaryDark transition">
                            Ingia Kuanza
                        </a>

                        <a href="<?php echo e(route('register')); ?>"
                           class="w-full inline-flex justify-center rounded-xl bg-accent text-navy font-bold px-6 py-3 hover:opacity-90 transition">
                            Tengeneza Akaunti
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="mt-6 pt-6 border-t space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Moduli</span>
                        <span class="font-bold text-navy"><?php echo e($totalModules); ?></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Mada</span>
                        <span class="font-bold text-navy"><?php echo e($totalTopics); ?></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Maswali</span>
                        <span class="font-bold text-navy"><?php echo e($totalQuestions); ?></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Cheti</span>
                        <span class="font-bold text-navy">Ndiyo</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="text-xl font-black text-navy">
                    Mwalimu
                </h3>

                <div class="mt-5 flex items-start gap-5">
                    <div class="w-20 h-20 rounded-full bg-transparent border border-primary/20 flex items-center justify-center shrink-0 overflow-hidden">
                        <img src="<?php echo e(asset('images/uzima-logo.png')); ?>"
                             alt="Uzima Milele Ministry"
                             class="w-16 h-16 object-contain">
                    </div>

                    <div class="flex-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showInstructorName): ?>
                            <h4 class="font-black text-navy text-lg">
                                <?php echo e($instructorName); ?>

                            </h4>

                            <p class="mt-1 text-sm font-bold text-primary">
                                <?php echo e($ministryName); ?>

                            </p>
                        <?php else: ?>
                            <h4 class="font-black text-navy text-lg">
                                <?php echo e($ministryName); ?>

                            </h4>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <p class="mt-4 text-sm text-gray-600 leading-relaxed">
                            <?php echo e($ministryBio); ?>

                        </p>
                    </div>
                </div>
            </div>

        </aside>

    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\lessons\show.blade.php ENDPATH**/ ?>