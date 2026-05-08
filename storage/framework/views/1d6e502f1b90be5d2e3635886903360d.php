

<?php $__env->startSection('title', 'Masomo ya Biblia'); ?>

<?php use Illuminate\Support\Str; ?>

<?php $__env->startSection('content'); ?>

<section class="bg-navy text-white py-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-accent font-bold uppercase text-sm">
            Jifunze Biblia
        </p>

        <h1 class="mt-3 text-4xl md:text-5xl font-black">
            Masomo ya Biblia
        </h1>

        <p class="mt-4 text-white/80 max-w-2xl mx-auto">
            Jifunze masomo ya Biblia hatua kwa hatua na ukue kiroho kila siku.
        </p>
    </div>
</section>

<section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 lg:grid-cols-3 gap-8">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

            <a href="<?php echo e(route('lessons.show', $lesson->slug)); ?>"
               class="group bg-white rounded-2xl shadow hover:shadow-xl transition overflow-hidden border border-gray-100">

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lesson->cover_image): ?>
                    <img src="<?php echo e(asset('storage/'.$lesson->cover_image)); ?>"
                         alt="<?php echo e($lesson->title); ?>"
                         class="w-full h-48 object-cover group-hover:scale-105 transition duration-500">
                <?php else: ?>
                    <div class="h-48 bg-primary flex items-center justify-center text-white font-bold">
                        Somo la Uzima Milele
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="p-6">
                    <span class="inline-flex items-center rounded-full bg-primary/10 text-primary text-xs font-bold px-3 py-1 mb-4">
                        Somo la Biblia
                    </span>

                    <h2 class="text-xl font-black text-navy leading-snug group-hover:text-primary transition">
                        <?php echo e($lesson->title); ?>

                    </h2>

                    <p class="text-sm text-gray-600 mt-3 leading-relaxed">
                        <?php echo e(Str::limit($lesson->description, 120)); ?>

                    </p>

                    <div class="mt-6 inline-flex items-center text-primary font-bold group-hover:text-primaryDark transition">
                        Tazama Somo →
                    </div>
                </div>
            </a>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="md:col-span-2 lg:col-span-3 bg-white rounded-2xl p-10 text-center shadow-sm">
                <h2 class="text-xl font-black text-navy">
                    Hakuna masomo yaliyopatikana.
                </h2>

                <p class="text-gray-500 mt-2">
                    Tafadhali rudi tena baadaye.
                </p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\lessons\index.blade.php ENDPATH**/ ?>