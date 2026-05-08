

<?php $__env->startSection('title', $topic->title); ?>

<?php $__env->startSection('content'); ?>
<section class="bg-gray-50 py-10">
    <div class="max-w-4xl mx-auto px-4">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="mb-6 rounded-xl bg-green-50 border border-green-200 px-5 py-4 text-green-700 font-bold">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="bg-navy px-6 py-6 text-white">
                <p class="text-sm text-white/70 font-bold">
                    <?php echo e($lesson->title); ?>

                </p>

                <h1 class="text-2xl md:text-3xl font-black mt-1">
                    <?php echo e($topic->title); ?>

                </h1>

                <p class="text-sm text-white/80 mt-2">
                    Module: <?php echo e($topic->module->title); ?>

                </p>
            </div>

            <div class="p-6 md:p-8 space-y-8">

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($topic->video_url): ?>
                    <div>
                        <h2 class="text-lg font-black text-navy mb-3">Video</h2>

                        <div class="aspect-video rounded-2xl overflow-hidden bg-black">
                            <iframe class="w-full h-full"
                                    src="<?php echo e($topic->video_url); ?>"
                                    frameborder="0"
                                    allowfullscreen></iframe>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="prose max-w-none text-gray-700 leading-8">
                    <?php echo $topic->content; ?>

                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($topic->pdf): ?>
                    <a href="<?php echo e(asset('storage/' . $topic->pdf)); ?>"
                       target="_blank"
                       class="inline-flex rounded-xl bg-accent px-5 py-3 text-sm font-black text-navy hover:opacity-90 transition">
                        Pakua PDF
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="border-t pt-6 flex flex-col md:flex-row gap-3 md:items-center md:justify-between">

                    <div class="flex flex-wrap gap-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($previousTopic): ?>
                            <a href="<?php echo e(route('lessons.topics.show', [$lesson, $previousTopic])); ?>"
                               class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-bold text-navy hover:bg-gray-50">
                                ← Iliyopita
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <a href="<?php echo e(route('student.dashboard')); ?>"
                           class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-bold text-navy hover:bg-gray-50">
                            Dashboard
                        </a>
                    </div>

                    <div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isCompleted): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nextTopic): ?>
                                <a href="<?php echo e(route('lessons.topics.show', [$lesson, $nextTopic])); ?>"
                                   class="rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white hover:bg-primaryDark">
                                    Topic Inayofuata →
                                </a>
                            <?php else: ?>
                                <a href="<?php echo e(route('student.dashboard')); ?>"
                                   class="rounded-xl bg-green-600 px-5 py-3 text-sm font-bold text-white">
                                    Rudi Dashboard
                                </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php else: ?>
                            <form method="POST" action="<?php echo e(route('lessons.topics.complete', [$lesson, $topic])); ?>">
                                <?php echo csrf_field(); ?>

                                <button type="submit"
                                        class="rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white hover:bg-primaryDark">
                                    Nimemaliza Somo Hili
                                </button>
                            </form>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                </div>

            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\student\topic-show.blade.php ENDPATH**/ ?>