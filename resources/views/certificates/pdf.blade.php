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
            position: absolute;
            top: 0;
            left: 0;
            width: 297mm;
            height: 62mm;
            background: #0083CB;
            color: #ffffff;
            text-align: center;
            padding-top: 6mm;
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
            width: 13mm;
            height: 13mm;
            margin: 0 auto 2mm auto;
            background: #ffffff;
            border-radius: 50%;
            text-align: center;
            padding-top: 1.3mm;
        }

        .logo {
            width: 10.5mm;
            height: 10.5mm;
        }

        .ministry {
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        .website {
            font-size: 6px;
            letter-spacing: 3px;
            margin-top: 1mm;
            opacity: 0.95;
        }

        .title {
            margin-top: 6mm;
            font-size: 34px;
            line-height: 35px;
            font-weight: bold;
            letter-spacing: 12px;
            text-transform: uppercase;
        }

        .subtitle {
            margin-top: 3mm;
            font-size: 10px;
            line-height: 10px;
            letter-spacing: 8px;
            text-transform: uppercase;
        }

        .body {
            position: absolute;
            top: 62mm;
            left: 0;
            width: 297mm;
            height: 148mm;
            padding: 7mm 24mm 0 24mm;
            text-align: center;
            overflow: hidden;
        }

        .content {
            position: relative;
            z-index: 2;
            height: 100%;
        }

        .presented {
            font-size: 10px;
            color: #6B7280;
            margin-bottom: 2.5mm;
        }

        .student-name {
            font-family: DejaVu Serif, serif;
            font-style: italic;
            font-size: 38px;
            line-height: 40px;
            font-weight: bold;
            color: #0E3D4F;
            margin-bottom: 2.5mm;
        }

        .gold-line {
            width: 38mm;
            height: 1.3mm;
            background: #F4B122;
            margin: 0 auto 4.5mm auto;
        }

        .completion-text {
            font-size: 10.5px;
            color: #374151;
            margin-bottom: 3.5mm;
            line-height: 1.6;
        }

        .lesson-title {
            font-size: 22px;
            line-height: 26px;
            font-weight: bold;
            color: #0083CB;
            margin-bottom: 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Signature / Seal / QR
        |--------------------------------------------------------------------------
        | 3-column table keeps the lower section centered in DomPDF.
        */
        .bottom-table {
            width: 140mm;
            margin: 10mm auto 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .bottom-table td {
            width: 33.333%;
            vertical-align: bottom;
            text-align: center;
        }

        .signature-fallback {
            font-family: DejaVu Serif, serif;
            font-style: italic;
            font-size: 22px;
            color: #0E3D4F;
            height: 16mm;
            line-height: 16mm;
        }

        .signature-img {
            max-width: 40mm;
            max-height: 16mm;
        }

        .signature-line {
            border-top: 1px solid #94A3B8;
            width: 42mm;
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
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #6B7280;
            margin-top: 1.2mm;
        }

        .seal-img {
            width: 30mm;
            opacity: 0.075;
            margin-bottom: 7mm;
        }

        .qr-box {
            display: inline-block;
            border: 1px solid #E5E7EB;
            padding: 2mm;
            background: #ffffff;
        }

        .qr-img {
            width: 24mm;
            height: 24mm;
        }

        .qr-title {
            font-size: 7px;
            font-weight: bold;
            color: #0E3D4F;
            margin-top: 2mm;
            text-transform: uppercase;
        }

        .verify-url {
            font-size: 3.6px;
            color: #9CA3AF;
            margin-top: 1mm;
            line-height: 5px;
            word-break: break-all;
        }

        /*
        |--------------------------------------------------------------------------
        | Bottom Details
        |--------------------------------------------------------------------------
        */
        .meta {
            width: 140mm;
            margin: 10mm auto 0 auto;
            border-top: 1px solid #E5E7EB;
            padding-top: 4mm;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .meta td {
            width: 33.333%;
            vertical-align: top;
            padding: 0 1mm;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        .meta td:nth-child(1) {
            text-align: left;
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
            letter-spacing: 1px;
            font-weight: bold;
            color: #9CA3AF;
            margin-bottom: 2mm;
        }

        .meta-value {
            font-size: 8px;
            font-weight: bold;
            color: #0E3D4F;
            white-space: normal;
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
@endphp

<div class="certificate">

    {{-- HEADER --}}
    <div class="top-header">
        <div class="circle-left"></div>
        <div class="circle-right"></div>

        <div class="header-content">
            <div class="logo-wrap">
                @if($hasLogo)
                    <img src="{{ $logoSrc }}" class="logo" alt="">
                @endif
            </div>

            <div class="ministry">
                Uzima Milele Ministry
            </div>

            <div class="website">
                www.uzimamilele.or.tz
            </div>

            <div class="title">
                Certificate
            </div>

            <div class="subtitle">
                of Completion
            </div>
        </div>
    </div>

    {{-- BODY --}}
    <div class="body">
        <div class="content">

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

            {{-- SIGNATURE / SEAL / QR --}}
            <table class="bottom-table">
                <tr>
                    {{-- SIGNATURE --}}
                    <td>
                        @if($hasSignature)
                            <img src="{{ $signatureSrc }}" class="signature-img" alt="">
                        @else
                            <div class="signature-fallback">Phillip Bisanda</div>
                        @endif

                        <div class="signature-line">
                            <div class="signature-name">
                                Phillip Bisanda
                            </div>

                            <div class="signature-title">
                                Executive Director
                            </div>
                        </div>
                    </td>

                    {{-- CENTER SEAL --}}
                    <td>
                        @if($hasLogo)
                            <img src="{{ $logoSrc }}" class="seal-img" alt="">
                        @endif
                    </td>

                    {{-- QR CODE --}}
                    <td>
                        <div class="qr-box">
                            <img src="{{ $qrImageUrl }}" class="qr-img" alt="">
                        </div>

                        <div class="qr-title">
                            Scan to Verify
                        </div>

                        <div class="verify-url">
                            {{ $verifyUrl }}
                        </div>
                    </td>
                </tr>
            </table>

            {{-- DETAILS --}}
            <table class="meta">
                <tr>
                    <td>
                        <div class="meta-label">
                            Certificate No
                        </div>

                        <div class="meta-value">
                            {{ $certificate->certificate_number }}
                        </div>
                    </td>

                    <td>
                        <div class="meta-label">
                            Issued Date
                        </div>

                        <div class="meta-value">
                            {{ $issuedFormatted }}
                        </div>
                    </td>

                    <td>
                        <div class="meta-label">
                            Verification
                        </div>

                        <div class="meta-value valid">
                            Valid Certificate
                        </div>
                    </td>
                </tr>
            </table>

        </div>
    </div>

</div>

</body>
</html>