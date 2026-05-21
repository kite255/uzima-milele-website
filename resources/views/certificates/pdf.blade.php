<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate - {{ $certificate->certificate_number }}</title>

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
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            overflow: hidden;
            background: #ffffff;
            font-family: DejaVu Sans, sans-serif;
            color: #0E3D4F;
        }

        .certificate {
            position: relative;
            width: 297mm;
            height: 210mm;
            overflow: hidden;
            background: #ffffff;
        }

        .top-header {
            position: absolute;
            top: 0;
            left: 0;
            width: 297mm;
            height: 62mm;
            background: #0083CB;
            color: #ffffff;
            text-align: center;
            overflow: hidden;
            padding-top: 6mm;
        }

        .circle-left,
        .circle-right {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.14);
        }

        .circle-left {
            width: 75mm;
            height: 75mm;
            left: -24mm;
            top: -31mm;
        }

        .circle-right {
            width: 82mm;
            height: 82mm;
            right: -27mm;
            bottom: -33mm;
        }

        .header-content {
            position: relative;
            z-index: 5;
            width: 297mm;
            text-align: center;
        }

        .logo-wrap {
            width: 14mm;
            height: 14mm;
            margin: 0 auto 2.5mm auto;
            background: #ffffff;
            border-radius: 50%;
            text-align: center;
            padding-top: 1.3mm;
        }

        .logo {
            width: 11mm;
            height: 11mm;
            border: 0;
        }

        .ministry {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        .website {
            font-size: 6.5px;
            letter-spacing: 2.5px;
            margin-top: 1.2mm;
        }

        .title-wrap {
            width: 112mm;
            margin: 6.5mm auto 0 auto;
            padding: 0;
            text-align: center;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .cert-title-table {
            width: 112mm;
            margin: 0 auto;
            padding: 0;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .cert-title-table td {
            width: 10.18mm;
            padding: 0;
            margin: 0;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            text-align: center;
            vertical-align: middle;
            font-size: 32px;
            line-height: 34px;
            font-weight: bold;
            color: #ffffff;
        }

        .subtitle {
            margin-top: 4mm;
            font-size: 10px;
            letter-spacing: 3px;
            text-transform: uppercase;
            white-space: nowrap;
            text-align: center;
        }

        .main-body {
            position: absolute;
            top: 72mm;
            left: 24mm;
            width: 249mm;
            text-align: center;
            padding: 0;
            margin: 0;
        }

        .presented {
            width: 249mm;
            font-size: 10.5px;
            color: #6B7280;
            margin: 0 auto 2mm auto;
            text-align: center;
        }

        .student-name {
            width: 249mm;
            font-family: DejaVu Serif, serif;
            font-style: italic;
            font-size: 35px;
            line-height: 37px;
            font-weight: bold;
            color: #0E3D4F;
            margin: 0 auto 2.5mm auto;
            text-align: center;
        }

        .gold-line {
            width: 38mm;
            height: 1.3mm;
            background: #F4B122;
            margin: 0 auto 4.5mm auto;
        }

        .completion-text {
            width: 249mm;
            font-size: 11px;
            color: #374151;
            margin: 0 auto 3.5mm auto;
            line-height: 1.5;
            text-align: center;
        }

        .lesson-title {
            width: 220mm;
            margin: 0 auto;
            font-size: 21px;
            line-height: 25px;
            font-weight: bold;
            color: #0083CB;
            text-align: center;
        }

        .auth-row {
            position: absolute;
            left: 24mm;
            top: 136mm;
            width: 249mm;
            display: table;
            table-layout: fixed;
        }

        .auth-cell {
            display: table-cell;
            width: 33.333%;
            text-align: center;
            vertical-align: middle;
        }

        .signature-block,
        .seal-block,
        .qr-block {
            width: 82mm;
            min-height: 31mm;
            margin: 0 auto;
            text-align: center;
        }

        .signature-img {
            max-width: 40mm;
            max-height: 14mm;
            border: 0;
            margin-bottom: 1.3mm;
        }

        .signature-fallback {
            font-family: DejaVu Serif, serif;
            font-style: italic;
            font-size: 22px;
            height: 14mm;
            line-height: 14mm;
            color: #0E3D4F;
            margin-bottom: 1.3mm;
        }

        .signature-line {
            border-top: 1px solid #94A3B8;
            width: 43mm;
            margin: 0 auto;
            padding-top: 3mm;
        }

        .signature-name {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0E3D4F;
        }

        .signature-title {
            font-size: 6px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #6B7280;
            margin-top: 1.2mm;
        }

        .seal-cell {
            vertical-align: middle;
            text-align: center;
        }

        .seal-img {
            width: 31mm;
            opacity: 0.22;
            margin: 0 auto;
            border: 0;
        }

      .qr-block {
    width: 82mm;
    margin: -2mm auto 0 auto;
    text-align: center;
}

     .qr-box {
    display: inline-block;
    width: 26mm;
    height: 26mm;
    border: 1px solid #E5E7EB;
    padding: 1.5mm;
    background: #ffffff;
    border-radius: 2.5mm;
    text-align: center;
}

     .qr-img {
    width: 23mm;
    height: 23mm;
    display: block;
    border: 0;
}

       .qr-svg {
    width: 23mm;
    height: 23mm;
    display: block;
}

      .qr-svg svg {
    width: 23mm;
    height: 23mm;
    display: block;
}

   .qr-title {
    font-size: 7px;
    font-weight: bold;
    margin-top: 2.2mm;
    text-transform: uppercase;
    color: #0E3D4F;
}

        .verify-url {
            display: none !important;
        }

        .meta {
            position: absolute;
            left: 24mm;
            top: 181mm;
            width: 249mm;
            border-top: 1px solid #E5E7EB;
            padding-top: 4mm;
            display: table;
            table-layout: fixed;
        }

        .meta-cell {
            display: table-cell;
            width: 33.333%;
            vertical-align: top;
        }

        .meta-left {
            text-align: left;
        }

        .meta-center {
            text-align: center;
        }

        .meta-right {
            text-align: right;
        }

        .meta-label {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            color: #9CA3AF;
            margin-bottom: 2mm;
        }

        .meta-value {
            font-size: 8px;
            font-weight: bold;
            color: #0E3D4F;
        }

        .valid {
            color: #008A2E;
        }
    </style>
</head>

<body>
@php
    $issuedDate = $certificate->issued_at ?? $certificate->created_at;
    $issuedFormatted = \Carbon\Carbon::parse($issuedDate)->format('d M Y');

    $verifyUrl = route('certificates.verify', $certificate->certificate_number);

    $logoPath = public_path('logo.png');
    $signaturePath = public_path('signature.png');

    $logoSrc = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : '';

    $signatureSrc = file_exists($signaturePath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($signaturePath))
        : '';

    $qrSvg = null;

    if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
        try {
            $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(150)
                ->margin(1)
                ->generate($verifyUrl);
        } catch (\Throwable $e) {
            $qrSvg = null;
        }
    }

    $fallbackQrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($verifyUrl);
@endphp

<div class="certificate">

    <div class="top-header">
        <div class="circle-left"></div>
        <div class="circle-right"></div>

        <div class="header-content">
            <div class="logo-wrap">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" class="logo" alt="Uzima Milele Ministry">
                @endif
            </div>

            <div class="ministry">Uzima Milele Ministry</div>
            <div class="website">www.uzimamilele.or.tz</div>

            <div class="title-wrap">
                <table class="cert-title-table">
                    <tr>
                        <td>C</td>
                        <td>E</td>
                        <td>R</td>
                        <td>T</td>
                        <td>I</td>
                        <td>F</td>
                        <td>I</td>
                        <td>C</td>
                        <td>A</td>
                        <td>T</td>
                        <td>E</td>
                    </tr>
                </table>
            </div>

            <div class="subtitle">OF COMPLETION</div>
        </div>
    </div>

    <div class="main-body">
        <div class="presented">
            This certificate is proudly presented to
        </div>

        <div class="student-name">
            {{ $certificate->user->name }}
        </div>

        <div class="gold-line"></div>

        <div class="completion-text">
            For successfully completing the Bible lesson program offered by
            <strong>Uzima Milele Ministry</strong>.
        </div>

        <div class="lesson-title">
            {{ $certificate->lesson->title }}
        </div>
    </div>

    <div class="auth-row">
        <div class="auth-cell">
            <div class="signature-block">
                @if($signatureSrc)
                    <img src="{{ $signatureSrc }}" class="signature-img" alt="Signature">
                @else
                    <div class="signature-fallback">Phillip Bisanda</div>
                @endif

                <div class="signature-line">
                    <div class="signature-name">Phillip Bisanda</div>
                    <div class="signature-title">Executive Director</div>
                </div>
            </div>
        </div>

        <div class="auth-cell seal-cell">
            <div class="seal-block">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" class="seal-img" alt="Uzima Milele Ministry Seal">
                @endif
            </div>
        </div>

        <div class="auth-cell">
            <div class="qr-block">
                <div class="qr-box">
                    @if($qrSvg)
                        <div class="qr-svg">{!! $qrSvg !!}</div>
                    @else
                        <img src="{{ $fallbackQrSrc }}" class="qr-img" alt="Certificate QR Code">
                    @endif
                </div>

                <div class="qr-title">Scan to Verify</div>
            </div>
        </div>
    </div>

    <div class="meta">
        <div class="meta-cell meta-left">
            <div class="meta-label">Certificate No</div>
            <div class="meta-value">{{ $certificate->certificate_number }}</div>
        </div>

        <div class="meta-cell meta-center">
            <div class="meta-label">Issued Date</div>
            <div class="meta-value">{{ $issuedFormatted }}</div>
        </div>

        <div class="meta-cell meta-right">
            <div class="meta-label">Verification</div>
            <div class="meta-value valid">Valid Certificate</div>
        </div>
    </div>

</div>
</body>
</html>
