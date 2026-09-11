<div class="um-admin-login">

    <style>
        body,
        .fi-simple-layout {
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
                    #082E3D 0%,
                    #0E3D4F 52%,
                    #15576F 100%
                ) !important;
        }

        .fi-simple-main-ctn {
            min-height: 100vh !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 14px !important;
        }

        .fi-simple-main {
            width: 100% !important;
            max-width: 500px !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            padding: 0 !important;
        }

        .um-admin-login {
            width: 100%;
        }

        .um-admin-card {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 22px 30px 20px;
            border-radius: 16px;
            background: #ffffff !important;
            border: 1px solid rgba(203, 213, 225, 0.85);
            box-shadow:
                0 18px 45px rgba(0, 0, 0, 0.16),
                0 5px 16px rgba(0, 0, 0, 0.05);
            color-scheme: light;
            color: #263244 !important;
        }

        .um-admin-logo {
            display: block;
            width: auto;
            height: 78px;
            margin: 0 auto 3px;
            object-fit: contain;
        }

        .um-brand-accent {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-bottom: 6px;
        }

        .um-brand-accent span {
            width: 10px;
            height: 4px;
            border-radius: 999px;
        }

        .um-brand-accent span:nth-child(1) {
            background: #22C55E;
        }

        .um-brand-accent span:nth-child(2) {
            background: #F4B122;
        }

        .um-brand-accent span:nth-child(3) {
            background: #0083CB;
        }

        .um-heading {
            margin-bottom: 22px;
            text-align: center;
        }

        .um-heading h1 {
            margin: 0;
            color: #263244 !important;
            font-size: 25px;
            line-height: 1.15;
            font-weight: 900;
        }

        .um-heading p {
            margin: 5px 0 0;
            color: #64748B !important;
            font-size: 12px;
            line-height: 1.5;
            font-weight: 500;
        }

        .um-admin-card .fi-fo-field-wrp {
            margin-bottom: 12px;
        }

        .um-admin-card .fi-fo-field-wrp-label,
        .um-admin-card .fi-fo-field-wrp-label label,
        .um-admin-card .fi-fo-field-wrp-label span,
        .um-admin-card label {
            color: #334155 !important;
        }

        .um-admin-card .fi-input-wrp {
            min-height: 48px;
            border-radius: 4px !important;
            background: #ffffff !important;
        }

        .um-admin-card input {
            color: #263244 !important;
            background: #ffffff !important;
        }

        .um-admin-card input::placeholder {
            color: #94A3B8 !important;
        }

        .um-admin-card .fi-checkbox-label,
        .um-admin-card .fi-checkbox-label span {
            color: #334155 !important;
        }

        .um-admin-card .text-danger-600,
        .um-admin-card .dark\:text-danger-400 {
            color: #DC2626 !important;
        }

        .um-submit {
            width: 100%;
            height: 48px;
            margin-top: 14px;
            border: 0;
            border-radius: 4px;
            background:
                linear-gradient(
                    135deg,
                    #0083CB 0%,
                    #076994 100%
                );
            color: #ffffff;
            font-family: inherit;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            transition:
                filter 0.2s ease,
                transform 0.2s ease;
        }

        .um-submit:hover {
            filter: brightness(0.96);
            transform: translateY(-1px);
        }

        .um-back {
            display: flex;
            justify-content: center;
            margin-top: 12px;
        }

        .um-back a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255, 255, 255, 0.95);
            font-size: 10px;
            font-weight: 900;
            text-decoration: none;
        }

        .um-back a:hover {
            color: #F4B122;
        }

        @media (max-width: 540px) {
            .fi-simple-main-ctn {
                align-items: flex-start !important;
                padding: 10px !important;
            }

            .um-admin-card {
                padding: 20px 17px 18px;
                border-radius: 14px;
            }

            .um-admin-logo {
                height: 70px;
            }

            .um-heading h1 {
                font-size: 23px;
            }
        }
    </style>

    <div class="um-admin-card">

        <img
            src="{{ asset('logo.png') }}"
            alt="Uzima Milele"
            class="um-admin-logo"
        >

        <div class="um-brand-accent" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <div class="um-heading">
            <h1>
                Karibu Tena
            </h1>

            <p>
                Ingia kwenye akaunti ya msimamizi wa Uzima Milele.
            </p>
        </div>

        <x-filament-panels::form wire:submit="authenticate">

            {{ $this->form }}

            <button
                type="submit"
                class="um-submit"
            >
                Ingia
            </button>

        </x-filament-panels::form>

    </div>

    <div class="um-back">
        <a href="{{ url('/') }}">
            ← Rudi kwenye Tovuti
        </a>
    </div>

</div>