<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH-商品一覧画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index.css')}}">
</head>

<body>

    <header class="header-shadow header">
        <div class="header-content">

            <a class="logo" href="/">
                <img src="{{ asset('storage/img/Vector (3).png') }}" alt="logoアイコン" class="icon logo-icon-img">
                <img src="{{ asset('storage/img/Group (2).png') }}" alt="logoテキスト" class="icon logo-text-img">
            </a>

            <!-- 検索フォームの組み込みと修正 -->
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
        <div class="index-container">

            <div class="index-form-container">
                {{-- おすすめ/マイリスト タブ --}}
                <div class="tab-menu">
                    @php
                        // $lastKeywordが空でなければ、リンクにキーワードを含める
                        $routeParams = !empty($lastKeyword) ? ['keyword' => $lastKeyword] : [];

                        // ★★★ 修正: おすすめタブがアクティブになる条件を定義 ★★★
                        // 以下のいずれかの条件を満たすとき、'active' クラスを付与する:
                        // 1. マイリスト画面ではない (おすすめ画面にいる)
                        // 2. かつ、URLに 'keyword' パラメータが含まれている (検索結果を表示している)
                        $isRecommendActive = !Request::is('mylist') && Request::has('keyword');
                    @endphp
                    <a
                        href="{{ route('items.index', $routeParams) }}"
                        class="tab-link @if($isRecommendActive) active @endif"
                    >
                        <span class="tab-text">おすすめ</span>
                    </a>
                    {{-- 未認証ユーザーはマイリストにアクセスできないため、@auth ディレクティブで囲む --}}
                    @auth
                        @php
                            // ★★★ マイリストのリンクに、$lastKeyword を付与する ★★★
                            $mylistParams = !empty($lastKeyword) ? ['keyword' => $lastKeyword] : [];
                        @endphp
                        <a href="{{ route('items.mylist', $mylistParams) }}" class="tab-link @if(Request::is('mylist')) active @endif">
                            <span class="tab-text">マイリスト</span>
                        </a>
                    @endauth
                </div>
            </div>

            {{-- 商品一覧グリッド (image_6e1b35.png を参考に作成) --}}
            <div class="index-grid-container">
                <div class="item-grid">
                    {{-- コントローラから渡された $items (商品コレクション) をループ処理します --}}
                    @if (isset($items) && $items->isNotEmpty())
                        @foreach ($items as $item)

                            {{-- 商品詳細ページへのリンクとして item-card をラップ --}}
                            <a href="/item/{{ $item->id }}" class="item-card">

                                {{-- 【画像表示】: item テーブルの image_path カラムを使用 --}}
                                <div class="item-image-placeholder">
                                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                                    {{-- 商品が購入済みの場合はSOLD OUTオーバーレイを表示 --}}
                                    @if ($item->is_sold)
                                        <div class="sold-out-overlay">
                                            <span class="sold-out-text">SOLD OUT</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- 【商品名表示】: item テーブルの name カラムを使用 --}}
                                <p class="item-name">{{ $item->name }}</p>

                            </a>
                        @endforeach
                    @else
                        {{-- 商品が存在しない場合の表示（CSSの崩れを防ぐため grid の外に配置推奨ですが、ここでは最小限の変更に留めます） --}}
                        <div class="w-full text-center p-8">
                            <p>商品がまだ登録されていません。</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // タブの切り替えロジックは、aタグによるページ遷移（/recommend または /mylist）に任せます。
            const tabLinks = document.querySelectorAll('.tab-link');
            tabLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    // ここでのクライアント側による active クラスの切り替えは不要です。
                });
            });
        });
    </script>
</body>
</html>