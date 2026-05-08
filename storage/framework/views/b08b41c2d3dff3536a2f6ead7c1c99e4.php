

<?php $__env->startSection('title', 'Instructor Dashboard'); ?>

<?php $__env->startSection('content'); ?>

<section class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4">

        
        <div class="relative overflow-hidden mb-10 bg-gradient-to-r from-navy via-primaryDark to-primary rounded-3xl p-8 md:p-10 text-white shadow-lg">
            <div class="relative z-10">
                <p class="text-white/80 text-sm font-bold mb-2">
                    Instructor Dashboard
                </p>

                <h1 class="text-3xl md:text-4xl font-black">
                    Karibu, <?php echo e(auth()->user()->name); ?>

                </h1>

                <p class="text-white/85 mt-3 max-w-2xl">
                    Fuatilia masomo yako, wanafunzi, maswali ya Q&A, na vyeti vilivyotolewa.
                </p>
            </div>

            <div class="absolute -right-10 -bottom-10 w-56 h-56 rounded-full bg-white/10"></div>
            <div class="absolute right-32 top-8 w-24 h-24 rounded-full bg-white/10"></div>
        </div>

        
        <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-10">
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-primary">
                <p class="text-sm text-gray-500">Masomo Yangu</p>
                <h2 class="text-3xl font-black text-navy mt-2">
                    <?php echo e($totalLessons); ?>

                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-primary">
                <p class="text-sm text-gray-500">Wanafunzi</p>
                <h2 class="text-3xl font-black text-primary mt-2">
                    <?php echo e($totalStudents); ?>

                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-accent">
                <p class="text-sm text-gray-500">Maswali Mapya</p>
                <h2 class="text-3xl font-black text-navy mt-2">
                    <?php echo e($pendingQuestions); ?>

                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-green-500">
                <p class="text-sm text-gray-500">Yaliyojibiwa</p>
                <h2 class="text-3xl font-black text-green-600 mt-2">
                    <?php echo e($answeredQuestions); ?>

                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 border-t-4 border-navy">
                <p class="text-sm text-gray-500">Vyeti</p>
                <h2 class="text-3xl font-black text-navy mt-2">
                    <?php echo e($certificatesIssued); ?>

                </h2>
            </div>
        </div>

        
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-10">
            <h2 class="text-2xl font-black text-navy mb-5">
                Quick Actions
            </h2>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="<?php echo e(route('instructor.questions.index')); ?>"
                   class="rounded-2xl bg-accent/20 text-navy font-black p-5 hover:bg-accent transition">
                    Answer Q&A
                </a>

                <a href="<?php echo e(route('lessons.index')); ?>"
                   class="rounded-2xl bg-gray-100 text-navy font-black p-5 hover:bg-gray-200 transition">
                    View Public Lessons
                </a>

                <a href="<?php echo e(route('instructor.dashboard')); ?>"
                   class="rounded-2xl bg-primary/10 text-primary font-black p-5 hover:bg-primary hover:text-white transition">
                    Refresh Dashboard
                </a>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role === 'admin'): ?>
                    <a href="<?php echo e(url('/admin')); ?>"
                       class="rounded-2xl bg-navy text-white font-black p-5 hover:bg-primaryDark transition">
                        Open Admin Panel
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-10">
            <div class="p-6 border-b">
                <h2 class="text-2xl font-black text-navy">
                    Masomo Yangu
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 text-sm">
                        <tr>
                            <th class="px-6 py-4">Somo</th>
                            <th class="px-6 py-4">Modules</th>
                            <th class="px-6 py-4">Topics</th>
                            <th class="px-6 py-4">Students</th>
                            <th class="px-6 py-4">Questions</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <p class="font-black text-navy">
                                        <?php echo e($lesson->title); ?>

                                    </p>

                                    <p class="text-xs text-gray-500">
                                        <?php echo e($lesson->category ?? 'No category'); ?>

                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <?php echo e($lesson->modules_count); ?>

                                </td>

                                <td class="px-6 py-4">
                                    <?php echo e($lesson->topics_count); ?>

                                </td>

                                <td class="px-6 py-4">
                                    <?php echo e($lesson->enrollments_count); ?>

                                </td>

                                <td class="px-6 py-4">
                                    <?php echo e($lesson->questions_count); ?>

                                </td>

                                <td class="px-6 py-4">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lesson->is_published): ?>
                                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                            Published
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">
                                            Draft
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <a href="<?php echo e(route('lessons.show', $lesson->slug)); ?>"
                                           class="text-primary font-bold hover:underline">
                                            View
                                        </a>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role === 'admin'): ?>
                                            <a href="<?php echo e(url('/admin/lessons/' . $lesson->id . '/edit')); ?>"
                                               class="text-navy font-bold hover:underline">
                                                Edit
                                            </a>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                    Huna masomo yoyote bado.
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b flex items-center justify-between">
                <h2 class="text-2xl font-black text-navy">
                    Maswali ya Hivi Karibuni
                </h2>

                <a href="<?php echo e(route('instructor.questions.index')); ?>"
                   class="text-primary font-bold hover:underline">
                    Answer Questions →
                </a>
            </div>

            <div class="divide-y">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentQuestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div>
                                <p class="font-black text-navy">
                                    <?php echo e($question->user->name ?? 'Mwanafunzi'); ?>

                                </p>

                                <p class="text-xs text-gray-500 mt-1">
                                    <?php echo e($question->lesson->title ?? 'Somo'); ?> • <?php echo e($question->created_at->format('d M Y, H:i')); ?>

                                </p>

                                <p class="mt-3 text-gray-700">
                                    <?php echo e($question->question); ?>

                                </p>
                            </div>

                            <div class="shrink-0 flex flex-col gap-2 items-start md:items-end">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($question->answer): ?>
                                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                        Answered
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold">
                                        Pending
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <a href="<?php echo e(route('instructor.questions.show', $question)); ?>"
                                   class="text-primary font-bold hover:underline text-sm">
                                    <?php echo e($question->answer ? 'Hariri Jibu' : 'Jibu Swali'); ?>

                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-10 text-center text-gray-500">
                        Hakuna maswali bado.
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/uzimamil/public_html/new.uzimamilele.or.tz/new/resources/views/instructor/dashboard.blade.php ENDPATH**/ ?>