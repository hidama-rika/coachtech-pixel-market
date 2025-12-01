<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH-プロフィール画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mypage.css')}}">
</head>

<body>

    <header class="header-shadow header">
        <div class="header-content">

            <a class="logo" href="/">
                <img src="{{ asset('storage/img/Vector (3).png') }}" alt="logoアイコン" class="icon logo-icon-img">
                <img src="{{ asset('storage/img/Group (2).png') }}" alt="logoテキスト" class="icon logo-text-img">
            </a>

            <div class="search-form-container">
                <!-- GETメソッドで / (または {{ route('items.index') }}) に検索クエリを送信 -->
                <!-- <input type="text">が<form>タグ内にあるため、Enterキーで自動的に送信されます。-->
                {{-- 検索アイコンを表示するため、inputとボタンを一つのコンテナでラップする場合はCSSの調整が必要です --}}
                <form action="/" method="GET" class="search-form">
                    <input
                        type="search"
                        name="keyword"
                        class="search-input"
                        placeholder="なにをお探しですか？"
                        value="{{ $lastKeyword }}"
                    >
                </form>
            </div>

            <nav class="nav-menu">

                <form method="POST" action="{{ route('logout') }}">
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

    <main>
        <div class="mypage-container">

            {{-- プロフィールセクション --}}
            <div class="profile-section">
                @php
                    // プロフィール画像のパスが存在するかチェックし、存在しない場合はデフォルトを設定
                    $profileImagePath = $user->profile_image ?? 'default/no-avatar.png';
                    $profileImageUrl = asset('storage/' . $profileImagePath);
                    $userName = $user->name ?? 'ユーザー名なし';
                @endphp
                <div class="profile-avatar">
                    {{-- $user->profile_imageがnullの場合のフォールバックを追加 --}}
                    <img
                        src="{{ asset('storage/' . ($user->profile_image ?? 'default/no-avatar.png')) }}"
                        alt=""
                    >
                </div>
                <div class="profile-info">
                    {{-- null許容の場合でも安全な記法に修正 --}}
                    <div class="user-name">
                        {{ $user->name ?? 'ユーザー名なし' }}
                    </div>
                    <a href="/mypage/profile" class="edit-button">プロフィールを編集</a>
                </div>
            </div>

            <div class="mypage-form-container">
                {{-- 出品した商品/購入した商品 タブ --}}
                <div class="tab-menu">
                    {{-- *** 修正箇所１：リンク先を専用のルートに変更し、アクティブ判定を $currentTab に変更 *** --}}
                    {{-- /mypage?tab=listed ル}ート（MypageController@listedItems）にリンクします --}
                    {{-- コントローラーから渡された $currentTab 変数を使ってアクティブを判定します --}}
                    <a
                        href="/mypage?tab=listed"
                        class="tab-link @if(isset($currentTab) && $currentTab === 'listed') active @endif"
                        data-target="listed"
                    >
                        <span class="tab-text">出品した商品</span>
                    </a>
                    <a
                        href="/mypage?tab=purchased"
                        class="tab-link @if(isset($currentTab) && $currentTab === 'purchased') active @endif"
                        data-target="purchased"
                    >
                        <span class="tab-text">購入した商品</span>
                    </a>
                </div>
            </div>

            {{-- 商品一覧グリッド (出品商品) --}}
            <div class="index-grid-container">

                <div id="listed-content" class="item-grid-wrapper @if(isset($currentTab) && $currentTab === 'listed') active-content @else hidden-content @endif">
                    <div class="item-grid">
                        {{-- コントローラから渡された $items (商品コレクション) をループ処理します --}}
                        {{-- *** 修正箇所: $items から $listedItems に変更します *** --}}
                        @if (isset($listedItems) && $listedItems->isNotEmpty())
                            @foreach ($listedItems as $item)
                                @php
                                    // 【修正1】画像パスが存在しない場合のフォールバックURLを定義
                                    $itemImageUrl = $item->image_path
                                    ? asset('storage/' . $item->image_path)
                                    : 'https://placehold.co/300x150/ffc107/fff?text=No+Image'; // 出品は黄色
                                @endphp
                                <a href="/item/{{ $item->id }}" class="item-card">
                                    {{-- 【画像表示】: item テーブルの image_path カラムを使用 --}}
                                    <div class="item-image-placeholder">
                                        {{-- 画像の読み込みに失敗した場合のフォールバックとしてエラー画像を表示 --}}
                                        <img
                                            src="{{ asset('storage/' . $item->image_path) }}"
                                            alt="{{ $item->name }}"
                                            onerror="this.onerror=null; this.src='https://placehold.co/300x150/ff3333/fff?text=Load+Error';"
                                        >
                                        {{-- 商品が購入済みの場合はSOLD OUTオーバーレイを表示 --}}
                                        @if ($item->is_sold)
                                            <div class="sold-out-overlay">
                                                <span class="sold-out-text">SOLD OUT</span>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="item-name">{{ $item->name }}</p>
                                </a>
                            @endforeach
                        @else
                            <div class="w-full text-center p-8">
                                <p>出品した商品がまだありません。</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- 商品一覧グリッド (購入商品) --}}
                <div id="purchased-content" class="item-grid-wrapper @if(isset($currentTab) && $currentTab === 'purchased') active-content @else hidden-content @endif">
                    <div class="item-grid">
                        {{-- *** 修正箇所: $listedItems から $purchasedItems に変更します *** --}}
                        @if (isset($purchasedItems) && $purchasedItems->isNotEmpty())
                            @foreach ($purchasedItems as $item)
                                @php
                                    // 画像パスの有無をチェックし、存在しなければプレースホルダーを設定
                                    $itemImageUrl = $item->image_path
                                        ? asset('storage/' . $item->image_path)
                                        : 'https://placehold.co/300x150/28a745/fff?text=No+Image'; // 購入は緑色
                                @endphp
                                <a href="/item/{{ $item->id }}" class="item-card">
                                    {{-- 【画像表示】: item テーブルの image_path カラムを使用 --}}
                                    <div class="item-image-placeholder">
                                        {{-- 画像の読み込みに失敗した場合のフォールバックとしてエラー画像を表示 --}}
                                        <img
                                            src="{{ asset('storage/' . $item->image_path) }}"
                                            alt="{{ $item->name }}"
                                            onerror="this.onerror=null; this.src='https://placehold.co/300x150/ff3333/fff?text=Load+Error';"
                                        >
                                        {{-- 商品が購入済みの場合はSOLD OUTオーバーレイを表示 --}}
                                        @if ($item->is_sold)
                                            <div class="sold-out-overlay">
                                                <span class="sold-out-text">SOLD OUT</span>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="item-name">{{ $item->name }}</p>
                                </a>
                            @endforeach
                        @else
                            <div class="w-full text-center p-8">
                                <p>購入した商品がまだありません。</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </main>

</body>
</html>