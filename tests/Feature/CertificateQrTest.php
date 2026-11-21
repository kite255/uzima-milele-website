<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Lesson;
use App\Models\User;
use Tests\TestCase;

class CertificateQrTest extends TestCase
{
    public function test_certificate_embeds_a_local_verification_qr_code_without_remote_images(): void
    {
        $certificate = new Certificate([
            'certificate_number' => 'UZM-2026-EXAMPLE1',
            'issued_at' => now(),
        ]);
        $certificate->setRelation('user', new User(['name' => 'Test Learner']));
        $certificate->setRelation('lesson', new Lesson(['title' => 'Biblia']));

        $html = view('certificates.pdf', [
            'certificate' => $certificate,
        ])->render();

        $verificationUrl = route('certificates.verify', $certificate->certificate_number);
        $this->assertSame(1, preg_match(
            '/<a href="'.preg_quote($verificationUrl, '/').'">\s*<img src="data:image\/svg\+xml;base64,[A-Za-z0-9+\/=]+"/',
            $html,
        ), 'The certificate should embed a QR image linked to its verification page.');
    }
}
