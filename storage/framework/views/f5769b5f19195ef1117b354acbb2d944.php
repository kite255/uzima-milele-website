

<?php $__env->startSection('title', 'Jibu Swali'); ?>

<?php $__env->startSection('content'); ?>

<section class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-5xl mx-auto px-4">

        
        <div class="mb-6">
            <a href="<?php echo e(route('instructor.questions.index')); ?>"
               class="inline-flex rounded-xl bg-white border border-gray-200 px-5 py-3 text-navy font-bold hover:bg-gray-100 transition">
                ← Rudi kwenye Maswali
            </a>
        </div>

        
        <div class="bg-gradient-to-r from-navy via-primaryDark to-primary rounded-3xl p-8 md:p-10 text-white shadow-lg mb-8">
            <p class="text-white/80 text-sm font-bold mb-2">
                Instructor Q&A
            </p>

            <h1 class="text-3xl md:text-4xl font-black">
                Jibu Swali la Mwanafunzi
            </h1>

            <p class="text-white/85 mt-3">
                Andika jibu fupi, wazi, na lenye kujenga kiroho.
            </p>
        </div>

        
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">
            <div class="flex flex-wrap items-center gap-2 mb-5">
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

            <div class="grid md:grid-cols-2 gap-5 mb-6">
                <div class="rounded-2xl bg-gray-50 border border-gray-100 p-5">
                    <p class="text-xs uppercase tracking-widest text-gray-400 font-bold">
                        Somo
                    </p>

                    <p class="mt-2 font-black text-navy">
                        <?php echo e($question->lesson->title ?? 'Somo'); ?>

                    </p>
                </div>

                <div class="rounded-2xl bg-gray-50 border border-gray-100 p-5">
                    <p class="text-xs uppercase tracking-widest text-gray-400 font-bold">
                        Mwanafunzi
                    </p>

                    <p class="mt-2 font-black text-navy">
                        <?php echo e($question->user->name ?? 'Mwanafunzi'); ?>

                    </p>

                    <p class="text-sm text-gray-500 mt-1">
                        <?php echo e($question->created_at->format('d M Y, H:i')); ?>

                    </p>
                </div>
            </div>

            <div class="rounded-2xl bg-primary/5 border border-primary/10 p-5">
                <p class="text-sm font-black text-primary">
                    Swali la Mwanafunzi
                </p>

                <p class="mt-3 text-lg text-navy leading-relaxed font-bold">
                    <?php echo e($question->question); ?>

                </p>
            </div>
        </div>

        
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
            <h2 class="text-2xl font-black text-navy mb-2">
                <?php echo e($question->answer ? 'Hariri Jibu' : 'Andika Jibu'); ?>

            </h2>

            <p class="text-gray-500 mb-6">
                Jibu hili litaonekana kwenye ukurasa wa somo kwa wanafunzi.
            </p>

            <form method="POST" action="<?php echo e(route('instructor.questions.update', $question)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div>
                    <label for="answer" class="block text-sm font-black text-navy mb-2">
                        Jibu
                    </label>

                    <textarea id="answer"
                              name="answer"
                              rows="8"
                              required
                              placeholder="Andika jibu hapa..."
                              class="w-full rounded-2xl border border-gray-300 bg-white px-5 py-4 text-base text-navy placeholder-gray-400 shadow-sm outline-none resize-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"><?php echo e(old('answer', $question->answer)); ?></textarea>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['answer'];
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

                <div class="mt-5 flex items-center gap-3">
                    <input type="checkbox"
                           id="is_published"
                           name="is_published"
                           value="1"
                           class="rounded border-gray-300 text-primary focus:ring-primary"
                           <?php if(old('is_published', $question->is_published)): echo 'checked'; endif; ?>>

                    <label for="is_published" class="text-sm font-bold text-navy">
                        Onyesha swali na jibu kwenye ukurasa wa somo
                    </label>
                </div>

                <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-end">
                    <a href="<?php echo e(route('instructor.questions.index')); ?>"
                       class="inline-flex justify-center rounded-xl bg-gray-100 px-6 py-3 text-navy font-bold hover:bg-gray-200 transition">
                        Ghairi
                    </a>

                    <button type="submit"
                            class="inline-flex justify-center rounded-xl bg-primary px-8 py-3 text-white font-black hover:bg-primaryDark transition">
                        Hifadhi Jibu
                    </button>
                </div>
            </form>
        </div>

    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/uzimamil/public_html/new.uzimamilele.or.tz/new/resources/views/instructor/questions/show.blade.php ENDPATH**/ ?>