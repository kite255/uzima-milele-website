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
@endphp

<div class="min-h-screen bg-slate-100 py-8 px-4">
    <div class="max-w-6xl mx-auto mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-[#0E3D4F]">
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

    <div class="max-w-6xl mx-auto certificate-preview-wrap">
        <div class="certificate-scale-box">
            <div class="certificate-preview bg-white relative overflow-hidden shadow-2xl">
                <div class="top-header">
                    <div class="circle-left"></div>
                    <div class="circle-right"></div>

                    <div class="relative z-10">
                        <div class="logo-wrap">
                            <img src="{{ $logoUrl }}" class="logo" alt="Uzima Milele Ministry">
                        </div>

                        <div class="ministry">Uzima Milele Ministry</div>
                        <div class="website">www.uzimamilele.or.tz</div>

                        <div class="title">Certificate</div>
                        <div class="subtitle">of Completion</div>
                    </div>
                </div>

                <div class="body-section">
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

                <div class="bottom-section">
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
                            <div class="signature-name">
                                Phillip Bisanda
                            </div>

                            <div class="signature-title">
                                Executive Director
                            </div>
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
                                <img
                                    src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($verifyUrl) }}"
                                    class="qr-img"
                                    alt="Certificate QR Code"
                                >
                            @endif
                        </div>

                        <div class="qr-title">
                            Scan to Verify
                        </div>
                    </div>
                </div>

                <div class="meta">
                    <div>
                        <div class="meta-label">Certificate No</div>
                        <div class="meta-value">{{ $certificate->certificate_number }}</div>
                    </div>

                    <div>
                        <div class="meta-label">Issued Date</div>
                        <div class="meta-value">{{ $issuedFormatted }}</div>
                    </div>

                    <div>
                        <div class="meta-label">Verification</div>
                        <div class="meta-value valid">Valid Certificate</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .certificate-preview-wrap {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        padding-bottom: 20px;
    }

    .certificate-scale-box {
        width: 1122px;
        height: 794px;
        margin: 0 auto;
    }

    .certificate-preview {
        width: 1122px;
        height: 794px;
        color: #0E3D4F;
        font-family: Arial, sans-serif;
    }

    .top-header {
        height: 235px;
        background: #0083CB;
        color: #ffffff;
        position: relative;
        text-align: center;
        padding-top: 24px;
        overflow: hidden;
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
        left: -90px;
        top: -118px;
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
        text-align: center;
    }

    .subtitle {
        margin-top: 12px;
        font-size: 11px;
        letter-spacing: 10px;
        text-transform: uppercase;
        text-align: center;
    }

    .body-section {
        position: absolute;
        top: 255px;
        left: 90px;
        right: 90px;
        text-align: center;
        padding: 0;
    }

    .presented {
        font-size: 12px;
        color: #6B7280;
        margin-bottom: 9px;
        text-align: center;
    }

    .student-name {
        font-family: Georgia, "Times New Roman", serif;
        font-style: italic;
        font-size: 42px;
        line-height: 46px;
        font-weight: 700;
        color: #0E3D4F;
        margin-bottom: 9px;
        text-align: center;
    }

    .gold-line {
        width: 144px;
        height: 5px;
        background: #F4B122;
        margin: 0 auto 17px;
    }

    .completion-text {
        font-size: 12px;
        color: #374151;
        margin-bottom: 14px;
        text-align: center;
    }

    .lesson-title {
        max-width: 760px;
        margin: 0 auto;
        font-size: 25px;
        line-height: 31px;
        font-weight: 800;
        color: #0083CB;
        text-align: center;
    }

    .bottom-section {
        position: absolute;
        left: 90px;
        right: 90px;
        top: 515px;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        align-items: center;
        gap: 30px;
    }

    .bottom-section > div {
        text-align: center;
    }

    .signature-block,
    .seal-block,
    .qr-block {
        min-height: 118px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .signature-img {
        max-width: 150px;
        max-height: 55px;
        object-fit: contain;
        margin-bottom: 5px;
    }

    .signature-fallback {
        font-family: Georgia, "Times New Roman", serif;
        font-style: italic;
        font-size: 22px;
        height: 50px;
        line-height: 50px;
        margin-bottom: 5px;
    }

    .signature-line {
        border-top: 1px solid #94A3B8;
        width: 160px;
        margin: 0 auto;
        padding-top: 10px;
    }

    .signature-name {
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .signature-title {
        font-size: 7px;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #6B7280;
        margin-top: 5px;
    }

    .seal-img {
        width: 120px;
        opacity: 0.22;
        margin: 0;
    }

    .qr-box {
        width: 96px;
        height: 96px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #E5E7EB;
        padding: 4px;
        background: #ffffff;
        border-radius: 10px;
    }

    .qr-box svg {
        width: 88px;
        height: 88px;
        display: block;
    }

    .qr-img {
        width: 88px;
        height: 88px;
        display: block;
    }

    .qr-title {
        font-size: 7px;
        font-weight: 800;
        margin-top: 8px;
        text-transform: uppercase;
        color: #0E3D4F;
    }

    .meta {
        position: absolute;
        left: 90px;
        right: 90px;
        bottom: 45px;
        border-top: 1px solid #E5E7EB;
        padding-top: 15px;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
    }

    .meta > div:nth-child(1) {
        text-align: left;
    }

    .meta > div:nth-child(2) {
        text-align: center;
    }

    .meta > div:nth-child(3) {
        text-align: right;
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
        .certificate-scale-box {
            width: 1122px;
            height: 794px;
            margin: 0;
        }
    }

    @media (max-width: 768px) {
        .certificate-preview-wrap {
            overflow: hidden;
        }

        .certificate-scale-box {
            width: 100%;
            height: 505px;
            overflow: hidden;
        }

        .certificate-preview {
            transform: scale(0.58);
            transform-origin: top left;
        }
    }

    @media (max-width: 430px) {
        .certificate-preview-wrap {
            overflow: hidden;
        }

        .certificate-scale-box {
            width: 100%;
            height: 310px;
            overflow: hidden;
        }

        .certificate-preview {
            transform: scale(0.36);
            transform-origin: top left;
        }
    }

    @media (max-width: 390px) {
        .certificate-scale-box {
            height: 290px;
        }

        .certificate-preview {
            transform: scale(0.34);
            transform-origin: top left;
        }
    }
</style>
@endsection