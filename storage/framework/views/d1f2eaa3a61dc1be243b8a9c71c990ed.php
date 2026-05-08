

<?php $__env->startSection('content'); ?>


<section class="relative h-[260px] md:h-[360px] flex items-center justify-center overflow-hidden bg-navy">
    <img src="https://images.unsplash.com/photo-1504052434569-70ad5836ab65?q=80&w=1600&auto=format&fit=crop"
         alt="Kuhusu Sisi"
         class="absolute inset-0 w-full h-full object-cover">

    <div class="absolute inset-0 bg-navy/70"></div>

    <div class="relative z-10 text-center px-4">
        <h1 class="text-3xl md:text-5xl font-black text-white">Kuhusu Sisi</h1>
        <p class="mt-4 text-white/90 max-w-2xl mx-auto text-sm md:text-base">
            Fahamu zaidi kuhusu huduma ya Uzima Milele na kazi tunayofanya.
        </p>
    </div>
</section>


<section class="bg-white py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-2 gap-12 items-center">

        <div>
            <p class="text-primary font-semibold tracking-wide mb-2">Kuhusu</p>

            <h2 class="text-4xl md:text-6xl font-black text-navy leading-tight mb-6">
                Uzima Milele
            </h2>

            <p class="text-gray-700 leading-relaxed mb-6 text-lg">
                Uzima Milele ni huduma ya Kikristo isiyo ya faida ambayo hutoa elimu ya Biblia, afya na jamii kupitia mifumo ya kidijitali kwa lugha ya Kiswahili.
            </p>

            <p class="text-gray-700 leading-relaxed mb-8 text-lg">
                Tunalenga kuwafikia watu wanaozungumza Kiswahili kwa kuwapatia mafundisho ya Biblia, tafakari, vitabu, video na nyenzo za kuwasaidia kukua kiroho.
            </p>

            <div class="flex flex-wrap gap-4">
                <a href="/contact"
                   class="bg-primary text-white px-7 py-3 rounded-xl font-bold hover:bg-primaryDark transition">
                    Wasiliana nasi
                </a>

                <a href="/lessons"
                   class="border border-primary text-primary px-7 py-3 rounded-xl font-bold hover:bg-primary hover:text-white transition">
                    Jifunze Biblia
                </a>
            </div>
        </div>

        <div class="relative transition duration-300 hover:scale-[1.02]">
            <div class="rounded-3xl overflow-hidden shadow-2xl bg-gray-100 aspect-video">
                <iframe
                    class="w-full h-full"
                    src="https://www.youtube.com/embed/1FkbFOqyPLw?si=rXl3Gb9n0dbnbFk4"
                    title="Uzima Milele Video"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen>
                </iframe>
            </div>

            <div class="absolute -bottom-6 left-6 md:left-10 bg-white shadow-xl rounded-2xl px-6 py-4 hidden md:block">
                <p class="text-3xl font-black text-primary">100M+</p>
                <p class="text-sm text-gray-600">Wazungumzaji wa Kiswahili</p>
            </div>
        </div>

    </div>
</section>


<section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-8">

        <div class="bg-white rounded-3xl shadow-sm p-8 border border-gray-100 hover:shadow-xl transition">
            <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mb-6">
                <span class="text-2xl text-primary">▲</span>
            </div>

            <h3 class="text-2xl font-black text-navy mb-4">Dira</h3>

            <p class="text-gray-700 leading-relaxed">
                Kuona jamii inayomjua Mungu, inayokua kiroho na kuishi maisha yenye tumaini kupitia Neno la Mungu.
            </p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm p-8 border border-gray-100 hover:shadow-xl transition">
            <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mb-6">
                <span class="text-2xl text-primary">●</span>
            </div>

            <h3 class="text-2xl font-black text-navy mb-4">Dhima</h3>

            <p class="text-gray-700 leading-relaxed">
                Kufikisha injili ya uzima wa milele kupitia mafundisho ya Biblia, tafakari, video, vitabu na maudhui ya kiroho kwa lugha ya Kiswahili.
            </p>
        </div>

    </div>
</section>


<section class="bg-white py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4">

        <div class="text-center max-w-3xl mx-auto mb-12">
            <p class="text-primary font-bold mb-2">Tunachofanya</p>

            <h2 class="text-3xl md:text-4xl font-black text-navy mb-4">
                Huduma zetu kuu
            </h2>

            <p class="text-gray-600">
                Tunatumia teknolojia kufikisha ujumbe wa matumaini, wokovu na ukuaji wa kiroho.
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                ['title' => 'Tafakari', 'text' => 'Tafakari za kila siku kwa ajili ya kuimarisha maisha ya kiroho.'],
                ['title' => 'Masomo ya Biblia', 'text' => 'Mafundisho yanayosaidia kuelewa Biblia kwa urahisi na kwa mpangilio.'],
                ['title' => 'Vitabu', 'text' => 'Vitabu na nyenzo za kujifunza zinazopatikana kwa mfumo wa kidijitali.'],
                ['title' => 'Video', 'text' => 'Mafundisho ya video kuhusu Biblia, afya, familia na maisha ya jamii.'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-gray-50 rounded-3xl p-6 hover:shadow-lg transition border border-gray-100">
                    <h3 class="font-black text-navy mb-3"><?php echo e($item['title']); ?></h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        <?php echo e($item['text']); ?>

                    </p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

    </div>
</section>


<section class="bg-gray-50 py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4">

        <div class="text-center max-w-3xl mx-auto mb-12">
            <p class="text-primary font-bold mb-2">Maadili yetu</p>

            <h2 class="text-3xl md:text-4xl font-black text-navy mb-4">
                Tunachoamini na kusimamia
            </h2>

            <p class="text-gray-600">
                Huduma yetu imejengwa juu ya misingi ya Neno la Mungu, uaminifu na upendo kwa jamii.
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

            <div class="bg-white rounded-3xl p-7 border border-gray-100 shadow-sm hover:shadow-xl transition">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary font-black mb-5">
                    01
                </div>
                <h3 class="text-xl font-black text-navy mb-3">Neno la Mungu</h3>
                <p class="text-gray-600 leading-relaxed text-sm">
                    Tunaliweka Neno la Mungu kuwa msingi wa mafundisho, tafakari na huduma zetu zote.
                </p>
            </div>

            <div class="bg-white rounded-3xl p-7 border border-gray-100 shadow-sm hover:shadow-xl transition">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary font-black mb-5">
                    02
                </div>
                <h3 class="text-xl font-black text-navy mb-3">Upendo na huduma</h3>
                <p class="text-gray-600 leading-relaxed text-sm">
                    Tunahudumia watu kwa upendo, heshima na moyo wa kuwasaidia kukua kiroho.
                </p>
            </div>

            <div class="bg-white rounded-3xl p-7 border border-gray-100 shadow-sm hover:shadow-xl transition">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary font-black mb-5">
                    03
                </div>
                <h3 class="text-xl font-black text-navy mb-3">Uaminifu</h3>
                <p class="text-gray-600 leading-relaxed text-sm">
                    Tunathamini ukweli, uwazi na uaminifu katika kufikisha ujumbe wa injili.
                </p>
            </div>

        </div>

    </div>
</section>


<section class="bg-navy py-16">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-black text-white mb-4">
            Jiunge nasi katika safari ya kiroho
        </h2>

        <p class="text-white/80 mb-8">
            Soma tafakari, jifunze Biblia na shiriki ujumbe wa tumaini kwa wengine.
        </p>

        <div class="flex flex-wrap justify-center gap-4">
            <a href="/devotions"
               class="bg-primary text-white px-6 py-3 rounded-xl font-bold hover:bg-primaryDark transition">
                Soma tafakari
            </a>

            <a href="/contact"
               class="bg-white text-navy px-6 py-3 rounded-xl font-bold hover:bg-gray-100 transition">
                Wasiliana nasi
            </a>
        </div>
    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\about.blade.php ENDPATH**/ ?>