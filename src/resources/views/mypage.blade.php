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
        <div class="mypage-container">

            {{-- プロフィールセクション --}}
            <div class="profile-section">
                <div class="profile-avatar"></div>
                <div class="profile-info">
                    <div class="user-name">ユーザー名</div>
                    <a href="/mypage/profile" class="edit-button">プロフィールを編集</a>
                </div>
            </div>

            <div class="mypage-form-container">
                {{-- 出品した商品/購入した商品 タブ --}}
                <div class="tab-menu">
                    <a href="/sell" class="tab-link @if(Request::is('sell')) active @endif">
                        <span class="tab-text">出品した商品</span>
                    </a>
                    <a href="/buy" class="tab-link @if(Request::is('buy')) active @endif">
                        <span class="tab-text">購入した商品</span>
                    </a>
                </div>
            </div>

            {{-- 商品一覧グリッド (image_6e1b35.png を参考に作成) --}}
            <div class="index-grid-container">
                <div class="item-grid">
                    {{-- 商品カードの繰り返しをBladeでシミュレーション --}}
                    @for ($i = 0; $i < 8; $i++)
                        <div class="item-card">
                            <div class="item-image-placeholder">商品画像</div>
                            <p class="item-name">商品名</p>
                        </div>
                    @endfor
                </div>
            </div>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabLinks = document.querySelectorAll('.tab-link');
            const contents = document.querySelectorAll('.item-grid-wrapper');

            tabLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault(); // ページ遷移を防止

                    // 1. タブの active クラスを付け替える
                    tabLinks.forEach(l => l.classList.remove('active'));
                    e.currentTarget.classList.add('active');

                    // 2. コンテンツを切り替える
                    const targetTab = e.currentTarget.dataset.tab; // data-tab="recommend" または "mylist" を取得

                    contents.forEach(content => {
                        if (content.id === `${targetTab}-content`) {
                            // クリックされたタブに対応するコンテンツを表示
                            content.classList.remove('hidden-content');
                            content.classList.add('active-content');
                        } else {
                            // その他のコンテンツを非表示
                            content.classList.remove('active-content');
                            content.classList.add('hidden-content');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>