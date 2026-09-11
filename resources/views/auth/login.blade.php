<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Ingia - Uzima Milele</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Lato:wght@400;500;600;700;900&display=swap"
        rel="stylesheet"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --primary: #0083CB;
            --primary-dark: #076994;
            --navy: #0E3D4F;
            --navy-dark: #082E3D;
            --accent: #F4B122;
            --text: #263244;
            --muted: #64748B;
            --border: #CBD5E1;
            --white: #FFFFFF;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            font-family: 'Lato', sans-serif;
            background:
                radial-gradient(
                    circle at 95% 5%,
                    rgba(0, 131, 203, 0.18),
                    transparent 22%
                ),
                radial-gradient(
                    circle at 5% 95%,
                    rgba(244, 177, 34, 0.08),
                    transparent 22%
                ),
                linear-gradient(
                    145deg,
                    var(--navy-dark) 0%,
                    var(--navy) 52%,
                    #15576F 100%
                );
        }

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px;
            position: relative;
            overflow: hidden;
        }

        .decoration {
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
        }

        .decoration-one {
            width: 260px;
            height: 260px;
            top: -120px;
            right: -80px;
            background: rgba(0, 131, 203, 0.11);
        }

        .decoration-two {
            width: 190px;
            height: 190px;
            bottom: -95px;
            left: -70px;
            background: rgba(244, 177, 34, 0.07);
        }

        .login-shell {
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 2;
        }

        .login-card {
            width: 100%;
            padding: 22px 30px 20px;
            border-radius: 16px;
            background: var(--white);
            border: 1px solid rgba(203, 213, 225, 0.85);
            box-shadow:
                0 18px 45px rgba(0, 0, 0, 0.16),
                0 5px 16px rgba(0, 0, 0, 0.05);
        }

        .brand {
            text-align: center;
        }

        .brand img {
            display: block;
            width: auto;
            height: 78px;
            margin: 0 auto 3px;
            object-fit: contain;
        }

        .brand-accent {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-bottom: 6px;
        }

        .brand-accent span {
            width: 10px;
            height: 4px;
            border-radius: 999px;
        }

        .brand-accent span:nth-child(1) {
            background: #22C55E;
        }

        .brand-accent span:nth-child(2) {
            background: var(--accent);
        }

        .brand-accent span:nth-child(3) {
            background: var(--primary);
        }

        .heading {
            text-align: center;
            margin-bottom: 20px;
        }

        .heading h1 {
            margin: 0;
            color: var(--text);
            font-size: 25px;
            line-height: 1.15;
            font-weight: 900;
        }

        .heading p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
            font-weight: 500;
        }

        .status {
            margin-bottom: 12px;
            padding: 9px 11px;
            border-radius: 8px;
            background: #ECFDF5;
            color: #047857;
            font-size: 11px;
            font-weight: 800;
        }

        .field {
            margin-bottom: 12px;
        }

        .field label {
            display: block;
            margin-bottom: 6px;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #64748B;
            pointer-events: none;
        }

        .input-control {
            width: 100%;
            height: 48px;
            padding: 0 46px 0 42px;
            border: 1px solid var(--border);
            border-radius: 4px;
            background: #FFFFFF;
            color: var(--text);
            font-family: inherit;
            font-size: 13px;
            font-weight: 500;
            outline: none;
            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }

        .input-control::placeholder {
            color: #94A3B8;
        }

        .input-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 131, 203, 0.10);
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 7px;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            background: transparent;
            color: #64748B;
            cursor: pointer;
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        .password-toggle svg {
            width: 19px;
            height: 19px;
        }

        .error {
            margin-top: 5px;
            color: #DC2626;
            font-size: 11px;
            font-weight: 800;
        }

        .options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 0 0 12px;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .remember input {
            width: 14px;
            height: 14px;
            margin: 0;
            accent-color: var(--primary);
        }

        .forgot {
            color: var(--primary-dark);
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
        }

        .forgot:hover {
            text-decoration: underline;
        }

        .submit-btn {
            width: 100%;
            height: 48px;
            border: 0;
            border-radius: 4px;
            background:
                linear-gradient(
                    135deg,
                    var(--primary) 0%,
                    var(--primary-dark) 100%
                );
            color: var(--white);
            font-family: inherit;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            transition:
                filter 0.2s ease,
                transform 0.2s ease;
        }

        .submit-btn:hover {
            filter: brightness(0.96);
            transform: translateY(-1px);
        }

        .register-text {
            margin: 12px 0 0;
            color: var(--muted);
            font-size: 12px;
            font-weight: 500;
            text-align: center;
        }

        .register-text a {
            color: var(--primary-dark);
            font-weight: 800;
            text-decoration: none;
        }

        .register-text a:hover {
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 13px 0 10px;
            color: #94A3B8;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #E2E8F0;
        }

        .google-btn {
            width: 100%;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid var(--border);
            border-radius: 4px;
            background: #FFFFFF;
            color: #334155;
            font-size: 11px;
            font-weight: 800;
            text-decoration: none;
        }

        .google-btn:hover {
            border-color: var(--primary);
            background: #F8FAFC;
        }

        .google-btn svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
        }

        .back-wrap {
            text-align: center;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 8px;
            color: rgba(255, 255, 255, 0.95);
            font-size: 10px;
            font-weight: 900;
            text-decoration: none;
        }

        .back-link:hover {
            color: var(--accent);
        }

        .back-link svg {
            width: 14px;
            height: 14px;
        }

        @media (max-width: 540px) {
            .login-page {
                align-items: flex-start;
                padding: 10px;
            }

            .login-shell {
                max-width: 100%;
            }

            .login-card {
                padding: 20px 17px 18px;
                border-radius: 14px;
            }

            .brand img {
                height: 70px;
            }

            .heading {
                margin-bottom: 18px;
            }

            .heading h1 {
                font-size: 23px;
            }

            .input-control,
            .submit-btn {
                height: 46px;
            }

            .google-btn {
                height: 44px;
            }
        }

        @media (max-width: 390px) {
            .options-row {
                align-items: flex-start;
                flex-direction: column;
                gap: 7px;
            }
        }
    </style>
</head>

<body>

    @php
        $redirect = request('redirect');

        $googleLoginUrl = Route::has('google.login')
            ? (
                $redirect
                    ? route('google.login', ['redirect' => $redirect])
                    : route('google.login')
            )
            : null;

        $registerUrl = Route::has('register')
            ? (
                $redirect
                    ? route('register', ['redirect' => $redirect])
                    : route('register')
            )
            : null;

        $forgotPasswordUrl = Route::has('password.request')
            ? (
                $redirect
                    ? route('password.request', ['redirect' => $redirect])
                    : route('password.request')
            )
            : null;
    @endphp

    <main class="login-page">

        <div class="decoration decoration-one"></div>
        <div class="decoration decoration-two"></div>

        <div class="login-shell">

            <section class="login-card">

                <div class="brand">

                    <img
                        src="{{ asset('logo.png') }}"
                        alt="Uzima Milele"
                    >

                    <div class="brand-accent" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                </div>

                <div class="heading">

                    <h1>
                        Karibu Tena
                    </h1>

                    <p>
                        Ingia kwenye akaunti yako kuendelea.
                    </p>

                </div>

                @if(session('status'))
                    <div class="status">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    @if($redirect)
                        <input
                            type="hidden"
                            name="redirect"
                            value="{{ $redirect }}"
                        >
                    @endif

                    <div class="field">

                        <label for="email">
                            Barua pepe
                        </label>

                        <div class="input-wrap">

                            <svg
                                class="input-icon"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <rect width="18" height="14" x="3" y="5" rx="2"></rect>
                                <path d="m3 7 9 6 9-6"></path>
                            </svg>

                            <input
                                id="email"
                                class="input-control"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="Weka barua pepe yako"
                            >

                        </div>

                        @error('email')
                            <div class="error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="field">

                        <label for="password">
                            Nenosiri
                        </label>

                        <div class="input-wrap">

                            <svg
                                class="input-icon"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <rect width="16" height="12" x="4" y="10" rx="2"></rect>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                            </svg>

                            <input
                                id="password"
                                class="input-control"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Weka nenosiri lako"
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                id="togglePassword"
                                aria-label="Onyesha au ficha nenosiri"
                            >
                                <svg
                                    id="eyeOpen"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M2.1 12a10.6 10.6 0 0 1 19.8 0"></path>
                                    <path d="M21.9 12a10.6 10.6 0 0 1-19.8 0"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>

                                <svg
                                    id="eyeClosed"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                    style="display: none;"
                                >
                                    <path d="m2 2 20 20"></path>
                                    <path d="M6.7 6.7A10.8 10.8 0 0 0 2.1 12a10.6 10.6 0 0 0 17.2 4.9"></path>
                                    <path d="M10.7 10.7a3 3 0 0 0 4.1 4.1"></path>
                                    <path d="M9.9 4.2A10.9 10.9 0 0 1 12 4c4.7 0 8.5 3.2 9.9 8a10.8 10.8 0 0 1-2.1 3.8"></path>
                                </svg>
                            </button>

                        </div>

                        @error('password')
                            <div class="error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="options-row">

                        <label class="remember">

                            <input
                                type="checkbox"
                                name="remember"
                                {{ old('remember') ? 'checked' : '' }}
                            >

                            <span>
                                Nikumbuke
                            </span>

                        </label>

                        @if($forgotPasswordUrl)
                            <a
                                href="{{ $forgotPasswordUrl }}"
                                class="forgot"
                            >
                                Umesahau nenosiri?
                            </a>
                        @endif

                    </div>

                    <button
                        type="submit"
                        class="submit-btn"
                    >
                        Ingia
                    </button>

                    @if($registerUrl)

                        <p class="register-text">
                            Huna akaunti?

                            <a href="{{ $registerUrl }}">
                                Jisajili
                            </a>
                        </p>

                    @endif

                    @if($googleLoginUrl)

                        <div class="divider">
                            au
                        </div>

                        <a
                            href="{{ $googleLoginUrl }}"
                            class="google-btn"
                        >
                            <svg viewBox="0 0 48 48" aria-hidden="true">
                                <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.1 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/>
                                <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.1 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>
                                <path fill="#4CAF50" d="M24 44c5.1 0 9.8-2 13.3-5.2l-6.1-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44z"/>
                                <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.3 4.2-4.1 5.6l6.1 5.2C36.9 39.1 44 34 44 24c0-1.3-.1-2.4-.4-3.5z"/>
                            </svg>

                            Endelea kwa kutumia Google
                        </a>

                    @endif

                </form>

            </section>

            <div class="back-wrap">

                <a
                    href="{{ url('/') }}"
                    class="back-link"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="m12 19-7-7 7-7"></path>
                        <path d="M19 12H5"></path>
                    </svg>

                    Rudi kwenye Tovuti
                </a>

            </div>

        </div>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const eyeOpen = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');

            if (
                passwordInput &&
                togglePassword &&
                eyeOpen &&
                eyeClosed
            ) {
                togglePassword.addEventListener('click', function () {
                    const isVisible = passwordInput.type === 'text';

                    passwordInput.type = isVisible
                        ? 'password'
                        : 'text';

                    eyeOpen.style.display = isVisible
                        ? 'block'
                        : 'none';

                    eyeClosed.style.display = isVisible
                        ? 'none'
                        : 'block';
                });
            }
        });
    </script>

</body>
</html>