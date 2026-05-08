<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Umesahau Nenosiri - Uzima Milele</title>

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
            --border: #d7e2ea;
            --muted: #5f6f85;
            --background: #edf6fb;
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
            padding: 24px 16px;
        }

        .card {
            width: 100%;
            max-width: 425px;
            background: #ffffff;
            border-radius: 24px;
            padding: 34px 27px 28px;
            box-shadow: 0 18px 45px rgba(14, 61, 79, 0.08);
            border-top: 6px solid var(--primary);
        }

        .logo {
            display: block;
            height: 42px;
            width: auto;
            max-width: 110px;
            object-fit: contain;
            margin: 0 auto 20px;
        }

        h1 {
            margin: 0;
            text-align: center;
            color: var(--navy);
            font-size: 28px;
            line-height: 1.15;
            font-weight: 900;
        }

        .subtitle {
            margin: 13px 0 24px;
            text-align: center;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.5;
        }

        .status {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 14px;
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
            color: #047857;
            font-size: 14px;
            font-weight: 700;
            text-align: center;
        }

        .field {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--navy);
            font-size: 15px;
            font-weight: 900;
        }

        input[type="email"] {
            width: 100%;
            height: 51px;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 0 17px;
            font-size: 16px;
            outline: none;
            color: #111827;
            background: #ffffff;
        }

        input::placeholder {
            color: #94a3b8;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0, 131, 203, 0.15);
        }

        .error {
            margin-top: 7px;
            color: #dc2626;
            font-size: 13px;
            font-weight: 700;
        }

        .btn {
            width: 100%;
            height: 50px;
            border: 0;
            border-radius: 13px;
            background: var(--primary);
            color: white;
            font-size: 17px;
            font-weight: 900;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .btn:hover {
            background: var(--primary-dark);
        }

        .login-text {
            margin: 20px 0 0;
            text-align: center;
            color: var(--muted);
            font-size: 14px;
            font-weight: 700;
        }

        .login-text a {
            color: var(--primary);
            font-weight: 900;
            text-decoration: none;
        }

        .login-text a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .back {
            display: block;
            margin-top: 22px;
            text-align: center;
            color: var(--navy);
            font-size: 15px;
            font-weight: 900;
            text-decoration: none;
        }

        .back:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        @media (max-width: 520px) {
            .page {
                padding: 24px 17px;
            }

            .card {
                max-width: 100%;
                border-radius: 22px;
                padding: 32px 27px 28px;
            }

            h1 {
                font-size: 27px;
            }

            .subtitle {
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <main class="page">
        <section class="card">
            <img src="{{ asset('logo.png') }}" alt="Uzima Milele" class="logo">

            <h1>Umesahau Nenosiri?</h1>

            <p class="subtitle">
                Weka barua pepe yako, tutakutumia kiungo cha kubadili nenosiri.
            </p>

            @if (session('status'))
                <div class="status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="field">
                    <label for="email">Barua Pepe</label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="mfano: jina@email.com"
                    >

                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn">
                    Tuma Kiungo
                </button>

                <p class="login-text">
                    Unakumbuka nenosiri?
                    <a href="{{ route('login') }}">
                        Ingia
                    </a>
                </p>
            </form>

            <a href="{{ url('/') }}" class="back">
                ← Rudi kwenye tovuti
            </a>
        </section>
    </main>
</body>
</html>