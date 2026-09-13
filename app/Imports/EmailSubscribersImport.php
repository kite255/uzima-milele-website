<?php

namespace App\Imports;

use App\Models\EmailSubscriber;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class EmailSubscribersImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $imported = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public int $failed = 0;

    public function __construct(
        protected bool $updateExisting = false,
        protected string $defaultStatus = 'subscribed',
    ) {
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            try {
                /*
                |--------------------------------------------------------------------------
                | EMAIL
                |--------------------------------------------------------------------------
                */

                $email = Str::lower(
                    trim((string) ($row['email'] ?? ''))
                );

                if (
                    blank($email) ||
                    ! filter_var($email, FILTER_VALIDATE_EMAIL)
                ) {
                    $this->failed++;

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | NAME
                |--------------------------------------------------------------------------
                */

                $fullName = $this->resolveName($row);

                /*
                |--------------------------------------------------------------------------
                | PHONE
                |--------------------------------------------------------------------------
                |
                | Supported:
                |
                | 0768461644
                | 768461644
                | 255768461644
                | +255768461644
                |
                | Stored:
                |
                | +255768461644
                |
                */

                $phone = $this->normalizePhone(
                    filled($row['phone'] ?? null)
                        ? (string) $row['phone']
                        : null
                );

                /*
                |--------------------------------------------------------------------------
                | CHECK EXISTING SUBSCRIBER
                |--------------------------------------------------------------------------
                */

                $existing = EmailSubscriber::query()
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [$email]
                    )
                    ->first();

                /*
                |--------------------------------------------------------------------------
                | UPDATE EXISTING SUBSCRIBER
                |--------------------------------------------------------------------------
                */

                if ($existing) {
                    if (! $this->updateExisting) {
                        $this->skipped++;

                        continue;
                    }

                    $nameForSplit = filled($fullName)
                        ? $fullName
                        : ($existing->name ?: $email);

                    [$firstName, $lastName] = $this->splitName(
                        $nameForSplit
                    );

                    $data = [
                        'email' => $email,
                        'source' => $existing->source ?: 'import',
                    ];

                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE NAME ONLY IF PROVIDED
                    |--------------------------------------------------------------------------
                    */

                    if (filled($fullName)) {
                        $data['name'] = $fullName;
                        $data['first_name'] = $firstName;
                        $data['last_name'] = $lastName;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE PHONE ONLY IF PROVIDED
                    |--------------------------------------------------------------------------
                    */

                    if (filled($phone)) {
                        $data['phone'] = $phone;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS
                    |--------------------------------------------------------------------------
                    */

                    if ($this->defaultStatus === 'subscribed') {
                        $data['status'] = 'subscribed';

                        $data['subscribed_at'] =
                            $existing->subscribed_at ?? now();

                        $data['unsubscribed_at'] = null;
                    } else {
                        $data['status'] = 'unsubscribed';

                        $data['unsubscribed_at'] =
                            $existing->unsubscribed_at ?? now();
                    }

                    $existing->update($data);

                    $this->updated++;

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | CREATE NEW SUBSCRIBER
                |--------------------------------------------------------------------------
                */

                if (blank($fullName)) {
                    $fullName = $email;
                }

                [$firstName, $lastName] = $this->splitName(
                    $fullName
                );

                EmailSubscriber::create([
                    'name' => $fullName,
                    'first_name' => $firstName,
                    'last_name' => $lastName,

                    'email' => $email,
                    'phone' => $phone,

                    'status' => $this->defaultStatus,

                    'source' => 'import',

                    'language' => 'sw',

                    'subscribed_at' =>
                        $this->defaultStatus === 'subscribed'
                            ? now()
                            : null,

                    'unsubscribed_at' =>
                        $this->defaultStatus === 'unsubscribed'
                            ? now()
                            : null,
                ]);

                $this->imported++;
            } catch (Throwable $exception) {
                report($exception);

                $this->failed++;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RESOLVE NAME
    |--------------------------------------------------------------------------
    |
    | Supported columns:
    |
    | name
    | full_name
    | jina
    |
    | Or:
    |
    | first_name + last_name
    |
    */

    protected function resolveName(Collection $row): ?string
    {
        $name =
            $row['name']
            ?? $row['full_name']
            ?? $row['jina']
            ?? null;

        if (filled($name)) {
            return $this->cleanName(
                (string) $name
            );
        }

        $firstName = trim(
            (string) (
                $row['first_name']
                ?? $row['firstname']
                ?? ''
            )
        );

        $lastName = trim(
            (string) (
                $row['last_name']
                ?? $row['lastname']
                ?? ''
            )
        );

        $combined = trim(
            $firstName . ' ' . $lastName
        );

        return filled($combined)
            ? $this->cleanName($combined)
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZE TANZANIA PHONE NUMBER
    |--------------------------------------------------------------------------
    |
    | Examples:
    |
    | 0768461644     -> +255768461644
    | 768461644      -> +255768461644
    | 255768461644   -> +255768461644
    | +255768461644  -> +255768461644
    |
    */

    protected function normalizePhone(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | CLEAN VALUE
        |--------------------------------------------------------------------------
        |
        | Removes spaces, hyphens, brackets etc.
        |
        | +255 768 461 644
        | becomes
        | +255768461644
        |
        */

        $phone = trim($phone);

        $phone = preg_replace(
            '/[^0-9+]/',
            '',
            $phone
        );

        if (blank($phone)) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | ALREADY INTERNATIONAL
        |--------------------------------------------------------------------------
        */

        if (str_starts_with($phone, '+255')) {
            return $this->isValidTanzaniaPhone($phone)
                ? $phone
                : $phone;
        }

        /*
        |--------------------------------------------------------------------------
        | INTERNATIONAL WITHOUT +
        |--------------------------------------------------------------------------
        */

        if (str_starts_with($phone, '255')) {
            $normalized = '+' . $phone;

            return $this->isValidTanzaniaPhone($normalized)
                ? $normalized
                : $normalized;
        }

        /*
        |--------------------------------------------------------------------------
        | LOCAL FORMAT WITH LEADING ZERO
        |--------------------------------------------------------------------------
        |
        | 0768461644 -> +255768461644
        |
        */

        if (
            strlen($phone) === 10 &&
            str_starts_with($phone, '0')
        ) {
            $normalized =
                '+255' . substr($phone, 1);

            return $normalized;
        }

        /*
        |--------------------------------------------------------------------------
        | EXCEL MAY REMOVE THE LEADING ZERO
        |--------------------------------------------------------------------------
        |
        | Excel may convert:
        |
        | 0768461644
        |
        | into:
        |
        | 768461644
        |
        */

        if (
            strlen($phone) === 9 &&
            preg_match('/^[67]/', $phone)
        ) {
            return '+255' . $phone;
        }

        /*
        |--------------------------------------------------------------------------
        | UNKNOWN FORMAT
        |--------------------------------------------------------------------------
        |
        | Keep it instead of silently deleting the data.
        |
        */

        return $phone;
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATE TANZANIA PHONE
    |--------------------------------------------------------------------------
    */

    protected function isValidTanzaniaPhone(string $phone): bool
    {
        return (bool) preg_match(
            '/^\+255[67][0-9]{8}$/',
            $phone
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CLEAN NAME
    |--------------------------------------------------------------------------
    */

    protected function cleanName(string $name): string
    {
        return trim(
            preg_replace(
                '/\s+/',
                ' ',
                $name
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SPLIT NAME
    |--------------------------------------------------------------------------
    */

    protected function splitName(string $name): array
    {
        $name = $this->cleanName($name);

        $parts = preg_split(
            '/\s+/',
            $name,
            2
        );

        return [
            $parts[0] ?? $name,
            $parts[1] ?? null,
        ];
    }
}