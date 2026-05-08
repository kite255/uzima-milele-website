

<?php $__env->startSection('title', 'Thibitisha Cheti'); ?>

<?php $__env->startSection('content'); ?>

<section class="bg-slate-50 min-h-screen py-16">
    <div class="max-w-5xl mx-auto px-4">

        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white shadow-sm border border-slate-100 mb-4">
                <img src="<?php echo e(asset('logo.png')); ?>" alt="Uzima Milele" class="w-11 h-11 object-contain">
            </div>

            <p class="text-primary font-black uppercase text-sm tracking-[0.25em]">
                Uzima Milele Ministry
            </p>

            <h1 class="mt-3 text-3xl md:text-5xl font-black text-navy">
                Uthibitisho wa Cheti
            </h1>

            <p class="mt-4 text-gray-600 max-w-2xl mx-auto">
                Hakiki uhalali wa cheti kilichotolewa na Uzima Milele Ministry kupitia mfumo rasmi wa kujifunza Biblia.
            </p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($certificate): ?>
            <div class="bg-white rounded-[2rem] shadow-xl border border-slate-100 overflow-hidden">

                <div class="relative overflow-hidden bg-gradient-to-r from-green-700 to-green-500 p-8 text-white">
                    <div class="absolute -right-12 -top-12 w-40 h-40 rounded-full bg-white/10"></div>
                    <div class="absolute -left-16 -bottom-16 w-48 h-48 rounded-full bg-white/10"></div>

                    <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div>
                            <div class="inline-flex items-center rounded-full bg-white/15 px-4 py-2 text-sm font-bold">
                                Verified Certificate
                            </div>

                            <h2 class="mt-4 text-3xl md:text-4xl font-black">
                                Cheti ni Halali
                            </h2>

                            <p class="mt-2 text-white/90">
                                Taarifa za cheti zimepatikana kwenye mfumo rasmi wa Uzima Milele.
                            </p>
                        </div>

                        <div class="bg-white text-green-700 rounded-2xl px-6 py-4 text-center shadow-sm">
                            <div class="text-3xl font-black">✓</div>
                            <div class="text-sm font-black uppercase tracking-widest">Valid</div>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    <div class="grid md:grid-cols-2 gap-5">

                        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-5">
                            <p class="text-sm text-gray-500">Jina la Mwanafunzi</p>
                            <p class="mt-2 text-xl font-black text-navy">
                                <?php echo e($certificate->user->name ?? 'Haijulikani'); ?>

                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-5">
                            <p class="text-sm text-gray-500">Somo Alilokamilisha</p>
                            <p class="mt-2 text-xl font-black text-navy">
                                <?php echo e($certificate->lesson->title ?? 'Haijulikani'); ?>

                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-5">
                            <p class="text-sm text-gray-500">Namba ya Cheti</p>
                            <p class="mt-2 text-xl font-black text-primary">
                                <?php echo e($certificate->certificate_number); ?>

                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-5">
                            <p class="text-sm text-gray-500">Tarehe ya Kutolewa</p>
                            <p class="mt-2 text-xl font-black text-navy">
                                <?php echo e(\Carbon\Carbon::parse($certificate->issued_at ?? $certificate->created_at)->format('d M Y')); ?>

                            </p>
                        </div>

                    </div>

                    <div class="mt-6 rounded-2xl bg-green-50 border border-green-200 p-5">
                        <p class="text-green-700 font-bold">
                            Cheti hiki kimethibitishwa kuwa halali kupitia mfumo wa Uzima Milele Ministry.
                        </p>
                    </div>

                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <a href="<?php echo e(route('home')); ?>"
                           class="inline-flex justify-center rounded-xl bg-primary px-6 py-3 text-white font-bold hover:bg-primaryDark transition">
                            Rudi Mwanzo
                        </a>

                        <a href="<?php echo e(route('lessons.index')); ?>"
                           class="inline-flex justify-center rounded-xl bg-accent px-6 py-3 text-navy font-bold hover:bg-yellow-400 transition">
                            Tazama Masomo
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-[2rem] shadow-xl border border-slate-100 overflow-hidden">

                <div class="relative overflow-hidden bg-gradient-to-r from-red-700 to-red-500 p-8 text-white">
                    <div class="absolute -right-12 -top-12 w-40 h-40 rounded-full bg-white/10"></div>
                    <div class="absolute -left-16 -bottom-16 w-48 h-48 rounded-full bg-white/10"></div>

                    <h2 class="text-3xl md:text-4xl font-black">
                        Cheti Hakijapatikana
                    </h2>

                    <p class="mt-2 text-white/90">
                        Hakuna cheti chenye namba hii kwenye mfumo wa Uzima Milele.
                    </p>
                </div>

                <div class="p-6 md:p-8">
                    <div class="rounded-2xl bg-red-50 border border-red-200 p-5">
                        <p class="text-red-700 font-bold">
                            Namba ya cheti uliyotafuta:
                            <span class="font-black"><?php echo e($certificateNumber); ?></span>
                        </p>
                    </div>

                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <a href="<?php echo e(route('home')); ?>"
                           class="inline-flex justify-center rounded-xl bg-primary px-6 py-3 text-white font-bold hover:bg-primaryDark transition">
                            Rudi Mwanzo
                        </a>

                        <a href="<?php echo e(route('lessons.index')); ?>"
                           class="inline-flex justify-center rounded-xl bg-gray-100 px-6 py-3 text-navy font-bold hover:bg-gray-200 transition">
                            Tazama Masomo
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/uzimamil/public_html/new.uzimamilele.or.tz/new/resources/views/certificates/verify.blade.php ENDPATH**/ ?>