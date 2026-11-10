<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Weka Nenosiri Jipya - Uzima Milele</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: system-ui, sans-serif;
            background: linear-gradient(145deg,#082e3d,#0e3d4f 52%,#15576f); }
        .page { min-height:100vh; padding:24px 14px; display:flex; justify-content:center; align-items:center; }
        .card { width:100%; max-width:480px; border-radius:16px; background:white; padding:28px;
            box-shadow:0 18px 45px rgba(0,0,0,.16); }
        .brand { display:block; height:72px; width:auto; max-width:100%; object-fit:contain; margin:0 auto 16px; }
        h1 { color:#263244; font-size:24px; margin:0 0 8px; text-align:center; }
        .hint { color:#64748b; font-size:14px; line-height:1.5; text-align:center; margin:0 0 24px; }
        label { display:block; color:#334155; font-weight:700; font-size:13px; margin:15px 0 6px; }
        input { width:100%; height:46px; border:1px solid #cbd5e1; padding:0 12px; border-radius:6px;
            font:inherit; color:#263244; }
        input:focus { outline:2px solid #0083cb; outline-offset:1px; }
        .error { color:#b91c1c; font-size:12px; margin-top:5px; }
        button { width:100%; min-height:48px; margin-top:22px; border:0; border-radius:6px; background:#0083cb;
            color:white; font:inherit; font-weight:750; cursor:pointer; }
        .back { display:block; margin-top:18px; text-align:center; color:#076994; font-weight:700; font-size:13px; }
        .alert { color:#b91c1c; background:#fef2f2; padding:12px; border-radius:6px; font-size:13px; }
    </style>
</head>
<body>
    <main class="page">
        <section class="card">
            <img src="{{ asset('logo.png') }}" alt="Uzima Milele" class="brand">
            <h1>Weka Nenosiri Jipya</h1>
            <p class="hint">Tengeneza nenosiri la Uzima Milele kwa barua pepe yako. Akaunti na maendeleo yako ya masomo yatabaki vilevile.</p>

            @if($errors->has('token') || $errors->has('email'))
                <div class="alert" role="alert">
                    {{ $errors->first('token') ?: $errors->first('email') }}
                    <p>Ikiwa kiungo kimeisha muda au hakifanyi kazi, omba kingine.</p>
                    <a href="{{ route('password.request') }}">Omba kiungo kipya</a>
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <label for="email">Barua pepe</label>
                <input id="email" type="email" name="email"
                       value="{{ old('email', $request->query('email')) }}"
                       autocomplete="email" required>
                @error('email') <div class="error" role="alert">{{ $message }}</div> @enderror

                <label for="password">Nenosiri jipya</label>
                <input id="password" type="password" name="password"
                       autocomplete="new-password" minlength="8" required>
                @error('password') <div class="error" role="alert">{{ $message }}</div> @enderror

                <label for="password_confirmation">Rudia nenosiri jipya</label>
                <input id="password_confirmation" type="password"
                       name="password_confirmation" autocomplete="new-password"
                       minlength="8" required>

                <button type="submit">Hifadhi Nenosiri Jipya</button>
            </form>
            <a class="back" href="{{ route('login') }}">Rudi kwenye kuingia</a>
        </section>
    </main>
</body>
</html>
