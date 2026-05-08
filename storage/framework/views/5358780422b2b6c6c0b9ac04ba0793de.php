

<?php $__env->startSection('title', 'Instructor Q&A'); ?>

<?php $__env->startSection('content'); ?>

<section class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4">

        
        <div class="relative overflow-hidden mb-8 bg-gradient-to-r from-navy via-primaryDark to-primary rounded-3xl p-8 md:p-10 text-white shadow-lg">
            <div class="relative z-10">
                <p class="text-white/80 text-sm font-bold mb-2">
                    Instructor Q&A
                </p>

                <h1 class="text-3xl md:text-4xl font-black">
                    Maswali ya Wanafunzi
                </h1>

                <p class="text-white/85 mt-3 max-w-2xl">
                    Jibu maswali yaliyoulizwa na wanafunzi kwenye masomo yako.
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

        
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-accent">
                <p class="text-sm text-gray-500">Maswali Yanayosubiri</p>
                <h2 class="text-3xl font-black text-navy mt-2">
                    <?php echo e($pendingCount); ?>

                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-green-500">
                <p class="text-sm text-gray-500">Maswali Yaliyojibiwa</p>
                <h2 class="text-3xl font-black text-green-600 mt-2">
                    <?php echo e($answeredCount); ?>

                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-primary">
                <p class="text-sm text-gray-500">Jumla</p>
                <h2 class="text-3xl font-black text-primary mt-2">
                    <?php echo e($questions->total()); ?>

                </h2>
            </div>
        </div>

        
        <div class="mb-6">
            <a href="<?php echo e(route('instructor.dashboard')); ?>"
               class="inline-flex rounded-xl bg-white border border-gray-200 px-5 py-3 text-navy font-bold hover:bg-gray-100 transition">
                ← Rudi Dashboard
            </a>
        </div>

        
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b">
                <h2 class="text-2xl font-black text-navy">
                    Orodha ya Maswali
                </h2>
            </div>

            <div class="divide-y">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-6 hover:bg-gray-50 transition">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">

                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($question->answer): ?>
                                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                            Answered
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold">
                                            Pending
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($question->is_published): ?>
                                        <span class="px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold">
                                            Published
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">
                                            Hidden
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <p class="text-sm text-gray-500">
                                    Somo:
                                    <span class="font-bold text-navy">
                                        <?php echo e($question->lesson->title ?? 'Somo'); ?>

                                    </span>
                                </p>

                                <p class="text-sm text-gray-500 mt-1">
                                    Mwanafunzi:
                                    <span class="font-bold text-navy">
                                        <?php echo e($question->user->name ?? 'Mwanafunzi'); ?>

                                    </span>
                                    • <?php echo e($question->created_at->format('d M Y, H:i')); ?>

                                </p>

                                <h3 class="mt-4 text-lg font-black text-navy">
                                    <?php echo e($question->question); ?>

                                </h3>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($question->answer): ?>
                                    <div class="mt-4 rounded-2xl bg-primary/5 border border-primary/10 p-4">
                                        <p class="text-sm font-black text-primary">
                                            Jibu:
                                        </p>

                                        <p class="mt-2 text-gray-700 leading-relaxed">
                                            <?php echo e($question->answer); ?>

                                        </p>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="shrink-0">
                                <a href="<?php echo e(route('instructor.questions.show', $question)); ?>"
                                   class="inline-flex justify-center rounded-xl bg-primary px-6 py-3 text-white font-black hover:bg-primaryDark transition">
                                    <?php echo e($question->answer ? 'Hariri Jibu' : 'Jibu Swali'); ?>

                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-10 text-center">
                        <h3 class="text-xl font-black text-navy">
                            Hakuna maswali bado.
                        </h3>

                        <p class="text-gray-500 mt-2">
                            Maswali ya wanafunzi yataonekana hapa.
                        </p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="mt-8">
            <?php echo e($questions->links()); ?>

        </div>

    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\instructor\questions\index.blade.php ENDPATH**/ ?>