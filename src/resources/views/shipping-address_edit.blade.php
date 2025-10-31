<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH-送付先住所変更画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shipping-address_edit.css')}}">
</head>

<body>

    <header class="header-shadow header">
        <div class="header-content">

            <div class="logo">
                <img src="{{ asset('storage/img/Vector (3).png') }}" alt="logoアイコン" class="icon logo-icon-img">
                <img src="{{ asset('storage/img/Group (2).png') }}" alt="logoテキスト" class="icon logo-text-img">
            </div>

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

    <main class="shipping-address_edit-container">
        <div class="shipping-address_edit-form-container">

            <div class="form-title">住所の変更</div>

            <form class="form" action="/mypage/profile" method="post" novalidate>
                @csrf

                {{-- 送付先郵便番号 --}}
                <div class="form-group">
                    <label for="shipping_post_code">郵便番号</label>
                    <input id="shipping_post_code" type="shipping_post_code" class="form-control" name="shipping_post_code" value="{{ old('shipping_post_code') }}" required placeholder="郵便番号を入力">
                    <p class="shipping-address_edit-form__error-message">
                        @error('post_code')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 送付先住所 --}}
                <div class="form-group">
                    <label for="shipping_address">住所</label>
                    <input id="shipping_address" type="shipping_address" class="form-control" name="shipping_address" required placeholder="住所を入力">
                    <p class="shipping-address_edit-form__error-message">
                        @error('address')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 送付先建物名 --}}
                <div class="form-group">
                    <label for="shipping_building-name">建物名</label>
                    <input id="shipping_building-name" type="shipping_building-name" class="form-control" name="shipping_building-name" required placeholder="建物名を入力">
                    <p class="shipping-address_edit-form__error-message">
                        @error('building-name')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 更新するボタン --}}
                <button type="submit" class="update-btn">
                    更新する
                </button>

            </form>

        </div>

    </main>

</body>
</html>