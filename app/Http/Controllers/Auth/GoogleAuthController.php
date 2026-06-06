<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        try {
            return Socialite::driver('google')
                ->stateless()
                ->scopes(['openid', 'profile', 'email'])
                ->with([
                    'prompt' => 'select_account',
                ])
                ->redirect();
        } catch (Throwable $e) {
            Log::error('Google redirect failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Imeshindikana kuanzisha Google login. Tafadhali jaribu tena.',
                ]);
        }
    }

    public function callback(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Secure fix for hosting/proxy losing Google OAuth query parameters
        |--------------------------------------------------------------------------
        | Only runs on /auth/google/callback.
        | Only restores expected OAuth parameters.
        | Does not log sensitive OAuth code values.
        */
        if (
            $request->is('auth/google/callback') &&
            ! $request->filled('code') &&
            isset($_SERVER['REQUEST_URI']) &&
            str_contains($_SERVER['REQUEST_URI'], '?')
        ) {
            $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

            if (! empty($queryString)) {
                parse_str($queryString, $rawQueryParams);

                $allowedKeys = [
                    'code',
                    'state',
                    'scope',
                    'authuser',
                    'prompt',
                    'error',
                    'error_description',
                ];

                $queryParams = array_intersect_key(
                    $rawQueryParams,
                    array_flip($allowedKeys)
                );

                if (! empty($queryParams)) {
                    $_SERVER['QUERY_STRING'] = http_build_query($queryParams);
                    $_GET = array_merge($_GET, $queryParams);
                    $_REQUEST = array_merge($_REQUEST, $queryParams);

                    $request->query->add($queryParams);
                }
            }
        }

        Log::info('Google callback received', [
            'has_code' => $request->filled('code'),
            'has_error' => $request->has('error'),
            'callback_path' => $request->path(),
            'query_keys' => array_keys($request->query()),
        ]);

        if ($request->has('error')) {
            Log::warning('Google login cancelled or denied', [
                'error' => $request->get('error'),
                'error_description' => $request->get('error_description'),
            ]);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Umeghairi au umekataa kuingia kwa kutumia Google. Tafadhali jaribu tena.',
                ]);
        }

        if (! $request->filled('code')) {
            Log::warning('Google login callback missing code', [
                'callback_path' => $request->path(),
                'query_keys' => array_keys($request->query()),
                'request_uri_has_query' => isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], '?'),
            ]);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Kuingia kwa kutumia Google hakukamilika. Tafadhali bonyeza tena kitufe cha Google au tumia dirisha jipya.',
                ]);
        }

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $email = $googleUser->getEmail();

            if (! $email) {
                Log::warning('Google did not return email', [
                    'google_id_present' => ! empty($googleUser->getId()),
                ]);

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Google haijarudisha barua pepe. Tafadhali tumia akaunti nyingine ya Google au ingia kwa barua pepe na nenosiri.',
                    ]);
            }

            $user = User::where('email', $email)->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            } else {
                $user = User::create([
                    'name' => $googleUser->getName()
                        ?: $googleUser->getNickname()
                        ?: 'Mtumiaji wa Google',
                    'email' => $email,
                    'password' => bcrypt(Str::random(32)),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                    'role' => 'student',
                ]);
            }

            Auth::login($user, true);

            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        } catch (Throwable $e) {
            Log::error('Google login failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'callback_path' => $request->path(),
                'query_keys' => array_keys($request->query()),
            ]);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Kuingia kwa kutumia Google kumeshindikana. Tafadhali jaribu tena au tumia barua pepe na nenosiri.',
                ]);
        }
    }
}