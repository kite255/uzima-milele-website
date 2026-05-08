

<?php $__env->startSection('content'); ?>


<section class="relative h-[240px] md:h-[330px] flex items-center justify-center overflow-hidden bg-navy">
    <img src="https://images.unsplash.com/photo-1504052434569-70ad5836ab65?q=80&w=1600&auto=format&fit=crop"
         alt="Tafakari"
         class="absolute inset-0 w-full h-full object-cover">

    <div class="absolute inset-0 bg-navy/70"></div>

    <div class="relative z-10 text-center px-4">
        <h1 class="text-3xl md:text-5xl font-black text-white">Tafakari</h1>
        <p class="mt-4 text-white/90 max-w-2xl mx-auto">
            Soma tafakari kulingana na tarehe na uendelee kukua kiroho kila siku.
        </p>
    </div>
</section>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($featured): ?>
<section class="bg-white py-12">
    <div class="max-w-6xl mx-auto px-4">

        <div class="bg-gray-50 rounded-3xl overflow-hidden shadow-sm border border-gray-100 grid md:grid-cols-2 gap-6 items-center">

            
            <div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($featured->image): ?>
                    <img src="<?php echo e(asset('storage/'.$featured->image)); ?>"
                         class="w-full h-72 object-cover">
                <?php else: ?>
                    <div class="w-full h-72 bg-primary/10 flex items-center justify-center">
                        <span class="text-primary font-black">Uzima Milele</span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="p-8">

                <p class="text-primary font-bold mb-2">
                    Tafakari ya leo
                </p>

                <h2 class="text-2xl md:text-3xl font-black text-navy mb-4">
                    <?php echo e($featured->title); ?>

                </h2>

                <p class="text-gray-600 mb-6">
                    <?php echo e(\Illuminate\Support\Str::limit(strip_tags($featured->content), 150)); ?>

                </p>

                <a href="<?php echo e(route('devotions.show', $featured->slug)); ?>"
                   class="inline-flex bg-primary text-white px-6 py-3 rounded-xl font-bold hover:bg-primaryDark transition">
                    Soma tafakari
                </a>

            </div>

        </div>

    </div>
</section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<section class="bg-gray-50 py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($devotions->count()): ?>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $devotions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $devotion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <article class="bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-xl transition">

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($devotion->image): ?>
                            <img src="<?php echo e(asset('storage/'.$devotion->image)); ?>"
                                 alt="<?php echo e($devotion->title); ?>"
                                 class="w-full h-56 object-cover">
                        <?php else: ?>
                            <div class="w-full h-56 bg-primary/10 flex items-center justify-center">
                                <span class="text-primary font-black text-lg">Uzima Milele</span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="p-6">

                            
                            <div class="inline-flex bg-primary/10 text-primary px-3 py-1 rounded-full text-xs font-bold mb-4">
                                <?php echo e($devotion->published_at?->format('d M Y') ?? 'Haijapangiwa'); ?>

                            </div>

                            
                            <h3 class="text-xl font-black text-navy mb-3 leading-snug">
                                <?php echo e($devotion->title); ?>

                            </h3>

                            
                            <p class="text-gray-600 text-sm leading-relaxed mb-5">
                                <?php echo e(\Illuminate\Support\Str::limit(strip_tags($devotion->content), 120)); ?>

                            </p>

                            
                            <a href="<?php echo e(route('devotions.show', $devotion->slug)); ?>"
                               class="inline-flex items-center gap-1 text-primary font-bold hover:text-primaryDark">
                                Soma zaidi →
                            </a>

                        </div>

                    </article>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="mt-14 flex justify-center">
                <?php echo e($devotions->links()); ?>

            </div>

        <?php else: ?>

            <div class="bg-white rounded-3xl p-10 text-center shadow-sm border border-gray-100">
                <h3 class="text-2xl font-black text-navy mb-3">
                    Hakuna tafakari bado
                </h3>
                <p class="text-gray-600">
                    Tafadhali ongeza tafakari kupitia admin panel na uchague tarehe yake.
                </p>
            </div>

        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\devotions\index.blade.php ENDPATH**/ ?>