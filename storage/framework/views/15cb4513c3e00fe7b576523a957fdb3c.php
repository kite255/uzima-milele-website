

<?php $__env->startSection('title', 'Cheti - ' . $certificate->certificate_number); ?>

<?php $__env->startSection('content'); ?>

<?php
    $verifyUrl = route('certificates.verify', $certificate->certificate_number);
    $downloadUrl = route('certificates.download', $certificate->certificate_number);

    $logo = asset('logo.png');

    $signatureExists = file_exists(public_path('signature.png'));
    $signature = asset('signature.png');

    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($verifyUrl);
?>

<section class="certificate-wrapper bg-slate-100 py-10 min-h-screen">
    <div class="max-w-6xl mx-auto px-4">

        
        <div class="certificate-actions mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-navy">
                    Cheti cha Kukamilisha Somo
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    Certificate No:
                    <span class="font-bold text-navy">
                        <?php echo e($certificate->certificate_number); ?>

                    </span>
                </p>
            </div>

            <div class="flex flex-col sm:flex-row flex-wrap gap-3">
                <a href="<?php echo e($downloadUrl); ?>"
                   class="inline-flex items-center justify-center rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white hover:bg-primaryDark transition">
                    Download PDF
                </a>

                <a href="<?php echo e($verifyUrl); ?>"
                   target="_blank"
                   class="inline-flex items-center justify-center rounded-xl bg-accent px-5 py-3 text-sm font-bold text-navy hover:bg-yellow-400 transition">
                    Verify Certificate
                </a>

                <button type="button"
                        onclick="window.print()"
                        class="inline-flex items-center justify-center rounded-xl bg-navy px-5 py-3 text-sm font-bold text-white hover:bg-primaryDark transition">
                    Print
                </button>
            </div>
        </div>

        
        <div class="certificate-page bg-white shadow-2xl border border-slate-200 overflow-hidden mx-auto">

            
            <div class="certificate-header relative overflow-hidden text-center text-white">

                <div class="absolute rounded-full bg-white/10"
                     style="width: 260px; height: 260px; left: -80px; top: -95px;"></div>

                <div class="absolute rounded-full bg-white/10"
                     style="width: 280px; height: 280px; right: -90px; bottom: -135px;"></div>

                <div class="relative z-10 pt-7">
                    <div class="w-14 h-14 rounded-full bg-white mx-auto p-2 flex items-center justify-center">
                        <img src="<?php echo e($logo); ?>"
                             alt="Uzima Milele"
                             class="w-full h-full object-contain">
                    </div>

                    <div class="mt-3 text-[10px] tracking-[0.35em] font-black uppercase">
                        Uzima Milele Ministry
                    </div>

                    <div class="mt-1 text-[9px] tracking-[0.25em]">
                        www.uzimamilele.or.tz
                    </div>

                    <div class="mt-7 text-5xl md:text-6xl font-black tracking-[0.22em]">
                        CERTIFICATE
                    </div>

                    <div class="mt-4 text-lg tracking-[0.5em]">
                        OF COMPLETION
                    </div>
                </div>
            </div>

            
            <div class="certificate-body relative text-center px-16 pt-7 pb-8">

                <p class="text-sm text-gray-500">
                    This certificate is proudly presented to
                </p>

                <h2 class="font-serif italic font-black text-navy mt-3 leading-none certificate-name">
                    <?php echo e($certificate->user->name); ?>

                </h2>

                <div class="w-28 h-1 bg-accent rounded-full mx-auto mt-5 mb-6"></div>

                <p class="text-gray-600 text-base leading-relaxed">
                    For successfully completing the Bible lesson program offered by
                    <strong class="text-navy">Uzima Milele Ministry</strong>.
                </p>

                <h3 class="text-primary font-black text-2xl md:text-3xl mt-5 leading-tight">
                    <?php echo e($certificate->lesson->title); ?>

                </h3>

                
                <div class="certificate-middle grid grid-cols-3 items-end gap-8 mt-9">

                    
                    <div class="text-center">
                        <div class="h-16 flex items-end justify-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($signatureExists): ?>
                                <img src="<?php echo e($signature); ?>"
                                     alt="Signature"
                                     class="max-h-14 object-contain">
                            <?php else: ?>
                                <div class="font-serif italic text-3xl text-navy">
                                    Phillip Bisanda
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="border-t border-slate-400 mt-2 pt-3 mx-auto max-w-xs">
                            <div class="text-sm font-black text-navy uppercase">
                                Phillip Bisanda
                            </div>

                            <div class="text-[11px] text-gray-500 tracking-widest uppercase mt-1">
                                Executive Director
                            </div>
                        </div>
                    </div>

                    
                    <div class="flex justify-center items-end pb-3">
                        <img src="<?php echo e($logo); ?>"
                             alt="Uzima Milele Seal"
                             class="w-36 opacity-[0.06]">
                    </div>

                    
                    <div class="text-center">
                        <div class="inline-block bg-white border border-slate-200 rounded-2xl p-3 shadow-sm">
                            <img src="<?php echo e($qrUrl); ?>"
                                 alt="QR Code"
                                 class="w-28 h-28">
                        </div>

                        <div class="text-[10px] text-navy font-black uppercase mt-3">
                            Scan to Verify
                        </div>
                    </div>
                </div>

                
                <div class="certificate-details mt-8 border-t border-slate-200 pt-5 grid grid-cols-3 gap-6 text-left">
                    <div>
                        <div class="text-[11px] text-slate-400 font-black tracking-widest uppercase">
                            Certificate No
                        </div>

                        <div class="mt-2 text-navy font-black">
                            <?php echo e($certificate->certificate_number); ?>

                        </div>
                    </div>

                    <div class="text-center">
                        <div class="text-[11px] text-slate-400 font-black tracking-widest uppercase">
                            Issued Date
                        </div>

                        <div class="mt-2 text-navy font-black">
                            <?php echo e($certificate->issued_at->format('d M Y')); ?>

                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-[11px] text-slate-400 font-black tracking-widest uppercase">
                            Verification
                        </div>

                        <div class="mt-2 text-green-600 font-black">
                            Valid Certificate
                        </div>
                    </div>
                </div>

                <div class="mt-7 border-t border-slate-100"></div>
            </div>
        </div>

    </div>
</section>

<style>
    .certificate-preview-scroll {
        width: 100%;
    }

    .certificate-page {
        width: 1120px;
        min-height: 793px;
    }

    .certificate-header {
        height: 275px;
        background: linear-gradient(135deg, #076994 0%, #0083CB 100%);
    }

    .certificate-name {
        font-size: 52px;
    }

    /*
    |--------------------------------------------------------------------------
    | MOBILE PREVIEW
    |--------------------------------------------------------------------------
    | Keep the certificate in A4 landscape shape even on phone.
    | It will be horizontally scrollable instead of turning into a long mobile card.
    */
    @media (max-width: 768px) {
        .certificate-wrapper {
            padding-top: 24px;
            padding-bottom: 24px;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
        }

        .certificate-wrapper > div {
            min-width: 1120px;
            padding-left: 16px;
            padding-right: 16px;
        }

        .certificate-page {
            width: 1120px;
            min-height: 793px;
            transform: scale(0.55);
            transform-origin: top left;
            margin-bottom: -350px;
        }

        .certificate-actions {
            width: calc(100vw - 32px);
            position: relative;
            z-index: 5;
        }

        .certificate-name {
            font-size: 52px;
        }

        .certificate-middle {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .certificate-details {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 480px) {
        .certificate-page {
            transform: scale(0.32);
            margin-bottom: -535px;
        }
    }

    @page {
        size: A4 landscape;
        margin: 0;
    }

    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        html,
        body {
            width: 297mm !important;
            height: 210mm !important;
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
            overflow: hidden !important;
        }

        nav,
        header,
        footer,
        .certificate-actions {
            display: none !important;
        }

        main {
            padding-top: 0 !important;
            min-height: 0 !important;
        }

        .certificate-wrapper {
            width: 297mm !important;
            height: 210mm !important;
            min-height: 210mm !important;
            padding: 0 !important;
            margin: 0 !important;
            background: white !important;
            overflow: hidden !important;
        }

        .certificate-wrapper > div {
            width: 297mm !important;
            height: 210mm !important;
            max-width: 297mm !important;
            min-width: 297mm !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .certificate-page {
            width: 297mm !important;
            height: 210mm !important;
            min-height: 210mm !important;
            max-height: 210mm !important;
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
            overflow: hidden !important;
            transform: none !important;
            page-break-inside: avoid !important;
        }

        .certificate-header {
            height: 58mm !important;
            background: linear-gradient(135deg, #076994 0%, #0083CB 100%) !important;
        }

        .certificate-header .pt-7 {
            padding-top: 5mm !important;
        }

        .certificate-header .w-14 {
            width: 12mm !important;
            height: 12mm !important;
        }

        .certificate-header .mt-7 {
            margin-top: 5mm !important;
        }

        .certificate-header .text-5xl,
        .certificate-header .md\:text-6xl {
            font-size: 30pt !important;
            line-height: 1 !important;
        }

        .certificate-header .mt-4 {
            margin-top: 4mm !important;
        }

        .certificate-body {
            height: 152mm !important;
            padding: 7mm 22mm 6mm 22mm !important;
            overflow: hidden !important;
        }

        .certificate-body p {
            font-size: 9pt !important;
            line-height: 1.4 !important;
        }

        .certificate-name {
            font-size: 34pt !important;
            margin-top: 3mm !important;
        }

        .certificate-body .w-28 {
            margin-top: 4mm !important;
            margin-bottom: 5mm !important;
        }

        .certificate-body h3 {
            font-size: 15pt !important;
            margin-top: 4mm !important;
        }

        .certificate-middle {
            display: grid !important;
            grid-template-columns: 1fr 1fr 1fr !important;
            align-items: end !important;
            gap: 14mm !important;
            margin-top: 9mm !important;
        }

        .certificate-middle .w-36 {
            width: 28mm !important;
        }

        .certificate-middle .w-28 {
            width: 26mm !important;
            height: 26mm !important;
        }

        .certificate-details {
            display: grid !important;
            grid-template-columns: 1fr 1fr 1fr !important;
            margin-top: 8mm !important;
            padding-top: 4mm !important;
            gap: 10mm !important;
        }

        .certificate-details div {
            font-size: 9pt !important;
        }

        .certificate-details .text-\[11px\] {
            font-size: 7pt !important;
        }

        .certificate-details .mt-2 {
            margin-top: 2mm !important;
        }

        .certificate-body > .mt-7 {
            margin-top: 5mm !important;
        }
    }
</style>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/uzimamil/public_html/new.uzimamilele.or.tz/new/resources/views/certificates/show.blade.php ENDPATH**/ ?>