<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH-商品購入画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/new_purchases.css')}}">
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
        <div class="new_purchases-container">

            <div class="new_purchases-second-container">

                {{-- 🔥 修正: $item が存在する場合のみフォームを表示し、アクションはシンプルな route('purchase.store') にする 🔥 --}}
                @isset($item)
                <form action="{{ route('checkout.start', ['item_id' => $item->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $item->id }}">

                    {{-- 購入サマリーエリア（左側） --}}
                    <div class="purchase-details-container">

                        <div class="item-info">
                            {{-- $item の存在チェックとエラー対応 --}}
                            @isset($item)
                            <div class="item-image-area">
                                <div class="item-image">
                                    {{-- 画像URLが有効でない場合を考慮して、画像パスをそのまま使用 --}}
                                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                                </div>
                            </div>
                            <div class="item-text">
                                <p class="item-name">{{ $item->name }}</p>
                                <p class="item-price">¥{{ number_format($item->price) }}</p>
                            </div>
                            @else
                                {{-- 商品情報がない場合の代替表示 --}}
                                <div class="item-image-area">
                                    <div class="item-image placeholder-image">
                                        <p>画像なし</p>
                                    </div>
                                </div>
                                <div class="item-text">
                                    <p class="item-name">商品情報が見つかりません</p>
                                    <p class="item-price">¥0</p>
                                </div>
                            @endisset
                        </div>

                        {{-- 支払い方法の選択 --}}
                        <div class="payment-method-section section-divider">
                            <p class="section-title-small">支払い方法</p>

                            {{--修正: フォーム送信用の隠し入力フィールド--}}
                            <input type="hidden" id="payment_method_input" name="payment_method_id" value="">

                            {{--修正: カスタムドロップダウンの表示エリア--}}
                            <div id="custom-payment-select" class="form-control custom-select-control payment-select-display">
                                <span class="payment-select-text">選択してください</span>
                            </div>

                            {{-- ❗ 修正: ドロップダウンのリスト ❗ --}}
                            <ul id="payment-options" class="custom-select-options payment-options-list">
                                {{-- IDをPaymentMethodSeederの内容と合わせる --}}
                                <li data-value="1" class="custom-select-options-text">コンビニ払い</li>
                                <li data-value="2" class="custom-select-options-text">カード支払い</li>
                            </ul>

                            {{-- エラーメッセージのエリア（Laravelの@errorディレクティブを仮定） --}}
                            <p class="new_purchases-form__error-message">
                                @error('payment_method_id')
                                {{ $message }}
                                @enderror
                            </p>

                        </div>

                        {{-- 配送先 --}}
                        <div class="shipping-address-section section-divider">
                            <p class="section-title-small">配送先</p>
                            {{-- 🚨 ルート名を修正し、セッション保存用の編集画面へ遷移 🚨 --}}
                            <a href="{{ route('shipping_session.edit') }}" class="change-link">変更する</a>

                            <input type="hidden" name="shipping_post_code" value="{{ $shipping->shipping_post_code ?? '' }}">
                            <input type="hidden" name="shipping_address" value="{{ $shipping->shipping_address ?? '' }}">
                            <input type="hidden" name="shipping_building" value="{{ $shipping->shipping_building ?? '' }}">

                            <p class="address-post-code">〒 {{ $shipping->shipping_post_code ?? '---' }}</p>
                            <p class="address-detail">{{ ($shipping->shipping_address ?? '配送先住所を登録・変更してください。') . ' ' . ($shipping->shipping_building ?? '') }}</p>

                        </div>
                    </div>

                    {{-- 購入サマリーエリア（右側） --}}
                    <div class="purchase-summary-container">
                        <div class="price-summary-box">
                            <div class="price-row">
                                <p class="summary-label">商品代金</p>
                                <p class="summary-value">¥{{ number_format($item->price ?? 0) }}</p>
                            </div>
                            <div class="price-row">
                                <p class="summary-label">支払い方法</p>
                                <p class="summary-value payment-summary" id="summary-payment-method">コンビニ払い</p>
                            </div>
                        </div>
                        <div class="purchase-button-area">
                            <button type="submit" class="purchase-btn">購入する</button>
                        </div>
                    </div>
                </form>
                @else
                    {{-- $item が存在しない場合（PurchaseController@createでエラー処理された後など）--}}
                    <div class="error-message-box">
                        <p>商品情報が見つからないため、購入手続きに進めません。</p>
                        <a href="{{ route('items.index') }}">商品一覧へ戻る</a>
                    </div>
                @endisset
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectElement = document.getElementById('custom-payment-select');
        const optionsList = document.getElementById('payment-options');
        const hiddenInput = document.getElementById('payment_method_input');
        // ❗ 修正ポイント: 右側のサマリー表示要素を取得 ❗
        const summaryPaymentMethod = document.getElementById('summary-payment-method');

        // 1. ドロップダウンの表示/非表示を切り替える
        selectElement.addEventListener('click', function() {
            optionsList.style.display = optionsList.style.display === 'block' ? 'none' : 'block';
            selectElement.classList.toggle('active'); // 選択エリアにアクティブ状態を付与
        });

        // 2. 項目が選択されたときの処理
        optionsList.querySelectorAll('li').forEach(item => {
            item.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                const text = this.textContent;

                // A. 左側のフォーム要素を更新
                hiddenInput.value = value;
                selectElement.querySelector('span').textContent = text;
                selectElement.classList.add('selected'); // 選択後のスタイルを適用

                // B. 選択状態のスタイルを更新 (チェックマーク表示用)
                optionsList.querySelectorAll('li').forEach(li => li.classList.remove('selected'));
                // 選択された項目に 'selected' クラスを追加 (チェックマーク表示用)
                this.classList.add('selected');

                // C. ドロップダウンを非表示
                optionsList.style.display = 'none';
                selectElement.classList.remove('active');

                // ❗ 修正ポイント: 右側のサマリー表示を更新 ❗
                summaryPaymentMethod.textContent = text;
            });
        });

        // 3. ドロップダウンの外側をクリックしたときに閉じる
        document.addEventListener('click', function(e) {
            if (!selectElement.contains(e.target) && !optionsList.contains(e.target)) {
                optionsList.style.display = 'none';
                selectElement.classList.remove('active');
            }
        });

        // 4. 初期ロード時の処理: サマリーに初期値を設定する
        // hiddenInputの初期値に基づいてサマリーを更新
        const initialText = optionsList.querySelector(`li[data-value="${hiddenInput.value}"]`)?.textContent || '選択してください';
        selectElement.querySelector('span').textContent = initialText;
        summaryPaymentMethod.textContent = initialText;
    });
    </script>

    </body>
</html>