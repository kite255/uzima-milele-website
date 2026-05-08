<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jisajili - Uzima Milele</title>

    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

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
        body,
        button,
        input,
        textarea,
        select,
        a {
            font-family: 'Lato', sans-serif;
        }

        html,
        body {
            margin: 0;
            width: 100%;
            min-height: 100%;
            background: var(--background);
            overflow-x: hidden;
        }

        .page {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 14px;
        }

        .card {
            width: 100%;
            max-width: 370px;
            background: #ffffff;
            border-radius: 20px;
            padding: 24px 24px 22px;
            box-shadow: 0 16px 38px rgba(14, 61, 79, 0.08);
            border-top: 5px solid var(--primary);
        }

        .logo {
            display: block;
            height: 34px;
            width: auto;
            max-width: 95px;
            object-fit: contain;
            margin: 0 auto 14px;
        }

        h1 {
            margin: 0;
            text-align: center;
            color: var(--navy);
            font-size: 22px;
            line-height: 1.15;
            font-weight: 900;
        }

        .subtitle {
            margin: 8px 0 18px;
            text-align: center;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
            font-weight: 400;
        }

        .field {
            margin-bottom: 11px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: var(--navy);
            font-size: 13px;
            font-weight: 900;
        }

        .optional {
            font-weight: 500;
            color: #64748b;
            font-size: 11px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"] {
            width: 100%;
            height: 42px;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0 14px;
            font-size: 14px;
            font-weight: 400;
            outline: none;
            color: #111827;
            background: #ffffff;
        }

        input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0, 131, 203, 0.15);
        }

        .error {
            margin-top: 6px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 700;
        }

        .btn {
            width: 100%;
            height: 42px;
            border: 0;
            border-radius: 12px;
            background: var(--primary);
            color: white;
            font-size: 15px;
            font-weight: 900;
            cursor: pointer;
            transition: 0.2s ease;
            margin-top: 4px;
        }

        .btn:hover {
            background: var(--primary-dark);
        }

        .login-text {
            margin: 14px 0 0;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
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
            margin-top: 15px;
            text-align: center;
            color: var(--navy);
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
        }

        .back:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        @media (max-width: 520px) {
            .card {
                max-width: 100%;
                border-radius: 18px;
                padding: 24px 22px 22px;
            }

            h1 {
                font-size: 22px;
            }

            .subtitle {
                font-size: 13px;
            }
        }

        @media (max-height: 680px) {
            .page {
                padding-top: 10px;
                padding-bottom: 10px;
            }

            .card {
                padding-top: 20px;
                padding-bottom: 18px;
            }

            .logo {
                height: 30px;
                margin-bottom: 10px;
            }

            .subtitle {
                margin-bottom: 14px;
            }

            .field {
                margin-bottom: 9px;
            }

            input[type="text"],
            input[type="email"],
            input[type="tel"],
            input[type="password"],
            .btn {
                height: 39px;
            }

            .login-text {
                margin-top: 12px;
            }

            .back {
                margin-top: 12px;
            }
        }
    </style>
</head>

<body>
    <main class="page">
        <section class="card">
            <img src="<?php echo e(asset('logo.png')); ?>" alt="Uzima Milele" class="logo">

            <h1>Jisajili</h1>

            <p class="subtitle">
                Tengeneza akaunti ili uanze kujifunza Biblia
            </p>

            <form method="POST" action="<?php echo e(route('register')); ?>">
                <?php echo csrf_field(); ?>

                <input type="hidden" name="redirect" value="<?php echo e(request('redirect')); ?>">

                <div class="field">
                    <label for="name">Jina Kamili</label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="<?php echo e(old('name')); ?>"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Weka jina lako kamili"
                    >

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="error"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="field">
                    <label for="email">Barua Pepe</label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="<?php echo e(old('email')); ?>"
                        required
                        autocomplete="username"
                        placeholder="mfano: jina@email.com"
                    >

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="error"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="field">
                    <label for="phone">
                        Namba ya Simu <span class="optional">(si lazima)</span>
                    </label>

                    <input
                        id="phone"
                        type="tel"
                        name="phone"
                        value="<?php echo e(old('phone')); ?>"
                        autocomplete="tel"
                        placeholder="0712345678"
                    >

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="error"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="field">
                    <label for="password">Nenosiri</label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="Tengeneza nenosiri"
                    >

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="error"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="field">
                    <label for="password_confirmation">Thibitisha Nenosiri</label>

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Rudia nenosiri"
                    >

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="error"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <button type="submit" class="btn">
                    Jisajili
                </button>

                <p class="login-text">
                    Tayari una akaunti?
                    <a href="<?php echo e(route('login', ['redirect' => request('redirect')])); ?>">
                        Ingia
                    </a>
                </p>
            </form>

            <a href="<?php echo e(url('/')); ?>" class="back">
                ← Rudi kwenye tovuti
            </a>
        </section>
    </main>
</body>
</html><?php /**PATH /home/uzimamil/public_html/new.uzimamilele.or.tz/new/resources/views/auth/register.blade.php ENDPATH**/ ?>