<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Uzima Milele</title>

    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --primary: #0083CB;
            --primary-dark: #076994;
            --navy: #0E3D4F;
            --accent: #F4B122;
            --border: #dbe3ea;
            --muted: #6b7280;
            --background: #f4f9fc;
        }

        html,
        body {
            margin: 0;
            width: 100%;
            min-height: 100%;
            font-family: 'Lato', sans-serif;
            background: var(--background);
            overflow-x: hidden;
        }

        .page {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px;
        }

        .card {
            width: 100%;
            max-width: 340px;
            background: #ffffff;
            border-radius: 20px;
            padding: 18px 22px;
            box-shadow: 0 16px 40px rgba(14, 61, 79, 0.12);
            border-top: 5px solid var(--primary);
        }

        .logo {
            display: block;
            width: auto;
            height: 42px;
            max-width: 140px;
            object-fit: contain;
            margin: 0 auto 8px;
        }

        h1 {
            margin: 0;
            text-align: center;
            color: var(--navy);
            font-size: 22px;
            font-weight: 900;
        }

        .subtitle {
            margin: 5px 0 14px;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
        }

        .status {
            margin-bottom: 12px;
            padding: 9px;
            border-radius: 10px;
            background: #ecfdf5;
            color: #047857;
            font-size: 12px;
            font-weight: 700;
        }

        .field {
            margin-bottom: 10px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: var(--navy);
            font-size: 12px;
            font-weight: 900;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            height: 40px;
            border: 1px solid var(--border);
            border-radius: 11px;
            padding: 0 13px;
            font-size: 13px;
            outline: none;
            color: #111827;
            background: #ffffff;
        }

        input::placeholder {
            color: #9ca3af;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0, 131, 203, 0.12);
        }

        .error {
            margin-top: 5px;
            color: #dc2626;
            font-size: 11px;
            font-weight: 700;
        }

        .row {
            margin: 0 0 13px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            margin: 0;
            cursor: pointer;
        }

        .remember input {
            width: 14px;
            height: 14px;
            accent-color: var(--primary);
        }

        .forgot {
            color: var(--primary);
            font-size: 12px;
            font-weight: 900;
            text-decoration: none;
            white-space: nowrap;
        }

        .forgot:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .btn {
            width: 100%;
            height: 40px;
            border: 0;
            border-radius: 11px;
            background: var(--primary);
            color: white;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .btn:hover {
            background: var(--primary-dark);
        }

        .register-text {
            margin: 12px 0 0;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .register-text a {
            color: var(--primary);
            font-weight: 900;
            text-decoration: none;
        }

        .register-text a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 13px 0 10px;
            color: #9ca3af;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
        }

        .divider::before,
        .divider::after {
            content: "";
            height: 1px;
            background: #e5e7eb;
            flex: 1;
        }

        .socials {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .social-btn {
            height: 38px;
            border: 1px solid var(--border);
            border-radius: 11px;
            background: #ffffff;
            color: var(--navy);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 900;
            transition: 0.2s ease;
        }

        .social-btn:hover {
            background: #f8fafc;
            border-color: var(--primary);
        }

        .social-btn svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
        }

        .back {
            display: block;
            margin-top: 12px;
            text-align: center;
            color: var(--navy);
            font-size: 12px;
            font-weight: 900;
            text-decoration: none;
        }

        .back:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        @media (max-width: 450px) {
            .page {
                align-items: center;
                padding: 12px;
            }

            .card {
                max-width: 100%;
                padding: 20px;
                border-radius: 18px;
            }

            .logo {
                height: 42px;
            }

            h1 {
                font-size: 22px;
            }

            .row {
                align-items: flex-start;
                flex-direction: column;
                gap: 7px;
            }

            .socials {
                grid-template-columns: 1fr;
            }
        }

        @media (max-height: 640px) {
            .page {
                align-items: center;
                padding-top: 8px;
                padding-bottom: 8px;
            }

            .card {
                padding-top: 16px;
                padding-bottom: 16px;
            }

            .logo {
                height: 36px;
                margin-bottom: 6px;
            }

            h1 {
                font-size: 20px;
            }

            .subtitle {
                margin-bottom: 10px;
            }

            .field {
                margin-bottom: 8px;
            }

            input[type="email"],
            input[type="password"],
            .btn,
            .social-btn {
                height: 36px;
            }

            .divider {
                margin: 10px 0 8px;
            }

            .back {
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>
    <main class="page">
        <section class="card">
            <img src="{{ asset('logo.png') }}" alt="Uzima Milele" class="logo">

            <h1>Login</h1>

            <p class="subtitle">
                Ingia kwenye akaunti yako
            </p>

            @if (session('status'))
                <div class="status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <input type="hidden" name="redirect" value="{{ request('redirect') }}">

                <div class="field">
                    <label for="email">Email Address</label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="you@example.com"
                    >

                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Password</label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    >

                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <label class="remember">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request', ['redirect' => request('redirect')]) }}" class="forgot">
                            Forgot?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn">
                    Log In
                </button>

                @if (Route::has('register'))
                    <p class="register-text">
                        Don’t have an account?
                        <a href="{{ route('register', ['redirect' => request('redirect')]) }}">
                            Register
                        </a>
                    </p>
                @endif

                @if (Route::has('google.login') || Route::has('facebook.login'))
                    <div class="divider">or</div>

                    <div class="socials">
                        @if (Route::has('google.login'))
                            <a href="{{ route('google.login', ['redirect' => request('redirect')]) }}" class="social-btn">
                                <svg viewBox="0 0 48 48" aria-hidden="true">
                                    <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.1 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/>
                                    <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.1 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>
                                    <path fill="#4CAF50" d="M24 44c5.1 0 9.8-2 13.3-5.2l-6.1-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44z"/>
                                    <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.3 4.2-4.1 5.6l6.1 5.2C36.9 39.1 44 34 44 24c0-1.3-.1-2.4-.4-3.5z"/>
                                </svg>
                                Google
                            </a>
                        @endif

                        @if (Route::has('facebook.login'))
                            <a href="{{ route('facebook.login', ['redirect' => request('redirect')]) }}" class="social-btn">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="#1877F2" d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.438H7.078v-3.49h3.047V9.413c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97H15.83c-1.49 0-1.955.93-1.955 1.884v2.267h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
                                </svg>
                                Facebook
                            </a>
                        @endif
                    </div>
                @endif
            </form>

            <a href="{{ url('/') }}" class="back">
                ← Back to Website
            </a>
        </section>
    </main>
</body>
</html>