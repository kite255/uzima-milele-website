<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.login', [
            'redirect' => $request->query('redirect'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Safe redirect after login
        |--------------------------------------------------------------------------
        | Used when user is sent to login from lessons/enrollment pages.
        */
        if ($request->filled('redirect')) {
            $redirect = $request->input('redirect');

            if (
                Str::startsWith($redirect, url('/')) ||
                Str::startsWith($redirect, '/')
            ) {
                return redirect()->to($redirect);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Role-based redirect
        |--------------------------------------------------------------------------
        */
        if (in_array($user->role, ['admin', 'instructor'])) {
            return redirect('/admin');
        }

        return redirect()->route('student.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}