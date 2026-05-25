@extends('layouts.app')

@section('title', 'Certificate - ' . $certificate->certificate_number)

@section('content')
@php
    $issuedDate = $certificate->issued_at ?? $certificate->created_at;
    $issuedFormatted = \Carbon\Carbon::parse($issuedDate)->format('d M Y');

    $verifyUrl = route('certificates.verify', $certificate->certificate_number);

    $logoUrl = asset('logo.png');
    $signatureUrl = asset('signature.png');

    $qrCodeHtml = null;

    if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
        try {
            $qrCodeHtml = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(180)
                ->margin(1)
                ->generate($verifyUrl);
        } catch (\Throwable $e) {
            $qrCodeHtml = null;
        }
    }

    $fallbackQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($verifyUrl);
@endphp

<div class="min-h-screen bg-slate-100 py-8 px-4">
    <div class="max-w-6xl mx-auto mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0E3D4F]">
                Certificate of Completion
            </h1>

            <p class="text-sm text-slate-600 mt-1">
                Certificate No: {{ $certificate->certificate_number }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('certificates.download', $certificate->certificate_number) }}"
               class="inline-flex items-center justify-center rounded-lg bg-[#0083CB] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#076994] transition">
                Download PDF
            </a>

            <a href="{{ route('certificates.verify', $certificate->certificate_number) }}"
               target="_blank"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-[#0E3D4F] hover:bg-slate-50 transition">
                Verify
            </a>
        </div>
    </div>

    <div class="max-w-6xl mx-auto overflow-x-auto">
        <div class="certificate-preview mx-auto bg-white shadow-2xl">

            {{-- Header --}}
            <div class="top-header">
                <div class="circle-left"></div>
                <div class="circle-right"></div>

                <div class="header-content">
                    <div class="logo-wrap">
                        <img src="{{ $logoUrl }}" class="logo" alt="Uzima Milele Ministry">
                    </div>

                    <div class="ministry">Uzima Milele Ministry</div>
                    <div class="website">www.uzimamilele.or.tz</div>

                    <div class="title">Certificate</div>
                    <div class="subtitle">of Completion</div>
                </div>
            </div>

            {{-- Main Body --}}
            <div class="certificate-body">
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

            {{-- Signature / Seal / QR --}}
            <div class="certificate-auth-row">
                <div class="signature-block">
                    <img
                        src="{{ $signatureUrl }}"
                        class="signature-img"
                        alt="Signature"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                    >

                    <div class="signature-fallback" style="display:none;">
                        Phillip Bisanda
                    </div>

                    <div class="signature-line">
                        <div class="signature-name">Phillip Bisanda</div>
                        <div class="signature-title">Executive Director</div>
                    </div>
                </div>

                <div class="seal-block">
                    <img src="{{ $logoUrl }}" class="seal-img" alt="Uzima Milele Ministry">
                </div>

                <div class="qr-block">
                    <div class="qr-box">
                        @if($qrCodeHtml)
                            {!! $qrCodeHtml !!}
                        @else
                            <img src="{{ $fallbackQrUrl }}" class="qr-img" alt="Certificate QR Code">
                        @endif
                    </div>

                    <div class="qr-title">Scan to Verify</div>

                    <div class="verify-url">
                        {{ $verifyUrl }}
                    </div>
                </div>
            </div>

            {{-- Bottom Meta --}}
            <div class="certificate-meta">
                <div class="meta-item text-left">
                    <div class="meta-label">Certificate No</div>
                    <div class="meta-value">{{ $certificate->certificate_number }}</div>
                </div>

                <div class="meta-item text-center">
                    <div class="meta-label">Issued Date</div>
                    <div class="meta-value">{{ $issuedFormatted }}</div>
                </div>

                <div class="meta-item text-right">
                    <div class="meta-label">Verification</div>
                    <div class="meta-value valid">Valid Certificate</div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .certificate-preview {
        width: 1122px;
        height: 794px;
        color: #0E3D4F;
        font-family: Arial, sans-serif;
        overflow: hidden;
        display: grid;
        grid-template-rows: 235px 190px 215px 154px;
        background: #ffffff;
    }

    .top-header {
        background: #0083CB;
        color: #ffffff;
        position: relative;
        text-align: center;
        overflow: hidden;
        padding-top: 24px;
    }

    .header-content {
        position: relative;
        z-index: 5;
    }

    .circle-left,
    .circle-right {
        position: absolute;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
    }

    .circle-left {
        width: 284px;
        height: 284px;
        left: -88px;
        top: -116px;
    }

    .circle-right {
        width: 310px;
        height: 310px;
        right: -104px;
        bottom: -126px;
    }

    .logo-wrap {
        width: 49px;
        height: 49px;
        margin: 0 auto 9px;
        background: #ffffff;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .logo {
        width: 40px;
        height: 40px;
        object-fit: contain;
    }

    .ministry {
        font-size: 7px;
        font-weight: 800;
        letter-spacing: 4px;
        text-transform: uppercase;
    }

    .website {
        font-size: 6px;
        letter-spacing: 3px;
        margin-top: 4px;
    }

    .title {
        margin-top: 24px;
        font-size: 40px;
        line-height: 42px;
        font-weight: 800;
        letter-spacing: 18px;
        text-transform: uppercase;
    }

    .subtitle {
        margin-top: 12px;
        font-size: 11px;
        letter-spacing: 10px;
        text-transform: uppercase;
    }

    .certificate-body {
        text-align: center;
        padding: 13px 90px 0;
    }

    .presented {
        font-size: 12px;
        color: #6B7280;
        margin-bottom: 9px;
    }

    .student-name {
        font-family: Georgia, "Times New Roman", serif;
        font-style: italic;
        font-size: 42px;
        line-height: 46px;
        font-weight: 700;
        color: #0E3D4F;
        margin-bottom: 9px;
    }

    .gold-line {
        width: 144px;
        height: 5px;
        background: #F4B122;
        margin: 0 auto 16px;
    }

    .completion-text {
        font-size: 12px;
        color: #374151;
        margin-bottom: 13px;
    }

    .lesson-title {
        max-width: 800px;
        margin: 0 auto;
        font-size: 25px;
        line-height: 31px;
        font-weight: 800;
        color: #0083CB;
    }

    .certificate-auth-row {
        padding: 18px 90px 0;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        align-items: end;
        column-gap: 30px;
    }

    .signature-block,
    .seal-block,
    .qr-block {
        height: 165px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        text-align: center;
    }

    .signature-img {
        max-width: 155px;
        max-height: 62px;
        object-fit: contain;
    }

    .signature-fallback {
        font-family: Georgia, "Times New Roman", serif;
        font-style: italic;
        font-size: 22px;
        height: 62px;
        line-height: 62px;
    }

    .signature-line {
        border-top: 1px solid #94A3B8;
        width: 310px;
        margin: 5px auto 0;
        padding-top: 10px;
    }

    .signature-name {
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        color: #0E3D4F;
    }

    .signature-title {
        font-size: 7px;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #6B7280;
        margin-top: 5px;
    }

    .seal-img {
        width: 105px;
        opacity: 0.09;
        margin-bottom: 42px;
    }

    .qr-box {
        width: 102px;
        height: 102px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #E5E7EB;
        padding: 7px;
        background: #ffffff;
        border-radius: 12px;
    }

    .qr-box svg,
    .qr-img {
        width: 86px;
        height: 86px;
        display: block;
    }

    .qr-title {
        font-size: 7px;
        font-weight: 800;
        margin-top: 8px;
        text-transform: uppercase;
        color: #0E3D4F;
    }

    .verify-url {
        width: 210px;
        margin: 4px auto 0;
        font-size: 4px;
        color: #9CA3AF;
        line-height: 6px;
        word-break: break-all;
    }

    .certificate-meta {
        margin: 0 90px;
        border-top: 1px solid #E5E7EB;
        padding-top: 15px;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        align-content: start;
    }

    .meta-label {
        font-size: 7px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 800;
        color: #9CA3AF;
        margin-bottom: 8px;
    }

    .meta-value {
        font-size: 9px;
        font-weight: 800;
        color: #0E3D4F;
    }

    .valid {
        color: #008A2E;
    }

    @media (max-width: 1200px) {
        .certificate-preview {
            transform: scale(0.9);
            transform-origin: top left;
        }
    }
</style> 
@endsection