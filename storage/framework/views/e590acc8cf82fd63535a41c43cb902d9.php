<div>
    <div class="space-y-6">

        <div style="border:1px solid #facc15;background:#fefce8;padding:16px;border-radius:12px;">
            <h2 style="font-weight:800;color:#854d0e;font-size:18px;">
                Overdue Students
            </h2>

            <p style="font-size:14px;color:#854d0e;margin-top:4px;">
                Students shown here enrolled more than 10 days ago and have not completed all published topics.
                SMS is manual only.
            </p>
        </div>

        <div style="overflow-x:auto;border:1px solid #e5e7eb;background:white;border-radius:12px;">
            <table style="width:100%;font-size:14px;border-collapse:collapse;">
                <thead style="background:#f9fafb;">
                    <tr>
                        <th style="padding:12px;text-align:left;">Student</th>
                        <th style="padding:12px;text-align:left;">Contact</th>
                        <th style="padding:12px;text-align:left;">Lesson</th>
                        <th style="padding:12px;text-align:left;">Progress</th>
                        <th style="padding:12px;text-align:left;">Days</th>
                        <th style="padding:12px;text-align:right;min-width:300px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr style="border-top:1px solid #f3f4f6;">
                            <td style="padding:16px;">
                                <strong><?php echo e($row['name']); ?></strong>
                                <div style="font-size:12px;color:#6b7280;">
                                    User ID: <?php echo e($row['user_id']); ?>

                                </div>
                            </td>

                            <td style="padding:16px;">
                                <div><?php echo e($row['email'] ?? 'No email'); ?></div>
                                <div style="font-size:12px;color:#6b7280;">
                                    <?php echo e($row['phone'] ?? 'No phone'); ?>

                                </div>
                            </td>

                            <td style="padding:16px;">
                                <strong><?php echo e($row['lesson_title']); ?></strong>
                                <div style="font-size:12px;color:#6b7280;">
                                    Lesson ID: <?php echo e($row['lesson_id']); ?>

                                </div>
                            </td>

                            <td style="padding:16px;">
                                <?php echo e($row['completed_topics']); ?>/<?php echo e($row['total_topics']); ?> topics
                                &nbsp;
                                <?php echo e($row['progress']); ?>%
                            </td>

                            <td style="padding:16px;">
                                <strong><?php echo e($row['days']); ?> days</strong>
                            </td>

                            <td style="padding:16px;text-align:right;min-width:300px;">
                                <button type="button"
                                        wire:click="sendManualReminder(<?php echo e($row['enrollment_id']); ?>, 'both')"
                                        style="background:#0083CB;color:white;padding:8px 12px;border-radius:8px;font-weight:700;font-size:12px;margin-right:6px;">
                                    Email + Notify
                                </button>

                                <button type="button"
                                        wire:click="sendManualReminder(<?php echo e($row['enrollment_id']); ?>, 'sms')"
                                        style="background:#F4B122;color:#0E3D4F;padding:8px 12px;border-radius:8px;font-weight:700;font-size:12px;margin-right:6px;">
                                    SMS
                                </button>

                                <button type="button"
                                        wire:click="sendManualReminder(<?php echo e($row['enrollment_id']); ?>, 'all')"
                                        style="background:#0E3D4F;color:white;padding:8px 12px;border-radius:8px;font-weight:700;font-size:12px;">
                                    All
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" style="padding:40px;text-align:center;">
                                <strong>No overdue students found.</strong>
                                <p style="font-size:14px;color:#6b7280;margin-top:4px;">
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
<?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\filament\pages\overdue-students-fixed.blade.php ENDPATH**/ ?>