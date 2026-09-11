<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Umesahau Nenosiri - Uzima Milele</title>

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

        .shell {
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 2;
        }

        .card {
            width: 100%;
            padding: 24px 30px 22px;
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
            margin-bottom: 8px;
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
            margin-bottom: 22px;
        }

        .heading h1 {
            margin: 0;
            color: var(--text);
            font-size: 25px;
            line-height: 1.15;
            font-weight: 900;
        }

        .heading p {
            margin: 7px auto 0;
            max-width: 390px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.55;
            font-weight: 500;
        }

        .status {
            margin-bottom: 14px;
            padding: 10px 12px;
            border-radius: 8px;
            background: #ECFDF5;
            border: 1px solid #BBF7D0;
            color: #047857;
            font-size: 11px;
            line-height: 1.5;
            font-weight: 700;
            text-align: center;
        }

        .field {
            margin-bottom: 14px;
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
            padding: 0 14px 0 42px;
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

        .error {
            margin-top: 6px;
            color: #DC2626;
            font-size: 11px;
            font-weight: 800;
        }

        .btn {
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
            font-size: 13px;
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
            margin: 14px 0 0;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
            font-weight: 500;
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
            margin-top: 10px;
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
                padding: 20px 17px 18px;
                border-radius: 14px;
            }

            .brand img {
                height: 70px;
            }

            .heading {
                margin-bottom: 20px;
            }

            .heading h1 {
                font-size: 23px;
            }

            .input-control,
            .btn {
                height: 46px;
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
                        Umesahau Nenosiri?
                    </h1>

                    <p>
                        Weka barua pepe yako, tutakutumia kiungo cha kubadili nenosiri.
                    </p>

                </div>

                @if(session('status'))
                    <div class="status">
                        {{ session('status') }}
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('password.email') }}"
                >
                    @csrf

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
                                <rect
                                    width="18"
                                    height="14"
                                    x="3"
                                    y="5"
                                    rx="2"
                                ></rect>

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

                    <button
                        type="submit"
                        class="btn"
                    >
                        Tuma Kiungo
                    </button>

                    <p class="login-text">
                        Unakumbuka nenosiri?

                        <a href="{{ route('login') }}">
                            Ingia
                        </a>
                    </p>

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

</body>
</html>