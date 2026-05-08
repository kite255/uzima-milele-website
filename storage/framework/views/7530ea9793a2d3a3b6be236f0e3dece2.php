

<?php $__env->startSection('title', 'Changia | Uzima Milele'); ?>

<?php $__env->startSection('content'); ?>


<section class="relative overflow-hidden bg-navy">
    <div class="absolute inset-0">
<img src="<?php echo e(asset('images/donation.jpg')); ?>"
     alt="Bible and prayer"
     class="h-full w-full object-cover">
             alt="Bible and prayer"
             class="h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-navy via-navy/90 to-primary/70"></div>
    </div>

    <div class="absolute -top-24 -left-24 h-80 w-80 rounded-full bg-primary/30 blur-3xl"></div>
    <div class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-accent/20 blur-3xl"></div>

    <div class="relative max-w-7xl mx-auto px-4 py-24 md:py-32">
        <div class="max-w-3xl">
            <span class="inline-flex rounded-full bg-accent px-5 py-2 text-sm font-black text-navy shadow">
                Sadaka na Michango
            </span>

            <h1 class="mt-6 text-4xl md:text-6xl font-black leading-tight text-white">
                Changia Huduma ya Uzima Milele
            </h1>

            <p class="mt-6 max-w-2xl text-base md:text-lg leading-relaxed text-white/85">
                Mchango wako unasaidia kuendeleza elimu ya Biblia, afya na jamii kupitia mifumo ya kidijitali kwa lugha ya Kiswahili.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                <a href="#donation-details"
                   class="inline-flex items-center justify-center rounded-full bg-accent px-8 py-4 text-sm font-black text-navy shadow-xl transition hover:bg-yellow-400">
                    Tazama Namna ya Kuchangia
                </a>

                <a href="https://wa.me/255769778834"
                   target="_blank"
                   class="inline-flex items-center justify-center rounded-full border border-white/30 bg-white/10 px-8 py-4 text-sm font-black text-white transition hover:bg-white/20">
                    Tuma Uthibitisho
                </a>
            </div>
        </div>
    </div>
</section>



<section id="donation-details" class="bg-gray-50 py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4">

        <div class="max-w-3xl mx-auto text-center mb-12">
            <span class="text-sm font-black uppercase tracking-wide text-primary">
                Changia kwa urahisi
            </span>

            <h2 class="mt-3 text-3xl md:text-4xl font-black text-navy">
                Taarifa za Akaunti ya Benki
            </h2>

            <p class="mt-4 text-gray-600 leading-relaxed">
                Unaweza kuchangia kupitia CRDB Bank kwa kutumia akaunti ya TZS au USD.
                Baada ya kuchangia, tuma uthibitisho kupitia WhatsApp.
            </p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8 items-start">

            
            <div class="lg:col-span-2 rounded-3xl bg-white p-6 md:p-8 shadow-xl border border-gray-100">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5 border-b border-gray-100 pb-6 mb-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-2xl bg-gray-50 p-3 border border-gray-100">
                            <img src="<?php echo e(asset('images/crdb.png')); ?>"
                                 alt="CRDB Bank"
                                 class="h-12 w-24 object-contain">
                        </div>

                        <div>
                            <p class="text-sm font-bold text-gray-500">Bank Transfer</p>
                            <h3 class="text-2xl font-black text-navy">CRDB Bank</h3>
                        </div>
                    </div>

                    <span class="rounded-full bg-primary/10 px-4 py-2 text-sm font-black text-primary">
                        Mikocheni Branch
                    </span>
                </div>

                <div class="rounded-3xl bg-gray-50 p-5 md:p-6 border border-gray-100 mb-5">
                    <p class="mb-1 text-sm font-bold text-gray-500">Account Name</p>
                    <p class="text-xl md:text-2xl font-black text-navy">
                        UZIMA MILELE SOCIETY
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-5">

                    
                    <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                        <div class="mb-4">
                            <p class="text-sm font-bold text-gray-500">TZS Account</p>
                            <p class="mt-1 text-2xl font-black tracking-wide text-navy">
                                10121966897
                            </p>
                        </div>

                        <button type="button"
                                onclick="copyText('10121966897')"
                                class="w-full rounded-full bg-primary px-5 py-3 text-sm font-black text-white transition hover:bg-primaryDark">
                            Copy TZS Account
                        </button>
                    </div>

                    
                    <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                        <div class="mb-4">
                            <p class="text-sm font-bold text-gray-500">USD Account</p>
                            <p class="mt-1 text-2xl font-black tracking-wide text-navy">
                                10121966986
                            </p>
                        </div>

                        <button type="button"
                                onclick="copyText('10121966986')"
                                class="w-full rounded-full bg-primary px-5 py-3 text-sm font-black text-white transition hover:bg-primaryDark">
                            Copy USD Account
                        </button>
                    </div>

                </div>

                <div class="mt-6 rounded-2xl bg-accent/10 p-5 border border-accent/20">
                    <p class="text-sm leading-relaxed text-gray-700">
                        <span class="font-black text-navy">Muhimu:</span>
                        Hakikisha jina la akaunti ni <strong>UZIMA MILELE SOCIETY</strong> kabla ya kuthibitisha malipo.
                    </p>
                </div>
            </div>


            
            <div class="rounded-3xl bg-gradient-to-br from-primary to-primaryDark p-6 md:p-8 text-white shadow-xl relative overflow-hidden">

                <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-accent/20"></div>

                <div class="relative">
                    <span class="inline-flex rounded-full bg-white/15 px-4 py-2 text-sm font-black">
                        Uthibitisho
                    </span>

                    <h3 class="mt-6 text-2xl font-black">
                        Tuma uthibitisho wa mchango
                    </h3>

                    <p class="mt-3 text-sm leading-relaxed text-white/85">
                        Baada ya kuchangia, tuma picha au ujumbe wa uthibitisho kupitia WhatsApp ili tuweze kurekodi mchango wako.
                    </p>

                    <div class="mt-6 rounded-3xl border border-white/20 bg-white/15 p-5">
                        <p class="text-sm text-white/70">Simu / WhatsApp</p>

                        <a href="tel:+255769778834"
                           class="mt-1 block text-2xl font-black">
                            +255 769 778 834
                        </a>

                        <a href="https://wa.me/255769778834"
                           target="_blank"
                           class="mt-6 inline-flex w-full items-center justify-center rounded-full bg-accent px-6 py-3 text-sm font-black text-navy shadow-lg transition hover:bg-yellow-400">
                            Tuma WhatsApp
                        </a>
                    </div>

                    <div class="mt-6 rounded-2xl bg-white/10 p-4 text-sm leading-relaxed text-white/80">
                        Asante kwa kuunga mkono kazi ya Mungu kupitia Uzima Milele.
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>



<script>
    function copyText(text) {
        navigator.clipboard.writeText(text).then(function () {
            alert("Namba ime-copy: " + text);
        }).catch(function () {
            alert("Imeshindikana ku-copy. Tafadhali copy manually: " + text);
        });
    }
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\donation.blade.php ENDPATH**/ ?>