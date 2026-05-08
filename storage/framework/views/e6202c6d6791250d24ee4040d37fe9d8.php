

<?php $__env->startSection('title', 'Uzima Milele'); ?>

<?php $__env->startSection('content'); ?>


<section
    x-data="{
        active: 0,
        slides: [
            {
                image: 'https://images.unsplash.com/photo-1504052434569-70ad5836ab65?q=80&w=1600',
                title: 'Jifunze Neno la Mungu Kila Siku',
                subtitle: 'Soma tafakari, jifunze Biblia na ukue kiroho kupitia Uzima Milele.',
                btn1: 'Soma Tafakari',
                link1: '<?php echo e(route('devotions.index')); ?>',
                btn2: 'Anza Masomo',
                link2: '<?php echo e(route('lessons.index')); ?>'
            },
            {
                image: 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?q=80&w=1600',
                title: 'Kwa Masomo ya Biblia',
                subtitle: 'Pata mafundisho ya Biblia yatakayokusaidia kuelewa ukweli wa Neno la Mungu.',
                btn1: 'Masomo ya Biblia',
                link1: '<?php echo e(route('lessons.index')); ?>',
                btn2: 'Wasiliana Nasi',
                link2: '<?php echo e(route('contact')); ?>'
            },
            {
                image: 'https://images.unsplash.com/photo-1490730141103-6cac27aaab94?q=80&w=1600',
                title: 'Kwa Maombi na Ushuhuda',
                subtitle: 'Tuma ombi lako la maombi kwa Uzima Milele. Tupo hapa kukuombea kwa upendo na uaminifu.',
                btn1: 'Tuma Ombi',
                link1: '<?php echo e(route('prayers.testimonies')); ?>',
                btn2: 'Soma Tafakari',
                link2: '<?php echo e(route('devotions.index')); ?>'
            }
        ]
    }"
    x-init="setInterval(() => active = (active + 1) % slides.length, 6000)"
    class="relative h-[550px] md:h-[620px] overflow-hidden bg-navy"
>

    
    <template x-for="(slide, index) in slides" :key="index">
        <div
            x-show="active === index"
            x-transition.opacity.duration.1000ms
            class="absolute inset-0 bg-cover bg-center"
            :style="`background-image: url('${slide.image}')`">
        </div>
    </template>

    
    <div class="absolute inset-0 bg-gradient-to-r from-navy/90 via-navy/75 to-primary/40"></div>

    
    <div class="relative z-10 max-w-7xl mx-auto px-4 md:px-6 h-full flex items-center text-white">
        <div class="max-w-4xl">

            <p class="text-accent font-black mb-4 uppercase tracking-wide">
                Karibu kwenye Uzima Milele
            </p>

            <h1 class="text-4xl md:text-6xl lg:text-7xl font-black leading-tight mb-6"
                x-text="slides[active].title"></h1>

            <p class="text-lg md:text-2xl text-white/90 mb-10 leading-relaxed max-w-3xl"
               x-text="slides[active].subtitle"></p>

            <div class="flex gap-4 flex-wrap">
                <a :href="slides[active].link1"
                   class="bg-accent text-navy px-7 py-4 rounded-xl font-black hover:bg-yellow-400 transition">
                    <span x-text="slides[active].btn1"></span>
                </a>

                <a :href="slides[active].link2"
                   class="bg-white text-navy px-7 py-4 rounded-xl font-black hover:bg-gray-100 transition">
                    <span x-text="slides[active].btn2"></span>
                </a>
            </div>

        </div>
    </div>

    
    <div class="absolute bottom-7 left-1/2 -translate-x-1/2 flex gap-3 z-20">
        <template x-for="(slide, index) in slides" :key="index">
            <button
                type="button"
                @click="active = index"
                :class="active === index ? 'bg-white w-8' : 'bg-white/50 w-3'"
                class="h-3 rounded-full transition-all duration-300">
            </button>
        </template>
    </div>

</section>


<section class="bg-white py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <p class="text-primary font-black mb-3">
                    Kuhusu Uzima Milele
                </p>

                <h2 class="text-3xl md:text-5xl font-black text-navy leading-tight mb-6">
                    Huduma ya Kidijitali kwa Biblia, Afya na Jamii
                </h2>

                <p class="text-gray-600 leading-8 mb-5">
                    Uzima Milele ni huduma ya Kikristo inayotumia mifumo ya kidijitali
                    kutoa mafundisho ya Biblia, afya na maisha ya kiroho kwa lugha ya Kiswahili.
                </p>

                <p class="text-gray-600 leading-8 mb-8">
                    Lengo letu ni kuwafikia watu wengi zaidi kwa ujumbe wa tumaini,
                    wokovu na ukuaji wa kiroho kupitia nyenzo rahisi na zinazopatikana mtandaoni.
                </p>

                <a href="<?php echo e(route('about')); ?>"
                   class="inline-flex px-7 py-3 rounded-xl bg-primary text-white font-black hover:bg-primaryDark transition">
                    Fahamu Zaidi
                </a>
            </div>

            <div class="rounded-[2rem] overflow-hidden shadow-xl bg-gray-100">
                <div class="aspect-video">
                    <iframe class="w-full h-full"
                            src="https://www.youtube.com/embed/1FkbFOqyPLw"
                            title="Uzima Milele Video"
                            frameborder="0"
                            allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>



<section class="bg-gray-50 py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10">
            <div>
                <p class="text-primary font-black mb-3">
                    Tafakari
                </p>

                <h2 class="text-3xl md:text-4xl font-black text-navy">
                    Tafakari za Karibuni
                </h2>

                <p class="mt-3 text-gray-600 max-w-2xl leading-7">
                    Soma ujumbe wa kiroho unaokusaidia kukua katika imani kila siku.
                </p>
            </div>

            <a href="<?php echo e(route('devotions.index')); ?>"
               class="inline-flex px-6 py-3 rounded-xl bg-primary text-white font-black hover:bg-primaryDark transition w-fit">
                Tafakari Zote
            </a>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($latestDevotions) && $latestDevotions->count()): ?>
            <div class="grid md:grid-cols-3 gap-7">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $latestDevotions->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $devotion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('devotions.show', $devotion->slug)); ?>"
                       class="group bg-white rounded-[2rem] overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition">
                        <div class="h-56 overflow-hidden bg-gray-100">
                            <img src="<?php echo e($devotion->image ? asset('storage/' . $devotion->image) : asset('images/devotion-default.jpg')); ?>"
                                 alt="<?php echo e($devotion->title); ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                        </div>

                        <div class="p-6">
                            <p class="text-sm text-primary font-black mb-3">
                                <?php echo e($devotion->published_at ? \Carbon\Carbon::parse($devotion->published_at)->format('d M Y') : 'Tafakari'); ?>

                            </p>

                            <h3 class="text-xl font-black text-navy leading-snug mb-3">
                                <?php echo e($devotion->title); ?>

                            </h3>

                            <p class="text-gray-600 leading-7 text-sm">
                                <?php echo e(\Illuminate\Support\Str::limit(strip_tags($devotion->content), 120)); ?>

                            </p>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-3xl border border-gray-100 p-10 text-center">
                <h3 class="text-2xl font-black text-navy">
                    Hakuna tafakari bado.
                </h3>
                <p class="mt-3 text-gray-600">
                    Tafakari mpya zitaonekana hapa baada ya kuchapishwa.
                </p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</section>



<section class="bg-white py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="rounded-[2rem] overflow-hidden shadow-xl bg-gray-100">
                <img src="<?php echo e(asset('images/biblestudy.jpg')); ?>"
                     alt="Masomo ya Biblia"
                     class="w-full h-[380px] object-cover">
            </div>

            <div>
                <p class="text-primary font-black mb-3">
                    Masomo ya Biblia
                </p>

                <h2 class="text-3xl md:text-5xl font-black text-navy leading-tight mb-6">
                    Jifunze Biblia Hatua kwa Hatua
                </h2>

                <p class="text-gray-600 leading-8 mb-8">
                    Fuata masomo yaliyopangwa kwa urahisi, jifunze mada muhimu za Biblia,
                    fuatilia maendeleo yako na ukue katika maarifa ya Neno la Mungu.
                </p>

                <a href="<?php echo e(route('lessons.index')); ?>"
                   class="inline-flex px-7 py-3 rounded-xl bg-primary text-white font-black hover:bg-primaryDark transition">
                    Anza Kujifunza
                </a>
            </div>
        </div>
    </div>
</section>



<section class="bg-navy py-16 md:py-24 text-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <p class="text-accent font-black mb-3">
                    Uzima Milele Watoto
                </p>

                <h2 class="text-3xl md:text-5xl font-black leading-tight mb-6">
                    Watoto Wajifunze Biblia kwa Njia Rahisi
                </h2>

                <p class="text-white/80 leading-8 mb-8">
                    Sehemu maalum kwa watoto kujifunza kuhusu Yesu, Biblia, maadili
                    na ukuaji wa kiroho kupitia video na masomo rahisi.
                </p>

                <a href="<?php echo e(route('children.index')); ?>"
                   class="inline-flex px-7 py-3 rounded-xl bg-accent text-navy font-black hover:bg-yellow-400 transition">
                    Twende Tukasome
                </a>
            </div>

            <div class="bg-white/10 rounded-[2rem] p-4 border border-white/10">
                <img src="<?php echo e(asset('images/childbiblestudy.jpg')); ?>"
                     alt="Uzima Milele Watoto"
                     class="w-full h-[360px] object-cover rounded-[1.5rem]">
            </div>
        </div>
    </div>
</section>


<section class="bg-white py-16 md:py-24">
    <div class="max-w-5xl mx-auto px-4 text-center">
        <p class="text-primary font-black mb-3">
            Maombi na Ushuhuda
        </p>

        <h2 class="text-3xl md:text-5xl font-black text-navy leading-tight mb-6">
            Unahitaji Maombi?
        </h2>

        <p class="text-gray-600 leading-8 max-w-3xl mx-auto mb-8">
            Tuma ombi lako la maombi kwa timu ya Uzima Milele. Tunaamini Mungu bado
            anasikia na kujibu maombi.
        </p>

        <a href="<?php echo e(route('prayers.testimonies')); ?>"
           class="inline-flex px-8 py-4 rounded-xl bg-primary text-white font-black hover:bg-primaryDark transition">
            Tuma Ombi la Maombi
        </a>
    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\home.blade.php ENDPATH**/ ?>