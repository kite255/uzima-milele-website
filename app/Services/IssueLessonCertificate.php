<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Lesson;
use App\Models\User;
use App\Notifications\CertificateIssuedNotification;
use Illuminate\Support\Str;

class IssueLessonCertificate
{
    public function issue(Lesson $lesson, User $student): Certificate
    {
        // The unique key on (user_id, lesson_id) also protects concurrent claims.
        return Certificate::firstOrCreate(
            ['user_id' => $student->id, 'lesson_id' => $lesson->id],
            ['certificate_number' => $this->generateCertificateNumber(), 'issued_at' => now()],
        );
    }

    public function notify(Certificate $certificate, User $student): void
    {
        $certificate->load(['lesson', 'user']);
        $student->notify(new CertificateIssuedNotification($certificate));
    }

    private function generateCertificateNumber(): string
    {
        do {
            $number = 'UZM-'.now()->format('Y').'-'.strtoupper(Str::random(8));
        } while (Certificate::where('certificate_number', $number)->exists());

        return $number;
    }
}
