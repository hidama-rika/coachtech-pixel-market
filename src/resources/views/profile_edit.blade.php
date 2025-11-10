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

    <main class="profile_edit-container">
        <div class="profile_edit-form-container">

            <div class="form-title">プロフィール設定</div>

            <form class="form" action="{{ route('mypage.profile.update') }}" method="post" enctype="multipart/form-data" novalidate>
                @csrf
                @method('patch') <!-- PATCHメソッドを指定 -->

                <!-- JavaScriptで値を設定するため、非表示の input[type="file"] をフォーム内に入れる -->
                <!-- 既存の input[type="file"] をそのまま使用 -->

                {{-- プロフィール画像 --}}
                <div class="profile-image-section">
                    <div class="profile-image-area">
                        <!-- idを追加してJavaScriptからアクセスできるようにする -->
                        <img
                            id="profile-preview"
                            src="{{ asset('storage/' . $user->profile_image) }}"
                            alt="プロフィール画像"
                            class="profile-image"
                            onerror="this.onerror=null; this.src='https://placehold.co/120x120/D9D9D9/333333?text=Avatar';"
                        >
                    </div>
                    <div class="image-upload-button-area">
                        <!-- ボタンとファイルインプットの連携 -->
                        <button type="button" class="image-select-btn" id="image-select-btn">画像を選択する</button>
                        <!-- name="profile_image" の input: 常に非表示 -->
                        <input type="file" id="image-upload" name="profile_image" accept="image/*" style="display: none;">
                    </div>
                    <p class="profile_edit-form__error-message">
                        @error('profile_image')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- ユーザー名 --}}
                <div class="form-group">
                    <label for="name">ユーザー名</label>
                    <!-- ★修正: value属性に $user->name を反映 -->
                    <input
                        id="name"
                        type="text"
                        class="form-control"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        autofocus
                        placeholder="ユーザー名を入力"
                    >
                    <p class="profile_edit-form__error-message">
                        @error('name')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 郵便番号 --}}
                <div class="form-group">
                    <label for="post_code">郵便番号</label>
                    <!-- ★修正: name="post_code" に合わせ、value属性に $user->post_code を反映 -->
                    <input
                        id="post_code"
                        type="text"
                        class="form-control"
                        name="post_code"
                        value="{{ old('post_code', $user->post_code) }}"
                        required
                        placeholder="郵便番号を入力"
                    >
                    <p class="profile_edit-form__error-message">
                        @error('post_code')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 住所 --}}
                <div class="form-group">
                    <label for="address">住所</label>
                    <!-- ★修正: value属性に $user->address を反映 -->
                    <input
                        id="address"
                        type="text"
                        class="form-control"
                        name="address"
                        value="{{ old('address', $user->address) }}"
                        required
                        placeholder="住所を入力"
                    >
                    <p class="profile_edit-form__error-message">
                        @error('address')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 建物名 --}}
                <div class="form-group">
                    <label for="building-name">建物名</label>
                    <!-- ★修正: id/nameを building_name に合わせ、value属性に $user->building_name を反映 -->
                    <input
                        id="building_name"
                        type="text"
                        class="form-control"
                        name="building_name"
                        value="{{ old('building_name', $user->building_name) }}"
                        placeholder="建物名を入力"
                    >
                    <p class="profile_edit-form__error-message">
                        @error('building_name')
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

    <!-- jQueryのCDNを読み込み -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // 1. 「画像を選択する」ボタンがクリックされたら
            $('#image-select-btn').on('click', function() {
                // 非表示のファイル選択フィールドをクリックする
                $('#image-upload').trigger('click');
            });

            // 2. ファイルが選択されたら
            $('#image-upload').on('change', function(e) {
                // 選択されたファイルを取得
                const file = e.target.files[0];

                if (file) {
                    // FileReader APIを使ってファイルを読み込む
                    const reader = new FileReader();

                    reader.onload = function(event) {
                        // 読み込みが完了したら、プロフィール画像要素の src を更新し、プレビューを表示
                        $('#profile-preview').attr('src', event.target.result);
                    }

                    // ファイルをData URLとして読み込む
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>

</body>
</html>