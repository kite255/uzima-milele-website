<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle(Request $request): RedirectResponse
    {
        $this->storeRedirectUrl($request);

        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $email = $socialUser->getEmail();

            if (! $email) {
                Log::warning('Google login failed because email was missing.', [
                    'google_id' => $socialUser->getId(),
                    'name' => $socialUser->getName(),
                ]);

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Akaunti yako ya Google haina barua pepe. Tafadhali tumia email na nenosiri.',
                    ]);
            }

            $user = User::where('email', strtolower($email))->first();

            if ($user) {
                $user->update([
                    'name' => $user->name ?: ($socialUser->getName() ?? $socialUser->getNickname() ?? 'Google User'),
                    'google_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ]);
            } else {
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Google User',
                    'email' => strtolower($email),
                    'google_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'student',
                    'email_verified_at' => now(),
                ]);
            }

            Auth::login($user, true);

            return $this->redirectAfterSocialLogin($user);
        } catch (\Throwable $e) {
            Log::error('Google login failed.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'google_config' => [
                    'client_id_exists' => filled(config('services.google.client_id')),
                    'client_secret_exists' => filled(config('services.google.client_secret')),
                    'redirect' => config('services.google.redirect'),
                ],
            ]);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Google login imeshindikana. Tafadhali jaribu tena.',
                ]);
        }
    }

    public function redirectToFacebook(Request $request): RedirectResponse
    {
        $this->storeRedirectUrl($request);

        return Socialite::driver('facebook')
            ->stateless()
            ->redirect();
    }

    public function handleFacebookCallback(): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver('facebook')
                ->stateless()
                ->user();

            $email = $socialUser->getEmail();

            if (! $email) {
                Log::warning('Facebook login failed because email was missing.', [
                    'facebook_id' => $socialUser->getId(),
                    'name' => $socialUser->getName(),
                ]);

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Akaunti yako ya Facebook haina barua pepe. Tafadhali tumia email na nenosiri.',
                    ]);
            }

            $user = User::where('email', strtolower($email))->first();

            if ($user) {
                $user->update([
                    'name' => $user->name ?: ($socialUser->getName() ?? $socialUser->getNickname() ?? 'Facebook User'),
                    'facebook_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ]);
            } else {
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Facebook User',
                    'email' => strtolower($email),
                    'facebook_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'student',
                    'email_verified_at' => now(),
                ]);
            }

            Auth::login($user, true);

            return $this->redirectAfterSocialLogin($user);
        } catch (\Throwable $e) {
            Log::error('Facebook login failed.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'facebook_config' => [
                    'client_id_exists' => filled(config('services.facebook.client_id')),
                    'client_secret_exists' => filled(config('services.facebook.client_secret')),
                    'redirect' => config('services.facebook.redirect'),
                ],
            ]);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Facebook login imeshindikana. Tafadhali jaribu tena.',
                ]);
        }
    }

    private function storeRedirectUrl(Request $request): void
    {
        if (! $request->filled('redirect')) {
            return;
        }

        $redirect = $request->query('redirect');

        if ($this->isSafeRedirect($redirect)) {
            session(['social_auth_redirect' => $redirect]);
        }
    }

    private function redirectAfterSocialLogin(User $user): RedirectResponse
    {
        $redirect = session()->pull('social_auth_redirect');

        if ($redirect && $this->isSafeRedirect($redirect)) {
            return redirect()->to($redirect);
        }

        if ($user->role === 'admin') {
            return redirect('/admin');
        }

        return redirect()->route('student.dashboard');
    }

    private function isSafeRedirect(?string $redirect): bool
    {
        if (! $redirect) {
            return false;
        }

        if (Str::startsWith($redirect, '//')) {
            return false;
        }

        return Str::startsWith($redirect, url('/')) ||
            Str::startsWith($redirect, '/');
    }
}