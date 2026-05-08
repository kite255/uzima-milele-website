<div>
    <div class="space-y-6">

        <div class="rounded-xl bg-yellow-50 border border-yellow-200 p-4">
            <h2 class="text-lg font-bold text-yellow-800">
                Overdue Students
            </h2>

            <p class="text-sm text-yellow-700 mt-1">
                Students shown here enrolled more than 10 days ago and have not completed all published topics.
                SMS is manual only.
            </p>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-600">
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3">Lesson</th>
                        <th class="px-4 py-3">Progress</th>
                        <th class="px-4 py-3">Days</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-4 py-4">
                                <div class="font-bold text-gray-900">
                                    <?php echo e($row['name']); ?>

                                </div>
                                <div class="text-xs text-gray-500">
                                    User ID: <?php echo e($row['user_id']); ?>

                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <div><?php echo e($row['email'] ?? 'No email'); ?></div>
                                <div class="text-xs text-gray-500">
                                    <?php echo e($row['phone'] ?? 'No phone'); ?>

                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <div class="font-semibold text-gray-800">
                                    <?php echo e($row['lesson_title']); ?>

                                </div>
                                <div class="text-xs text-gray-500">
                                    Lesson ID: <?php echo e($row['lesson_id']); ?>

                                </div>
                            </td>

                            <td class="px-4 py-4 min-w-[180px]">
                                <div class="flex justify-between text-xs mb-1">
                                    <span><?php echo e($row['completed_topics']); ?>/<?php echo e($row['total_topics']); ?> topics</span>
                                    <span><?php echo e($row['progress']); ?>%</span>
                                </div>

                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-2 bg-primary-600 rounded-full"
                                         style="width: <?php echo e($row['progress']); ?>%">
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <span class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700">
                                    <?php echo e($row['days']); ?> days
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex flex-wrap justify-end gap-2">

                                    <button type="button"
                                            wire:click="sendManualReminder(<?php echo e($row['enrollment_id']); ?>, 'both')"
                                            class="rounded-lg bg-primary-600 px-3 py-2 text-xs font-bold text-white hover:bg-primary-700">
                                        Email + Notify
                                    </button>

                                    <button type="button"
                                            wire:click="sendManualReminder(<?php echo e($row['enrollment_id']); ?>, 'sms')"
                                            class="rounded-lg bg-yellow-500 px-3 py-2 text-xs font-bold text-white hover:bg-yellow-600">
                                        SMS
                                    </button>

                                    <button type="button"
                                            wire:click="sendManualReminder(<?php echo e($row['enrollment_id']); ?>, 'all')"
                                            class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-bold text-white hover:bg-black">
                                        All
                                    </button>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center">
                                <h3 class="font-bold text-gray-900">
                                    No overdue students found.
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Students will appear here after more than 10 days if they have not completed their lessons.
                                </p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
<?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\filament\pages\overdue-students.blade.php ENDPATH**/ ?>