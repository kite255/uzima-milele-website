<?php

namespace App\Http\Controllers;

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
            ->with([
                'prompt' => 'select_account',
            ])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            if ($request->filled('error')) {
                Log::warning('Google login returned OAuth error.', [
                    'error' => $request->query('error'),
                    'error_description' => $request->query('error_description'),
                    'full_url' => $request->fullUrl(),
                ]);

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Google login haijakamilika. Tafadhali jaribu tena.',
                    ]);
            }

            if (! $request->filled('code')) {
                Log::warning('Google callback reached without authorization code.', [
                    'query' => $request->query(),
                    'full_url' => $request->fullUrl(),
                    'google_config' => $this->googleConfigStatus(),
                ]);

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Google login haijakamilika. Tafadhali bonyeza Google tena.',
                    ]);
            }

            $socialUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $email = strtolower((string) $socialUser->getEmail());

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

            $user = $this->findOrCreateSocialUser(
                provider: 'google',
                providerId: (string) $socialUser->getId(),
                name: $socialUser->getName() ?: $socialUser->getNickname() ?: 'Google User',
                email: $email,
                avatar: $socialUser->getAvatar(),
            );

            Auth::login($user, true);
            $request->session()->regenerate();

            Log::info('Google login successful.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            return $this->redirectAfterSocialLogin($user);
        } catch (\Throwable $e) {
            Log::error('Google login failed.', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'query' => $request->query(),
                'full_url' => $request->fullUrl(),
                'google_config' => $this->googleConfigStatus(),
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

    public function handleFacebookCallback(Request $request): RedirectResponse
    {
        try {
            if ($request->filled('error')) {
                Log::warning('Facebook login returned OAuth error.', [
                    'error' => $request->query('error'),
                    'error_description' => $request->query('error_description'),
                    'full_url' => $request->fullUrl(),
                ]);

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Facebook login haijakamilika. Tafadhali jaribu tena.',
                    ]);
            }

            if (! $request->filled('code')) {
                Log::warning('Facebook callback reached without authorization code.', [
                    'query' => $request->query(),
                    'full_url' => $request->fullUrl(),
                    'facebook_config' => $this->facebookConfigStatus(),
                ]);

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Facebook login haijakamilika. Tafadhali bonyeza Facebook tena.',
                    ]);
            }

            $socialUser = Socialite::driver('facebook')
                ->stateless()
                ->user();

            $email = strtolower((string) $socialUser->getEmail());

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

            $user = $this->findOrCreateSocialUser(
                provider: 'facebook',
                providerId: (string) $socialUser->getId(),
                name: $socialUser->getName() ?: $socialUser->getNickname() ?: 'Facebook User',
                email: $email,
                avatar: $socialUser->getAvatar(),
            );

            Auth::login($user, true);
            $request->session()->regenerate();

            Log::info('Facebook login successful.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            return $this->redirectAfterSocialLogin($user);
        } catch (\Throwable $e) {
            Log::error('Facebook login failed.', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'query' => $request->query(),
                'full_url' => $request->fullUrl(),
                'facebook_config' => $this->facebookConfigStatus(),
            ]);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Facebook login imeshindikana. Tafadhali jaribu tena.',
                ]);
        }
    }

    private function findOrCreateSocialUser(
        string $provider,
        string $providerId,
        string $name,
        string $email,
        ?string $avatar = null
    ): User {
        $providerColumn = $provider.'_id';

        $user = User::query()
            ->where($providerColumn, $providerId)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $user->forceFill([
                'name' => $user->name ?: $name,
                'email' => $user->email ?: $email,
                $providerColumn => $providerId,
                'avatar' => $avatar ?: $user->avatar,
                'email_verified_at' => $user->email_verified_at ?: now(),
            ])->save();

            return $user;
        }

        return User::create([
            'name' => $name,
            'email' => $email,
            $providerColumn => $providerId,
            'avatar' => $avatar,
            'password' => Hash::make(Str::random(40)),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);
    }

    private function storeRedirectUrl(Request $request): void
    {
        if (! $request->filled('redirect')) {
            return;
        }

        $redirect = (string) $request->query('redirect');

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

        if ($user->role === 'instructor') {
            return redirect()->route('instructor.dashboard');
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

    private function googleConfigStatus(): array
    {
        return [
            'client_id_exists' => filled(config('services.google.client_id')),
            'client_secret_exists' => filled(config('services.google.client_secret')),
            'redirect' => config('services.google.redirect'),
        ];
    }

    private function facebookConfigStatus(): array
    {
        return [
            'client_id_exists' => filled(config('services.facebook.client_id')),
            'client_secret_exists' => filled(config('services.facebook.client_secret')),
            'redirect' => config('services.facebook.redirect'),
        ];
    }
}