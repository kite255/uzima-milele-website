<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Thibitisha Barua Pepe - Uzima Milele</title>

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
            margin: 7px auto 0;
            max-width: 400px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.6;
            font-weight: 500;
        }

        .status {
            margin-bottom: 16px;
            padding: 11px 13px;
            border-radius: 8px;
            background: #ECFDF5;
            border: 1px solid #BBF7D0;
            color: #047857;
            font-size: 11px;
            line-height: 1.5;
            font-weight: 700;
            text-align: center;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .resend-form,
        .logout-form {
            width: 100%;
        }

        .resend-btn,
        .logout-btn {
            width: 100%;
            height: 48px;
            border-radius: 4px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
            transition:
                filter 0.2s ease,
                transform 0.2s ease,
                border-color 0.2s ease,
                background 0.2s ease;
        }

        .resend-btn {
            border: 0;
            background:
                linear-gradient(
                    135deg,
                    var(--primary) 0%,
                    var(--primary-dark) 100%
                );
            color: var(--white);
        }

        .resend-btn:hover {
            filter: brightness(0.96);
            transform: translateY(-1px);
        }

        .logout-btn {
            border: 1px solid var(--border);
            background: #FFFFFF;
            color: #475569;
        }

        .logout-btn:hover {
            border-color: var(--primary);
            color: var(--primary-dark);
            background: #F8FAFC;
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

            .heading h1 {
                font-size: 23px;
            }

            .resend-btn,
            .logout-btn {
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
                        Thibitisha Barua Pepe
                    </h1>

                    <p>
                        Asante kwa kujisajili. Kabla ya kuendelea, tafadhali thibitisha barua pepe yako kwa kubofya kiungo tulichokutumia.
                        Kama hujapokea ujumbe huo, unaweza kutuma kiungo kingine.
                    </p>

                </div>

                @if(session('status') === 'verification-link-sent')
                    <div class="status">
                        Kiungo kipya cha uthibitisho kimetumwa kwenye barua pepe uliyotumia wakati wa kujisajili.
                    </div>
                @endif

                <div class="actions">

                    <form
                        method="POST"
                        action="{{ route('verification.send') }}"
                        class="resend-form"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="resend-btn"
                        >
                            Tuma Tena Barua ya Uthibitisho
                        </button>
                    </form>

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        class="logout-form"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="logout-btn"
                        >
                            Ondoka
                        </button>
                    </form>

                </div>

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