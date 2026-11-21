<?php

namespace App\Services;

use App\Models\Certificate;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

class CertificatePdf
{
    public function render(Certificate $certificate): DomPdf
    {
        return Pdf::loadView('certificates.pdf', [
            'certificate' => $certificate,
        ])
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 96,
            ]);
    }

    public static function verificationQr(string $verifyUrl): string
    {
        $writer = new Writer(new ImageRenderer(
            new RendererStyle(240, 4),
            new SvgImageBackEnd(),
        ));

        return 'data:image/svg+xml;base64,'.base64_encode($writer->writeString($verifyUrl));
    }
}
