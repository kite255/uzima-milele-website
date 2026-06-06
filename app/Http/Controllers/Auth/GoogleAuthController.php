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
        Log::info('Google callback received', [
            'full_url' => $request->fullUrl(),
            'query' => $request->query(),
            'has_code' => $request->has('code'),
            'has_error' => $request->has('error'),
        ]);

        if ($request->has('error')) {
            Log::warning('Google login cancelled or denied', [
                'error' => $request->get('error'),
                'error_description' => $request->get('error_description'),
                'full_url' => $request->fullUrl(),
            ]);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Umeghairi au umekataa kuingia kwa kutumia Google. Tafadhali jaribu tena.',
                ]);
        }

        if (! $request->filled('code')) {
            Log::warning('Google login callback missing code', [
                'query' => $request->query(),
                'full_url' => $request->fullUrl(),
                'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
                'query_string' => $_SERVER['QUERY_STRING'] ?? null,
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
                    'google_id' => $googleUser->getId(),
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
                'full_url' => $request->fullUrl(),
                'query' => $request->query(),
            ]);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Kuingia kwa kutumia Google kumeshindikana. Tafadhali jaribu tena au tumia barua pepe na nenosiri.',
                ]);
        }
    }
}