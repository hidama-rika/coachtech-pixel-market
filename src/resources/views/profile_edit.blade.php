<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH-プロフィール設定画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile_edit.css')}}">
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

    <main class="profile_edit-container">
        <div class="profile_edit-form-container">

            <div class="form-title">プロフィール設定</div>

            <div class="profile-image-section">
                <div class="profile-image-area">
                    <img src="placeholder_path_to_profile_image" alt="プロフィール画像" class="profile-image">
                </div>
                <div class="image-upload-button-area">
                    <button type="button" class="image-select-btn">画像を選択する</button>
                    <input type="file" id="image-upload" name="profile_image" accept="image/*" style="display: none;">
                </div>
            </div>

            <form class="form" action="/mypage/profile" method="post" novalidate>
                @csrf

                {{-- ユーザー名 --}}
                <div class="form-group">
                    <label for="name">ユーザー名</label>
                    <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus placeholder="ユーザー名を入力">
                    <p class="profile_edit-form__error-message">
                        @error('name')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 郵便番号 --}}
                <div class="form-group">
                    <label for="post_code">郵便番号</label>
                    <input id="post_code" type="post_code" class="form-control" name="post_code" value="{{ old('post_code') }}" required placeholder="郵便番号を入力">
                    <p class="profile_edit-form__error-message">
                        @error('email')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 住所 --}}
                <div class="form-group">
                    <label for="address">住所</label>
                    <input id="address" type="address" class="form-control" name="address" required placeholder="住所を入力">
                    <p class="profile_edit-form__error-message">
                        @error('address')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 建物名 --}}
                <div class="form-group">
                    <label for="building-name">建物名</label>
                    <input id="building-name" type="building-name" class="form-control" name="building-name" required placeholder="建物名を入力">
                    <p class="profile_edit-form__error-message">
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