<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH-商品詳細画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/show.css')}}">
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

    <main>
        <div class="show-container">

            {{-- ❗ 修正: 2カラムレイアウトの親要素 ❗ --}}
            <div class="item-detail-wrapper">

                {{-- 1. 左側: 商品画像 --}}
                <div class="item-image-area">
                    <div class="show-image-container">
                        <div class="item-image-placeholder">商品画像</div>
                    </div>
                </div>

                {{-- 2. 右側: 商品情報、商品説明、コメント --}}
                <div class="item-info-area">
                    <h1 class="item-name">商品名がここに入る</h1>
                    <p class="item-brand">ブランド名</p>
                    <p class="item-price">¥47,000<span class="tax-info">(税込)</span></p>

                    {{-- いいね/コメントアイコン --}}
                    <div class="reaction-buttons">
                        {{-- いいね (星アイコン) --}}
                        <div class="reaction-item">
                            <span class="reaction-icon">
                                <img src="{{ asset('storage/img/星アイコン8.png') }}" alt="星アイコン" class="icon reaction-icon-img">
                            </span>
                            <span class="reaction-count">10</span>
                        </div>
                        {{-- コメント (ふきだしアイコン) --}}
                        <div class="reaction-item">
                            <span class="reaction-icon">
                                <img src="{{ asset('storage/img/ふきだしのアイコン.png') }}" alt="ふきだしアイコン" class="icon reaction-icon-img">
                            </span>
                            <span class="reaction-count">1</span>
                        </div>
                    </div>

                    {{-- 購入手続きへ ボタン (画像中央の右側エリアにあるため移動) --}}
                    <a href="/purchase" class="purchase-link">購入手続きへ</a>

                    {{-- 商品説明 --}}
                    <h2 class="section-title">商品説明</h2>
                    <p class="item-description">
                        カラー：グレー<br>
                        新品<br>
                        商品の状態は良好です。傷もありません。<br>
                        購入後、即発送いたします。
                    </p>

                    {{-- 商品の情報 (カテゴリ/状態) --}}
                    <h2 class="section-title">商品の情報</h2>
                    <div class="item-metadata">
                        <div class="metadata-row">
                            <span class="metadata-label">カテゴリー</span>
                            <span class="metadata-value">洋服</span>
                            <span class="metadata-value">メンズ</span>
                        </div>
                        <div class="metadata-row">
                            <span class="metadata-label">商品の状態</span>
                            <span class="metadata-value">良好</span>
                        </div>
                    </div>

                    {{-- コメントセクション --}}
                    <div class="comment-section">
                        <h2 class="comment-section-title">コメント(1)</h2>
                        <div class="comment-list">
                            <div class="comment-item">
                                <div class="profile-avatar"></div>
                                <span class="comment-user">admin</span>
                            </div>
                            <div class="comment-display">
                                <p class="comment-text">こちらにコメントが入ります。</p>
                            </div>
                            {{-- コメントの繰り返し（省略） --}}
                        </div>
                        <h2 class="section-title comment-form-title">商品へのコメント</h2>
                        <textarea class="comment-input" placeholder="コメントを入力"></textarea>
                    </div>

                    {{-- コメントを送信する ボタン --}}
                    <div class="comment-button">
                        <button type="submit" class="comment-submit-button">コメントを送信する</button>
                    </div>
                </div>

                {{-- ❗ 削除: .purchase-sidebar-area は不要 ❗ --}}

            </div> {{-- item-detail-wrapper 終了 --}}
        </div> {{-- show-container 終了 --}}
    </main>
</body>
</html>