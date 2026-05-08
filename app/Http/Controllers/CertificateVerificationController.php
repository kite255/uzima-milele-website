<?php

namespace App\Http\Controllers;

use App\Models\Certificate;

class CertificateVerificationController extends Controller
{
    public function show(string $certificateNumber)
    {
        $certificate = Certificate::with(['user', 'lesson'])
            ->where('certificate_number', $certificateNumber)
            ->first();

        return view('certificates.verify', [
            'certificate' => $certificate,
            'certificateNumber' => $certificateNumber,
        ]);
    }
}