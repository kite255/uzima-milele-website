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
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request)
    {
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

        if (! $request->has('code')) {
            Log::warning('Google login callback missing code - restarting Google login', [
                'query' => $request->query(),
                'url' => $request->fullUrl(),
            ]);

            return redirect()->route('google.login');
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $email = $googleUser->getEmail();

            if (! $email) {
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
                    'role' => 'student',
                ]);
            }

            Auth::login($user, true);

            return redirect()->intended('/dashboard');
        } catch (Throwable $e) {
            Log::error('Google login failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Kuingia kwa kutumia Google kumeshindikana. Tafadhali jaribu tena au tumia barua pepe na nenosiri.',
                ]);
        }
    }
}