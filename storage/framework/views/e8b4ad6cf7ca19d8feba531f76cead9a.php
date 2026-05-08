

<?php $__env->startSection('content'); ?>

<?php
    use Illuminate\Support\Str;
?>


<section class="relative min-h-[560px] flex items-center justify-center overflow-hidden bg-[#0083CB]">

    <img src="https://images.unsplash.com/photo-1509099836639-18ba1795216d?q=80&w=1800&auto=format&fit=crop"
         alt="Children learning"
         class="absolute inset-0 w-full h-full object-cover">

    <div class="absolute inset-0 bg-black/35"></div>

    <img src="<?php echo e(asset('images/clouds.png')); ?>" alt=""
         class="absolute top-4 left-2 md:left-16 w-60 md:w-[420px] opacity-60 pointer-events-none">

    <img src="<?php echo e(asset('images/clouds2.png')); ?>" alt=""
         class="absolute top-14 right-0 md:right-20 w-64 md:w-[460px] opacity-50 pointer-events-none">

    <div class="absolute -left-12 bottom-24 w-44 h-44 border-[28px] border-[#69C45F]/60 rounded-full"></div>
    <div class="absolute right-10 bottom-28 w-24 h-24 bg-[#F4B122]/80 rounded-full"></div>

    <div class="relative z-10 max-w-5xl mx-auto px-4 text-center text-white">
        <h1 class="watoto-font text-5xl md:text-8xl leading-tight drop-shadow-xl">
            Uzima Milele Watoto
        </h1>

        <p class="mt-6 max-w-2xl mx-auto text-lg md:text-2xl text-white/95 font-semibold leading-relaxed">
            Jifunze Biblia kupitia video, hadithi na mafundisho rahisi kwa watoto.
        </p>

        <div class="mt-9 flex flex-col sm:flex-row justify-center gap-4">
            <a href="#videos"
               class="inline-flex items-center justify-center bg-[#F4B122] text-[#0E3D4F] font-black px-10 py-4 rounded-full shadow-xl hover:-translate-y-1 hover:shadow-2xl transition">
                Anza Kujifunza
            </a>

            <a href="#featured"
               class="inline-flex items-center justify-center bg-white text-[#0E3D4F] font-black px-10 py-4 rounded-full shadow-xl hover:-translate-y-1 hover:shadow-2xl transition">
                Video ya Wiki
            </a>
        </div>
    </div>

    <div class="absolute bottom-0 left-0 w-full">
        <svg viewBox="0 0 1440 120" class="w-full h-[90px]" preserveAspectRatio="none">
            <path fill="#ffffff" d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64V120H0Z"></path>
        </svg>
    </div>
</section>



<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($featuredVideo): ?>
<section id="featured" class="py-14 bg-gradient-to-b from-[#F7FBFD] to-white relative overflow-hidden">

    <div class="absolute -top-20 -left-20 w-[300px] h-[300px] bg-[#0083CB]/10 rounded-full"></div>
    <div class="absolute bottom-0 right-0 w-[260px] h-[260px] bg-[#F4B122]/20 rounded-full"></div>

    <div class="max-w-7xl mx-auto px-4">

        <div class="text-center max-w-3xl mx-auto mb-10">
            <h2 class="text-3xl md:text-5xl font-black text-[#0E3D4F] leading-tight">
                <?php echo e($featuredVideo->title); ?>

            </h2>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 items-stretch">

            <div class="group relative bg-white rounded-[2rem] overflow-hidden shadow-xl hover:shadow-2xl transition">
                <iframe
                    class="w-full h-[300px] md:h-full"
                    src="<?php echo e($featuredVideo->youtube_embed); ?>"
                    title="<?php echo e($featuredVideo->title); ?>"
                    allowfullscreen>
                </iframe>
            </div>

            <div class="relative bg-white rounded-[2rem] p-8 md:p-10 shadow-xl border border-gray-100 flex flex-col justify-center">

                <div class="absolute left-0 top-0 h-full w-2 bg-[#0083CB] rounded-l-[2rem]"></div>
                <div class="absolute -top-6 -right-6 w-20 h-20 bg-[#F4B122]/30 rounded-full"></div>

                <div class="space-y-6 relative z-10">

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($featuredVideo->main_lesson): ?>
                        <div>
                            <h3 class="font-bold text-lg text-[#0E3D4F]">Funzo Kuu</h3>
                            <p class="text-gray-600 mt-1"><?php echo e($featuredVideo->main_lesson); ?></p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($featuredVideo->bible_verse): ?>
                        <div>
                            <h3 class="font-bold text-lg text-[#0E3D4F]">Mstari wa Biblia</h3>
                            <p class="text-gray-600 mt-1"><?php echo e($featuredVideo->bible_verse); ?></p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($featuredVideo->reflection_question): ?>
                        <div>
                            <h3 class="font-bold text-lg text-[#0E3D4F]">Swali la Kutafakari</h3>
                            <p class="text-gray-600 mt-1"><?php echo e($featuredVideo->reflection_question); ?></p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <a href="<?php echo e(route('children.show', $featuredVideo->slug)); ?>"
                       class="inline-block mt-4 bg-[#0083CB] text-white px-7 py-3 rounded-full font-bold shadow hover:bg-[#076994] hover:-translate-y-1 transition">
                        Fungua Somo
                    </a>

                </div>
            </div>

        </div>
    </div>
</section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>



<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($categories->count()): ?>
<section class="py-14 bg-[#F7FBFD] relative overflow-hidden">
    <img src="<?php echo e(asset('images/clouds.png')); ?>" alt=""
         class="absolute -top-24 left-10 w-[360px] opacity-15 pointer-events-none">

    <div class="absolute -bottom-10 -right-10 w-44 h-44 bg-[#F4B122]/20 rounded-full"></div>

    <div class="relative max-w-6xl mx-auto px-4 text-center">
        <h2 class="watoto-font text-4xl md:text-5xl text-[#0E3D4F] mb-7">
            Chagua Kundi
        </h2>

        <div class="flex flex-wrap justify-center gap-4">
            <a href="#videos" class="px-6 py-3 rounded-full bg-[#0083CB] text-white font-black shadow">
                Zote
            </a>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="#<?php echo e(Str::slug($category)); ?>"
                   class="px-6 py-3 rounded-full bg-white border border-gray-100 text-[#0E3D4F] font-black shadow-sm hover:bg-[#F4B122] transition">
                    <?php echo e($category); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>



<section id="videos" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">

        <div class="text-center mb-14">
            <span class="inline-block bg-[#0083CB]/10 text-[#0083CB] px-6 py-2 rounded-full font-black">
                Maktaba ya Video
            </span>

            <h2 class="watoto-font mt-5 text-4xl md:text-6xl text-[#0E3D4F]">
                Video za Watoto
            </h2>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($videos->count()): ?>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article id="<?php echo e($video->category ? Str::slug($video->category) : 'video-'.$video->id); ?>"
                             class="group bg-white rounded-[2rem] shadow-lg border border-gray-100 overflow-hidden hover:-translate-y-2 hover:shadow-2xl transition duration-300">

                        <a href="<?php echo e(route('children.show', $video->slug)); ?>" class="relative block overflow-hidden">
                            <img src="<?php echo e($video->youtube_thumbnail); ?>"
                                 alt="<?php echo e($video->title); ?>"
                                 class="w-full h-56 object-cover group-hover:scale-105 transition duration-500">

                            <div class="absolute inset-0 bg-gradient-to-t from-[#0E3D4F]/70 via-[#0E3D4F]/20 to-transparent"></div>

                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="w-16 h-16 rounded-full bg-[#F4B122] text-[#0E3D4F] flex items-center justify-center shadow-xl group-hover:scale-110 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </span>
                            </div>
                        </a>

                        <div class="p-6">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($video->category): ?>
                                <span class="inline-block text-xs font-black bg-[#0083CB]/10 text-[#0083CB] px-4 py-1.5 rounded-full mb-4">
                                    <?php echo e($video->category); ?>

                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <h3 class="text-xl font-black text-[#0E3D4F] mb-4 leading-snug">
                                <a href="<?php echo e(route('children.show', $video->slug)); ?>" class="hover:text-[#0083CB] transition">
                                    <?php echo e($video->title); ?>

                                </a>
                            </h3>

                            <a href="<?php echo e(route('children.show', $video->slug)); ?>"
                               class="inline-flex items-center text-[#0083CB] font-black hover:text-[#076994] transition">
                                Tazama Somo <span class="ml-2">→</span>
                            </a>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="text-center bg-[#F7FBFD] rounded-[2rem] p-12 border border-[#0083CB]/10 shadow-sm">
                <h3 class="watoto-font text-4xl text-[#0E3D4F]">
                    Hakuna video bado.
                </h3>

                <p class="text-gray-600 mt-3">
                    Ongeza video kupitia admin dashboard.
                </p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\watoto\index.blade.php ENDPATH**/ ?>