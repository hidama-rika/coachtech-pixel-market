<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECHフリマ-メール認証誘導画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/verify-email.css')}}">
</head>

<body>

    <header class="header-shadow header">
        <div class="header-content">

            <a class="logo" href="/">
                <img src="{{ asset('storage/img/Vector (3).png') }}" alt="logoアイコン" class="icon logo-icon-img">
                <img src="{{ asset('storage/img/Group (2).png') }}" alt="logoテキスト" class="icon logo-text-img">
            </a>

        </div>
    </header>

	<main class="verify-email-container">
        <div class="verify-email-form-container">

            {{-- メッセージ領域 --}}
            <div class="message-area">
                <p class="verify-message-sent">登録していただいたメールアドレスに認証メールを送付しました。</p>
                <p class="verify-instruction">メール認証を完了してください。</p>
            </div>

            {{-- ボタン/リンク領域 --}}
            <div class="action-area">
                {{-- 認証はこちらからボタン --}}
                {{-- Fortifyの仕様上、このボタンはメール内のリンクを押すことを誘導するだけの表示にすることが多いです --}}
                <a href="#" class="verify-email-btn">
                    認証はこちらから
                </a>

                {{-- 認証メールを再送するリンク（実際はPOSTフォーム推奨） --}}
                <form method="POST" action="{{ route('verification.send') }}" class="resend-form">
                    @csrf
                    <button type="submit" class="verify-email-link">
                        認証メールを再送する
                    </button>
                </form>
            </div>

        </div>

    </main>
</body>

</html>