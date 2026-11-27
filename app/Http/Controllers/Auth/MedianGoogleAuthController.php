<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MedianGoogleAuthController
{
    public function __invoke(Request $request)
    {
        abort_unless($request->session()->get('median_app_mode'), 403);
        $audience = config('services.google.median_client_id');
        abort_unless(is_string($audience) && $audience !== '', 503);
        $data = $request->validate(['id_token' => 'required|string|max:8192']);

        try {
            $keys = Cache::remember('median_google_jwks', 300, fn () =>
                Http::timeout(5)->get('https://www.googleapis.com/oauth2/v3/certs')->throw()->json());
            $claims = JWT::decode($data['id_token'], JWK::parseKeySet($keys, 'RS256'));
        } catch (\Throwable $e) {
            Log::warning('Median Google token verification failed', ['exception' => get_class($e)]);
            return $this->reject('TOKEN_VERIFICATION', 401);
        }

        if (!in_array($claims->iss ?? '', ['accounts.google.com', 'https://accounts.google.com'], true)) {
            return $this->reject('TOKEN_ISSUER', 401);
        }
        if (($claims->aud ?? null) !== $audience) {
            return $this->reject('TOKEN_AUDIENCE', 401);
        }
        $authorizedClients = array_filter([$audience, config('services.google.median_android_client_id')]);
        if (isset($claims->azp) && !in_array($claims->azp, $authorizedClients, true)) {
            return $this->reject('TOKEN_AUTHORIZED_PARTY', 401);
        }
        if (!isset($claims->exp) || $claims->exp <= time()
            || ($claims->email_verified ?? false) !== true
            || !is_string($claims->sub ?? null) || $claims->sub === ''
            || !is_string($claims->email ?? null) || !filter_var($claims->email, FILTER_VALIDATE_EMAIL)) {
            return $this->reject('TOKEN_CLAIMS', 401);
        }

        $email = strtolower($claims->email);
        $user = User::where('google_id', $claims->sub)->first();
        if (!$user) {
            // Do not silently link an existing password account using only an email claim.
            // Existing Google-linked accounts are identified by Google's stable subject.
            if (User::whereRaw('LOWER(email) = ?', [$email])->exists()) {
                return $this->reject('ACCOUNT_LINK_REQUIRED', 409);
            }
            $user = User::create([
                'name' => Str::limit((string) ($claims->name ?? 'Mtumiaji wa Google'), 200, ''),
                'email' => $email,
                'google_id' => $claims->sub,
                'password' => Hash::make(Str::random(48)),
                'role' => 'student',
                'email_verified_at' => now(),
            ]);
        }
        if ($user->role !== 'student') {
            return $this->reject('ACCOUNT_ROLE', 403);
        }
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['ok' => true]);
    }

    private function reject(string $code, int $status)
    {
        Log::warning('Median Google login rejected', ['reason' => $code]);
        return response()->json(['code' => $code], $status);
    }
}
