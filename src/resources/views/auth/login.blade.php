<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECHフリマ-ログイン</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css')}}">
</head>

<body>

    <header class="header-shadow header">
        <div class="header-content">

            <div class="logo">
                <img src="{{ asset('storage/img/Vector (3).png') }}" alt="logoアイコン" class="icon logo-icon-img">
                <img src="{{ asset('storage/img/Group (2).png') }}" alt="logoテキスト" class="icon logo-text-img">
            </div>

        </div>
    </header>

	<main class="login-container">
        <div class="login-form-container">

            <div class="form-title">ログイン</div>

            <form class="form" action="/login" method="post" novalidate>
                @csrf

                {{-- メールアドレス --}}
                <div class="form-group">
                    <label for="email">メールアドレス</label>
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="メールアドレスを入力">
                    <p class="login-form__error-message">
                        @error('email')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- パスワード --}}
                <div class="form-group">
                    <label for="password">パスワード</label>
                    <input id="password" type="password" class="form-control" name="password" required placeholder="パスワードを入力">
                    <p class="login-form__error-message">
                        @error('password')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- ログインボタン --}}
                <button type="submit" class="login-btn">
                    ログインする
                </button>
            </form>

            {{-- 会員登録リンク --}}
            <a class="register-link" href="/register">
                会員登録はこちら
            </a>

        </div>

    </main>
</body>

</html>