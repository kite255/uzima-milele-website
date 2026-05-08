

<?php $__env->startSection('content'); ?>

<section class="bg-gray-50 py-12 md:py-20">
    <div class="max-w-4xl mx-auto px-4">

        <a href="<?php echo e(route('devotions.index')); ?>"
           class="inline-flex mb-8 text-primary font-bold hover:text-primaryDark">
            ← Rudi kwenye tafakari
        </a>

        <article class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($devotion->image): ?>
                <img src="<?php echo e(asset('storage/'.$devotion->image)); ?>"
                     alt="<?php echo e($devotion->title); ?>"
                     class="w-full h-72 md:h-[420px] object-cover">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="p-6 md:p-10">

                <div class="inline-flex bg-primary/10 text-primary px-3 py-1 rounded-full text-xs font-bold mb-5">
                    <?php echo e($devotion->published_at ? \Carbon\Carbon::parse($devotion->published_at)->format('d M Y') : 'Haijapangiwa'); ?>

                </div>

                <h1 class="text-3xl md:text-5xl font-black text-navy leading-tight mb-6">
                    <?php echo e($devotion->title); ?>

                </h1>

                <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                    <?php echo $devotion->content; ?>

                </div>

            </div>
        </article>

        <div class="mt-10 flex flex-wrap gap-4 justify-between">
            <a href="<?php echo e(route('devotions.index')); ?>"
               class="bg-white border border-gray-200 text-navy px-6 py-3 rounded-xl font-bold hover:bg-gray-100 transition">
                Tafakari nyingine
            </a>

            <a href="/contact"
               class="bg-primary text-white px-6 py-3 rounded-xl font-bold hover:bg-primaryDark transition">
                Tuma maombi / ushuhuda
            </a>
        </div>

    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\devotions\show.blade.php ENDPATH**/ ?>