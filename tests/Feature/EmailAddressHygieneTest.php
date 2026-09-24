<?php

namespace Tests\Feature;

use App\Services\Email\EmailAddressHygieneService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EmailAddressHygieneTest extends TestCase
{
    #[DataProvider('typoProvider')]
    public function test_common_domain_typos_return_suggestion_without_mutating_original(
        string $email,
        string $expected
    ): void {
        $service = app(EmailAddressHygieneService::class);
        $original = $email;

        $suggestion = $service->suggestion($email);

        $this->assertSame($expected, $suggestion);
        $this->assertSame($original, $email);
    }

    public function test_valid_known_domain_has_no_suggestion(): void
    {
        $this->assertNull(
            app(EmailAddressHygieneService::class)
                ->suggestion('person@gmail.com')
        );
    }

    public function test_invalid_email_has_no_automatic_correction(): void
    {
        $this->assertNull(
            app(EmailAddressHygieneService::class)
                ->suggestion('not-an-email')
        );
    }

    public static function typoProvider(): array
    {
        return [
            'gmal.com' => [
                'person@gmal.com',
                'person@gmail.com',
            ],
            'gmial.com' => [
                'person@gmial.com',
                'person@gmail.com',
            ],
            'yaho.com' => [
                'person@yaho.com',
                'person@yahoo.com',
            ],
        ];
    }
}
