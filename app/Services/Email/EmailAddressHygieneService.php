<?php

namespace App\Services\Email;

class EmailAddressHygieneService
{
    /**
     * Return a suggested correction for a small, explicit set of common
     * provider-domain typos. The original address is never modified.
     */
    public function suggestion(string $email): ?string
    {
        $normalized = strtolower(trim($email));

        if (! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        [$localPart, $domain] = explode('@', $normalized, 2);

        $domainSuggestion = match ($domain) {
            'gmal.com', 'gmial.com' => 'gmail.com',
            'yaho.com' => 'yahoo.com',
            default => null,
        };

        if ($domainSuggestion === null) {
            return null;
        }

        return $localPart.'@'.$domainSuggestion;
    }
}
