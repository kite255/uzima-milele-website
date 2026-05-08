

<?php $__env->startSection('title', $lesson->title); ?>

<?php $__env->startSection('content'); ?>

<section class="bg-gray-50 py-10">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        
        <aside class="relative z-10 bg-white rounded-2xl shadow-sm p-6 h-fit mb-8 lg:mb-0 lg:sticky lg:top-32">
            <h2 class="text-2xl font-black text-navy mb-6">
                Yaliyomo kwenye Somo
            </h2>

            
            <div class="mb-6">
                <div class="flex justify-between text-sm mb-2">
                    <span class="font-bold text-navy">Maendeleo</span>
                    <span class="font-bold text-primary"><?php echo e($progressPercent ?? 0); ?>%</span>
                </div>

                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div class="bg-primary h-3 rounded-full"
                         style="width: <?php echo e($progressPercent ?? 0); ?>%">
                    </div>
                </div>

                <p class="text-xs text-gray-500 mt-2">
                    <?php echo e($completedTopicsCount ?? 0); ?> / <?php echo e($totalTopics ?? 0); ?> mada zimekamilika
                </p>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $lesson->modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="mb-6 last:mb-0">
                    <h3 class="font-black text-primary mb-3">
                        <?php echo e($module->title); ?>

                    </h3>

                    <div class="space-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $module->topics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                            <?php
                                $isActiveTopic = $currentTopic && $currentTopic->id === $topic->id;
                                $isCompletedTopic = in_array($topic->id, $completedTopicIds ?? []);
                            ?>

                            <a href="<?php echo e(route('lessons.learn', ['lesson' => $lesson->slug, 'topic' => $topic->id])); ?>"
                               class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-xl border px-4 py-3 text-sm transition
                                      <?php echo e($isActiveTopic
                                            ? 'bg-primary text-white border-primary shadow'
                                            : 'bg-white text-gray-700 border-gray-200 hover:border-primary/40 hover:text-primary'); ?>">

                                <span class="font-semibold leading-relaxed">
                                    <?php echo e($loop->iteration); ?>. <?php echo e($topic->title); ?>

                                </span>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isCompletedTopic): ?>
                                    <span class="w-fit text-xs font-black <?php echo e($isActiveTopic ? 'text-white' : 'text-green-600'); ?>">
                                        Imekamilika
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                            <p class="text-sm text-gray-500">
                                Hakuna mada kwenye module hii.
                            </p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-gray-500">
                    Hakuna module zilizochapishwa.
                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </aside>

        
        <main class="relative z-0 font-lato lg:col-span-2 bg-white rounded-2xl shadow-sm p-6 md:p-10 min-w-0">

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                <div class="mb-6 rounded-xl bg-green-50 text-green-700 border border-green-200 px-5 py-4 font-bold">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                <div class="mb-6 rounded-xl bg-red-50 text-red-700 border border-red-200 px-5 py-4 font-bold">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentTopic): ?>

                <?php
                    $currentTopicCompleted = in_array($currentTopic->id, $completedTopicIds ?? []);
                ?>

                <div class="mb-6">
                    <p class="text-sm text-primary font-bold uppercase tracking-wide">
                        Mada ya Somo
                    </p>

                    <h2 class="text-3xl md:text-4xl font-black text-navy mt-2 leading-tight">
                        <?php echo e($currentTopic->title); ?>

                    </h2>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentTopic->video_url): ?>
                    <?php
                        $videoUrl = $currentTopic->video_url;

                        if (str_contains($videoUrl, 'youtube.com/watch?v=')) {
                            $videoUrl = str_replace('watch?v=', 'embed/', $videoUrl);
                        }

                        if (str_contains($videoUrl, 'youtu.be/')) {
                            $videoId = basename(parse_url($videoUrl, PHP_URL_PATH));
                            $videoUrl = 'https://www.youtube.com/embed/' . $videoId;
                        }

                        if (str_contains($videoUrl, 'youtube.com/live/')) {
                            $path = parse_url($videoUrl, PHP_URL_PATH);
                            $videoId = str_replace('/live/', '', $path);
                            $videoUrl = 'https://www.youtube.com/embed/' . $videoId;
                        }

                        $videoUrl = strtok($videoUrl, '?');
                    ?>

                    <div class="mb-8 rounded-2xl overflow-hidden shadow-lg border border-gray-200">
                        <div class="aspect-video bg-black">
                            <iframe
                                class="w-full h-full"
                                src="<?php echo e($videoUrl); ?>"
                                title="<?php echo e($currentTopic->title); ?>"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                <?php elseif($lesson->cover_image): ?>
                    <img src="<?php echo e(asset('storage/' . $lesson->cover_image)); ?>"
                         alt="<?php echo e($lesson->title); ?>"
                         class="w-full h-72 object-cover rounded-2xl mb-8">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="font-lato max-w-none text-gray-700 leading-relaxed space-y-4
                            [&_h2]:text-2xl [&_h2]:font-black [&_h2]:text-navy [&_h2]:mt-6 [&_h2]:mb-3
                            [&_h3]:text-xl [&_h3]:font-black [&_h3]:text-navy [&_h3]:mt-5 [&_h3]:mb-2
                            [&_p]:text-base [&_p]:leading-8 [&_p]:text-gray-700
                            [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6
                            [&_li]:mb-2 [&_a]:text-primary [&_a]:font-bold">
                    <?php echo html_entity_decode($currentTopic->content); ?>

                </div>

                
                <div class="mt-8 flex flex-col sm:flex-row gap-4">

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $currentTopicCompleted): ?>
                        <form method="POST" action="<?php echo e(route('lessons.topics.complete', [$lesson, $currentTopic])); ?>">
                            <?php echo csrf_field(); ?>

                            <button type="submit"
                                    class="w-full sm:w-auto bg-green-600 text-white font-bold px-6 py-3 rounded-xl shadow hover:bg-green-700 transition">
                                Nimemaliza Somo Hili
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="inline-flex items-center justify-center bg-green-50 text-green-700 border border-green-200 font-bold px-6 py-3 rounded-xl text-center">
                            Imekamilika
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentTopic->pdf): ?>
                        <a href="<?php echo e(asset('storage/' . $currentTopic->pdf)); ?>"
                           target="_blank"
                           class="inline-flex items-center justify-center bg-accent text-navy font-bold px-6 py-3 rounded-xl shadow hover:opacity-90 transition text-center">
                            Pakua PDF
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentTopic->quiz): ?>
                        <a href="<?php echo e(route('quiz.show', $currentTopic->quiz->id)); ?>"
                           class="inline-flex items-center justify-center bg-primary text-white font-bold px-6 py-3 rounded-xl shadow hover:bg-primaryDark transition text-center">
                            Jibu Maswali
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div class="mt-12 pt-6 border-t flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($previousTopic): ?>
                        <a href="<?php echo e(route('lessons.learn', ['lesson' => $lesson->slug, 'topic' => $previousTopic->id])); ?>"
                           class="rounded-xl border border-gray-200 px-5 py-3 text-sm font-bold text-gray-700 hover:text-primary hover:border-primary/40 transition">
                            Iliyopita: <?php echo e($previousTopic->title); ?>

                        </a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nextTopic): ?>
                        <?php
                            $nextLocked = ! $currentTopicCompleted;
                        ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nextLocked): ?>
                            <span class="rounded-xl bg-gray-100 px-5 py-3 text-sm font-bold text-gray-400 text-center cursor-not-allowed">
                                Kamilisha mada hii ili kuendelea
                            </span>
                        <?php else: ?>
                            <a href="<?php echo e(route('lessons.learn', ['lesson' => $lesson->slug, 'topic' => $nextTopic->id])); ?>"
                               class="rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white hover:bg-primaryDark transition text-center">
                                Inayofuata: <?php echo e($nextTopic->title); ?>

                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allTopicsCompleted ?? false): ?>
                    <div class="mt-10 rounded-2xl border border-green-200 bg-green-50 p-6">
                        <h3 class="text-xl font-black text-green-700">
                            Hongera! Umekamilisha mada zote za somo hili.
                        </h3>

                        <p class="mt-2 text-sm text-green-700/80">
                            Hatua inayofuata ni kufanya jaribio la mwisho au kupata cheti cha kukamilisha somo.
                        </p>

                        <div class="mt-5 flex flex-col sm:flex-row flex-wrap gap-3">

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($finalQuiz ?? null) && ! ($finalQuizPassed ?? false)): ?>
                                <a href="<?php echo e(route('quiz.show', $finalQuiz->id)); ?>"
                                   class="inline-flex items-center justify-center rounded-xl bg-accent px-6 py-3 text-sm font-black text-navy hover:bg-yellow-500 transition">
                                    Fanya Jaribio la Mwisho
                                </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($certificate ?? null): ?>
                                <a href="<?php echo e(route('certificates.show', $certificate->certificate_number)); ?>"
                                   class="inline-flex items-center justify-center rounded-xl bg-green-600 px-6 py-3 text-sm font-black text-white hover:bg-green-700 transition">
                                    Tazama Cheti
                                </a>

                                <a href="<?php echo e(route('certificates.download', $certificate->certificate_number)); ?>"
                                   class="inline-flex items-center justify-center rounded-xl border border-green-300 bg-white px-6 py-3 text-sm font-black text-green-700 hover:bg-green-100 transition">
                                    Pakua Cheti
                                </a>
                            <?php elseif($canGenerateCertificate ?? false): ?>
                                <form method="POST" action="<?php echo e(route('certificates.issue', $lesson->id)); ?>">
                                    <?php echo csrf_field(); ?>

                                    <button type="submit"
                                            class="w-full sm:w-auto rounded-xl bg-accent px-6 py-3 text-sm font-black text-navy hover:bg-yellow-500 transition">
                                        Tengeneza Cheti
                                    </button>
                                </form>
                            <?php elseif(($finalQuiz ?? null) && ! ($finalQuizPassed ?? false)): ?>
                                <span class="inline-flex items-center justify-center rounded-xl bg-white px-6 py-3 text-sm font-bold text-gray-500 border border-green-200">
                                    Lazima ufaulu jaribio la mwisho ili kupata cheti.
                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php else: ?>
                <div class="text-center py-20">
                    <h2 class="text-2xl font-black text-navy">
                        Hakuna mada inayopatikana
                    </h2>

                    <p class="text-gray-500 mt-2">
                        Somo hili bado halina mada zilizochapishwa.
                    </p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        </main>

    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/uzimamil/public_html/new.uzimamilele.or.tz/new/resources/views/lessons/learn.blade.php ENDPATH**/ ?>