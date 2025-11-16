<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH-商品詳細画面</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                        <!-- いいねボタン -->
                    {{-- 認証済みユーザーのみいいねボタンを有効化 --}}
                    @auth
                        {{-- 💡 ここをフォームで囲むことで、ルーティングのエラーを解消します 💡 --}}
                        {{-- data-item-id は不要になりますが、JavaScript側で data-like-url を使っているのでそのまま残します --}}
                        {{-- data-* 属性はJavaScriptのために残します --}}
                        <form id="like-toggle-form" action="{{ route('like.toggle', $item) }}" method="POST">
                            @csrf
                            <button type="button" class="reaction-item like-button"
                            id="like-toggle-button"
                            data-item-id="{{ $item->id }}"
                            data-like-url="{{ route('like.toggle', ['item' => $item->id]) }}"
                            data-is-liked="@if(Auth::user()->isLiking($item)) true @else false
                            @endif"
                            >
                                <span class="reaction-icon">
                                    {{-- ユーザーがいいね済みなら 'liked' クラスを付与 --}}
                                    <img src="{{ asset('storage/img/Vector (4).png') }}" alt="いいねアイコン" class="like-icon-img @if(Auth::user()->isLiking($item)) liked @endif" id="like-icon">
                                </span>
                            </button>
                        </form>
                    @else
                        {{-- 未認証ユーザーはボタンとして機能させず、アイコンとカウントのみ表示 --}}
                        <div class="reaction-item">
                            <div class="reaction-item-liked">
                                <span class="reaction-icon">
                                    <img
                                    src="{{ asset('storage/img/Vector (4).png') }}"
                                    alt="いいねアイコン"
                                    class="icon like-icon-img @if($isLiked ?? false) liked @endif"
                                    id="like-icon"
                                    >
                                </span>
                            </div>
                    @endauth
                        {{-- いいね数の表示を @auth/@else の外に出して、常に表示されるように変更 --}}
                            <span class="reaction-count" id="like-count">
                                {{ $item->likedUsers->count() }}
                            </span>
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
                            {{-- ❗ 修正: metadata-valueにタグを並べるためのflex-wrapクラスを追加 ❗--}}
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
                                <div class=comment-item-header>
                                    <div class="comment-item">
                                    {{-- 修正: $comment からユーザー情報にアクセス --}}
                                        <div class="profile-avatar">
                                            <div class="avatar-image">
                                            {{-- $comment->user は必ず存在し、profile_image には必ずデータがある前提 --}}
                                                <img
                                                src="{{ asset('storage/'. $comment->user->profile_image) }}"
                                                alt="{{ $comment->user->name }}のアバター"
                                                >
                                            </div>
                                        </div>

                                        {{-- $comment からユーザー名にアクセス --}}
                                        <span class="comment-user">{{ $comment->user->name }}</span>

                                    </div>

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
                        <!-- 💡 修正点 1: ここにメッセージ表示用の要素を追加 💡 -->
                        <p class="show-form__error-message" id="comment-message" style="display: none;"></p>
                        {{-- 💡 修正: フォームにIDを追加 💡 --}}
                        <form id="comment-form" method="POST" action="{{ route('comment.store', ['item_id' => $item->id]) }}">
                            @csrf
                            <textarea name="comment" class="comment-input" placeholder="コメントを入力"></textarea>

                            {{-- コメントを送信する ボタン --}}
                            <div class="comment-button">
                                <button type="submit" id="comment-submit-button"
                                class="comment-submit-button">コメントを送信する</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ❗ 削除: .purchase-sidebar-area は不要 ❗ --}}

            </div>
        </div>
    </main>

    {{-- ======================================================= --}}
    {{-- コメント投稿後に自動でリロードするためのJavaScriptを追加 💡 --}}
    {{-- ======================================================= --}}
    <script>
        // CSRFトークンをmetaタグから取得
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('comment-form');
            const submitButton = document.getElementById('comment-submit-button');
            const messageArea = document.getElementById('comment-message');
            const textarea = form.querySelector('textarea[name="comment"]');

            // メッセージ表示関数
            function showMessage(message, type = 'success') {
                messageArea.textContent = message;
                messageArea.className = `show-form__error-message comment-message ${type}`;
                messageArea.style.display = 'block';
            }

            // メッセージ非表示関数
            function hideMessage() {
                messageArea.style.display = 'none';
                messageArea.className = 'show-form__error-message';
            }

            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault(); // ページのデフォルトのフォーム送信を停止
                    hideMessage();

                    const comment = textarea.value.trim();
                    if (!comment) {
                        showMessage('コメントを入力してください。', 'error');
                        return;
                    }

                    // 複数回送信を防ぐためボタンを無効化
                    submitButton.disabled = true;
                    submitButton.textContent = '送信中...';

                    // フォームデータを取得
                    const formData = new FormData(form);
                    // FormDataをJSON形式に変換
                    const payload = {};
                    formData.forEach((value, key) => {
                        payload[key] = value;
                    });

                    // fetchのオプション
                    const fetchOptions = {
                        method: 'POST',
                        // JSONボディを送信するため、ヘッダーとボディを調整
                        headers: {
                            'Content-Type': 'application/json',
                            // グローバル変数 csrfToken を使用してトークンをヘッダーで送信
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest' // LaravelでAJAXリクエストを認識させる
                        },
                        body: JSON.stringify(payload)
                    };

                    try {
                        const response = await fetch(form.action, fetchOptions);

                        if (response.ok) {
                            // 成功したら、サーバーからのメッセージを取得（オプション）
                            const data = await response.json();

                            // 成功メッセージを表示し、ページをリロード
                            showMessage(data.message || 'コメントを投稿しました。ページをリロードしています...', 'success');

                            // ページの再読み込みを実行
                            // これにより、DBから最新のコメントが新しい順に取得され、一番上に表示される
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);

                        } else {
                            // エラー（バリデーションエラーやその他の問題）
                            const errorData = await response.json();
                            let errorMessage = 'コメントの投稿に失敗しました。';

                            // バリデーションエラーがあれば詳細を表示
                            if (errorData.errors && Object.keys(errorData.errors).length > 0) {
                                // 最初のバリデーションエラーメッセージを取得して表示
                                const firstError = Object.values(errorData.errors)[0][0];
                                errorMessage = firstError;
                            } else if (errorData.message) {
                                errorMessage = errorData.message;
                            }

                            showMessage(errorMessage, 'error', true);
                        }

                    } catch (error) {
                        console.error('通信エラー:', error);
                        showMessage('通信中に予期せぬエラーが発生しました。', 'error', true);
                    } finally {
                        // 処理が終わったらボタンを元に戻す
                        submitButton.disabled = false;
                        submitButton.textContent = 'コメントを送信する';
                        textarea.value = ''; // コメント欄をクリア
                    }
                });
            }
        });
    </script>
    // いいね処理は外部ファイル (show.js) に集約する
    <script src="{{ asset('js/show.js') }}" defer></script>
</body>
</html>