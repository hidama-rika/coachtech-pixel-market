<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH-会員登録</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/register.css')}}">
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

    <main class="register-container">
        <div class="register-form-container">

            <div class="form-title">会員登録</div>

            <form class="form" action="/register" method="post" novalidate>
                @csrf

                {{-- お名前 --}}
                <div class="form-group">
                    <label for="name">お名前</label>
                    <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus placeholder="名前を入力">
                    <p class="register-form__error-message">
                        @error('name')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- メールアドレス --}}
                <div class="form-group">
                    <label for="email">メールアドレス</label>
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required placeholder="メールアドレスを入力">
                    <p class="register-form__error-message">
                        @error('email')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- パスワード --}}
                <div class="form-group">
                    <label for="password">パスワード</label>
                    <input id="password" type="password" class="form-control" name="password" required placeholder="パスワードを入力">
                    <p class="register-form__error-message">
                        @error('password')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 確認用パスワード --}}
                <div class="form-group">
                    <label for="confirmed-password">確認用パスワード</label>
                    <input id="confirmed-password" type="confirmed-password" class="form-control" name="confirmed-password" required placeholder="確認用パスワードを入力">
                    <p class="register-form__error-message">
                        @error('confirmed-password')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 登録するボタン --}}
                <button type="submit" class="register-btn">
                    登録する
                </button>
            </form>

            {{-- ログインはこちらリンク --}}
            <a class="login-link" href="/login">
                ログインはこちら
            </a>

        </div>

    </main>

</body>
</html>