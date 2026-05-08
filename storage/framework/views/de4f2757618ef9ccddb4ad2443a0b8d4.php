

<?php $__env->startSection('title', 'Dashibodi ya Mwanafunzi'); ?>

<?php $__env->startSection('content'); ?>

<?php
    use Illuminate\Support\Str;

    $authUser = auth()->user();

    $dashboardProgress = $progressPercent ?? $overallProgress ?? 0;
    $certificateCount = isset($certificates) ? $certificates->count() : 0;
    $attemptsCount = $quizAttempts ?? $totalAttempts ?? 0;
?>

<section class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4">

        
        <div class="relative overflow-hidden mb-10 bg-gradient-to-r from-navy via-primaryDark to-primary rounded-3xl p-8 md:p-10 text-white shadow-lg">
            <div class="relative z-10">
                <p class="text-white/80 text-sm font-bold mb-2">
                    Dashibodi ya Mwanafunzi
                </p>

                <h1 class="text-3xl md:text-4xl font-black">
                    Karibu, <?php echo e($authUser->name ?? 'Mwanafunzi'); ?>

                </h1>

                <p class="text-white/85 mt-3 max-w-2xl">
                    Endelea kujifunza, fuatilia maendeleo yako, kamilisha masomo na upate vyeti vyako.
                </p>
            </div>

            <div class="absolute -right-10 -bottom-10 w-56 h-56 rounded-full bg-white/10"></div>
            <div class="absolute right-32 top-8 w-24 h-24 rounded-full bg-white/10"></div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="mb-6 rounded-2xl bg-green-50 border border-green-200 text-green-700 px-6 py-4 font-bold">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 text-red-700 px-6 py-4 font-bold">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-primary">
                <p class="text-sm text-gray-500">Masomo Yaliyokamilika</p>
                <h2 class="text-3xl font-black text-navy mt-2">
                    <?php echo e($completedLessons ?? 0); ?>/<?php echo e($totalLessons ?? 0); ?>

                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-primary">
                <p class="text-sm text-gray-500">Maendeleo</p>
                <h2 class="text-3xl font-black text-primary mt-2">
                    <?php echo e($dashboardProgress); ?>%
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-accent">
                <p class="text-sm text-gray-500">Majaribio Yaliyofanyika</p>
                <h2 class="text-3xl font-black text-navy mt-2">
                    <?php echo e($attemptsCount); ?>

                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-green-500">
                <p class="text-sm text-gray-500">Vyeti</p>
                <h2 class="text-3xl font-black text-green-600 mt-2">
                    <?php echo e($certificateCount); ?>

                </h2>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-10">
            <div class="flex justify-between mb-3">
                <span class="font-bold text-navy">Maendeleo ya Jumla</span>
                <span class="font-bold text-primary">
                    <?php echo e($dashboardProgress); ?>%
                </span>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                <div class="bg-primary h-4 rounded-full transition-all duration-500"
                     style="width: <?php echo e($dashboardProgress); ?>%">
                </div>
            </div>
        </div>

        
        <div class="mb-10">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-black text-navy">
                    Endelea Kujifunza
                </h2>

                <a href="<?php echo e(route('lessons.index')); ?>"
                   class="hidden sm:inline-flex text-sm font-bold text-primary hover:text-primaryDark">
                    Tazama Masomo Yote →
                </a>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $lessons ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $totalTopics = $lesson->total_topics_count ?? 0;
                        $completedTopics = $lesson->completed_topics_count ?? 0;
                        $lessonProgress = $lesson->progress ?? 0;

                        $lessonImage = $lesson->cover_image ?? $lesson->image ?? null;

                        $certificate = $lesson->certificate ?? null;

                        if (! $certificate && isset($certificates)) {
                            if ($certificates instanceof \Illuminate\Support\Collection) {
                                $certificate = $certificates->get($lesson->id);
                            } else {
                                $certificate = $certificates[$lesson->id] ?? null;
                            }
                        }

                        $canGenerateCertificate = $lesson->can_generate_certificate ?? false;

                        $finalQuizRequired = $lesson->final_quiz_required ?? false;
                        $finalQuizPassed = $lesson->final_quiz_passed ?? true;
                        $finalQuiz = $lesson->final_quiz ?? $lesson->finalQuiz ?? null;

                        $nextTopic = $lesson->next_topic ?? null;
                        $enrolledAt = $lesson->pivot?->enrolled_at ?? null;
                    ?>

                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 hover:shadow-lg transition overflow-hidden">

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($lessonImage)): ?>
                            <img src="<?php echo e(asset('storage/' . $lessonImage)); ?>"
                                 class="w-full h-48 object-cover"
                                 alt="<?php echo e($lesson->title); ?>">
                        <?php else: ?>
                            <div class="h-48 bg-gradient-to-br from-primary to-navy flex items-center justify-center text-white font-black text-2xl">
                                Uzima Milele
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="p-6">
                            <h3 class="font-black text-lg text-navy mb-2 leading-snug">
                                <?php echo e($lesson->title); ?>

                            </h3>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($enrolledAt): ?>
                                <p class="mb-3 text-xs text-gray-500">
                                    Ulijiunga:
                                    <span class="font-bold text-navy">
                                        <?php echo e(\Carbon\Carbon::parse($enrolledAt)->format('d M Y, H:i')); ?>

                                    </span>
                                </p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <p class="text-sm text-gray-500 mb-5 line-clamp-3">
                                <?php echo e(Str::limit(strip_tags($lesson->description ?? 'Hakuna maelezo yaliyowekwa.'), 130)); ?>

                            </p>

                            <div class="mb-4">
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="font-bold text-navy">Maendeleo</span>
                                    <span class="font-bold text-navy"><?php echo e($lessonProgress); ?>%</span>
                                </div>

                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div class="bg-primary h-3 rounded-full"
                                         style="width: <?php echo e($lessonProgress); ?>%">
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-gray-400 mb-4">
                                <?php echo e($completedTopics); ?> / <?php echo e($totalTopics); ?> mada zimekamilika
                            </p>

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nextTopic): ?>
                                <a href="<?php echo e(route('lessons.topics.show', [$lesson, $nextTopic])); ?>"
                                   class="block text-center bg-navy hover:bg-primaryDark text-white font-bold py-3 rounded-xl transition">
                                    <?php echo e($lessonProgress > 0 ? 'Endelea Kusoma' : 'Anza Kusoma'); ?>

                                </a>
                            <?php elseif($totalTopics > 0 && $lessonProgress >= 100): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($finalQuizRequired && !$finalQuizPassed && $finalQuiz): ?>
                                    <a href="<?php echo e(route('quiz.show', $finalQuiz->id)); ?>"
                                       class="block text-center bg-accent hover:bg-yellow-500 text-navy font-bold py-3 rounded-xl transition">
                                        Fanya Jaribio la Mwisho
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo e(route('lessons.show', $lesson->slug)); ?>"
                                       class="block text-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition">
                                        Somo Limekamilika
                                    </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                <a href="<?php echo e(route('lessons.show', $lesson->slug)); ?>"
                                   class="block text-center bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 rounded-xl transition">
                                    Hakuna Mada
                                </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($certificate): ?>
                                <a href="<?php echo e(route('certificates.show', $certificate->certificate_number)); ?>"
                                   class="mt-3 block text-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition">
                                    Tazama Cheti
                                </a>

                                <a href="<?php echo e(route('certificates.download', $certificate->certificate_number)); ?>"
                                   class="mt-2 block text-center bg-navy hover:bg-primaryDark text-white font-bold py-3 rounded-xl transition">
                                    Download Cheti
                                </a>
                            <?php elseif($lessonProgress < 100): ?>
                                <p class="mt-3 text-xs text-gray-500 text-center">
                                    Kamilisha mada zote ili kupata cheti.
                                </p>
                            <?php elseif($finalQuizRequired && !$finalQuizPassed && $finalQuiz): ?>
                                <p class="mt-2 text-xs text-gray-500 text-center">
                                    Lazima ufaulu jaribio la mwisho ili kupata cheti.
                                </p>
                            <?php elseif($canGenerateCertificate): ?>
                                <form action="<?php echo e(route('certificates.issue', $lesson->id)); ?>" method="POST" class="mt-3">
                                    <?php echo csrf_field(); ?>

                                    <button type="submit"
                                            class="w-full bg-accent hover:bg-yellow-500 text-navy font-bold py-3 rounded-xl transition">
                                        Tengeneza Cheti
                                    </button>
                                </form>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="sm:col-span-2 lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
                        <h3 class="text-xl font-black text-navy">
                            Bado hujajiunga na somo lolote.
                        </h3>

                        <p class="text-gray-500 mt-2">
                            Fungua orodha ya masomo kisha bonyeza “Jiunge na Somo” ili somo lionekane hapa.
                        </p>

                        <a href="<?php echo e(route('lessons.index')); ?>"
                           class="inline-flex mt-6 bg-primary hover:bg-primaryDark text-white font-bold px-6 py-3 rounded-xl transition">
                            Tazama Masomo
                        </a>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="sm:hidden mt-6">
                <a href="<?php echo e(route('lessons.index')); ?>"
                   class="block text-center bg-white border border-gray-200 rounded-xl py-3 font-bold text-primary">
                    Tazama Masomo Yote
                </a>
            </div>
        </div>

    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\student\dashboard.blade.php ENDPATH**/ ?>