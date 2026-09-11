<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.register', [
            'redirect' => $request->query('redirect'),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'redirect' => ['nullable', 'string'],
        ]);

        $phone = null;

        if ($request->filled('phone')) {
            $phone = preg_replace('/[^0-9]/', '', $request->phone);

            if (str_starts_with($phone, '0')) {
                $phone = '255' . substr($phone, 1);
            } elseif (strlen($phone) === 9) {
                $phone = '255' . $phone;
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'phone' => $phone,
            'password' => Hash::make($request->password),
            'role' => 'student',
        ]);

        event(new Registered($user));

        Auth::login($user);

        if ($request->filled('redirect')) {
            $redirect = $request->input('redirect');

            if (
                Str::startsWith($redirect, url('/')) ||
                Str::startsWith($redirect, '/')
            ) {
                return redirect()->to($redirect);
            }
        }

        return redirect()->route('student.dashboard');
    }
}