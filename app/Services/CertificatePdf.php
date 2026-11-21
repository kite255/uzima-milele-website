<?php

namespace App\Services;

use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

class CertificatePdf
{
    public function render(Certificate $certificate, bool $allowRemoteQr = true): DomPdf
    {
        return Pdf::loadView('certificates.pdf', [
            'certificate' => $certificate,
            'allowRemoteQr' => $allowRemoteQr,
        ])
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isRemoteEnabled' => $allowRemoteQr,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 96,
            ]);
    }
}
