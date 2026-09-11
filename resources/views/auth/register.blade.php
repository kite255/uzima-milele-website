<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Jisajili - Uzima Milele</title>

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

        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
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

        .shell {
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 2;
        }

        .card {
            width: 100%;
            padding: 20px 30px 18px;
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
            height: 72px;
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
            margin-bottom: 16px;
        }

        .heading h1 {
            margin: 0;
            color: var(--text);
            font-size: 24px;
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

        .field {
            margin-bottom: 10px;
        }

        .field label {
            display: block;
            margin-bottom: 5px;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
        }

        .optional {
            color: #94A3B8;
            font-size: 10px;
            font-weight: 600;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 17px;
            height: 17px;
            color: #64748B;
            pointer-events: none;
        }

        .input-control {
            width: 100%;
            height: 43px;
            padding: 0 43px 0 41px;
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
            right: 6px;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
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
            width: 18px;
            height: 18px;
        }

        .error {
            margin-top: 5px;
            color: #DC2626;
            font-size: 10px;
            line-height: 1.35;
            font-weight: 800;
        }

        .btn {
            width: 100%;
            height: 44px;
            margin-top: 2px;
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

        .btn:hover {
            filter: brightness(0.96);
            transform: translateY(-1px);
        }

        .login-text {
            margin: 11px 0 0;
            color: var(--muted);
            font-size: 11px;
            font-weight: 500;
            text-align: center;
        }

        .login-text a {
            color: var(--primary-dark);
            font-weight: 800;
            text-decoration: none;
        }

        .login-text a:hover {
            text-decoration: underline;
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
            .page {
                align-items: flex-start;
                padding: 10px;
            }

            .shell {
                max-width: 100%;
            }

            .card {
                padding: 18px 17px 16px;
                border-radius: 14px;
            }

            .brand img {
                height: 64px;
            }

            .heading h1 {
                font-size: 22px;
            }

            .input-control {
                height: 42px;
            }

            .btn {
                height: 43px;
            }
        }

        @media (max-height: 720px) and (min-width: 541px) {
            .page {
                align-items: flex-start;
                padding-top: 8px;
                padding-bottom: 8px;
            }

            .card {
                padding-top: 15px;
                padding-bottom: 14px;
            }

            .brand img {
                height: 58px;
            }

            .heading {
                margin-bottom: 12px;
            }

            .field {
                margin-bottom: 7px;
            }

            .input-control {
                height: 39px;
            }

            .btn {
                height: 40px;
            }
        }
    </style>
</head>

<body>

    <main class="page">

        <div class="decoration decoration-one"></div>
        <div class="decoration decoration-two"></div>

        <div class="shell">

            <section class="card">

                {{-- LOGO --}}
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

                {{-- HEADING --}}
                <div class="heading">
                    <h1>Jisajili</h1>

                    <p>
                        Tengeneza akaunti ili uanze kujifunza Biblia.
                    </p>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    @if(request('redirect'))
                        <input
                            type="hidden"
                            name="redirect"
                            value="{{ request('redirect') }}"
                        >
                    @endif

                    {{-- NAME --}}
                    <div class="field">

                        <label for="name">
                            Jina Kamili
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
                                <path d="M20 21a8 8 0 0 0-16 0"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>

                            <input
                                id="name"
                                class="input-control"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="Weka jina lako kamili"
                            >

                        </div>

                        @error('name')
                            <div class="error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- EMAIL --}}
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

                    {{-- PHONE --}}
                    <div class="field">

                        <label for="phone">
                            Namba ya Simu
                            <span class="optional">(si lazima)</span>
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
                                <path d="M22 16.9v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 22 16.9z"></path>
                            </svg>

                            <input
                                id="phone"
                                class="input-control"
                                type="tel"
                                name="phone"
                                value="{{ old('phone') }}"
                                autocomplete="tel"
                                placeholder="0712345678"
                            >

                        </div>

                        @error('phone')
                            <div class="error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- PASSWORD --}}
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
                                autocomplete="new-password"
                                placeholder="Tengeneza nenosiri"
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                data-toggle-password="password"
                                aria-label="Onyesha au ficha nenosiri"
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
                                    <path d="M2.1 12a10.6 10.6 0 0 1 19.8 0"></path>
                                    <path d="M21.9 12a10.6 10.6 0 0 1-19.8 0"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>

                        </div>

                        @error('password')
                            <div class="error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- CONFIRM PASSWORD --}}
                    <div class="field">

                        <label for="password_confirmation">
                            Thibitisha Nenosiri
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
                                id="password_confirmation"
                                class="input-control"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Rudia nenosiri"
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                data-toggle-password="password_confirmation"
                                aria-label="Onyesha au ficha nenosiri"
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
                                    <path d="M2.1 12a10.6 10.6 0 0 1 19.8 0"></path>
                                    <path d="M21.9 12a10.6 10.6 0 0 1-19.8 0"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>

                        </div>

                        @error('password_confirmation')
                            <div class="error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- REGISTER --}}
                    <button
                        type="submit"
                        class="btn"
                    >
                        Jisajili
                    </button>

                    <p class="login-text">
                        Tayari una akaunti?

                        <a
                            href="{{ route('login', array_filter([
                                'redirect' => request('redirect'),
                            ])) }}"
                        >
                            Ingia
                        </a>
                    </p>

                </form>

            </section>

            {{-- BACK --}}
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
            const buttons = document.querySelectorAll(
                '[data-toggle-password]'
            );

            buttons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const targetId =
                        button.getAttribute('data-toggle-password');

                    const input =
                        document.getElementById(targetId);

                    if (! input) {
                        return;
                    }

                    input.type =
                        input.type === 'password'
                            ? 'text'
                            : 'password';
                });
            });
        });
    </script>

</body>
</html>