<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH-購入完了画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/success.css')}}">
</head>

<body>

    <header class="header-shadow header">
        <div class="header-content">

            <a class="logo" href="/">
                <img src="{{ asset('storage/img/Vector (3).png') }}" alt="logoアイコン" class="icon logo-icon-img">
                <img src="{{ asset('storage/img/Group (2).png') }}" alt="logoテキスト" class="icon logo-text-img">
            </a>

            <div class="search-form-container">
                <input type="text" class="search-input" placeholder="なにをお探しですか？">
            </div>

            <nav class="nav-menu">

                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="nav-button logout-button">
                        <span class="nav-text">ログアウト</span>
                    </button>
                </form>

                <a href="/mypage" class="nav-button mypage-button">
                    <span class="nav-text">マイページ</span>
                </a>

                <a href="/sell" class="nav-button sell-button">
                    <span class="sell-text">出品</span>
                </a>

            </nav>

        </div>
    </header>

    <main class="success-container">
        <div class="success-form-container">

            {{-- メッセージ領域 --}}
            <div class="message-area">
                <p class="success-message-purchase">ご購入手続きが完了しました。</p>
                <p class="message-shipping">ご登録の配送先住所へ発送準備を進めます。</p>
            </div>

            {{-- リンク領域 --}}
            <div class="action-area">

                {{-- 購入した商品を確認できるマイページへのリンク --}}
                <a href="{{ route('mypage') }}" class="mypage-link">
                    購入した商品を確認する
                </a>

            </div>

        </div>

    </main>
</body>

</html>