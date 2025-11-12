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

    <main>
        <div class="show-container">

            {{-- ❗ 修正: 2カラムレイアウトの親要素 ❗ --}}
            <div class="item-detail-wrapper">

                {{-- 1. 左側: 商品画像 --}}
                <div class="item-image-area">
                    <div class="show-image-container">
                        <div class="item-image-placeholder">
                            <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                        </div>
                    </div>
                </div>

                {{-- 2. 右側: 商品情報、商品説明、コメント --}}
                <div class="item-info-area">
                    <h1 class="item-name">{{ $item->name }}</h1>
                    <p class="item-brand">{{ empty($item->brand) ? '' : $item->brand }}</p>
                    <p class="item-price">¥{{ number_format($item->price) }}<span class="tax-info">(税込)</span></p>

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
                            <span class="reaction-count">{{ $item->comments->count() }}</span>
                        </div>
                    </div>

                    {{-- 購入手続きへ ボタン (画像中央の右側エリアにあるため移動) --}}
                    <a href="{{ route('purchases.create', ['item_id' => $item->id]) }}" class="purchase-link">購入手続きへ</a>

                    {{-- 商品説明 --}}
                    <h2 class="section-title">商品説明</h2>
                    <p class="item-description">
                        {{ $item->description }}
                    </p>

                    {{-- 商品の情報 (カテゴリ/状態) --}}
                    <h2 class="section-title">商品の情報</h2>
                    <div class="item-metadata">
                        <div class="metadata-row">
                            <span class="metadata-label">カテゴリー</span>
                            {{-- ❗ 修正: metadata-valueにタグを並べるためのflex-wrapクラスを追加 ❗ --}}
                            <span class="metadata-value category-tags-wrapper">
                                {{-- 多対多のリレーションなので、categoriesコレクションをループして表示します --}}
                                @forelse ($item->categories as $category)
                                    {{-- ❗ 修正: 各カテゴリを独立したタグ要素で囲む ❗ --}}
                                    <span class="category-tag">
                                        {{ $category->name }}
                                    </span>
                                @empty
                                    カテゴリなし
                                @endforelse
                            </span>
                        </div>
                        <div class="metadata-row">
                            <span class="metadata-label">商品の状態</span>
                            <span class="metadata-value">
                                {{ $item->condition?->name }}
                            </span>
                        </div>
                    </div>

                    {{-- コメントセクション --}}
                    <div class="comment-section">
                        <h2 class="comment-section-title">コメント({{ $item->comments->count() }})</h2>
                        <div class="comment-list">
                            {{-- 💡 $item->comments（コレクション）をループし、個々の $comment を取り出す 💡 --}}
                            @forelse ($item->comments as $comment)
                                <div class="comment-item">
                                    {{-- 修正: $comment からユーザー情報にアクセス --}}
                                    <div class="profile-avatar">
                                        {{-- $comment->user は必ず存在し、profile_image には必ずデータがある前提 --}}
                                        <img
                                            src="{{ asset('storage/' . $comment->user->profile_image) }}"
                                            alt="{{ $comment->user->name }}のアバター"
                                        >
                                    </div>

                                    {{-- $comment からユーザー名にアクセス --}}
                                    <span class="comment-user">{{ $comment->user->name }}</span>

                                    <div class="comment-display">
                                        {{-- $comment のコメント本文にアクセス --}}
                                        <p class="comment-text">{{ $comment->body ?? $comment->comment }}</p>
                                    </div>
                                </div>
                            @empty
                                {{-- コメントがない場合の表示 --}}
                                <p class="no-comment-message">コメントなし。</p>
                            @endforelse
                        </div>
                        <h2 class="section-title comment-form-title">商品へのコメント</h2>
                        <form method="POST" action="/comment/store/{{ $item->id }}">
                            @csrf
                            <textarea name="body" class="comment-input" placeholder="コメントを入力"></textarea>

                            {{-- コメントを送信する ボタン --}}
                            <div class="comment-button">
                                <button type="submit" class="comment-submit-button">コメントを送信する</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ❗ 削除: .purchase-sidebar-area は不要 ❗ --}}

            </div> {{-- item-detail-wrapper 終了 --}}
        </div> {{-- show-container 終了 --}}
    </main>
</body>
</html>