<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 297mm;
            height: 210mm;
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            background: #ffffff;
            color: #0E3D4F;
        }

        .certificate {
            width: 297mm;
            height: 210mm;
            position: relative;
            background: #ffffff;
            overflow: hidden;
        }

        .top-header {
            height: 68mm;
            background: #0083CB;
            color: #ffffff;
            text-align: center;
            padding-top: 7mm;
            position: relative;
            overflow: hidden;
        }

        .circle-left {
            position: absolute;
            left: -25mm;
            top: -32mm;
            width: 75mm;
            height: 75mm;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
        }

        .circle-right {
            position: absolute;
            right: -28mm;
            bottom: -35mm;
            width: 82mm;
            height: 82mm;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
        }

        .header-content {
            position: relative;
            z-index: 5;
        }

        .logo-wrap {
            width: 14mm;
            height: 14mm;
            margin: 0 auto 3mm auto;
            background: #ffffff;
            border-radius: 50%;
            text-align: center;
            padding-top: 1.5mm;
        }

        .logo {
            width: 11mm;
            height: 11mm;
        }

        .ministry {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 5px;
            text-transform: uppercase;
        }

        .website {
            font-size: 6px;
            letter-spacing: 3px;
            margin-top: 1.5mm;
            opacity: 0.95;
        }

        .title {
            margin-top: 7mm;
            font-size: 37px;
            line-height: 38px;
            font-weight: bold;
            letter-spacing: 12px;
            text-transform: uppercase;
        }

        .subtitle {
            margin-top: 4mm;
            font-size: 13px;
            line-height: 13px;
            letter-spacing: 10px;
            text-transform: uppercase;
        }

        .body {
            height: 142mm;
            position: relative;
            padding: 7mm 24mm 0 24mm;
            text-align: center;
        }

        .watermark {
            position: absolute;
            top: 61mm;
            left: 50%;
            margin-left: -21mm;
            width: 42mm;
            opacity: 0.035;
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 2;
        }

        .presented {
            font-size: 10.5px;
            color: #6B7280;
            margin-bottom: 3mm;
        }

        .student-name {
            font-family: Georgia, serif;
            font-style: italic;
            font-size: 36px;
            line-height: 38px;
            font-weight: bold;
            color: #0E3D4F;
            margin-bottom: 3mm;
        }

        .gold-line {
            width: 28mm;
            height: 1.1mm;
            background: #F4B122;
            margin: 0 auto 5mm auto;
        }

        .completion-text {
            font-size: 11px;
            color: #374151;
            margin-bottom: 4mm;
        }

        .lesson-title {
            font-size: 20px;
            line-height: 24px;
            font-weight: bold;
            color: #0083CB;
            margin-bottom: 8mm;
        }

        .bottom-table {
            width: 86%;
            margin: 0 auto;
            border-collapse: collapse;
        }

        .bottom-table td {
            width: 50%;
            vertical-align: bottom;
            text-align: center;
        }

        .signature-fallback {
            font-family: Georgia, serif;
            font-style: italic;
            font-size: 24px;
            color: #0E3D4F;
            height: 17mm;
            line-height: 17mm;
        }

        .signature-img {
            width: 50mm;
            height: 17mm;
        }

        .signature-line {
            border-top: 1px solid #94A3B8;
            width: 70mm;
            margin: 1mm auto 0 auto;
            padding-top: 3mm;
        }

        .signature-name {
            font-size: 9px;
            font-weight: bold;
            color: #0E3D4F;
            text-transform: uppercase;
        }

        .signature-title {
            font-size: 7px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #6B7280;
            margin-top: 1.2mm;
        }

        .qr-box {
            display: inline-block;
            border: 1px solid #E5E7EB;
            padding: 2mm;
            background: #ffffff;
        }

        .qr-img {
            width: 26mm;
            height: 26mm;
        }

        .qr-title {
            font-size: 7px;
            font-weight: bold;
            color: #0E3D4F;
            margin-top: 2mm;
            text-transform: uppercase;
        }

        .verify-url {
            font-size: 5.5px;
            color: #9CA3AF;
            margin-top: 1mm;
            line-height: 7px;
            word-break: break-all;
        }

        .meta {
            width: 90%;
            margin: 8mm auto 0 auto;
            border-top: 1px solid #E5E7EB;
            padding-top: 4mm;
            border-collapse: collapse;
        }

        .meta td {
            width: 33.333%;
            text-align: left;
            vertical-align: top;
            padding: 0 6mm;
        }

        .meta td:nth-child(2) {
            text-align: center;
        }

        .meta td:nth-child(3) {
            text-align: right;
        }

        .meta-label {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: bold;
            color: #9CA3AF;
            margin-bottom: 2mm;
        }

        .meta-value {
            font-size: 10px;
            font-weight: bold;
            color: #0E3D4F;
        }

        .valid {
            color: #008A2E;
        }

        .bottom-border {
            width: 90%;
            margin: 7mm auto 0 auto;
            border-top: 1px solid #F1F5F9;
        }
    </style>
</head>

<body>

<?php
    $issuedDate = $certificate->issued_at ?? $certificate->created_at;
    $issuedFormatted = \Carbon\Carbon::parse($issuedDate)->format('d M Y');

    $verifyUrl = route('certificates.verify', $certificate->certificate_number);
    $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($verifyUrl);

    $logoPath = public_path('logo.png');
    $signaturePath = public_path('signature.png');

    $logoSrc = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : '';

    $signatureSrc = file_exists($signaturePath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($signaturePath))
        : '';

    $hasLogo = $logoSrc !== '';
    $hasSignature = $signatureSrc !== '';
?>

<div class="certificate">

    <div class="top-header">
        <div class="circle-left"></div>
        <div class="circle-right"></div>

        <div class="header-content">
            <div class="logo-wrap">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasLogo): ?>
                    <img src="<?php echo e($logoSrc); ?>" class="logo" alt="">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="ministry">Uzima Milele Ministry</div>
            <div class="website">www.uzimamilele.or.tz</div>

            <div class="title">Certificate</div>
            <div class="subtitle">of Completion</div>
        </div>
    </div>

    <div class="body">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasLogo): ?>
            <img src="<?php echo e($logoSrc); ?>" class="watermark" alt="">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="content">
            <div class="presented">
                This certificate is proudly presented to
            </div>

            <div class="student-name">
                <?php echo e($certificate->user->name); ?>

            </div>

            <div class="gold-line"></div>

            <div class="completion-text">
                For successfully completing the Bible lesson program offered by
                <strong>Uzima Milele Ministry</strong>.
            </div>

            <div class="lesson-title">
                <?php echo e($certificate->lesson->title); ?>

            </div>

            <table class="bottom-table">
                <tr>
                    <td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasSignature): ?>
                            <img src="<?php echo e($signatureSrc); ?>" class="signature-img" alt="">
                        <?php else: ?>
                            <div class="signature-fallback">Phillip Bisanda</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="signature-line">
                            <div class="signature-name">Phillip Bisanda</div>
                            <div class="signature-title">Executive Director</div>
                        </div>
                    </td>

                    <td>
                        <div class="qr-box">
                            <img src="<?php echo e($qrImageUrl); ?>" class="qr-img" alt="">
                        </div>

                        <div class="qr-title">Scan to Verify</div>
                        <div class="verify-url"><?php echo e($verifyUrl); ?></div>
                    </td>
                </tr>
            </table>

            <table class="meta">
                <tr>
                    <td>
                        <div class="meta-label">Certificate No</div>
                        <div class="meta-value"><?php echo e($certificate->certificate_number); ?></div>
                    </td>

                    <td>
                        <div class="meta-label">Issued Date</div>
                        <div class="meta-value"><?php echo e($issuedFormatted); ?></div>
                    </td>

                    <td>
                        <div class="meta-label">Verification</div>
                        <div class="meta-value valid">Valid Certificate</div>
                    </td>
                </tr>
            </table>

            <div class="bottom-border"></div>
        </div>
    </div>

</div>

</body>
</html><?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\certificates\pdf.blade.php ENDPATH**/ ?>